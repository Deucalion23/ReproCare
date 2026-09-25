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
        Schema::table('learning_materials', function (Blueprint $table) {
            if (!Schema::hasColumn('learning_materials', 'quiz_data')) {
                $table->json('quiz_data')->nullable()->after('video_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            if (Schema::hasColumn('learning_materials', 'quiz_data')) {
                $table->dropColumn('quiz_data');
            }
        });
    }
};
