<?php

namespace App\Services;

use App\Models\Checkup;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SmartNotificationService
{
    /** One alert per risk episode. Reading it never starts another episode. */
    public function notifyHighRisk(User $patient, string $reason, string $riskLevel = 'High'): void
    {
        if ($riskLevel === 'Low') {
            $this->resolveRisk($patient->id);
            return;
        }
        if ($patient->role !== 'user' || $patient->status !== 'approved') {
            return;
        }

        $alert = DB::transaction(function () use ($patient, $reason, $riskLevel) {
            User::whereKey($patient->id)->lockForUpdate()->firstOrFail();
            $latest = Notification::withTrashed()->where('user_id', $patient->id)
                ->where('category', 'risk')->latest('id')->first();
            $fingerprint = hash('sha256', $riskLevel . '|' . trim($reason));

            // Adopt a legacy alert without losing its read receipt.
            if ($latest && !$latest->risk_fingerprint && !$latest->resolved_at) {
                $latest->update(['risk_fingerprint' => $fingerprint, 'subject_user_id' => $patient->id]);
            }
            if ($latest && !$latest->resolved_at && $latest->risk_fingerprint === $fingerprint) {
                return $latest;
            }
            // At most one new risk notification per patient per calendar day.
            if ($latest && $latest->created_at->isToday()) {
                return null;
            }
            $this->resolveRisk($patient->id);
            return Notification::create([
                'user_id' => $patient->id,
                'subject_user_id' => $patient->id,
                'category' => 'risk',
                'event_key' => 'risk:' . Str::uuid(),
                'risk_fingerprint' => $fingerprint,
                'title' => "{$riskLevel} Risk Alert",
                'message' => "Our system has detected a health concern: {$reason} Please contact your health worker immediately.",
                'type' => in_array($riskLevel, ['High', 'Critical'], true) ? 'danger' : 'warning',
                'action_url' => '/user/health-records',
                'is_read' => false,
            ]);
        });

        if ($alert) {
            $this->remindRiskAlert($alert, $patient);
        }
    }

    public function resolveRisk(int $patientId): void
    {
        Notification::withTrashed()->where('user_id', $patientId)->where('category', 'risk')
            ->whereNull('resolved_at')->update(['resolved_at' => now()]);
    }

    private function remindRiskAlert(Notification $alert, User $patient): bool
    {
        $alert->refresh();
        if ($alert->is_read || $alert->read_at || $alert->resolved_at || $alert->trashed()) {
            return false;
        }
        $claimed = Notification::whereKey($alert->id)->where('is_read', false)
            ->whereNull('read_at')->whereNull('resolved_at')
            ->where(fn ($q) => $q->whereNull('last_reminded_at')->orWhere('last_reminded_at', '<', today()))
            ->update(['last_reminded_at' => now()]);
        if (!$claimed) {
            return false;
        }

        app(SmsService::class)->send($patient, 'high_risk_alert', [
            'reason' => $alert->message, 'notification_id' => $alert->id,
        ]);
        $this->notifyManagers($patient, $alert);
        return true;
    }

    private function notifyManagers(User $patient, Notification $alert): void
    {
        $bhwId = $patient->healthRecords()->whereNotNull('recorded_by_id')->latest('id')->value('recorded_by_id')
            ?: $patient->created_by_bhw_id;
        $midwifeId = $patient->checkups()->whereNotNull('midwife_id')->latest('id')->value('midwife_id')
            ?: $patient->created_by_midwife_id;
        $ids = json_decode((string) Setting::get('rhu.escalation_recipients', '[]'), true) ?: [];
        $recipients = User::whereIn('id', array_filter(array_merge((array) $ids, [$bhwId, $midwifeId])))
            ->whereIn('role', ['bhw', 'midwife', 'rhu'])->where('status', 'approved')->get();
        foreach ($recipients as $recipient) {
            Notification::firstOrCreate([
                'user_id' => $recipient->id, 'event_key' => 'risk-copy:' . $alert->id,
            ], [
                'category' => 'risk_staff', 'subject_user_id' => $patient->id,
                'parent_notification_id' => $alert->id,
                'title' => 'Patient Risk Alert – ' . $patient->name,
                'message' => $alert->message, 'type' => $alert->type,
                'action_url' => $recipient->role === 'rhu' ? '/rhu/dashboard' : "/{$recipient->role}/patients/{$patient->id}",
                'is_read' => false,
            ]);
        }
    }

    /** The latest episode is authoritative; old unread alerts cannot restart SMS. */
    public function remindUnseenHighRisk(): int
    {
        $count = 0;
        User::where('role', 'user')->where('status', 'approved')
            ->whereHas('notifications', fn ($q) => $q->where('category', 'risk')->whereNull('resolved_at'))
            ->eachById(function (User $patient) use (&$count) {
                $latestRecord = $patient->healthRecords()->latest('id')->first();
                if ($latestRecord && $latestRecord->risk_level === 'Low') {
                    $this->resolveRisk($patient->id);
                    return;
                }
                $alert = Notification::withTrashed()->where('user_id', $patient->id)
                    ->where('category', 'risk')->latest('id')->first();
                if ($alert && $this->remindRiskAlert($alert, $patient)) {
                    $count++;
                }
            });
        return $count;
    }

    /**
     * Walk-in risk SMS: walk-ins have no portal inbox, so the contact number
     * is the alert channel. Capped once per phone per day inside SmsService.
     * Also notifies the recording BHW / assigned midwife in-app for follow-up.
     */
    public function notifyWalkInRisk(\App\Models\WalkInPatient $walkIn, string $reason, string $riskLevel = 'High'): void
    {
        if ($riskLevel === 'Low' || !$walkIn->hasSmsEnabled()) {
            return;
        }
        app(SmsService::class)->sendToWalkIn($walkIn, 'high_risk_alert', [
            'reason' => $reason,
        ]);
        $staffIds = array_filter([$walkIn->recorded_by_id]);
        $midwifeId = \App\Models\Checkup::where('walk_in_patient_id', $walkIn->id)
            ->whereNotNull('midwife_id')->latest('id')->value('midwife_id');
        if ($midwifeId) {
            $staffIds[] = $midwifeId;
        }
        foreach (array_unique($staffIds) as $staffId) {
            $staff = User::find($staffId);
            if (!$staff) {
                continue;
            }
            Notification::firstOrCreate([
                'user_id' => $staffId,
                'event_key' => 'walkin-risk:'.$walkIn->id.':'.today()->toDateString(),
            ], [
                'category' => 'risk_staff',
                'subject_user_id' => null,
                'title' => 'Walk-in Risk Alert – '.$walkIn->full_name,
                'message' => "{$riskLevel} risk for walk-in {$walkIn->full_name} ({$walkIn->contact_number}): {$reason}",
                'type' => in_array($riskLevel, ['High', 'Critical'], true) ? 'danger' : 'warning',
                'action_url' => ($staff->role === 'midwife' ? '/midwife/walk-in-patients/' : '/bhw/walk-in-patients/').$walkIn->id,
                'is_read' => false,
            ]);
        }
    }

    public function notifyUpcomingCheckup(Checkup $checkup): void
    {
        $this->notifyCheckup($checkup, false);
    }

    public function notifyMissedCheckup(Checkup $checkup): void
    {
        $this->notifyCheckup($checkup, true);
    }

    private function notifyCheckup(Checkup $checkup, bool $missed): void
    {
        $checkup->refresh();
        if ($checkup->status !== ($missed ? 'Missed' : 'Scheduled')) {
            return;
        }
        if (!$missed && !$checkup->scheduled_date->isTomorrow()) {
            return;
        }
        $date = $checkup->scheduled_date->format('F j, Y');
        $time = $checkup->scheduled_time ? \Carbon\Carbon::parse($checkup->scheduled_time)->format('h:i A') : 'your scheduled time';
        $type = $missed ? 'missed_checkup' : 'appointment_reminder';
        $message = $missed
            ? "You missed your scheduled checkup on {$date}. Please reschedule as soon as possible."
            : "You have a checkup scheduled for tomorrow, {$date} at {$time}.";

        if ($checkup->user_id && $checkup->woman) {
            $alert = Notification::withTrashed()->firstOrCreate([
                'user_id' => $checkup->user_id,
                'event_key' => "{$type}:{$checkup->id}:" . $checkup->scheduled_date->toDateString() . ':' . ($checkup->scheduled_time ?? ''),
            ], [
                'category' => $type, 'checkup_id' => $checkup->id,
                'subject_user_id' => $checkup->user_id,
                'title' => $missed ? 'Missed Checkup' : 'Upcoming Checkup Reminder',
                'message' => $message, 'type' => $missed ? 'danger' : 'info',
                'action_url' => '/user/checkups', 'is_read' => false,
            ]);
            app(SmsService::class)->send($checkup->woman, $type, [
                'date' => $date, 'time' => $time, 'notification_id' => $alert->id,
            ]);
        } elseif ($checkup->walkInPatient?->contact_number) {
            $checkup->loadMissing('walkInPatient');
            app(SmsService::class)->sendToWalkIn($checkup->walkInPatient, $type, [
                'date' => $date, 'time' => $time,
            ]);
        }

        if ($missed) {
            foreach (array_unique(array_filter([$checkup->scheduled_by_id, $checkup->midwife_id])) as $staffId) {
                $staff = User::find($staffId);
                if (!$staff) continue;
                Notification::firstOrCreate([
                    'user_id' => $staffId, 'event_key' => "missed-staff:{$checkup->id}:" . $checkup->scheduled_date->toDateString(),
                ], [
                    'title' => 'Checkup Missed – ' . $checkup->patient_name,
                    'message' => $message, 'type' => 'warning', 'is_read' => false,
                    'checkup_id' => $checkup->id,
                ]);
            }
        }
    }

    public static function patientRiskAlertStatus(int $patientId): array
    {
        $latest = Notification::withTrashed()->where('user_id', $patientId)
            ->where('category', 'risk')->latest('id')->first();
        if (!$latest) {
            return ['state' => 'none', 'seen' => false, 'read_at' => null, 'created_at' => null];
        }
        $sms = $latest->smsLogs()->latest('id')->first();
        return [
            'state' => $latest->resolved_at ? 'resolved' : ($latest->is_read || $latest->read_at ? 'seen' : 'unseen'),
            'seen' => (bool) ($latest->is_read || $latest->read_at),
            'read_at' => $latest->read_at, 'created_at' => $latest->created_at,
            'resolved_at' => $latest->resolved_at, 'last_reminded_at' => $latest->last_reminded_at,
            'title' => $latest->title, 'sms_status' => $sms?->status,
            'sms_mock' => $sms && str_starts_with($sms->provider_sid ?? '', 'MOCK_'),
            'sms_sent_at' => $sms?->sent_at,
        ];
    }
}
