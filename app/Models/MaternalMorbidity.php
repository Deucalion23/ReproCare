<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaternalMorbidity extends Model
{
    use SoftDeletes;

    protected $table = 'maternal_morbidities';

    protected $fillable = [
        'user_id',
        'walk_in_patient_id',
        'pregnancy_id',
        'recorded_by_id',
        'reviewed_by_id',
        'purok_id',
        'barangay',
        'event_date',
        'event_time',
        'complication_type',
        'place_of_event',
        'outcome',
        'maternal_death_id',
        'description',
        'interventions_done',
        'notes',
        'review_status',
        'review_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'event_date' => 'date',
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

    public function maternalDeath()
    {
        return $this->belongsTo(MaternalDeath::class, 'maternal_death_id');
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
