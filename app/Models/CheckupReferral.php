<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckupReferral extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'referred_by_bhw_id',
        'user_id',
        'walk_in_patient_id',
        'pregnancy_id',
        'health_record_ids',
        'assigned_midwife_id',
        'converted_checkup_id',
        'reason',
        'urgency',
        'bhw_notes',
        'midwife_notes',
        'status',
        'reviewed_at',
        'accepted_at',
        'scheduled_at',
        'completed_at',
    ];

    protected $casts = [
        'health_record_ids' => 'array',
        'reviewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function referredByBhw(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_bhw_id');
    }

    public function woman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function walkInPatient(): BelongsTo
    {
        return $this->belongsTo(WalkInPatient::class, 'walk_in_patient_id');
    }

    public function assignedMidwife(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_midwife_id');
    }

    public function convertedCheckup(): BelongsTo
    {
        return $this->belongsTo(Checkup::class, 'converted_checkup_id');
    }

    public function pregnancy(): BelongsTo
    {
        return $this->belongsTo(Pregnancy::class, 'pregnancy_id');
    }

    /**
     * Health records the BHW explicitly attached to this referral.
     * Stored as an id list so the midwife always sees the exact snapshot
     * the BHW reported, even if newer records are added later.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, HealthRecord>
     */
    public function attachedHealthRecords()
    {
        $ids = array_values(array_filter(array_map('intval', (array) ($this->health_record_ids ?? []))));

        if ($ids === []) {
            return HealthRecord::query()->whereRaw('1 = 0')->get();
        }

        return HealthRecord::query()->whereIn('id', $ids)->orderBy('created_at')->get();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'reviewed']);
    }

    public function getIsTerminalAttribute(): bool
    {
        return in_array($this->status, ['scheduled', 'completed', 'declined'], true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeUrgent($query)
    {
        return $query->whereIn('urgency', ['urgent', 'emergency']);
    }

    public function getPatientNameAttribute(): string
    {
        if ($this->woman) {
            return $this->woman->name;
        }
        if ($this->walkInPatient) {
            return $this->walkInPatient->full_name;
        }
        return 'Unknown';
    }
}
