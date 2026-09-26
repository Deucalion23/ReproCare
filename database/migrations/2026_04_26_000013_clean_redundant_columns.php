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
        // Step 1: Drop redundant phone column from users (contact_number is the standard) if it exists
        if (Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }

        // Step 2: Drop unused assigned_barangays column (assigned_barangay is the active one) if it exists
        if (Schema::hasColumn('users', 'assigned_barangays')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('assigned_barangays');
            });
        }

        // Step 3: Consolidate forum_likes to use only user_id
        // First, migrate data from role-specific columns to user_id.
        // COALESCE references only columns that actually exist (portable).
        try {
            $likeColumns = Schema::getColumnListing('forum_likes');
            $coalesce = array_values(array_intersect(['user_id', 'midwife_id', 'bhw_id'], $likeColumns));
            if (in_array('user_id', $coalesce) && count($coalesce) > 1) {
                DB::statement('UPDATE forum_likes SET user_id = COALESCE(' . implode(', ', $coalesce) . ') WHERE user_id IS NULL');
            }
        } catch (\Throwable $e) {
        }

        // Add unique constraint on (post_id, user_id) (best effort — the
        // information_schema introspection is MySQL-only)
        try {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->unique(['post_id', 'user_id']);
            });
        } catch (\Throwable $e) {
        }

        // Drop role-specific columns individually (need to drop unique constraint first)
        try {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->dropUnique('unique_like');
            });
        } catch (\Throwable $e) {
        }
        foreach (['woman_id', 'midwife_id', 'bhw_id'] as $column) {
            if (!Schema::hasColumn('forum_likes', $column)) {
                continue;
            }
            try {
                Schema::table('forum_likes', function (Blueprint $table) use ($column) {
                    try {
                        $table->dropForeign([$column]);
                    } catch (\Throwable $e) {
                    }
                });
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('forum_likes', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            } catch (\Throwable $e) {
                // SQLite cannot drop FK-bound/indexed columns — rename away.
                if (DB::getDriverName() === 'sqlite' && Schema::hasColumn('forum_likes', $column)) {
                    try {
                        Schema::table('forum_likes', function (Blueprint $table) use ($column) {
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
        // Reverse: Drop unique constraint on (post_id, user_id) - need to drop foreign keys first
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropForeign('forum_likes_post_id_foreign');
            $table->dropForeign('forum_likes_user_id_foreign');
        });
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropUnique('forum_likes_post_id_user_id_unique');
        });

        // Reverse: Add back role-specific columns to forum_likes if they don't exist
        if (!Schema::hasColumn('forum_likes', 'woman_id')) {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->unsignedBigInteger('woman_id')->nullable()->after('user_id');
            });
        }
        if (!Schema::hasColumn('forum_likes', 'midwife_id')) {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->unsignedBigInteger('midwife_id')->nullable()->after('woman_id');
            });
        }
        if (!Schema::hasColumn('forum_likes', 'bhw_id')) {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->unsignedBigInteger('bhw_id')->nullable()->after('midwife_id');
            });
        }

        // Reverse: Re-add foreign keys
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->foreign('post_id')->references('id')->on('forum_posts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Reverse: Add back phone column if it doesn't exist
        if (!Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone', 20)->nullable()->after('contact_number');
            });
        }

        // Reverse: Add back assigned_barangays column if it doesn't exist
        if (!Schema::hasColumn('users', 'assigned_barangays')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('assigned_barangays')->nullable()->after('assigned_barangay');
            });
        }
    }
};
