<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class User extends Authenticatable implements MustVerifyEmail
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
        'google_id',
        'is_profile_complete',
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
        'failed_login_attempts',
        'profile_image',
        'profile_image_data',
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
        // Step 5 of self-registration: valid ID front/back scan paths,
        // plus a database data-URL copy of each (survives ephemeral disks —
        // file paths alone kept turning into "No ID on file" after deploys).
        'id_image_front',
        'id_image_back',
        'id_image_front_data',
        'id_image_back_data',
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
            'is_profile_complete'         => 'boolean',
        ];
    }

    /**
     * Google OAuth may only ever provision patient accounts ('user').
     * Staff / clinical roles must be created by admins, never via Socialite.
     */
    public const OAUTH_ALLOWED_ROLES = ['user'];

    public const STAFF_ROLES = ['cho', 'rhu', 'midwife', 'bhw', 'bhw_president'];

    /**
     * Shown on the login page when a rejected account tries to enter. The
     * account is simultaneously re-queued as pending so the RHU 1
     * administrator can reassess / re-verify it.
     */
    public const REJECTED_LOGIN_MESSAGE = 'Your account has been rejected, please fill up the correct information needed. It will be reassessed by the RHU 1 administrator for re-verification.';

    /**
     * Demo accounts exempt from the email-verification + onboarding gates:
     * they verify instantly and log straight into the dashboard (status
     * gates like RHU approval still apply). Add future demo emails here —
     * never real patient addresses.
     */
    public const DEMO_EMAILS = [
        'mariasanta@gmail.com',
    ];

    /**
     * Case-insensitive demo-account check (see DEMO_EMAILS).
     */
    public static function isDemoAccount(?string $email): bool
    {
        $email = strtolower(trim((string) $email));

        if ($email === '') {
            return false;
        }

        foreach (self::DEMO_EMAILS as $demo) {
            if (strtolower(trim((string) $demo)) === $email) {
                return true;
            }
        }

        return false;
    }

    /**
     * Provisional (limited) access: a patient account that verified its
     * email and finished onboarding but is still awaiting RHU approval.
     * Provisional users may browse the dashboard, learning materials,
     * community posts (read-only) and their own profile — clinical and
     * write features (pregnancies, cycle logging, checkups, health
     * records, care chat, community posting) stay locked until approval.
     * Demo accounts are excluded: they always enjoy full access.
     */
    public function hasProvisionalAccess(): bool
    {
        return ($this->role ?? null) === 'user'
            && ($this->status ?? 'approved') === 'pending'
            && $this->hasVerifiedEmail()
            && ! $this->needsProfileCompletion()
            && ! self::isDemoAccount($this->email ?? null);
    }

    /**
     * Whether this account is still forced through profile completion
     * (missing phone/barangay after Google sign-up).
     */
    public function needsProfileCompletion(): bool
    {
        return $this->role === 'user' && ! (bool) ($this->is_profile_complete ?? false);
    }

    // ── Domain Relationships ─────────────────────────────────────────────────

    public function pregnancies()
    {
        return $this->hasMany(Pregnancy::class, 'user_id');
    }

    /**
     * Menstrual periods must not be recorded while a pregnancy remains active.
     * Pregnancy-related bleeding is a clinical concern, not a cycle entry.
     */
    public function pregnancyBlockingMenstrualLogging(): ?Pregnancy
    {
        // Do not silently resume cycle tracking simply because an estimated
        // due date passed. A clinician must first close the pregnancy record.
        return $this->pregnancies()->whereNull('ended_at')->latest('lmp')->first();
    }

    public function canLogMenstrualPeriod(): bool
    {
        return $this->pregnancyBlockingMenstrualLogging() === null;
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

    /**
     * Seed a demo/staff account WITHOUT clobbering real edits.
     *
     * Seeders run on every production boot, so plain updateOrCreate would
     * reset address, phone number, photos and names that staff or patients
     * changed back to seed values. seedAccount creates a missing account,
     * but on existing rows it only backfills columns that are still empty.
     * Credentials, role, status and any filled profile field are left alone.
     */
    public static function seedAccount(array $unique, array $values): static
    {
        $account = static::withTrashed()->where($unique)->first();
        if (! $account) {
            return static::create($unique + $values);
        }

        $backfill = collect($values)
            ->except(array_merge(array_keys($unique), ['password', 'remember_token']))
            ->filter(fn ($value, $key) => $value !== null
                && ($account->getAttribute($key) === null || $account->getAttribute($key) === ''));

        if ($backfill->isNotEmpty()) {
            $account->fill($backfill->all())->save();
        }

        return $account->refresh();
    }

    // Profile Image Handling
    //
    // profile_image_data holds a small resized data-URL copy of the avatar
    // in the database (survives ephemeral disks, renders on every device).
    // The file path in profile_image remains as a fallback.
    public static function makeAvatarDataUrl($file): ?string
    {
        try {
            if (! $file || ! method_exists($file, 'getRealPath') || ! is_file($file->getRealPath())) {
                return null;
            }

            // Preferred: resized JPEG via GD (max 256px, ~15-30KB).
            if (function_exists('imagecreatefromstring') && function_exists('imagecreatetruecolor')
                && function_exists('imagejpeg') && function_exists('imagesx') && function_exists('imagesy')) {
                $raw = @file_get_contents($file->getRealPath());
                if ($raw !== false) {
                    $src = @imagecreatefromstring($raw);
                    if ($src !== false) {
                        $w = imagesx($src);
                        $h = imagesy($src);
                        if ($w > 0 && $h > 0) {
                            $max = 256;
                            $scale = min(1, $max / max($w, $h));
                            $nw = max(1, (int) round($w * $scale));
                            $nh = max(1, (int) round($h * $scale));
                            $dst = imagecreatetruecolor($nw, $nh);
                            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                            ob_start();
                            imagejpeg($dst, null, 72);
                            $jpeg = ob_get_clean();
                            imagedestroy($src);
                            imagedestroy($dst);
                            if ($jpeg !== false && strlen($jpeg) <= 90000) {
                                return 'data:image/jpeg;base64,' . base64_encode($jpeg);
                            }
                        } else {
                            imagedestroy($src);
                        }
                    }
                }
            }

            // Fallback when GD is unavailable: embed only tiny originals.
            $size = method_exists($file, 'getSize') ? (int) $file->getSize() : 0;
            if ($size > 0 && $size <= 49152) {
                $mime = method_exists($file, 'getMimeType') ? ($file->getMimeType() ?: 'image/jpeg') : 'image/jpeg';
                if (! str_starts_with($mime, 'image/')) {
                    return null;
                }
                $raw = @file_get_contents($file->getRealPath());
                if ($raw !== false) {
                    return 'data:' . $mime . ';base64,' . base64_encode($raw);
                }
            }
        } catch (\Throwable $e) {
            // Never break an upload because the inline copy failed.
        }

        return null;
    }

    public function getProfileImageUrlAttribute()
    {
        $inline = $this->getAttribute('profile_image_data');
        if (is_string($inline) && str_starts_with($inline, 'data:image')) {
            return $inline;
        }

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

        // Return default avatar: BHW and BHW Presidents share one badge
        // (lavender silhouette on white with slim black border),
        // everyone else falls back by gender.
        if (in_array($this->role, ['bhw', 'bhw_president'], true)) {
            return self::versionedAvatar('avatar-bhw-president.svg');
        }
        $defaultAvatar = $this->gender === 'male' ? 'avatar-male.svg' : 'avatar-female.svg';
        return self::versionedAvatar($defaultAvatar);
    }

    /**
     * Default avatar URL with a filemtime version query so browsers fetch
     * the new artwork immediately after it changes instead of showing a
     * stale cached copy (which looks like the avatar "flip-flops").
     */
    private static array $avatarVersions = [];

    private static function versionedAvatar(string $filename): string
    {
        if (!array_key_exists($filename, self::$avatarVersions)) {
            $path = public_path('images/avatars/' . $filename);
            self::$avatarVersions[$filename] = is_file($path) ? (int) filemtime($path) : 0;
        }

        return asset('images/avatars/' . $filename) . '?v=' . self::$avatarVersions[$filename];
    }

    public function hasProfileImage()
    {
        $inline = $this->getAttribute('profile_image_data');
        if (is_string($inline) && str_starts_with($inline, 'data:image')) {
            return true;
        }

        return !empty($this->profile_image);
    }

    /**
     * Display title for directories and chats: BHW roles include their
     * barangay (e.g. "BHW · Barangay Burgos Padlan"), others show the
     * plain role name.
     */
    public function getStaffTitleAttribute(): string
    {
        $base = match ($this->role) {
            'user' => 'Patient',
            'midwife' => 'Midwife',
            'bhw_president' => 'BHW President',
            'bhw' => 'BHW',
            default => 'Member',
        };

        if (in_array($this->role, ['bhw', 'bhw_president'], true)) {
            $barangay = $this->assigned_barangay ?: $this->barangay;
            if (is_string($barangay) && trim($barangay) !== '') {
                return $base . ' · ' . \App\Services\BhwPresidentAssignmentService::displayBarangay($barangay);
            }
        }

        return $base;
    }

    /**
     * Public URL for the front scan of the registrant's valid ID, if any.
     * Prefers the database copy (survives ephemeral disks), then falls back
     * to the file-path chain (public disk → legacy public copy) so RHU
     * verifiers never hit a broken image link.
     */
    public function getIdImageFrontUrlAttribute(): ?string
    {
        $inline = $this->getAttribute('id_image_front_data');
        if (is_string($inline) && str_starts_with($inline, 'data:image')) {
            return $inline;
        }

        return $this->resolveIdImageUrl($this->id_image_front);
    }

    public function getIdImageBackUrlAttribute(): ?string
    {
        $inline = $this->getAttribute('id_image_back_data');
        if (is_string($inline) && str_starts_with($inline, 'data:image')) {
            return $inline;
        }

        return $this->resolveIdImageUrl($this->id_image_back);
    }

    public function hasIdImages(): bool
    {
        return !empty($this->id_image_front) || !empty($this->id_image_back)
            || !empty($this->id_image_front_data) || !empty($this->id_image_back_data);
    }

    /**
     * CHO display fallbacks: when a registered woman has no contact or
     * address on file, show a stable demo value instead of "N/A".
     * Display-only — nothing is written to the database. Values derive
     * from the user id, so each woman shows the same number/barangay on
     * every page load instead of a reshuffling random value.
     */
    public function getDisplayContactNumberAttribute(): string
    {
        if (!empty($this->contact_number)) {
            return (string) $this->contact_number;
        }

        return '09' . sprintf('%09d', $this->demoFallbackSeed() % 1000000000);
    }

    public function getDisplayBarangayAttribute(): string
    {
        if (!empty($this->barangay)) {
            return (string) $this->barangay;
        }

        $names = \App\Models\Barangay::catchmentNames('RHU 1');
        if (empty($names)) {
            $names = ['San Carlos City'];
        }

        return $names[$this->demoFallbackSeed() % count($names)];
    }

    public function getDisplayAddressAttribute(): string
    {
        if (!empty($this->address)) {
            return (string) $this->address;
        }

        return $this->display_barangay . ', San Carlos City, Pangasinan';
    }

    private function demoFallbackSeed(): int
    {
        return abs(crc32('cho-demo|' . ($this->id ?? 0)));
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
