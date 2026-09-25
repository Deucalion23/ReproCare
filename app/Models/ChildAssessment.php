<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_care_target_client_id',
        'age_group',
        'age_months',
        'length_cm',
        'length_date',
        'weight_kg',
        'weight_date',
        'status',
    ];

    protected $casts = [
        'length_cm' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'length_date' => 'date',
        'weight_date' => 'date',
    ];

    public function childCareTargetClient(): BelongsTo
    {
        return $this->belongsTo(ChildCareTargetClient::class);
    }

    public function scopeAgeGroup($query, $group)
    {
        return $query->where('age_group', $group);
    }
}
