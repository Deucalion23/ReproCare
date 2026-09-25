<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalkInPatient extends Model
{
    use SoftDeletes;

    /**
     * Field-registry terminology:
     * - Enrolled Account (Portal-Active / Direct Access / Authenticated Patient):
     *   linked users row, has_portal_access = true.
     * - Unlinked Profile (BHW-Managed / Field Record Only / Managed Beneficiary):
     *   user_id = NULL, has_portal_access = false.
     */
    public const TYPE_ENROLLED = 'enrolled';
    public const TYPE_UNLINKED = 'unlinked';

    protected $fillable = [
        'recorded_by_id',
        'user_id',
        'has_portal_access',
        'first_name',
        'middle_initial',
        'last_name',
        'date_of_birth',
        'address',
        'barangay',
        'purok_id',
        'contact_number',
        'reason_for_visit',
        'notes',
        'converted_to_user_id',
        'converted_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'converted_at' => 'datetime',
        'has_portal_access' => 'boolean',
    ];

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function purok(): BelongsTo
    {
        return $this->belongsTo(Purok::class, 'purok_id');
    }

    public function convertedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_to_user_id');
    }

    /**
     * Portal account link. NULL for BHW-Managed (unlinked) field records;
     * set to the new users.id on account activation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Effective linked user id (prefers user_id, falls back to legacy converted_to_user_id). */
    public function linkedUserId(): ?int
    {
        return $this->user_id ?? $this->converted_to_user_id;
    }

    public function isPortalActive(): bool
    {
        return (bool) $this->has_portal_access || $this->linkedUserId() !== null;
    }

    /** UI label pair: Enrolled Account (Portal-Active) vs Unlinked Profile (BHW-Managed). */
    public function portalStatusLabel(): string
    {
        return $this->isPortalActive() ? 'Enrolled Account · Portal-Active' : 'Unlinked Profile · BHW-Managed';
    }

    public function checkupReferrals(): HasMany
    {
        return $this->hasMany(CheckupReferral::class, 'walk_in_patient_id');
    }

    public function pregnancies(): HasMany
    {
        return $this->hasMany(Pregnancy::class, 'walk_in_patient_id');
    }

    public function healthRecords(): HasMany
    {
        return $this->hasMany(HealthRecord::class, 'walk_in_patient_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_initial} {$this->last_name}");
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        return $this->date_of_birth->age;
    }

    public function isTeenage(): bool
    {
        return $this->date_of_birth && $this->date_of_birth->age < 19;
    }

    public function hasSmsEnabled(): bool
    {
        return !empty($this->contact_number);
    }

    public function smsPhone(): ?string
    {
        if (empty($this->contact_number)) {
            return null;
        }
        $digits = preg_replace('/\D/', '', (string) $this->contact_number);
        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            return $digits;
        }
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '63'.substr($digits, 1);
        }
        if (str_starts_with($digits, '9') && strlen($digits) === 10) {
            return '63'.$digits;
        }
        return $digits ?: null;
    }

    public static function phoneRule(): string
    {
        return 'nullable|string|max:20|regex:/^(\+?63|0)?9\d{9}$/';
    }

    public function scopeNotConverted($query)
    {
        return $query->whereNull('converted_to_user_id');
    }

    public function scopeConverted($query)
    {
        return $query->whereNotNull('converted_to_user_id');
    }
}
