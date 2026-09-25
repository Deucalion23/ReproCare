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
            // Rename existing id_image to id_image_front
            $table->renameColumn('id_image', 'id_image_front');
            // Add id_image_back column
            $table->string('id_image_back')->nullable()->after('id_image_front');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id_image_back');
            $table->renameColumn('id_image_front', 'id_image');
        });
    }
};
