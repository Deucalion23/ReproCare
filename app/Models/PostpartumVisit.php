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

    /** Flag visits needing clinical follow-up. */
    public function getHasDangerSignsAttribute(): bool
    {
        [$sys] = $this->parseBp() + [null];

        return $this->bleeding === 'heavy'
            || !empty(trim((string) $this->infection_signs))
            || ($sys !== null && $sys >= 140)
            || ($this->temperature !== null && (float) $this->temperature >= 38.0)
            || ($this->depression_score !== null && (int) $this->depression_score >= 6);
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
