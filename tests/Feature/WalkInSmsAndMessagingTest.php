<?php

namespace Tests\Feature;

use App\Http\Controllers\MessageController;
use App\Models\Checkup;
use App\Models\Message;
use App\Models\WalkInPatient;
use App\Services\SmartNotificationService;
use Illuminate\Support\Facades\Http;

class WalkInSmsAndMessagingTest extends AutomationTestCase
{
    public function test_walkin_contact_validation_and_risk_sms(): void
    {
        $walkIn = WalkInPatient::create(['first_name' => 'Walk', 'last_name' => 'In', 'contact_number' => '09171234567']);
        $this->assertTrue($walkIn->hasSmsEnabled());
        $this->assertSame('639171234567', $walkIn->smsPhone());
        $this->assertSame('nullable|string|max:20|regex:/^(\+?63|0)?9\d{9}$/', WalkInPatient::phoneRule());

        (new SmartNotificationService())->notifyWalkInRisk($walkIn, 'High BP noted', 'High');
        (new SmartNotificationService())->notifyWalkInRisk($walkIn, 'High BP noted', 'High');
        $this->assertDatabaseCount('sms_logs', 1);
        $this->assertDatabaseHas('sms_logs', ['phone_number' => '639171234567', 'type' => 'high_risk_alert', 'status' => 'sent']);
        Http::assertSentCount(1);

        $noPhone = WalkInPatient::create(['first_name' => 'No', 'last_name' => 'Phone']);
        (new SmartNotificationService())->notifyWalkInRisk($noPhone, 'Concern', 'High');
        $this->assertDatabaseCount('sms_logs', 1);
    }

    public function test_walkin_checkup_sms_uses_bilingual_template_and_daily_cap(): void
    {
        $walkIn = WalkInPatient::create(['first_name' => 'Walk', 'last_name' => 'In', 'contact_number' => '09171234567']);
        $checkup = Checkup::create(['walk_in_patient_id' => $walkIn->id, 'scheduled_date' => today()->addDay(), 'status' => 'Scheduled']);
        $service = new SmartNotificationService();
        $service->notifyUpcomingCheckup($checkup);
        $service->notifyUpcomingCheckup($checkup);
        $this->assertDatabaseCount('sms_logs', 1);
        $log = \App\Models\SmsLog::first();
        $this->assertStringContainsString('Walk', $log->message);
        $this->assertStringContainsString('PAALALA', $log->message);
    }

    public function test_messaging_idempotency_trash_and_restore(): void
    {
        $a = $this->patient(['role' => 'midwife']);
        $b = $this->patient();
        $this->actingAs($a);
        $controller = new MessageController();
        $uuid = 'test-uuid-123';
        $req = function () use ($b, $uuid) {
            $r = \Illuminate\Http\Request::create('/midwife/messages/send', 'POST', [
                'receiver_id' => $b->id, 'receiver_role' => 'woman',
                'body' => 'Hello', 'client_uuid' => $uuid,
            ]);
            $r->headers->set('Accept', 'application/json');
            return $r;
        };
        $first = $controller->send($req());
        $second = $controller->send($req());
        $this->assertTrue($first->getData(true)['success']);
        $this->assertTrue($second->getData(true)['deduped']);
        $this->assertSame(1, Message::count());

        $thread = Message::first();
        $reply = Message::create(['sender_id' => $b->id, 'receiver_id' => $a->id, 'body' => 'Hi', 'reply_to_id' => $thread->id]);
        $this->actingAs($a);
        $updates = $controller->updates(\Illuminate\Http\Request::create('/x', 'GET', ['after_id' => $thread->id]), $thread->id);
        $this->assertCount(1, $updates->getData(true)['messages']);

        $delReq = \Illuminate\Http\Request::create('/x', 'DELETE');
        $delReq->headers->set('Accept', 'application/json');
        $controller->destroy($delReq, $thread->id);
        $this->assertSame(0, Message::count());
        $this->assertSame(2, Message::onlyTrashed()->count());

        $restoreReq = \Illuminate\Http\Request::create('/x', 'POST');
        $restoreReq->headers->set('Accept', 'application/json');
        $controller->restore($restoreReq, $thread->id);
        $this->assertSame(2, Message::count());
    }
}
