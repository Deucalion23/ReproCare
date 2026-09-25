<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuplicateReview extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_LINKED = 'linked';
    public const STATUS_DISMISSED = 'dismissed';

    protected $fillable = [
        'user_id',
        'walk_in_patient_id',
        'matched_user_id',
        'matched_walk_in_patient_id',
        'match_type',
        'score',
        'status',
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

    public function matchedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_user_id');
    }

    public function matchedWalkInPatient(): BelongsTo
    {
        return $this->belongsTo(WalkInPatient::class, 'matched_walk_in_patient_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
