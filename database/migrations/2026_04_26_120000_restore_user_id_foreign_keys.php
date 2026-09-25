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
        // Clean up orphaned data before adding foreign keys
        DB::statement("DELETE FROM checkups WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM users)");
        DB::statement("DELETE FROM health_records WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM users)");
        DB::statement("DELETE FROM menstruation_dailies WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM users)");
        DB::statement("DELETE FROM forum_likes WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM users)");
        DB::statement("DELETE FROM fertility_logs WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM users)");

        // Restore foreign key for checkups.user_id (if not exists)
        if (!Schema::hasColumn('checkups', 'user_id')) {
            // Skip if column doesn't exist
        } else {
            try {
                Schema::table('checkups', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // FK already exists, skip
            }
        }

        // Restore foreign key for health_records.user_id (if not exists)
        try {
            Schema::table('health_records', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // FK already exists, skip
        }

        // Restore foreign key for menstruation_dailies.user_id (if not exists)
        try {
            Schema::table('menstruation_dailies', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // FK already exists, skip
        }

        // Restore foreign key for forum_likes.user_id (if not exists)
        try {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // FK already exists, skip
        }

        // Add foreign key for fertility_logs.user_id (if not exists)
        try {
            Schema::table('fertility_logs', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // FK already exists, skip
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('health_records', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('menstruation_dailies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('forum_likes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('fertility_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
