<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildNutritionTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_care_target_client_id',
        'month_range',
        'exclusive_breastfeeding',
    ];

    protected $casts = [
        'exclusive_breastfeeding' => 'boolean',
    ];

    public function childCareTargetClient(): BelongsTo
    {
        return $this->belongsTo(ChildCareTargetClient::class);
    }
}
