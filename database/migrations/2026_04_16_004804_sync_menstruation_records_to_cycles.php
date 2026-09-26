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
        // Legacy one-time data sync. On fresh installs (pgsql/sqlite production)
        // there is no data to sync — skip gracefully. Uses query builder instead
        // of Eloquent models so deleted model classes can't break fresh migrates.
        try {
            if (!Schema::hasTable('menstruation_records') || !Schema::hasTable('cycles')) {
                return;
            }
            $records = DB::table('menstruation_records')->get();
            foreach ($records as $record) {
                if (!isset($record->user_id, $record->start_date)) {
                    continue;
                }
                $exists = DB::table('cycles')
                    ->where('user_id', $record->user_id)
                    ->where('period_start_date', $record->start_date)
                    ->exists();
                if (!$exists) {
                    DB::table('cycles')->insert([
                        'user_id' => $record->user_id,
                        'period_start_date' => $record->start_date,
                        'period_end_date' => $record->end_date ?? null,
                        'flow_intensity' => 'medium',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove synced Cycle records
        // This is a one-time sync, so we can leave the records or add logic to identify synced ones
    }
};
