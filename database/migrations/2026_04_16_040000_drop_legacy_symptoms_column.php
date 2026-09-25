<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drop the legacy symptoms column from menstruation_records table
     * Data has been migrated to the normalized pivot table (menstruation_record_symptoms)
     */
    public function up(): void
    {
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->dropColumn('symptoms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->text('symptoms')->nullable()->after('end_date');
        });
    }
};
