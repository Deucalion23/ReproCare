<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->decimal('basal_temp', 4, 2)->nullable()->after('notes');
            $table->string('cervical_mucus')->nullable()->after('basal_temp');
            $table->string('ovulation_test')->nullable()->after('cervical_mucus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->dropColumn(['basal_temp', 'cervical_mucus', 'ovulation_test']);
        });
    }
};
