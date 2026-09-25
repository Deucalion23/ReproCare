<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Patient — Full model for patients table with authentication.
 *
 * This model replaces the old User model for patients.
 * It contains both authentication fields and patient-specific health data.
 */
class Patient extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('patient', function ($query) {
            $query->where('role', 'user');
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
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'medical_history' => 'array',
        'date_of_birth' => 'date',
        'password' => 'hashed',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    /**
     * Checkups for this patient.
     */
    public function checkups()
    {
        return $this->hasMany(Checkup::class, 'user_id');
    }

    /**
     * Health records for this patient.
     * Note: This model is deprecated, use User model instead.
     */
    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class, 'user_id');
    }

    /**
     * Pregnancies for this patient.
     */
    public function pregnancies()
    {
        return $this->hasMany(Pregnancy::class, 'user_id');
    }

    /**
     * Notifications for this patient.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Forum posts by this patient.
     */
    public function forumPosts()
    {
        return $this->morphMany(ForumPost::class, 'user');
    }

    /**
     * Forum comments by this patient.
     */
    public function forumComments()
    {
        return $this->morphMany(ForumComment::class, 'user');
    }

    /**
     * Forum likes by this patient.
     */
    public function forumLikes()
    {
        return $this->hasMany(ForumLike::class, 'user_id');
    }

    /**
     * Menstrual cycles for this patient.
     */
    public function cycles()
    {
        return $this->morphMany(Cycle::class, 'patient');
    }

    /**
     * Menstruation records for this patient.
     */

    /**
     * Menstruation daily logs for this patient.
     */
    public function menstruationDailies()
    {
        return $this->morphMany(MenstruationDaily::class, 'patient');
    }

    /**
     * Fertility logs for this patient.
     */

    /**
     * Preventive interventions for this patient.
     */
    public function preventiveInterventions()
    {
        return $this->morphMany(PreventiveIntervention::class, 'patient');
    }

    /**
     * Sent messages.
     */
    public function sentMessages()
    {
        return $this->morphMany(Message::class, 'sender');
    }

    /**
     * Received messages.
     */
    public function receivedMessages()
    {
        return $this->morphMany(Message::class, 'receiver');
    }

    // ── Helper Methods ───────────────────────────────────────────────────────

    public function isPatient()
    {
        return true;
    }

    public function isMidwife()
    {
        return false;
    }

    public function isBhw()
    {
        return false;
    }

    public function getAgeAttribute()
    {
        if (!$this->date_of_birth) {
            return 'N/A';
        }
        return \Carbon\Carbon::parse($this->date_of_birth)->age;
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
