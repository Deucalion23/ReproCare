<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'action_url',
        'is_read',
        'read_at',
        'category',
        'event_key',
        'risk_fingerprint',
        'subject_user_id',
        'checkup_id',
        'parent_notification_id',
        'last_reminded_at',
        'resolved_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'last_reminded_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    // Methods
    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at ??= now();
        $this->save();
    }

    public function patientAlert()
    {
        return $this->belongsTo(self::class, 'parent_notification_id')->withTrashed();
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    public function markAsUnread()
    {
        $this->is_read = false;
        $this->read_at = null;
        $this->save();
    }

    // Static method for creating notifications
    public static function createNotification(
        int $userId,
        string $message,
        string $title = '',
        string $type = 'info',
        ?string $actionUrl = null,
        ?string $userRole = null
    )
    {
        return self::create([
            'user_id'    => $userId,
            'title'      => $title ?: $message,
            'message'    => $message,
            'type'       => $type,
            'action_url' => $actionUrl,
            'is_read'    => false,
        ]);
    }
}
