<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveIntervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'risk_level',
        'trigger_reason',
        'recommendations',
        'intervention_type',
        'status',
        'triggered_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function woman()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Alias for backward compatibility
    public function patient()
    {
        return $this->woman();
    }

    public function user()
    {
        return $this->woman();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'triggered');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', 'High');
    }

    // Methods
    public function markAsCompleted(string $notes = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $notes,
        ]);
    }
}
