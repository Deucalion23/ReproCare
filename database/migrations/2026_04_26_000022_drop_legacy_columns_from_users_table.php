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
        Schema::table('users', function (Blueprint $table) {
            // Drop legacy columns that should be in profile tables or other specific tables
            $table->dropColumn(['feeding_method', 'family_planning_method', 'vitamins']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('feeding_method')->nullable();
            $table->string('family_planning_method')->nullable();
            $table->string('vitamins')->nullable();
        });
    }
};
