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

        // Consolidate midwife_id
        if (Schema::hasColumn('checkups', 'midwife_id')) {
            $this->ensureColumn('checkups', 'midwife_id_consolidated');
            $this->copyRoleFiltered('checkups', 'midwife_id', 'midwife_id_consolidated', ['midwife']);
            $this->dropColumnSafely('checkups', 'midwife_id');
            $this->renameColumnSafely('checkups', 'midwife_id_consolidated', 'midwife_id');
            $this->addForeignKey('checkups', 'midwife_id', 'users', 'cascade');
            $this->addIndex('checkups', 'midwife_id');
        }

        // Consolidate scheduled_by columns
        if (Schema::hasColumn('checkups', 'scheduled_by_midwife_id') || Schema::hasColumn('checkups', 'scheduled_by_bhw_id')) {
            $this->ensureColumn('checkups', 'scheduled_by_user_id');

            if (Schema::hasColumn('checkups', 'scheduled_by_midwife_id')) {
                $this->copyRoleFiltered('checkups', 'scheduled_by_midwife_id', 'scheduled_by_user_id', ['midwife']);
            }

            if (Schema::hasColumn('checkups', 'scheduled_by_bhw_id')) {
                $this->copyRoleFiltered('checkups', 'scheduled_by_bhw_id', 'scheduled_by_user_id', ['bhw', 'bhw_president']);
            }

            // If scheduled_by_id exists and scheduled_by_user_id is null, use it
            if (Schema::hasColumn('checkups', 'scheduled_by_id')) {
                try {
                    DB::statement('UPDATE checkups SET scheduled_by_user_id = scheduled_by_id WHERE scheduled_by_user_id IS NULL AND scheduled_by_id IS NOT NULL');
                } catch (\Throwable $e) {
                }
            }

            try {
                DB::statement('DELETE FROM checkups WHERE scheduled_by_user_id IS NULL AND (scheduled_by_midwife_id IS NOT NULL OR scheduled_by_bhw_id IS NOT NULL)');
            } catch (\Throwable $e) {
            }
            try {
                DB::statement('DELETE FROM checkups WHERE scheduled_by_user_id IS NOT NULL AND scheduled_by_user_id NOT IN (SELECT id FROM users)');
            } catch (\Throwable $e) {
            }

            $this->addForeignKey('checkups', 'scheduled_by_user_id', 'users', 'set null');
            $this->addIndex('checkups', 'scheduled_by_user_id');

            // Drop old scheduled_by columns
            $this->dropColumnSafely('checkups', 'scheduled_by_midwife_id');
            $this->dropColumnSafely('checkups', 'scheduled_by_bhw_id');
            $this->dropColumnSafely('checkups', 'scheduled_by_id');

            $this->renameColumnSafely('checkups', 'scheduled_by_user_id', 'scheduled_by_id');
        }

        // Consolidate bhw_president_id
        if (Schema::hasColumn('checkups', 'bhw_president_id')) {
            $this->ensureColumn('checkups', 'bhw_president_user_id');
            $this->copyRoleFiltered('checkups', 'bhw_president_id', 'bhw_president_user_id', ['bhw_president']);

            try {
                DB::statement('DELETE FROM checkups WHERE bhw_president_user_id IS NULL AND bhw_president_id IS NOT NULL');
            } catch (\Throwable $e) {
            }
            try {
                DB::statement('DELETE FROM checkups WHERE bhw_president_user_id IS NOT NULL AND bhw_president_user_id NOT IN (SELECT id FROM users)');
            } catch (\Throwable $e) {
            }

            $this->addForeignKey('checkups', 'bhw_president_user_id', 'users', 'set null');
            $this->addIndex('checkups', 'bhw_president_user_id');

            $this->dropColumnSafely('checkups', 'bhw_president_id');

            $this->renameColumnSafely('checkups', 'bhw_president_user_id', 'bhw_president_id');
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
        // NOTE: each in its own Schema::table call — a missing FK must not
        // prevent the index drop (and vice versa), since Blueprint commands
        // only throw at execution time for the whole call.
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
        // Reverse midwife_id
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropForeign(['midwife_id']);
            $table->dropIndex(['midwife_id']);
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->renameColumn('midwife_id', 'midwife_id_consolidated');
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->unsignedBigInteger('midwife_id')->nullable()->after('midwife_id_consolidated');
        });

        DB::statement("
            UPDATE checkups c
            INNER JOIN users u ON c.midwife_id_consolidated = u.id AND u.role = 'midwife'
            SET c.midwife_id = c.midwife_id_consolidated
            WHERE c.midwife_id_consolidated IS NOT NULL
        ");

        Schema::table('checkups', function (Blueprint $table) {
            $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('midwife_id');
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->dropColumn('midwife_id_consolidated');
        });

        // Reverse scheduled_by columns
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropForeign(['scheduled_by_id']);
            $table->dropIndex(['scheduled_by_id']);
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->renameColumn('scheduled_by_id', 'scheduled_by_user_id');
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->unsignedBigInteger('scheduled_by_midwife_id')->nullable()->after('scheduled_by_user_id');
            $table->unsignedBigInteger('scheduled_by_bhw_id')->nullable()->after('scheduled_by_midwife_id');
        });

        DB::statement("
            UPDATE checkups c
            INNER JOIN users u ON c.scheduled_by_user_id = u.id AND u.role = 'midwife'
            SET c.scheduled_by_midwife_id = c.scheduled_by_user_id
            WHERE u.role = 'midwife'
        ");

        DB::statement("
            UPDATE checkups c
            INNER JOIN users u ON c.scheduled_by_user_id = u.id AND u.role IN ('bhw', 'bhw_president')
            SET c.scheduled_by_bhw_id = c.scheduled_by_user_id
            WHERE u.role IN ('bhw', 'bhw_president')
        ");

        Schema::table('checkups', function (Blueprint $table) {
            $table->foreign('scheduled_by_midwife_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('scheduled_by_bhw_id')->references('id')->on('users')->onDelete('set null');
            $table->index('scheduled_by_midwife_id');
            $table->index('scheduled_by_bhw_id');
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->dropColumn('scheduled_by_user_id');
        });

        // Reverse bhw_president_id
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropForeign(['bhw_president_id']);
            $table->dropIndex(['bhw_president_id']);
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->renameColumn('bhw_president_id', 'bhw_president_user_id');
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->unsignedBigInteger('bhw_president_id')->nullable()->after('bhw_president_user_id');
        });

        DB::statement("
            UPDATE checkups c
            INNER JOIN users u ON c.bhw_president_user_id = u.id AND u.role = 'bhw_president'
            SET c.bhw_president_id = c.bhw_president_user_id
            WHERE c.bhw_president_user_id IS NOT NULL
        ");

        Schema::table('checkups', function (Blueprint $table) {
            $table->foreign('bhw_president_id')->references('id')->on('users')->onDelete('set null');
            $table->index('bhw_president_id');
        });

        Schema::table('checkups', function (Blueprint $table) {
            $table->dropColumn('bhw_president_user_id');
        });
    }
};
