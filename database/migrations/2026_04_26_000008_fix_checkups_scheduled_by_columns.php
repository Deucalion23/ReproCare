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
        // If both columns exist, migrate data from scheduled_by_user_id to scheduled_by_id
        if (Schema::hasColumn('checkups', 'scheduled_by_id') && Schema::hasColumn('checkups', 'scheduled_by_user_id')) {
            // Copy data from scheduled_by_user_id to scheduled_by_id where scheduled_by_id is null
            DB::statement("
                UPDATE checkups
                SET scheduled_by_id = scheduled_by_user_id
                WHERE scheduled_by_id IS NULL AND scheduled_by_user_id IS NOT NULL
            ");

            // Drop foreign key constraint and index if they exist
            try {
                Schema::table('checkups', function (Blueprint $table) {
                    $table->dropForeign(['scheduled_by_user_id']);
                });
            } catch (\Exception $e) {
                // Foreign key may not exist, continue
            }

            try {
                Schema::table('checkups', function (Blueprint $table) {
                    $table->dropIndex(['scheduled_by_user_id']);
                });
            } catch (\Exception $e) {
                // Index may not exist, continue
            }

            // Drop the redundant scheduled_by_user_id column
            Schema::table('checkups', function (Blueprint $table) {
                $table->dropColumn('scheduled_by_user_id');
            });
        }

        // If only scheduled_by_user_id exists (and not scheduled_by_id), rename it
        if (!Schema::hasColumn('checkups', 'scheduled_by_id') && Schema::hasColumn('checkups', 'scheduled_by_user_id')) {
            Schema::table('checkups', function (Blueprint $table) {
                $table->renameColumn('scheduled_by_user_id', 'scheduled_by_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a fix migration, no meaningful rollback needed
    }
};
