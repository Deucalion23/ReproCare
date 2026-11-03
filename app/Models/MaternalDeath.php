<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaternalDeath extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'walk_in_patient_id',
        'pregnancy_id',
        'recorded_by_id',
        'reviewed_by_id',
        'purok_id',
        'barangay',
        'death_date',
        'death_time',
        'age_at_death',
        'place_of_death',
        'cause_of_death',
        'cause_category',
        'death_timing',
        'notes',
        'audit_status',
        'audit_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'death_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function walkInPatient()
    {
        return $this->belongsTo(WalkInPatient::class, 'walk_in_patient_id');
    }

    public function pregnancy()
    {
        return $this->belongsTo(Pregnancy::class, 'pregnancy_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function purok()
    {
        return $this->belongsTo(Purok::class, 'purok_id');
    }

    // Computed Name attribute for list displays
    public function getPatientNameAttribute()
    {
        if ($this->user) {
            return $this->user->name;
        }
        if ($this->walkInPatient) {
            return $this->walkInPatient->name;
        }
        return 'Unknown';
    }

    public static function placeLabels(): array
    {
        return [
            'home' => 'Home',
            'barangay_health_station' => 'Barangay Health Station',
            'rhu' => 'RHU',
            'city_hospital' => 'City Hospital',
            'provincial_hospital' => 'Provincial Hospital',
            'private_hospital' => 'Private Hospital',
            'in_transit' => 'In Transit',
            'other' => 'Other',
        ];
    }

    public static function causeLabels(): array
    {
        return [
            'hemorrhage' => 'Obstetric Hemorrhage',
            'hypertension_eclampsia' => 'Hypertension / Eclampsia',
            'sepsis' => 'Sepsis / Infection',
            'obstructed_labor' => 'Obstructed / Prolonged Labor',
            'unsafe_abortion' => 'Complications of Abortion',
            'embolism' => 'Embolism',
            'other_direct' => 'Other Direct Cause',
            'indirect_cause' => 'Indirect Cause',
            'unknown' => 'Undetermined',
        ];
    }

    public static function timingLabels(): array
    {
        return [
            'during_pregnancy' => 'During Pregnancy',
            'during_delivery' => 'During Delivery',
            'within_24_hours_postpartum' => 'Within 24 Hours Postpartum',
            'within_7_days_postpartum' => 'Within 7 Days Postpartum',
            'within_42_days_postpartum' => 'Within 42 Days Postpartum',
            'unknown' => 'Undetermined',
        ];
    }

    public static function placeLabel(?string $place): string
    {
        return static::placeLabels()[$place] ?? ucwords(str_replace('_', ' ', (string) $place));
    }

    public static function causeLabel(?string $cause): string
    {
        return static::causeLabels()[$cause] ?? ucwords(str_replace('_', ' ', (string) $cause));
    }

    public static function timingLabel(?string $timing): string
    {
        return static::timingLabels()[$timing] ?? ucwords(str_replace('_', ' ', (string) $timing));
    }

    public function getPlaceOfDeathLabelAttribute(): string
    {
        return static::placeLabel($this->place_of_death);
    }

    public function getCauseCategoryLabelAttribute(): string
    {
        return static::causeLabel($this->cause_category);
    }

    public function getDeathTimingLabelAttribute(): string
    {
        return static::timingLabel($this->death_timing);
    }
}
