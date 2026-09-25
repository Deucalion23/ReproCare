<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenstruationDaily extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'is_period',
    ];

    protected $casts = [
        'date' => 'date',
        'is_period' => 'boolean',
    ];

    // Relationships
    public function woman()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Alias for backward compatibility
    public function patient()
    {
        return $this->woman();
    }

    public function user()
    {
        return $this->woman();
    }

    // Accessor for backward compatibility - return array of symptom names
    public function getSymptomsAttribute()
    {
        return [];
    }

    // Available symptoms list
    public static function getSymptomsList()
    {
        return [];
    }

}
