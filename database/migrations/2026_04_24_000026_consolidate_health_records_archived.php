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
        // Step 1: Add is_archived column to health_records table
        Schema::table('health_records', function (Blueprint $table) {
            $table->boolean('is_archived')->default(false)->after('recommendations');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->string('archived_reason')->nullable()->after('archived_at');
            
            $table->index('is_archived');
            $table->index('archived_at');
        });

        // Step 2: Migrate data from health_records_archived to health_records
        DB::statement("
            INSERT INTO health_records (
                woman_id, recorded_by_midwife_id, recorded_by_bhw_id, 
                bp, weight, heart_rate, temperature, notes, risk_level,
                is_archived, archived_at, archived_reason, created_at, updated_at
            )
            SELECT 
                woman_id, recorded_by_midwife_id, recorded_by_bhw_id,
                bp, weight, heart_rate, temperature, notes, risk_level,
                true as is_archived, archived_at, archived_reason, created_at, updated_at
            FROM health_records_archived
        ");

        // Step 3: Drop the health_records_archived table
        Schema::dropIfExists('health_records_archived');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily rolled back due to data migration
        // In production, restore from database backup if needed
        throw new \Exception('This migration cannot be rolled back. Restore from database backup if needed.');
    }
};
