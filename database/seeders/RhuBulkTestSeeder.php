<?php

namespace Database\Seeders;

use App\Models\Checkup;
use App\Models\Cycle;
use App\Models\FamilyPlanningRecord;
use App\Models\HealthRecord;
use App\Models\Pregnancy;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * RhuBulkTestSeeder
 * ─────────────────────────────────────────────────────────────────────────────
 * Seeds 50 test patients in each of 5 RHUs (RHU 1 – RHU 5), plus 1 midwife
 * and 1 BHW per RHU.  Every digital-solution scenario is exercised so testers
 * can verify every system feature without manual data entry.
 *
 * Shared password for ALL accounts: TestReproCare2026!
 *
 * Run:  php artisan db:seed --class=RhuBulkTestSeeder
 *
 * Idempotent — re-running skips accounts whose e-mail already exists.
 */
class RhuBulkTestSeeder extends Seeder
{
    private const PASSWORD = 'TestReproCare2026!';

    // ── RHU definitions ───────────────────────────────────────────────────────
    // Barangay names MUST match the `barangays` table (used by AnalyticsScope)
    // so patients appear in RHU/CHO dashboards and directory counts.
    private const RHUS = [
        1 => [
            'label'     => 'RHU 1',
            'barangays' => ['Cacaritan', 'Calomboyan', 'Capataan', 'Lucban St', 'Mamarlao'],
        ],
        2 => [
            'label'     => 'RHU 2',
            'barangays' => ['Abanon', 'Anando', 'Bolosan', 'Guelew', 'Tebag'],
        ],
        3 => [
            'label'     => 'RHU 3',
            'barangays' => ['Bocboc', 'Balococ', 'Bani', 'Salinap', 'Tayambani'],
        ],
        4 => [
            'label'     => 'RHU 4',
            'barangays' => ['Cobol', 'Coliling', 'Ilang', 'Longos', 'Turac'],
        ],
        5 => [
            'label'     => 'RHU 5',
            'barangays' => ['Doyong', 'Cruz', 'Manzon', 'Parayao', 'Rizal Ave'],
        ],
    ];

    // ── Filipino name pools ───────────────────────────────────────────────────
    private const FIRST_NAMES = [
        'Ana','Belen','Carmen','Delia','Elena','Fe','Gloria','Helen','Irene','Julia',
        'Karen','Lorna','Maria','Nena','Ofelia','Perla','Rosa','Salud','Teresa','Ursula',
        'Vicky','Wena','Ximena','Yolanda','Zenaida','Alicia','Belinda','Cora','Divina','Edna',
        'Filomena','Grace','Herminia','Isidra','Josefa','Lilia','Minda','Norma','Olympia','Pilar',
        'Quirina','Rita','Sonia','Thelma','Urduja','Violeta','Wilma','Xenia','Ylang','Zita',
    ];

    private const LAST_NAMES = [
        'Aquino','Bautista','Cruz','De Vera','Espino','Flores','Garcia','Hernandez','Ignacio','Jimenez',
        'Katigbak','Lopez','Macaraeg','Navarro','Ortega','Pangilinan','Quiambao','Reyes','Santos','Tolentino',
        'Ureta','Valdez','Wenceslao','Ybañez','Zapanta','Andres','Buenaventura','Corpuz','Dizon','Enriquez',
        'Ferrer','Gutierrez','Hidalgo','Ilagan','Juan','Lacson','Mendoza','Natividad','Oliva','Palomo',
        'Quijano','Ramos','Salazar','Tan','Umali','Villanueva','Wycoco','Xavier','Yap','Zobel',
    ];

    // 10 scenario types — cycling through these gives 50 patients per RHU with
    // every major feature represented 5 times each.
    private const SCENARIOS = [
        'early_pregnancy',      // Pregnancy tracker — 1st trimester
        'mid_pregnancy',        // Pregnancy tracker — 2nd trimester
        'late_pregnancy',       // Pregnancy tracker — 3rd trimester, near due date
        'high_risk_pregnancy',  // High-risk flag, escalation alerts
        'regular_cycle',        // Menstrual cycle tracker — regular
        'irregular_cycle',      // Menstrual cycle tracker — irregular
        'family_planning',      // Family-planning module
        'fp_and_cycle',         // FP + cycle combo
        'analytics_data',       // Multiple health records for dashboard/analytics
        'onboarding',           // New user — pending approval flow
    ];

