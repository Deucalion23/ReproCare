<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create maternal_prenatal_visits table
        if (!Schema::hasTable('maternal_prenatal_visits')) {
            Schema::create('maternal_prenatal_visits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('maternal_care_target_client_id')->constrained('maternal_care_target_clients')->onDelete('cascade');
                $table->integer('visit_number');
                $table->enum('trimester', ['first', 'second', 'third']);
                $table->date('visit_date');
                $table->timestamps();
                
                $table->unique(['maternal_care_target_client_id', 'visit_number'], 'mat_pre_visit_unique');
                $table->index(['maternal_care_target_client_id', 'trimester'], 'mat_pre_visit_trim_idx');
            });
        }

        // 2. Create maternal_supplements table
        if (!Schema::hasTable('maternal_supplements')) {
            Schema::create('maternal_supplements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('maternal_care_target_client_id')->constrained('maternal_care_target_clients')->onDelete('cascade');
                $table->enum('supplement_type', ['iron_folic', 'calcium', 'iodine']);
                $table->integer('visit_number');
                $table->date('distribution_date')->nullable();
                $table->integer('tablets_given')->nullable();
                $table->timestamps();
                
                $table->unique(['maternal_care_target_client_id', 'supplement_type', 'visit_number'], 'mat_supp_unique');
                $table->index('maternal_care_target_client_id', 'mat_supp_client_idx');
            });
        }

        // 3. Create maternal_vaccinations table
        if (!Schema::hasTable('maternal_vaccinations')) {
            Schema::create('maternal_vaccinations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('maternal_care_target_client_id')->constrained('maternal_care_target_clients')->onDelete('cascade');
                $table->enum('vaccine_type', ['td', 'dpt_hepb_hib', 'opv', 'pcv', 'ipv', 'mmr']);
                $table->integer('dose_number');
                $table->date('vaccination_date')->nullable();
                $table->timestamps();
                
                $table->unique(['maternal_care_target_client_id', 'vaccine_type', 'dose_number'], 'mat_vax_unique');
                $table->index('maternal_care_target_client_id', 'mat_vax_client_idx');
            });
        }

        // 4. Create maternal_screenings table
        if (!Schema::hasTable('maternal_screenings')) {
            Schema::create('maternal_screenings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('maternal_care_target_client_id')->constrained('maternal_care_target_clients')->onDelete('cascade');
                $table->enum('screening_type', ['syphilis', 'hepatitis_b', 'hiv', 'gestational_diabetes', 'cbc']);
                $table->date('screening_date')->nullable();
                $table->enum('result', ['positive', 'negative', 'with_anemia', 'without_anemia'])->nullable();
                $table->boolean('given_iron')->nullable();
                $table->timestamps();
                
                $table->unique(['maternal_care_target_client_id', 'screening_type'], 'mat_screen_unique');
                $table->index('maternal_care_target_client_id', 'mat_screen_client_idx');
            });
        }

        // 5. Create maternal_assessments table
        if (!Schema::hasTable('maternal_assessments')) {
            Schema::create('maternal_assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('maternal_care_target_client_id')->constrained('maternal_care_target_clients')->onDelete('cascade');
                $table->enum('assessment_type', ['nutritional', 'postpartum_24h', 'postpartum_7d']);
                $table->string('age_months', 255)->nullable();
                $table->decimal('length_cm', 6, 2)->nullable();
                $table->date('length_date')->nullable();
                $table->decimal('weight_kg', 6, 2)->nullable();
                $table->date('weight_date')->nullable();
                $table->enum('status', ['low', 'normal', 'high', 'S', 'W-MAM', 'W-SAM', 'O', 'N'])->nullable();
                $table->timestamps();
                
                $table->unique(['maternal_care_target_client_id', 'assessment_type'], 'mat_assess_unique');
                $table->index('maternal_care_target_client_id', 'mat_assess_client_idx');
            });
        }

        // 6. Create maternal_postpartum_care table
        if (!Schema::hasTable('maternal_postpartum_care')) {
            Schema::create('maternal_postpartum_care', function (Blueprint $table) {
                $table->id();
                $table->foreignId('maternal_care_target_client_id')->constrained('maternal_care_target_clients')->onDelete('cascade');
                $table->enum('iron_month', ['first', 'second', 'third']);
                $table->string('iron_given', 255)->nullable();
                $table->date('vitamin_a_date')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
                
                $table->unique(['maternal_care_target_client_id', 'iron_month'], 'mat_postpartum_unique');
                $table->index('maternal_care_target_client_id', 'mat_postpartum_client_idx');
            });
        }

        // Migrate existing data from the denormalized columns (only if tables were just created)
        $this->migrateExistingMaternalData();
    }

    public function down(): void
    {
        Schema::dropIfExists('maternal_postpartum_care');
        Schema::dropIfExists('maternal_assessments');
        Schema::dropIfExists('maternal_screenings');
        Schema::dropIfExists('maternal_vaccinations');
        Schema::dropIfExists('maternal_supplements');
        Schema::dropIfExists('maternal_prenatal_visits');
    }

    private function migrateExistingMaternalData(): void
    {
        // Check if the source columns still exist - if not, data was already migrated
        $columns = Schema::getColumnListing('maternal_care_target_clients');
        if (!in_array('prenatal_first_trimester_date', $columns)) {
            return; // Data already migrated, skip
        }

        $clients = DB::table('maternal_care_target_clients')->get();
        
        foreach ($clients as $client) {
            // Migrate prenatal visits
            $visits = [
                ['date' => $client->prenatal_first_trimester_date, 'trimester' => 'first', 'number' => 1],
                ['date' => $client->prenatal_second_trimester_date, 'trimester' => 'second', 'number' => 2],
                ['date' => $client->prenatal_second_trimester_date_1, 'trimester' => 'second', 'number' => 3],
                ['date' => $client->prenatal_second_trimester_date_2, 'trimester' => 'second', 'number' => 4],
                ['date' => $client->prenatal_third_trimester_date, 'trimester' => 'third', 'number' => 5],
                ['date' => $client->prenatal_third_trimester_date_1, 'trimester' => 'third', 'number' => 6],
                ['date' => $client->prenatal_third_trimester_date_2, 'trimester' => 'third', 'number' => 7],
                ['date' => $client->prenatal_third_trimester_date_3, 'trimester' => 'third', 'number' => 8],
                ['date' => $client->prenatal_third_trimester_date_4, 'trimester' => 'third', 'number' => 9],
                ['date' => $client->prenatal_third_trimester_date_5, 'trimester' => 'third', 'number' => 10],
            ];
            
            foreach ($visits as $visit) {
                if ($visit['date']) {
                    DB::table('maternal_prenatal_visits')->insert([
                        'maternal_care_target_client_id' => $client->id,
                        'visit_number' => $visit['number'],
                        'trimester' => $visit['trimester'],
                        'visit_date' => $visit['date'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Migrate TD vaccinations
            $tdDates = [
                ['type' => 'td', 'dose' => 1, 'date' => $client->td1_date],
                ['type' => 'td', 'dose' => 2, 'date' => $client->td2_date],
                ['type' => 'td', 'dose' => 3, 'date' => $client->td3_date],
                ['type' => 'td', 'dose' => 4, 'date' => $client->td4_date],
                ['type' => 'td', 'dose' => 5, 'date' => $client->td5_date],
            ];
            
            foreach ($tdDates as $td) {
                if ($td['date']) {
                    DB::table('maternal_vaccinations')->insert([
                        'maternal_care_target_client_id' => $client->id,
                        'vaccine_type' => $td['type'],
                        'dose_number' => $td['dose'],
                        'vaccination_date' => $td['date'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Migrate iron/folic supplements
            $ironVisits = [
                ['number' => 1, 'date' => $client->iron_folic_first_visit_date, 'tablets' => $client->iron_folic_first_visit_tablets],
                ['number' => 2, 'date' => $client->iron_folic_second_visit_date, 'tablets' => $client->iron_folic_second_visit_tablets],
                ['number' => 3, 'date' => $client->iron_folic_third_visit_date, 'tablets' => $client->iron_folic_third_visit_tablets],
                ['number' => 4, 'date' => $client->iron_folic_fourth_visit_date, 'tablets' => $client->iron_folic_fourth_visit_tablets],
            ];
            
            foreach ($ironVisits as $visit) {
                if ($visit['date']) {
                    DB::table('maternal_supplements')->insert([
                        'maternal_care_target_client_id' => $client->id,
                        'supplement_type' => 'iron_folic',
                        'visit_number' => $visit['number'],
                        'distribution_date' => $visit['date'],
                        'tablets_given' => $visit['tablets'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Migrate calcium supplements
            $calciumVisits = [
                ['number' => 2, 'date' => $client->calcium_second_visit_date, 'tablets' => $client->calcium_second_visit_tablets],
                ['number' => 3, 'date' => $client->calcium_third_visit_date, 'tablets' => $client->calcium_third_visit_tablets],
                ['number' => 4, 'date' => $client->calcium_fourth_visit_date, 'tablets' => $client->calcium_fourth_visit_tablets],
            ];
            
            foreach ($calciumVisits as $visit) {
                if ($visit['date']) {
                    DB::table('maternal_supplements')->insert([
                        'maternal_care_target_client_id' => $client->id,
                        'supplement_type' => 'calcium',
                        'visit_number' => $visit['number'],
                        'distribution_date' => $visit['date'],
                        'tablets_given' => $visit['tablets'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Migrate iodine
            if ($client->iodine_date) {
                DB::table('maternal_supplements')->insert([
                    'maternal_care_target_client_id' => $client->id,
                    'supplement_type' => 'iodine',
                    'visit_number' => 1,
                    'distribution_date' => $client->iodine_date,
                    'tablets_given' => $client->iodine_capsules_given,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Migrate screenings
            $screenings = [
                ['type' => 'syphilis', 'date' => $client->syphilis_screening_date, 'result' => $client->syphilis_screening_result],
                ['type' => 'hepatitis_b', 'date' => $client->hepatitis_b_screening_date, 'result' => $client->hepatitis_b_screening_result],
                ['type' => 'hiv', 'date' => $client->hiv_screening_date, 'result' => null],
                ['type' => 'gestational_diabetes', 'date' => $client->gestational_diabetes_screening_date, 'result' => $client->gestational_diabetes_result],
                ['type' => 'cbc', 'date' => $client->cbc_screening_date, 'result' => $client->cbc_anemia_status, 'given_iron' => $client->cbc_given_iron],
            ];
            
            foreach ($screenings as $screening) {
                if ($screening['date']) {
                    $data = [
                        'maternal_care_target_client_id' => $client->id,
                        'screening_type' => $screening['type'],
                        'screening_date' => $screening['date'],
                        'result' => $screening['result'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    if (isset($screening['given_iron'])) {
                        $data['given_iron'] = $screening['given_iron'];
                    }
                    DB::table('maternal_screenings')->insert($data);
                }
            }

            // Migrate postpartum iron
            $postpartumIron = [
                ['month' => 'first', 'value' => $client->postpartum_iron_first_month],
                ['month' => 'second', 'value' => $client->postpartum_iron_second_month],
                ['month' => 'third', 'value' => $client->postpartum_iron_third_month],
            ];
            
            foreach ($postpartumIron as $iron) {
                if ($iron['value']) {
                    DB::table('maternal_postpartum_care')->insert([
                        'maternal_care_target_client_id' => $client->id,
                        'iron_month' => $iron['month'],
                        'iron_given' => $iron['value'],
                        'vitamin_a_date' => $client->vitamin_a_date,
                        'remarks' => $client->postpartum_remarks,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
};
