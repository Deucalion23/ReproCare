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
        // Clean up orphaned data before adding foreign keys (only for tables
        // that actually still have a user_id column).
        foreach (['checkups', 'health_records', 'menstruation_dailies', 'forum_likes', 'fertility_logs'] as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'user_id')) {
                continue;
            }
            try {
                DB::statement("DELETE FROM {$table} WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM users)");
            } catch (\Throwable $e) {
            }
        }

        // Restore foreign keys (only where the column exists; best effort).
        foreach (['checkups', 'health_records', 'menstruation_dailies', 'forum_likes', 'fertility_logs'] as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'user_id')) {
                continue;
            }
            try {
                Schema::table($table, function (Blueprint $tableBlueprint) {
                    $tableBlueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // FK already exists, skip
            }
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
