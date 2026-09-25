<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaternalCareTargetClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pregnancy_id',
        'recorded_by_id',
        'date_of_registration',
        'family_serial_no',
        'gravida',
        'parity',
        'prenatal_first_trimester_date',
        'prenatal_second_trimester_date',
        'prenatal_third_trimester_date',
        'td1_date',
        'td2_date',
        'td3_date',
        'td4_date',
        'td5_date',
        'gtpal_term',
        'gtpal_preterm',
        'gtpal_abortions',
        'gtpal_living_children',
        'health_conditions',
        'health_condition_other',
        'fim_status',
        'iron_folic_first_visit_date',
        'iron_folic_first_visit_tablets',
        'iron_folic_second_visit_date',
        'iron_folic_second_visit_tablets',
        'iron_folic_third_visit_date',
        'iron_folic_third_visit_tablets',
        'iron_folic_fourth_visit_date',
        'iron_folic_fourth_visit_tablets',
        'calcium_second_visit_date',
        'calcium_second_visit_tablets',
        'calcium_third_visit_date',
        'calcium_third_visit_tablets',
        'calcium_fourth_visit_date',
        'calcium_fourth_visit_tablets',
        'iodine_date',
        'iodine_capsules_given',
        'nutritional_assessment_status',
        'deworming_date',
        'syphilis_screening_date',
        'syphilis_screening_result',
        'hepatitis_b_screening_date',
        'hepatitis_b_screening_result',
        'hiv_screening_date',
        'gestational_diabetes_screening_date',
        'gestational_diabetes_result',
        'cbc_screening_date',
        'cbc_anemia_status',
        'cbc_given_iron',
        'pregnancy_terminated_date',
        'pregnancy_outcome',
        'pregnancy_outcome_sex',
        'outcome_details',
        'delivery_type',
        'birth_weight_category',
        'facility_delivery_type',
        'facility_bemonc_capable',
        'facility_ownership',
        'non_health_facility_code',
        'birth_attendant_code',
        'delivery_remarks',
        'delivery_date',
        'delivery_time',
        'postpartum_within_24_hours_date',
        'postpartum_within_7_days_date',
        'postpartum_iron_first_month',
        'postpartum_iron_second_month',
        'postpartum_iron_third_month',
        'vitamin_a_date',
        'postpartum_remarks',
    ];

    protected $casts = [
        'date_of_registration' => 'date',
        'prenatal_first_trimester_date' => 'date',
        'prenatal_second_trimester_date' => 'date',
        'prenatal_third_trimester_date' => 'date',
        'td1_date' => 'date',
        'td2_date' => 'date',
        'td3_date' => 'date',
        'td4_date' => 'date',
        'td5_date' => 'date',
        'fim_status' => 'boolean',
        'iron_folic_first_visit_date' => 'date',
        'iron_folic_second_visit_date' => 'date',
        'iron_folic_third_visit_date' => 'date',
        'iron_folic_fourth_visit_date' => 'date',
        'calcium_second_visit_date' => 'date',
        'calcium_third_visit_date' => 'date',
        'calcium_fourth_visit_date' => 'date',
        'iodine_date' => 'date',
        'deworming_date' => 'date',
        'syphilis_screening_date' => 'date',
        'hepatitis_b_screening_date' => 'date',
        'hiv_screening_date' => 'date',
        'gestational_diabetes_screening_date' => 'date',
        'cbc_screening_date' => 'date',
        'cbc_given_iron' => 'boolean',
        'pregnancy_terminated_date' => 'date',
        'delivery_date' => 'date',
        'delivery_time' => 'datetime',
        'postpartum_within_24_hours_date' => 'date',
        'postpartum_within_7_days_date' => 'date',
        'vitamin_a_date' => 'date',
        'health_conditions' => 'array',
    ];

    // Relationships
    public function woman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pregnancy(): BelongsTo
    {
        return $this->belongsTo(Pregnancy::class, 'pregnancy_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function prenatalVisits(): HasMany
    {
        return $this->hasMany(MaternalPrenatalVisit::class)->orderBy('visit_number');
    }

    public function supplements(): HasMany
    {
        return $this->hasMany(MaternalSupplement::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(MaternalVaccination::class);
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(MaternalScreening::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(MaternalAssessment::class);
    }

    public function postpartumCare(): HasMany
    {
        return $this->hasMany(MaternalPostpartumCare::class);
    }

    // Helper methods for backward compatibility
    public function getTdVaccinationsAttribute()
    {
        return $this->vaccinations()->td()->orderBy('dose_number')->get();
    }

    public function getIronSupplementsAttribute()
    {
        return $this->supplements()->ironFolic()->orderBy('visit_number')->get();
    }

    public function getCalciumSupplementsAttribute()
    {
        return $this->supplements()->calcium()->orderBy('visit_number')->get();
    }

    public function getPrenatalVisitByTrimester($trimester)
    {
        return $this->prenatalVisits()->where('trimester', $trimester)->get();
    }

    public function getScreeningByType($type)
    {
        return $this->screenings()->type($type)->first();
    }
}
