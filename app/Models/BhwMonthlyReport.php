<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BhwMonthlyReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bhw_monthly_reports';

    protected $fillable = [
        'bhw_id',
        'report_type',
        'title',
        'description',
        'report_month',
        'report_year',
        'filters',
        'total_records',
        'status',
        'printed_at',
        'submission_status',
        'submitted_to_president_by',
        'submitted_to_president_at',
        'approved_by_president',
        'approved_by_president_at',
        'president_notes',
        'submitted_to_midwife_by',
        'submitted_to_midwife_at',
        'approved_by_midwife',
        'approved_by_midwife_at',
        'midwife_notes',
        'approved_by_rhu_by',
        'approved_by_rhu_at',
        // 1. Rejection Feedback Loop — correction & resubmission state
        'revision_count',
        'rejected_by_id',
        'rejected_at',
        'rejection_reason',
        'resubmitted_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'printed_at' => 'datetime',
        'submitted_to_president_at' => 'datetime',
        'approved_by_president_at' => 'datetime',
        'submitted_to_midwife_at' => 'datetime',
        'approved_by_midwife_at' => 'datetime',
        'approved_by_rhu_at' => 'datetime',
        'rejected_at' => 'datetime',
        'resubmitted_at' => 'datetime',
    ];

    // ── 1. Rejection Feedback Loop helpers ─────────────────────────────
    public function scopeNeedsRevision($query)
    {
        return $query->whereIn('submission_status', ['needs_revision', 'rejected']);
    }

    public function getIsNeedsRevisionAttribute(): bool
    {
        return in_array($this->submission_status, ['needs_revision', 'rejected'], true);
    }

    public function getReviewerNoteAttribute(): ?string
    {
        return $this->rejection_reason ?? $this->midwife_notes ?? $this->president_notes;
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by_id');
    }

    /** BHW fixes the flagged items and puts the report back in queue. */
    public function resubmit(int $submitterId): void
    {
        $this->update([
            'submission_status' => 'submitted_to_president',
            'submitted_to_president_by' => $submitterId,
            'submitted_to_president_at' => now(),
            'resubmitted_at' => now(),
        ]);
    }

    public function bhw()
    {
        return $this->belongsTo(User::class, 'bhw_id');
    }

    public function submittedToPresidentBy()
    {
        return $this->belongsTo(User::class, 'submitted_to_president_by');
    }

    public function approvedByPresident()
    {
        return $this->belongsTo(User::class, 'approved_by_president');
    }

    public function submittedToMidwifeBy()
    {
        return $this->belongsTo(User::class, 'submitted_to_midwife_by');
    }

    public function approvedByMidwife()
    {
        return $this->belongsTo(User::class, 'approved_by_midwife');
    }

    public function approvedByRhu()
    {
        return $this->belongsTo(User::class, 'approved_by_rhu_by');
    }

    public function submitToPresident($userId)
    {
        $this->update([
            'submission_status' => 'submitted_to_president',
            'submitted_to_president_by' => $userId,
            'submitted_to_president_at' => now(),
        ]);
    }

    public function approveByPresident($userId, $notes = null)
    {
        $this->update([
            'submission_status' => 'approved_by_president',
            'approved_by_president' => $userId,
            'approved_by_president_at' => now(),
            'president_notes' => $notes,
        ]);
    }

    public function rejectByPresident($userId, $notes)
    {
        $this->update([
            'submission_status' => 'needs_revision',
            'approved_by_president' => $userId,
            'approved_by_president_at' => now(),
            'president_notes' => $notes,
            'rejected_by_id' => $userId,
            'rejected_at' => now(),
            'rejection_reason' => $notes,
            'revision_count' => ((int) ($this->revision_count ?? 0)) + 1,
        ]);
    }

    public function submitToMidwife($userId)
    {
        $this->update([
            'submission_status' => 'submitted_to_midwife',
            'submitted_to_midwife_by' => $userId,
            'submitted_to_midwife_at' => now(),
        ]);
    }

    public function approveByMidwife($userId, $notes = null)
    {
        $this->update([
            'submission_status' => 'approved_by_midwife',
            'approved_by_midwife' => $userId,
            'approved_by_midwife_at' => now(),
            'midwife_notes' => $notes,
        ]);
    }

    public function rejectByMidwife($userId, $notes)
    {
        $this->update([
            'submission_status' => 'needs_revision',
            'approved_by_midwife' => $userId,
            'approved_by_midwife_at' => now(),
            'midwife_notes' => $notes,
            'rejected_by_id' => $userId,
            'rejected_at' => now(),
            'rejection_reason' => $notes,
            'revision_count' => ((int) ($this->revision_count ?? 0)) + 1,
        ]);
    }

    /**
     * Midwife sends the report back to the BHW President for re-check
     * (instead of bouncing it all the way to the BHW). The president can
     * then forward it to the midwife again or send it back to the BHW.
     */
    public function returnToPresident($midwifeId, $notes)
    {
        $this->update([
            'submission_status' => 'returned_to_president',
            'rejected_by_id' => $midwifeId,
            'rejected_at' => now(),
            'rejection_reason' => $notes,
            'midwife_notes' => $notes,
        ]);
    }

    /** President re-forwards a returned report to the midwife queue. */
    public function resubmitToMidwife($presidentId)
    {
        $this->update([
            'submission_status' => 'submitted_to_midwife',
            'approved_by_president' => $presidentId,
            'approved_by_president_at' => now(),
            'submitted_to_midwife_by' => $presidentId,
            'submitted_to_midwife_at' => now(),
        ]);
    }

    /**
     * RHU final validation. Terminal state — the report is now ready
     * for the CHO.
     */
    public function approveByRhu($userId, $notes = null)
    {
        $this->update([
            'submission_status' => 'approved_by_rhu',
            'approved_by_rhu_by' => $userId,
            'approved_by_rhu_at' => now(),
        ]);
    }

    public function getReportPeriodAttribute()
    {
        return now()
            ->setMonth((int) $this->report_month)
            ->setYear((int) $this->report_year)
            ->startOfMonth()
            ->format('F Y');
    }
}
