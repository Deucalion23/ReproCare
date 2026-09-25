<?php

namespace Tests\Feature;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Models\Checkup;
use App\Models\HealthRecord;
use App\Models\Notification;
use App\Models\SmsLog;
use App\Services\SmartNotificationService;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Http;

class AlertLifecycleTest extends AutomationTestCase
{
    public function test_daily_risk_reminders_stop_after_read_even_when_risk_is_reevaluated(): void
    {
        $patient = $this->patient();
        HealthRecord::create(['user_id' => $patient->id, 'risk_level' => 'High']);
        $service = new SmartNotificationService();
        $service->notifyHighRisk($patient, 'Elevated blood pressure');
        $service->notifyHighRisk($patient, 'Elevated blood pressure');
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseCount('sms_logs', 1);
        $this->travelTo(Carbon::parse('2026-09-16 08:45:00'));
        $this->assertSame(1, $service->remindUnseenHighRisk());
        $this->assertSame(0, $service->remindUnseenHighRisk());
        $this->assertDatabaseCount('sms_logs', 2);
        Notification::first()->markAsRead();
        $this->travelTo(Carbon::parse('2026-09-17 08:45:00'));
        $service->notifyHighRisk($patient, 'Elevated blood pressure');
        $this->assertSame(0, $service->remindUnseenHighRisk());
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseCount('sms_logs', 2);
        $this->assertSame('seen', $service::patientRiskAlertStatus($patient->id)['state']);
        Http::assertSentCount(2);
    }

    public function test_manager_read_does_not_acknowledge_for_patient_and_receipt_is_linked(): void
    {
        $patient = $this->patient();
        $bhw = $this->patient(['role' => 'bhw']);
        HealthRecord::create(['user_id' => $patient->id, 'risk_level' => 'High', 'recorded_by_id' => $bhw->id]);
        $service = new SmartNotificationService();
        $service->notifyHighRisk($patient, 'Concern');
        $copy = Notification::where('user_id', $bhw->id)->firstOrFail();
        $copy->markAsRead();
        $this->assertFalse($copy->patientAlert->is_read);
        $this->travelTo(Carbon::parse('2026-09-16 08:45:00'));
        $service->remindUnseenHighRisk();
        $this->assertDatabaseCount('sms_logs', 2);
        $this->assertDatabaseCount('notifications', 2);
        $copy->patientAlert->markAsRead();
        $this->assertTrue($copy->fresh()->patientAlert->is_read);
    }

    public function test_resolved_risk_stops_and_a_later_new_episode_can_alert(): void
    {
        $patient = $this->patient();
        $record = HealthRecord::create(['user_id' => $patient->id, 'risk_level' => 'High']);
        $service = new SmartNotificationService();
        $service->notifyHighRisk($patient, 'Concern');
        $record->update(['risk_level' => 'Low']);
        $this->travelTo(Carbon::parse('2026-09-16 08:45:00'));
        $this->assertSame(0, $service->remindUnseenHighRisk());
        $this->assertSame('resolved', $service::patientRiskAlertStatus($patient->id)['state']);
        $record->update(['risk_level' => 'High']);
        $service->notifyHighRisk($patient, 'Concern');
        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseCount('sms_logs', 2);
    }

    public function test_legacy_acknowledgement_and_deleted_alert_are_not_recreated(): void
    {
        $patient = $this->patient();
        Notification::create(['user_id' => $patient->id, 'category' => 'risk', 'title' => 'High Risk Alert',
            'message' => 'Concern', 'is_read' => true, 'read_at' => now()]);
        $this->travelTo(Carbon::parse('2026-09-16 08:45:00'));
        $service = new SmartNotificationService();
        $service->notifyHighRisk($patient, 'Concern');
        Notification::first()->delete();
        $service->notifyHighRisk($patient, 'Concern');
        $this->assertSame(1, Notification::withTrashed()->count());
        Http::assertNothingSent();
    }

