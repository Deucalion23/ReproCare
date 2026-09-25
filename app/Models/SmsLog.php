<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'message',
        'type',
        'status',
        'error_message',
        'provider_sid',
        'sent_at',
        'notification_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'appointment_reminder' => '📅',
            'high_risk_alert'      => '🚨',
            'missed_checkup'       => '⛔',
            'broadcast'            => '📢',
            default                => '💬',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'sent'    => '<span class="badge bg-success">Sent</span>',
            'failed'  => '<span class="badge bg-danger">Failed</span>',
            default   => '<span class="badge bg-warning text-dark">Pending</span>',
        };
    }
}
