<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaternalScreening extends Model
{
    use HasFactory;

    protected $fillable = [
        'maternal_care_target_client_id',
        'screening_type',
        'screening_date',
        'result',
        'given_iron',
    ];

    protected $casts = [
        'screening_date' => 'date',
        'given_iron' => 'boolean',
    ];

    public function maternalCareTargetClient(): BelongsTo
    {
        return $this->belongsTo(MaternalCareTargetClient::class);
    }

    public function scopeType($query, $type)
    {
        return $query->where('screening_type', $type);
    }
}