    // ─────────────────────────────────────────────────────────────────────────
    public function run(): void
    {
        $password = Hash::make(self::PASSWORD);
        $totalCreated = 0;

        foreach (self::RHUS as $rhuNum => $rhuConfig) {
            $rhuLabel   = $rhuConfig['label'];
            $barangays  = $rhuConfig['barangays'];
            $primaryBar = $barangays[0];

            $this->command?->info("── Seeding {$rhuLabel} ──────────────────────────────");

            // ── Staff: 1 midwife + 1 BHW per RHU ────────────────────────
            $midwifePurok = Purok::firstOrCreate(
                ['name' => 'Purok 1', 'barangay' => $primaryBar],
                ['description' => "{$rhuLabel} staff catchment"]
            );

            $midwife = $this->ensureUser([
                'first_name'        => 'Midwife',
                'middle_initial'    => null,
                'last_name'         => "RHU{$rhuNum}",
                'email'             => "midwife.rhu{$rhuNum}@reprocare.test",
                'password'          => $password,
                'role'              => 'midwife',
                'status'            => 'approved',
                'gender'            => 'female',
                'date_of_birth'     => '1985-06-15',
                'contact_number'    => '0917000' . str_pad($rhuNum * 10, 4, '0', STR_PAD_LEFT),
                'address'           => "{$rhuLabel}, {$primaryBar}, San Carlos City, Pangasinan",
                'barangay'          => $primaryBar,
                'purok_id'          => $midwifePurok->id,
                'assigned_barangay' => $primaryBar,
                'rhu_assignment'    => $rhuLabel,
            ]);

            $bhw = $this->ensureUser([
                'first_name'        => 'BHW',
                'middle_initial'    => null,
                'last_name'         => "RHU{$rhuNum}",
                'email'             => "bhw.rhu{$rhuNum}@reprocare.test",
                'password'          => $password,
                'role'              => 'bhw',
                'status'            => 'approved',
                'gender'            => 'female',
                'date_of_birth'     => '1990-03-10',
                'contact_number'    => '0917000' . str_pad($rhuNum * 10 + 1, 4, '0', STR_PAD_LEFT),
                'address'           => "Purok 1, {$primaryBar}, San Carlos City, Pangasinan",
                'barangay'          => $primaryBar,
                'purok_id'          => $midwifePurok->id,
                'assigned_barangay' => $primaryBar,
                'rhu_assignment'    => $rhuLabel,
            ]);

            // ── 50 patients ──────────────────────────────────────────────
            $createdPatients = [];
            for ($i = 0; $i < 50; $i++) {
                $scenario    = self::SCENARIOS[$i % 10];
                $firstName   = self::FIRST_NAMES[$i % count(self::FIRST_NAMES)];
                $lastName    = self::LAST_NAMES[$i % count(self::LAST_NAMES)];
                $barangay    = $barangays[$i % count($barangays)];
                $purokNo     = ($i % 4) + 1;
                $houseNo     = $i + 1;

                // Unique email: rhu{N}.p{00}.firstname.lastname@reprocare.test
                $slug  = preg_replace('/[^a-z0-9]/', '', strtolower($firstName . $lastName));
                $email = "rhu{$rhuNum}.p" . str_pad($i + 1, 2, '0', STR_PAD_LEFT) . ".{$slug}@reprocare.test";

                // Random-ish but deterministic DOB
                $year  = 1988 + ($i % 17);          // 1988–2004
                $month = str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT);
                $day   = str_pad(($i % 28) + 1, 2, '0', STR_PAD_LEFT);
                $dob   = "{$year}-{$month}-{$day}";

                // Deterministic contact number
                $contactBase = 9170100000 + ($rhuNum * 1000) + $i;
                $contact     = '0' . $contactBase;

                $purok = Purok::firstOrCreate(
                    ['name' => "Purok {$purokNo}", 'barangay' => $barangay],
                    ['description' => "{$rhuLabel} {$barangay} catchment"]
                );

                $user = $this->ensureUser([
                    'first_name'     => $firstName,
                    'middle_initial' => null,
                    'last_name'      => $lastName,
                    'email'          => $email,
                    'password'       => $password,
                    'role'           => 'user',
                    'status'         => $scenario === 'onboarding' ? 'pending' : 'approved',
                    'gender'         => 'female',
                    'date_of_birth'  => $dob,
                    'contact_number' => $contact,
                    'address'        => "House {$houseNo}, Purok {$purokNo}, {$barangay}, San Carlos City, Pangasinan",
                    'barangay'       => $barangay,
                    'purok_id'       => $purok->id,
                    'rhu_assignment' => $rhuLabel,
                    'partner_name'   => $scenario !== 'onboarding' ? "Partner of {$firstName}" : null,
                ]);

                if ($user->wasRecentlyCreated) {
                    $totalCreated++;
                    $this->seedScenario($user, $scenario, $i, $midwife, $bhw);
                }

                // Collect a representative from each scenario for cross-linking
                if (!isset($createdPatients[$scenario])) {
                    $createdPatients[$scenario] = $user;
                }
            }

            $this->command?->info("   ✓ {$rhuLabel}: staff + 50 patients done.");
        }

