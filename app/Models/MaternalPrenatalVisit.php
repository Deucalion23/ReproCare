<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaternalPrenatalVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'maternal_care_target_client_id',
        'visit_number',
        'trimester',
        'visit_date',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'visit_number' => 'integer',
    ];

    public function maternalCareTargetClient(): BelongsTo
    {
        return $this->belongsTo(MaternalCareTargetClient::class);
    }
}
