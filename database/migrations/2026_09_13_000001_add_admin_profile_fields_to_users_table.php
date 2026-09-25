<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin My Profile fields for CHO Super Admin and RHU Admin:
     * identity, contact, signature, notification channels, delegation.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'official_title')) {
                $table->string('official_title')->nullable()->after('specialization');
            }
            if (!Schema::hasColumn('users', 'employee_id')) {
                $table->string('employee_id')->nullable()->after('official_title');
            }
            if (!Schema::hasColumn('users', 'office_extension')) {
                $table->string('office_extension', 20)->nullable()->after('employee_id');
            }
            if (!Schema::hasColumn('users', 'emergency_mobile')) {
                $table->string('emergency_mobile', 30)->nullable()->after('office_extension');
            }
            if (!Schema::hasColumn('users', 'station_contact')) {
                $table->string('station_contact', 30)->nullable()->after('emergency_mobile');
            }
            if (!Schema::hasColumn('users', 'signature_image')) {
                $table->string('signature_image')->nullable()->after('profile_image');
            }
            if (!Schema::hasColumn('users', 'pref_mortality_alerts')) {
                $table->boolean('pref_mortality_alerts')->default(true)->after('pref_2fa_enabled');
            }
            if (!Schema::hasColumn('users', 'pref_audit_warnings')) {
                $table->boolean('pref_audit_warnings')->default(true)->after('pref_mortality_alerts');
            }
            if (!Schema::hasColumn('users', 'pref_compliance_updates')) {
                $table->boolean('pref_compliance_updates')->default(true)->after('pref_audit_warnings');
            }
            if (!Schema::hasColumn('users', 'pref_bhw_conflicts')) {
                $table->boolean('pref_bhw_conflicts')->default(true)->after('pref_compliance_updates');
            }
            if (!Schema::hasColumn('users', 'pref_pending_reports')) {
                $table->boolean('pref_pending_reports')->default(true)->after('pref_bhw_conflicts');
            }
            if (!Schema::hasColumn('users', 'pref_highrisk_escalation')) {
                $table->boolean('pref_highrisk_escalation')->default(true)->after('pref_pending_reports');
            }
            if (!Schema::hasColumn('users', 'recovery_question_1')) {
                $table->string('recovery_question_1')->nullable()->after('pref_highrisk_escalation');
            }
            if (!Schema::hasColumn('users', 'recovery_answer_1')) {
                $table->string('recovery_answer_1')->nullable()->after('recovery_question_1');
            }
            if (!Schema::hasColumn('users', 'recovery_question_2')) {
                $table->string('recovery_question_2')->nullable()->after('recovery_answer_1');
            }
            if (!Schema::hasColumn('users', 'recovery_answer_2')) {
                $table->string('recovery_answer_2')->nullable()->after('recovery_question_2');
            }
            if (!Schema::hasColumn('users', 'out_of_office')) {
                $table->boolean('out_of_office')->default(false)->after('recovery_answer_2');
            }
            if (!Schema::hasColumn('users', 'delegate_to_user_id')) {
                $table->foreignId('delegate_to_user_id')->nullable()->constrained('users')->nullOnDelete()->after('out_of_office');
            }
            if (!Schema::hasColumn('users', 'ooo_note')) {
                $table->string('ooo_note')->nullable()->after('delegate_to_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'delegate_to_user_id')) {
                $table->dropConstrainedForeignId('delegate_to_user_id');
            }
            $columns = array_filter([
                'official_title', 'employee_id', 'office_extension', 'emergency_mobile',
                'station_contact', 'signature_image', 'pref_mortality_alerts', 'pref_audit_warnings',
                'pref_compliance_updates', 'pref_bhw_conflicts', 'pref_pending_reports',
                'pref_highrisk_escalation', 'recovery_question_1', 'recovery_answer_1',
                'recovery_question_2', 'recovery_answer_2', 'out_of_office', 'ooo_note',
            ], fn ($column) => Schema::hasColumn('users', $column));
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