    public function test_sms_provider_errors_are_logged_and_not_retried_in_a_same_day_loop(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['message' => 'Device unavailable'], 503)]);
        $patient = $this->patient();
        $service = new SmartNotificationService();
        $service->notifyHighRisk($patient, 'Concern');
        $service->notifyHighRisk($patient, 'Concern');
        $this->assertDatabaseHas('sms_logs', ['status' => 'failed', 'error_message' => 'Device unavailable']);
        $this->assertSame('failed', $service::patientRiskAlertStatus($patient->id)['sms_status']);
        Http::assertSentCount(1);
    }

    public function test_gateway_payload_uses_international_number_and_links_acknowledgement(): void
    {
        $patient = $this->patient();
        (new SmartNotificationService())->notifyHighRisk($patient, 'Concern');
        Http::assertSent(fn ($request) => $request['recipients'] === ['+639171234567']
            && str_contains($request['message'], '/user/notifications')
            && $request->hasHeader('x-api-key', 'test-key'));
        $this->assertNotNull(SmsLog::first()->notification_id);
    }

    public function test_opt_out_and_missing_phone_do_not_send(): void
    {
        $service = new SmartNotificationService();
        $service->notifyHighRisk($this->patient(['sms_opt_out' => true]), 'Concern');
        $service->notifyHighRisk($this->patient(['contact_number' => null]), 'Concern');
        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseCount('sms_logs', 0);
        Http::assertNothingSent();
    }

    public function test_reading_notification_list_does_not_acknowledge_unopened_alerts(): void
    {
        $patient = $this->patient();
        (new SmartNotificationService())->notifyHighRisk($patient, 'Concern');
        $this->actingAs($patient);
        (new UserController())->notifications();
        $this->assertFalse(Notification::first()->is_read);
        (new NotificationController())->markAsRead(Notification::first()->id);
        $firstRead = Notification::first()->read_at;
        $this->travelTo(now()->addDay());
        (new NotificationController())->markAsRead(Notification::first()->id);
        $this->assertTrue($firstRead->equalTo(Notification::first()->read_at));
    }

    public function test_patient_cannot_acknowledge_another_patients_alert(): void
    {
        (new SmartNotificationService())->notifyHighRisk($this->patient(), 'Concern');
        $this->actingAs($this->patient());
        $this->expectException(ModelNotFoundException::class);
        (new NotificationController())->markAsRead(Notification::first()->id);
    }

    public function test_checkup_reminder_is_idempotent_and_completed_care_stops_stale_sms(): void
    {
        $patient = $this->patient();
        $checkup = Checkup::create(['user_id' => $patient->id, 'scheduled_date' => today()->addDay(), 'status' => 'Scheduled']);
        $service = new SmartNotificationService();
        $service->notifyUpcomingCheckup($checkup);
        $service->notifyUpcomingCheckup($checkup);
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseCount('sms_logs', 1);
        $checkup->update(['status' => 'Completed']);
        $this->travelTo(now()->addDay());
        $service->notifyUpcomingCheckup($checkup);
        $this->assertFalse((new SmsService())->send($patient, 'appointment_reminder', ['notification_id' => Notification::first()->id]));
        Http::assertSentCount(1);
    }

    public function test_checkup_read_receipt_does_not_suppress_a_different_appointment(): void
    {
        $patient = $this->patient();
        $service = new SmartNotificationService();
        $first = Checkup::create(['user_id' => $patient->id, 'scheduled_date' => today()->addDay(), 'status' => 'Scheduled']);
        $service->notifyUpcomingCheckup($first);
        Notification::first()->markAsRead();
        $this->travelTo(now()->addDay());
        $second = Checkup::create(['user_id' => $patient->id, 'scheduled_date' => today()->addDay(), 'status' => 'Scheduled']);
        $service->notifyUpcomingCheckup($second);
        $this->assertDatabaseCount('notifications', 2);
        Http::assertSentCount(2);
    }
}
