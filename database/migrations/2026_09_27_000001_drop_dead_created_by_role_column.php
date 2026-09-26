<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run without a DDL transaction (best-effort steps; a failure must not
     * poison the statements that follow on Postgres).
     */
    public $withinTransaction = false;

    /**
     * health_records.created_by_role is a dead legacy column: nothing in the
     * codebase reads or writes it (role is derived from recorded_by_id), and
     * migration 2026_04_15_000004 was supposed to drop it. On databases where
     * that drop silently failed it remains NOT NULL without a default, so
     * EVERY health-record insert fails with SQLSTATE 23502. Remove it
     * properly: covering indexes/constraints first (exact names via catalogs
     * on Postgres), then the column.
     */
    public function up(): void
    {
        if (!Schema::hasTable('health_records') || !Schema::hasColumn('health_records', 'created_by_role')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            try {
                $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'health_records' AND indexdef LIKE '%created_by_role%'");
                foreach ($indexes as $ix) {
                    try {
                        DB::statement(sprintf('DROP INDEX IF EXISTS "%s"', $ix->indexname));
                    } catch (\Throwable $e) {
                    }
                }
            } catch (\Throwable $e) {
            }

            try {
                $constraints = DB::select(
                    "SELECT DISTINCT con.conname AS name
                     FROM pg_constraint con
                     JOIN pg_class tbl ON tbl.oid = con.conrelid
                     LEFT JOIN pg_attribute att ON att.attrelid = tbl.oid AND att.attnum = ANY (con.conkey)
                     WHERE tbl.relname = 'health_records'
                       AND (att.attname = 'created_by_role' OR con.conname LIKE '%created_by_role%')"
                );
                foreach ($constraints as $c) {
                    try {
                        DB::statement(sprintf('ALTER TABLE "health_records" DROP CONSTRAINT IF EXISTS "%s"', $c->name));
                    } catch (\Throwable $e) {
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        // Best-effort conventional drops for mysql/sqlite.
        try {
            Schema::table('health_records', function (Blueprint $table) {
                $table->dropForeign(['created_by_role']);
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table('health_records', function (Blueprint $table) {
                $table->dropIndex(['created_by_role']);
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('health_records', function (Blueprint $table) {
                $table->dropColumn('created_by_role');
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        // Dead column; do not restore it.
    }
};
