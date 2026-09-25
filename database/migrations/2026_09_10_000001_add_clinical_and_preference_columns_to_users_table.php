<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Clinical identification + workflow preference columns for
     * clinical validators (midwives). All nullable / defaulted so
     * existing accounts keep working.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'license_number')) {
                $table->string('license_number')->nullable()->after('contact_number');
            }
            if (!Schema::hasColumn('users', 'license_expiry')) {
                $table->date('license_expiry')->nullable()->after('license_number');
            }
            if (!Schema::hasColumn('users', 'specialization')) {
                $table->string('specialization')->nullable()->after('license_expiry');
            }
            if (!Schema::hasColumn('users', 'pref_high_risk_email')) {
                $table->boolean('pref_high_risk_email')->default(true)->after('sms_opt_out');
            }
            if (!Schema::hasColumn('users', 'pref_high_risk_sms')) {
                $table->boolean('pref_high_risk_sms')->default(true)->after('pref_high_risk_email');
            }
            if (!Schema::hasColumn('users', 'pref_high_risk_dashboard')) {
                $table->boolean('pref_high_risk_dashboard')->default(true)->after('pref_high_risk_sms');
            }
            if (!Schema::hasColumn('users', 'pref_approval_summary')) {
                $table->string('pref_approval_summary', 20)->default('daily')->after('pref_high_risk_dashboard');
            }
            if (!Schema::hasColumn('users', 'pref_escalation_alerts')) {
                $table->boolean('pref_escalation_alerts')->default(true)->after('pref_approval_summary');
            }
            if (!Schema::hasColumn('users', 'pref_2fa_enabled')) {
                $table->boolean('pref_2fa_enabled')->default(false)->after('pref_escalation_alerts');
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
                'license_number',
                'license_expiry',
                'specialization',
                'pref_high_risk_email',
                'pref_high_risk_sms',
                'pref_high_risk_dashboard',
                'pref_approval_summary',
                'pref_escalation_alerts',
                'pref_2fa_enabled',
            ]);
        });
    }
};
