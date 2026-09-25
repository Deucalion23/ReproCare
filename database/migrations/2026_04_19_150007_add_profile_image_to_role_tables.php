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
        // Add profile_image to midwives table
        Schema::table('midwives', function (Blueprint $table) {
            $table->string('profile_image')->nullable()->after('phone');
        });

        // Add profile_image to patients table
        Schema::table('patients', function (Blueprint $table) {
            $table->string('profile_image')->nullable()->after('phone');
        });

        // Add profile_image to bhws table
        Schema::table('bhws', function (Blueprint $table) {
            $table->string('profile_image')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('midwives', function (Blueprint $table) {
            $table->dropColumn('profile_image');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('profile_image');
        });

        Schema::table('bhws', function (Blueprint $table) {
            $table->dropColumn('profile_image');
        });
    }
};
