<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newborn extends Model
{
    use HasFactory;

    protected $fillable = [
        'mother_id',
        'pregnancy_id',
        'name',
        'sex',
        'birth_date',
        'birth_weight_kg',
        'feeding_type',
        'danger_signs',
        'notes',
        'recorded_by_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'birth_weight_kg' => 'decimal:2',
    ];

    /** Standard newborn immunization schedule (weeks after birth). */
    public const IMMUNIZATION_SCHEDULE = [
        ['vaccine' => 'BCG', 'week' => 0],
        ['vaccine' => 'Hepatitis B (Birth Dose)', 'week' => 0],
        ['vaccine' => 'OPV 0', 'week' => 0],
        ['vaccine' => 'Pentavalent 1 + OPV 1 + PCV 1', 'week' => 6],
        ['vaccine' => 'Pentavalent 2 + OPV 2 + PCV 2', 'week' => 10],
        ['vaccine' => 'Pentavalent 3 + OPV 3 + PCV 3', 'week' => 14],
        ['vaccine' => 'Measles (MCV 1)', 'week' => 39],
    ];

    public function mother()
    {
        return $this->belongsTo(User::class, 'mother_id');
    }

    public function pregnancy()
    {
        return $this->belongsTo(Pregnancy::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function postpartumVisits()
    {
        return $this->hasMany(PostpartumVisit::class)->orderByDesc('visit_date');
    }

    public function immunizations()
    {
        return $this->hasMany(NewbornImmunization::class)->orderBy('scheduled_date');
    }

    public function getHasDangerSignsAttribute(): bool
    {
        return !empty(trim((string) $this->danger_signs));
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: 'Baby of ' . ($this->mother?->name ?? 'mother #' . $this->mother_id);
    }

    /** Seed the standard immunization schedule rows for this newborn. */
    public function seedImmunizationSchedule(): void
    {
        foreach (self::IMMUNIZATION_SCHEDULE as $item) {
            $this->immunizations()->firstOrCreate(
                ['vaccine' => $item['vaccine']],
                ['scheduled_date' => $this->birth_date->copy()->addWeeks($item['week'])]
            );
        }
    }
}
