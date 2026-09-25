<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up orphaned data before adding foreign keys
        DB::statement('DELETE FROM cycles WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM users)');
        DB::statement('DELETE FROM forum_likes WHERE post_id IS NOT NULL AND post_id NOT IN (SELECT id FROM forum_posts)');
        DB::statement('DELETE FROM pregnancies WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM users)');
        DB::statement('DELETE FROM bhw_monthly_reports WHERE bhw_id IS NOT NULL AND bhw_id NOT IN (SELECT id FROM users)');

        Schema::table('cycles', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('forum_likes', function (Blueprint $table) {
            $table->foreign('post_id')->references('id')->on('forum_posts')->onDelete('cascade');
        });

        Schema::table('pregnancies', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cycles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
        });

        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            $table->dropForeign(['bhw_id']);
        });
    }
};
