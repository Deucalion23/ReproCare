<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildVaccination extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_care_target_client_id',
        'vaccine_type',
        'dose_number',
        'vaccination_date',
    ];

    protected $casts = [
        'vaccination_date' => 'date',
        'dose_number' => 'integer',
    ];

    public function childCareTargetClient(): BelongsTo
    {
        return $this->belongsTo(ChildCareTargetClient::class);
    }

    public function scopeType($query, $type)
    {
        return $query->where('vaccine_type', $type);
    }
}
