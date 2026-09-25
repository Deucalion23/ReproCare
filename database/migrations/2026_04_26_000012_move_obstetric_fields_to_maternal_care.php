<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add new columns to maternal_care_target_clients
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->integer('gtpal_term')->unsigned()->default(0)->after('parity');
            $table->integer('gtpal_preterm')->unsigned()->default(0)->after('gtpal_term');
            $table->integer('gtpal_abortions')->unsigned()->default(0)->after('gtpal_preterm');
            $table->integer('gtpal_living_children')->unsigned()->default(0)->after('gtpal_abortions');
            $table->longText('health_conditions')->nullable()->after('deworming_date');
            $table->string('health_condition_other', 255)->nullable()->after('health_conditions');
            $table->text('outcome_details')->nullable()->after('pregnancy_outcome');
        });

        // Step 2: Migrate data from pregnancies to maternal_care_target_clients
        $maternalRecords = DB::table('maternal_care_target_clients')->get();

        foreach ($maternalRecords as $record) {
            if ($record->pregnancy_id) {
                $pregnancy = DB::table('pregnancies')->where('id', $record->pregnancy_id)->first();

                if ($pregnancy) {
                    $updateData = [];

                    // Migrate gravida if not already set
                    if ($pregnancy->gravida && !$record->gravida) {
                        $updateData['gravida'] = $pregnancy->gravida;
                    }

                    // Migrate para to parity if not already set
                    if ($pregnancy->para && !$record->parity) {
                        $updateData['parity'] = $pregnancy->para;
                    }

                    // Migrate GTPAL fields
                    if ($pregnancy->gtpal_term !== null) {
                        $updateData['gtpal_term'] = $pregnancy->gtpal_term;
                    }
                    if ($pregnancy->gtpal_preterm !== null) {
                        $updateData['gtpal_preterm'] = $pregnancy->gtpal_preterm;
                    }
                    if ($pregnancy->gtpal_abortions !== null) {
                        $updateData['gtpal_abortions'] = $pregnancy->gtpal_abortions;
                    }
                    if ($pregnancy->gtpal_living_children !== null) {
                        $updateData['gtpal_living_children'] = $pregnancy->gtpal_living_children;
                    }

                    // Migrate health conditions
                    if ($pregnancy->health_conditions) {
                        $updateData['health_conditions'] = $pregnancy->health_conditions;
                    }
                    if ($pregnancy->health_condition_other) {
                        $updateData['health_condition_other'] = $pregnancy->health_condition_other;
                    }

                    // Migrate outcome details
                    if ($pregnancy->outcome_details) {
                        $updateData['outcome_details'] = $pregnancy->outcome_details;
                    }

                    if (!empty($updateData)) {
                        DB::table('maternal_care_target_clients')
                            ->where('id', $record->id)
                            ->update($updateData);
                    }
                }
            }
        }

        // Step 3: Drop columns from pregnancies
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropColumn([
                'gravida',
                'para',
                'gtpal_term',
                'gtpal_preterm',
                'gtpal_abortions',
                'gtpal_living_children',
                'health_conditions',
                'health_condition_other',
                'outcome',
                'outcome_details',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: Add columns back to pregnancies
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->integer('gravida')->unsigned()->after('aog');
            $table->integer('para')->unsigned()->after('gravida');
            $table->integer('gtpal_term')->unsigned()->default(0)->after('para');
            $table->integer('gtpal_preterm')->unsigned()->default(0)->after('gtpal_term');
            $table->integer('gtpal_abortions')->unsigned()->default(0)->after('gtpal_preterm');
            $table->integer('gtpal_living_children')->unsigned()->default(0)->after('gtpal_abortions');
            $table->longText('health_conditions')->nullable()->after('gtpal_living_children');
            $table->string('health_condition_other', 255)->nullable()->after('health_conditions');
            $table->string('outcome', 255)->nullable()->after('risk_notes');
            $table->text('outcome_details')->nullable()->after('outcome');
        });

        // Reverse: Migrate data back from maternal_care_target_clients to pregnancies
        $maternalRecords = DB::table('maternal_care_target_clients')->get();

        foreach ($maternalRecords as $record) {
            if ($record->pregnancy_id) {
                $updateData = [];

                if ($record->gravida) {
                    $updateData['gravida'] = $record->gravida;
                }
                if ($record->parity) {
                    $updateData['para'] = $record->parity;
                }
                if ($record->gtpal_term !== null) {
                    $updateData['gtpal_term'] = $record->gtpal_term;
                }
                if ($record->gtpal_preterm !== null) {
                    $updateData['gtpal_preterm'] = $record->gtpal_preterm;
                }
                if ($record->gtpal_abortions !== null) {
                    $updateData['gtpal_abortions'] = $record->gtpal_abortions;
                }
                if ($record->gtpal_living_children !== null) {
                    $updateData['gtpal_living_children'] = $record->gtpal_living_children;
                }
                if ($record->health_conditions) {
                    $updateData['health_conditions'] = $record->health_conditions;
                }
                if ($record->health_condition_other) {
                    $updateData['health_condition_other'] = $record->health_condition_other;
                }
                if ($record->pregnancy_outcome) {
                    $updateData['outcome'] = $record->pregnancy_outcome;
                }
                if ($record->outcome_details) {
                    $updateData['outcome_details'] = $record->outcome_details;
                }

                if (!empty($updateData)) {
                    DB::table('pregnancies')
                        ->where('id', $record->pregnancy_id)
                        ->update($updateData);
                }
            }
        }

        // Reverse: Drop columns from maternal_care_target_clients
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->dropColumn([
                'gtpal_term',
                'gtpal_preterm',
                'gtpal_abortions',
                'gtpal_living_children',
                'health_conditions',
                'health_condition_other',
                'outcome_details',
            ]);
        });
    }
};
