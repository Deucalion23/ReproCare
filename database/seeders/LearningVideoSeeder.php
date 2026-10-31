<?php

namespace Database\Seeders;

use App\Models\LearningMaterial;
use Illuminate\Database\Seeder;

/**
 * Curated YouTube health-education videos (featured picks, nutrition, family
 * planning, teenage pregnancy, breastfeeding). Reruns are safe: keyed by
 * video_url. youtube_id is auto-extracted by LearningMaterial::booted() on save.
 * sort_order > 0 pins a video to the front of the Learning Materials list.
 */
class LearningVideoSeeder extends Seeder
{
    public function run(): void
    {
        $videos = [
            // ── Featured (pinned to the front of Learning Materials) ──────
            [
                'title' => 'Pregnancy is Insane (Kurzgesagt)',
                'content' => 'Kurzgesagt\'s animated tour of the biology of pregnancy: how one cell builds a whole human, what the placenta and umbilical cord do, how organs shift to make room, and why the mother\'s body changes so dramatically.',
                'video_url' => 'https://www.youtube.com/watch?v=Zpq1GbPqhy4',
                'youtube_id' => 'Zpq1GbPqhy4',
                'category' => 'prenatal-care',
                'sort_order' => 100,
            ],
            [
                'title' => 'Understanding Warning Signs in Pregnancy (Medical Aid Films)',
                'content' => 'Community health film for mothers and families: danger signs to act on quickly — severe headache, blurred vision, swelling, bleeding, fever, reduced baby movement — and why reaching a clinic fast saves lives.',
                'video_url' => 'https://www.youtube.com/watch?v=swhPa4DS9vs',
                'youtube_id' => 'swhPa4DS9vs',
                'category' => 'warning-signs',
                'sort_order' => 10,
            ],
            [
                'title' => 'Pregnancy 101 | National Geographic',
                'content' => 'National Geographic follows conception to birth: fertilization, implantation, trimester-by-trimester growth, and the changes a woman\'s body goes through along the way.',
                'video_url' => 'https://www.youtube.com/watch?v=XEfnq4Q4bfk',
                'youtube_id' => 'XEfnq4Q4bfk',
                'category' => 'prenatal-care',
                'sort_order' => 9,
            ],
            [
                'title' => 'Mental Health Support for New Mothers (Stanford Health Education)',
                'content' => 'Stanford Center for Health Education on emotional wellbeing after birth: baby blues versus postpartum depression and anxiety, the warning signs to watch for, and where mothers can get support.',
                'video_url' => 'https://www.youtube.com/watch?v=mDpBgpMPI0Y',
                'youtube_id' => 'mDpBgpMPI0Y',
                'category' => 'postpartum',
                'sort_order' => 8,
            ],
            // ── Nutrition & diet in pregnancy ─────────────────────────────
            [
                'title' => 'Nutrition During Pregnancy (Stanford Health Education)',
                'content' => 'Stanford Center for Health Education walks through eating well in pregnancy: handling nausea, colorful fruits and vegetables, proteins and starches, staying hydrated, and why clinic supplements still matter.',
                'video_url' => 'https://www.youtube.com/watch?v=0BrxCY89_uQ',
                'youtube_id' => '0BrxCY89_uQ',
                'category' => 'nutrition',
            ],
            [
                'title' => 'Pregnancy Diet: What to Eat and What to Avoid (UC Davis Health)',
                'content' => 'UC Davis Health dietitians explain balanced eating by trimester, extra calorie needs (+350/day in 2nd trimester, +450/day in 3rd), folic acid, iron with citrus for absorption, and omega-3s for brain development.',
                'video_url' => 'https://www.youtube.com/watch?v=pozcaggYIWk',
                'youtube_id' => 'pozcaggYIWk',
                'category' => 'nutrition',
            ],
            [
                'title' => 'Nutrition Tips for Pregnancy (Loyola Medicine)',
                'content' => 'What a growing baby needs from mom: calcium, iron, folate, DHA, which foods to avoid (raw fish, unpasteurized dairy, high-mercury fish), hydration, caffeine limits, and healthy weight gain.',
                'video_url' => 'https://www.youtube.com/watch?v=IWBF60kQcuk',
                'youtube_id' => 'IWBF60kQcuk',
                'category' => 'nutrition',
            ],
            // ── Family planning ───────────────────────────────────────────
            [
                'title' => 'How Do Contraceptives Work? (TED-Ed)',
                'content' => 'A short animated explainer: contraceptives block, disable, or suppress — condoms, pills, patches, IUDs — plus how typical-use effectiveness differs from perfect use.',
                'video_url' => 'https://www.youtube.com/watch?v=Zx8zbTMTncs',
                'youtube_id' => 'Zx8zbTMTncs',
                'category' => 'family-planning',
            ],
            [
                'title' => 'Family Planning Methods Explained (APGO)',
                'content' => 'Association of Professors of Gynecology and Obstetrics lecture: how each contraceptive method works, LARCs, injections, pills, barrier methods, emergency contraception, and sterilization.',
                'video_url' => 'https://www.youtube.com/watch?v=IEffOROmkbQ',
                'youtube_id' => 'IEffOROmkbQ',
                'category' => 'family-planning',
            ],
            [
                'title' => 'Family Planning Lecture: Natural and Artificial Methods (Dr. Nica Taloma)',
                'content' => 'Full Filipino-friendly lecture: signs of ovulation, calendar and mucus methods, pills, IUDs, injections, implants, and sterilization — with pros, cons, and who each method fits.',
                'video_url' => 'https://www.youtube.com/watch?v=YRc0Htg6ab0',
                'youtube_id' => 'YRc0Htg6ab0',
                'category' => 'family-planning',
            ],
            [
                'title' => 'About Contraception (Sexual Health Victoria)',
                'content' => 'Plain-language tour of every option — condoms, diaphragms, hormonal and copper IUDs, implant, injection, pills, vaginal ring, natural methods, emergency contraception, and permanent options.',
                'video_url' => 'https://www.youtube.com/watch?v=ziSKvk93kPw',
                'youtube_id' => 'ziSKvk93kPw',
                'category' => 'family-planning',
            ],
            // ── Teenage pregnancy ─────────────────────────────────────────
            [
                'title' => 'Teens, Pregnancy, and Birth Control (Johnson County Health Dept.)',
                'content' => 'Made for teens: how each birth-control method works, how effective it really is, STI protection, and where young people can get confidential help.',
                'video_url' => 'https://www.youtube.com/watch?v=GodXgpShxfs',
                'youtube_id' => 'GodXgpShxfs',
                'category' => 'teen-pregnancy',
            ],
            [
                'title' => 'Teen Health: Sexual Health (Penn State PRO Wellness)',
                'content' => 'Why STIs hit young people hardest, why testing matters even without symptoms, condoms, and how to talk to a doctor when it feels awkward.',
                'video_url' => 'https://www.youtube.com/watch?v=4Qyp-7ZfmPM',
                'youtube_id' => '4Qyp-7ZfmPM',
                'category' => 'teen-pregnancy',
            ],
            // ── Breastfeeding & newborn ───────────────────────────────────
            [
                'title' => 'Breastfeeding Master Class (UNICEF)',
                'content' => 'UNICEF lactation consultant Dr. Michele Griswold busts breastfeeding myths: good bacteria, feeding 8–12 times a day, pain vs. normal adjustment, and eating normally while nursing.',
                'video_url' => 'https://www.youtube.com/watch?v=mTm9zvz5-Dc',
                'youtube_id' => 'mTm9zvz5-Dc',
                'category' => 'breastfeeding',
            ],
            [
                'title' => 'Early Initiation of Breastfeeding (Global Health Media Project)',
                'content' => 'For mothers and birth helpers: skin-to-skin in the first hour, letting the newborn self-attach, and giving mother and baby quiet, undisturbed time after birth.',
                'video_url' => 'https://www.youtube.com/watch?v=hs7ai466toE',
                'youtube_id' => 'hs7ai466toE',
                'category' => 'breastfeeding',
            ],
            [
                'title' => 'Attaching Your Baby at the Breast (Global Health Media Project)',
                'content' => 'Deep attachment is the key to milk flow without pain: feeding cues, positioning, wide mouth, chin-to-breast, and how to tell the latch is right.',
                'video_url' => 'https://www.youtube.com/watch?v=wjt-Ashodw8',
                'youtube_id' => 'wjt-Ashodw8',
                'category' => 'breastfeeding',
            ],
        ];

        foreach ($videos as $video) {
            LearningMaterial::firstOrCreate(
                ['video_url' => $video['video_url']],
                [
                    'title' => $video['title'],
                    'content' => $video['content'],
                    'material_type' => 'video',
                    'category' => $video['category'],
                    'sort_order' => $video['sort_order'] ?? 0,
                ]
            );
        }
    }
}
