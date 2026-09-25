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
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            $table->dropColumn('symptoms');
            $table->dropColumn('mood');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            $table->json('symptoms')->nullable()->after('flow_intensity');
            $table->json('mood')->nullable()->after('symptoms');
        });
    }
};
