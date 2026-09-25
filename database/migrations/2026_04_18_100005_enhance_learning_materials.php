<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            if (!Schema::hasColumn('learning_materials', 'category')) {
                $table->enum('category', [
                    'general', 'nutrition', 'warning-signs',
                    'family-planning', 'postpartum', 'pregnancy-guide'
                ])->default('general')->after('image');
            }
            if (!Schema::hasColumn('learning_materials', 'video_url')) {
                $table->string('video_url')->nullable()->after('link_url');
            }
            if (!Schema::hasColumn('learning_materials', 'quiz_data')) {
                $table->json('quiz_data')->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('learning_materials', 'week_number')) {
                $table->unsignedTinyInteger('week_number')->nullable()->after('quiz_data');
            }
        });

        // Extend material_type ENUM to include video and quiz
        DB::statement("ALTER TABLE learning_materials MODIFY COLUMN material_type ENUM('article','link','file','video','quiz') DEFAULT 'article'");
    }

    public function down(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropColumn(['category', 'video_url', 'quiz_data', 'week_number']);
        });
        DB::statement("ALTER TABLE learning_materials MODIFY COLUMN material_type ENUM('article','link','file') DEFAULT 'article'");
    }
};
