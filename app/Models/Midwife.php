<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Midwife — Full model for midwives table with authentication.
 *
 * This model replaces the old User model for midwives.
 * It contains both authentication fields and midwife-specific professional data.
 */
class Midwife extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('midwife', function ($query) {
            $query->where('role', 'midwife');
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
        'assigned_barangays' => 'array',
        'license_expiry' => 'date',
        'date_of_birth' => 'date',
        'password' => 'hashed',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    /**
     * Checkups where this midwife was assigned.
     */
    public function assignedCheckups()
    {
        return $this->hasMany(Checkup::class, 'midwife_id');
    }

    /**
     * Checkups scheduled by this midwife.
     */
    public function scheduledCheckups()
    {
        return $this->hasMany(Checkup::class, 'scheduled_by_id');
    }

    /**
     * Health records this midwife recorded.
     */
    public function recordedHealthRecords()
    {
        return $this->hasMany(HealthRecord::class, 'recorded_by_id');
    }

    /**
     * Notifications for this midwife.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Forum posts by this midwife.
     */
    public function forumPosts()
    {
        return $this->hasMany(ForumPost::class, 'midwife_id');
    }

    /**
     * Forum comments by this midwife.
     */
    public function forumComments()
    {
        return $this->hasMany(ForumComment::class, 'midwife_id');
    }

    /**
     * Forum likes by this midwife.
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

    public function isMidwife()
    {
        return true;
    }

    public function isBhw()
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
