<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\BhwMonthlyReport;
use App\Models\Checkup;
use App\Models\CheckupReferral;
use App\Models\DuplicateReview;
use App\Models\EmergencyAlert;
use App\Models\HealthRecord;
use App\Models\MaternalCareTargetClient;
use App\Models\Newborn;
use App\Models\Notification;
use App\Models\PatientTransfer;
use App\Models\PostpartumVisit;
use App\Models\Pregnancy;
use App\Models\User;
use App\Models\WalkInPatient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Central workflow hub for the five cross-cutting maternal-health flows:
 *
 *  1. Rejection Feedback Loop  (send-back + correction + resubmission)
 *  2. Patient Relocation       (barangay/purok transfer requests)
 *  3. Emergency Fast-Lane      (critical-risk bypass of the BHW chain)
 *  4. Pregnancy→Postpartum     (delivery outcome auto-transition)
 *  5. Transparency             (in-app + SMS notifications on every action)
 */
class WorkflowService
{
    // States shared by health_records / pregnancies / bhw_monthly_reports.
    public const NEEDS_REVISION = 'needs_revision';

    // ─────────────────────────────────────────────────────────────────────
    // 5. Transparency Notifications (used by all flows below)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Create an in-app notification and, when possible, an SMS copy.
     * Never throws — notification failures must not break clinical flows.
     */
    public function notifyAction(
        int $userId,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null,
        bool $sendSms = false,
        ?string $smsEn = null,
        ?string $smsTl = null
    ): ?Notification {
        try {
            $notification = Notification::createNotification($userId, $message, $title, $type, $actionUrl);

            if ($sendSms) {
                $user = User::find($userId);
                if ($user && $user->hasSmsEnabled()) {
                    try {
                        (new SmsService())->sendCustom($user, $smsEn ?: $message, $smsTl ?: '');
                    } catch (\Throwable $e) {
                        Log::warning('WorkflowService SMS failed: ' . $e->getMessage());
                    }
                }
            }

            return $notification;
        } catch (\Throwable $e) {
            Log::error('WorkflowService notifyAction failed: ' . $e->getMessage());

            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // 1. Rejection Feedback Loop ("Send-Back" Mechanism)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Bounce a record back to the submitter's "Needs Revision" queue.
     *
     * @param  string  $kind  health_record|pregnancy|bhw_report
     * @param  Model   $record
     */
    public function sendBackForRevision(string $kind, Model $record, int $reviewerId, string $reason): Model
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new \InvalidArgumentException('A rejection reason is required.');
        }

        return DB::transaction(function () use ($kind, $record, $reviewerId, $reason) {
            $now = now();

            if ($record instanceof HealthRecord || $kind === 'health_record') {
                $record->update([
                    'workflow_status' => self::NEEDS_REVISION,
                    'rejected_by_id' => $reviewerId,
                    'rejected_at' => $now,
                    'rejection_reason' => $reason,
                    'workflow_notes' => $reason,
                    'revision_count' => ((int) ($record->revision_count ?? 0)) + 1,
                ]);
                $submitterId = (int) $record->recorded_by_id;
                $label = 'Health record for ' . $record->patient_name;
                $url = route('bhw.health-records.index');
            } elseif ($record instanceof Pregnancy || $kind === 'pregnancy') {
                $record->update([
                    'workflow_status' => self::NEEDS_REVISION,
                    'rejected_by_id' => $reviewerId,
                    'rejected_at' => $now,
                    'rejection_reason' => $reason,
                    'workflow_notes' => $reason,
                    'revision_count' => ((int) ($record->revision_count ?? 0)) + 1,
                ]);
                $submitterId = $this->resolvePregnancySubmitter($record);
                $label = 'Pregnancy record for ' . $record->patient_name;
                $url = route('bhw.pregnancies.index');
            } elseif ($record instanceof BhwMonthlyReport || $kind === 'bhw_report') {
                $record->update([
                    'submission_status' => self::NEEDS_REVISION,
                    'rejected_by_id' => $reviewerId,
                    'rejected_at' => $now,
                    'rejection_reason' => $reason,
                    'president_notes' => $record->president_notes,
                    'midwife_notes' => $reason,
                    'revision_count' => ((int) ($record->revision_count ?? 0)) + 1,
                ]);
                $submitterId = (int) $record->bhw_id;
                $label = 'Monthly report "' . ($record->title ?? "#{$record->id}") . '"';
                $url = route('bhw.reports.index');
            } else {
                throw new \InvalidArgumentException("Unknown revision kind: {$kind}");
            }

            // Transparency: submitter gets in-app + SMS notice with the note.
            if (!empty($submitterId)) {
                $this->notifyAction(
                    $submitterId,
                    '↩️ Needs Revision: ' . $label,
                    "Your submission was sent back for correction. Reviewer note: {$reason} Please edit and resubmit.",
                    'warning',
                    $url,
                    true
                );
            }

            try {
                ActivityLog::log('reject', "Sent back for revision ({$kind} #{$record->getKey()}): {$reason}");
            } catch (\Throwable $e) {
            }

            return $record->refresh();
        });
    }

