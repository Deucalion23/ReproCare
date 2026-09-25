<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaternalVaccination extends Model
{
    use HasFactory;

    protected $fillable = [
        'maternal_care_target_client_id',
        'vaccine_type',
        'dose_number',
        'vaccination_date',
    ];

    protected $casts = [
        'vaccination_date' => 'date',
        'dose_number' => 'integer',
    ];

    public function maternalCareTargetClient(): BelongsTo
    {
        return $this->belongsTo(MaternalCareTargetClient::class);
    }

    public function scopeTd($query)
    {
        return $query->where('vaccine_type', 'td');
    }
}
