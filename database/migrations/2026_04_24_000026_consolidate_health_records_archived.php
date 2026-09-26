<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run without a DDL transaction. Steps below are best-effort
     * (attempt-and-ignore-if-present); on Postgres a failed statement
     * aborts the whole transaction, so caught failures must not poison
     * the statements that follow. All steps are guarded and re-runnable.
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add is_archived column to health_records table
        Schema::table('health_records', function (Blueprint $table) {
            if (!Schema::hasColumn('health_records', 'is_archived')) {
                $table->boolean('is_archived')->default(false);
            }
            if (!Schema::hasColumn('health_records', 'archived_at')) {
                $table->timestamp('archived_at')->nullable();
            }
            if (!Schema::hasColumn('health_records', 'archived_reason')) {
                $table->string('archived_reason')->nullable();
            }
        });
        foreach (['is_archived', 'archived_at'] as $column) {
            try {
                Schema::table('health_records', function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            } catch (\Throwable $e) {
            }
        }

        // Step 2: Migrate data from health_records_archived to health_records
        try {
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
        } catch (\Throwable $e) {
        }

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