    /**
     * Resubmit a corrected record after the BHW edits vitals/typos.
     *
     * @param  string  $kind  health_record|pregnancy|bhw_report
     */
    public function resubmitAfterRevision(string $kind, Model $record, int $submitterId, array $corrections = []): Model
    {
        return DB::transaction(function () use ($kind, $record, $submitterId, $corrections) {
            $status = $record->workflow_status ?? $record->submission_status ?? null;
            if (!in_array($status, [self::NEEDS_REVISION, 'rejected', 'bhw_president_rejected'], true)) {
                throw new \RuntimeException('Only records in the Needs Revision queue can be resubmitted.');
            }

            if (!empty($corrections)) {
                $record->update($corrections);
                $record->refresh();
            }

            if ($record instanceof HealthRecord || $kind === 'health_record') {
                $record->update([
                    'workflow_status' => 'submitted_to_bhw_president',
                    'submitted_to_bhw_president_at' => now(),
                    'resubmitted_at' => now(),
                    'workflow_notes' => trim(($record->workflow_notes ?? '') . "\n[Resubmitted after correction]"),
                ]);
                // Notify the BHW President(s) of the same barangay.
                $this->notifyReviewers($record->patient_barangay, 'bhw_president', 'Health record resubmitted', $record->patient_name);
            } elseif ($record instanceof Pregnancy || $kind === 'pregnancy') {
                $record->update([
                    'workflow_status' => 'submitted_to_bhw_president',
                    'submitted_to_bhw_president_at' => now(),
                    'resubmitted_at' => now(),
                ]);
                $this->notifyReviewers($record->woman?->barangay ?? $record->walkInPatient?->barangay, 'bhw_president', 'Pregnancy record resubmitted', $record->patient_name);
            } else {
                $record->update([
                    'submission_status' => 'submitted_to_president',
                    'submitted_to_president_by' => $submitterId,
                    'submitted_to_president_at' => now(),
                    'resubmitted_at' => now(),
                ]);
                $this->notifyReviewers($record->bhw?->barangay, 'bhw_president', 'Monthly report resubmitted', $record->title ?? "#{$record->id}");
            }

            try {
                ActivityLog::log('update', "Resubmitted after revision ({$kind} #{$record->getKey()})");
            } catch (\Throwable $e) {
            }

            return $record->refresh();
        });
    }

    /**
     * Records the BHW can still fix: drafts + bounced-back items.
     */
    public function revisionQueueForBhw(int $bhwId, string $kind = 'health_record')
    {
        if ($kind === 'pregnancy') {
            return Pregnancy::where('workflow_status', self::NEEDS_REVISION)
                ->where(function ($q) use ($bhwId) {
                    $q->whereHas('woman', fn ($w) => $w->where('created_by_bhw_id', $bhwId))
                        ->orWhereHas('walkInPatient', fn ($w) => $w->where('recorded_by_id', $bhwId));
                })->latest()->get();
        }

        if ($kind === 'bhw_report') {
            return BhwMonthlyReport::where('bhw_id', $bhwId)
                ->whereIn('submission_status', [self::NEEDS_REVISION, 'rejected'])
                ->latest()->get();
        }

        return HealthRecord::where('recorded_by_id', $bhwId)
            ->whereIn('workflow_status', [self::NEEDS_REVISION, 'bhw_president_rejected', 'recorded_by_bhw'])
            ->latest()->get();
    }

