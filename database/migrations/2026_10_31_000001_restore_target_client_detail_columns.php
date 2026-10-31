<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The maternal/child Target Client Lists still encode every DOH form column on
 * the profile row (the normalized visit/immunization tables are not wired up),
 * but 2026_04_26_000003 dropped those columns. Re-add them so the TCL input
 * pages can save instead of failing with "column ... does not exist".
 *
 * Guarded and re-runnable so it is safe on databases where the drops were
 * skipped or partially applied.
 */
return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        $this->addColumns('maternal_care_target_clients', [
            'date' => [
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
                'td1_date', 'td2_date', 'td3_date', 'td4_date', 'td5_date',
                'iron_folic_first_visit_date',
                'iron_folic_second_visit_date',
                'iron_folic_third_visit_date',
                'iron_folic_fourth_visit_date',
                'calcium_second_visit_date',
                'calcium_third_visit_date',
                'calcium_fourth_visit_date',
                'iodine_date',
                'syphilis_screening_date',
                'hepatitis_b_screening_date',
                'hiv_screening_date',
                'gestational_diabetes_screening_date',
                'cbc_screening_date',
                'postpartum_within_24_hours_date',
                'postpartum_within_7_days_date',
                'vitamin_a_date',
            ],
            'integer' => [
                'iron_folic_first_visit_tablets',
                'iron_folic_second_visit_tablets',
                'iron_folic_third_visit_tablets',
                'iron_folic_fourth_visit_tablets',
                'calcium_second_visit_tablets',
                'calcium_third_visit_tablets',
                'calcium_fourth_visit_tablets',
                'iodine_capsules_given',
            ],
            'string' => [
                'syphilis_screening_result',
                'hepatitis_b_screening_result',
                'gestational_diabetes_result',
                'cbc_anemia_status',
                'postpartum_iron_first_month',
                'postpartum_iron_second_month',
                'postpartum_iron_third_month',
            ],
            'boolean' => [
                'cbc_given_iron',
            ],
            'text' => [
                'postpartum_remarks',
            ],
        ]);

        $this->addColumns('child_care_target_clients', [
            'date' => [
                'breastfeeding_initiated_date',
                'bcg_date',
                'hepa_b_bd_date',
                'assessment_1_3_length_date',
                'assessment_1_3_weight_date',
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
                'ipv_2_date',
                'assessment_6_11_length_date',
                'assessment_6_11_weight_date',
                'vitamin_a_date',
                'mnp_date',
                'mmr_1_date',
                'mmr_2_date',
                'assessment_12_length_date',
                'assessment_12_weight_date',
                'fic_date',
                'cic_date',
            ],
            'string' => [
                'assessment_1_3_age_months',
                'assessment_1_3_status',
                'assessment_6_11_age_months',
                'assessment_6_11_status',
                'assessment_12_age_months',
                'assessment_12_status',
            ],
            'decimal' => [
                'assessment_1_3_length_cm',
                'assessment_1_3_weight_kg',
                'assessment_6_11_length_cm',
                'assessment_6_11_weight_kg',
                'assessment_12_length_cm',
                'assessment_12_weight_kg',
            ],
            'integer' => [
                'mnp_sachets_given',
            ],
            'boolean' => [
                'exclusive_breastfeeding_1_5_months',
                'exclusive_breastfeeding_2_5_months',
                'exclusive_breastfeeding_3_5_months',
                'exclusive_breastfeeding_4_5_months',
                'exclusive_breastfeeding_5_9_months',
                'exclusive_breastfed_up_to_6_months',
                'complementary_feeding_introduced',
                'man_admitted_sfp',
                'man_cured',
                'man_defaulted',
                'man_died',
                'sam_admitted_otc',
                'sam_cured',
                'sam_defaulted',
                'sam_died',
            ],
            'text' => [
                'remarks',
            ],
        ]);

        // The child create form captures the middle initial and barangay; the
        // barangay column was dropped as "denormalized" but nothing else fills
        // it in, so the list search and display lost that data.
        $this->addColumns('child_records', [
            'string' => ['middle_initial', 'barangay'],
        ]);
    }

    /**
     * @param  array<string, array<int, string>>  $groups
     */
    private function addColumns(string $table, array $groups): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($groups as $type => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    continue;
                }

                try {
                    Schema::table($table, function (Blueprint $blueprint) use ($type, $column) {
                        $definition = match ($type) {
                            'date' => $blueprint->date($column),
                            'integer' => $blueprint->unsignedInteger($column),
                            'decimal' => $blueprint->decimal($column, 6, 2),
                            'boolean' => $blueprint->boolean($column),
                            'text' => $blueprint->text($column),
                            default => $blueprint->string($column),
                        };

                        $definition->nullable();
                    });
                } catch (\Throwable $e) {
                    // Best-effort: a single stubborn column must not block the rest.
                }
            }
        }
    }

    public function down(): void
    {
        // Restoring these columns is additive; keep the data on rollback.
    }
};
