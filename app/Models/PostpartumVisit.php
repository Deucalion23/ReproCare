<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostpartumVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pregnancy_id',
        'newborn_id',
        'visit_date',
        'visit_week',
        'bp',
        'temperature',
        'bleeding',
        'infection_signs',
        'depression_score',
        'breastfeeding',
        'notes',
        'recorded_by_id',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'temperature' => 'decimal:1',
    ];

    public function mother()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pregnancy()
    {
        return $this->belongsTo(Pregnancy::class);
    }

    public function newborn()
    {
        return $this->belongsTo(Newborn::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    /**
     * Reasons this visit needs clinical follow-up, keyed by the metric name so
     * views can highlight exactly the offending chip. Empty means routine.
     */
    public function dangerSignals(): array
    {
        $systolic = $this->parseBp()[0];

        return array_filter([
            'bleeding' => $this->bleeding === 'heavy' ? 'Heavy bleeding' : null,
            'infection' => ! empty(trim((string) $this->infection_signs)) ? 'Infection signs' : null,
            'bp' => ($systolic !== null && $systolic >= 140) ? "BP {$this->bp}" : null,
            'temperature' => ($this->temperature !== null && (float) $this->temperature >= 38.0) ? "{$this->temperature}°C" : null,
            'mood' => ($this->depression_score !== null && (int) $this->depression_score >= 6) ? "PPD score {$this->depression_score}" : null,
        ]);
    }

    /** Flag visits needing clinical follow-up. */
    public function getHasDangerSignsAttribute(): bool
    {
        return $this->dangerSignals() !== [];
    }

    protected function parseBp(): array
    {
        $parts = explode('/', (string) $this->bp);
        if (count($parts) === 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]))) {
            return [(int) trim($parts[0]), (int) trim($parts[1])];
        }
        return [null, null];
    }
}
