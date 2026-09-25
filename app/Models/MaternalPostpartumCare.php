<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaternalPostpartumCare extends Model
{
    use HasFactory;

    protected $fillable = [
        'maternal_care_target_client_id',
        'iron_month',
        'iron_given',
        'vitamin_a_date',
        'remarks',
    ];

    protected $casts = [
        'vitamin_a_date' => 'date',
    ];

    public function maternalCareTargetClient(): BelongsTo
    {
        return $this->belongsTo(MaternalCareTargetClient::class);
    }
}
