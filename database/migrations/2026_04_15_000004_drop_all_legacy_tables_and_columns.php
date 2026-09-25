<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Phase 4: FINAL CLEANUP - Drop all legacy tables and columns
     * This migration completes the normalization to 100%
     */
    public function up(): void
    {
        // Step 1: Migrate any remaining checkups.midwife_id data to midwife_user_id
        $this->migrateRemainingCheckupData();

        // Step 2: Drop old FK on checkups.midwife_id
        $this->dropCheckupMidwifeFK();

        // Step 3: Drop checkups.midwife_id column
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropColumn('midwife_id');
        });

        // Step 4: Drop health_records.created_by_role (redundant - computed from recorded_by_id)
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn('created_by_role');
        });

        // Step 5: Drop legacy views
        DB::statement('DROP VIEW IF EXISTS midwives_legacy');
        DB::statement('DROP VIEW IF EXISTS bhw_legacy');
        DB::statement('DROP VIEW IF EXISTS health_records_enriched');

        // Step 6: Drop midwives table (data migrated to users)
        Schema::dropIfExists('midwives');

        // Step 7: Drop bhw table (data migrated to users)
        Schema::dropIfExists('bhw');

        // Step 8: Clean up legacy tracking columns from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_legacy_midwife_id');
            $table->dropIndex('idx_users_legacy_bhw_id');
            $table->dropColumn(['legacy_midwife_id', 'legacy_bhw_id']);
        });

        // Step 9: Fix bhw_monthly_reports.bhw_id CASCADE to RESTRICT
        $this->fixBhwMonthlyReportsCascade();
    }

    /**
     * Reverse the migrations.
     * WARNING: This will NOT restore dropped tables/columns
     * You would need to restore from backup
     */
    public function down(): void
    {
        // Cannot reverse table drops - would need backup restore
        throw new \RuntimeException('This migration cannot be rolled back. Restore from backup if needed.');
    }

    /**
     * Migrate any remaining checkup data from midwife_id to midwife_user_id
     */
    protected function migrateRemainingCheckupData(): void
    {
        // Find checkups that still have midwife_id but no midwife_user_id
        $orphanedCheckups = DB::table('checkups')
            ->whereNotNull('midwife_id')
            ->whereNull('midwife_user_id')
            ->count();

        if ($orphanedCheckups > 0) {
            // This shouldn't happen if Phase 1 ran correctly, but safety check
            \Log::warning("Found {$orphanedCheckups} checkups with midwife_id but no midwife_user_id");
        }
    }

    /**
     * Drop the old FK constraint on checkups.midwife_id
     */
    protected function dropCheckupMidwifeFK(): void
    {
        // Check if FK exists
        $fkExists = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'checkups'
            AND COLUMN_NAME = 'midwife_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if (count($fkExists) > 0) {
            $constraintName = $fkExists[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE checkups DROP FOREIGN KEY {$constraintName}");
        }
    }

    /**
     * Fix bhw_monthly_reports.bhw_id CASCADE to RESTRICT
     */
    protected function fixBhwMonthlyReportsCascade(): void
    {
        // Check current FK rule
        $fkRules = DB::select("
            SELECT rc.DELETE_RULE, kcu.CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
            JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc 
                ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
            WHERE kcu.TABLE_SCHEMA = DATABASE()
            AND kcu.TABLE_NAME = 'bhw_monthly_reports'
            AND kcu.COLUMN_NAME = 'bhw_id'
        ");

        if (count($fkRules) > 0) {
            $currentRule = $fkRules[0];
            
            // Only change if it's still CASCADE
            if ($currentRule->DELETE_RULE === 'CASCADE') {
                // Drop old FK
                DB::statement("ALTER TABLE bhw_monthly_reports DROP FOREIGN KEY {$currentRule->CONSTRAINT_NAME}");
                
                // Add new FK with RESTRICT
                DB::statement("
                    ALTER TABLE bhw_monthly_reports
                    ADD CONSTRAINT fk_bhw_monthly_reports_bhw
                    FOREIGN KEY (bhw_id) REFERENCES users(id)
                    ON DELETE RESTRICT
                ");
                
                \Log::info("Changed bhw_monthly_reports.bhw_id FK from CASCADE to RESTRICT");
            }
        }
    }
};