    // ─────────────────────────────────────────────────────────────────────
    // 2. Patient Relocation (Barangay-to-Barangay / Purok Transfers)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * BHW A initiates a transfer. No delete, no duplicate — a ledger row
     * is created and the actual reassignment happens only on approval.
     */
    public function requestPatientTransfer(array $data): PatientTransfer
    {
        $validated = validator($data, [
            'user_id' => 'nullable|exists:users,id',
            'walk_in_patient_id' => 'nullable|exists:walk_in_patients,id',
            'to_bhw_id' => 'nullable|exists:users,id',
            'to_purok_id' => 'nullable|exists:puroks,id',
            'to_barangay' => 'nullable|string|max:255',
            'reason' => 'required|string|max:1000',
            'requested_by_id' => 'required|exists:users,id',
        ])->validate();

        if (empty($validated['user_id']) && empty($validated['walk_in_patient_id'])) {
            throw new \InvalidArgumentException('A patient (user_id or walk_in_patient_id) is required.');
        }
        if (empty($validated['to_bhw_id']) && empty($validated['to_purok_id']) && empty($validated['to_barangay'])) {
            throw new \InvalidArgumentException('A transfer destination (BHW, purok, or barangay) is required.');
        }

        // Snapshot the current assignment so history is preserved.
        $fromBhwId = null;
        $fromPurokId = null;
        $fromBarangay = null;
        if (!empty($validated['user_id'])) {
            $patient = User::findOrFail($validated['user_id']);
            $fromBhwId = $patient->created_by_bhw_id;
            $fromPurokId = $patient->purok_id;
            $fromBarangay = $patient->barangay;
        } else {
            $patient = WalkInPatient::findOrFail($validated['walk_in_patient_id']);
            $fromBhwId = $patient->recorded_by_id;
            $fromPurokId = $patient->purok_id;
            $fromBarangay = $patient->barangay;
        }

        // Prevent duplicate pending requests for the same patient.
        $existing = PatientTransfer::pending()
            ->when(!empty($validated['user_id']), fn ($q) => $q->where('user_id', $validated['user_id']))
            ->when(!empty($validated['walk_in_patient_id']), fn ($q) => $q->where('walk_in_patient_id', $validated['walk_in_patient_id']))
            ->first();
        if ($existing) {
            throw new \RuntimeException('A pending transfer request already exists for this patient.');
        }

        $transfer = PatientTransfer::create([
            ...$validated,
            'from_bhw_id' => $fromBhwId,
            'from_purok_id' => $fromPurokId,
            'from_barangay' => $fromBarangay,
            'status' => PatientTransfer::STATUS_PENDING,
        ]);

        // Notify the receiving BHW + BHW Presidents / RHU admins for approval.
        if (!empty($validated['to_bhw_id'])) {
            $this->notifyAction(
                (int) $validated['to_bhw_id'],
                '🔄 Incoming Patient Transfer',
                "Patient {$transfer->patient_name} was requested for transfer to you. Reason: {$transfer->reason}",
                'info',
                route('bhw.patients')
            );
        }
        $this->notifyRoleForApproval($transfer);

        try {
            ActivityLog::log('create', "Requested patient transfer #{$transfer->id} for {$transfer->patient_name}");
        } catch (\Throwable $e) {
        }

        return $transfer;
    }

    /**
     * BHW President (or RHU Admin) approves: safely reassigns tracking
     * while preserving ALL past checkup / pregnancy history (FKs untouched).
     */
    public function approvePatientTransfer(int $transferId, int $approverId, ?string $notes = null): PatientTransfer
    {
        return DB::transaction(function () use ($transferId, $approverId, $notes) {
            $transfer = PatientTransfer::findOrFail($transferId);
            if (!$transfer->isPending()) {
                throw new \RuntimeException('This transfer request has already been reviewed.');
            }

            if ($transfer->user_id) {
                $patient = User::findOrFail($transfer->user_id);
                $patient->update(array_filter([
                    'barangay' => $transfer->to_barangay ?: $patient->barangay,
                    'purok_id' => $transfer->to_purok_id ?: $patient->purok_id,
                    'created_by_bhw_id' => $transfer->to_bhw_id ?: $patient->created_by_bhw_id,
                ], fn ($v) => $v !== null));
            } else {
                $patient = WalkInPatient::findOrFail($transfer->walk_in_patient_id);
                $patient->update(array_filter([
                    'barangay' => $transfer->to_barangay ?: $patient->barangay,
                    'purok_id' => $transfer->to_purok_id ?: $patient->purok_id,
                    'recorded_by_id' => $transfer->to_bhw_id ?: $patient->recorded_by_id,
                ], fn ($v) => $v !== null));
            }

            $transfer->update([
                'status' => PatientTransfer::STATUS_APPROVED,
                'reviewed_by_id' => $approverId,
                'reviewer_notes' => $notes,
                'reviewed_at' => now(),
            ]);

            // Transparency: tell the requesting BHW + receiving BHW.
            foreach (array_filter([$transfer->requested_by_id, $transfer->to_bhw_id, $transfer->from_bhw_id]) as $userId) {
                $this->notifyAction(
                    (int) $userId,
                    '✅ Patient Transfer Approved',
                    "Transfer of {$transfer->patient_name} was approved. Tracking reassigned; full history preserved.",
                    'success',
                    route('bhw.patients')
                );
            }

            try {
                ActivityLog::log('approve', "Approved patient transfer #{$transfer->id} for {$transfer->patient_name}", $transfer);
            } catch (\Throwable $e) {
            }

            return $transfer->refresh();
        });
    }

    public function rejectPatientTransfer(int $transferId, int $approverId, string $notes): PatientTransfer
    {
        if (trim($notes) === '') {
            throw new \InvalidArgumentException('A rejection reason is required.');
        }

        $transfer = PatientTransfer::findOrFail($transferId);
        if (!$transfer->isPending()) {
            throw new \RuntimeException('This transfer request has already been reviewed.');
        }

        $transfer->update([
            'status' => PatientTransfer::STATUS_REJECTED,
            'reviewed_by_id' => $approverId,
            'reviewer_notes' => $notes,
            'reviewed_at' => now(),
        ]);

        if ($transfer->requested_by_id) {
            $this->notifyAction(
                (int) $transfer->requested_by_id,
                '❌ Patient Transfer Rejected',
                "Transfer of {$transfer->patient_name} was rejected. Reason: {$notes}",
                'error',
                route('bhw.patients'),
                true
            );
        }

        return $transfer->refresh();
    }

