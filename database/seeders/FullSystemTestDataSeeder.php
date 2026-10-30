<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\BhwAssignment;
use App\Models\BhwMonthlyReport;
use App\Models\CheckupReferral;
use App\Models\ChildAssessment;
use App\Models\ChildCareTargetClient;
use App\Models\ChildCheckup;
use App\Models\ChildFeedingMilestone;
use App\Models\ChildManagementOutcome;
use App\Models\ChildNutritionTracking;
use App\Models\ChildRecord;
use App\Models\ChildSupplement;
use App\Models\ChildVaccination;
use App\Models\ChoHandover;
use App\Models\DuplicateReview;
use App\Models\EmergencyAlert;
use App\Models\FamilyPlanningRecord;
use App\Models\ForumComment;
use App\Models\ForumLike;
use App\Models\ForumPost;
use App\Models\MaternalAssessment;
use App\Models\MaternalCareTargetClient;
use App\Models\MaternalDeath;
use App\Models\MaternalMorbidity;
use App\Models\MaternalPostpartumCare;
use App\Models\MaternalPrenatalVisit;
use App\Models\MaternalScreening;
use App\Models\MaternalSupplement;
use App\Models\MaternalVaccination;
use App\Models\Message;
use App\Models\Notification;
use App\Models\PatientCondition;
use App\Models\PatientTransfer;
use App\Models\Pregnancy;
use App\Models\PreventiveIntervention;
use App\Models\Purok;
use App\Models\SmsLog;
use App\Models\StaffTransition;
use App\Models\SupplyRequest;
use App\Models\SyncLog;
use App\Models\Task;
use App\Models\User;
use App\Models\WalkInPatient;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Opt-in, fictional fixtures that exercise every module in the system.
 *
 * Runs TestScenarioAccountsSeeder first (100 fictional accounts), then fills
 * the modules that seeder does not cover: walk-ins, family planning, child
 * nutrition, maternal care tracking, mortality/morbidity audits, duplicate
 * reviews, transfers, emergency alerts, staff transitions, activity/SMS/sync
 * logs and extra engagement content.
 *
 * Never call from DatabaseSeeder: that seeder also runs on production boots.
 * Re-running this seeder leaves existing rows (including edits made during
 * testing) alone.
 */
class FullSystemTestDataSeeder extends Seeder
{
    private const PASSWORD = 'TestAccount123!';

    private const BARANGAYS = ['Cacaritan', 'Palaming', 'Abanon'];

