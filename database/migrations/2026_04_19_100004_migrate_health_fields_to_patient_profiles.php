<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This data migration:
     * 1. Creates profile rows for all existing users of each role
     *    (backfilling midwife_profiles, bhw_profiles, and patient_profiles)
     * 2. Copies patient-specific health fields from users → patient_profiles
     * 3. Drops the moved columns from the users table
     */
    public function up(): void
    {
        // ── 1. Create midwife_profiles rows for all existing midwife users ──────
        DB::table('users')
            ->where('role', 'midwife')
            ->orderBy('id')
            ->chunk(200, function ($users) {
                foreach ($users as $user) {
                    DB::table('midwife_profiles')->insertOrIgnore([
                        'user_id'    => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

        // ── 2. Create bhw_profiles rows for all existing BHW users ──────────────
        DB::table('users')
            ->where('role', 'bhw')
            ->orderBy('id')
            ->chunk(200, function ($users) {
                foreach ($users as $user) {
                    DB::table('bhw_profiles')->insertOrIgnore([
                        'user_id'           => $user->id,
                        'assigned_barangay' => $user->barangay ?? null,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }
            });

        // ── 3. Create patient_profiles rows, migrating health fields ─────────────
        DB::table('users')
            ->where('role', 'user')
            ->orderBy('id')
            ->chunk(200, function ($users) {
                foreach ($users as $user) {
                    DB::table('patient_profiles')->insertOrIgnore([
                        'user_id'                => $user->id,
                        'feeding_method'         => $user->feeding_method         ?? null,
                        'family_planning_method' => $user->family_planning_method ?? null,
                        'vitamins'               => $user->vitamins               ?? null,
                        'medical_history'        => $user->medical_history        ?? null,
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ]);
                }
            });

        // ── 4. Drop moved columns from users table ───────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'feeding_method',
                'family_planning_method',
                'vitamins',
                'medical_history',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Restores the four health-profile columns to users and clears profile tables.
     * Note: Data previously in those columns is NOT restored (use a backup to recover).
     */
    public function down(): void
    {
        // Restore columns to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('feeding_method')->nullable()->after('contact_number');
            $table->string('family_planning_method')->nullable()->after('feeding_method');
            $table->string('vitamins')->nullable()->after('family_planning_method');
            $table->json('medical_history')->nullable()->after('vitamins');
        });

        // Restore data from patient_profiles back to users (best-effort)
        DB::table('patient_profiles')
            ->orderBy('user_id')
            ->chunk(200, function ($profiles) {
                foreach ($profiles as $profile) {
                    DB::table('users')->where('id', $profile->user_id)->update([
                        'feeding_method'         => $profile->feeding_method,
                        'family_planning_method' => $profile->family_planning_method,
                        'vitamins'               => $profile->vitamins,
                        'medical_history'        => $profile->medical_history,
                    ]);
                }
            });
    }
};
