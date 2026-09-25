<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'body',
        'is_read',
        'read_at',
        'reply_to_id',
        'is_sent',
        'scheduled_at',
        'client_uuid',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'is_sent' => 'boolean',
        'scheduled_at' => 'datetime',
    ];

    // Relationships
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeInbox($query, int $userId)
    {
        return $query->where('receiver_id', $userId);
    }

    public function scopeThread($query)
    {
        return $query->whereNull('reply_to_id');
    }

    // Mark as read
    public function markRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    public function getSenderRoleAttribute(): ?string
    {
        return $this->sender?->role === 'user' ? 'woman' : $this->sender?->role;
    }

    public function getReceiverRoleAttribute(): ?string
    {
        return $this->receiver?->role === 'user' ? 'woman' : $this->receiver?->role;
    }
}
