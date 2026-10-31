<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Archived cycles keep their audit trail but must not block re-logging the
 * same date after a typo was archived. The old full unique constraint
 * (user_id, period_start_date) made such a correction impossible, so it is
 * replaced by a partial unique index covering active rows only.
 */
return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        $this->dropFullUnique();

        if (in_array(DB::getDriverName(), ['pgsql', 'sqlite'], true)) {
            DB::statement(
                'CREATE UNIQUE INDEX IF NOT EXISTS cycles_user_start_unique '
                . 'ON cycles (user_id, period_start_date) WHERE deleted_at IS NULL'
            );
        }
    }

    public function down(): void
    {
        try {
            DB::statement('DROP INDEX IF EXISTS cycles_user_start_unique');
        } catch (\Throwable $e) {
        }
        try {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE cycles DROP CONSTRAINT IF EXISTS cycles_user_start_unique');
            }
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('cycles', function (Blueprint $table) {
                $table->unique(['user_id', 'period_start_date'], 'cycles_user_start_unique');
            });
        } catch (\Throwable $e) {
        }
    }

    private function dropFullUnique(): void
    {
        try {
            if (DB::getDriverName() === 'pgsql') {
                // Postgres compiles Blueprint::unique() to a table constraint.
                DB::statement('ALTER TABLE cycles DROP CONSTRAINT IF EXISTS cycles_user_start_unique');
            } elseif (DB::getDriverName() === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS cycles_user_start_unique');
            } else {
                Schema::table('cycles', function (Blueprint $table) {
                    $table->dropUnique('cycles_user_start_unique');
                });
            }
        } catch (\Throwable $e) {
        }
    }
};
