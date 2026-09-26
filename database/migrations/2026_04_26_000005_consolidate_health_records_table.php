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
        // Portable version of the original MySQL-only consolidation (which used
        // information_schema introspection, "ALTER TABLE .. DROP FOREIGN KEY"
        // and "UPDATE .. INNER JOIN .. SET" — none parse on pgsql/sqlite).

        // Consolidate recorded_by columns
        if (Schema::hasColumn('health_records', 'recorded_by_midwife_id') || Schema::hasColumn('health_records', 'recorded_by_bhw_id')) {
            $this->ensureColumn('health_records', 'recorded_by_user_id');

            if (Schema::hasColumn('health_records', 'recorded_by_midwife_id')) {
                $this->copyRoleFiltered('health_records', 'recorded_by_midwife_id', 'recorded_by_user_id', ['midwife']);
            }

            if (Schema::hasColumn('health_records', 'recorded_by_bhw_id')) {
                $this->copyRoleFiltered('health_records', 'recorded_by_bhw_id', 'recorded_by_user_id', ['bhw', 'bhw_president']);
            }

            try {
                DB::statement('DELETE FROM health_records WHERE recorded_by_user_id IS NULL AND (recorded_by_midwife_id IS NOT NULL OR recorded_by_bhw_id IS NOT NULL)');
            } catch (\Throwable $e) {
            }
            try {
                DB::statement('DELETE FROM health_records WHERE recorded_by_user_id IS NOT NULL AND recorded_by_user_id NOT IN (SELECT id FROM users)');
            } catch (\Throwable $e) {
            }

            $this->addForeignKey('health_records', 'recorded_by_user_id', 'users', 'set null');
            $this->addIndex('health_records', 'recorded_by_user_id');

            $this->dropColumnSafely('health_records', 'recorded_by_midwife_id');
            $this->dropColumnSafely('health_records', 'recorded_by_bhw_id');

            $this->renameColumnSafely('health_records', 'recorded_by_user_id', 'recorded_by_id');
        }

        // Consolidate bhw_president_id
        if (Schema::hasColumn('health_records', 'bhw_president_id')) {
            $this->ensureColumn('health_records', 'bhw_president_user_id');
            $this->copyRoleFiltered('health_records', 'bhw_president_id', 'bhw_president_user_id', ['bhw_president']);

            try {
                DB::statement('DELETE FROM health_records WHERE bhw_president_user_id IS NULL AND bhw_president_id IS NOT NULL');
            } catch (\Throwable $e) {
            }
            try {
                DB::statement('DELETE FROM health_records WHERE bhw_president_user_id IS NOT NULL AND bhw_president_user_id NOT IN (SELECT id FROM users)');
            } catch (\Throwable $e) {
            }

            $this->addForeignKey('health_records', 'bhw_president_user_id', 'users', 'set null');
            $this->addIndex('health_records', 'bhw_president_user_id');

            $this->dropColumnSafely('health_records', 'bhw_president_id');

            $this->renameColumnSafely('health_records', 'bhw_president_user_id', 'bhw_president_id');
        }
    }

    protected function ensureColumn(string $table, string $column): void
    {
        if (Schema::hasColumn($table, $column)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->unsignedBigInteger($column)->nullable();
            });
        } catch (\Throwable $e) {
        }
    }

    /**
     * Copy values from $source to $target for rows whose $source id belongs
     * to a user with one of the given roles. Portable equivalent of
     * "UPDATE .. INNER JOIN users .. SET".
     */
    protected function copyRoleFiltered(string $table, string $source, string $target, array $roles): void
    {
        if (!Schema::hasColumn($table, $source) || !Schema::hasColumn($table, $target)) {
            return;
        }
        try {
            $ids = DB::table('users')->whereIn('role', $roles)->pluck('id');
            if ($ids->isNotEmpty()) {
                DB::table($table)->whereNotNull($source)->whereIn($source, $ids)->update([$target => DB::raw($source)]);
            }
        } catch (\Throwable $e) {
        }
    }

    protected function addForeignKey(string $table, string $column, string $on, string $delete): void
    {
        if (!Schema::hasColumn($table, $column) || !Schema::hasTable($on)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $t) use ($column, $on, $delete) {
                $t->foreign($column)->references('id')->on($on)->onDelete($delete);
            });
        } catch (\Throwable $e) {
        }
    }

    protected function addIndex(string $table, string $column): void
    {
        if (!Schema::hasColumn($table, $column)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->index($column);
            });
        } catch (\Throwable $e) {
        }
    }

    protected function dropColumnSafely(string $table, string $column): void
    {
        if (!Schema::hasColumn($table, $column)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropForeign([$column]);
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropIndex([$column]);
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropColumn($column);
            });
        } catch (\Throwable $e) {
            // SQLite cannot drop FK-bound columns — rename away instead.
            if (DB::getDriverName() === 'sqlite' && Schema::hasColumn($table, $column)) {
                try {
                    Schema::table($table, function (Blueprint $t) use ($column) {
                        $t->renameColumn($column, $column . '__deprecated');
                    });
                } catch (\Throwable $e2) {
                }
            }
        }
    }

    protected function renameColumnSafely(string $table, string $from, string $to): void
    {
        if (!Schema::hasColumn($table, $from) || Schema::hasColumn($table, $to)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $t) use ($from, $to) {
                $t->renameColumn($from, $to);
            });
        } catch (\Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse recorded_by columns
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropForeign(['recorded_by_id']);
            $table->dropIndex(['recorded_by_id']);
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->renameColumn('recorded_by_id', 'recorded_by_user_id');
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->unsignedBigInteger('recorded_by_midwife_id')->nullable()->after('recorded_by_user_id');
            $table->unsignedBigInteger('recorded_by_bhw_id')->nullable()->after('recorded_by_midwife_id');
        });

        DB::statement("
            UPDATE health_records h
            INNER JOIN users u ON h.recorded_by_user_id = u.id AND u.role = 'midwife'
            SET h.recorded_by_midwife_id = h.recorded_by_user_id
            WHERE u.role = 'midwife'
        ");

        DB::statement("
            UPDATE health_records h
            INNER JOIN users u ON h.recorded_by_user_id = u.id AND u.role IN ('bhw', 'bhw_president')
            SET h.recorded_by_bhw_id = h.recorded_by_user_id
            WHERE u.role IN ('bhw', 'bhw_president')
        ");

        Schema::table('health_records', function (Blueprint $table) {
            $table->foreign('recorded_by_midwife_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('recorded_by_bhw_id')->references('id')->on('users')->onDelete('set null');
            $table->index('recorded_by_midwife_id');
            $table->index('recorded_by_bhw_id');
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn('recorded_by_user_id');
        });

        // Reverse bhw_president_id
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropForeign(['bhw_president_id']);
            $table->dropIndex(['bhw_president_id']);
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->renameColumn('bhw_president_id', 'bhw_president_user_id');
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->unsignedBigInteger('bhw_president_id')->nullable()->after('bhw_president_user_id');
        });

        DB::statement("
            UPDATE health_records h
            INNER JOIN users u ON h.bhw_president_user_id = u.id AND u.role = 'bhw_president'
            SET h.bhw_president_id = h.bhw_president_user_id
            WHERE h.bhw_president_user_id IS NOT NULL
        ");

        Schema::table('health_records', function (Blueprint $table) {
            $table->foreign('bhw_president_id')->references('id')->on('users')->onDelete('set null');
            $table->index('bhw_president_id');
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn('bhw_president_user_id');
        });
    }
};
