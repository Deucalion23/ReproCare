<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HealthRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'walk_in_patient_id',
        'pregnancy_id',
        'bp',
        'weight',
        'height',
        'bmi',
        'heart_rate',
        'temperature',
        'hemoglobin',
        'gestational_age',
        'immunization_status',
        'contraceptive_use',
        'lab_results',
        'smoking_status',
        'alcohol_use',
        'drug_use',
        'lifestyle_notes',
        'obstetric_history',
        'notes',
        'risk_level',
        'risk_assessment_mode',
        'risk_notes',
        'recommendations',
        'recorded_by_id',
        'recorded_by_user_id',
        'bhw_president_id',
        'bhw_president_reviewed_at',
        'bhw_president_notes',
        'workflow_status',
        'submitted_to_bhw_president_at',
        'submitted_to_midwife_at',
        'midwife_accepted_at',
        'workflow_notes',
        // 1. Rejection Feedback Loop — correction & resubmission state
        'revision_count',
        'rejected_by_id',
        'rejected_at',
        'rejection_reason',
        'resubmitted_at',
        // 3. Emergency fast-lane flag
        'is_emergency',
    ];

    protected $casts = [
        'rejected_at' => 'datetime',
        'resubmitted_at' => 'datetime',
        'is_emergency' => 'boolean',
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

    public function pregnancy()
    {
        return $this->belongsTo(Pregnancy::class, 'pregnancy_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function bhwPresident()
    {
        return $this->belongsTo(User::class, 'bhw_president_id');
    }

    // BHW President relationship - not used in current schema
    // public function bhwPresident()
    // {
    //     return $this->belongsTo(BhwPresident::class, 'bhw_president_id');
    // }

    // Alias for backward compatibility
    public function user()
    {
        return $this->woman();
    }

    public function patient()
    {
        return $this->woman();
    }

    public function getPatientRecordAttribute()
    {
        return $this->woman ?? $this->walkInPatient;
    }

    public function getPatientNameAttribute(): string
    {
        return $this->woman?->name
            ?? $this->walkInPatient?->full_name
            ?? 'Unknown patient';
    }

    public function getPatientContactAttribute(): ?string
    {
        return $this->woman?->contact_number
            ?? $this->woman?->phone
            ?? $this->walkInPatient?->contact_number;
    }

    public function getPatientBarangayAttribute(): ?string
    {
        return $this->woman?->barangay ?? $this->walkInPatient?->barangay;
    }

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


    // Scopes
    public function scopeLowRisk($query)
    {
        return $query->where('risk_level', 'Low');
    }

    public function scopeMediumRisk($query)
    {
        return $query->where('risk_level', 'Medium');
    }

    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', 'High');
    }

    /**
     * Scope: Filter by midwife-created records
     */
    public function scopeByMidwife($query)
    {
        return $query->whereHas('recordedBy', function($q) {
            $q->where('role', 'midwife');
        });
    }

    /**
     * Scope: Filter by BHW-created records
     */
    public function scopeByBhw($query)
    {
        return $query->whereHas('recordedBy', function($q) {
            $q->whereIn('role', ['bhw', 'bhw_president']);
        });
    }

    /**
     * Get the role of the user who created this record
     */
    public function getRecordedByRoleAttribute()
    {
        if ($this->recordedBy) {
            return optional($this->recordedBy)->role;
        }
        return null;
    }

    /**
     * Archive this record before deletion
     */
    public function archive($reason = null)
    {
        $archiveData = $this->attributes;
        $archiveData['id'] = $this->id;
        $archiveData['archived_at'] = now();
        $archiveData['archived_reason'] = $reason;
        $archiveData['created_at'] = $this->created_at;
        $archiveData['updated_at'] = $this->updated_at;
        $archiveData['is_archived'] = true;

        \Illuminate\Support\Facades\DB::table('health_records_archived')->insert($archiveData);
    }

    /**
     * Check if patient can be safely deleted
     */
    public static function canDeletePatient($patientId)
    {
        $recordCount = self::where('user_id', $patientId)->count();
        return $recordCount === 0;
    }

    /**
     * Archive all records for a patient (before patient deletion)
     */
    public static function archivePatientRecords($patientId, $reason = 'Patient deleted')
    {
        $records = self::where('user_id', $patientId)->get();

        foreach ($records as $record) {
            $record->archive($reason);
        }

        return $records->count();
    }

    public static function resolvePregnancyIdForWoman(?int $womanId, CarbonInterface|string|null $recordedAt = null): ?int
    {
        if (!$womanId) {
            return null;
        }

        $recordedDate = $recordedAt
            ? \Carbon\Carbon::parse($recordedAt)->toDateString()
            : now()->toDateString();

        return Pregnancy::query()
            ->where('user_id', $womanId)
            ->whereDate('lmp', '<=', $recordedDate)
            ->where(function ($query) use ($recordedDate) {
                $query->where(function ($activeQuery) use ($recordedDate) {
                    $activeQuery->whereNull('ended_at')
                        ->whereDate('edd', '>=', $recordedDate);
                })->orWhereDate('ended_at', '>=', $recordedDate);
            })
            ->orderByDesc('lmp')
            ->value('id');
    }
}
