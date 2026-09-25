<?php

namespace Tests\Feature;

use App\Models\Cycle;
use App\Services\CyclePredictionService;
use App\Services\PeriodNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CyclePredictionTest extends AutomationTestCase
{
    private function history(int $id, array $dates): void
    {
        foreach ($dates as $date) {
            Cycle::create(['user_id' => $id, 'period_start_date' => $date,
                'period_end_date' => Carbon::parse($date)->addDays(4)]);
        }
    }

    public function test_regular_history_predicts_from_start_to_start_intervals(): void
    {
        $user = $this->patient();
        $this->history($user->id, ['2026-06-01', '2026-06-29', '2026-07-27', '2026-08-24']);
        $service = new CyclePredictionService();
        $detail = $service->getPredictionDetail($user->id);
        $this->assertSame(28, $detail['average_cycle']);
        $this->assertSame('2026-09-21', $detail['next_predicted']->toDateString());
        $this->assertSame('regular', $detail['regularity']);
        $this->assertSame('high', $detail['confidence']);
        $this->assertSame(5, $service->getAveragePeriodLength($user->id));
    }

    public function test_irregular_history_has_low_confidence_widening_ranges_and_no_fertility_days(): void
    {
        $user = $this->patient();
        $this->history($user->id, ['2026-05-01', '2026-05-22', '2026-07-06', '2026-08-03']);
        $service = new CyclePredictionService();
        $detail = $service->getPredictionDetail($user->id);
        $this->assertSame('irregular', $detail['regularity']);
        $this->assertSame('low', $detail['confidence']);
        $this->assertSame('2026-08-24', $detail['next_earliest']->toDateString());
        $this->assertSame('2026-09-17', $detail['next_latest']->toDateString());
        $predictions = $service->predictFuturePeriods($user->id);
        $this->assertSame(48, (int) $predictions[1]['period_start_earliest']->diffInDays($predictions[1]['period_start_latest']));
        $this->assertNull($predictions[0]['ovulation']);
        $this->assertNull($predictions[0]['fertile_start']);
        $calendar = $service->getCalendarData($user->id, 2026, 8);
        $this->assertNull($calendar['current_cycle_prediction']);
        $this->assertCount(0, array_filter($calendar['calendar_days'], fn ($d) => $d['ovulation'] || $d['fertile']));
    }

    public function test_no_history_and_single_record_do_not_claim_high_confidence(): void
    {
        $user = $this->patient();
        $service = new CyclePredictionService();
        $this->assertNull($service->predictNextPeriod($user->id));
        $this->history($user->id, ['2026-09-01']);
        $this->assertSame('low', $service->getPredictionDetail($user->id)['confidence']);
        $this->assertSame('insufficient_data', $service->getCycleRegularity($user->id));
        $this->assertNull($service->predictFuturePeriods($user->id)[0]['ovulation']);
    }

    public function test_backdated_edits_deletes_and_stale_cached_lengths_are_handled(): void
    {
        $user = $this->patient();
        $this->history($user->id, ['2026-06-01', '2026-07-27', '2026-06-29']);
        $this->assertSame(28, Cycle::whereDate('period_start_date', '2026-07-27')->first()->cycle_length);
        Cycle::whereDate('period_start_date', '2026-06-29')->first()->delete();
        $this->assertSame(56, Cycle::whereDate('period_start_date', '2026-07-27')->first()->cycle_length);
        Cycle::whereDate('period_start_date', '2026-06-01')->first()->delete();
        $this->assertNull(Cycle::first()->cycle_length);
        DB::table('cycles')->whereNull('deleted_at')->update(['cycle_length' => 99]);
        $this->assertSame(28, (new CyclePredictionService())->getAverageCycleLength($user->id));
    }

    public function test_period_reminders_work_at_nine_am_and_retries_do_not_duplicate(): void
    {
        $user = $this->patient();
        $this->history($user->id, ['2026-08-21']);
        $service = new PeriodNotificationService();
        $this->assertCount(1, $service->checkAndNotify());
        $this->assertCount(0, $service->checkAndNotify());
        $this->assertDatabaseHas('notifications', ['user_id' => $user->id, 'type' => 'info', 'category' => 'period']);
        $this->travelTo(Carbon::parse('2026-09-17 09:00:00'));
        $this->assertSame('1_day_before', $service->checkAndNotify()[0]['type']);
        $this->travelTo(Carbon::parse('2026-09-18 09:00:00'));
        $this->assertSame('today', $service->checkAndNotify()[0]['type']);
        $this->history($user->id, ['2026-09-18']);
        $this->assertCount(0, $service->checkAndNotify());
    }

    public function test_pregnant_and_unapproved_patients_get_no_period_reminders(): void
    {
        $user = $this->patient();
        $this->history($user->id, ['2026-08-21']);
        // An overdue EDD alone does not mean a pregnancy has ended.
        DB::table('pregnancies')->insert(['user_id' => $user->id, 'edd' => '2026-09-01']);
        $other = $this->patient(['status' => 'pending']);
        $this->history($other->id, ['2026-08-21']);
        $this->assertCount(0, (new PeriodNotificationService())->checkAndNotify());
    }

    public function test_irregular_calendar_page_renders_range_and_confidence(): void
    {
        $user = $this->patient();
        $this->history($user->id, ['2026-05-01', '2026-05-22', '2026-07-06', '2026-08-03']);
        $this->actingAs($user);
        $html = (new \App\Http\Controllers\UserController())->menstruationCalendar()->render();
        $this->assertStringContainsString('Estimated period start window', $html);
        $this->assertStringContainsString('Low confidence', $html);
        $this->assertStringNotContainsString('Ovulation Est. (day)', $html);
    }
}
