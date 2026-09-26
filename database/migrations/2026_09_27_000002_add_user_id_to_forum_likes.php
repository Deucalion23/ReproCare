<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run without a DDL transaction (best-effort steps; a failure must not
     * poison the statements that follow on Postgres).
     */
    public $withinTransaction = false;

    /**
     * forum_likes.user_id was dropped by the 2026_04_20 normalize migration
     * and never re-added: 2026_04_26_000003 explicitly skips forum_likes and
     * 2026_04_26_000004 is an intentional no-op. But ForumPost::isLikedBy()
     * and ForumController@like both query forum_likes.user_id, so every
     * forum page 500s on fresh installs (SQLSTATE 42703). Restore the
     * column plus its foreign key and the (post_id, user_id) uniqueness
     * that 2026_04_26_000013 intended. No-op where the column survived.
     */
    public function up(): void
    {
        if (!Schema::hasTable('forum_likes')) {
            return;
        }

        if (!Schema::hasColumn('forum_likes', 'user_id')) {
            try {
                Schema::table('forum_likes', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable();
                });
            } catch (\Throwable $e) {
            }
        }

        if (!Schema::hasColumn('forum_likes', 'user_id')) {
            return;
        }

        try {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->unique(['post_id', 'user_id']);
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        foreach (['forum_likes_post_id_user_id_unique'] as $index) {
            try {
                Schema::table('forum_likes', function (Blueprint $table) use ($index) {
                    $table->dropUnique($index);
                });
            } catch (\Throwable $e) {
            }
        }
        try {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable $e) {
        }
        if (Schema::hasColumn('forum_likes', 'user_id')) {
            try {
                Schema::table('forum_likes', function (Blueprint $table) {
                    $table->dropColumn('user_id');
                });
            } catch (\Throwable $e) {
            }
        }
    }
};
