<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyAlert extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';
    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'user_id',
        'walk_in_patient_id',
        'reported_by_id',
        'symptoms',
        'bp',
        'notes',
        'location',
        'barangay',
        'purok_id',
        'referral_id',
        'status',
        'acknowledged_by_id',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function walkInPatient(): BelongsTo
    {
        return $this->belongsTo(WalkInPatient::class, 'walk_in_patient_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(CheckupReferral::class, 'referral_id');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by_id');
    }

    public function purok(): BelongsTo
    {
        return $this->belongsTo(Purok::class, 'purok_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function getPatientNameAttribute(): string
    {
        return $this->patient?->name
            ?? $this->walkInPatient?->full_name
            ?? 'Unknown patient';
    }

    public function acknowledge(int $userId): void
    {
        $this->update([
            'status' => self::STATUS_ACKNOWLEDGED,
            'acknowledged_by_id' => $userId,
            'acknowledged_at' => now(),
        ]);
    }

    public function resolve(): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);
    }
}
