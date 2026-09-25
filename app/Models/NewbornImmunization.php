<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewbornImmunization extends Model
{
    use HasFactory;

    protected $fillable = [
        'newborn_id',
        'vaccine',
        'scheduled_date',
        'given_date',
        'status',
        'recorded_by_id',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'given_date' => 'date',
    ];

    public function newborn()
    {
        return $this->belongsTo(Newborn::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_date->isPast();
    }
}
