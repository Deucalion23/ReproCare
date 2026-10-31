<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds two breastfeeding videos requested after the curated batch.
     * Keyed by youtube_id so reruns and fresh seeds never duplicate rows.
     */
    public function up(): void
    {
        if (!Schema::hasTable('learning_materials')) {
            return;
        }

        $videos = [
            [
                'title' => '7 Methods To Dry Up Breast Milk (Medical Centric)',
                'content' => 'For mothers who need to stop producing milk — after loss, when returning to work, or on medical advice: gradual weaning, cold compresses and cabbage leaves, avoiding stimulation, pain relief, and when to call a doctor.',
                'youtube_id' => '2_D7iXEtwwk',
                'category' => 'breastfeeding',
                'sort_order' => 7,
            ],
            [
                'title' => 'Breastfeeding: Anatomy and Physiology (Osmosis)',
                'content' => 'Osmosis explains how lactation works: mammary gland structure, prolactin and oxytocin, the milk let-down reflex, colostrum versus mature milk, and common clinical issues such as engorgement and mastitis.',
                'youtube_id' => 'T0R33e_P_2Y',
                'category' => 'breastfeeding',
                'sort_order' => 6,
            ],
        ];

        foreach ($videos as $video) {
            $videoUrl = 'https://www.youtube.com/watch?v=' . $video['youtube_id'];

            $exists = DB::table('learning_materials')
                ->where('youtube_id', $video['youtube_id'])
                ->orWhere('video_url', $videoUrl)
                ->exists();

            if ($exists) {
                DB::table('learning_materials')
                    ->where('youtube_id', $video['youtube_id'])
                    ->orWhere('video_url', $videoUrl)
                    ->update([
                        'sort_order' => $video['sort_order'],
                        'updated_at' => now(),
                    ]);
                continue;
            }

            DB::table('learning_materials')->insert([
                'title' => $video['title'],
                'content' => $video['content'],
                'material_type' => 'video',
                'category' => $video['category'],
                'video_url' => $videoUrl,
                'youtube_id' => $video['youtube_id'],
                'sort_order' => $video['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('learning_materials')) {
            return;
        }

        DB::table('learning_materials')->whereIn('youtube_id', [
            '2_D7iXEtwwk',
            'T0R33e_P_2Y',
        ])->delete();
    }
};
