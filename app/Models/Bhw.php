<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Bhw — Full model for bhws table with authentication.
 *
 * This model replaces the old User model for BHWs.
 * It contains both authentication fields and BHW-specific professional data.
 */
class Bhw extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('bhw', function ($query) {
            $query->where('role', 'bhw');
        });
    }

    protected $fillable = [
        'first_name',
        'middle_initial',
        'last_name',
        'date_of_birth',
        'gender',
        'contact_number',
        'address',
        'barangay',
        'purok_id',
        'assigned_barangay',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'certification_date' => 'date',
        'date_of_birth' => 'date',
        'password' => 'hashed',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    /**
     * Checkups scheduled by this BHW
     */
    public function scheduledCheckups()
    {
        return $this->hasMany(Checkup::class, 'scheduled_by_id');
    }

    /**
     * Health records this BHW recorded.
     */
    public function recordedHealthRecords()
    {
        return $this->hasMany(HealthRecord::class, 'recorded_by_id');
    }

    /**
     * Notifications for this BHW.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Forum posts by this BHW.
     */
    public function forumPosts()
    {
        return $this->hasMany(ForumPost::class, 'bhw_id');
    }

    /**
     * Forum comments by this BHW.
     */
    public function forumComments()
    {
        return $this->hasMany(ForumComment::class, 'bhw_id');
    }

    /**
     * Forum likes by this BHW.
     */
    public function forumLikes()
    {
        return $this->hasMany(ForumLike::class, 'user_id');
    }

    /**
     * Sent messages.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Received messages.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // ── Helper Methods ───────────────────────────────────────────────────────

    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . ($this->middle_initial ? $this->middle_initial . '. ' : '') . $this->last_name);
    }

    public function isBhw()
    {
        return true;
    }

    public function isMidwife()
    {
        return false;
    }

    public function isPatient()
    {
        return false;
    }

    // ── Profile Image Handling ─────────────────────────────────────────────────

    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');
            $filename = basename($this->profile_image);

            if ($publicDisk->exists($this->profile_image)) {
                return '/storage/' . ltrim($this->profile_image, '/');
            }

            foreach (['uploads/profile/', 'profile/'] as $directory) {
                $path = $directory . $filename;
                if ($publicDisk->exists($path)) {
                    return '/storage/' . $path;
                }
            }
        }

        // Default avatar if no profile image
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0d6efd&color=fff&size=200';
    }

    public function hasProfileImage()
    {
        return !empty($this->profile_image);
    }
}
