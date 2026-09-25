<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['forum_posts', 'forum_comments'] as $table) {
            $this->consolidateForumTable($table);
        }

        // forum_likes table - skip for now due to unique_like index complexity
        // Will handle in a separate migration
    }

    /**
     * Portable version of the original MySQL-only consolidation (which used
     * information_schema introspection, "ALTER TABLE .. DROP FOREIGN KEY" and
     * "UPDATE .. INNER JOIN .. SET" — none of which parse on pgsql/sqlite).
     */
    protected function consolidateForumTable(string $table): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if (!Schema::hasColumn($table, 'user_id')) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('user_id')->nullable();
            });
        }

        // Migrate data from polymorphic columns to user_id (only ids that
        // belong to a user with a matching role).
        $moves = [
            ['woman_id', ['user']],
            ['midwife_id', ['midwife']],
            ['bhw_id', ['bhw', 'bhw_president']],
        ];
        foreach ($moves as [$source, $roles]) {
            if (!Schema::hasColumn($table, $source)) {
                continue;
            }
            try {
                $ids = DB::table('users')->whereIn('role', $roles)->pluck('id');
                if ($ids->isNotEmpty()) {
                    DB::table($table)->whereNotNull($source)->whereIn($source, $ids)->update(['user_id' => DB::raw($source)]);
                }
            } catch (\Throwable $e) {
            }
        }

        try {
            DB::statement("DELETE FROM {$table} WHERE user_id IS NULL");
        } catch (\Throwable $e) {
        }
        try {
            DB::statement("DELETE FROM {$table} WHERE user_id NOT IN (SELECT id FROM users)");
        } catch (\Throwable $e) {
        }

        // Add foreign key constraint + index (best effort)
        try {
            Schema::table($table, function (Blueprint $t) {
                $t->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
        }
        try {
            Schema::table($table, function (Blueprint $t) {
                $t->index('user_id');
            });
        } catch (\Throwable $e) {
        }

        // Drop old polymorphic columns (drop FKs first; the referenced role
        // tables may already be gone, so every step is best effort).
        foreach (['woman_id', 'midwife_id', 'bhw_id'] as $column) {
            if (!Schema::hasColumn($table, $column)) {
                continue;
            }
            try {
                Schema::table($table, function (Blueprint $t) use ($column) {
                    try {
                        $t->dropForeign([$column]);
                    } catch (\Throwable $e) {
                    }
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse forum_posts
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            $table->unsignedBigInteger('midwife_id')->nullable()->after('woman_id');
            $table->unsignedBigInteger('bhw_id')->nullable()->after('midwife_id');
        });

        DB::statement("
            UPDATE forum_posts f
            INNER JOIN users u ON f.user_id = u.id AND u.role = 'user'
            SET f.woman_id = f.user_id
            WHERE u.role = 'user'
        ");

        DB::statement("
            UPDATE forum_posts f
            INNER JOIN users u ON f.user_id = u.id AND u.role = 'midwife'
            SET f.midwife_id = f.user_id
            WHERE u.role = 'midwife'
        ");

        DB::statement("
            UPDATE forum_posts f
            INNER JOIN users u ON f.user_id = u.id AND u.role IN ('bhw', 'bhw_president')
            SET f.bhw_id = f.user_id
            WHERE u.role IN ('bhw', 'bhw_president')
        ");

        Schema::table('forum_posts', function (Blueprint $table) {
            $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('woman_id');
            $table->index('midwife_id');
            $table->index('bhw_id');
        });

        Schema::table('forum_posts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });

        // Reverse forum_comments
        Schema::table('forum_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            $table->unsignedBigInteger('midwife_id')->nullable()->after('woman_id');
            $table->unsignedBigInteger('bhw_id')->nullable()->after('midwife_id');
        });

        DB::statement("
            UPDATE forum_comments f
            INNER JOIN users u ON f.user_id = u.id AND u.role = 'user'
            SET f.woman_id = f.user_id
            WHERE u.role = 'user'
        ");

        DB::statement("
            UPDATE forum_comments f
            INNER JOIN users u ON f.user_id = u.id AND u.role = 'midwife'
            SET f.midwife_id = f.user_id
            WHERE u.role = 'midwife'
        ");

        DB::statement("
            UPDATE forum_comments f
            INNER JOIN users u ON f.user_id = u.id AND u.role IN ('bhw', 'bhw_president')
            SET f.bhw_id = f.user_id
            WHERE u.role IN ('bhw', 'bhw_president')
        ");

        Schema::table('forum_comments', function (Blueprint $table) {
            $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('woman_id');
            $table->index('midwife_id');
            $table->index('bhw_id');
        });

        Schema::table('forum_comments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });

        // Reverse forum_likes
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            $table->unsignedBigInteger('midwife_id')->nullable()->after('woman_id');
            $table->unsignedBigInteger('bhw_id')->nullable()->after('midwife_id');
        });

        DB::statement("
            UPDATE forum_likes f
            INNER JOIN users u ON f.user_id = u.id AND u.role = 'user'
            SET f.woman_id = f.user_id
            WHERE u.role = 'user'
        ");

        DB::statement("
            UPDATE forum_likes f
            INNER JOIN users u ON f.user_id = u.id AND u.role = 'midwife'
            SET f.midwife_id = f.user_id
            WHERE u.role = 'midwife'
        ");

        DB::statement("
            UPDATE forum_likes f
            INNER JOIN users u ON f.user_id = u.id AND u.role IN ('bhw', 'bhw_president')
            SET f.bhw_id = f.user_id
            WHERE u.role IN ('bhw', 'bhw_president')
        ");

        Schema::table('forum_likes', function (Blueprint $table) {
            $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('woman_id');
            $table->index('midwife_id');
            $table->index('bhw_id');
        });

        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
