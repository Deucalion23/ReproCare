<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cycles')) {
            return;
        }

        // Clean up legacy indexes that still point to the pre-polymorphic user column.
        foreach (['idx_cycles_user_id', 'idx_cycles_user_start', 'cycles_user_id_period_start_date_index'] as $indexName) {
            $indexExists = DB::table('information_schema.STATISTICS')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'cycles')
                ->where('INDEX_NAME', $indexName)
                ->exists();

            if ($indexExists) {
                DB::statement("DROP INDEX {$indexName} ON cycles");
            }
        }

        if (Schema::hasColumn('cycles', 'user_id_old')) {
            Schema::table('cycles', function (Blueprint $table) {
                $table->dropColumn('user_id_old');
            });
        }

        $patientIndexExists = DB::table('information_schema.STATISTICS')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'cycles')
            ->where('INDEX_NAME', 'cycles_patient_id_patient_type_period_start_date_index')
            ->exists();

        if (!$patientIndexExists && Schema::hasColumn('cycles', 'patient_id') && Schema::hasColumn('cycles', 'patient_type')) {
            Schema::table('cycles', function (Blueprint $table) {
                $table->index(['patient_id', 'patient_type', 'period_start_date'], 'cycles_patient_id_patient_type_period_start_date_index');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('cycles')) {
            return;
        }

        $patientIndexExists = DB::table('information_schema.STATISTICS')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'cycles')
            ->where('INDEX_NAME', 'cycles_patient_id_patient_type_period_start_date_index')
            ->exists();

        if ($patientIndexExists) {
            Schema::table('cycles', function (Blueprint $table) {
                $table->dropIndex('cycles_patient_id_patient_type_period_start_date_index');
            });
        }

        if (!Schema::hasColumn('cycles', 'user_id_old')) {
            Schema::table('cycles', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id_old')->nullable()->after('patient_type');
                $table->index('user_id_old', 'idx_cycles_user_id');
                $table->index(['user_id_old', 'period_start_date'], 'idx_cycles_user_start');
            });
        }
    }
};
