<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            // Modify category to varchar(100) to support all categories (prenatal-care, hcw-training, etc.)
            DB::statement("ALTER TABLE `learning_materials` MODIFY COLUMN `category` VARCHAR(100) NOT NULL DEFAULT 'general'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            DB::statement("ALTER TABLE `learning_materials` MODIFY COLUMN `category` ENUM('general', 'nutrition', 'warning-signs', 'family-planning', 'postpartum', 'pregnancy-guide') NOT NULL DEFAULT 'general'");
        });
    }
};
