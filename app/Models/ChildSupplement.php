<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildSupplement extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_care_target_client_id',
        'supplement_type',
        'month_number',
        'given_date',
        'quantity',
    ];

    protected $casts = [
        'given_date' => 'date',
        'month_number' => 'integer',
        'quantity' => 'integer',
    ];

    public function childCareTargetClient(): BelongsTo
    {
        return $this->belongsTo(ChildCareTargetClient::class);
    }

    public function scopeType($query, $type)
    {
        return $query->where('supplement_type', $type);
    }
}
