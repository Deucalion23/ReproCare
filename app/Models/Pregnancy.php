<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Pregnancy extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'walk_in_patient_id',
        'lmp',
        'edd',
        'aog',
        'gravida',
        'para',
        'risk_level',
        'risk_assessment_mode',
        'risk_notes',
        'is_high_risk',
        'notes',
        'ended_at',
        'workflow_status',
        'submitted_to_bhw_president_at',
        'bhw_president_reviewed_at',
        'workflow_notes',
        'bhw_president_notes',
        // 1. Rejection Feedback Loop — correction & resubmission state
        'revision_count',
        'rejected_by_id',
        'rejected_at',
        'rejection_reason',
        'resubmitted_at',
        // 4. Pregnancy→Postpartum auto-transition
        'outcome',
        'postpartum_transitioned_at',
        // Sequential-pregnancy archival lock (delivered = read-only history)
        'is_locked',
        // Feature C — Facility Delivery Tracking
        'facility_delivery_place',
        'delivery_date',
        'delivery_time',
        'delivery_attendant',
        'delivery_notes',
    ];

    // Relationships
    public function woman()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function walkInPatient()
    {
        return $this->belongsTo(WalkInPatient::class, 'walk_in_patient_id');
    }

    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class, 'pregnancy_id')->orderByDesc('created_at');
    }

    public function maternalCareTargetClient()
    {
        return $this->hasOne(MaternalCareTargetClient::class, 'pregnancy_id');
    }

    public function referrals()
    {
        return $this->hasMany(CheckupReferral::class, 'pregnancy_id')->latest();
    }

    // Get the patient model (either user or walk-in)
    public function getPatientModel()
    {
        return $this->user_id ? $this->woman : $this->walkInPatient;
    }

    // Get patient name
    public function getPatientNameAttribute()
    {
        if ($this->user_id && $this->woman) {
            return $this->woman->name;
        }
        if ($this->walk_in_patient_id && $this->walkInPatient) {
            return $this->walkInPatient->full_name;
        }
        return 'Unknown';
    }

    protected $casts = [
        'lmp' => 'date',
        'edd' => 'date',
        'ended_at' => 'date',
        'is_high_risk' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'rejected_at' => 'datetime',
        'resubmitted_at' => 'datetime',
        'postpartum_transitioned_at' => 'datetime',
    ];

    // ── 1. Rejection Feedback Loop helpers ─────────────────────────────
    public function scopeNeedsRevision($query)
    {
        return $query->whereIn('workflow_status', ['needs_revision', 'bhw_president_rejected']);
    }

    public function getIsNeedsRevisionAttribute(): bool
    {
        return in_array($this->workflow_status, ['needs_revision', 'bhw_president_rejected'], true);
    }

    public function getReviewerNoteAttribute(): ?string
    {
        return $this->rejection_reason ?? $this->workflow_notes;
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by_id');
    }

    public function newborns()
    {
        return $this->hasMany(Newborn::class, 'pregnancy_id');
    }

    // Alias for backward compatibility
    public function user()
    {
        return $this->woman();
    }

    // Accessors and Mutators
    public function setLmpAttribute($value)
    {
        $this->attributes['lmp'] = $value;
        // Auto-calculate EDD (280 days from LMP)
        $this->attributes['edd'] = Carbon::parse($value)->addDays(280)->format('Y-m-d');
        // Auto-calculate AOG in completed weeks
        $this->attributes['aog'] = floor(Carbon::parse($value)->diffInDays(Carbon::now()) / 7);
    }

    public function getAogWeeksAttribute()
    {
        if (!$this->lmp) return 0;
        return floor(Carbon::parse($this->lmp)->diffInDays(Carbon::now()) / 7);
    }

    public function getAogDaysAttribute()
    {
        if (!$this->lmp) return 0;
        return Carbon::parse($this->lmp)->diffInDays(Carbon::now()) % 7;
    }

    public function getFormattedAogAttribute()
    {
        $weeks = floor($this->aog_weeks);
        $days = $this->aog_days;
        return "{$weeks} weeks, {$days} days";
    }

    /**
     * Calculate current gestational age in weeks
     */
    public function getCurrentGestationalAge()
    {
        if (!$this->lmp) return null;
        return Carbon::parse($this->lmp)->diffInDays(Carbon::now()) / 7;
    }

    /**
     * Get current trimester
     */
    public function getTrimesterAttribute()
    {
        $weeks = $this->getCurrentGestationalAge();
        if ($weeks === null) return null;
        
        if ($weeks < 13) return 1;
        if ($weeks < 27) return 2;
        return 3;
    }

    /**
     * Get trimester name
     */
    public function getTrimesterNameAttribute()
    {
        $trimester = $this->trimester;
        if ($trimester === null) return 'Unknown';
        
        $names = [1 => 'First Trimester', 2 => 'Second Trimester', 3 => 'Third Trimester'];
        return $names[$trimester];
    }

    /**
     * Get pregnancy milestones
     */
    public function getMilestonesAttribute()
    {
        if (!$this->lmp || !$this->edd) return [];
        
        $lmp = Carbon::parse($this->lmp);
        $edd = Carbon::parse($this->edd);
        $now = Carbon::now();
        
        $milestones = [
            [
                'name' => 'Conception',
                'date' => $lmp->copy()->addDays(14),
                'week' => 2,
                'status' => $now->gt($lmp->copy()->addDays(14)) ? 'completed' : ($now->gte($lmp->copy()->addDays(14)) ? 'current' : 'upcoming'),
            ],
            [
                'name' => 'First Trimester Ends',
                'date' => $lmp->copy()->addDays(91),
                'week' => 13,
                'status' => $now->gt($lmp->copy()->addDays(91)) ? 'completed' : ($now->gte($lmp->copy()->addDays(91)) ? 'current' : 'upcoming'),
            ],
            [
                'name' => 'Second Trimester Ends',
                'date' => $lmp->copy()->addDays(189),
                'week' => 27,
                'status' => $now->gt($lmp->copy()->addDays(189)) ? 'completed' : ($now->gte($lmp->copy()->addDays(189)) ? 'current' : 'upcoming'),
            ],
            [
                'name' => 'Third Trimester Ends',
                'date' => $edd,
                'week' => 40,
                'status' => $now->gt($edd) ? 'completed' : ($now->gte($edd) ? 'current' : 'upcoming'),
            ],
        ];
        
        return $milestones;
    }

    /**
     * Get recommended prenatal visit schedule
     */
    public function getPrenatalScheduleAttribute()
    {
        if (!$this->lmp) return [];
        
        $lmp = Carbon::parse($this->lmp);
        $schedule = [];
        
        // Standard prenatal visit schedule
        $visits = [
            ['week' => 8, 'name' => 'Initial Prenatal Visit'],
            ['week' => 12, 'name' => 'First Trimester Checkup'],
            ['week' => 16, 'name' => 'Second Trimester Checkup'],
            ['week' => 20, 'name' => 'Anatomy Scan'],
            ['week' => 24, 'name' => 'Prenatal Checkup'],
            ['week' => 28, 'name' => 'Glucose Screening'],
            ['week' => 32, 'name' => 'Prenatal Checkup'],
            ['week' => 36, 'name' => 'Weekly Checkups Begin'],
            ['week' => 40, 'name' => 'Due Date'],
        ];
        
        foreach ($visits as $visit) {
            $visitDate = $lmp->copy()->addWeeks($visit['week']);
            $schedule[] = [
                'name' => $visit['name'],
                'week' => $visit['week'],
                'date' => $visitDate,
                'completed' => Carbon::now()->gt($visitDate),
            ];
        }
        
        return $schedule;
    }

    /**
     * Get days until due date
     */
    public function getDaysUntilDueAttribute()
    {
        if (!$this->edd) return null;
        return floor(Carbon::now()->diffInDays(Carbon::parse($this->edd), false));
    }

    /**
     * Check if pregnancy is overdue
     */
    public function getIsOverdueAttribute()
    {
        if (!$this->edd) return false;
        return Carbon::now()->gt(Carbon::parse($this->edd));
    }

    /**
   * Get gravida from maternal care target client (if exists)
   */
    public function getGravidaAttribute()
    {
        return $this->maternalCareTargetClient?->gravida ?? $this->attributes['gravida'] ?? null;
    }

    /**
   * Get para from maternal care target client (if exists)
   */
    public function getParaAttribute()
    {
        return $this->maternalCareTargetClient?->parity ?? $this->attributes['para'] ?? null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at')
            ->where('edd', '>=', Carbon::today());
    }
    
    public function scopeCompleted($query)
    {
        return $query->where(function ($query) {
            $query->whereNotNull('ended_at')
                ->orWhere('edd', '<', Carbon::today());
        });
    }
    
    public function scopeHighRisk($query)
    {
        return $query->where('is_high_risk', true);
    }

    // Workflow Scopes
    public function scopeDraft($query)
    {
        return $query->where('workflow_status', 'draft');
    }

    public function scopeSubmittedToBhwPresident($query)
    {
        return $query->where('workflow_status', 'submitted_to_bhw_president');
    }

    public function scopeBhwPresidentReview($query)
    {
        return $query->where('workflow_status', 'bhw_president_review');
    }

    public function scopeBhwPresidentApproved($query)
    {
        return $query->where('workflow_status', 'bhw_president_approved');
    }

    public function scopeBhwPresidentRejected($query)
    {
        return $query->where('workflow_status', 'bhw_president_rejected');
    }

    public function scopeWorkflowCompleted($query)
    {
        return $query->where('workflow_status', 'completed');
    }

    public function scopePendingReview($query)
    {
        return $query->whereIn('workflow_status', ['submitted_to_bhw_president', 'bhw_president_review']);
    }
    
    // Accessors
    public function getIsActiveAttribute()
    {
        return is_null($this->ended_at) && $this->edd && $this->edd >= Carbon::today();
    }
    
    public function getIsHighRiskAttribute()
    {
        return $this->is_high_risk ?? false;
    }

    public function getStatusAttribute()
    {
        if ($this->ended_at || ($this->edd && $this->edd < Carbon::today())) {
            return 'completed';
        }

        return 'active';
    }

    public function getCanStartAnotherPregnancyAttribute(): bool
    {
        return !$this->is_active;
    }
}
