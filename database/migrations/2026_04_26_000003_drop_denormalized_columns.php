<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop denormalized columns from maternal_care_target_clients
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $columns = Schema::getColumnListing('maternal_care_target_clients');
            
            // Prenatal visits
            $prenatalColumns = [
                'prenatal_first_trimester_date',
                'prenatal_second_trimester_date',
                'prenatal_second_trimester_date_1',
                'prenatal_second_trimester_date_2',
                'prenatal_third_trimester_date',
                'prenatal_third_trimester_date_1',
                'prenatal_third_trimester_date_2',
                'prenatal_third_trimester_date_3',
                'prenatal_third_trimester_date_4',
                'prenatal_third_trimester_date_5',
            ];
            foreach ($prenatalColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
            
            // TD vaccinations
            $tdColumns = ['td1_date', 'td2_date', 'td3_date', 'td4_date', 'td5_date'];
            foreach ($tdColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
            
            // Iron/folic supplements
            $ironColumns = [
                'iron_folic_first_visit_date',
                'iron_folic_first_visit_tablets',
                'iron_folic_second_visit_date',
                'iron_folic_second_visit_tablets',
                'iron_folic_third_visit_date',
                'iron_folic_third_visit_tablets',
                'iron_folic_fourth_visit_date',
                'iron_folic_fourth_visit_tablets',
            ];
            foreach ($ironColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
            
            // Calcium supplements
            $calciumColumns = [
                'calcium_second_visit_date',
                'calcium_second_visit_tablets',
                'calcium_third_visit_date',
                'calcium_third_visit_tablets',
                'calcium_fourth_visit_date',
                'calcium_fourth_visit_tablets',
            ];
            foreach ($calciumColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
            
            // Iodine
            $iodineColumns = ['iodine_date', 'iodine_capsules_given'];
            foreach ($iodineColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
            
            // Screenings
            $screeningColumns = [
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
            ];
            foreach ($screeningColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
            
            // Postpartum
            $postpartumColumns = [
                'postpartum_within_24_hours_date',
                'postpartum_within_7_days_date',
                'postpartum_iron_first_month',
                'postpartum_iron_second_month',
                'postpartum_iron_third_month',
                'vitamin_a_date',
                'postpartum_remarks',
            ];
            foreach ($postpartumColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
        });

        // Drop denormalized columns from child_care_target_clients
        Schema::table('child_care_target_clients', function (Blueprint $table) {
            $columns = Schema::getColumnListing('child_care_target_clients');
            
            // Vaccinations
            $vaccineColumns = [
                'bcg_date',
                'hepa_b_bd_date',
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
                'ipv_2_date',
                'mmr_1_date',
                'mmr_2_date',
                'fic_date',
                'cic_date',
            ];
            foreach ($vaccineColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
            
            // Assessments
            $assessmentColumns = [
                'assessment_1_3_age_months',
                'assessment_1_3_length_cm',
                'assessment_1_3_length_date',
                'assessment_1_3_weight_kg',
                'assessment_1_3_weight_date',
                'assessment_1_3_status',
                'assessment_6_11_age_months',
                'assessment_6_11_length_cm',
                'assessment_6_11_length_date',
                'assessment_6_11_weight_kg',
                'assessment_6_11_weight_date',
                'assessment_6_11_status',
                'assessment_12_age_months',
                'assessment_12_length_cm',
                'assessment_12_length_date',
                'assessment_12_weight_kg',
                'assessment_12_weight_date',
                'assessment_12_status',
            ];
            foreach ($assessmentColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
            
            // Supplements
            $supplementColumns = [
                'low_birth_weight_iron_1_month_date',
                'low_birth_weight_iron_2_month_date',
                'low_birth_weight_iron_3_month_date',
                'vitamin_a_date',
                'mnp_date',
                'mnp_sachets_given',
            ];
            foreach ($supplementColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
            
            // Nutrition tracking
            $nutritionColumns = [
                'exclusive_breastfeeding_1_5_months',
                'exclusive_breastfeeding_2_5_months',
                'exclusive_breastfeeding_3_5_months',
                'exclusive_breastfeeding_4_5_months',
                'exclusive_breastfeeding_5_9_months',
                'exclusive_breastfed_up_to_6_months',
                'complementary_feeding_introduced',
                'breastfeeding_initiated_date',
            ];
            foreach ($nutritionColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
            
            // Management outcomes
            $outcomeColumns = [
                'man_admitted_sfp',
                'man_cured',
                'man_defaulted',
                'man_died',
                'sam_admitted_otc',
                'sam_cured',
                'sam_defaulted',
                'sam_died',
            ];
            foreach ($outcomeColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
            
            // Remarks
            if (in_array('remarks', $columns)) {
                $table->dropColumn('remarks');
            }
        });

        // Drop redundant columns from pregnancies (health data should be in health_records)
        Schema::table('pregnancies', function (Blueprint $table) {
            $columns = Schema::getColumnListing('pregnancies');
            
            $pregnancyColumns = [
                'blood_pressure',
                'weight',
                'height',
                'bmi',
                'smoking_status',
                'alcohol_status',
                'drug_use_status',
                'lifestyle_notes',
                'obstetric_history',
            ];
            foreach ($pregnancyColumns as $col) {
                if (in_array($col, $columns)) {
                    $table->dropColumn($col);
                }
            }
        });

        // Drop material_type_new from learning_materials (redundant)
        Schema::table('learning_materials', function (Blueprint $table) {
            if (Schema::hasColumn('learning_materials', 'material_type_new')) {
                $table->dropColumn('material_type_new');
            }
        });

        // Drop barangay from child_records (redundant - should come from puroks)
        Schema::table('child_records', function (Blueprint $table) {
            if (Schema::hasColumn('child_records', 'barangay')) {
                $table->dropColumn('barangay');
            }
        });
    }

    public function down(): void
    {
        // Note: Down migration is complex for column additions
        // This would require knowing the exact column definitions
        // For production, it's recommended to restore from backup instead
    }
};
