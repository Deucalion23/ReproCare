<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyPlanningRecord extends Model
{
    protected $fillable = [
        'user_id', 'recorded_by_id', 'preferred_method', 'method_given',
        'pregnancy_intention', 'counseling_date', 'start_date', 'stop_date',
        'next_follow_up', 'side_effects', 'notes', 'informed_choice_confirmed', 'status',
    ];

    protected $casts = [
        'counseling_date' => 'date', 'start_date' => 'date', 'stop_date' => 'date',
        'next_follow_up' => 'date', 'informed_choice_confirmed' => 'boolean',
    ];

    public function client() { return $this->belongsTo(User::class, 'user_id'); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by_id'); }
}
