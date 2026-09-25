<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create child_vaccinations table
        Schema::create('child_vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_care_target_client_id')->constrained('child_care_target_clients')->onDelete('cascade');
            $table->enum('vaccine_type', ['bcg', 'hepa_b_bd', 'dpt_hepb_hib', 'opv', 'pcv', 'ipv', 'mmr', 'fic', 'cic']);
            $table->integer('dose_number')->nullable();
            $table->date('vaccination_date')->nullable();
            $table->timestamps();
            
            $table->unique(['child_care_target_client_id', 'vaccine_type', 'dose_number'], 'child_vax_unique');
            $table->index('child_care_target_client_id', 'child_vax_client_idx');
        });

        // 2. Create child_assessments table
        Schema::create('child_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_care_target_client_id')->constrained('child_care_target_clients')->onDelete('cascade');
            $table->enum('age_group', ['1_3_months', '6_11_months', '12_months']);
            $table->string('age_months', 255)->nullable();
            $table->decimal('length_cm', 6, 2)->nullable();
            $table->date('length_date')->nullable();
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->date('weight_date')->nullable();
            $table->enum('status', ['S', 'W-MAM', 'W-SAM', 'O', 'N'])->nullable();
            $table->timestamps();
            
            $table->unique(['child_care_target_client_id', 'age_group'], 'child_assess_unique');
            $table->index('child_care_target_client_id', 'child_assess_client_idx');
        });

        // 3. Create child_nutrition_tracking table
        Schema::create('child_nutrition_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_care_target_client_id')->constrained('child_care_target_clients')->onDelete('cascade');
            $table->enum('month_range', ['1_5', '2_5', '3_5', '4_5', '5_9']);
            $table->boolean('exclusive_breastfeeding')->nullable();
            $table->timestamps();
            
            $table->unique(['child_care_target_client_id', 'month_range'], 'child_nutrition_unique');
            $table->index('child_care_target_client_id', 'child_nutrition_client_idx');
        });

        // 4. Create child_supplements table
        Schema::create('child_supplements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_care_target_client_id')->constrained('child_care_target_clients')->onDelete('cascade');
            $table->enum('supplement_type', ['low_birth_weight_iron', 'vitamin_a', 'mnp']);
            $table->integer('month_number')->nullable(); // For iron supplements (1, 2, 3 months)
            $table->date('given_date')->nullable();
            $table->integer('quantity')->nullable(); // For MNP sachets
            $table->timestamps();
            
            $table->unique(['child_care_target_client_id', 'supplement_type', 'month_number'], 'child_supp_unique');
            $table->index('child_care_target_client_id', 'child_supp_client_idx');
        });

        // 5. Create child_management_outcomes table
        Schema::create('child_management_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_care_target_client_id')->constrained('child_care_target_clients')->onDelete('cascade');
            $table->enum('program_type', ['man_sfp', 'sam_otc']);
            $table->boolean('admitted')->nullable();
            $table->boolean('cured')->nullable();
            $table->boolean('defaulted')->nullable();
            $table->boolean('died')->nullable();
            $table->timestamps();
            
            $table->unique(['child_care_target_client_id', 'program_type'], 'child_outcome_unique');
            $table->index('child_care_target_client_id', 'child_outcome_client_idx');
        });

        // 6. Create child_feeding_milestones table
        Schema::create('child_feeding_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_care_target_client_id')->constrained('child_care_target_clients')->onDelete('cascade');
            $table->boolean('exclusive_breastfed_up_to_6_months')->nullable();
            $table->boolean('complementary_feeding_introduced')->nullable();
            $table->date('breastfeeding_initiated_date')->nullable();
            $table->timestamps();
            
            $table->unique('child_care_target_client_id', 'child_feeding_unique');
        });

        // Migrate existing data
        $this->migrateExistingChildData();
    }

    public function down(): void
    {
        Schema::dropIfExists('child_feeding_milestones');
        Schema::dropIfExists('child_management_outcomes');
        Schema::dropIfExists('child_supplements');
        Schema::dropIfExists('child_nutrition_tracking');
        Schema::dropIfExists('child_assessments');
        Schema::dropIfExists('child_vaccinations');
    }

    private function migrateExistingChildData(): void
    {
        $clients = DB::table('child_care_target_clients')->get();
        
        foreach ($clients as $client) {
            // Migrate vaccinations
            $vaccinations = [
                ['type' => 'bcg', 'dose' => null, 'date' => $client->bcg_date],
                ['type' => 'hepa_b_bd', 'dose' => null, 'date' => $client->hepa_b_bd_date],
                ['type' => 'dpt_hepb_hib', 'dose' => 1, 'date' => $client->dpt_hepb_hib_1_date],
                ['type' => 'dpt_hepb_hib', 'dose' => 2, 'date' => $client->dpt_hepb_hib_2_date],
                ['type' => 'dpt_hepb_hib', 'dose' => 3, 'date' => $client->dpt_hepb_hib_3_date],
                ['type' => 'opv', 'dose' => 1, 'date' => $client->opv_1_date],
                ['type' => 'opv', 'dose' => 2, 'date' => $client->opv_2_date],
                ['type' => 'opv', 'dose' => 3, 'date' => $client->opv_3_date],
                ['type' => 'pcv', 'dose' => 1, 'date' => $client->pcv_1_date],
                ['type' => 'pcv', 'dose' => 2, 'date' => $client->pcv_2_date],
                ['type' => 'pcv', 'dose' => 3, 'date' => $client->pcv_3_date],
                ['type' => 'ipv', 'dose' => 1, 'date' => $client->ipv_1_date],
                ['type' => 'ipv', 'dose' => 2, 'date' => $client->ipv_2_date],
                ['type' => 'mmr', 'dose' => 1, 'date' => $client->mmr_1_date],
                ['type' => 'mmr', 'dose' => 2, 'date' => $client->mmr_2_date],
                ['type' => 'fic', 'dose' => null, 'date' => $client->fic_date],
                ['type' => 'cic', 'dose' => null, 'date' => $client->cic_date],
            ];
            
            foreach ($vaccinations as $vax) {
                if ($vax['date']) {
                    DB::table('child_vaccinations')->insert([
                        'child_care_target_client_id' => $client->id,
                        'vaccine_type' => $vax['type'],
                        'dose_number' => $vax['dose'],
                        'vaccination_date' => $vax['date'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Migrate assessments
            $assessments = [
                [
                    'group' => '1_3_months',
                    'age_months' => $client->assessment_1_3_age_months,
                    'length_cm' => $client->assessment_1_3_length_cm,
                    'length_date' => $client->assessment_1_3_length_date,
                    'weight_kg' => $client->assessment_1_3_weight_kg,
                    'weight_date' => $client->assessment_1_3_weight_date,
                    'status' => $client->assessment_1_3_status,
                ],
                [
                    'group' => '6_11_months',
                    'age_months' => $client->assessment_6_11_age_months,
                    'length_cm' => $client->assessment_6_11_length_cm,
                    'length_date' => $client->assessment_6_11_length_date,
                    'weight_kg' => $client->assessment_6_11_weight_kg,
                    'weight_date' => $client->assessment_6_11_weight_date,
                    'status' => $client->assessment_6_11_status,
                ],
                [
                    'group' => '12_months',
                    'age_months' => $client->assessment_12_age_months,
                    'length_cm' => $client->assessment_12_length_cm,
                    'length_date' => $client->assessment_12_length_date,
                    'weight_kg' => $client->assessment_12_weight_kg,
                    'weight_date' => $client->assessment_12_weight_date,
                    'status' => $client->assessment_12_status,
                ],
            ];
            
            foreach ($assessments as $assessment) {
                if ($assessment['length_date'] || $assessment['weight_date']) {
                    DB::table('child_assessments')->insert([
                        'child_care_target_client_id' => $client->id,
                        'age_group' => $assessment['group'],
                        'age_months' => $assessment['age_months'],
                        'length_cm' => $assessment['length_cm'],
                        'length_date' => $assessment['length_date'],
                        'weight_kg' => $assessment['weight_kg'],
                        'weight_date' => $assessment['weight_date'],
                        'status' => $assessment['status'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Migrate nutrition tracking (exclusive breastfeeding)
            $nutritionMonths = [
                ['range' => '1_5', 'value' => $client->exclusive_breastfeeding_1_5_months],
                ['range' => '2_5', 'value' => $client->exclusive_breastfeeding_2_5_months],
                ['range' => '3_5', 'value' => $client->exclusive_breastfeeding_3_5_months],
                ['range' => '4_5', 'value' => $client->exclusive_breastfeeding_4_5_months],
                ['range' => '5_9', 'value' => $client->exclusive_breastfeeding_5_9_months],
            ];
            
            foreach ($nutritionMonths as $month) {
                if ($month['value'] !== null) {
                    DB::table('child_nutrition_tracking')->insert([
                        'child_care_target_client_id' => $client->id,
                        'month_range' => $month['range'],
                        'exclusive_breastfeeding' => $month['value'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Migrate supplements
            $supplements = [
                ['type' => 'low_birth_weight_iron', 'month' => 1, 'date' => $client->low_birth_weight_iron_1_month_date],
                ['type' => 'low_birth_weight_iron', 'month' => 2, 'date' => $client->low_birth_weight_iron_2_month_date],
                ['type' => 'low_birth_weight_iron', 'month' => 3, 'date' => $client->low_birth_weight_iron_3_month_date],
                ['type' => 'vitamin_a', 'month' => null, 'date' => $client->vitamin_a_date],
                ['type' => 'mnp', 'month' => null, 'date' => $client->mnp_date, 'quantity' => $client->mnp_sachets_given],
            ];
            
            foreach ($supplements as $supp) {
                if ($supp['date']) {
                    $data = [
                        'child_care_target_client_id' => $client->id,
                        'supplement_type' => $supp['type'],
                        'month_number' => $supp['month'] ?? null,
                        'given_date' => $supp['date'],
                        'quantity' => $supp['quantity'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    DB::table('child_supplements')->insert($data);
                }
            }

            // Migrate management outcomes
            $outcomes = [
                [
                    'type' => 'man_sfp',
                    'admitted' => $client->man_admitted_sfp,
                    'cured' => $client->man_cured,
                    'defaulted' => $client->man_defaulted,
                    'died' => $client->man_died,
                ],
                [
                    'type' => 'sam_otc',
                    'admitted' => $client->sam_admitted_otc,
                    'cured' => $client->sam_cured,
                    'defaulted' => $client->sam_defaulted,
                    'died' => $client->sam_died,
                ],
            ];
            
            foreach ($outcomes as $outcome) {
                if ($outcome['admitted'] !== null || $outcome['cured'] !== null || 
                    $outcome['defaulted'] !== null || $outcome['died'] !== null) {
                    DB::table('child_management_outcomes')->insert([
                        'child_care_target_client_id' => $client->id,
                        'program_type' => $outcome['type'],
                        'admitted' => $outcome['admitted'],
                        'cured' => $outcome['cured'],
                        'defaulted' => $outcome['defaulted'],
                        'died' => $outcome['died'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Migrate feeding milestones
            if ($client->exclusive_breastfed_up_to_6_months !== null || 
                $client->complementary_feeding_introduced !== null ||
                $client->breastfeeding_initiated_date !== null) {
                DB::table('child_feeding_milestones')->insert([
                    'child_care_target_client_id' => $client->id,
                    'exclusive_breastfed_up_to_6_months' => $client->exclusive_breastfed_up_to_6_months,
                    'complementary_feeding_introduced' => $client->complementary_feeding_introduced,
                    'breastfeeding_initiated_date' => $client->breastfeeding_initiated_date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
