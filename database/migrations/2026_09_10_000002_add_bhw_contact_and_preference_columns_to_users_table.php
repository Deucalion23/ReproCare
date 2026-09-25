<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Secondary contact channels + field-work preference columns for
     * Barangay Health Workers. All nullable / defaulted so existing
     * accounts keep working.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'secondary_email')) {
                $table->string('secondary_email')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'secondary_contact')) {
                $table->string('secondary_contact', 20)->nullable()->after('contact_number');
            }
            if (!Schema::hasColumn('users', 'pref_registration_email')) {
                $table->boolean('pref_registration_email')->default(true)->after('pref_2fa_enabled');
            }
            if (!Schema::hasColumn('users', 'pref_registration_sms')) {
                $table->boolean('pref_registration_sms')->default(true)->after('pref_registration_email');
            }
            if (!Schema::hasColumn('users', 'pref_registration_dashboard')) {
                $table->boolean('pref_registration_dashboard')->default(true)->after('pref_registration_sms');
            }
            if (!Schema::hasColumn('users', 'pref_checkup_reminders')) {
                $table->boolean('pref_checkup_reminders')->default(true)->after('pref_registration_dashboard');
            }
            if (!Schema::hasColumn('users', 'pref_report_summary')) {
                $table->string('pref_report_summary', 20)->default('monthly')->after('pref_checkup_reminders');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'secondary_email',
                'secondary_contact',
                'pref_registration_email',
                'pref_registration_sms',
                'pref_registration_dashboard',
                'pref_checkup_reminders',
                'pref_report_summary',
            ]);
        });
    }
};
