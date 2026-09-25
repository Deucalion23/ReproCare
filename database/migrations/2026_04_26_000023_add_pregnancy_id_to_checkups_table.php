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
        // Step 1: Add pregnancy_id column (nullable). No ->after(): checkups
        // has no user_id column at this point (MySQL errors on AFTER missing).
        if (!Schema::hasColumn('checkups', 'pregnancy_id')) {
            Schema::table('checkups', function (Blueprint $table) {
                $table->unsignedBigInteger('pregnancy_id')->nullable();
            });
        }

        // Step 2: Populate pregnancy_id for existing checkups
        // For each checkup, find the active pregnancy for that user at the time.
        // Skipped when the legacy user_id column is gone (fresh installs).
        if (!Schema::hasColumn('checkups', 'user_id') || !Schema::hasColumn('pregnancies', 'user_id')) {
            $checkups = collect();
        } else {
            $checkups = DB::table('checkups')->whereNotNull('user_id')->get();
        }

        foreach ($checkups as $checkup) {
            // Find the pregnancy that was active at the time of the checkup
            // A pregnancy is active if: created_at <= checkup.scheduled_date AND (ended_at IS NULL OR ended_at >= checkup.scheduled_date)
            $pregnancy = DB::table('pregnancies')
                ->where('user_id', $checkup->user_id)
                ->where('created_at', '<=', $checkup->scheduled_date)
                ->where(function ($query) use ($checkup) {
                    $query->whereNull('ended_at')
                          ->orWhere('ended_at', '>=', $checkup->scheduled_date);
                })
                ->orderBy('created_at', 'desc')
                ->first();

            if ($pregnancy) {
                DB::table('checkups')
                    ->where('id', $checkup->id)
                    ->update(['pregnancy_id' => $pregnancy->id]);
            }
        }

        // Step 3: Add foreign key constraint (best effort)
        try {
            Schema::table('checkups', function (Blueprint $table) {
                $table->foreign('pregnancy_id')->references('id')->on('pregnancies')->onDelete('set null');
            });
        } catch (\Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropForeign(['pregnancy_id']);
            $table->dropColumn('pregnancy_id');
        });
    }
};
