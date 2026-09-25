<?php

namespace Database\Seeders;

use App\Models\LearningMaterial;
use Illuminate\Database\Seeder;

class LearningMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Articles
        LearningMaterial::create([
            'title' => 'Healthy Pregnancy Tips',
            'content' => 'Maintain a balanced diet rich in folic acid, iron, and calcium. Exercise regularly and get enough rest. Avoid alcohol, smoking, and drugs. Attend all prenatal checkups and follow your healthcare provider\'s advice.',
            'material_type' => 'article',
        ]);

        LearningMaterial::create([
            'title' => 'Prenatal Care Guidelines',
            'content' => 'Regular prenatal checkups are essential for monitoring maternal and fetal health. Schedule monthly visits until 28 weeks, then bi-weekly until 36 weeks, and weekly until delivery. Bring questions to each appointment.',
            'material_type' => 'article',
        ]);

        LearningMaterial::create([
            'title' => 'Nutrition During Pregnancy',
            'content' => 'Eat a variety of fruits, vegetables, whole grains, and lean proteins. Take prenatal vitamins daily. Stay hydrated with 8-10 glasses of water. Avoid raw fish, unpasteurized dairy, and deli meats.',
            'material_type' => 'article',
        ]);

        LearningMaterial::create([
            'title' => 'Exercise for Expectant Mothers',
            'content' => 'Low-impact exercises like walking, swimming, and prenatal yoga are beneficial. Aim for 30 minutes of moderate activity most days. Avoid exercises that involve lying flat on your back after the first trimester.',
            'material_type' => 'article',
        ]);

        LearningMaterial::create([
            'title' => 'Common Pregnancy Discomforts',
            'content' => 'Morning sickness, fatigue, and back pain are common. Eat small, frequent meals for nausea. Use pillows for support when sleeping. Practice good posture and wear comfortable shoes.',
            'material_type' => 'article',
        ]);

        // Links
        LearningMaterial::create([
            'title' => 'WHO Pregnancy Guidelines',
            'content' => 'Official World Health Organization guidelines for prenatal care and maternal health.',
            'material_type' => 'link',
            'link_url' => 'https://www.who.int/health-topics/pregnancy',
        ]);

        LearningMaterial::create([
            'title' => 'CDC Pregnancy Resources',
            'content' => 'Centers for Disease Control and Prevention comprehensive pregnancy resources.',
            'material_type' => 'link',
            'link_url' => 'https://www.cdc.gov/reproductive-health/pregnancy/index.htm',
        ]);

        LearningMaterial::create([
            'title' => 'March of Dimes Pregnancy Guide',
            'content' => 'Educational resources and support for healthy pregnancies from March of Dimes.',
            'material_type' => 'link',
            'link_url' => 'https://www.marchofdimes.org/pregnancy',
        ]);

        LearningMaterial::create([
            'title' => 'American College of Obstetricians and Gynecologists',
            'content' => 'ACOG provides authoritative guidance on women\'s health and pregnancy care.',
            'material_type' => 'link',
            'link_url' => 'https://www.acog.org/womens-health/pregnancy',
        ]);

        // Files (simulated as articles with file references)
        LearningMaterial::create([
            'title' => 'Pregnancy Checklist',
            'content' => 'Downloadable checklist for each trimester including appointments, tests, and preparations needed. This file helps you stay organized throughout your pregnancy journey.',
            'material_type' => 'file',
        ]);

        LearningMaterial::create([
            'title' => 'Birth Plan Template',
            'content' => 'Customizable birth plan template to help you communicate your preferences for labor and delivery with your healthcare team. Includes options for pain management, birthing positions, and postpartum care.',
            'material_type' => 'file',
        ]);

        LearningMaterial::create([
            'title' => 'Newborn Care Guide',
            'content' => 'Comprehensive guide for newborn care including feeding, sleeping, bathing, and health monitoring. Essential information for first-time parents preparing for their baby\'s arrival.',
            'material_type' => 'file',
        ]);

        LearningMaterial::create([
            'title' => 'Postpartum Recovery Tips',
            'content' => 'Downloadable guide for postpartum recovery including physical healing, emotional wellness, and when to seek medical help. Covers the first 6 weeks after delivery.',
            'material_type' => 'file',
        ]);

        $this->command->info('Learning materials seeded successfully!');
    }
}
