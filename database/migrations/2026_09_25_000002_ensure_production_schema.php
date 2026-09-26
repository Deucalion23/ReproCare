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
     * Production safety net for fresh installs (Render/Supabase Postgres).
     *
     * The pre-2026-09-25 migration history was developed against a single
     * long-lived MySQL database and contains drops, renames and MySQL-dialect
     * SQL that left fresh databases with subtly different schemas (missing
     * users.deleted_at, missing users.status, leftover helper columns, ...).
     * Everything here is portable (Schema facade only), guarded by
     * hasTable/hasColumn checks, and best effort — on healthy databases it
     * is a pure no-op.
     */
    public function up(): void
    {
        $this->ensureSoftDeletes([
            'users',
            'bhw_assignments',
            'bhw_monthly_reports',
            'checkups',
            'checkup_referrals',
            'child_checkups',
            'cycles',
            'emergency_contacts',
            'health_records',
            'learning_materials',
            'maternal_deaths',
            'maternal_morbidities',
            'messages',
            'notifications',
            'pregnancies',
            'supply_requests',
            'tasks',
            'walk_in_patients',
        ]);

        // Columns the login flow and seeders cannot work without.
        $this->ensureColumn('users', 'status', fn (Blueprint $t) => $t->string('status')->default('approved'));
        $this->ensureColumn('users', 'role', fn (Blueprint $t) => $t->string('role')->default('patient'));
        $this->ensureColumn('users', 'first_name', fn (Blueprint $t) => $t->string('first_name')->nullable());
        $this->ensureColumn('users', 'last_name', fn (Blueprint $t) => $t->string('last_name')->nullable());
        $this->ensureColumn('users', 'email', fn (Blueprint $t) => $t->string('email')->nullable());
        $this->ensureColumn('users', 'password', fn (Blueprint $t) => $t->string('password')->nullable());

        // Remove helper columns left behind by the portable SQLite fallbacks
        // (renamed instead of dropped when a column was FK-bound). They never
        // occur on Postgres/MySQL, but cleaning them is harmless everywhere.
        $this->dropDeprecatedColumns();
    }

    public function down(): void
    {
        // Safety-net only; nothing to roll back.
    }

    protected function ensureSoftDeletes(array $tables): void
    {
        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }
            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            } catch (\Throwable $e) {
            }
        }
    }

    protected function ensureColumn(string $table, string $column, callable $definition): void
    {
        if (!Schema::hasTable($table) || Schema::hasColumn($table, $column)) {
            return;
        }
        try {
            Schema::table($table, $definition);
        } catch (\Throwable $e) {
        }
    }

    protected function dropDeprecatedColumns(): void
    {
        try {
            $tables = Schema::getTables();
        } catch (\Throwable $e) {
            return;
        }
        foreach ($tables as $info) {
            $table = is_array($info) ? ($info['name'] ?? null) : null;
            if (!$table || str_starts_with($table, 'sqlite_')) {
                continue;
            }
            try {
                $columns = Schema::getColumnListing($table);
            } catch (\Throwable $e) {
                continue;
            }
            foreach ($columns as $column) {
                if (!str_ends_with($column, '__deprecated')) {
                    continue;
                }
                // Drop covering FK/index first (best effort), then the column.
                foreach (['dropForeign', 'dropIndex'] as $dropper) {
                    try {
                        Schema::table($table, function (Blueprint $t) use ($column, $dropper) {
                            $t->{$dropper}([$column]);
                        });
                    } catch (\Throwable $e) {
                    }
                }
                // On Postgres the constraint may keep its pre-rename name;
                // attempt a pg_catalog lookup for exact constraint names.
                if (DB::getDriverName() === 'pgsql') {
                    $this->dropPostgresConstraintsFor($table, $column);
                }
                try {
                    Schema::table($table, function (Blueprint $t) use ($column) {
                        $t->dropColumn($column);
                    });
                } catch (\Throwable $e) {
                }
            }
        }
    }

    protected function dropPostgresConstraintsFor(string $table, string $column): void
    {
        try {
            $rows = DB::select(
                "SELECT con.conname AS name
                 FROM pg_constraint con
                 JOIN pg_class tbl ON tbl.oid = con.conrelid
                 JOIN pg_attribute att ON att.attrelid = tbl.oid AND att.attnum = ANY (con.conkey)
                 WHERE tbl.relname = ? AND att.attname = ?",
                [$table, $column]
            );
            foreach ($rows as $row) {
                try {
                    DB::statement(sprintf('ALTER TABLE "%s" DROP CONSTRAINT "%s"', $table, $row->name));
                } catch (\Throwable $e) {
                }
            }
        } catch (\Throwable $e) {
        }
    }
};
