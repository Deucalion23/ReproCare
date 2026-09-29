<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Google OAuth ("Continue with Google") + mandatory profile completion.
 *
 * Column mapping against the existing ReproCare schema:
 * - google_id            → NEW (nullable, unique). Links a Google account.
 * - is_profile_complete  → NEW (boolean, default false). Gates portal access
 *                           until the patient submits phone + barangay.
 * - phone_number         → maps to the existing `contact_number` column
 *                           (no duplicate column created).
 * - barangay             → existing nullable `barangay` column (validated
 *                           against San Carlos City / RHU 1 catchments).
 * - role                 → existing `role` column. Social login may ONLY ever
 *                           create/provision the patient role ('user' in this
 *                           codebase); staff roles are never created via OAuth.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('email');
            }

            if (! Schema::hasColumn('users', 'is_profile_complete')) {
                $table->boolean('is_profile_complete')->default(false)->after('google_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'google_id')) {
                try {
                    $table->dropUnique(['google_id']);
                } catch (\Throwable $e) {
                    // Index name may differ per driver — fall through to column drop.
                }
                $table->dropColumn('google_id');
            }

            if (Schema::hasColumn('users', 'is_profile_complete')) {
                $table->dropColumn('is_profile_complete');
            }
        });
    }
};
