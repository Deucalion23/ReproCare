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
        // Tables with woman_id that need to be renamed to user_id
        $tables = [
            'checkups',
            'checkup_referrals',
            'cycles',
            'fertility_logs',
            'health_records',
            'maternal_care_target_clients',
            'menstruation_dailies',
            'menstruation_records',
            'pregnancies',
            'preventive_interventions',
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'woman_id') && !Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->renameColumn('woman_id', 'user_id');
                });
            }
        }

        // Handle forum_likes separately - it has both woman_id and user_id
        // Skip for now, will handle in a separate migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tables that were renamed from user_id back to woman_id
        $tables = [
            'checkups',
            'checkup_referrals',
            'cycles',
            'fertility_logs',
            'health_records',
            'maternal_care_target_clients',
            'menstruation_dailies',
            'menstruation_records',
            'pregnancies',
            'preventive_interventions',
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'user_id') && !Schema::hasColumn($table, 'woman_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->renameColumn('user_id', 'woman_id');
                });
            }
        }

        // Restore forum_likes woman_id column
        if (Schema::hasTable('forum_likes') && !Schema::hasColumn('forum_likes', 'woman_id')) {
            Schema::table('forum_likes', function (Blueprint $table) {
                $table->unsignedBigInteger('woman_id')->nullable()->after('id');
                $table->foreign('woman_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('woman_id');
            });
            DB::statement("UPDATE forum_likes SET woman_id = user_id WHERE user_id IS NOT NULL");
        }
    }
};