    // ─────────────────────────────────────────────────────────────────────
    // 3. Emergency "Fast-Lane" Bypass (Critical Risk Override)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Instant escalation: bypasses President/monthly queues, flags a live
     * alert for Midwife + RHU Admin dashboards, and fires SMS broadcasts.
     */
    public function triggerEmergencyAlert(array $data): EmergencyAlert
    {
        $validated = validator($data, [
            'user_id' => 'nullable|exists:users,id',
            'walk_in_patient_id' => 'nullable|exists:walk_in_patients,id',
            'reported_by_id' => 'required|exists:users,id',
            'symptoms' => 'required|string|max:2000',
            'bp' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:2000',
            'location' => 'nullable|string|max:500',
            'barangay' => 'nullable|string|max:255',
            'purok_id' => 'nullable|exists:puroks,id',
        ])->validate();

        if (empty($validated['user_id']) && empty($validated['walk_in_patient_id'])) {
            throw new \InvalidArgumentException('An emergency alert requires a patient.');
        }

        return DB::transaction(function () use ($validated) {
            $reporter = User::find($validated['reported_by_id']);
            $midwife = User::where('role', 'midwife')->where('status', 'approved')->first();

            // 1) Urgent referral that jumps straight to the midwife queue.
            $referral = CheckupReferral::create([
                'referred_by_bhw_id' => $validated['reported_by_id'],
                'user_id' => $validated['user_id'] ?? null,
                'walk_in_patient_id' => $validated['walk_in_patient_id'] ?? null,
                'assigned_midwife_id' => $midwife?->id,
                'reason' => 'EMERGENCY: ' . $validated['symptoms'],
                'urgency' => 'emergency',
                'bhw_notes' => trim(($validated['notes'] ?? '') . ' BP: ' . ($validated['bp'] ?? 'n/a')),
                'status' => 'pending',
            ]);

            // 2) Alert ledger row for the live dashboards.
            $alert = EmergencyAlert::create([
                ...$validated,
                'referral_id' => $referral->id,
                'status' => EmergencyAlert::STATUS_ACTIVE,
            ]);

            $patientName = $alert->patient_name;
            $detail = "🚨 EMERGENCY for {$patientName}: {$validated['symptoms']} (BP " . ($validated['bp'] ?? 'n/a') . ')';

            // 3) In-app flags for every midwife + RHU admin (live dashboards).
            $responders = User::whereIn('role', ['midwife', 'rhu'])
                ->where('status', 'approved')->get(['id', 'role']);
            foreach ($responders as $responder) {
                $this->notifyAction(
                    $responder->id,
                    '🚨 EMERGENCY — Immediate Response Required',
                    $detail . " Reported by {$reporter?->name}. Location: " . ($validated['location'] ?? $validated['barangay'] ?? 'field visit'),
                    'danger',
                    $responder->role === 'rhu' ? route('rhu.dashboard') : route('midwife.referrals.index')
                );
            }

            // 4) Immediate SMS broadcast to the patient + responders.
            try {
                $sms = new SmsService();
                $patient = !empty($validated['user_id'])
                    ? User::find($validated['user_id'])
                    : null;
                if ($patient && $patient->hasSmsEnabled()) {
                    $sms->sendCustom(
                        $patient,
                        "🚨 REPROCARE EMERGENCY: {$patient->first_name}, your health worker raised an emergency alert ({$validated['symptoms']}). Please go to the nearest facility NOW.",
                        "🚨 REPROCARE EMERGENCY: {$patient->first_name}, nagtaas ng emergency alert ang health worker ({$validated['symptoms']}). Pumunta sa pinakamalapit na facility NGAYON."
                    );
                }
                foreach ($responders as $responder) {
                    $full = User::find($responder->id);
                    if ($full && $full->hasSmsEnabled()) {
                        $sms->sendCustom($full, $detail, '');
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Emergency SMS broadcast failed: ' . $e->getMessage());
            }

            try {
                ActivityLog::log('create', "EMERGENCY alert #{$alert->id} for {$patientName}: {$validated['symptoms']}", $alert);
            } catch (\Throwable $e) {
            }

            return $alert;
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // 4. Pregnancy-to-Postpartum Lifecycle Auto-Transition
    // ─────────────────────────────────────────────────────────────────────

    /**
     * When a pregnancy outcome is logged as delivered / live birth, close
     * the pregnancy and auto-generate the postpartum + newborn schedule:
     * visits at 24h / 1 week / 6 weeks and the standard immunization
     * series — without manual re-entry of patient context.
     *
     * Idempotent: safe to call twice (checks postpartum_transitioned_at).
     */
    public function handleDeliveryOutcome(Pregnancy $pregnancy, array $delivery): Pregnancy
    {
        $validated = validator($delivery, [
            'delivery_date' => 'required|date|before_or_equal:today',
            'delivery_time' => 'nullable',
            'facility_delivery_place' => 'nullable|string|max:255',
            'delivery_attendant' => 'nullable|string|max:255',
            'delivery_notes' => 'nullable|string|max:2000',
            'outcome' => 'nullable|string|max:50',
            'newborn_name' => 'nullable|string|max:255',
            'newborn_sex' => 'nullable|in:male,female',
            'birth_weight_kg' => 'nullable|numeric|min:0.3|max:8',
            'recorded_by_id' => 'nullable|exists:users,id',
        ])->validate();

        return DB::transaction(function () use ($pregnancy, $validated) {
            $pregnancy->refresh();
            $outcome = strtolower((string) ($validated['outcome'] ?? 'delivered'));
            $isLiveBirth = in_array($outcome, ['delivered', 'live_birth', 'live birth', 'normal'], true);

            $pregnancy->update([
                'delivery_date' => $validated['delivery_date'],
                'delivery_time' => $validated['delivery_time'] ?? $pregnancy->delivery_time,
                'facility_delivery_place' => $validated['facility_delivery_place'] ?? $pregnancy->facility_delivery_place,
                'delivery_attendant' => $validated['delivery_attendant'] ?? $pregnancy->delivery_attendant,
                'delivery_notes' => $validated['delivery_notes'] ?? $pregnancy->delivery_notes,
                'outcome' => $outcome,
                'ended_at' => $pregnancy->ended_at ?? $validated['delivery_date'],
            ]);

            // Record outcome on the target-client row (FHSIS/MNCHN reporting).
            // Schema enum: ft (full term) / pt (preterm) / fd (fetal death) / ab (abortion).
            $fhsisOutcome = match ($outcome) {
                'stillbirth' => 'fd',
                'miscarriage', 'abortion', 'terminated' => 'ab',
                default => 'ft', // delivered / live birth → full-term live birth
            };
            if ($pregnancy->maternalCareTargetClient) {
                $pregnancy->maternalCareTargetClient->update([
                    'pregnancy_outcome' => $fhsisOutcome,
                    'pregnancy_terminated_date' => $validated['delivery_date'],
                    'delivery_date' => $validated['delivery_date'],
                    'outcome_details' => $validated['delivery_notes'] ?? null,
                ]);
            } elseif ($pregnancy->user_id) {
                MaternalCareTargetClient::create([
                    'user_id' => $pregnancy->user_id,
                    'pregnancy_id' => $pregnancy->id,
                    'pregnancy_outcome' => $fhsisOutcome,
                    'pregnancy_terminated_date' => $validated['delivery_date'],
                    'delivery_date' => $validated['delivery_date'],
                ]);
            }

            // Already transitioned? Keep it idempotent.
            if ($pregnancy->postpartum_transitioned_at) {
                return $pregnancy->refresh();
            }

            $birthDate = \Carbon\Carbon::parse($validated['delivery_date'])->toDateString();
            $recorderId = $validated['recorded_by_id'] ?? auth()->id();

            // 1) Linked newborn (registered mothers; walk-ins tracked by name).
            $newborn = null;
            if ($pregnancy->user_id && $isLiveBirth) {
                $newborn = Newborn::firstOrCreate(
                    ['mother_id' => $pregnancy->user_id, 'pregnancy_id' => $pregnancy->id],
                    [
                        'name' => $validated['newborn_name'] ?? null,
                        'sex' => $validated['newborn_sex'] ?? null,
                        'birth_date' => $birthDate,
                        'birth_weight_kg' => $validated['birth_weight_kg'] ?? null,
                        'feeding_type' => 'exclusive_breast',
                        'notes' => 'Auto-created on delivery outcome.',
                        'recorded_by_id' => $recorderId,
                    ]
                );
                $newborn->seedImmunizationSchedule();
            }

            // 2) Postpartum visit schedule: 24h, 1 week, 6 weeks.
            $midwifeId = User::where('role', 'midwife')->where('status', 'approved')->value('id');
            $schedule = [
                ['label' => 'Postpartum visit — within 24 hours', 'date' => \Carbon\Carbon::parse($birthDate)->addDay()->toDateString()],
                ['label' => 'Postpartum visit — 1 week', 'date' => \Carbon\Carbon::parse($birthDate)->addWeek()->toDateString()],
                ['label' => 'Postpartum visit — 6 weeks', 'date' => \Carbon\Carbon::parse($birthDate)->addWeeks(6)->toDateString()],
            ];
            foreach ($schedule as $item) {
                $exists = Checkup::where('user_id', $pregnancy->user_id)
                    ->when($pregnancy->walk_in_patient_id, fn ($q) => $q->orWhere('walk_in_patient_id', $pregnancy->walk_in_patient_id))
                    ->where('purpose', $item['label'])
                    ->whereDate('scheduled_date', $item['date'])
                    ->exists();
                if (!$exists && ($pregnancy->user_id || $pregnancy->walk_in_patient_id)) {
                    Checkup::create([
                        'user_id' => $pregnancy->user_id,
                        'walk_in_patient_id' => $pregnancy->walk_in_patient_id,
                        'midwife_id' => $midwifeId,
                        'scheduled_by_id' => $recorderId,
                        'scheduled_date' => $item['date'],
                        'purpose' => $item['label'],
                        'notes' => "Auto-generated from delivery on {$birthDate} (pregnancy #{$pregnancy->id}).",
                        'status' => 'Scheduled',
                    ]);
                }
            }

            // 3) Placeholder postpartum visit rows so danger-sign tracking starts.
            if ($pregnancy->user_id) {
                foreach ([0, 1, 6] as $week) {
                    PostpartumVisit::firstOrCreate(
                        ['user_id' => $pregnancy->user_id, 'pregnancy_id' => $pregnancy->id, 'visit_week' => $week],
                        [
                            'newborn_id' => $newborn?->id,
                            'visit_date' => \Carbon\Carbon::parse($birthDate)->addWeeks($week)->toDateString(),
                            'bleeding' => 'none',
                            'breastfeeding' => 'exclusive',
                            'notes' => 'Auto-scheduled postpartum check (please update vitals on visit).',
                            'recorded_by_id' => $recorderId,
                        ]
                    );
                }
            }

            $pregnancy->update(['postpartum_transitioned_at' => now(), 'is_locked' => true]);

            // 4) Notify mother + assigned BHW + midwife.
            if ($pregnancy->user_id) {
                $this->notifyAction(
                    $pregnancy->user_id,
                    '🤱 Postpartum Care Started',
                    "Your postpartum schedule is ready: checkups at 24 hours, 1 week, and 6 weeks"
                        . ($newborn ? ', plus newborn immunizations.' : '.'),
                    'success',
                    route('user.dashboard'),
                    true
                );
            }
            $bhwId = $pregnancy->woman?->created_by_bhw_id;
            if ($bhwId) {
                $this->notifyAction(
                    (int) $bhwId,
                    '🤱 Delivery Logged — Postpartum Auto-Scheduled',
                    "Postpartum visits (24h / 1wk / 6wk) and immunizations were auto-created for {$pregnancy->patient_name}.",
                    'success',
                    route('bhw.postpartum.index')
                );
            }
            if ($midwifeId) {
                $this->notifyAction(
                    (int) $midwifeId,
                    '🤱 New Delivery — Postpartum Auto-Scheduled',
                    "Delivery logged for {$pregnancy->patient_name}. Postpartum + newborn schedule generated.",
                    'info',
                    route('midwife.postpartum.index')
                );
            }

            try {
                ActivityLog::log('create', "Auto-transitioned pregnancy #{$pregnancy->id} to postpartum/newborn schedule", $pregnancy);
            } catch (\Throwable $e) {
            }

            return $pregnancy->refresh();
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // Duplicate Patient Detection ("Double Registration" Trap)
    // ─────────────────────────────────────────────────────────────────────

    public static function normalizePersonName(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = preg_replace('/[^a-zñ\s]/u', '', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    /**
     * Fuzzy-match a patient against portal accounts + BHW field profiles.
     * Returns candidates with score >= 40: 100 = name+birthdate,
     * 90 = contact number, 70 = name+barangay, 40 = name only.
     *
     * @return array<int, array{kind:string, id:int, name:string, barangay:?string, birthdate:?string, contact:?string, score:int, match_type:string}>
     */
    public function findPossibleDuplicates(User $patient): array
    {
        $name = self::normalizePersonName(
            trim($patient->first_name . ' ' . $patient->middle_initial . ' ' . $patient->last_name)
        );
        if ($name === '') {
            return [];
        }

        $birthdate = $patient->date_of_birth?->toDateString();
        $barangay = mb_strtolower(trim((string) $patient->barangay));
        $contact = preg_replace('/\D/', '', (string) $patient->contact_number);

        $candidates = [];

        $users = User::where('role', 'user')
            ->where('id', '!=', $patient->id)
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'barangay', 'contact_number', 'status']);
        foreach ($users as $u) {
            $score = $this->duplicateScore(
                $name, $birthdate, $barangay, $contact,
                self::normalizePersonName(trim($u->first_name . ' ' . $u->middle_initial . ' ' . $u->last_name)),
                $u->date_of_birth?->toDateString(),
                mb_strtolower(trim((string) $u->barangay)),
                preg_replace('/\D/', '', (string) $u->contact_number)
            );
            if ($score[0] >= 40) {
                $candidates[] = [
                    'kind' => 'user', 'id' => $u->id, 'name' => $u->name,
                    'barangay' => $u->barangay,
                    'birthdate' => $u->date_of_birth?->toDateString(),
                    'contact' => $u->contact_number,
                    'status' => $u->status,
                    'score' => $score[0], 'match_type' => $score[1],
                ];
            }
        }

        $walkIns = WalkInPatient::whereNull('converted_to_user_id')
            ->whereNull('user_id')
            ->get(['id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'barangay', 'contact_number']);
        foreach ($walkIns as $w) {
            $score = $this->duplicateScore(
                $name, $birthdate, $barangay, $contact,
                self::normalizePersonName(trim($w->first_name . ' ' . $w->middle_initial . ' ' . $w->last_name)),
                $w->date_of_birth?->toDateString(),
                mb_strtolower(trim((string) $w->barangay)),
                preg_replace('/\D/', '', (string) $w->contact_number)
            );
            if ($score[0] >= 40) {
                $candidates[] = [
                    'kind' => 'walk_in', 'id' => $w->id, 'name' => $w->full_name,
                    'barangay' => $w->barangay,
                    'birthdate' => $w->date_of_birth?->toDateString(),
                    'contact' => $w->contact_number,
                    'status' => 'field record',
                    'score' => $score[0], 'match_type' => $score[1],
                ];
            }
        }

        usort($candidates, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($candidates, 0, 5);
    }

    /** @return array{int, string} [score, match_type] */
    private function duplicateScore(string $name, ?string $dob, string $brgy, string $contact, string $otherName, ?string $otherDob, string $otherBrgy, string $otherContact): array
    {
        if ($otherName === '' || $otherName !== $name) {
            // Contact-only fallback still catches re-registered numbers.
            if ($contact !== '' && $contact === $otherContact) {
                return [90, 'contact'];
            }

            return [0, 'none'];
        }

        if ($dob && $otherDob && $dob === $otherDob) {
            return [100, 'name_birthdate'];
        }
        if ($contact !== '' && $contact === $otherContact) {
            return [90, 'name_contact'];
        }
        if ($brgy !== '' && $brgy === $otherBrgy) {
            return [70, 'name_barangay'];
        }

        return [40, 'name_only'];
    }

    /**
     * Link a BHW field profile to the portal account (same person, one
     * identity). History on both sides is preserved — nothing is deleted.
     */
    public function linkDuplicateWalkIn(int $walkInId, int $userId, int $reviewerId, ?string $notes = null): WalkInPatient
    {
        return DB::transaction(function () use ($walkInId, $userId, $reviewerId, $notes) {
            $walkIn = WalkInPatient::findOrFail($walkInId);
            $user = User::where('role', 'user')->findOrFail($userId);

            if ($walkIn->linkedUserId() !== null && $walkIn->linkedUserId() !== $user->id) {
                throw new \RuntimeException('This field profile is already linked to a different account.');
            }

            $walkIn->update([
                'user_id' => $user->id,
                'converted_to_user_id' => $user->id,
                'converted_at' => now(),
                'has_portal_access' => true,
            ]);

            DuplicateReview::create([
                'user_id' => $user->id,
                'walk_in_patient_id' => $walkIn->id,
                'match_type' => 'manual_link',
                'score' => 100,
                'status' => DuplicateReview::STATUS_LINKED,
                'reviewed_by_id' => $reviewerId,
                'reviewer_notes' => $notes,
                'reviewed_at' => now(),
            ]);

            $this->notifyAction(
                $user->id,
                '🔗 Records Linked',
                "Your BHW field records were linked to your portal account. Full history preserved on both sides.",
                'success',
                route('user.dashboard')
            );

            try {
                ActivityLog::log('update', "Linked field profile #{$walkIn->id} ({$walkIn->full_name}) to account #{$user->id} ({$user->name})");
            } catch (\Throwable $e) {
            }

            return $walkIn->refresh();
        });
    }

    public function dismissDuplicate(?int $userId, ?int $walkInId, ?int $matchedUserId, ?int $matchedWalkInId, int $reviewerId, string $matchType, int $score): DuplicateReview
    {
        return DuplicateReview::create([
            'user_id' => $userId,
            'walk_in_patient_id' => $walkInId,
            'matched_user_id' => $matchedUserId,
            'matched_walk_in_patient_id' => $matchedWalkInId,
            'match_type' => $matchType,
            'score' => $score,
            'status' => DuplicateReview::STATUS_DISMISSED,
            'reviewed_by_id' => $reviewerId,
            'reviewed_at' => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Staff Offboarding Guard (no stranded patients / pending items)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Count work that would strand if this staff account were deactivated.
     *
     * @return array<string, int> labels => counts (empty = safe to offboard)
     */
    public function offboardingBlockers(User $staff): array
    {
        $blockers = [];

        if ($staff->role === 'bhw') {
            $counts = [
                'Active assigned patients' => User::where('role', 'user')
                    ->where('status', 'approved')->where('created_by_bhw_id', $staff->id)->count(),
                'Actionable health records' => HealthRecord::where('recorded_by_id', $staff->id)
                    ->whereIn('workflow_status', ['recorded_by_bhw', 'submitted_to_bhw_president', 'needs_revision', 'bhw_president_rejected'])->count(),
                'Pending scheduled checkups' => Checkup::where('scheduled_by_id', $staff->id)
                    ->whereNotIn('status', ['Completed', 'Cancelled', 'Missed'])->count(),
                'Open reports / referrals' => BhwMonthlyReport::where('bhw_id', $staff->id)
                    ->whereIn('submission_status', ['draft', 'submitted_to_president', 'needs_revision', 'rejected'])->count()
                    + CheckupReferral::where('referred_by_bhw_id', $staff->id)->where('status', 'pending')->count(),
            ];
        } elseif ($staff->role === 'midwife') {
            $counts = [
                'Actionable checkups' => Checkup::where(function ($q) use ($staff) {
                        $q->where('midwife_id', $staff->id)->orWhere('scheduled_by_id', $staff->id);
                    })->whereNotIn('status', ['Completed', 'Cancelled', 'Missed'])->count(),
                'Records awaiting midwife review' => HealthRecord::where('workflow_status', 'submitted_to_midwife')->count(),
                'Registered patients' => User::where('role', 'user')
                    ->where('status', 'approved')->where('created_by_midwife_id', $staff->id)->count(),
            ];
        } elseif ($staff->role === 'bhw_president') {
            $counts = [
                'Pending presidential reviews' => BhwMonthlyReport::where('submission_status', 'submitted_to_president')->count(),
                'Team BHWs' => User::where('role', 'bhw')->where('status', 'approved')
                    ->where('barangay', $staff->barangay)->count(),
            ];
        } else {
            return [];
        }

        return array_filter($counts, fn ($c) => $c > 0);
    }

    /**
     * Offboarding gate: returns a blocking redirect when this staff member
     * still owns work that would strand, otherwise null (safe to proceed).
     * Forces the handover-first flow: deactivate is refused until the
     * Staff Transitions console reassigns every item.
     */
    public function guardOffboarding(User $staff)
    {
        $blockers = $this->offboardingBlockers($staff);
        if (empty($blockers)) {
            return null;
        }

        $summary = collect($blockers)
            ->map(fn ($count, $label) => "{$count} {$label}")
            ->implode(', ');

        $message = "Cannot offboard {$staff->name} yet: {$summary} would be stranded. "
            . 'Complete the handover first — every item must have a new owner.';

        if (auth()->check() && in_array(auth()->user()->role, ['rhu', 'cho'], true)) {
            return redirect($this->offboardingConsoleUrl($staff))
                ->with('error', $message . ' The transition console below is pre-filled for this worker.');
        }

        return back()->withErrors(['handover' => $message . ' Please ask your RHU Admin to run the handover.']);
    }

    /**
     * Handover console jump for the offboarding guard message.
     */
    public function offboardingConsoleUrl(User $staff): string
    {
        $type = match ($staff->role) {
            'bhw' => 'bhw_transfer',
            'bhw_president' => 'president_replace',
            default => 'midwife_replace',
        };

        return route('rhu.staff-transitions.index', ['type' => $type, 'outgoing_id' => $staff->id]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Sequential Pregnancy Support (gravida/para carryover)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Suggest gravida/para for a NEW pregnancy from the latest closed one:
     * gravida always +1 (a new pregnancy), parity +1 only after a live birth.
     *
     * @return array{gravida: int, para: int}
     */
    public function suggestGravidaPara(?int $userId, ?int $walkInId = null): array
    {
        $last = Pregnancy::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(!$userId && $walkInId, fn ($q) => $q->where('walk_in_patient_id', $walkInId))
            ->whereNotNull('ended_at')
            ->with('maternalCareTargetClient')
            ->latest('ended_at')
            ->first();

        if (!$last) {
            return ['gravida' => 1, 'para' => 0];
        }

        $client = $last->maternalCareTargetClient;
        $gravida = ((int) ($client?->gravida ?? 1)) + 1;
        $para = (int) ($client?->parity ?? 0);
        $outcome = strtolower((string) ($last->outcome ?? $client?->pregnancy_outcome ?? ''));
        if (in_array($outcome, ['delivered', 'live_birth', 'live birth', 'ft', 'pt'], true)) {
            $para++;
        }

        return ['gravida' => $gravida, 'para' => $para];
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function resolvePregnancySubmitter(Pregnancy $pregnancy): ?int
    {
        $first = $pregnancy->healthRecords()->oldest()->first();

        return (int) ($first?->recorded_by_id
            ?? $pregnancy->woman?->created_by_bhw_id
            ?? $pregnancy->walkInPatient?->recorded_by_id
            ?? 0) ?: null;
    }

    private function notifyReviewers(?string $barangay, string $role, string $title, string $subject): void
    {
        try {
            $reviewers = User::where('role', $role)->where('status', 'approved')
                ->when($barangay, fn ($q) => $q->where(fn ($w) => $w->whereNull('barangay')->orWhere('barangay', $barangay)))
                ->get(['id']);
            foreach ($reviewers as $reviewer) {
                $this->notifyAction(
                    $reviewer->id,
                    '📥 ' . $title,
                    "{$title}: {$subject} needs review.",
                    'info',
                    route('bhw-president.health-records.index')
                );
            }
        } catch (\Throwable $e) {
        }
    }

    private function notifyRoleForApproval(PatientTransfer $transfer): void
    {
        try {
            $approvers = User::whereIn('role', ['bhw_president', 'rhu'])
                ->where('status', 'approved')->get(['id', 'role']);
            foreach ($approvers as $approver) {
                $this->notifyAction(
                    $approver->id,
                    '🔄 Patient Transfer Needs Approval',
                    "Transfer request for {$transfer->patient_name} → {$transfer->to_barangay}. Reason: {$transfer->reason}",
                    'warning',
                    $approver->role === 'rhu' ? route('rhu.dashboard') : route('bhw-president.dashboard')
                );
            }
        } catch (\Throwable $e) {
        }
    }
}
