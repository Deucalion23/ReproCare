<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            // Add new specific foreign key columns only if they don't exist
            if (!Schema::hasColumn('health_records', 'woman_id')) {
                $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('health_records', 'recorded_by_midwife_id')) {
                $table->unsignedBigInteger('recorded_by_midwife_id')->nullable()->after('recorded_by_id');
            }
            if (!Schema::hasColumn('health_records', 'recorded_by_bhw_id')) {
                $table->unsignedBigInteger('recorded_by_bhw_id')->nullable()->after('recorded_by_midwife_id');
            }

            // Get existing foreign keys
            $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'health_records' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();

            // Add foreign key constraints only if they don't already exist
            if (Schema::hasColumn('health_records', 'woman_id') && !in_array('health_records_woman_id_foreign', $foreignKeys)) {
                $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');
            }
            if (Schema::hasColumn('health_records', 'recorded_by_midwife_id') && !in_array('health_records_recorded_by_midwife_id_foreign', $foreignKeys)) {
                $table->foreign('recorded_by_midwife_id')->references('id')->on('midwives')->onDelete('set null');
            }
            if (Schema::hasColumn('health_records', 'recorded_by_bhw_id') && !in_array('health_records_recorded_by_bhw_id_foreign', $foreignKeys)) {
                $table->foreign('recorded_by_bhw_id')->references('id')->on('bhws')->onDelete('set null');
            }

            // Add indexes
            $table->index('woman_id');
            $table->index('recorded_by_midwife_id');
            $table->index('recorded_by_bhw_id');
        });

        // Migrate data from polymorphic columns to specific columns
        DB::statement("
            UPDATE health_records hr
            SET hr.woman_id = hr.patient_id
            WHERE hr.patient_type = 'App\\\\Models\\\\Woman' OR hr.patient_type = 'App\\\\Models\\\\Patient'
        ");

        DB::statement("
            UPDATE health_records hr
            SET hr.recorded_by_midwife_id = hr.recorded_by_id
            WHERE hr.recorded_by_type = 'App\\\\Models\\\\Midwife'
        ");

        DB::statement("
            UPDATE health_records hr
            SET hr.recorded_by_bhw_id = hr.recorded_by_id
            WHERE hr.recorded_by_type = 'App\\\\Models\\\\Bhw'
        ");

        // Drop polymorphic columns
        Schema::table('health_records', function (Blueprint $table) {
            // Get existing foreign keys
            $foreignKeys = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'health_records' AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'"))->pluck('CONSTRAINT_NAME')->toArray();
            
            if (in_array('health_records_recorded_by_id_foreign', $foreignKeys)) {
                $table->dropForeign(['recorded_by_id']);
            }
            
            // Drop columns that exist
            $columnsToDrop = [];
            if (Schema::hasColumn('health_records', 'patient_id')) $columnsToDrop[] = 'patient_id';
            if (Schema::hasColumn('health_records', 'patient_type')) $columnsToDrop[] = 'patient_type';
            if (Schema::hasColumn('health_records', 'recorded_by_type')) $columnsToDrop[] = 'recorded_by_type';
            if (Schema::hasColumn('health_records', 'recorded_by_id')) $columnsToDrop[] = 'recorded_by_id';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            // Re-add polymorphic columns
            $table->unsignedBigInteger('patient_id')->nullable()->after('id');
            $table->string('patient_type')->nullable()->after('patient_id');
            $table->unsignedBigInteger('recorded_by_id')->nullable()->after('recorded_by_bhw_id');
            $table->string('recorded_by_type')->nullable()->after('recorded_by_id');
        });

        // Migrate data back
        DB::statement("UPDATE health_records SET patient_id = woman_id, patient_type = 'App\\\\Models\\\\Woman'");
        DB::statement("UPDATE health_records SET recorded_by_id = recorded_by_midwife_id, recorded_by_type = 'App\\\\Models\\\\Midwife' WHERE recorded_by_midwife_id IS NOT NULL");
        DB::statement("UPDATE health_records SET recorded_by_id = recorded_by_bhw_id, recorded_by_type = 'App\\\\Models\\\\Bhw' WHERE recorded_by_bhw_id IS NOT NULL");

        // Drop specific columns
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropForeign(['recorded_by_midwife_id']);
            $table->dropForeign(['recorded_by_bhw_id']);
            $table->dropColumn(['woman_id', 'recorded_by_midwife_id', 'recorded_by_bhw_id']);
        });
    }
};
