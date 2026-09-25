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
}
