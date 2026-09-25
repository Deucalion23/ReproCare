<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChildCareTargetClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'date_of_registration',
        'family_serial_number',
        'cpab_tt2_tt4',
        'cpab_tt3_tt5',
        'newborn_status',
        'breastfeeding_initiated_date',
        'bcg_date',
        'hepa_b_bd_date',
        'assessment_1_3_age_months',
        'assessment_1_3_length_cm',
        'assessment_1_3_length_date',
        'assessment_1_3_weight_kg',
        'assessment_1_3_weight_date',
        'assessment_1_3_status',
        'low_birth_weight_iron_1_month_date',
        'low_birth_weight_iron_2_month_date',
        'low_birth_weight_iron_3_month_date',
        'dpt_hepb_hib_1_date',
        'dpt_hepb_hib_2_date',
        'dpt_hepb_hib_3_date',
        'opv_1_date',
        'opv_2_date',
        'opv_3_date',
        'pcv_1_date',
        'pcv_2_date',
        'pcv_3_date',
        'ipv_1_date',
        'exclusive_breastfeeding_1_5_months',
        'exclusive_breastfeeding_2_5_months',
        'exclusive_breastfeeding_3_5_months',
        'exclusive_breastfeeding_4_5_months',
        'exclusive_breastfeeding_5_9_months',
        'assessment_6_11_age_months',
        'assessment_6_11_length_cm',
        'assessment_6_11_length_date',
        'assessment_6_11_weight_kg',
        'assessment_6_11_weight_date',
        'assessment_6_11_status',
        'exclusive_breastfed_up_to_6_months',
        'complementary_feeding_introduced',
        'vitamin_a_date',
        'mnp_date',
        'mnp_sachets_given',
        'mmr_1_date',
        'ipv_2_date',
        'assessment_12_age_months',
        'assessment_12_length_cm',
        'assessment_12_length_date',
        'assessment_12_weight_kg',
        'assessment_12_weight_date',
        'assessment_12_status',
        'mmr_2_date',
        'fic_date',
        'cic_date',
        'man_admitted_sfp',
        'man_cured',
        'man_defaulted',
        'man_died',
        'sam_admitted_otc',
        'sam_cured',
        'sam_defaulted',
        'sam_died',
        'remarks',
    ];

    protected $casts = [
        'date_of_registration' => 'date',
        'cpab_tt2_tt4' => 'boolean',
        'cpab_tt3_tt5' => 'boolean',
        'breastfeeding_initiated_date' => 'date',
        'bcg_date' => 'date',
        'hepa_b_bd_date' => 'date',
        'assessment_1_3_length_date' => 'date',
        'assessment_1_3_weight_date' => 'date',
        'low_birth_weight_iron_1_month_date' => 'date',
        'low_birth_weight_iron_2_month_date' => 'date',
        'low_birth_weight_iron_3_month_date' => 'date',
        'dpt_hepb_hib_1_date' => 'date',
        'dpt_hepb_hib_2_date' => 'date',
        'dpt_hepb_hib_3_date' => 'date',
        'opv_1_date' => 'date',
        'opv_2_date' => 'date',
        'opv_3_date' => 'date',
        'pcv_1_date' => 'date',
        'pcv_2_date' => 'date',
        'pcv_3_date' => 'date',
        'ipv_1_date' => 'date',
        'exclusive_breastfeeding_1_5_months' => 'boolean',
        'exclusive_breastfeeding_2_5_months' => 'boolean',
        'exclusive_breastfeeding_3_5_months' => 'boolean',
        'exclusive_breastfeeding_4_5_months' => 'boolean',
        'exclusive_breastfeeding_5_9_months' => 'boolean',
        'assessment_6_11_length_date' => 'date',
        'assessment_6_11_weight_date' => 'date',
        'exclusive_breastfed_up_to_6_months' => 'boolean',
        'complementary_feeding_introduced' => 'boolean',
        'vitamin_a_date' => 'date',
        'mnp_date' => 'date',
        'mmr_1_date' => 'date',
        'ipv_2_date' => 'date',
        'assessment_12_length_date' => 'date',
        'assessment_12_weight_date' => 'date',
        'mmr_2_date' => 'date',
        'fic_date' => 'date',
        'cic_date' => 'date',
        'man_admitted_sfp' => 'boolean',
        'man_cured' => 'boolean',
        'man_defaulted' => 'boolean',
        'man_died' => 'boolean',
        'sam_admitted_otc' => 'boolean',
        'sam_cured' => 'boolean',
        'sam_defaulted' => 'boolean',
        'sam_died' => 'boolean',
    ];

    // Relationships
    public function child(): BelongsTo
    {
        return $this->belongsTo(ChildRecord::class, 'child_id');
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(ChildVaccination::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(ChildAssessment::class);
    }

    public function nutritionTracking(): HasMany
    {
        return $this->hasMany(ChildNutritionTracking::class);
    }

    public function supplements(): HasMany
    {
        return $this->hasMany(ChildSupplement::class);
    }

    public function managementOutcomes(): HasMany
    {
        return $this->hasMany(ChildManagementOutcome::class);
    }

    public function feedingMilestones(): HasOne
    {
        return $this->hasOne(ChildFeedingMilestone::class);
    }

    // Helper methods for backward compatibility
    public function getVaccinationByType($type, $dose = null)
    {
        $query = $this->vaccinations()->type($type);
        if ($dose) {
            $query->where('dose_number', $dose);
        }
        return $query->first();
    }

    public function getAssessmentByAgeGroup($group)
    {
        return $this->assessments()->ageGroup($group)->first();
    }

    public function getManagementOutcomeByType($type)
    {
        return $this->managementOutcomes()->programType($type)->first();
    }
}
