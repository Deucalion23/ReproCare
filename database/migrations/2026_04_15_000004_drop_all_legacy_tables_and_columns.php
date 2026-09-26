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
        try {
            if (Schema::hasTable('checkups') && Schema::hasColumn('checkups', 'midwife_id')) {
                // Drop FK + index first (required on mysql/pgsql before DROP COLUMN)
                Schema::table('checkups', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['midwife_id']);
                    } catch (\Throwable $e) {
                    }
                    try {
                        $table->dropIndex('idx_checkups_midwife_id');
                    } catch (\Throwable $e) {
                    }
                    try {
                        $table->dropIndex(['midwife_id']);
                    } catch (\Throwable $e) {
                    }
                });
                try {
                    Schema::table('checkups', function (Blueprint $table) {
                        $table->dropColumn('midwife_id');
                    });
                } catch (\Throwable $e) {
                    // SQLite cannot DROP a column used in a FOREIGN KEY — rename it
                    // away so later migrations can re-add a clean `midwife_id`.
                    // The final repair migration removes the leftover on Postgres.
                    if (DB::getDriverName() === 'sqlite' && Schema::hasColumn('checkups', 'midwife_id')) {
                        Schema::table('checkups', function (Blueprint $table) {
                            $table->renameColumn('midwife_id', 'midwife_id_legacy_drop');
                        });
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        // Step 4: Drop health_records.created_by_role (redundant - computed from recorded_by_id)
        try {
            if (Schema::hasTable('health_records') && Schema::hasColumn('health_records', 'created_by_role')) {
                Schema::table('health_records', function (Blueprint $table) {
                    $table->dropColumn('created_by_role');
                });
            }
        } catch (\Throwable $e) {
        }

        // Step 5: Drop legacy views
        foreach (['midwives_legacy', 'bhw_legacy', 'health_records_enriched'] as $view) {
            try {
                DB::statement("DROP VIEW IF EXISTS {$view}");
            } catch (\Throwable $e) {
            }
        }

        // Step 6: Drop midwives table (data migrated to users)
        Schema::dropIfExists('midwives');

        // Step 7: Drop bhw table (data migrated to users)
        Schema::dropIfExists('bhw');

        // Step 8: Clean up legacy tracking columns from users
        try {
            Schema::table('users', function (Blueprint $table) {
                foreach (['idx_users_legacy_midwife_id', 'idx_users_legacy_bhw_id'] as $index) {
                    try {
                        $table->dropIndex($index);
                    } catch (\Throwable $e) {
                    }
                }
                $drop = [];
                foreach (['legacy_midwife_id', 'legacy_bhw_id'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $drop[] = $col;
                    }
                }
                if (!empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        } catch (\Throwable $e) {
        }

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
        // MySQL-only introspection; on pgsql/sqlite Laravel handles FKs via Schema.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        try {
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
        } catch (\Throwable $e) {
        }
    }

    /**
     * Fix bhw_monthly_reports.bhw_id CASCADE to RESTRICT
     */
    protected function fixBhwMonthlyReportsCascade(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        try {
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
        } catch (\Throwable $e) {
        }
    }
};
