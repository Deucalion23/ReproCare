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
            if (!Schema::hasColumn('learning_materials', 'youtube_id')) {
                $table->string('youtube_id', 16)->nullable()->after('video_url');
            }
        });

        // Backfill youtube_id from existing video_url values.
        $rows = DB::table('learning_materials')
            ->whereNotNull('video_url')
            ->where(function ($q) {
                $q->whereNull('youtube_id')->orWhere('youtube_id', '');
            })
            ->get(['id', 'video_url']);

        foreach ($rows as $row) {
            $id = \App\Models\LearningMaterial::extractYoutubeId((string) $row->video_url);
            if ($id) {
                DB::table('learning_materials')->where('id', $row->id)->update(['youtube_id' => $id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            if (Schema::hasColumn('learning_materials', 'youtube_id')) {
                $table->dropColumn('youtube_id');
            }
        });
    }
};
