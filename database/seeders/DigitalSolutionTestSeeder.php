<?php

namespace Database\Seeders;

use App\Models\Checkup;
use App\Models\Cycle;
use App\Models\FamilyPlanningRecord;
use App\Models\HealthRecord;
use App\Models\LearningMaterial;
use App\Models\Pregnancy;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * DigitalSolutionTestSeeder — Creates 10 test women accounts plus 1 midwife
 * and 1 BHW, each with realistic data that exercises all 10 ReproCare digital
 * solutions. Idempotent: re-running skips existing emails.
 *
 * Shared password for ALL test accounts: TestReproCare2026!
 *
 * Run with:  php artisan db:seed --class=DigitalSolutionTestSeeder
 */
class DigitalSolutionTestSeeder extends Seeder
{
    private const PASSWORD = 'TestReproCare2026!';

    public function run(): void
    {
        $password = Hash::make(self::PASSWORD);

        // Resolve or create a purok in Cacaritan for test data
        $purok = Purok::firstOrCreate(
            ['name' => 'Purok 1', 'barangay' => 'Cacaritan'],
            ['description' => 'Digital solution test catchment']
        );

        // ─── Staff accounts (midwife + BHW) ────────────────────────────
        $midwife = $this->ensureUser([
            'first_name'      => 'Test',
            'middle_initial'  => 'M',
            'last_name'       => 'Midwife',
            'email'           => 'test.midwife@reprocare.test',
            'password'        => $password,
            'role'            => 'midwife',
            'status'          => 'approved',
            'gender'          => 'female',
            'date_of_birth'   => '1985-06-15',
            'contact_number'  => '09170000001',
            'address'         => 'RHU 1, Cacaritan, San Carlos City',
            'barangay'        => 'Cacaritan',
            'purok_id'        => $purok->id,
            'assigned_barangay' => 'Cacaritan',
            'rhu_assignment'  => 'RHU 1',
        ]);

        $bhw = $this->ensureUser([
            'first_name'      => 'Test',
            'middle_initial'  => 'B',
            'last_name'       => 'BHW',
            'email'           => 'test.bhw@reprocare.test',
            'password'        => $password,
            'role'            => 'bhw',
            'status'          => 'approved',
            'gender'          => 'female',
            'date_of_birth'   => '1990-03-10',
            'contact_number'  => '09170000002',
            'address'         => 'Purok 1, Cacaritan, San Carlos City',
            'barangay'        => 'Cacaritan',
            'purok_id'        => $purok->id,
            'assigned_barangay' => 'Cacaritan',
            'rhu_assignment'  => 'RHU 1',
        ]);

        // ─── 10 Women test accounts ────────────────────────────────────
        $women = [
            // 1. Active pregnancy — 1st trimester (early)
            [
                'first_name' => 'Rosa', 'middle_initial' => 'A', 'last_name' => 'Flores',
                'email' => 'rosa.flores@reprocare.test',
                'date_of_birth' => '1998-05-12', 'contact_number' => '09170000010',
                'scenario' => 'early_pregnancy', 'weeks' => 10,
            ],
            // 2. Active pregnancy — 2nd trimester, high-risk
            [
                'first_name' => 'Lorna', 'middle_initial' => 'B', 'last_name' => 'Santos',
                'email' => 'lorna.santos@reprocare.test',
                'date_of_birth' => '1986-11-23', 'contact_number' => '09170000011',
                'scenario' => 'high_risk_pregnancy', 'weeks' => 24,
            ],
            // 3. Active pregnancy — 3rd trimester, near due date
            [
                'first_name' => 'Carmen', 'middle_initial' => 'C', 'last_name' => 'Reyes',
                'email' => 'carmen.reyes@reprocare.test',
                'date_of_birth' => '1995-01-07', 'contact_number' => '09170000012',
                'scenario' => 'late_pregnancy', 'weeks' => 36,
            ],
            // 4. Regular menstrual cycle tracker
            [
                'first_name' => 'Delia', 'middle_initial' => 'D', 'last_name' => 'Cruz',
                'email' => 'delia.cruz@reprocare.test',
                'date_of_birth' => '2000-08-19', 'contact_number' => '09170000013',
                'scenario' => 'regular_cycle',
            ],
            // 5. Irregular menstrual cycle tracker
            [
                'first_name' => 'Elena', 'middle_initial' => 'E', 'last_name' => 'Garcia',
                'email' => 'elena.garcia@reprocare.test',
                'date_of_birth' => '1999-04-30', 'contact_number' => '09170000014',
                'scenario' => 'irregular_cycle',
            ],
            // 6. Forum-active community member
            [
                'first_name' => 'Fe', 'middle_initial' => 'F', 'last_name' => 'Mendoza',
                'email' => 'fe.mendoza@reprocare.test',
                'date_of_birth' => '1997-09-15', 'contact_number' => '09170000015',
                'scenario' => 'forum_active',
            ],
            // 7. Family planning user (active method)
            [
                'first_name' => 'Gloria', 'middle_initial' => 'G', 'last_name' => 'Aquino',
                'email' => 'gloria.aquino@reprocare.test',
                'date_of_birth' => '1993-12-25', 'contact_number' => '09170000016',
                'scenario' => 'family_planning',
            ],
            // 8. Family planning + cycle tracker combo
            [
                'first_name' => 'Helen', 'middle_initial' => 'H', 'last_name' => 'Bautista',
                'email' => 'helen.bautista@reprocare.test',
                'date_of_birth' => '1996-02-14', 'contact_number' => '09170000017',
                'scenario' => 'fp_and_cycle',
            ],
            // 9. Multiple health records for analytics
            [
                'first_name' => 'Irene', 'middle_initial' => 'I', 'last_name' => 'Tolentino',
                'email' => 'irene.tolentino@reprocare.test',
                'date_of_birth' => '1994-07-04', 'contact_number' => '09170000018',
                'scenario' => 'analytics_data',
            ],
            // 10. New user — pending/onboarding (tests portal access flow)
            [
                'first_name' => 'Julia', 'middle_initial' => 'J', 'last_name' => 'Navarro',
                'email' => 'julia.navarro@reprocare.test',
                'date_of_birth' => '2001-10-01', 'contact_number' => '09170000019',
                'scenario' => 'onboarding',
            ],
        ];

        $createdPatients = [];
        foreach ($women as $index => $w) {
            $user = $this->ensureUser([
                'first_name'     => $w['first_name'],
                'middle_initial' => $w['middle_initial'],
                'last_name'      => $w['last_name'],
                'email'          => $w['email'],
                'password'       => $password,
                'role'           => 'user',
                'status'         => $w['scenario'] === 'onboarding' ? 'pending' : 'approved',
                'gender'         => 'female',
                'date_of_birth'  => $w['date_of_birth'],
                'contact_number' => $w['contact_number'],
                'address'        => 'House ' . ($index + 1) . ', Purok 1, Cacaritan, San Carlos City',
                'barangay'       => 'Cacaritan',
                'purok_id'       => $purok->id,
                'partner_name'   => $w['scenario'] !== 'onboarding' ? 'Partner of ' . $w['first_name'] : null,
            ]);
            $createdPatients[$w['scenario']] = $user;

            // Populate scenario-specific data only for new accounts
            if ($user->wasRecentlyCreated) {
                $this->seedScenario($user, $w, $midwife, $bhw);
            }
        }

        // ─── Cross-scenario fixtures ───────────────────────────────────
        // Forum content is owned by CommunityForumSeeder.

        // ─── Summary ──────────────────────────────────────────────────
        $this->command?->info('');
        $this->command?->info('╔══════════════════════════════════════════════════════════════╗');
        $this->command?->info('║        Digital Solution Test Accounts Created                ║');
        $this->command?->info('╠══════════════════════════════════════════════════════════════╣');
        $this->command?->info('║  Shared Password: ' . self::PASSWORD . str_repeat(' ', 22) . '║');
        $this->command?->info('╠══════════════════════════════════════════════════════════════╣');
        $this->command?->info('║  STAFF ACCOUNTS                                              ║');
        $this->command?->info('║  Midwife : test.midwife@reprocare.test                       ║');
        $this->command?->info('║  BHW     : test.bhw@reprocare.test                           ║');
        $this->command?->info('╠══════════════════════════════════════════════════════════════╣');
        $this->command?->info('║  WOMEN (PATIENT) ACCOUNTS                                    ║');

        foreach ($women as $w) {
            $label = str_pad($w['first_name'] . ' ' . $w['last_name'], 20);
            $email = str_pad($w['email'], 36);
            $this->command?->info("║  {$label} {$email} ║");
        }

        $this->command?->info('╚══════════════════════════════════════════════════════════════╝');
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Helpers
    // ═══════════════════════════════════════════════════════════════════

    private function ensureUser(array $attributes): User
    {
        $existing = User::withTrashed()->where('email', $attributes['email'])->first();
        if ($existing) {
            return $existing;
        }

        $user = User::create($attributes);
        if (($attributes['status'] ?? '') !== 'pending') {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $user;
    }

    private function seedScenario(User $user, array $config, User $midwife, User $bhw): void
    {
        $scenario = $config['scenario'];

        // ── Health record for most scenarios ──────────────────────────
        if ($scenario !== 'onboarding') {
            $this->seedHealthRecord($user, $bhw, $scenario);
        }

        // ── Scenario-specific data ────────────────────────────────────
        match ($scenario) {
            'early_pregnancy'       => $this->seedPregnancy($user, $config['weeks'], false, $midwife, $bhw),
            'high_risk_pregnancy'   => $this->seedPregnancy($user, $config['weeks'], true, $midwife, $bhw),
            'late_pregnancy'        => $this->seedPregnancy($user, $config['weeks'], false, $midwife, $bhw),
            'regular_cycle'         => $this->seedCycles($user, [28, 28, 29, 28, 28]),
            'irregular_cycle'       => $this->seedCycles($user, [22, 35, 26, 41, 30]),
            'forum_active'          => null, // Handled in cross-scenario
            'family_planning'       => $this->seedFamilyPlanning($user, $bhw),
            'fp_and_cycle'          => $this->seedFpAndCycle($user, $bhw),
            'analytics_data'        => $this->seedAnalyticsData($user, $bhw, $midwife),
            'onboarding'            => null, // Intentionally empty — new user experience
            default                 => null,
        };
    }

    // ── Pregnancy + Checkups ──────────────────────────────────────────

    private function seedPregnancy(User $user, int $weeks, bool $highRisk, User $midwife, User $bhw): void
    {
        $lmp = today()->subWeeks($weeks);
        $pregnancy = Pregnancy::firstOrCreate(
            ['user_id' => $user->id, 'ended_at' => null],
            [
                'lmp'                 => $lmp->toDateString(),
                'gravida'             => $highRisk ? 4 : 1,
                'para'                => 0,
                'risk_level'          => $highRisk ? 'High' : 'Low',
                'risk_assessment_mode'=> 'manual',
                'is_high_risk'        => $highRisk,
                'risk_notes'          => $highRisk ? '[TEST] Age > 35, history of gestational hypertension' : null,
                'notes'               => '[TEST] ' . ($highRisk ? 'High-risk' : 'Normal') . " pregnancy at {$weeks} weeks",
                'workflow_status'     => 'draft',
            ]
        );

        // Schedule upcoming checkups
        $checkupDates = [
            today()->addDays(7),
            today()->addDays(21),
            today()->addDays(35),
        ];
        foreach ($checkupDates as $i => $date) {
            Checkup::firstOrCreate(
                ['user_id' => $user->id, 'scheduled_date' => $date->toDateString(), 'purpose' => '[TEST] Prenatal visit ' . ($i + 1)],
                [
                    'midwife_id'    => $midwife->id,
                    'scheduled_by_id' => $bhw->id,
                    'scheduled_time' => '09:00',
                    'status'        => 'Scheduled',
                ]
            );
        }

        // One completed past checkup
        Checkup::firstOrCreate(
            ['user_id' => $user->id, 'purpose' => '[TEST] Initial prenatal checkup'],
            [
                'midwife_id'     => $midwife->id,
                'scheduled_by_id' => $bhw->id,
                'scheduled_date' => today()->subDays(14)->toDateString(),
                'actual_date'    => today()->subDays(14)->toDateString(),
                'scheduled_time' => '10:00',
                'status'         => 'Completed',
            ]
        );
    }

    // ── Menstrual Cycle Records ───────────────────────────────────────

    private function seedCycles(User $user, array $cycleLengths): void
    {
        $start = today()->subDays(array_sum($cycleLengths) + 5);
        foreach ($cycleLengths as $length) {
            $periodEnd = $start->copy()->addDays(rand(3, 5));
            Cycle::firstOrCreate(
                ['user_id' => $user->id, 'period_start_date' => $start->toDateString()],
                [
                    'period_end_date' => $periodEnd->toDateString(),
                    'pain_score'      => rand(1, 5),
                    'notes'           => '[TEST] Cycle logged for digital menstrual tracker testing',
                ]
            );
            $start->addDays($length);
        }
    }

    // ── Health Records ────────────────────────────────────────────────

    private function seedHealthRecord(User $user, User $bhw, string $scenario, ?string $notes = null): void
    {
        $isRisk = in_array($scenario, ['high_risk_pregnancy', 'analytics_data']);
        $record = new HealthRecord([
            'user_id'             => $user->id,
            'bp'                  => $isRisk ? '145/95' : '118/76',
            'weight'              => 50 + rand(0, 20),
            'height'              => 155,
            'heart_rate'          => $isRisk ? 100 : 76,
            'temperature'         => $isRisk ? 37.8 : 36.6,
            'risk_level'          => $isRisk ? 'High' : 'Low',
            'risk_assessment_mode'=> 'manual',
            'notes'               => $notes ?? ('[TEST] Health record for ' . $scenario),
            'recorded_by_id'      => $bhw->id,
            'workflow_status'     => 'accepted_by_midwife',
        ]);

        if (Schema::hasColumn('health_records', 'user_id_old__deprecated')) {
            $record->setAttribute('user_id_old__deprecated', $user->id);
            $record->setAttribute('recorded_by_id_old__deprecated', $bhw->id);
        }
        $record->save();
    }

    // ── Family Planning Records ───────────────────────────────────────

    private function seedFamilyPlanning(User $user, User $bhw): void
    {
        FamilyPlanningRecord::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'active'],
            [
                'recorded_by_id'           => $bhw->id,
                'preferred_method'         => 'pill',
                'method_given'             => 'pill',
                'pregnancy_intention'      => 'spacing',
                'counseling_date'          => today()->subMonths(2)->toDateString(),
                'start_date'              => today()->subMonths(2)->toDateString(),
                'next_follow_up'          => today()->addDays(14)->toDateString(),
                'notes'                    => '[TEST] Active oral contraceptive user — follow-up due in 2 weeks',
                'informed_choice_confirmed'=> true,
            ]
        );

        // A previous stopped method
        FamilyPlanningRecord::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'stopped'],
            [
                'recorded_by_id'           => $bhw->id,
                'preferred_method'         => 'injectable',
                'method_given'             => 'injectable',
                'pregnancy_intention'      => 'avoiding',
                'counseling_date'          => today()->subMonths(8)->toDateString(),
                'start_date'              => today()->subMonths(8)->toDateString(),
                'stop_date'               => today()->subMonths(3)->toDateString(),
                'side_effects'            => 'Weight gain, irregular spotting',
                'notes'                    => '[TEST] Switched from injectable to pill due to side effects',
                'informed_choice_confirmed'=> true,
            ]
        );
    }

    // ── Family Planning + Cycle combo ─────────────────────────────────

    private function seedFpAndCycle(User $user, User $bhw): void
    {
        $this->seedCycles($user, [30, 28, 31, 29]);

        FamilyPlanningRecord::firstOrCreate(
            ['user_id' => $user->id, 'preferred_method' => 'natural_method'],
            [
                'recorded_by_id'           => $bhw->id,
                'method_given'             => 'natural_method',
                'pregnancy_intention'      => 'spacing',
                'counseling_date'          => today()->subMonths(1)->toDateString(),
                'start_date'              => today()->subMonths(1)->toDateString(),
                'next_follow_up'          => today()->addDays(30)->toDateString(),
                'notes'                    => '[TEST] Using natural family planning (calendar method) — tracking cycles for fertility awareness',
                'informed_choice_confirmed'=> true,
                'status'                   => 'active',
            ]
        );
    }

    // ── Analytics data (multiple records over time) ───────────────────

    private function seedAnalyticsData(User $user, User $bhw, User $midwife): void
    {
        // Multiple health records over 6 months to populate dashboards
        $riskLevels = ['Low', 'Low', 'Medium', 'Low', 'High', 'Medium'];
        $bpValues   = ['118/76', '120/80', '132/88', '116/74', '150/96', '135/90'];

        for ($i = 0; $i < 6; $i++) {
            $createdDate = today()->subMonths(6 - $i);
            $record = new HealthRecord([
                'user_id'              => $user->id,
                'bp'                   => $bpValues[$i],
                'weight'               => 52 + $i,
                'height'               => 158,
                'heart_rate'           => 72 + ($i * 3),
                'temperature'          => $riskLevels[$i] === 'High' ? 37.9 : 36.5,
                'risk_level'           => $riskLevels[$i],
                'risk_assessment_mode' => 'manual',
                'notes'                => "[TEST] Monthly health check #{$i} for analytics dashboard",
                'recorded_by_id'       => $bhw->id,
                'workflow_status'      => 'accepted_by_midwife',
                'created_at'           => $createdDate,
                'updated_at'           => $createdDate,
            ]);

            if (Schema::hasColumn('health_records', 'user_id_old__deprecated')) {
                $record->setAttribute('user_id_old__deprecated', $user->id);
                $record->setAttribute('recorded_by_id_old__deprecated', $bhw->id);
            }
            $record->save();
        }

        // A past pregnancy (completed) for historical reports
        Pregnancy::firstOrCreate(
            ['user_id' => $user->id, 'notes' => '[TEST] Completed pregnancy for analytics'],
            [
                'lmp'                  => today()->subMonths(12)->toDateString(),
                'gravida'              => 2,
                'para'                 => 1,
                'risk_level'           => 'Low',
                'risk_assessment_mode' => 'manual',
                'is_high_risk'         => false,
                'workflow_status'      => 'completed',
                'ended_at'             => today()->subMonths(3)->toDateString(),
                'delivery_date'        => today()->subMonths(3)->toDateString(),
                'outcome'              => 'live_birth',
            ]
        );

        // Active pregnancy for mixed data
        Pregnancy::firstOrCreate(
            ['user_id' => $user->id, 'ended_at' => null],
            [
                'lmp'                  => today()->subWeeks(16)->toDateString(),
                'gravida'              => 3,
                'para'                 => 1,
                'risk_level'           => 'Medium',
                'risk_assessment_mode' => 'manual',
                'is_high_risk'         => false,
                'notes'                => '[TEST] Current pregnancy with extensive health record history',
                'workflow_status'      => 'draft',
            ]
        );

        // Schedule checkups at different statuses
        Checkup::firstOrCreate(
            ['user_id' => $user->id, 'purpose' => '[TEST] Completed monthly checkup'],
            [
                'midwife_id'     => $midwife->id,
                'scheduled_by_id'=> $bhw->id,
                'scheduled_date' => today()->subDays(30)->toDateString(),
                'actual_date'    => today()->subDays(30)->toDateString(),
                'scheduled_time' => '09:00',
                'status'         => 'Completed',
            ]
        );
        Checkup::firstOrCreate(
            ['user_id' => $user->id, 'purpose' => '[TEST] Missed checkup for analytics'],
            [
                'midwife_id'     => $midwife->id,
                'scheduled_by_id'=> $bhw->id,
                'scheduled_date' => today()->subDays(7)->toDateString(),
                'scheduled_time' => '10:00',
                'status'         => 'Missed',
            ]
        );
        Checkup::firstOrCreate(
            ['user_id' => $user->id, 'purpose' => '[TEST] Upcoming prenatal visit'],
            [
                'midwife_id'     => $midwife->id,
                'scheduled_by_id'=> $bhw->id,
                'scheduled_date' => today()->addDays(5)->toDateString(),
                'scheduled_time' => '14:00',
                'status'         => 'Scheduled',
            ]
        );
    }
}
