<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * BhwPresident — BHW President model with authentication.
 *
 * This model represents BHW Presidents who manage BHW assignments and tasks.
 */
class BhwPresident extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('bhw_president', function ($query) {
            $query->where('role', 'bhw_president');
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
        'term_start' => 'date',
        'term_end' => 'date',
        'password' => 'hashed',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    /**
     * BHW assignments created by this president.
     */
    public function bhwAssignments()
    {
        return $this->hasMany(BhwAssignment::class, 'assigned_by_id');
    }

    /**
     * Tasks assigned by this president.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_by_id');
    }

    /**
     * Checkups supervised by this president.
     */
    public function checkups()
    {
        return $this->hasMany(Checkup::class, 'bhw_president_id');
    }

    /**
     * Health records supervised by this president.
     */
    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class, 'bhw_president_id');
    }

    // ── Helper Methods ───────────────────────────────────────────────────────

    public function isBhwPresident()
    {
        return true;
    }

    public function isBhw()
    {
        return false;
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

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0d6efd&color=fff&size=200';
    }

    public function hasProfileImage()
    {
        return !empty($this->profile_image);
    }
}
