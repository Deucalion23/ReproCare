<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
     * Marks health records self-reported by the woman herself, so the
     * BHW President → Midwife validation queue can tell them apart from
     * BHW-recorded vitals.
     */
    public function up(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            if (!Schema::hasColumn('health_records', 'recorded_by_user_id')) {
                $table->unsignedBigInteger('recorded_by_user_id')->nullable();
            }
        });

        // Foreign key (best effort — may already exist from an earlier migration).
        try {
            Schema::table('health_records', function (Blueprint $table) {
                $table->foreign('recorded_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            try {
                $table->dropForeign(['recorded_by_user_id']);
            } catch (\Exception $e) {
            }
            if (Schema::hasColumn('health_records', 'recorded_by_user_id')) {
                $table->dropColumn('recorded_by_user_id');
            }
        });
    }
};
