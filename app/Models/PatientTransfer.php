<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientTransfer extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'walk_in_patient_id',
        'from_bhw_id',
        'to_bhw_id',
        'from_purok_id',
        'to_purok_id',
        'from_barangay',
        'to_barangay',
        'reason',
        'status',
        'requested_by_id',
        'reviewed_by_id',
        'reviewer_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function walkInPatient(): BelongsTo
    {
        return $this->belongsTo(WalkInPatient::class, 'walk_in_patient_id');
    }

    public function fromBhw(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_bhw_id');
    }

    public function toBhw(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_bhw_id');
    }

    public function fromPurok(): BelongsTo
    {
        return $this->belongsTo(Purok::class, 'from_purok_id');
    }

    public function toPurok(): BelongsTo
    {
        return $this->belongsTo(Purok::class, 'to_purok_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function getPatientNameAttribute(): string
    {
        return $this->patient?->name
            ?? $this->walkInPatient?->full_name
            ?? 'Unknown patient';
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
