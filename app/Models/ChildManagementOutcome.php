<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildManagementOutcome extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_care_target_client_id',
        'program_type',
        'admitted',
        'cured',
        'defaulted',
        'died',
    ];

    protected $casts = [
        'admitted' => 'boolean',
        'cured' => 'boolean',
        'defaulted' => 'boolean',
        'died' => 'boolean',
    ];

    public function childCareTargetClient(): BelongsTo
    {
        return $this->belongsTo(ChildCareTargetClient::class);
    }

    public function scopeProgramType($query, $type)
    {
        return $query->where('program_type', $type);
    }
}
