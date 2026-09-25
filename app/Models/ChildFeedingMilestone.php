<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildFeedingMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_care_target_client_id',
        'exclusive_breastfed_up_to_6_months',
        'complementary_feeding_introduced',
        'breastfeeding_initiated_date',
    ];

    protected $casts = [
        'exclusive_breastfed_up_to_6_months' => 'boolean',
        'complementary_feeding_introduced' => 'boolean',
        'breastfeeding_initiated_date' => 'date',
    ];

    public function childCareTargetClient(): BelongsTo
    {
        return $this->belongsTo(ChildCareTargetClient::class);
    }
}
