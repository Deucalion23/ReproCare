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
        // Skip column creation if they already exist (partial migration)
        if (!Schema::hasColumn('messages', 'sender_id') || !Schema::hasColumn('messages', 'receiver_id')) {
            Schema::table('messages', function (Blueprint $table) {
                if (!Schema::hasColumn('messages', 'sender_id')) {
                    $table->unsignedBigInteger('sender_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('messages', 'receiver_id')) {
                    $table->unsignedBigInteger('receiver_id')->nullable()->after('sender_id');
                }
            });
        }

        // Migrate sender data from polymorphic columns to single sender_id
        // Only migrate if the user exists in users table
        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.sender_woman_id = u.id AND u.role = 'user'
            SET m.sender_id = m.sender_woman_id
            WHERE m.sender_woman_id IS NOT NULL
        ");

        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.sender_midwife_id = u.id AND u.role = 'midwife'
            SET m.sender_id = m.sender_midwife_id
            WHERE m.sender_midwife_id IS NOT NULL
        ");

        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.sender_bhw_id = u.id AND u.role IN ('bhw', 'bhw_president')
            SET m.sender_id = m.sender_bhw_id
            WHERE m.sender_bhw_id IS NOT NULL
        ");

        // Migrate receiver data from polymorphic columns to single receiver_id
        // Only migrate if the user exists in users table
        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.receiver_woman_id = u.id AND u.role = 'user'
            SET m.receiver_id = m.receiver_woman_id
            WHERE m.receiver_woman_id IS NOT NULL
        ");

        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.receiver_midwife_id = u.id AND u.role = 'midwife'
            SET m.receiver_id = m.receiver_midwife_id
            WHERE m.receiver_midwife_id IS NOT NULL
        ");

        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.receiver_bhw_id = u.id AND u.role IN ('bhw', 'bhw_president')
            SET m.receiver_id = m.receiver_bhw_id
            WHERE m.receiver_bhw_id IS NOT NULL
        ");

        // Delete messages where sender or receiver couldn't be migrated (orphaned)
        DB::statement("
            DELETE FROM messages
            WHERE sender_id IS NULL OR receiver_id IS NULL
        ");

        // Delete messages where sender_id or receiver_id don't exist in users table
        DB::statement("
            DELETE FROM messages
            WHERE sender_id NOT IN (SELECT id FROM users)
               OR receiver_id NOT IN (SELECT id FROM users)
        ");

        // Now add foreign key constraints for new columns
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add indexes for new columns
        Schema::table('messages', function (Blueprint $table) {
            $table->index('sender_id');
            $table->index('receiver_id');
        });

        // Drop old polymorphic columns
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['sender_woman_id']);
            $table->dropIndex(['sender_midwife_id']);
            $table->dropIndex(['sender_bhw_id']);
            $table->dropIndex(['receiver_woman_id']);
            $table->dropIndex(['receiver_midwife_id']);
            $table->dropIndex(['receiver_bhw_id']);
            $table->dropColumn([
                'sender_woman_id',
                'sender_midwife_id',
                'sender_bhw_id',
                'receiver_woman_id',
                'receiver_midwife_id',
                'receiver_bhw_id',
            ]);
        });

        // Drop scheduled_at and is_sent columns
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['scheduled_at', 'is_sent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Re-add polymorphic columns
            $table->unsignedBigInteger('sender_woman_id')->nullable()->after('id');
            $table->unsignedBigInteger('sender_midwife_id')->nullable()->after('sender_woman_id');
            $table->unsignedBigInteger('sender_bhw_id')->nullable()->after('sender_midwife_id');
            $table->unsignedBigInteger('receiver_woman_id')->nullable()->after('sender_bhw_id');
            $table->unsignedBigInteger('receiver_midwife_id')->nullable()->after('receiver_woman_id');
            $table->unsignedBigInteger('receiver_bhw_id')->nullable()->after('receiver_midwife_id');

            // Re-add scheduled_at and is_sent columns
            $table->timestamp('scheduled_at')->nullable()->after('reply_to_id');
            $table->boolean('is_sent')->default(true)->after('scheduled_at');
        });

        // Migrate sender data back to polymorphic columns
        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.sender_id = u.id
            SET m.sender_woman_id = m.sender_id
            WHERE u.role = 'user'
        ");

        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.sender_id = u.id
            SET m.sender_midwife_id = m.sender_id
            WHERE u.role = 'midwife'
        ");

        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.sender_id = u.id
            SET m.sender_bhw_id = m.sender_id
            WHERE u.role IN ('bhw', 'bhw_president')
        ");

        // Migrate receiver data back to polymorphic columns
        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.receiver_id = u.id
            SET m.receiver_woman_id = m.receiver_id
            WHERE u.role = 'user'
        ");

        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.receiver_id = u.id
            SET m.receiver_midwife_id = m.receiver_id
            WHERE u.role = 'midwife'
        ");

        DB::statement("
            UPDATE messages m
            INNER JOIN users u ON m.receiver_id = u.id
            SET m.receiver_bhw_id = m.receiver_id
            WHERE u.role IN ('bhw', 'bhw_president')
        ");

        // Add foreign keys and indexes for polymorphic columns
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_woman_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sender_midwife_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sender_bhw_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_woman_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_midwife_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_bhw_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('sender_woman_id');
            $table->index('sender_midwife_id');
            $table->index('sender_bhw_id');
            $table->index('receiver_woman_id');
            $table->index('receiver_midwife_id');
            $table->index('receiver_bhw_id');
        });

        // Drop new simplified columns
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
            $table->dropIndex(['sender_id']);
            $table->dropIndex(['receiver_id']);
            $table->dropColumn(['sender_id', 'receiver_id']);
        });
    }
};
