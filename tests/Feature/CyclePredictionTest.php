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

    public function test_archived_period_dates_can_be_logged_again(): void
    {
        $user = $this->patient();
        // Archived rows keep blocking the unique rule unless it excludes
        // soft-deleted records — so a typo'd date could never be re-logged.
        DB::table('cycles')->insert([
            'user_id' => $user->id,
            'period_start_date' => '2026-09-01',
            'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('user.menstruation.store'), ['start_date' => '2026-09-01'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('user.menstruation.index'));

        $this->assertSame(1, Cycle::whereDate('period_start_date', '2026-09-01')->count());
    }

    public function test_patient_period_history_is_listed_chronologically(): void
    {
        $user = $this->patient();
        $this->history($user->id, ['2026-07-01', '2026-08-01', '2026-09-01']);

        $html = $this->actingAs($user)
            ->get(route('user.menstruation.index'))
            ->assertOk()
            ->getContent();

        $july = strpos($html, 'Jul 01');
        $september = strpos($html, 'Sep 01');
        $this->assertNotFalse($july);
        $this->assertNotFalse($september);
        $this->assertLessThan($september, $july);
    }

    public function test_irregular_calendar_page_renders_range_and_confidence(): void
    {
        $user = $this->patient();
        $this->history($user->id, ['2026-05-01', '2026-05-22', '2026-07-06', '2026-08-03']);
        $this->actingAs($user);
        $html = (new \App\Http\Controllers\UserController())->menstruationCalendar()->render();
        $this->assertStringContainsString('Estimated period start window', $html);
        $this->assertStringContainsString('Weak prediction (Level 1/4)', $html);
        $this->assertStringNotContainsString('Ovulation Est. (day)', $html);
    }

    public function test_cycle_day_and_window_come_from_the_newest_record(): void
    {
        $user = $this->patient();
        // History itself is listed oldest-first, but the hero ring and the
        // prediction must still be measured from the latest period.
        $this->history($user->id, ['2026-07-01', '2026-08-01', '2026-09-02']);

        $html = $this->actingAs($user)
            ->get(route('user.menstruation.index'))
            ->assertOk()
            ->getContent();

        // 2026-09-15 is day 14 of a 32-day average cycle.
        $this->assertStringContainsString('<div class="day-num">14</div>', $html);
        $this->assertStringContainsString('Oct 3 – Oct 4', $html);
    }

    public function test_moderate_history_explains_which_cycles_limit_the_strength(): void
    {
        $user = $this->patient();
        // Four tight 28-day gaps plus one 22-day and one 34-day gap keep the
        // prediction at Level 2 — the page must say why instead of only
        // showing a bare level to a patient who has logged many records.
        $this->history($user->id, [
            '2026-02-19', '2026-03-19', '2026-04-16', '2026-05-14',
            '2026-06-11', '2026-07-03', '2026-08-06',
        ]);

        $html = $this->actingAs($user)
            ->get(route('user.menstruation.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Moderate prediction (Level 2 of 4)', $html);
        $this->assertStringContainsString('based on 6 completed cycles', $html);
        $this->assertStringContainsString('your last 6 cycles ranged 22–34 days', $html);
    }

    public function test_stale_history_is_advanced_instead_of_showing_past_dates(): void
    {
        $user = $this->patient();
        // Last log is over three average cycles old at the test date.
        $this->history($user->id, ['2026-05-01', '2026-05-29']);

        $detail = (new CyclePredictionService())->getPredictionDetail($user->id);

        $this->assertTrue($detail['is_stale']);
        $this->assertSame('2026-05-29', $detail['last_period_date']->toDateString());
        $this->assertSame('2026-09-18', $detail['next_predicted']->toDateString());
        $this->assertSame('2026-09-18', $detail['next_earliest']->toDateString());
        $this->assertFalse($detail['next_latest']->lt(today()));

        $predictions = (new CyclePredictionService())->predictFuturePeriods($user->id);
        $this->assertSame('2026-09-18', $predictions[0]['period_start']->toDateString());
        $this->assertFalse($predictions[0]['period_start_earliest']->lt(today()->subDay()));
    }

    public function test_implausible_intervals_are_excluded_from_predictions(): void
    {
        $user = $this->patient();
        // A 2-day "cycle" is a duplicate/typo, not an interval.
        $this->history($user->id, ['2026-06-01', '2026-06-29', '2026-07-27', '2026-08-24', '2026-08-26']);

        $detail = (new CyclePredictionService())->getPredictionDetail($user->id);
        $this->assertSame(28, $detail['average_cycle']);
        $this->assertSame(28, $detail['shortest_cycle']);
        $this->assertSame(28, $detail['longest_cycle']);

        // A very long gap usually means missing logs, not a 181-day cycle.
        $gappy = $this->patient();
        $this->history($gappy->id, ['2026-03-01', '2026-08-29']);
        $this->assertSame(28, (new CyclePredictionService())->getPredictionDetail($gappy->id)['average_cycle']);
    }

    public function test_short_cycles_do_not_show_an_ovulation_day(): void
    {
        $user = $this->patient();
        $this->history($user->id, ['2026-07-01', '2026-07-18', '2026-08-04', '2026-08-21']);

        $service = new CyclePredictionService();
        $detail = $service->getPredictionDetail($user->id);
        $this->assertSame(17, $detail['average_cycle']);

        $predictions = $service->predictFuturePeriods($user->id);
        $this->assertNull($predictions[0]['ovulation']);
        $this->assertNull($predictions[0]['fertile_start']);
        $this->assertNull($predictions[0]['fertile_end']);
    }

    public function test_patient_can_delete_a_period_record(): void
    {
        $user = $this->patient();
        $this->history($user->id, ['2026-08-01', '2026-09-01']);
        $record = Cycle::whereDate('period_start_date', '2026-09-01')->firstOrFail();

        $this->actingAs($user)
            ->delete(route('user.menstruation.destroy', $record->id))
            ->assertRedirect(route('user.menstruation.index'));

        $this->assertSoftDeleted('cycles', ['id' => $record->id]);
    }
}