        $this->printSummary($totalCreated);
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Scenario dispatcher
    // ═════════════════════════════════════════════════════════════════════════

    private function seedScenario(User $user, string $scenario, int $idx, User $midwife, User $bhw): void
    {
        // Health record for almost everyone (skipped for pure onboarding)
        if ($scenario !== 'onboarding') {
            $this->seedHealthRecord($user, $bhw, $scenario);
        }

        match ($scenario) {
            'early_pregnancy'    => $this->seedPregnancy($user, 8 + ($idx % 4),  false, $midwife, $bhw),
            'mid_pregnancy'      => $this->seedPregnancy($user, 18 + ($idx % 6), false, $midwife, $bhw),
            'late_pregnancy'     => $this->seedPregnancy($user, 34 + ($idx % 5), false, $midwife, $bhw),
            'high_risk_pregnancy'=> $this->seedPregnancy($user, 20 + ($idx % 8), true,  $midwife, $bhw),
            'regular_cycle'      => $this->seedCycles($user, [28, 28, 29, 28, 28]),
            'irregular_cycle'    => $this->seedCycles($user, [22, 35, 26, 41, 30]),
            'family_planning'    => $this->seedFamilyPlanning($user, $bhw),
            'fp_and_cycle'       => $this->seedFpAndCycle($user, $bhw),
            'analytics_data'     => $this->seedAnalyticsData($user, $bhw, $midwife),
            'onboarding'         => null,  // Intentionally empty — tests approval flow
            default              => null,
        };
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Scenario helpers
    // ═════════════════════════════════════════════════════════════════════════

    // ── Pregnancy + scheduled checkups ───────────────────────────────────────
    private function seedPregnancy(User $user, int $weeks, bool $highRisk, User $midwife, User $bhw): void
    {
        $lmp = today()->subWeeks($weeks);

        $pregnancy = Pregnancy::firstOrCreate(
            ['user_id' => $user->id, 'ended_at' => null],
            [
                'lmp'                  => $lmp->toDateString(),
                'gravida'              => $highRisk ? 4 : 1,
                'para'                 => 0,
                'risk_level'           => $highRisk ? 'High' : ($weeks > 28 ? 'Low' : 'Low'),
                'risk_assessment_mode' => 'manual',
                'is_high_risk'         => $highRisk,
                'risk_notes'           => $highRisk
                    ? '[TEST] Age >35, gestational hypertension history'
                    : null,
                'notes'                => '[TEST] ' . ($highRisk ? 'High-risk' : 'Normal')
                    . " pregnancy at {$weeks} weeks",
                'workflow_status'      => 'draft',
            ]
        );

        // Three upcoming checkups
        foreach ([7, 21, 35] as $daysAhead => $days) {
            Checkup::firstOrCreate(
                [
                    'user_id'        => $user->id,
                    'scheduled_date' => today()->addDays($days)->toDateString(),
                    'purpose'        => '[TEST] Prenatal visit ' . ($daysAhead + 1),
                ],
                [
                    'midwife_id'      => $midwife->id,
                    'scheduled_by_id' => $bhw->id,
                    'scheduled_time'  => '09:00',
                    'status'          => 'Scheduled',
                ]
            );
        }

        // One completed past checkup
        Checkup::firstOrCreate(
            ['user_id' => $user->id, 'purpose' => '[TEST] Initial prenatal checkup'],
            [
                'midwife_id'      => $midwife->id,
                'scheduled_by_id' => $bhw->id,
                'scheduled_date'  => today()->subDays(14)->toDateString(),
                'actual_date'     => today()->subDays(14)->toDateString(),
                'scheduled_time'  => '10:00',
                'status'          => 'Completed',
            ]
        );
    }

    // ── Menstrual cycle records ───────────────────────────────────────────────
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
                    'notes'           => '[TEST] Cycle logged for menstrual tracker testing',
                ]
            );
            $start->addDays($length);
        }
    }

    // ── Single health record ──────────────────────────────────────────────────
    private function seedHealthRecord(User $user, User $bhw, string $scenario): void
    {
        $isRisk = in_array($scenario, ['high_risk_pregnancy', 'analytics_data']);

        $record = new HealthRecord([
            'user_id'              => $user->id,
            'bp'                   => $isRisk ? '145/95' : '118/76',
            'weight'               => 50 + rand(0, 20),
            'height'               => 155,
            'heart_rate'           => $isRisk ? 100 : 76,
            'temperature'          => $isRisk ? 37.8 : 36.6,
            'risk_level'           => $isRisk ? 'High' : 'Low',
            'risk_assessment_mode' => 'manual',
            'notes'                => "[TEST] Health record for {$scenario}",
            'recorded_by_id'       => $bhw->id,
            'workflow_status'      => 'accepted_by_midwife',
        ]);

        if (Schema::hasColumn('health_records', 'user_id_old__deprecated')) {
            $record->setAttribute('user_id_old__deprecated', $user->id);
            $record->setAttribute('recorded_by_id_old__deprecated', $bhw->id);
        }
        $record->save();
    }

    // ── Family planning records ───────────────────────────────────────────────
    private function seedFamilyPlanning(User $user, User $bhw): void
    {
        FamilyPlanningRecord::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'active'],
            [
                'recorded_by_id'            => $bhw->id,
                'preferred_method'          => 'pill',
                'method_given'              => 'pill',
                'pregnancy_intention'       => 'spacing',
                'counseling_date'           => today()->subMonths(2)->toDateString(),
                'start_date'                => today()->subMonths(2)->toDateString(),
                'next_follow_up'            => today()->addDays(14)->toDateString(),
                'notes'                     => '[TEST] Active oral contraceptive — follow-up in 2 weeks',
                'informed_choice_confirmed' => true,
            ]
        );

        // Previous stopped method
        FamilyPlanningRecord::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'stopped'],
            [
                'recorded_by_id'            => $bhw->id,
                'preferred_method'          => 'injectable',
                'method_given'              => 'injectable',
                'pregnancy_intention'       => 'avoiding',
                'counseling_date'           => today()->subMonths(8)->toDateString(),
                'start_date'                => today()->subMonths(8)->toDateString(),
                'stop_date'                 => today()->subMonths(3)->toDateString(),
                'side_effects'              => 'Weight gain, irregular spotting',
                'notes'                     => '[TEST] Switched from injectable to pill due to side effects',
                'informed_choice_confirmed' => true,
            ]
        );
    }

    // ── Family planning + cycle combo ─────────────────────────────────────────
    private function seedFpAndCycle(User $user, User $bhw): void
    {
        $this->seedCycles($user, [30, 28, 31, 29]);

        FamilyPlanningRecord::firstOrCreate(
            ['user_id' => $user->id, 'preferred_method' => 'natural_method'],
            [
                'recorded_by_id'            => $bhw->id,
                'method_given'              => 'natural_method',
                'pregnancy_intention'       => 'spacing',
                'counseling_date'           => today()->subMonths(1)->toDateString(),
                'start_date'                => today()->subMonths(1)->toDateString(),
                'next_follow_up'            => today()->addDays(30)->toDateString(),
                'notes'                     => '[TEST] Natural family planning (calendar method) — cycle tracking active',
                'informed_choice_confirmed' => true,
                'status'                    => 'active',
            ]
        );
    }

    // ── Analytics: multiple health records + pregnancies + checkups ───────────
    private function seedAnalyticsData(User $user, User $bhw, User $midwife): void
    {
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

        // Completed past pregnancy
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

        // Active pregnancy in 2nd trimester
        Pregnancy::firstOrCreate(
            ['user_id' => $user->id, 'ended_at' => null],
            [
                'lmp'                  => today()->subWeeks(16)->toDateString(),
                'gravida'              => 3,
                'para'                 => 1,
                'risk_level'           => 'Medium',
                'risk_assessment_mode' => 'manual',
                'is_high_risk'         => false,
                'notes'                => '[TEST] Current pregnancy with extensive health history',
                'workflow_status'      => 'draft',
            ]
        );

        // Checkups at different statuses (completed / missed / upcoming)
        foreach ([
            ['[TEST] Completed monthly checkup', today()->subDays(30), 'Completed', true],
            ['[TEST] Missed checkup for analytics', today()->subDays(7),  'Missed',    false],
            ['[TEST] Upcoming prenatal visit',     today()->addDays(5),   'Scheduled', false],
        ] as [$purpose, $date, $status, $hasActual]) {
            Checkup::firstOrCreate(
                ['user_id' => $user->id, 'purpose' => $purpose],
                [
                    'midwife_id'      => $midwife->id,
                    'scheduled_by_id' => $bhw->id,
                    'scheduled_date'  => $date->toDateString(),
                    'actual_date'     => $hasActual ? $date->toDateString() : null,
                    'scheduled_time'  => '09:00',
                    'status'          => $status,
                ]
            );
        }
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  Utility helpers
    // ═════════════════════════════════════════════════════════════════════════

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

    private function printSummary(int $newCount): void
    {
        $this->command?->info('');
        $this->command?->info('╔══════════════════════════════════════════════════════════════════╗');
        $this->command?->info('║             RhuBulkTestSeeder — Complete                         ║');
        $this->command?->info('╠══════════════════════════════════════════════════════════════════╣');
        $this->command?->info('║  Shared password : TestReproCare2026!                            ║');
        $this->command?->info('╠══════════════════════════════════════════════════════════════════╣');
        $this->command?->info('║  RHU  │ Midwife email                  │ BHW email               ║');
        $this->command?->info('╠══════════════════════════════════════════════════════════════════╣');
        foreach (array_keys(self::RHUS) as $n) {
            $mid = str_pad("midwife.rhu{$n}@reprocare.test", 30);
            $bhw = "bhw.rhu{$n}@reprocare.test";
            $this->command?->info("║  RHU {$n} │ {$mid} │ {$bhw}");
        }
        $this->command?->info('╠══════════════════════════════════════════════════════════════════╣');
        $this->command?->info("║  Patients per RHU : 50  │  Total RHUs : 5  │  New rows: {$newCount}");
        $this->command?->info('║  Patient emails pattern:                                         ║');
        $this->command?->info('║    rhu{N}.p{01-50}.{name}@reprocare.test                         ║');
        $this->command?->info('╠══════════════════════════════════════════════════════════════════╣');
        $this->command?->info('║  SCENARIOS covered (cycled across 50 patients × 5 RHUs)          ║');
        $this->command?->info('║  1  early_pregnancy      – 1st trimester + checkups              ║');
        $this->command?->info('║  2  mid_pregnancy        – 2nd trimester + checkups              ║');
        $this->command?->info('║  3  late_pregnancy       – 3rd trimester + checkups              ║');
        $this->command?->info('║  4  high_risk_pregnancy  – high-risk flag + escalation data      ║');
        $this->command?->info('║  5  regular_cycle        – regular menstrual cycle tracker       ║');
        $this->command?->info('║  6  irregular_cycle      – irregular menstrual cycle tracker     ║');
        $this->command?->info('║  7  family_planning      – active + stopped FP methods           ║');
        $this->command?->info('║  8  fp_and_cycle         – FP combined with cycle tracking       ║');
        $this->command?->info('║  9  analytics_data       – 6-month health records + pregnancies  ║');
        $this->command?->info('║  10 onboarding           – pending approval / new-user flow      ║');
        $this->command?->info('╚══════════════════════════════════════════════════════════════════╝');
    }
}

