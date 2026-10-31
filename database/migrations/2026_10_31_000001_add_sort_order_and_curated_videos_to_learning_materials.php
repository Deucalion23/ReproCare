<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds an explicit display order (pinned materials sort first) and
     * publishes four curated maternal-health videos, led by Kurzgesagt's
     * "Pregnancy is Insane". Insertion is keyed by youtube_id so reruns
     * and fresh seeds never duplicate rows.
     */
    public function up(): void
    {
        if (!Schema::hasTable('learning_materials')) {
            return;
        }

        if (!Schema::hasColumn('learning_materials', 'sort_order')) {
            Schema::table('learning_materials', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('week_number');
            });
        }

        $videos = [
            [
                'title' => 'Pregnancy is Insane (Kurzgesagt)',
                'content' => 'Kurzgesagt\'s animated tour of the biology of pregnancy: how one cell builds a whole human, what the placenta and umbilical cord do, how organs shift to make room, and why the mother\'s body changes so dramatically.',
                'youtube_id' => 'Zpq1GbPqhy4',
                'category' => 'prenatal-care',
                'sort_order' => 100,
            ],
            [
                'title' => 'Understanding Warning Signs in Pregnancy (Medical Aid Films)',
                'content' => 'Community health film for mothers and families: danger signs to act on quickly — severe headache, blurred vision, swelling, bleeding, fever, reduced baby movement — and why reaching a clinic fast saves lives.',
                'youtube_id' => 'swhPa4DS9vs',
                'category' => 'warning-signs',
                'sort_order' => 10,
            ],
            [
                'title' => 'Pregnancy 101 | National Geographic',
                'content' => 'National Geographic follows conception to birth: fertilization, implantation, trimester-by-trimester growth, and the changes a woman\'s body goes through along the way.',
                'youtube_id' => 'XEfnq4Q4bfk',
                'category' => 'prenatal-care',
                'sort_order' => 9,
            ],
            [
                'title' => 'Mental Health Support for New Mothers (Stanford Health Education)',
                'content' => 'Stanford Center for Health Education on emotional wellbeing after birth: baby blues versus postpartum depression and anxiety, the warning signs to watch for, and where mothers can get support.',
                'youtube_id' => 'mDpBgpMPI0Y',
                'category' => 'postpartum',
                'sort_order' => 8,
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
        if (Schema::hasTable('learning_materials')) {
            DB::table('learning_materials')->whereIn('youtube_id', [
                'Zpq1GbPqhy4',
                'swhPa4DS9vs',
                'XEfnq4Q4bfk',
                'mDpBgpMPI0Y',
            ])->delete();
        }

        if (Schema::hasColumn('learning_materials', 'sort_order')) {
            Schema::table('learning_materials', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