    public function run(): void
    {
        $this->call(TestScenarioAccountsSeeder::class);

        $ready = false;

        DB::transaction(function () use (&$ready) {
            $ctx = $this->context();
            if ($ctx === null) {
                return;
            }

            $this->seedWalkIns($ctx);
            $this->seedFamilyPlanning($ctx);
            $this->seedChildNutrition($ctx);
            $this->seedMaternalCare($ctx);
            $this->seedMaternalOutcomes($ctx);
            $this->seedPatientConditions($ctx);
            $this->seedOrganization($ctx);
            $this->seedTransfersAndReviews($ctx);
            $this->seedEmergencyAlerts($ctx);
            $this->seedPreventiveInterventions($ctx);
            $this->seedEngagement($ctx);
            $this->seedOperations($ctx);
            $ready = true;
        });

        if (!$ready) {
            $this->command?->warn('Full-system fixtures skipped: run TestScenarioAccountsSeeder first.');
            return;
        }

        $this->command?->info('Full-system fixtures are ready.');
        $this->command?->line(sprintf(
            'Walk-ins %d | family planning %d | child target clients %d | maternal target clients %d',
            WalkInPatient::count(),
            FamilyPlanningRecord::count(),
            ChildCareTargetClient::count(),
            MaternalCareTargetClient::count()
        ));
        $this->command?->line(sprintf(
            'Deaths %d | morbidities %d | transfers %d | duplicate reviews %d | alerts %d',
            MaternalDeath::count(),
            MaternalMorbidity::count(),
            PatientTransfer::count(),
            DuplicateReview::count(),
            EmergencyAlert::count()
        ));
        $this->command?->line(sprintf(
            'Tasks %d | supply requests %d | reports %d | notifications %d',
            Task::count(),
            SupplyRequest::count(),
            BhwMonthlyReport::count(),
            Notification::count()
        ));
        $this->command?->line('Staff logins (password ' . self::PASSWORD . '):');
        $this->command?->line('  cho        reprocare-test-001@example.test');
        $this->command?->line('  rhu        reprocare-test-002@example.test, reprocare-test-003@example.test');
        $this->command?->line('  midwife    reprocare-test-004..006@example.test');
        $this->command?->line('  president  reprocare-test-007..009@example.test');
        $this->command?->line('  bhw        reprocare-test-010..012@example.test');
        $this->command?->line('Patients: reprocare-test-013..100@example.test (scenario fixtures)');
        $this->command?->line('Walk-ins: reprocare-walkin-001..006@example.test (converted portal accounts)');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function context(): ?array
    {
        $staff = User::whereIn('role', ['cho', 'rhu', 'midwife', 'bhw_president', 'bhw'])
            ->where('email', 'like', 'reprocare-test-%')
            ->get();

        $patients = User::where('role', 'user')
            ->where('email', 'like', 'reprocare-test-%')
            ->where('status', 'approved')
            ->orderBy('email')
            ->get();

        if ($staff->isEmpty() || $patients->count() < 12) {
            return null;
        }

        return [
            'staff' => $staff,
            'patients' => $patients,
            'patientIds' => $patients->pluck('id')->all(),
            'puroks' => Purok::whereIn('barangay', self::BARANGAYS)->get(),
            'bhws' => $staff->where('role', 'bhw')->values(),
            'presidents' => $staff->where('role', 'bhw_president')->values(),
            'midwives' => $staff->where('role', 'midwife')->values(),
            'rhus' => $staff->where('role', 'rhu')->values(),
            'cho' => $staff->where('role', 'cho')->first(),
        ];
    }

    private function staffFor(array $ctx, string $role, ?string $barangay = null): ?User
    {
        $pool = $ctx['staff']->where('role', $role);

        if ($barangay !== null) {
            return $pool->firstWhere('assigned_barangay', $barangay) ?? $pool->first();
        }

        return $pool->first();
    }

    private function purokFor(array $ctx, string $barangay): ?Purok
    {
        return $ctx['puroks']->firstWhere('barangay', $barangay) ?? $ctx['puroks']->first();
    }

    /**
     * Date casts store a time component, so a plain where() never matches a
     * Y-m-d value. Compare with whereDate to keep repeat runs idempotent.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    private function findOrCreateOnDate(string $modelClass, array $attributes, string $column, string $date, array $values)
    {
        $model = $modelClass::where($attributes)->whereDate($column, $date)->first();
        if ($model) {
            return $model;
        }

        return $modelClass::create($attributes + [$column => $date] + $values);
    }

    private function seedWalkIns(array $ctx): void
    {
        $firstNames = [
            'Nora', 'Lito', 'Perla', 'Rey', 'Sonia', 'Ben', 'Cora', 'Dado',
            'Ester', 'Fidel', 'Gloria', 'Hector', 'Ivy', 'Jomar', 'Karla', 'Lando',
            'Mila', 'Nestor', 'Olive', 'Pablo',
        ];
        $lastNames = ['Aguilar', 'Bueno', 'Cruz', 'Dizon', 'Estrada', 'Flores', 'Gonzales', 'Hidalgo'];
        $reasons = [
            'Prenatal check-up', 'Family planning counseling', 'Fever and body ache',
            'Blood pressure monitoring', 'Immunization inquiry', 'Wound dressing',
            'Antenatal referral follow-up', 'General consultation',
        ];

        foreach (range(0, 35) as $i) {
            $barangay = self::BARANGAYS[$i % 3];
            $purok = $this->purokFor($ctx, $barangay);
            $bhw = $this->staffFor($ctx, 'bhw', $barangay);
            $first = $firstNames[$i % count($firstNames)];
            $last = $lastNames[intdiv($i, count($firstNames)) % count($lastNames)];
            $birth = today()->subYears(18 + $i % 22)->subDays(($i * 11) % 300);

            $walkIn = $this->findOrCreateOnDate(
                WalkInPatient::class,
                [
                    'first_name' => $first,
                    'middle_initial' => chr(65 + $i % 26),
                    'last_name' => $last,
                ],
                'date_of_birth',
                $birth->toDateString(),
                [
                    'recorded_by_id' => $bhw?->id,
                    'address' => "Sitio {$i}, Purok 1, {$barangay}, San Carlos City",
                    'barangay' => $barangay,
                    'purok_id' => $purok?->id,
                    'contact_number' => sprintf('09%09d', 600000000 + $i),
                    'reason_for_visit' => $reasons[$i % count($reasons)],
                    'notes' => '[TEST] Fictional walk-in record',
                ]
            );

            if ($i % 6 === 0 && $walkIn->converted_to_user_id === null) {
                $email = sprintf('reprocare-walkin-%03d@example.test', $i + 1);
                $user = User::withTrashed()->where('email', $email)->first();

                if (!$user) {
                    $user = User::create([
                        'first_name' => $first,
                        'middle_initial' => chr(65 + $i % 26),
                        'last_name' => $last,
                        'email' => $email,
                        'password' => Hash::make(self::PASSWORD),
                        'role' => 'user',
                        'status' => 'approved',
                        'is_profile_complete' => true,
                        'date_of_birth' => $birth,
                        'gender' => 'female',
                        'contact_number' => $walkIn->contact_number,
                        'address' => $walkIn->address,
                        'barangay' => $barangay,
                        'purok_id' => $purok?->id,
                        'rhu_assignment' => $i % 3 === 2 ? 'RHU 2' : 'RHU 1',
                    ]);
                    $user->forceFill(['email_verified_at' => now()])->save();
                }

                $walkIn->update([
                    'user_id' => $user->id,
                    'converted_to_user_id' => $user->id,
                    'converted_at' => now(),
                    'has_portal_access' => true,
                ]);
            } elseif ($i % 3 === 1 && $walkIn->user_id === null) {
                $walkIn->update([
                    'user_id' => $ctx['patients'][$i % $ctx['patients']->count()]->id,
                    'has_portal_access' => true,
                ]);
            }
        }
    }

    private function seedFamilyPlanning(array $ctx): void
    {
        $methods = ['pills', 'injectable', 'implant', 'iud', 'condom', 'natural', 'lactational_amenorrhea'];
        $statuses = ['requested', 'active', 'stopped', 'referred'];
        $intentions = ['prevent', 'delay', 'space', 'limit'];

        foreach ($ctx['patients'] as $i => $patient) {
            if ($i % 3 !== 0) {
                continue;
            }

            $method = $methods[$i % count($methods)];
            $status = $statuses[intdiv($i, 3) % 4];
            $midwife = $this->staffFor($ctx, 'midwife', $patient->barangay);
            $start = today()->subDays(40 + ($i * 7) % 120);

            $this->findOrCreateOnDate(
                FamilyPlanningRecord::class,
                ['user_id' => $patient->id],
                'counseling_date',
                $start->copy()->subDays(5)->toDateString(),
                [
                    'recorded_by_id' => $midwife?->id,
                    'preferred_method' => $method,
                    'method_given' => in_array($status, ['active', 'stopped'], true) ? $method : null,
                    'pregnancy_intention' => $intentions[$i % count($intentions)],
                    'start_date' => $start->toDateString(),
                    'stop_date' => $status === 'stopped' ? $start->copy()->addDays(20 + $i % 10)->toDateString() : null,
                    'next_follow_up' => in_array($status, ['requested', 'active'], true)
                        ? today()->addDays(20 + $i % 15)->toDateString()
                        : null,
                    'side_effects' => $status === 'stopped' ? '[TEST] Mild headache reported' : null,
                    'notes' => '[TEST] Family planning fixture',
                    'informed_choice_confirmed' => true,
                    'status' => $status,
                ]
            );

            $pregnancy = Pregnancy::where('user_id', $patient->id)
                ->whereNotNull('ended_at')
                ->latest('id')
                ->first();

            if ($pregnancy) {
                FamilyPlanningRecord::firstOrCreate(
                    [
                        'user_id' => $patient->id,
                        'pregnancy_id' => $pregnancy->id,
                        'is_postpartum_plan' => true,
                    ],
                    [
                        'recorded_by_id' => $midwife?->id,
                        'preferred_method' => $methods[($i + 2) % count($methods)],
                        'pregnancy_intention' => 'space',
                        'counseling_date' => today()->subDays(5 + $i % 20)->toDateString(),
                        'next_follow_up' => today()->addDays(35)->toDateString(),
                        'notes' => '[TEST] Postpartum family-planning plan',
                        'informed_choice_confirmed' => true,
                        'status' => 'active',
                        'postpartum_start_timing' => ['immediately_after_delivery', 'before_discharge', 'six_week_visit', 'undecided'][$i % 4],
                    ]
                );
            }
        }
    }

    private function seedChildNutrition(array $ctx): void
    {
        $children = ChildRecord::whereIn('mother_id', $ctx['patientIds'])->orderBy('id')->get();
        $vaccines = [
            ['bcg', 1, 0], ['hepa_b_bd', 1, 0],
            ['dpt_hepb_hib', 1, 6], ['dpt_hepb_hib', 2, 10], ['dpt_hepb_hib', 3, 14],
            ['opv', 1, 6], ['opv', 2, 10], ['opv', 3, 14],
            ['pcv', 1, 6], ['pcv', 2, 10], ['pcv', 3, 14],
            ['ipv', 1, 14], ['mmr', 1, 38], ['mmr', 2, 52],
        ];
        $statuses = ['S', 'N', 'W-MAM', 'O', 'W-SAM'];

        foreach ($children as $i => $child) {
            $birth = Carbon::parse($child->date_of_birth);
            $mother = $ctx['patients']->firstWhere('id', $child->mother_id);

            $client = ChildCareTargetClient::firstOrCreate(
                ['child_id' => $child->id],
                [
                    'date_of_registration' => $birth->copy()->addDay()->toDateString(),
                    'family_serial_number' => 'TEST-C-' . $child->id,
                    'newborn_status' => 'live_birth',
                    'cpab_tt2_tt4' => $i % 2 === 0,
                    'cpab_tt3_tt5' => $i % 3 === 0,
                ]
            );

            foreach ($vaccines as $v => [$type, $dose, $days]) {
                $given = ($i + $v) % 4 !== 0;
                ChildVaccination::firstOrCreate(
                    [
                        'child_care_target_client_id' => $client->id,
                        'vaccine_type' => $type,
                        'dose_number' => $dose,
                    ],
                    [
                        'vaccination_date' => $given
                            ? $birth->copy()->addDays($days)->toDateString()
                            : null,
                    ]
                );
            }

            $assessments = [
                '1_3_months' => ['1-3', 24 + $i, 4.2 + ($i % 5) / 10],
                '6_11_months' => ['6-11', 30 + $i, 7.1 + ($i % 6) / 10],
                '12_months' => ['12', 34 + $i, 8.6 + ($i % 7) / 10],
            ];
            foreach ($assessments as $group => [$ageMonths, $length, $weight]) {
                $measured = $birth->copy()->addMonths($group === '1_3_months' ? 2 : ($group === '6_11_months' ? 8 : 12));

                ChildAssessment::firstOrCreate(
                    [
                        'child_care_target_client_id' => $client->id,
                        'age_group' => $group,
                    ],
                    [
                        'age_months' => $ageMonths,
                        'length_cm' => $length,
                        'length_date' => $measured->toDateString(),
                        'weight_kg' => $weight,
                        'weight_date' => $measured->toDateString(),
                        'status' => $statuses[($i + strlen($group)) % count($statuses)],
                    ]
                );
            }

            $supplements = [['vitamin_a', 6], ['vitamin_a', 12], ['mnp', 6], ['mnp', 7], ['mnp', 8]];
            if ((float) $child->birth_weight < 2.5) {
                $supplements[] = ['low_birth_weight_iron', 1];
                $supplements[] = ['low_birth_weight_iron', 2];
                $supplements[] = ['low_birth_weight_iron', 3];
            }

            foreach ($supplements as $s => [$type, $month]) {
                ChildSupplement::firstOrCreate(
                    [
                        'child_care_target_client_id' => $client->id,
                        'supplement_type' => $type,
                        'month_number' => $month,
                    ],
                    [
                        'given_date' => $birth->copy()->addMonths($month)->toDateString(),
                        'quantity' => $type === 'mnp' ? 30 : 1,
                    ]
                );
            }

            foreach (['1_5', '2_5', '3_5', '4_5', '5_9'] as $m => $range) {
                ChildNutritionTracking::firstOrCreate(
                    [
                        'child_care_target_client_id' => $client->id,
                        'month_range' => $range,
                    ],
                    ['exclusive_breastfeeding' => $m < 3]
                );
            }

            ChildFeedingMilestone::firstOrCreate(
                ['child_care_target_client_id' => $client->id],
                [
                    'exclusive_breastfed_up_to_6_months' => $i % 2 === 0,
                    'complementary_feeding_introduced' => true,
                    'breastfeeding_initiated_date' => $birth->toDateString(),
                ]
            );

            if ($i % 3 === 0) {
                $program = $i % 2 === 0 ? 'man_sfp' : 'sam_otc';
                ChildManagementOutcome::firstOrCreate(
                    [
                        'child_care_target_client_id' => $client->id,
                        'program_type' => $program,
                    ],
                    [
                        'admitted' => true,
                        'cured' => $i % 4 === 0,
                        'defaulted' => false,
                        'died' => false,
                    ]
                );
            }

            foreach ([2, 6, 12] as $months) {
                $this->findOrCreateOnDate(
                    ChildCheckup::class,
                    ['child_id' => $child->id],
                    'checkup_date',
                    $birth->copy()->addMonths($months)->toDateString(),
                    [
                        'weight' => round(4.2 + $months * 0.55, 1),
                        'height' => 55 + $months * 2,
                        'head_circumference' => 38 + $months * 0.5,
                        'developmental_milestones' => '[TEST] Age-appropriate milestones observed',
                        'notes' => '[TEST] Growth monitoring visit',
                        'conducted_by_id' => $this->staffFor($ctx, 'midwife', $mother?->barangay)?->id,
                    ]
                );
            }
        }
    }

    private function seedMaternalCare(array $ctx): void
    {
        $clients = MaternalCareTargetClient::whereIn('user_id', $ctx['patientIds'])->orderBy('id')->get();
        $pregnancies = Pregnancy::whereIn('id', $clients->pluck('pregnancy_id')->filter()->all())
            ->get()
            ->keyBy('id');

        foreach ($clients as $i => $client) {
            $patient = $ctx['patients']->firstWhere('id', $client->user_id);
            $pregnancy = $pregnancies->get($client->pregnancy_id);
            $base = today()->subDays(70);
            $midwife = $this->staffFor($ctx, 'midwife', $patient?->barangay);

            foreach ([1 => 'first', 2 => 'second', 3 => 'third'] as $visit => $trimester) {
                MaternalPrenatalVisit::firstOrCreate(
                    [
                        'maternal_care_target_client_id' => $client->id,
                        'visit_number' => $visit,
                    ],
                    [
                        'trimester' => $trimester,
                        'visit_date' => $base->copy()->addDays($visit * 21)->toDateString(),
                    ]
                );
            }

            $supplements = [
                ['iron_folic', 1, 30], ['iron_folic', 2, 30], ['iron_folic', 3, 30],
                ['calcium', 2, 60], ['calcium', 3, 60], ['iodine', 1, 1],
            ];
            foreach ($supplements as $s => [$type, $visit, $tablets]) {
                MaternalSupplement::firstOrCreate(
                    [
                        'maternal_care_target_client_id' => $client->id,
                        'supplement_type' => $type,
                        'visit_number' => $visit,
                    ],
                    [
                        'distribution_date' => $base->copy()->addDays($visit * 21 + $s)->toDateString(),
                        'tablets_given' => $tablets,
                    ]
                );
            }

            $screenings = [
                ['syphilis', 'negative'],
                ['hepatitis_b', 'negative'],
                ['hiv', 'negative'],
                ['gestational_diabetes', 'negative'],
                ['cbc', $i % 5 === 0 ? 'with_anemia' : 'without_anemia'],
            ];
            foreach ($screenings as $s => [$type, $result]) {
                MaternalScreening::firstOrCreate(
                    [
                        'maternal_care_target_client_id' => $client->id,
                        'screening_type' => $type,
                    ],
                    [
                        'screening_date' => $base->copy()->addDays(14 + $s * 3)->toDateString(),
                        'result' => $result,
                        'given_iron' => $type === 'cbc' && $result === 'with_anemia',
                    ]
                );
            }

            foreach ([1, 2, 3] as $dose) {
                MaternalVaccination::firstOrCreate(
                    [
                        'maternal_care_target_client_id' => $client->id,
                        'vaccine_type' => 'td',
                        'dose_number' => $dose,
                    ],
                    ['vaccination_date' => $base->copy()->addDays($dose * 28)->toDateString()]
                );
            }

            MaternalAssessment::firstOrCreate(
                [
                    'maternal_care_target_client_id' => $client->id,
                    'assessment_type' => 'nutritional',
                ],
                [
                    'age_months' => null,
                    'length_cm' => 152 + $i % 8,
                    'length_date' => $base->toDateString(),
                    'weight_kg' => 52 + ($i % 9),
                    'weight_date' => $base->toDateString(),
                    'status' => $i % 7 === 0 ? 'low' : 'normal',
                ]
            );

            if ($pregnancy && $pregnancy->ended_at) {
                foreach (['postpartum_24h', 'postpartum_7d'] as $type) {
                    MaternalAssessment::firstOrCreate(
                        [
                            'maternal_care_target_client_id' => $client->id,
                            'assessment_type' => $type,
                        ],
                        [
                            'length_cm' => 152 + $i % 8,
                            'length_date' => Carbon::parse($pregnancy->ended_at)->addDay()->toDateString(),
                            'weight_kg' => 50 + ($i % 7),
                            'weight_date' => Carbon::parse($pregnancy->ended_at)->addDay()->toDateString(),
                            'status' => $i % 6 === 0 ? 'low' : 'normal',
                        ]
                    );
                }

                foreach (['first', 'second', 'third'] as $m => $month) {
                    MaternalPostpartumCare::firstOrCreate(
                        [
                            'maternal_care_target_client_id' => $client->id,
                            'iron_month' => $month,
                        ],
                        [
                            'iron_given' => $m === 0 ? '60 tablets' : '30 tablets',
                            'vitamin_a_date' => $m === 0 ? Carbon::parse($pregnancy->ended_at)->addDay()->toDateString() : null,
                            'remarks' => '[TEST] Postpartum care follow-up',
                        ]
                    );
                }
            }
        }
    }

    private function seedMaternalOutcomes(array $ctx): void
    {
        $walkIns = WalkInPatient::whereNull('user_id')->orderBy('id')->take(10)->get();
        if ($walkIns->count() < 8) {
            return;
        }

        $places = [
            'home', 'barangay_health_station', 'rhu', 'city_hospital',
            'provincial_hospital', 'private_hospital', 'in_transit', 'other',
        ];
        $causes = [
            ['Postpartum hemorrhage', 'hemorrhage'],
            ['Severe preeclampsia with seizures', 'hypertension_eclampsia'],
            ['Puerperal sepsis', 'sepsis'],
            ['Obstructed labor', 'obstructed_labor'],
            ['Unsafe abortion complication', 'unsafe_abortion'],
            ['Pulmonary embolism', 'embolism'],
            ['Other direct obstetric cause', 'other_direct'],
            ['Indirect cardiac complication', 'indirect_cause'],
        ];
        $timings = [
            'during_pregnancy', 'during_delivery', 'within_24_hours_postpartum',
            'within_7_days_postpartum', 'within_42_days_postpartum', 'unknown',
        ];
        $audit = ['pending', 'under_review', 'reviewed', 'closed'];
        $deaths = [];

        foreach (range(0, 7) as $i) {
            $walkIn = $walkIns[$i];
            $status = $audit[$i % count($audit)];
            $reviewed = in_array($status, ['reviewed', 'closed'], true);

            $deaths[] = $this->findOrCreateOnDate(
                MaternalDeath::class,
                ['walk_in_patient_id' => $walkIn->id],
                'death_date',
                today()->subDays(20 + $i * 9)->toDateString(),
                [
                    'recorded_by_id' => $this->staffFor($ctx, 'bhw', $walkIn->barangay)?->id,
                    'reviewed_by_id' => $reviewed ? $this->staffFor($ctx, 'midwife', $walkIn->barangay)?->id : null,
                    'purok_id' => $walkIn->purok_id,
                    'barangay' => $walkIn->barangay,
                    'death_time' => sprintf('%02d:40:00', 2 + $i % 20),
                    'age_at_death' => 22 + $i * 3,
                    'place_of_death' => $places[$i % count($places)],
                    'cause_of_death' => $causes[$i % count($causes)][0],
                    'cause_category' => $causes[$i % count($causes)][1],
                    'death_timing' => $timings[$i % count($timings)],
                    'notes' => '[TEST] Fictional maternal death for audit workflow',
                    'audit_status' => $status,
                    'audit_notes' => $reviewed ? '[TEST] Audit completed with recommendations.' : null,
                    'reviewed_at' => $reviewed ? now()->subDays(3 + $i) : null,
                ]
            );
        }

        $complications = [
            'severe_hemorrhage', 'eclampsia', 'severe_preeclampsia', 'sepsis',
            'ruptured_uterus', 'severe_anemia', 'obstructed_labor',
            'placenta_previa', 'placental_abruption', 'other',
        ];
        $outcomes = [
            'survived_no_intervention', 'survived_with_intervention',
            'transferred_to_higher_facility', 'died',
        ];

        $pregnancies = Pregnancy::whereIn('user_id', $ctx['patientIds'])->orderBy('id')->take(12)->get();

        foreach ($pregnancies as $i => $pregnancy) {
            $patient = $ctx['patients']->firstWhere('id', $pregnancy->user_id);
            if (!$patient) {
                continue;
            }

            $this->findOrCreateOnDate(
                MaternalMorbidity::class,
                ['user_id' => $patient->id],
                'event_date',
                today()->subDays(15 + $i * 4)->toDateString(),
                [
                    'pregnancy_id' => $pregnancy->id,
                    'recorded_by_id' => $this->staffFor($ctx, 'bhw', $patient->barangay)?->id,
                    'reviewed_by_id' => $i % 4 >= 2 ? $this->staffFor($ctx, 'midwife', $patient->barangay)?->id : null,
                    'purok_id' => $patient->purok_id,
                    'barangay' => $patient->barangay,
                    'event_time' => '14:20:00',
                    'complication_type' => $complications[$i % count($complications)],
                    'place_of_event' => $places[$i % count($places)],
                    'outcome' => $outcomes[$i % count($outcomes)],
                    'description' => '[TEST] Fictional maternal complication',
                    'interventions_done' => '[TEST] Referral and stabilization performed',
                    'notes' => '[TEST] Morbidity audit fixture',
                    'review_status' => $audit[$i % count($audit)],
                    'review_notes' => $i % 4 >= 2 ? '[TEST] Case reviewed by midwife' : null,
                    'reviewed_at' => $i % 4 >= 2 ? now()->subDays(2 + $i) : null,
                ]
            );
        }

        $firstDeath = $deaths[0] ?? null;
        if ($firstDeath && $firstDeath->walk_in_patient_id) {
            $walkIn = WalkInPatient::find($firstDeath->walk_in_patient_id);
            if ($walkIn) {
                $this->findOrCreateOnDate(
                    MaternalMorbidity::class,
                    ['walk_in_patient_id' => $walkIn->id],
                    'event_date',
                    Carbon::parse($firstDeath->death_date)->toDateString(),
                    [
                        'maternal_death_id' => $firstDeath->id,
                        'recorded_by_id' => $this->staffFor($ctx, 'bhw', $walkIn->barangay)?->id,
                        'reviewed_by_id' => $this->staffFor($ctx, 'midwife', $walkIn->barangay)?->id,
                        'purok_id' => $walkIn->purok_id,
                        'barangay' => $walkIn->barangay,
                        'event_time' => '03:10:00',
                        'complication_type' => 'severe_hemorrhage',
                        'place_of_event' => 'home',
                        'outcome' => 'died',
                        'description' => '[TEST] Terminal morbidity linked to death audit',
                        'interventions_done' => '[TEST] BHW transported patient to RHU',
                        'review_status' => 'under_review',
                    ]
                );
            }
        }
    }

    private function seedPatientConditions(array $ctx): void
    {
        if (!Schema::hasTable('patient_conditions')) {
            return;
        }

        $conditions = [
            'Hypertension', 'Gestational Diabetes', 'Anemia', 'Asthma',
            'Previous Preeclampsia', 'Thyroid Disorder', 'Mitral Valve Prolapse',
        ];
        $statuses = ['Active', 'Under Review', 'Resolved'];
        $verification = ['Verified', 'Awaiting Verification', 'Reported'];

        foreach ($ctx['patients'] as $i => $patient) {
            if ($i % 2 !== 0) {
                continue;
            }

            PatientCondition::firstOrCreate(
                [
                    'user_id' => $patient->id,
                    'condition_name' => $conditions[$i % count($conditions)],
                ],
                [
                    'diagnosed_on' => today()->subMonths(2 + $i % 18)->toDateString(),
                    'status' => $statuses[$i % count($statuses)],
                    'verification_status' => $verification[$i % count($verification)],
                    'notes' => '[TEST] Fictional medical history entry',
                    'recorded_by_id' => $this->staffFor($ctx, 'midwife', $patient->barangay)?->id,
                    'updated_by_id' => $this->staffFor($ctx, 'midwife', $patient->barangay)?->id,
                ]
            );
        }
    }

    private function seedOrganization(array $ctx): void
    {
        $cho = $ctx['cho'];

        foreach ($ctx['bhws'] as $bhw) {
            $president = $this->staffFor($ctx, 'bhw_president', $bhw->assigned_barangay);
            $assignedBy = $president ?? $cho;

            if ($bhw->purok_id && $assignedBy) {
                BhwAssignment::firstOrCreate(
                    ['bhw_id' => $bhw->id, 'purok_id' => $bhw->purok_id],
                    [
                        'assigned_by_id' => $assignedBy->id,
                        'assigned_at' => now()->subMonths(6),
                        'is_active' => true,
                        'notes' => '[TEST] Primary catchment',
                    ]
                );
            }

            $extra = Purok::where('barangay', $bhw->assigned_barangay)
                ->where('id', '!=', $bhw->purok_id)
                ->first();

            if ($extra && $assignedBy) {
                BhwAssignment::firstOrCreate(
                    ['bhw_id' => $bhw->id, 'purok_id' => $extra->id],
                    [
                        'assigned_by_id' => $assignedBy->id,
                        'assigned_at' => now()->subYear(),
                        'is_active' => false,
                        'notes' => '[TEST] Previous catchment before realignment',
                    ]
                );
            }
        }

        $midwives = $ctx['midwives']->values();
        $presidents = $ctx['presidents']->values();

        if ($cho && $midwives->count() >= 2) {
            StaffTransition::firstOrCreate(
                [
                    'type' => 'midwife_replace',
                    'outgoing_user_id' => $midwives[0]->id,
                    'incoming_names' => $midwives[1]->name,
                ],
                [
                    'incoming_user_ids' => [$midwives[1]->id],
                    'performed_by_id' => $cho->id,
                    'outgoing_name' => $midwives[0]->name,
                    'counts' => ['checkups' => 8, 'health_records' => 31, 'patients' => 24],
                    'note' => '[TEST] Coverage handover during leave',
                ]
            );
        }

        if ($cho && $presidents->count() >= 2) {
            StaffTransition::firstOrCreate(
                [
                    'type' => 'president_replace',
                    'outgoing_user_id' => $presidents[0]->id,
                    'incoming_names' => $presidents[1]->name,
                ],
                [
                    'incoming_user_ids' => [$presidents[1]->id],
                    'performed_by_id' => $cho->id,
                    'outgoing_name' => $presidents[0]->name,
                    'counts' => ['reports' => 4],
                    'note' => '[TEST] President turnover',
                ]
            );
        }

        if ($cho && $ctx['bhws']->count() >= 2) {
            $outgoing = $ctx['bhws'][0];
            $incoming = $ctx['bhws'][1];
            StaffTransition::firstOrCreate(
                [
                    'type' => 'bhw_transfer',
                    'outgoing_user_id' => $outgoing->id,
                    'incoming_names' => $incoming->name,
                ],
                [
                    'incoming_user_ids' => [$incoming->id],
                    'performed_by_id' => $cho->id,
                    'outgoing_name' => $outgoing->name,
                    'counts' => ['patients' => 6, 'checkups' => 3, 'health_records' => 12, 'reports' => 1],
                    'note' => '[TEST] Catchment reassignment',
                ]
            );
        }

        if ($cho && $ctx['rhus']->isNotEmpty()) {
            $rhu = $ctx['rhus'][0];
            ChoHandover::firstOrCreate(
                [
                    'outgoing_name' => $cho->name,
                    'incoming_name' => $rhu->name,
                    'auth_method' => 'password',
                ],
                [
                    'outgoing_user_id' => $cho->id,
                    'incoming_user_id' => $rhu->id,
                    'performed_by_id' => $cho->id,
                    'former_signature_path' => null,
                ]
            );

            ChoHandover::firstOrCreate(
                [
                    'outgoing_name' => $rhu->name,
                    'incoming_name' => $cho->name,
                    'auth_method' => 'signature',
                ],
                [
                    'outgoing_user_id' => $rhu->id,
                    'incoming_user_id' => $cho->id,
                    'performed_by_id' => $cho->id,
                    'former_signature_path' => 'signatures/test-fixture.png',
                ]
            );
        }
    }

    private function seedTransfersAndReviews(array $ctx): void
    {
        $statuses = ['pending', 'approved', 'rejected'];
        $pairs = [
            ['Cacaritan', 'Palaming'],
            ['Palaming', 'Abanon'],
            ['Abanon', 'Cacaritan'],
        ];

        foreach ($ctx['patients'] as $i => $patient) {
            if ($i % 9 !== 0 || $i > 63) {
                continue;
            }

            [$fromBarangay, $toBarangay] = $pairs[intdiv($i, 9) % count($pairs)];
            $fromBhw = $this->staffFor($ctx, 'bhw', $fromBarangay);
            $toBhw = $this->staffFor($ctx, 'bhw', $toBarangay);
            $status = $statuses[intdiv($i, 9) % count($statuses)];

            PatientTransfer::firstOrCreate(
                [
                    'user_id' => $patient->id,
                    'from_bhw_id' => $fromBhw?->id,
                    'to_bhw_id' => $toBhw?->id,
                ],
                [
                    'from_purok_id' => $patient->purok_id,
                    'to_purok_id' => $this->purokFor($ctx, $toBarangay)?->id,
                    'from_barangay' => $fromBarangay,
                    'to_barangay' => $toBarangay,
                    'reason' => '[TEST] Patient relocated to another barangay',
                    'status' => $status,
                    'requested_by_id' => $fromBhw?->id,
                    'reviewed_by_id' => $status !== 'pending' ? $this->staffFor($ctx, 'midwife', $fromBarangay)?->id : null,
                    'reviewer_notes' => $status === 'rejected' ? '[TEST] Address verification failed' : null,
                    'reviewed_at' => $status !== 'pending' ? now()->subDays(2) : null,
                ]
            );
        }

        $matchTypes = ['name_birthdate', 'name_contact', 'name_barangay', 'name_only'];
        $reviewStatuses = ['pending', 'linked', 'dismissed'];
        $plainWalkIns = WalkInPatient::whereNull('user_id')->orderBy('id')->take(8)->get();

        foreach ($plainWalkIns as $i => $walkIn) {
            $patient = $ctx['patients'][($i * 5 + 3) % $ctx['patients']->count()];
            $status = $reviewStatuses[$i % count($reviewStatuses)];
            $reviewed = $status !== 'pending';

            DuplicateReview::firstOrCreate(
                [
                    'user_id' => $patient->id,
                    'walk_in_patient_id' => $walkIn->id,
                    'match_type' => $matchTypes[$i % count($matchTypes)],
                    'score' => 97 - $i * 4,
                ],
                [
                    'matched_user_id' => $i % 2 === 0 ? $patient->id : null,
                    'status' => $status,
                    'reviewed_by_id' => $reviewed ? $this->staffFor($ctx, 'midwife', $walkIn->barangay)?->id : null,
                    'reviewer_notes' => $reviewed ? '[TEST] Duplicate candidate reviewed' : null,
                    'reviewed_at' => $reviewed ? now()->subDays(4) : null,
                ]
            );
        }
    }

    private function seedEmergencyAlerts(array $ctx): void
    {
        $alerts = [
            ['Severe headache with visual disturbance', '160/110', 'active'],
            ['Heavy vaginal bleeding since last night', '90/60', 'active'],
            ['Difficulty breathing and chest pain', '130/90', 'acknowledged'],
            ['High fever with chills after delivery', '120/80', 'acknowledged'],
            ['Seizure episode at home', '170/120', 'resolved'],
            ['Severe abdominal pain', '110/70', 'resolved'],
        ];
        $referralId = CheckupReferral::whereIn('user_id', $ctx['patientIds'])->value('id');
        $plainWalkIns = WalkInPatient::whereNull('user_id')->orderBy('id')->take(6)->get();

        foreach ($alerts as $i => [$symptoms, $bp, $status]) {
            $patient = $ctx['patients'][($i * 7 + 2) % $ctx['patients']->count()];
            $midwife = $this->staffFor($ctx, 'midwife', $patient->barangay);
            $bhw = $this->staffFor($ctx, 'bhw', $patient->barangay);
            $walkIn = $i >= 4 ? $plainWalkIns->get($i - 4) : null;

            $key = $walkIn
                ? ['walk_in_patient_id' => $walkIn->id, 'symptoms' => $symptoms]
                : ['user_id' => $patient->id, 'symptoms' => $symptoms];

            $attributes = [
                'reported_by_id' => $bhw?->id,
                'bp' => $bp,
                'notes' => '[TEST] Fictional emergency alert',
                'location' => 'Purok 1, ' . ($walkIn?->barangay ?? $patient->barangay),
                'barangay' => $walkIn?->barangay ?? $patient->barangay,
                'purok_id' => $walkIn?->purok_id ?? $patient->purok_id,
                'status' => $status,
                'referral_id' => $i % 2 === 0 ? $referralId : null,
            ];

            if ($status === 'acknowledged') {
                $attributes['acknowledged_by_id'] = $midwife?->id;
                $attributes['acknowledged_at'] = now()->subHours(4);
            } elseif ($status === 'resolved') {
                $attributes['acknowledged_by_id'] = $midwife?->id;
                $attributes['acknowledged_at'] = now()->subDay();
                $attributes['resolved_at'] = now()->subHours(6);
            }

            EmergencyAlert::firstOrCreate($key, $attributes);
        }
    }

    private function seedPreventiveInterventions(array $ctx): void
    {
        $highRisk = Pregnancy::whereIn('user_id', $ctx['patientIds'])
            ->where('is_high_risk', true)
            ->get()
            ->pluck('user_id')
            ->unique()
            ->values();

        $types = [
            'hypertension_management', 'anemia_prevention', 'adolescent_care',
            'care_continuity', 'pregnancy_monitoring', 'general_health',
        ];
        $levels = ['High', 'Critical', 'Medium'];

        foreach ($highRisk as $i => $userId) {
            $patient = $ctx['patients']->firstWhere('id', $userId);
            if (!$patient) {
                continue;
            }

            $type = $types[$i % count($types)];
            $level = $levels[$i % count($levels)];

            PreventiveIntervention::firstOrCreate(
                [
                    'user_id' => $patient->id,
                    'risk_level' => $level,
                    'intervention_type' => $type,
                ],
                [
                    'trigger_reason' => '[TEST] Elevated blood pressure with risk flags',
                    'recommendations' => "[TEST] Schedule follow-up within 48 hours\n[TEST] Repeat BP monitoring",
                    'status' => $i % 3 === 0 ? 'completed' : 'triggered',
                    'triggered_at' => now()->subDays(6 + $i),
                    'completed_at' => $i % 3 === 0 ? now()->subDays(2 + $i) : null,
                    'notes' => '[TEST] Preventive intervention fixture',
                ]
            );
        }
    }

    private function seedEngagement(array $ctx): void
    {
        $staff = $ctx['staff'];
        $patients = $ctx['patients'];
        $templates = [
            ['High-risk patient needs review', 'A patient in your catchment was flagged High risk.', 'warning'],
            ['Monthly report awaiting action', 'A BHW monthly report is waiting for your review.', 'info'],
            ['Supply request submitted', 'A new supply request needs approval.', 'info'],
            ['Follow-up visit completed', 'A scheduled follow-up visit was completed.', 'success'],
            ['Duplicate record detected', 'A possible duplicate patient record needs verification.', 'warning'],
        ];

        foreach ($staff as $i => $member) {
            foreach (range(0, 2) as $n) {
                [$title, $message, $type] = $templates[($i + $n) % count($templates)];

                Notification::firstOrCreate(
                    [
                        'user_id' => $member->id,
                        'title' => '[TEST] ' . $title,
                        'message' => $message,
                    ],
                    [
                        'type' => $type,
                        'is_read' => ($i + $n) % 3 === 0,
                        'read_at' => ($i + $n) % 3 === 0 ? now()->subDay() : null,
                        'category' => 'test_fixture',
                    ]
                );
            }
        }

        $midwife = $this->staffFor($ctx, 'midwife', 'Cacaritan');
        $president = $this->staffFor($ctx, 'bhw_president', 'Palaming');
        $bhw = $this->staffFor($ctx, 'bhw', 'Abanon');
        $rhu = $ctx['rhus']->first();
        $cho = $ctx['cho'];

        $threads = [
            [$patients[0], $midwife, '[TEST] Prenatal schedule question', 'When is my next prenatal visit?', $patients[0], 'Your next visit is scheduled next week.'],
            [$bhw, $president, '[TEST] Report reminder', 'Reminder to submit the monthly report.', $president, 'Received. I will review it today.'],
            [$president, $midwife, '[TEST] High-risk escalation', 'Please review a high-risk referral from our barangay.', $midwife, 'Escalation received and queued for review.'],
            [$rhu, $cho, '[TEST] Supply shortfall', 'We are running low on prenatal kits.', $cho, 'Approved for urgent replenishment.'],
            [$midwife, $patients[1], '[TEST] Checkup reminder', 'Please confirm your scheduled checkup.', $patients[1], 'Confirmed, thank you.'],
        ];

        foreach ($threads as $i => [$sender, $receiver, $subject, $body, $replySender, $replyBody]) {
            if (!$sender || !$receiver) {
                continue;
            }

            $message = $this->message(
                ['sender_id' => $sender->id, 'receiver_id' => $receiver->id, 'subject' => $subject],
                ['body' => $body, 'is_read' => $i % 2 === 0, 'is_sent' => true]
            );

            $this->message(
                [
                    'reply_to_id' => $message->id,
                    'sender_id' => $replySender->id,
                    'receiver_id' => $sender->id,
                ],
                [
                    'subject' => 'Re: ' . $subject,
                    'body' => $replyBody,
                    'is_read' => false,
                    'is_sent' => true,
                ]
            );
        }

        $topics = [
            'What should I bring to my first prenatal check-up?',
            'How do I log an irregular cycle?',
            'Is it normal to feel dizzy in the second trimester?',
            'Where can I get free prenatal vitamins?',
            'How often should I have my blood pressure checked?',
            'What are the warning signs during pregnancy?',
            'Can I continue working during my third trimester?',
            'How do I reschedule a missed checkup?',
            'What is the newborn screening schedule?',
            'How can I track my postpartum recovery?',
            'Where is the nearest RHU for family planning?',
            'How do I update my emergency contact?',
        ];
        $posts = [];

        foreach ($topics as $i => $topic) {
            $author = $patients[($i * 3 + 1) % $patients->count()];
            $posts[] = ForumPost::firstOrCreate(
                ['user_id' => $author->id, 'content' => '[TEST] ' . $topic],
                ['status' => 'active']
            );
        }

        foreach ($posts as $i => $post) {
            $commenter = $patients[($i * 5 + 4) % $patients->count()];
            ForumComment::firstOrCreate(
                ['post_id' => $post->id, 'user_id' => $commenter->id],
                ['content' => '[TEST] Thanks for sharing this question.']
            );

            foreach ([($i + 7), ($i + 11)] as $offset) {
                $liker = $patients[($offset * 2) % $patients->count()];
                if (!ForumLike::where('post_id', $post->id)->where('user_id', $liker->id)->exists()) {
                    $like = new ForumLike(['post_id' => $post->id, 'user_id' => $liker->id]);
                    if (Schema::hasColumn('forum_likes', 'user_id_old__deprecated')) {
                        $like->setAttribute('user_id_old__deprecated', $liker->id);
                    }
                    $like->save();
                }
            }
        }
    }

    private function seedOperations(array $ctx): void
    {
        $taskTypes = ['patient_visit', 'data_collection', 'follow_up', 'report_submission', 'other'];
        $taskStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];

        foreach ($ctx['bhws'] as $i => $bhw) {
            $president = $this->staffFor($ctx, 'bhw_president', $bhw->assigned_barangay);

            foreach (range(0, 3) as $n) {
                $status = $taskStatuses[($i + $n) % count($taskStatuses)];

                Task::firstOrCreate(
                    [
                        'title' => '[TEST] ' . ucfirst(str_replace('_', ' ', $taskTypes[($i + $n) % count($taskTypes)])) . ' ' . $bhw->id . '-' . $n,
                        'assigned_to_id' => $bhw->id,
                    ],
                    [
                        'assigned_by_id' => $president?->id ?? $ctx['cho']?->id,
                        'task_type' => $taskTypes[($i + $n) % count($taskTypes)],
                        'description' => '[TEST] Fictional task for workflow coverage',
                        'status' => $status,
                        'due_date' => today()->addDays(($n - 1) * 4),
                        'completed_at' => $status === 'completed' ? now()->subDay() : null,
                    ]
                );
            }
        }

        $categories = ['vitamins', 'vaccines', 'birthing_kits', 'medicines', 'equipment', 'ppe', 'other'];
        $supplyStatuses = ['draft', 'submitted', 'under_review', 'approved', 'declined', 'delivered'];
        $requesters = $ctx['rhus']->concat($ctx['midwives'])->concat($ctx['bhws'])->values();

        foreach ($requesters as $i => $requester) {
            $status = $supplyStatuses[$i % count($supplyStatuses)];
            $submitted = $status !== 'draft';
            $reviewed = in_array($status, ['under_review', 'approved', 'declined', 'delivered'], true);
            $approved = in_array($status, ['approved', 'delivered'], true);

            SupplyRequest::firstOrCreate(
                [
                    'requested_by_id' => $requester->id,
                    'supply_name' => '[TEST] ' . ucfirst($categories[$i % count($categories)]) . ' restock ' . $i,
                ],
                [
                    'supply_category' => $categories[$i % count($categories)],
                    'quantity_requested' => 5 + $i * 3,
                    'unit' => 'boxes',
                    'urgency' => ['routine', 'urgent', 'emergency'][$i % 3],
                    'reason' => '[TEST] Fictional inventory restock',
                    'status' => $status,
                    'cho_notes' => $reviewed ? '[TEST] Reviewed by CHO staff' : null,
                    'expected_delivery_date' => $approved ? today()->addDays(5) : null,
                    'submitted_at' => $submitted ? now()->subDays(4) : null,
                    'reviewed_at' => $reviewed ? now()->subDays(2) : null,
                    'approved_by_id' => $approved ? $ctx['cho']?->id : null,
                    'approved_at' => $approved ? now()->subDay() : null,
                    'delivered_at' => $status === 'delivered' ? now() : null,
                ]
            );
        }

        $reportStates = [
            'submitted_to_president', 'returned_to_president', 'submitted_to_midwife',
            'approved_by_midwife', 'approved_by_rhu', 'submitted_to_cho', 'received_by_cho',
        ];

        foreach ($ctx['bhws'] as $i => $bhw) {
            $president = $this->staffFor($ctx, 'bhw_president', $bhw->assigned_barangay);
            $midwife = $this->staffFor($ctx, 'midwife', $bhw->assigned_barangay);
            $rhu = $ctx['rhus']->first();

            // One report per workflow state so every approval screen has data.
            foreach ($reportStates as $s => $state) {
                $presidentApproved = in_array($state, ['submitted_to_midwife', 'approved_by_midwife', 'approved_by_rhu', 'submitted_to_cho', 'received_by_cho'], true);
                $midwifeApproved = in_array($state, ['approved_by_rhu', 'submitted_to_cho', 'received_by_cho'], true);
                $rhuApproved = in_array($state, ['submitted_to_cho', 'received_by_cho'], true);

                BhwMonthlyReport::firstOrCreate(
                    [
                        'bhw_id' => $bhw->id,
                        'title' => '[TEST] Workflow report ' . $state,
                    ],
                    [
                        'report_type' => 'health_records',
                        'description' => '[TEST] Fictional monthly report',
                        'report_month' => today()->month,
                        'report_year' => today()->year,
                        'total_records' => 12 + $i * 4 + $s,
                        'status' => 'draft',
                        'submission_status' => $state,
                        'submitted_to_president_by' => $bhw->id,
                        'submitted_to_president_at' => now()->subDays(8),
                        'president_notes' => $state === 'returned_to_president' ? '[TEST] Please correct the totals.' : null,
                        'rejected_by_id' => $state === 'returned_to_president' ? $president?->id : null,
                        'rejected_at' => $state === 'returned_to_president' ? now()->subDays(3) : null,
                        'rejection_reason' => $state === 'returned_to_president' ? '[TEST] Total records mismatch' : null,
                        'revision_count' => $state === 'returned_to_president' ? 1 : 0,
                        'approved_by_president' => $presidentApproved ? $president?->id : null,
                        'approved_by_president_at' => $presidentApproved ? now()->subDays(4) : null,
                        'submitted_to_midwife_by' => $presidentApproved ? $president?->id : null,
                        'submitted_to_midwife_at' => $presidentApproved ? now()->subDays(4) : null,
                        'approved_by_midwife' => $midwifeApproved ? $midwife?->id : null,
                        'approved_by_midwife_at' => $midwifeApproved ? now()->subDays(2) : null,
                        'approved_by_rhu_by' => $rhuApproved ? $rhu?->id : null,
                        'approved_by_rhu_at' => $rhuApproved ? now()->subDay() : null,
                        'submitted_to_cho_by' => $rhuApproved ? $rhu?->id : null,
                        'submitted_to_cho_at' => $rhuApproved ? now()->subDay() : null,
                        'received_by_cho_by' => $state === 'received_by_cho' ? $ctx['cho']?->id : null,
                        'received_by_cho_at' => $state === 'received_by_cho' ? now() : null,
                    ]
                );
            }
        }

        $smsTypes = ['appointment_reminder', 'high_risk_alert', 'missed_checkup', 'custom', 'broadcast'];
        foreach ($ctx['patients'] as $i => $patient) {
            if ($i % 3 !== 0) {
                continue;
            }

            $type = $smsTypes[$i % count($smsTypes)];
            $status = $i % 11 === 0 ? 'failed' : ($i % 7 === 0 ? 'pending' : 'sent');

            SmsLog::firstOrCreate(
                [
                    'phone_number' => $patient->contact_number,
                    'message' => '[TEST] ' . $type . ' SMS for ' . $patient->first_name,
                ],
                [
                    'user_id' => $patient->id,
                    'type' => $type,
                    'status' => $status,
                    'error_message' => $status === 'failed' ? 'Gateway timeout (fiction)' : null,
                    'provider_sid' => $status === 'sent' ? 'TEST-SID-' . $patient->id : null,
                    'sent_at' => $status === 'sent' ? now()->subHours($i % 48) : null,
                ]
            );
        }

        $actions = ['create', 'update', 'approve', 'reject', 'login', 'export', 'other'];
        $roles = ['bhw', 'bhw_president', 'midwife', 'rhu', 'cho'];
        foreach (range(0, 59) as $i) {
            $actor = $ctx['staff'][$i % $ctx['staff']->count()];

            ActivityLog::firstOrCreate(
                [
                    'user_id' => $actor->id,
                    'description' => '[TEST] Fixture activity #' . $i,
                ],
                [
                    'user_role' => $roles[$i % count($roles)],
                    'user_name' => $actor->name,
                    'action' => $actions[$i % count($actions)],
                    'model_type' => ['App\\Models\\User', 'App\\Models\\HealthRecord', 'App\\Models\\BhwMonthlyReport', 'App\\Models\\SupplyRequest'][$i % 4],
                    'model_id' => $ctx['patients'][$i % $ctx['patients']->count()]->id,
                    'ip_address' => '192.0.2.' . (10 + $i % 200),
                    'user_agent' => 'ReproCare test fixture',
                    'is_protected' => false,
                ]
            );
        }

        foreach ($ctx['bhws'] as $i => $bhw) {
            foreach (range(1, 4) as $n) {
                SyncLog::firstOrCreate(
                    ['sync_uuid' => sprintf('test-sync-%d-%d', $bhw->id, $n)],
                    [
                        'endpoint' => '/api/sync/' . ['patients', 'health-records', 'checkups', 'reports'][$n - 1],
                        'user_id' => $bhw->id,
                        'client_timestamp' => now()->subHours($n),
                        'synced_at' => now()->subHours($n)->addMinutes(2),
                    ]
                );
            }
        }
    }

    private function message(array $key, array $values): Message
    {
        $existing = Message::where($key)->first();
        if ($existing) {
            return $existing;
        }

        $message = new Message($key + $values);
        if (Schema::hasColumn('messages', 'sender_id_old__deprecated')) {
            $message->setAttribute('sender_id_old__deprecated', $key['sender_id']);
            $message->setAttribute('receiver_id_old__deprecated', $key['receiver_id']);
        }
        $message->save();

        return $message;
    }
}
