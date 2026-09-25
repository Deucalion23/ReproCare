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
        // Normalize forum_posts
        Schema::table('forum_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('forum_posts', 'woman_id')) {
                $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('forum_posts', 'midwife_id')) {
                $table->unsignedBigInteger('midwife_id')->nullable()->after('woman_id');
            }
            if (!Schema::hasColumn('forum_posts', 'bhw_id')) {
                $table->unsignedBigInteger('bhw_id')->nullable()->after('midwife_id');
            }

            $foreignKeys = $this->existingForeignKeys('forum_posts');

            if (Schema::hasColumn('forum_posts', 'woman_id') && !in_array('forum_posts_woman_id_foreign', $foreignKeys)) {
                $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');
            }
            if (Schema::hasColumn('forum_posts', 'midwife_id') && !in_array('forum_posts_midwife_id_foreign', $foreignKeys)) {
                $table->foreign('midwife_id')->references('id')->on('midwives')->onDelete('cascade');
            }
            if (Schema::hasColumn('forum_posts', 'bhw_id') && !in_array('forum_posts_bhw_id_foreign', $foreignKeys)) {
                $table->foreign('bhw_id')->references('id')->on('bhws')->onDelete('cascade');
            }
        });

        if (Schema::hasColumn('forum_posts', 'user_id') && Schema::hasColumn('forum_posts', 'user_type')) {
            // Portable query-builder updates (original MySQL "UPDATE .. alias SET
            // alias.col" syntax does not parse on pgsql/sqlite).
            try {
                DB::table('forum_posts')->whereIn('user_type', ['App\\Models\\Woman', 'App\\Models\\Patient'])->update(['woman_id' => DB::raw('user_id')]);
            } catch (\Throwable $e) {
            }
            try {
                DB::table('forum_posts')->where('user_type', 'App\\Models\\Midwife')->update(['midwife_id' => DB::raw('user_id')]);
            } catch (\Throwable $e) {
            }
            try {
                DB::table('forum_posts')->where('user_type', 'App\\Models\\Bhw')->update(['bhw_id' => DB::raw('user_id')]);
            } catch (\Throwable $e) {
            }
        }

        try {
            Schema::table('forum_posts', function (Blueprint $table) {
                $columnsToDrop = [];
                $existingColumns = Schema::getColumnListing('forum_posts');
                if (in_array('user_id', $existingColumns)) $columnsToDrop[] = 'user_id';
                if (in_array('user_type', $existingColumns)) $columnsToDrop[] = 'user_type';
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        } catch (\Exception $e) {
            // Columns don't exist, skip
        }

        // Normalize forum_comments
        Schema::table('forum_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('forum_comments', 'woman_id')) {
                $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('forum_comments', 'midwife_id')) {
                $table->unsignedBigInteger('midwife_id')->nullable()->after('woman_id');
            }
            if (!Schema::hasColumn('forum_comments', 'bhw_id')) {
                $table->unsignedBigInteger('bhw_id')->nullable()->after('midwife_id');
            }

            $foreignKeys = $this->existingForeignKeys('forum_comments');

            if (Schema::hasColumn('forum_comments', 'woman_id') && !in_array('forum_comments_woman_id_foreign', $foreignKeys)) {
                $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');
            }
            if (Schema::hasColumn('forum_comments', 'midwife_id') && !in_array('forum_comments_midwife_id_foreign', $foreignKeys)) {
                $table->foreign('midwife_id')->references('id')->on('midwives')->onDelete('cascade');
            }
            if (Schema::hasColumn('forum_comments', 'bhw_id') && !in_array('forum_comments_bhw_id_foreign', $foreignKeys)) {
                $table->foreign('bhw_id')->references('id')->on('bhws')->onDelete('cascade');
            }
        });

        if (Schema::hasColumn('forum_comments', 'user_id') && Schema::hasColumn('forum_comments', 'user_type')) {
            try {
                DB::table('forum_comments')->whereIn('user_type', ['App\\Models\\Woman', 'App\\Models\\Patient'])->update(['woman_id' => DB::raw('user_id')]);
            } catch (\Throwable $e) {
            }
            try {
                DB::table('forum_comments')->where('user_type', 'App\\Models\\Midwife')->update(['midwife_id' => DB::raw('user_id')]);
            } catch (\Throwable $e) {
            }
            try {
                DB::table('forum_comments')->where('user_type', 'App\\Models\\Bhw')->update(['bhw_id' => DB::raw('user_id')]);
            } catch (\Throwable $e) {
            }
        }

        try {
            Schema::table('forum_comments', function (Blueprint $table) {
                $columnsToDrop = [];
                $existingColumns = Schema::getColumnListing('forum_comments');
                if (in_array('user_id', $existingColumns)) $columnsToDrop[] = 'user_id';
                if (in_array('user_type', $existingColumns)) $columnsToDrop[] = 'user_type';
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        } catch (\Exception $e) {
            // Columns don't exist, skip
        }

        // Normalize forum_likes
        Schema::table('forum_likes', function (Blueprint $table) {
            if (!Schema::hasColumn('forum_likes', 'woman_id')) {
                $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('forum_likes', 'midwife_id')) {
                $table->unsignedBigInteger('midwife_id')->nullable()->after('woman_id');
            }
            if (!Schema::hasColumn('forum_likes', 'bhw_id')) {
                $table->unsignedBigInteger('bhw_id')->nullable()->after('midwife_id');
            }

            $foreignKeys = $this->existingForeignKeys('forum_likes');

            if (Schema::hasColumn('forum_likes', 'woman_id') && !in_array('forum_likes_woman_id_foreign', $foreignKeys)) {
                $table->foreign('woman_id')->references('id')->on('women')->onDelete('cascade');
            }
            if (Schema::hasColumn('forum_likes', 'midwife_id') && !in_array('forum_likes_midwife_id_foreign', $foreignKeys)) {
                $table->foreign('midwife_id')->references('id')->on('midwives')->onDelete('cascade');
            }
            if (Schema::hasColumn('forum_likes', 'bhw_id') && !in_array('forum_likes_bhw_id_foreign', $foreignKeys)) {
                $table->foreign('bhw_id')->references('id')->on('bhws')->onDelete('cascade');
            }
        });

        // Add unique constraint (best effort — ignored if already present)
        try {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->unique(['post_id', 'woman_id', 'midwife_id', 'bhw_id'], 'unique_like');
            });
        } catch (\Throwable $e) {
        }

        if (Schema::hasColumn('forum_likes', 'user_id') && Schema::hasColumn('forum_likes', 'user_type')) {
            try {
                DB::table('forum_likes')->whereIn('user_type', ['App\\Models\\Woman', 'App\\Models\\Patient'])->update(['woman_id' => DB::raw('user_id')]);
            } catch (\Throwable $e) {
            }
            try {
                DB::table('forum_likes')->where('user_type', 'App\\Models\\Midwife')->update(['midwife_id' => DB::raw('user_id')]);
            } catch (\Throwable $e) {
            }
            try {
                DB::table('forum_likes')->where('user_type', 'App\\Models\\Bhw')->update(['bhw_id' => DB::raw('user_id')]);
            } catch (\Throwable $e) {
            }
        }

        try {
            Schema::table('forum_likes', function (Blueprint $table) {
                $columnsToDrop = [];
                $existingColumns = Schema::getColumnListing('forum_likes');
                if (in_array('user_id', $existingColumns)) $columnsToDrop[] = 'user_id';
                if (in_array('user_type', $existingColumns)) $columnsToDrop[] = 'user_type';
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        } catch (\Exception $e) {
            // Columns don't exist, skip
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse forum_posts
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('user_type')->nullable()->after('user_id');
        });

        DB::statement("UPDATE forum_posts SET user_id = woman_id, user_type = 'App\\\\Models\\\\Woman' WHERE woman_id IS NOT NULL");
        DB::statement("UPDATE forum_posts SET user_id = midwife_id, user_type = 'App\\\\Models\\\\Midwife' WHERE midwife_id IS NOT NULL");
        DB::statement("UPDATE forum_posts SET user_id = bhw_id, user_type = 'App\\\\Models\\\\Bhw' WHERE bhw_id IS NOT NULL");

        Schema::table('forum_posts', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropForeign(['midwife_id']);
            $table->dropForeign(['bhw_id']);
            $table->dropColumn(['woman_id', 'midwife_id', 'bhw_id']);
        });

        // Reverse forum_comments
        Schema::table('forum_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('user_type')->nullable()->after('user_id');
        });

        DB::statement("UPDATE forum_comments SET user_id = woman_id, user_type = 'App\\\\Models\\\\Woman' WHERE woman_id IS NOT NULL");
        DB::statement("UPDATE forum_comments SET user_id = midwife_id, user_type = 'App\\\\Models\\\\Midwife' WHERE midwife_id IS NOT NULL");
        DB::statement("UPDATE forum_comments SET user_id = bhw_id, user_type = 'App\\\\Models\\\\Bhw' WHERE bhw_id IS NOT NULL");

        Schema::table('forum_comments', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropForeign(['midwife_id']);
            $table->dropForeign(['bhw_id']);
            $table->dropColumn(['woman_id', 'midwife_id', 'bhw_id']);
        });

        // Reverse forum_likes
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('user_type')->nullable()->after('user_id');
        });

        DB::statement("UPDATE forum_likes SET user_id = woman_id, user_type = 'App\\\\Models\\\\Woman' WHERE woman_id IS NOT NULL");
        DB::statement("UPDATE forum_likes SET user_id = midwife_id, user_type = 'App\\\\Models\\\\Midwife' WHERE midwife_id IS NOT NULL");
        DB::statement("UPDATE forum_likes SET user_id = bhw_id, user_type = 'App\\\\Models\\\\Bhw' WHERE bhw_id IS NOT NULL");

        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropForeign(['midwife_id']);
            $table->dropForeign(['bhw_id']);
            $table->dropUnique('unique_like');
            $table->dropColumn(['woman_id', 'midwife_id', 'bhw_id']);
        });
    }

    /**
     * MySQL-only FK introspection helper. Returns [] on pgsql/sqlite so the
     * caller falls back to best-effort Schema attempts.
     */
    protected function existingForeignKeys(string $table): array
    {
        if (DB::getDriverName() !== 'mysql') {
            return [];
        }
        try {
            return collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = ? AND CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE '%foreign'", [$table]))->pluck('CONSTRAINT_NAME')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
};
