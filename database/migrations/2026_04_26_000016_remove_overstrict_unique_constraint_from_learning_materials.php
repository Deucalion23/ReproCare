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
        // Remove the over-strict unique constraint on (title, material_type)
        // This allows having the same title across different material types
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropUnique('idx_unique_title_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the unique constraint if needed
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->unique(['title', 'material_type'], 'idx_unique_title_type');
        });
    }
};
