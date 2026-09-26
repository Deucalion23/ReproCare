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
        // One-off local data fix — no-op on fresh installs.
        try {
            if (Schema::hasTable('menstruation_records')) {
                DB::table('menstruation_records')->where('user_id', 6)
                    ->where('start_date', '2026-04-10')
                    ->update(['start_date' => '2026-04-11', 'end_date' => '2026-04-14']);
            }
            if (Schema::hasTable('cycles')) {
                DB::table('cycles')->where('user_id', 6)
                    ->where('period_start_date', '2026-04-10')
                    ->update(['period_start_date' => '2026-04-11', 'period_end_date' => '2026-04-14']);
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            if (Schema::hasTable('menstruation_records')) {
                DB::table('menstruation_records')->where('user_id', 6)
                    ->where('start_date', '2026-04-11')
                    ->update(['start_date' => '2026-04-10', 'end_date' => '2026-04-13']);
            }
            if (Schema::hasTable('cycles')) {
                DB::table('cycles')->where('user_id', 6)
                    ->where('period_start_date', '2026-04-11')
                    ->update(['period_start_date' => '2026-04-10', 'period_end_date' => '2026-04-13']);
            }
        } catch (\Throwable $e) {
        }
    }
};
