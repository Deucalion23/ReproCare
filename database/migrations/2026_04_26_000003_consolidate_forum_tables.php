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
        // forum_posts table
        Schema::table('forum_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('forum_posts', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
        });

        // Migrate data from polymorphic columns to user_id (only if columns exist)
        if (Schema::hasColumn('forum_posts', 'woman_id')) {
            DB::statement("
                UPDATE forum_posts f
                INNER JOIN users u ON f.woman_id = u.id AND u.role = 'user'
                SET f.user_id = f.woman_id
                WHERE f.woman_id IS NOT NULL
            ");
        }

        if (Schema::hasColumn('forum_posts', 'midwife_id')) {
            DB::statement("
                UPDATE forum_posts f
                INNER JOIN users u ON f.midwife_id = u.id AND u.role = 'midwife'
                SET f.user_id = f.midwife_id
                WHERE f.midwife_id IS NOT NULL
            ");
        }

        if (Schema::hasColumn('forum_posts', 'bhw_id')) {
            DB::statement("
                UPDATE forum_posts f
                INNER JOIN users u ON f.bhw_id = u.id AND u.role IN ('bhw', 'bhw_president')
                SET f.user_id = f.bhw_id
                WHERE f.bhw_id IS NOT NULL
            ");
        }

        DB::statement("DELETE FROM forum_posts WHERE user_id IS NULL");
        DB::statement("DELETE FROM forum_posts WHERE user_id NOT IN (SELECT id FROM users)");

        // Add foreign key constraint if not exists
        $fkExists = DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.table_constraints
            WHERE table_schema = DATABASE()
            AND table_name = 'forum_posts'
            AND constraint_name = 'forum_posts_user_id_foreign'
            AND constraint_type = 'FOREIGN KEY'
        ");

        if (empty($fkExists) || $fkExists[0]->count == 0) {
            Schema::table('forum_posts', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Add index if not exists
        $indexExists = DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
            AND table_name = 'forum_posts'
            AND index_name = 'forum_posts_user_id_index'
        ");

        if (empty($indexExists) || $indexExists[0]->count == 0) {
            Schema::table('forum_posts', function (Blueprint $table) {
                $table->index('user_id');
            });
        }

        // Drop old polymorphic columns
        $fkNames = ['forum_posts_woman_id_foreign', 'forum_posts_midwife_id_foreign', 'forum_posts_bhw_id_foreign'];
        foreach ($fkNames as $fkName) {
            $fkExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.table_constraints
                WHERE table_schema = DATABASE()
                AND table_name = 'forum_posts'
                AND constraint_name = '$fkName'
                AND constraint_type = 'FOREIGN KEY'
            ");
            if (!empty($fkExists) && $fkExists[0]->count > 0) {
                DB::statement("ALTER TABLE forum_posts DROP FOREIGN KEY $fkName");
            }
        }

        // Drop columns using raw SQL
        $columnsToDrop = ['woman_id', 'midwife_id', 'bhw_id'];
        foreach ($columnsToDrop as $column) {
            if (Schema::hasColumn('forum_posts', $column)) {
                DB::statement("ALTER TABLE forum_posts DROP COLUMN $column");
            }
        }

        // forum_comments table
        Schema::table('forum_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('forum_comments', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
        });

        // Migrate data from polymorphic columns to user_id (only if columns exist)
        if (Schema::hasColumn('forum_comments', 'woman_id')) {
            DB::statement("
                UPDATE forum_comments f
                INNER JOIN users u ON f.woman_id = u.id AND u.role = 'user'
                SET f.user_id = f.woman_id
                WHERE f.woman_id IS NOT NULL
            ");
        }

        if (Schema::hasColumn('forum_comments', 'midwife_id')) {
            DB::statement("
                UPDATE forum_comments f
                INNER JOIN users u ON f.midwife_id = u.id AND u.role = 'midwife'
                SET f.user_id = f.midwife_id
                WHERE f.midwife_id IS NOT NULL
            ");
        }

        if (Schema::hasColumn('forum_comments', 'bhw_id')) {
            DB::statement("
                UPDATE forum_comments f
                INNER JOIN users u ON f.bhw_id = u.id AND u.role IN ('bhw', 'bhw_president')
                SET f.user_id = f.bhw_id
                WHERE f.bhw_id IS NOT NULL
            ");
        }

        DB::statement("DELETE FROM forum_comments WHERE user_id IS NULL");
        DB::statement("DELETE FROM forum_comments WHERE user_id NOT IN (SELECT id FROM users)");

        // Add foreign key constraint if not exists
        $fkExists = DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.table_constraints
            WHERE table_schema = DATABASE()
            AND table_name = 'forum_comments'
            AND constraint_name = 'forum_comments_user_id_foreign'
            AND constraint_type = 'FOREIGN KEY'
        ");

        if (empty($fkExists) || $fkExists[0]->count == 0) {
            Schema::table('forum_comments', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Add index if not exists
        $indexExists = DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
            AND table_name = 'forum_comments'
            AND index_name = 'forum_comments_user_id_index'
        ");

        if (empty($indexExists) || $indexExists[0]->count == 0) {
            Schema::table('forum_comments', function (Blueprint $table) {
                $table->index('user_id');
            });
        }

        // Drop old polymorphic columns
        $fkNames = ['forum_comments_woman_id_foreign', 'forum_comments_midwife_id_foreign', 'forum_comments_bhw_id_foreign'];
        foreach ($fkNames as $fkName) {
            $fkExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.table_constraints
                WHERE table_schema = DATABASE()
                AND table_name = 'forum_comments'
                AND constraint_name = '$fkName'
                AND constraint_type = 'FOREIGN KEY'
            ");
            if (!empty($fkExists) && $fkExists[0]->count > 0) {
                DB::statement("ALTER TABLE forum_comments DROP FOREIGN KEY $fkName");
            }
        }

        // Drop columns using raw SQL
        $columnsToDrop = ['woman_id', 'midwife_id', 'bhw_id'];
        foreach ($columnsToDrop as $column) {
            if (Schema::hasColumn('forum_comments', $column)) {
                DB::statement("ALTER TABLE forum_comments DROP COLUMN $column");
            }
        }

        // forum_likes table - skip for now due to unique_like index complexity
        // Will handle in a separate migration
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
