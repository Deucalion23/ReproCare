<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cycles', function (Blueprint $table) {
            $table->unsignedTinyInteger('pain_score')->nullable()->after('flow_intensity');
            $table->unsignedTinyInteger('missed_school_work_days')->nullable()->after('pain_score');
            $table->boolean('irregular_bleeding')->default(false)->after('missed_school_work_days');
            $table->boolean('severe_symptoms')->default(false)->after('irregular_bleeding');
        });
    }

    public function down(): void
    {
        Schema::table('cycles', function (Blueprint $table) {
            $table->dropColumn(['pain_score', 'missed_school_work_days', 'irregular_bleeding', 'severe_symptoms']);
        });
    }
};
