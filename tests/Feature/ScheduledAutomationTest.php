<?php

namespace Tests\Feature;

use App\Models\Checkup;
use App\Models\Message;
use App\Services\SmartNotificationService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ScheduledAutomationTest extends AutomationTestCase
{
    public function test_scheduled_messages_deliver_once_without_a_queue_worker(): void
    {
        $sender = $this->patient(['role' => 'bhw']);
        $patient = $this->patient();
        $due = Message::create(['sender_id' => $sender->id, 'receiver_id' => $patient->id, 'body' => 'Follow up',
            'scheduled_at' => now()->subMinute(), 'is_sent' => false]);
        $future = Message::create(['sender_id' => $sender->id, 'receiver_id' => $patient->id, 'body' => 'Later',
            'scheduled_at' => now()->addHour(), 'is_sent' => false]);
        $this->artisan('messages:process-scheduled')->assertSuccessful();
        $this->artisan('messages:process-scheduled')->assertSuccessful();
        $this->assertTrue($due->fresh()->is_sent);
        $this->assertFalse($future->fresh()->is_sent);
        $this->assertDatabaseCount('notifications', 1);
        Http::assertNothingSent();
    }

    public function test_scheduler_registers_daily_reminders_periods_and_minutely_messages(): void
    {
        $events = collect(app(Schedule::class)->events());
        foreach (['--reminders' => '45 8 * * *', '--periods' => '0 9 * * *',
            '--appointments' => '0 7 * * *', 'messages:process-scheduled' => '* * * * *'] as $name => $expression) {
            $event = $events->first(fn ($event) => str_contains($event->command ?? '', $name));
            $this->assertNotNull($event, $name);
            $this->assertSame($expression, $event->expression);
            $this->assertTrue($event->withoutOverlapping);
        }
    }

    public function test_todays_checkup_is_not_missed_and_overdue_walkin_does_not_crash(): void
    {
        $today = Checkup::create(['scheduled_date' => today(), 'status' => 'Scheduled']);
        $yesterday = Checkup::create(['scheduled_date' => today()->subDay(), 'status' => 'Scheduled']);
        $this->artisan('alerts:run', ['--checkups' => true])->assertSuccessful();
        $this->assertSame('Scheduled', $today->fresh()->status);
        $this->assertSame('Missed', $yesterday->fresh()->status);
    }

    public function test_walkin_sms_is_capped_once_daily_without_a_portal_notification(): void
    {
        $walkin = DB::table('walk_in_patients')->insertGetId(['first_name' => 'Walk', 'last_name' => 'In', 'contact_number' => '09171234567']);
        $checkup = Checkup::create(['walk_in_patient_id' => $walkin, 'scheduled_date' => today()->addDay(), 'status' => 'Scheduled']);
        $service = new SmartNotificationService();
        $service->notifyUpcomingCheckup($checkup);
        $service->notifyUpcomingCheckup($checkup);
        $this->assertDatabaseCount('notifications', 0);
        $this->assertDatabaseCount('sms_logs', 1);
        Http::assertSentCount(1);
    }

    public function test_manual_risk_command_uses_current_user_id_schema(): void
    {
        $patient = $this->patient();
        \App\Models\HealthRecord::create(['user_id' => $patient->id, 'risk_level' => 'Low']);
        Checkup::create(['user_id' => $patient->id, 'scheduled_date' => today()->subDays(2), 'status' => 'Missed']);
        $this->artisan('alerts:run', ['--risk' => true])->assertSuccessful();
        $this->artisan('alerts:run', ['--risk' => true])->assertSuccessful();
        $this->assertDatabaseCount('preventive_interventions', 1);
        $this->assertSame(1, Checkup::scheduled()->count());
        $this->assertDatabaseHas('health_records', ['user_id' => $patient->id, 'risk_level' => 'Medium']);
        $this->artisan('alerts:run', ['--reminders' => true])->assertSuccessful();
        $this->artisan('alerts:run', ['--appointments' => true])->assertSuccessful();
    }

    public function test_critical_risk_is_saved_and_creates_one_urgent_followup(): void
    {
        $patient = $this->patient();
        \App\Models\HealthRecord::create(['user_id' => $patient->id, 'bp' => '170/115']);
        Checkup::create(['user_id' => $patient->id, 'scheduled_date' => today()->subDays(2), 'status' => 'Missed']);
        $this->artisan('alerts:run', ['--risk' => true])->assertSuccessful();
        $this->assertDatabaseHas('health_records', ['user_id' => $patient->id, 'risk_level' => 'Critical']);
        $this->assertDatabaseHas('preventive_interventions', ['user_id' => $patient->id, 'risk_level' => 'Critical']);
        $this->assertSame(1, Checkup::scheduled()->count());
    }

    public function test_dry_run_rolls_back_all_reminder_and_checkup_changes(): void
    {
        $patient = $this->patient();
        Checkup::create(['user_id' => $patient->id, 'scheduled_date' => today()->addDay(), 'status' => 'Scheduled']);
        $this->artisan('alerts:run', ['--all' => true, '--dry-run' => true])->assertSuccessful();
        $this->assertDatabaseCount('notifications', 0);
        $this->assertDatabaseCount('sms_logs', 0);
        $this->assertDatabaseCount('checkups', 1);
    }

    public function test_daily_review_clears_risk_after_missed_care_is_completed_without_a_health_record(): void
    {
        $patient = $this->patient();
        $checkup = Checkup::create(['user_id' => $patient->id, 'scheduled_date' => today()->subDay(), 'status' => 'Missed']);
        $this->artisan('alerts:run', ['--risk' => true])->assertSuccessful();
        $checkup->update(['status' => 'Completed']);
        $this->artisan('alerts:run', ['--review' => true])->assertSuccessful();
        $this->assertSame('resolved', SmartNotificationService::patientRiskAlertStatus($patient->id)['state']);
    }
}
