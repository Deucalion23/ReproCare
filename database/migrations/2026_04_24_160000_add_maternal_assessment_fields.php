<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            if (!Schema::hasColumn('pregnancies', 'risk_level')) {
                $table->string('risk_level')->default('Low')->after('para');
            }
            if (!Schema::hasColumn('pregnancies', 'risk_assessment_mode')) {
                $table->string('risk_assessment_mode')->default('automatic')->after('risk_level');
            }
            if (!Schema::hasColumn('pregnancies', 'risk_notes')) {
                $table->text('risk_notes')->nullable()->after('risk_assessment_mode');
            }
            if (!Schema::hasColumn('pregnancies', 'blood_pressure')) {
                $table->string('blood_pressure')->nullable()->after('risk_notes');
            }
            if (!Schema::hasColumn('pregnancies', 'weight')) {
                $table->decimal('weight', 5, 2)->nullable()->after('blood_pressure');
            }
            if (!Schema::hasColumn('pregnancies', 'height')) {
                $table->decimal('height', 5, 2)->nullable()->after('weight');
            }
            if (!Schema::hasColumn('pregnancies', 'bmi')) {
                $table->decimal('bmi', 5, 2)->nullable()->after('height');
            }
            if (!Schema::hasColumn('pregnancies', 'smoking_status')) {
                $table->string('smoking_status')->nullable()->after('bmi');
            }
            if (!Schema::hasColumn('pregnancies', 'alcohol_status')) {
                $table->string('alcohol_status')->nullable()->after('smoking_status');
            }
            if (!Schema::hasColumn('pregnancies', 'drug_use_status')) {
                $table->string('drug_use_status')->nullable()->after('alcohol_status');
            }
            if (!Schema::hasColumn('pregnancies', 'lifestyle_notes')) {
                $table->text('lifestyle_notes')->nullable()->after('drug_use_status');
            }
            if (!Schema::hasColumn('pregnancies', 'obstetric_history')) {
                $table->text('obstetric_history')->nullable()->after('lifestyle_notes');
            }
            if (!Schema::hasColumn('pregnancies', 'outcome')) {
                $table->string('outcome')->nullable()->after('obstetric_history');
            }
            if (!Schema::hasColumn('pregnancies', 'outcome_details')) {
                $table->text('outcome_details')->nullable()->after('outcome');
            }
        });

        Schema::table('health_records', function (Blueprint $table) {
            if (!Schema::hasColumn('health_records', 'height')) {
                $table->decimal('height', 5, 2)->nullable()->after('weight');
            }
            if (!Schema::hasColumn('health_records', 'bmi')) {
                $table->decimal('bmi', 5, 2)->nullable()->after('height');
            }
            if (!Schema::hasColumn('health_records', 'smoking_status')) {
                $table->string('smoking_status')->nullable()->after('lab_results');
            }
            if (!Schema::hasColumn('health_records', 'alcohol_use')) {
                $table->string('alcohol_use')->nullable()->after('smoking_status');
            }
            if (!Schema::hasColumn('health_records', 'drug_use')) {
                $table->string('drug_use')->nullable()->after('alcohol_use');
            }
            if (!Schema::hasColumn('health_records', 'lifestyle_notes')) {
                $table->text('lifestyle_notes')->nullable()->after('drug_use');
            }
            if (!Schema::hasColumn('health_records', 'obstetric_history')) {
                $table->text('obstetric_history')->nullable()->after('lifestyle_notes');
            }
            if (!Schema::hasColumn('health_records', 'risk_assessment_mode')) {
                $table->string('risk_assessment_mode')->default('automatic')->after('risk_level');
            }
            if (!Schema::hasColumn('health_records', 'risk_notes')) {
                $table->text('risk_notes')->nullable()->after('risk_assessment_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $columns = [
                'risk_level',
                'risk_assessment_mode',
                'risk_notes',
                'blood_pressure',
                'weight',
                'height',
                'bmi',
                'smoking_status',
                'alcohol_status',
                'drug_use_status',
                'lifestyle_notes',
                'obstetric_history',
                'outcome',
                'outcome_details',
            ];

            $existing = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('pregnancies', $column)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });

        Schema::table('health_records', function (Blueprint $table) {
            $columns = [
                'height',
                'bmi',
                'smoking_status',
                'alcohol_use',
                'drug_use',
                'lifestyle_notes',
                'obstetric_history',
                'risk_assessment_mode',
                'risk_notes',
            ];

            $existing = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('health_records', $column)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
