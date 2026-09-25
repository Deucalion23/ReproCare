<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_initial',
        'last_name',
        'email',
        'password',
        'date_of_birth',
        'gender',
        'contact_number',
        'address',
        'barangay',
        'purok_id',
        'assigned_barangay',
        'license_number',
        'license_expiry',
        'specialization',
        'official_title',
        'employee_id',
        'office_extension',
        'emergency_mobile',
        'station_contact',
        'signature_image',
        'pref_high_risk_email',
        'pref_high_risk_sms',
        'pref_high_risk_dashboard',
        'pref_approval_summary',
        'pref_escalation_alerts',
        'pref_2fa_enabled',
        'pref_mortality_alerts',
        'pref_audit_warnings',
        'pref_compliance_updates',
        'pref_bhw_conflicts',
        'pref_pending_reports',
        'pref_highrisk_escalation',
        'recovery_question_1',
        'recovery_answer_1',
        'recovery_question_2',
        'recovery_answer_2',
        'out_of_office',
        'delegate_to_user_id',
        'ooo_note',
        'pwa_cache_version',
        'catchment_barangays',
        'secondary_email',
        'secondary_contact',
        'pref_registration_email',
        'pref_registration_sms',
        'pref_registration_dashboard',
        'pref_checkup_reminders',
        'pref_report_summary',
        'role',
        'status',
        'profile_image',
        'rejection_reason',
        'archived_at',
        'archived_reason',
        'archived_by',
        'rhu_assignment',
        'cho_office',
        'registered_by_rhu_id',
        'registered_by_cho_id',
        'created_by_bhw_id',
        'created_by_midwife_id',
        'sms_opt_out',
        'partner_name',
        'partner_contact',
        'latitude',
        'longitude',
        'address_label',
        // Step 5 of self-registration: valid ID front/back scan paths
        'id_image_front',
        'id_image_back',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'archived_at'       => 'datetime',
            'password'          => 'hashed',
            'date_of_birth'     => 'date',
            'license_expiry'    => 'date',
            'pref_high_risk_email'     => 'boolean',
            'pref_high_risk_sms'       => 'boolean',
            'pref_high_risk_dashboard' => 'boolean',
            'pref_escalation_alerts'   => 'boolean',
            'pref_2fa_enabled'         => 'boolean',
            'pref_mortality_alerts'    => 'boolean',
            'pref_audit_warnings'      => 'boolean',
            'pref_compliance_updates'  => 'boolean',
            'pref_bhw_conflicts'       => 'boolean',
            'pref_pending_reports'     => 'boolean',
            'pref_highrisk_escalation' => 'boolean',
            'out_of_office'            => 'boolean',
            'catchment_barangays'      => 'array',
            'pref_registration_email'     => 'boolean',
            'pref_registration_sms'       => 'boolean',
            'pref_registration_dashboard' => 'boolean',
            'pref_checkup_reminders'      => 'boolean',
        ];
    }

    // ── Domain Relationships ─────────────────────────────────────────────────

    public function pregnancies()
    {
        return $this->hasMany(Pregnancy::class, 'user_id');
    }

    public function newborns()
    {
        return $this->hasMany(Newborn::class, 'mother_id')->orderByDesc('birth_date');
    }

    public function postpartumVisits()
    {
        return $this->hasMany(PostpartumVisit::class, 'user_id')->orderByDesc('visit_date');
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class, 'user_id')->ordered();
    }

    public function primaryEmergencyContact()
    {
        return $this->hasOne(EmergencyContact::class, 'user_id')->where('contact_order', 1);
    }

    public function secondaryEmergencyContact()
    {
        return $this->hasOne(EmergencyContact::class, 'user_id')->where('contact_order', 2);
    }

    public function tertiaryEmergencyContact()
    {
        return $this->hasOne(EmergencyContact::class, 'user_id')->where('contact_order', 3);
    }

    public function maternalCareTargetClients()
    {
        return $this->hasMany(MaternalCareTargetClient::class, 'user_id');
    }

    // Alias for backward compatibility - returns the most recent record
    public function maternalCareTargetClient()
    {
        return $this->hasOne(MaternalCareTargetClient::class, 'user_id')->latest();
    }

    public function childRecords()
    {
        return $this->hasMany(ChildRecord::class, 'mother_id');
    }

    public function purok()
    {
        return $this->belongsTo(Purok::class);
    }

    public function bhwAssignments()
    {
        return $this->hasMany(BhwAssignment::class, 'bhw_id');
    }

    public function activeBhwAssignment()
    {
        return $this->hasOne(BhwAssignment::class, 'bhw_id')->where('is_active', true)->latestOfMany();
    }

    /**
     * The BHW who registered this woman (users.created_by_bhw_id).
     */
    public function createdByBhw()
    {
        return $this->belongsTo(User::class, 'created_by_bhw_id');
    }

    /**
     * The midwife who registered this woman (users.created_by_midwife_id).
     */
    public function createdByMidwife()
    {
        return $this->belongsTo(User::class, 'created_by_midwife_id');
    }

    public function cycles()
    {
        return $this->hasMany(Cycle::class, 'user_id');
    }


    public function checkups()
    {
        if ($this->role === 'user') {
            return $this->hasMany(Checkup::class, 'user_id');
        }
        return $this->hasMany(Checkup::class, 'user_id')->whereRaw('1=0');
    }

    public function healthRecords()
    {
        if ($this->role === 'user') {
            return $this->hasMany(HealthRecord::class, 'user_id');
        } elseif ($this->role === 'midwife') {
            return $this->hasMany(HealthRecord::class, 'recorded_by_id');
        } elseif ($this->role === 'bhw') {
            return $this->hasMany(HealthRecord::class, 'recorded_by_id');
        }
        // Default fallback - shouldn't happen but return empty relation
        return $this->hasMany(HealthRecord::class, 'user_id')->whereRaw('1=0');
    }

    public function forumPosts()
    {
        return $this->hasMany(ForumPost::class);
    }

    public function forumComments()
    {
        return $this->hasMany(ForumComment::class);
    }

    public function forumLikes()
    {
        return $this->hasMany(ForumLike::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class, 'user_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function recordedHealthRecords()
    {
        if ($this->role === 'midwife') {
            return $this->hasMany(HealthRecord::class, 'recorded_by_id');
        } elseif ($this->role === 'bhw') {
            return $this->hasMany(HealthRecord::class, 'recorded_by_id');
        }
        return $this->hasMany(HealthRecord::class, 'recorded_by_id')->whereRaw('1=0');
    }

    // Helper methods

    /**
     * Returns true if the user can receive SMS alerts.
     * Patient must have a contact number and must NOT have opted out.
     */
    public function hasSmsEnabled(): bool
    {
        return !empty($this->contact_number) && !$this->sms_opt_out;
    }

    public function isAdmin()
    {
        return $this->role === 'rhu' || $this->role === 'cho';
    }

    public function isCho()
    {
        return $this->role === 'cho';
    }

    public function isRhu()
    {
        return $this->role === 'rhu';
    }

    public function isMidwife()
    {
        return $this->role === 'midwife';
    }

    public function isBhw()
    {
        return $this->role === 'bhw';
    }

    public function isBhwPresident()
    {
        return $this->role === 'bhw_president';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function isPending()
    {
        return ($this->status ?? 'approved') === 'pending';
    }

    public function isApproved()
    {
        return ($this->status ?? 'approved') === 'approved';
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Accessors for Reports
    public function getNameAttribute()
    {
        $name = trim($this->first_name . ' ' . 
            ($this->middle_initial ? $this->middle_initial . '. ' : '') . 
            $this->last_name);
        return $name;
    }

    public function getAgeAttribute()
    {
        if (!$this->date_of_birth) {
            return null;
        }
        return \Carbon\Carbon::parse($this->date_of_birth)->age;
    }

    public function isTeenage(): bool
    {
        return $this->date_of_birth && \Carbon\Carbon::parse($this->date_of_birth)->age < 19;
    }

    public function getPregnancyStatusAttribute()
    {
        $activePregnancy = $this->pregnancies()->active()->first();
        if ($activePregnancy) {
            return 'Pregnant';
        }

        $completedPregnancy = $this->pregnancies()->completed()->first();
        if ($completedPregnancy) {
            return 'Postpartum';
        }

        return 'Not Pregnant';
    }

    public function getAssignedBhwAttribute()
    {
        $lastHealthRecord = $this->healthRecords()
            ->byBhw()
            ->latest()
            ->first();

        if ($lastHealthRecord) {
            return $lastHealthRecord->recordedBy;
        }

        return null;
    }

    public function getLastCheckupAttribute()
    {
        return $this->checkups()
            ->where('status', 'Completed')
            ->latest('scheduled_date')
            ->first();
    }

    public function getNextAppointmentAttribute()
    {
        return $this->checkups()
            ->where('status', 'Scheduled')
            ->where('scheduled_date', '>=', Carbon::now())
            ->orderBy('scheduled_date', 'asc')
            ->first();
    }

    // Profile Image Handling
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            $publicDisk = Storage::disk('public');
            $filename = basename($this->profile_image);

            if ($publicDisk->exists($this->profile_image)) {
                return asset('storage/' . ltrim($this->profile_image, '/'));
            }

            foreach (['uploads/profile/', 'profile/'] as $directory) {
                $path = $directory . $filename;

                if ($publicDisk->exists($path)) {
                    return asset('storage/' . ltrim($path, '/'));
                }
            }

            foreach (['uploads/profile/', 'profile/', 'images/uploads/profile/'] as $directory) {
                $legacyPublicPath = public_path($directory . $filename);

                if (file_exists($legacyPublicPath)) {
                    return asset(trim(str_replace('\\', '/', $directory . $filename), '/'));
                }
            }
        }

        // Return default avatar based on gender
        $defaultAvatar = $this->gender === 'male' ? 'avatar-male.svg' : 'avatar-female.svg';
        return asset('images/avatars/' . $defaultAvatar);
    }

    public function hasProfileImage()
    {
        return !empty($this->profile_image);
    }

    /**
     * Public URL for the front scan of the registrant's valid ID, if any.
     * Mirrors the profile-image fallback chain (public disk → legacy
     * public copy) so RHU verifiers never hit a broken image link.
     */
    public function getIdImageFrontUrlAttribute(): ?string
    {
        return $this->resolveIdImageUrl($this->id_image_front);
    }

    public function getIdImageBackUrlAttribute(): ?string
    {
        return $this->resolveIdImageUrl($this->id_image_back);
    }

    public function hasIdImages(): bool
    {
        return !empty($this->id_image_front) || !empty($this->id_image_back);
    }

    private function resolveIdImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        $publicDisk = Storage::disk('public');

        if ($publicDisk->exists($normalized)) {
            return '/storage/' . $normalized;
        }

        if (file_exists(public_path($normalized))) {
            return '/' . $normalized;
        }

        // Legacy absolute copy under public/storage.
        if (file_exists(public_path('storage/' . $normalized))) {
            return '/storage/' . $normalized;
        }

        return null;
    }

    /**
     * Backup staff member receiving urgent requests while this admin is out-of-office.
     */
    public function delegateTo()
    {
        return $this->belongsTo(User::class, 'delegate_to_user_id');
    }

    /**
     * Public URL for the uploaded official digital signature, if any.
     */
    public function getSignatureImageUrlAttribute()
    {
        if ($this->signature_image && Storage::disk('public')->exists($this->signature_image)) {
            return '/storage/' . ltrim($this->signature_image, '/');
        }
        return null;
    }
}
