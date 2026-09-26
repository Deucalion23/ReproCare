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
        // Helper function to safely create index
        $safeIndex = function (string $table, string $indexName, array $columns): void {
            // Check if all columns exist
            $existingColumns = Schema::getColumnListing($table);
            foreach ($columns as $column) {
                if (!in_array($column, $existingColumns)) {
                    return; // Skip if column doesn't exist
                }
            }
            $columnList = implode(', ', $columns);
            DB::statement("CREATE INDEX IF NOT EXISTS {$indexName} ON {$table}({$columnList})");
        };

        // Health records indexes
        $safeIndex('health_records', 'idx_health_records_user_id', ['user_id']);
        $safeIndex('health_records', 'idx_health_records_created_at', ['created_at']);
        $safeIndex('health_records', 'idx_health_records_user_created', ['user_id', 'created_at']);
        $safeIndex('health_records', 'idx_health_records_risk_level', ['risk_level']);
        $safeIndex('health_records', 'idx_health_records_recorded_by', ['recorded_by_id']);

        // Checkups indexes
        $safeIndex('checkups', 'idx_checkups_woman_id', ['woman_id']);
        $safeIndex('checkups', 'idx_checkups_midwife_id', ['midwife_id']);
        $safeIndex('checkups', 'idx_checkups_scheduled_date', ['scheduled_date']);
        $safeIndex('checkups', 'idx_checkups_status', ['status']);
        $safeIndex('checkups', 'idx_checkups_woman_date', ['woman_id', 'scheduled_date']);

        // Pregnancies indexes
        $safeIndex('pregnancies', 'idx_pregnancies_woman_id', ['woman_id']);
        $safeIndex('pregnancies', 'idx_pregnancies_high_risk', ['is_high_risk']);
        $safeIndex('pregnancies', 'idx_pregnancies_woman_risk', ['woman_id', 'is_high_risk']);
        $safeIndex('pregnancies', 'idx_pregnancies_ended_at', ['ended_at']);

        // Cycles indexes
        $safeIndex('cycles', 'idx_cycles_user_id', ['user_id']);
        $safeIndex('cycles', 'idx_cycles_start_date', ['period_start_date']);
        $safeIndex('cycles', 'idx_cycles_user_start', ['user_id', 'period_start_date']);

        // Fertility logs indexes
        $safeIndex('fertility_logs', 'idx_fertility_logs_user_id', ['user_id']);
        $safeIndex('fertility_logs', 'idx_fertility_logs_log_date', ['log_date']);
        $safeIndex('fertility_logs', 'idx_fertility_logs_user_date', ['user_id', 'log_date']);

        // Users indexes
        $safeIndex('users', 'idx_users_role', ['role']);
        $safeIndex('users', 'idx_users_barangay', ['barangay']);
        $safeIndex('users', 'idx_users_role_barangay', ['role', 'barangay']);

        // BHW reports indexes
        $safeIndex('bhw_monthly_reports', 'idx_bhw_reports_bhw_id', ['bhw_id']);
        $safeIndex('bhw_monthly_reports', 'idx_bhw_reports_month', ['report_month']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $safeDropIndex = function (string $table, string $indexName): void {
            try {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            } catch (\Exception $e) {
                // Index doesn't exist, ignore
            }
        };

        // Remove indexes from health_records
        $safeDropIndex('health_records', 'idx_health_records_user_id');
        $safeDropIndex('health_records', 'idx_health_records_created_at');
        $safeDropIndex('health_records', 'idx_health_records_user_created');
        $safeDropIndex('health_records', 'idx_health_records_risk_level');
        $safeDropIndex('health_records', 'idx_health_records_recorded_by');

        // Remove indexes from checkups
        $safeDropIndex('checkups', 'idx_checkups_woman_id');
        $safeDropIndex('checkups', 'idx_checkups_midwife_id');
        $safeDropIndex('checkups', 'idx_checkups_scheduled_date');
        $safeDropIndex('checkups', 'idx_checkups_status');
        $safeDropIndex('checkups', 'idx_checkups_woman_date');

        // Remove indexes from pregnancies
        $safeDropIndex('pregnancies', 'idx_pregnancies_woman_id');
        $safeDropIndex('pregnancies', 'idx_pregnancies_high_risk');
        $safeDropIndex('pregnancies', 'idx_pregnancies_woman_risk');
        $safeDropIndex('pregnancies', 'idx_pregnancies_ended_at');

        // Remove indexes from cycles
        $safeDropIndex('cycles', 'idx_cycles_user_id');
        $safeDropIndex('cycles', 'idx_cycles_start_date');
        $safeDropIndex('cycles', 'idx_cycles_user_start');

        // Remove indexes from fertility_logs
        $safeDropIndex('fertility_logs', 'idx_fertility_logs_user_id');
        $safeDropIndex('fertility_logs', 'idx_fertility_logs_log_date');
        $safeDropIndex('fertility_logs', 'idx_fertility_logs_user_date');

        // Remove indexes from users
        $safeDropIndex('users', 'idx_users_role');
        $safeDropIndex('users', 'idx_users_barangay');
        $safeDropIndex('users', 'idx_users_role_barangay');

        // Remove indexes from bhw_monthly_reports
        $safeDropIndex('bhw_monthly_reports', 'idx_bhw_reports_bhw_id');
        $safeDropIndex('bhw_monthly_reports', 'idx_bhw_reports_month');
    }
};
