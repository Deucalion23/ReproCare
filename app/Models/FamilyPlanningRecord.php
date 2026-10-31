<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyPlanningRecord extends Model
{
    protected $fillable = [
        'user_id', 'pregnancy_id', 'is_postpartum_plan', 'postpartum_start_timing', 'recorded_by_id', 'preferred_method', 'method_given',
        'pregnancy_intention', 'counseling_date', 'start_date', 'stop_date',
        'next_follow_up', 'side_effects', 'notes', 'informed_choice_confirmed', 'status',
    ];

    protected $casts = [
        'counseling_date' => 'date', 'start_date' => 'date', 'stop_date' => 'date',
        'next_follow_up' => 'date', 'informed_choice_confirmed' => 'boolean', 'is_postpartum_plan' => 'boolean',
    ];

    public function client() { return $this->belongsTo(User::class, 'user_id'); }
    public function pregnancy() { return $this->belongsTo(Pregnancy::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by_id'); }

    /**
     * Display label for a stored method code. Plain ucfirst would render the
     * "iud" code as "Iud" and "natural_method" as "Natural method".
     */
    public static function methodLabel(?string $method): string
    {
        return match ($method) {
            null, '' => 'Not stated',
            'iud' => 'IUD',
            'natural_method' => 'Natural family planning',
            default => ucfirst(str_replace('_', ' ', $method)),
        };
    }
}
