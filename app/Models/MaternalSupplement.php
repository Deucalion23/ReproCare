<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaternalSupplement extends Model
{
    use HasFactory;

    protected $fillable = [
        'maternal_care_target_client_id',
        'supplement_type',
        'visit_number',
        'distribution_date',
        'tablets_given',
    ];

    protected $casts = [
        'distribution_date' => 'date',
        'visit_number' => 'integer',
        'tablets_given' => 'integer',
    ];

    public function maternalCareTargetClient(): BelongsTo
    {
        return $this->belongsTo(MaternalCareTargetClient::class);
    }

    public function scopeIronFolic($query)
    {
        return $query->where('supplement_type', 'iron_folic');
    }

    public function scopeCalcium($query)
    {
        return $query->where('supplement_type', 'calcium');
    }

    public function scopeIodine($query)
    {
        return $query->where('supplement_type', 'iodine');
    }
}
