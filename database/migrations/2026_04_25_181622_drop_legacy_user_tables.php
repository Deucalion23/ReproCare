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
     * WARNING: This migration drops legacy user tables (bhws, midwives, women, bhw_presidents).
     * Run the verification script first: php database/verify_migration.php
     * Create a database backup before running this migration.
     */
    public function up(): void
    {
        // Drop legacy user tables after data migration to users table.
        // Dependent FKs are removed first on Postgres (which refuses to drop
        // referenced tables); FK enforcement is toggled off for SQLite.
        $this->dropDependentForeignKeys(['bhws', 'midwives', 'women', 'bhw_presidents']);
        Schema::disableForeignKeyConstraints();
        try {
            Schema::dropIfExists('bhws');
            Schema::dropIfExists('midwives');
            Schema::dropIfExists('women');
            Schema::dropIfExists('bhw_presidents');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Drop FK constraints in any table that reference the given parent tables.
     * Postgres-only; other drivers don't need it here. Best effort.
     */
    protected function dropDependentForeignKeys(array $parents): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        try {
            $rows = DB::select(
                "SELECT con.conname AS name, tbl.relname AS table_name
                 FROM pg_constraint con
                 JOIN pg_class tbl ON tbl.oid = con.conrelid
                 JOIN pg_class ref ON ref.oid = con.confrelid
                 WHERE con.contype = 'f' AND ref.relname IN (" . implode(',', array_fill(0, count($parents), '?')) . ')',
                $parents
            );
            foreach ($rows as $row) {
                try {
                    DB::statement(sprintf('ALTER TABLE "%s" DROP CONSTRAINT "%s"', $row->table_name, $row->name));
                } catch (\Throwable $e) {
                }
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     * 
     * NOTE: This migration cannot be rolled back automatically.
     * Restore from database backup if needed.
     */
    public function down(): void
    {
        throw new \Exception('Cannot rollback this migration. All data has been migrated to the users table. Restore from database backup if needed.');
    }
};
