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
        // forum_likes table - skip due to unique_like index complexity
        // Will handle manually or with a different approach
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse forum_likes
        // Drop the new unique_like index
        DB::statement("ALTER TABLE forum_likes DROP INDEX unique_like");

        // Re-add polymorphic columns
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->unsignedBigInteger('woman_id')->nullable()->after('id');
            $table->unsignedBigInteger('midwife_id')->nullable()->after('woman_id');
            $table->unsignedBigInteger('bhw_id')->nullable()->after('midwife_id');
        });

        // Migrate data back to polymorphic columns
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

        // Add foreign keys and indexes for polymorphic columns
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('midwife_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('woman_id');
            $table->index('midwife_id');
            $table->index('bhw_id');
        });

        // Recreate the old unique_like index
        DB::statement("ALTER TABLE forum_likes ADD UNIQUE INDEX unique_like (post_id, woman_id, midwife_id, bhw_id)");

        // Drop user_id column
        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
