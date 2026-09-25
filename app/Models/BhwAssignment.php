<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BhwAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bhw_id',
        'purok_id',
        'assigned_by_id',
        'assigned_at',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function bhw()
    {
        return $this->belongsTo(User::class, 'bhw_id');
    }

    public function purok()
    {
        return $this->belongsTo(Purok::class, 'purok_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
