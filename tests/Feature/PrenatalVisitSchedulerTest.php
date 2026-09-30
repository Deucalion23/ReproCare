<?php

namespace Tests\Feature;

use App\Models\Checkup;
use App\Models\Pregnancy;
use App\Services\PrenatalVisitScheduler;
use Carbon\Carbon;

class PrenatalVisitSchedulerTest extends AutomationTestCase
{
    protected function pregnantWoman(int $daysAgoLmp, array $attributes = []): \App\Models\User
    {
        $woman = $this->patient($attributes);
        Pregnancy::create([
            'user_id' => $woman->id,
            'lmp' => today()->subDays($daysAgoLmp)->toDateString(),
        ]);

        return $woman->fresh();
    }

    protected function completedCheckup(\App\Models\User $woman, ?\App\Models\User $midwife = null): Checkup
    {
        $midwife ??= $this->patient(['role' => 'midwife']);

        return Checkup::create([
            'user_id' => $woman->id,
            'midwife_id' => $midwife->id,
            'scheduled_by_id' => $midwife->id,
            'scheduled_date' => today()->toDateString(),
            'scheduled_time' => '09:00:00',
            'purpose' => 'First prenatal checkup',
            'status' => 'Completed',
        ]);
    }

    public function test_first_completion_schedules_next_visit_in_four_weeks(): void
    {
        $woman = $this->pregnantWoman(108); // ~15 weeks
        $midwife = $this->patient(['role' => 'midwife']);

        $next = app(PrenatalVisitScheduler::class)
            ->scheduleNextVisit($this->completedCheckup($woman, $midwife), $midwife);

        $this->assertNotNull($next);
        $this->assertSame('Scheduled', $next->status);
        $this->assertTrue($next->scheduled_date->isSameDay(today()->addWeeks(4)));
        $this->assertSame($midwife->id, $next->midwife_id);
        $this->assertSame('Prenatal follow-up visit (auto-scheduled)', $next->purpose);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $woman->id,
            'title' => 'Next prenatal visit scheduled',
        ]);
    }

    public function test_interval_shortens_with_gestation(): void
    {
        $second = $this->pregnantWoman(210); // 30 weeks
        $third = $this->pregnantWoman(259); // 37 weeks

        $nextSecond = app(PrenatalVisitScheduler::class)
            ->scheduleNextVisit($this->completedCheckup($second));
        $nextThird = app(PrenatalVisitScheduler::class)
            ->scheduleNextVisit($this->completedCheckup($third));

        $this->assertTrue($nextSecond->scheduled_date->isSameDay(today()->addWeeks(2)));
        $this->assertTrue($nextThird->scheduled_date->isSameDay(today()->addWeek()));
    }

    public function test_existing_upcoming_visit_is_not_duplicated(): void
    {
        $woman = $this->pregnantWoman(108);
        Checkup::create([
            'user_id' => $woman->id,
            'scheduled_date' => today()->addDays(10)->toDateString(),
            'purpose' => 'Already booked',
            'status' => 'Scheduled',
        ]);

        $next = app(PrenatalVisitScheduler::class)
            ->scheduleNextVisit($this->completedCheckup($woman));

        $this->assertNull($next);
        $this->assertSame(1, Checkup::where('user_id', $woman->id)->scheduled()->count());
    }

    public function test_no_schedule_without_active_pregnancy_or_past_edd(): void
    {
        // Ended pregnancy.
        $ended = $this->patient();
        Pregnancy::create([
            'user_id' => $ended->id,
            'lmp' => today()->subDays(300)->toDateString(),
            'ended_at' => today()->subDays(10)->toDateString(),
        ]);
        $this->assertNull(app(PrenatalVisitScheduler::class)
            ->scheduleNextVisit($this->completedCheckup($ended)));

        // Next visit would fall past the EDD (39w+1d, EDD in 6 days).
        $term = $this->pregnantWoman(274);
        $this->assertNull(app(PrenatalVisitScheduler::class)
            ->scheduleNextVisit($this->completedCheckup($term)));

        // Walk-in profile without a portal account.
        $walkIn = Checkup::create([
            'walk_in_patient_id' => 1,
            'scheduled_date' => today()->toDateString(),
            'purpose' => 'Walk-in checkup',
            'status' => 'Completed',
        ]);
        $this->assertNull(app(PrenatalVisitScheduler::class)->scheduleNextVisit($walkIn));

        // Not a completion.
        $woman = $this->pregnantWoman(108);
        $scheduled = Checkup::create([
            'user_id' => $woman->id,
            'scheduled_date' => today()->toDateString(),
            'purpose' => 'Not done yet',
            'status' => 'Scheduled',
        ]);
        $this->assertNull(app(PrenatalVisitScheduler::class)->scheduleNextVisit($scheduled));
    }

    public function test_mark_completed_auto_schedules_and_announces(): void
    {
        $midwife = $this->patient(['role' => 'midwife']);
        $woman = $this->pregnantWoman(108);
        $checkup = $this->completedCheckup($woman, $midwife);
        // Back to Scheduled so the controller performs the completion.
        $checkup->update(['status' => 'Scheduled']);

        $response = $this->actingAs($midwife)
            ->post(route('midwife.checkups.complete', $checkup->id));

        $response->assertRedirect();
        $response->assertSessionHas(
            'success',
            'Checkup marked as completed — next visit auto-scheduled for ' . today()->addWeeks(4)->format('F j, Y')
        );
        $this->assertDatabaseHas('checkups', [
            'user_id' => $woman->id,
            'status' => 'Scheduled',
            'purpose' => 'Prenatal follow-up visit (auto-scheduled)',
        ]);
    }
}
