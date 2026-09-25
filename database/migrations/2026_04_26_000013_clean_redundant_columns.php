<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
        // First, migrate data from role-specific columns to user_id
        DB::statement("
            UPDATE forum_likes
            SET user_id = COALESCE(user_id, midwife_id, bhw_id)
            WHERE user_id IS NULL
        ");

        // Add unique constraint on (post_id, user_id) if it doesn't exist
        $uniqueExists = DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.table_constraints
            WHERE table_schema = DATABASE()
            AND table_name = 'forum_likes'
            AND constraint_name = 'forum_likes_post_id_user_id_unique'
            AND constraint_type = 'UNIQUE'
        ");
        if (empty($uniqueExists) || $uniqueExists[0]->count == 0) {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->unique(['post_id', 'user_id']);
            });
        }

        // Drop role-specific columns individually (need to drop unique constraint first)
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropUnique('unique_like');
        });
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropColumn('woman_id');
        });
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropColumn('midwife_id');
        });
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropColumn('bhw_id');
        });
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
