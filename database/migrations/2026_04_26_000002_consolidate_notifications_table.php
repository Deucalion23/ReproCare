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
        // Add new user_id column if not exists
        if (!Schema::hasColumn('notifications', 'user_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable();
            });
        }

        // Migrate data from polymorphic columns to user_id (only if user_id is null).
        // Portable query-builder version of the original MySQL UPDATE..JOIN.
        $moves = [
            ['woman_id', ['user']],
            ['midwife_id', ['midwife']],
            ['bhw_id', ['bhw', 'bhw_president']],
        ];
        foreach ($moves as [$source, $roles]) {
            if (!Schema::hasColumn('notifications', $source) || !Schema::hasColumn('notifications', 'user_id')) {
                continue;
            }
            try {
                $ids = DB::table('users')->whereIn('role', $roles)->pluck('id');
                if ($ids->isNotEmpty()) {
                    DB::table('notifications')->whereNotNull($source)->whereNull('user_id')->whereIn($source, $ids)->update(['user_id' => DB::raw($source)]);
                }
            } catch (\Throwable $e) {
            }
        }

        // Delete notifications where user_id couldn't be migrated
        try {
            DB::statement('DELETE FROM notifications WHERE user_id IS NULL');
        } catch (\Throwable $e) {
        }

        // Delete notifications where user_id doesn't exist in users table
        try {
            DB::statement('DELETE FROM notifications WHERE user_id NOT IN (SELECT id FROM users)');
        } catch (\Throwable $e) {
        }

        // Add foreign key constraint + index (best effort)
        if (Schema::hasColumn('notifications', 'user_id')) {
            try {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->index('user_id');
                });
            } catch (\Throwable $e) {
            }
        }

        // Drop old polymorphic columns (drop FKs first, best effort — the
        // referenced role tables may already be gone)
        foreach (['woman_id', 'midwife_id', 'bhw_id'] as $column) {
            if (!Schema::hasColumn('notifications', $column)) {
                continue;
            }
            try {
                Schema::table('notifications', function (Blueprint $table) use ($column) {
                    try {
                        $table->dropForeign([$column]);
                    } catch (\Throwable $e) {
                    }
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('notifications', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            } catch (\Throwable $e) {
                if (DB::getDriverName() === 'sqlite' && Schema::hasColumn('notifications', $column)) {
                    try {
                        Schema::table('notifications', function (Blueprint $table) use ($column) {
                            $table->renameColumn($column, $column . '__deprecated');
                        });
                    } catch (\Throwable $e2) {
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add polymorphic columns
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            $table->unsignedBigInteger('midwife_id')->nullable()->after('woman_id');
            $table->unsignedBigInteger('bhw_id')->nullable()->after('midwife_id');
        });

        // Migrate data back to polymorphic columns
        DB::statement("
            UPDATE notifications n
            INNER JOIN users u ON n.user_id = u.id AND u.role = 'user'
            SET n.woman_id = n.user_id
            WHERE u.role = 'user'
        ");

        DB::statement("
            UPDATE notifications n
            INNER JOIN users u ON n.user_id = u.id AND u.role = 'midwife'
            SET n.midwife_id = n.user_id
            WHERE u.role = 'midwife'
        ");

        DB::statement("
            UPDATE notifications n
            INNER JOIN users u ON n.user_id = u.id AND u.role IN ('bhw', 'bhw_president')
            SET n.bhw_id = n.user_id
            WHERE u.role IN ('bhw', 'bhw_president')
        ");

        // Add foreign keys and indexes for polymorphic columns
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('woman_id');
            $table->index('midwife_id');
            $table->index('bhw_id');
        });

        // Drop user_id column
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
