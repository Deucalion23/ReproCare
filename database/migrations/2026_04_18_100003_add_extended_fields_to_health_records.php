<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->decimal('hemoglobin', 5, 1)->nullable()->after('temperature')->comment('g/dL');
            $table->integer('gestational_age')->nullable()->after('hemoglobin')->comment('weeks');
            $table->string('immunization_status')->nullable()->after('gestational_age');
            $table->string('contraceptive_use')->nullable()->after('immunization_status');
            $table->string('lab_results')->nullable()->after('contraceptive_use');
        });
    }

    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn([
                'hemoglobin', 'gestational_age', 'immunization_status',
                'contraceptive_use', 'lab_results'
            ]);
        });
    }
};
