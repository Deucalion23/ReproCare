<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Woman extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('woman', function ($query) {
            $query->where('role', 'user');
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
    ];

    /**
     * Get the checkups for the woman.
     */
    public function checkups()
    {
        return $this->hasMany(Checkup::class, 'user_id');
    }

    public function purok()
    {
        return $this->belongsTo(Purok::class);
    }

    /**
     * Get the health records for the woman.
     */
    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class, 'user_id');
    }

    /**
     * Get the pregnancies for the woman.
     */
    public function pregnancies()
    {
        return $this->hasMany(Pregnancy::class, 'user_id');
    }

    public function maternalCareTargetClient()
    {
        return $this->hasOne(MaternalCareTargetClient::class, 'user_id');
    }

    /**
     * Get the child records for the woman.
     */
    public function childRecords()
    {
        return $this->hasMany(ChildRecord::class, 'mother_id');
    }

    /**
     * Get the menstruation records for the woman.
     */

    /**
     * Get the cycles for the woman.
     */
    public function cycles()
    {
        return $this->hasMany(Cycle::class, 'user_id');
    }

    /**
     * Get the fertility logs for the woman.
     */

    /**
     * Get the menstruation dailies for the woman.
     */
    public function menstruationDailies()
    {
        return $this->hasMany(MenstruationDaily::class, 'user_id');
    }

    /**
     * Get the preventive interventions for the woman.
     */
    public function preventiveInterventions()
    {
        return $this->hasMany(PreventiveIntervention::class, 'user_id');
    }

    /**
     * Get the notifications for the woman.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Get the forum posts created by the woman.
     */
    public function forumPosts()
    {
        return $this->hasMany(ForumPost::class, 'user_id');
    }

    /**
     * Get the forum comments created by the woman.
     */
    public function forumComments()
    {
        return $this->hasMany(ForumComment::class, 'user_id');
    }

    /**
     * Get the forum likes created by the woman.
     */
    public function forumLikes()
    {
        return $this->hasMany(ForumLike::class, 'user_id');
    }

    /**
     * Get sent messages by the woman.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get received messages by the woman.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Alias for backward compatibility (user -> woman)
     */
    public function user()
    {
        return $this;
    }

    public function isWoman()
    {
        return true;
    }

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

    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . ($this->middle_initial ? $this->middle_initial . '. ' : '') . $this->last_name);
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

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

    public function getPregnancyStatusAttribute(): string
    {
        if ($this->pregnancies()->whereNull('ended_at')->exists()) {
            return 'Pregnant';
        }

        if ($this->pregnancies()->exists()) {
            return 'Postpartum';
        }

        return 'Not Pregnant';
    }

    public function getLastCheckupAttribute()
    {
        return $this->checkups()
            ->orderByDesc('scheduled_date')
            ->first();
    }

    public function getNextAppointmentAttribute()
    {
        return $this->checkups()
            ->whereDate('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date')
            ->first();
    }

    public function getAssignedBhwAttribute()
    {
        $latestCheckup = $this->checkups()
            ->with('scheduledByBhw')
            ->whereNotNull('scheduled_by_id')
            ->latest('scheduled_date')
            ->first();

        if ($latestCheckup?->scheduledByBhw) {
            return $latestCheckup->scheduledByBhw;
        }

        $latestHealthRecord = $this->healthRecords()
            ->with('recordedBy')
            ->whereNotNull('recorded_by_id')
            ->latest()
            ->first();

        return $latestHealthRecord?->recordedBy;
    }
}
