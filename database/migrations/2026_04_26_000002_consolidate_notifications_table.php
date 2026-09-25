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
        // Add new user_id column if not exists
        if (!Schema::hasColumn('notifications', 'user_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            });
        }

        // Migrate data from polymorphic columns to user_id (only if user_id is null)
        DB::statement("
            UPDATE notifications n
            INNER JOIN users u ON n.woman_id = u.id AND u.role = 'user'
            SET n.user_id = n.woman_id
            WHERE n.woman_id IS NOT NULL AND n.user_id IS NULL
        ");

        DB::statement("
            UPDATE notifications n
            INNER JOIN users u ON n.midwife_id = u.id AND u.role = 'midwife'
            SET n.user_id = n.midwife_id
            WHERE n.midwife_id IS NOT NULL AND n.user_id IS NULL
        ");

        DB::statement("
            UPDATE notifications n
            INNER JOIN users u ON n.bhw_id = u.id AND u.role IN ('bhw', 'bhw_president')
            SET n.user_id = n.bhw_id
            WHERE n.bhw_id IS NOT NULL AND n.user_id IS NULL
        ");

        // Delete notifications where user_id couldn't be migrated
        DB::statement("
            DELETE FROM notifications
            WHERE user_id IS NULL
        ");

        // Delete notifications where user_id doesn't exist in users table
        DB::statement("
            DELETE FROM notifications
            WHERE user_id NOT IN (SELECT id FROM users)
        ");

        // Add foreign key constraint if not exists
        $fkExists = DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.table_constraints
            WHERE table_schema = DATABASE()
            AND table_name = 'notifications'
            AND constraint_name = 'notifications_user_id_foreign'
            AND constraint_type = 'FOREIGN KEY'
        ");

        if (empty($fkExists) || $fkExists[0]->count == 0) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Add index if not exists
        $indexExists = DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
            AND table_name = 'notifications'
            AND index_name = 'notifications_user_id_index'
        ");

        if (empty($indexExists) || $indexExists[0]->count == 0) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index('user_id');
            });
        }

        // Drop old polymorphic columns
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['woman_id']);
            $table->dropForeign(['midwife_id']);
            $table->dropForeign(['bhw_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['woman_id', 'midwife_id', 'bhw_id']);
        });
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
