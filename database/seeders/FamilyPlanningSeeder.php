<?php

namespace Database\Seeders;

use App\Models\FamilyPlanningRecord;
use App\Models\Pregnancy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

/**
 * Gives the family-planning module a caseload worth reviewing: counseling
 * requests waiting for a health worker, active methods with resupply dates,
 * overdue follow-ups, methods stopped for side effects, referrals, and
 * postpartum plans linked to real pregnancies.
 *
 * Two things hold it together:
 *  - Barangay "(Poblacion)" clients are created here because the RHU Midwife
 *    demo account is scoped to Poblacion and had no women at all, so its
 *    family-planning list and client picker were permanently empty.
 *  - Rows are matched by client + note text, so a re-run cleans up the old
 *    "[TEST]" fixtures without duplicating or overwriting real entries.
 */
class FamilyPlanningSeeder extends Seeder
{
    private const METHODS = ['pill', 'injectable', 'implant', 'iud', 'condom', 'natural_method', 'other'];

    /** How long one supply/coverage period lasts, in days. */
    private const SUPPLY_DAYS = [
        'pill' => 28,
        'injectable' => 90,
        'implant' => 365,
        'iud' => 365,
        'condom' => 60,
        'natural_method' => 120,
        'other' => 90,
    ];

    private const TIMINGS = ['immediately_after_delivery', 'before_discharge', 'six_week_visit', 'undecided'];

    private array $recorderCache = [];

    public function run(): void
    {
        $this->removeTestRecords();
        $this->ensurePoblacionCaseload();

        $clients = $this->clientPool();
        if ($clients->isEmpty()) {
            $this->command?->warn('FamilyPlanningSeeder: no approved women found, skipping.');

            return;
        }

        $records = 0;
        foreach ($clients->values()->all() as $index => $client) {
            foreach ($this->storylineFor($index, $client) as $definition) {
                if ($this->createRecord($client, $definition)) {
                    $records++;
                }
            }
        }

        $records += $this->seedPostpartumPlans();
        $records += $this->seedNamedDemoClients();

        $this->command?->info("Family planning: {$records} service records across {$clients->count()} clients.");
    }

    private function removeTestRecords(): void
    {
        FamilyPlanningRecord::where('notes', 'like', '%[TEST]%')->delete();
        FamilyPlanningRecord::where('side_effects', 'like', '%[TEST]%')->delete();
    }

    /**
     * The RHU proper (Poblacion) had no registered women, so the midwife demo
     * account could not open a single family-planning record.
     */
    private function ensurePoblacionCaseload(): void
    {
        $password = Hash::make('password123');

        foreach ($this->poblacionWomen() as [$first, $last, $barangay, $dob, $contact, $houseNo, $purokNo]) {
            $email = strtolower(preg_replace('/[^a-z]/i', '', $first . '.' . $last)) . '@reprocare.test';

            User::seedAccount(
                ['email' => $email],
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'email' => $email,
                    'password' => $password,
                    'date_of_birth' => $dob,
                    'gender' => 'female',
                    'contact_number' => $contact,
                    'address' => "House {$houseNo}, Purok {$purokNo}, {$barangay}, San Carlos City, Pangasinan",
                    'barangay' => $barangay,
                    'role' => 'user',
                    'status' => 'approved',
                    'is_profile_complete' => true,
                ]
            );
        }
    }

    /**
     * One entry per woman: first, last, barangay (the real San Carlos City
     * "Poblacion" names so the RHU Midwife jurisdiction matches), birth date,
     * mobile number, house and purok numbers.
     */
    private function poblacionWomen(): array
    {
        return [
            ['Rosalie', 'Mendoza', 'Burgos Padlan (Poblacion)', '1993-03-11', '09176601145', 14, 1],
            ['Divina', 'Alcantara', 'Rizal (Poblacion)', '1989-07-24', '09204478102', 6, 2],
            ['Maricel', 'Bustamante', 'Mabini (Poblacion)', '1996-11-02', '09392215674', 21, 1],
            ['Joanna Marie', 'Reyes', 'Lucban (Poblacion)', '2001-02-17', '09478830215', 3, 3],
            ['Terpsicore', 'Villanueva', 'Palaris (Poblacion)', '1991-05-30', '09561174328', 11, 2],
            ['Nieves', 'Tolentino', 'Roxas Boulevard (Poblacion)', '1987-09-14', '09615520783', 8, 1],
            ['Carlita', 'Domingo', 'Quezon Boulevard (Poblacion)', '1998-12-08', '09755591246', 30, 4],
            ['Rowena', 'Guzman', 'Perez Boulevard (Poblacion)', '1994-08-21', '09085513470', 17, 2],
            ['Imelda', 'Castillo', 'Bonifacio (Poblacion)', '1985-04-05', '09095526187', 5, 1],
            ['Shaira', 'Ocampo', 'Bugallon-Posadas Street (Poblacion)', '2003-06-27', '09195440726', 26, 3],
            ['Lorna', 'Pascual', 'Burgos Padlan (Poblacion)', '1990-10-19', '09275563218', 9, 1],
            ['Evelyn', 'Mercado', 'Rizal (Poblacion)', '1997-01-09', '09365572049', 13, 2],
            ['Grace', 'Nery', 'Mabini (Poblacion)', '1992-06-16', '09505589371', 2, 1],
            ['Mila', 'Arceo', 'Lucban (Poblacion)', '1988-03-28', '09915594028', 22, 3],
            ['Fe', 'Sumadcop', 'Palaris (Poblacion)', '2000-08-12', '09615537469', 7, 2],
            ['Rhea', 'Del Rosario', 'Roxas Boulevard (Poblacion)', '1995-12-23', '09475528130', 19, 1],
        ];
    }

    /**
     * Approved women, capped per barangay so every RHU/BHW jurisdiction has a
     * handful of clients without pushing thousands of rows into a boot.
     */
    private function clientPool(): Collection
    {
        $women = User::where('role', 'user')
            ->where('status', 'approved')
            ->where('barangay', '!=', '')
            ->orderBy('barangay')
            ->orderBy('id')
            ->get();

        $seen = [];

        return $women->filter(function (User $user) use (&$seen) {
            $key = strtolower(trim($user->barangay));
            $seen[$key] = ($seen[$key] ?? 0) + 1;

            // Poblacion clients are the RHU Midwife caseload, keep all of them.
            $cap = str_contains($key, 'poblacion') ? 99 : 4;

            return $seen[$key] <= $cap;
        })->values();
    }

    /**
     * Nine counseling storylines, assigned by client position so the staff
     * list mixes requests, active methods, overdue resupply, switches,
     * referrals and postpartum plans instead of one repeated shape.
     */
    private function storylineFor(int $index, User $client): array
    {
        $method = self::METHODS[$index % count(self::METHODS)];
        $second = self::METHODS[($index + 3) % count(self::METHODS)];
        $intention = $this->pick(['spacing', 'avoiding', 'spacing', 'trying', 'undecided', 'avoiding'], $index);
        $label = $this->label($method);
        $secondLabel = $this->label($second);
        $age = $this->ageInYears($client);

        switch ($index % 9) {
            case 0:
                // Requested: she sent it herself, so no health worker is attached yet.
                return [[
                    'status' => 'requested',
                    'client_entered' => true,
                    'preferred_method' => $method,
                    'pregnancy_intention' => $intention,
                    'requested_days_ago' => 1 + ($index % 11),
                    'notes' => $this->pick([
                        'Asks whether ' . $label . ' is free at the RHU before she decides.',
                        'Wants private counseling on ' . $label . '; her husband is willing if it suits her.',
                        'Heard ' . $label . ' causes irregular bleeding and would like it explained first.',
                        'She is ' . $age . ' years old with ' . ($index % 4 + 1) . ' children and wants to space the next pregnancy by three years.',
                        'Her ' . $label . ' supply ran out last month and she asks for a resupply appointment.',
                        'Breastfeeding her ' . ($index % 8 + 2) . '-month-old and wants a method that will not affect her milk.',
                        'Not sure yet — asks the midwife to compare ' . $label . ' with ' . $secondLabel . '.',
                    ], $index),
                ]];

            case 1:
                // Active, resupply still ahead of her.
                return [[
                    'status' => 'active',
                    'preferred_method' => $method,
                    'method_given' => $method,
                    'pregnancy_intention' => $intention,
                    'counseling_days_ago' => 30 + ($index % 40),
                    'start_days_ago' => 30 + ($index % 40),
                    'follow_up_offset_days' => $this->supplyDays($method) - (5 + $index % 15),
                    'informed_choice' => true,
                    'notes' => $this->pick([
                        'Counseled at the RHU on how ' . $label . ' works, what side effects to expect and when to return. She chose it on her own.',
                        $label . ' given as free DOH supply — no charge to her. Reminded to return before it runs out.',
                        'Started ' . $label . ' after a negative pregnancy test. Warning signs to report were written on her card.',
                        'Partner joined the counseling and agreed on ' . $label . '. Return date explained twice.',
                    ], $index),
                ]];

            case 2:
                // Active but the follow-up date already passed.
                return [[
                    'status' => 'active',
                    'preferred_method' => $method,
                    'method_given' => $method,
                    'pregnancy_intention' => $intention,
                    'counseling_days_ago' => 70 + ($index % 60),
                    'start_days_ago' => 70 + ($index % 60),
                    'follow_up_offset_days' => -1 - ($index % 24),
                    'informed_choice' => true,
                    'notes' => $this->pick([
                        'Resupply overdue. Flagged for a home visit by her BHW.',
                        'She missed the ' . $label . ' appointment twice; asked the BHW to follow her up at home.',
                        'Works in a nearby town and returns late — advised to pick up supplies at any RHU, free of charge.',
                        'Using ' . $label . ' correctly but late for the check-in. No complaints to report.',
                    ], $index),
                ]];

            case 3:
                // Switched methods: the old one stopped for side effects.
                return [
                    [
                        'status' => 'stopped',
                        'preferred_method' => $second,
                        'method_given' => $second,
                        'pregnancy_intention' => $intention,
                        'counseling_days_ago' => 200 + ($index % 60),
                        'start_days_ago' => 200 + ($index % 60),
                        'stop_days_ago' => 60 + ($index % 30),
                        'informed_choice' => true,
                        'side_effects' => $this->pick([
                            'Irregular spotting for two months, headaches',
                            'Weight gain and mood swings',
                            'Nausea with the first two packs',
                            'Tender injection site and delayed periods',
                        ], $index),
                        'notes' => 'Stopped ' . $secondLabel . ' because of the side effects she described. No complication on follow-up.',
                    ],
                    [
                        'status' => 'active',
                        'preferred_method' => $method,
                        'method_given' => $method,
                        'pregnancy_intention' => $intention,
                        'counseling_days_ago' => 60 + ($index % 30),
                        'start_days_ago' => 55 + ($index % 30),
                        'follow_up_offset_days' => $this->supplyDays($method) - (10 + $index % 20),
                        'informed_choice' => true,
                        'notes' => 'Switched from ' . $secondLabel . ' to ' . $label . ' after repeat counseling. She is comfortable with the change.',
                    ],
                ];

            case 4:
                // Long-acting method with the yearly check still ahead.
                return [[
                    'status' => 'active',
                    'preferred_method' => $method,
                    'method_given' => $method,
                    'pregnancy_intention' => $intention,
                    'counseling_days_ago' => 90 + ($index % 120),
                    'start_days_ago' => 88 + ($index % 120),
                    'follow_up_offset_days' => 60 + ($index % 200),
                    'informed_choice' => true,
                    'notes' => $this->pick([
                        $label . ' in place, threads and site checked. Explained she may return anytime before the coverage ends.',
                        'Counseling covered the ' . $label . ' duration and the removal schedule. She wants it removed only when she plans another baby.',
                        'No complaint at the ' . $label . ' check-in. Free of charge under the RHU program.',
                    ], $index),
                ]];

            case 5:
                // Referred out for insertion or specialist counseling.
                return [[
                    'status' => 'referred',
                    'preferred_method' => $method,
                    'pregnancy_intention' => $intention,
                    'counseling_days_ago' => 12 + ($index % 30),
                    'follow_up_offset_days' => 14 + ($index % 20),
                    'informed_choice' => true,
                    'notes' => $this->pick([
                        'Referred to the city health office for ' . $label . ' insertion; the RHU only rechecks it afterward.',
                        'Referred to the municipal midwife for ' . $label . ' counseling because she wants a long-acting option.',
                        'Referred for ' . $label . ' with her husband present. She asked for the insertion on her next off-day.',
                    ], $index),
                ]];

            case 6:
                // Postpartum period: breastfeeding, asking when to start.
                return [[
                    'status' => 'requested',
                    'client_entered' => true,
                    'preferred_method' => $method,
                    'pregnancy_intention' => 'spacing',
                    'requested_days_ago' => 3 + ($index % 9),
                    'notes' => $this->pick([
                        'Four months postpartum and breastfeeding. Asks when she may safely start ' . $label . '.',
                        'Delivered her second baby last month and wants to decide on ' . $label . ' before the 6-week visit.',
                        'Still bleeding lightly after delivery; asks whether ' . $label . ' can already be given.',
                    ], $index),
                ]];

            case 7:
                // Natural family planning used together with cycle tracking.
                return [[
                    'status' => 'active',
                    'preferred_method' => 'natural_method',
                    'method_given' => 'natural_method',
                    'pregnancy_intention' => $intention,
                    'counseling_days_ago' => 40 + ($index % 60),
                    'start_days_ago' => 40 + ($index % 60),
                    'follow_up_offset_days' => 30 + ($index % 60),
                    'informed_choice' => true,
                    'notes' => $this->pick([
                        'Taught the calendar method together with her cycle log; she records her own dates on the app.',
                        'Fertility-awareness counseling with her husband. She prefers a hormone-free option.',
                        'Using natural family planning while she decides on a long-term method later.',
                    ], $index),
                ]];

            default:
                // Stopped on her own — counseling still documented for the record.
                return [[
                    'status' => 'stopped',
                    'preferred_method' => $method,
                    'method_given' => $method,
                    'pregnancy_intention' => $intention,
                    'counseling_days_ago' => 150 + ($index % 90),
                    'start_days_ago' => 150 + ($index % 90),
                    'stop_days_ago' => 20 + ($index % 60),
                    'informed_choice' => true,
                    'side_effects' => $this->pick([
                        'None reported; she simply stopped coming back',
                        'Mild dizziness in the first month',
                        'Irregular bleeding, later confirmed normal',
                    ], $index),
                    'notes' => $this->pick([
                        'She and her husband now prefer natural family planning. ' . $label . ' stopped without complication.',
                        'Plans another pregnancy within the year, so ' . $label . ' was discontinued after counseling.',
                        'Moved to a nearby barangay; record closed as stopped and referred to her new RHU.',
                    ], $index),
                ]];
        }
    }

    /**
     * The accounts DatabaseSeeder prints as demo logins get a fuller history,
     * because they are the ones opened on the patient portal and by BHW Ana.
     */
    private function seedNamedDemoClients(): int
    {
        $histories = [
            'mariasanta@gmail.com' => [
                [
                    'status' => 'requested',
                    'client_entered' => true,
                    'preferred_method' => 'implant',
                    'pregnancy_intention' => 'spacing',
                    'requested_days_ago' => 2,
                    'notes' => 'Her implant expires next month and she asks whether the RHU can replace it for free.',
                ],
                [
                    'status' => 'active',
                    'preferred_method' => 'implant',
                    'method_given' => 'implant',
                    'pregnancy_intention' => 'spacing',
                    'counseling_days_ago' => 1050,
                    'start_days_ago' => 1040,
                    'follow_up_offset_days' => 0,
                    'informed_choice' => true,
                    'notes' => 'Implant inserted at the RHU after counseling on the three-year coverage. Resupply check now due.',
                ],
                [
                    'status' => 'stopped',
                    'preferred_method' => 'injectable',
                    'method_given' => 'injectable',
                    'pregnancy_intention' => 'spacing',
                    'counseling_days_ago' => 1400,
                    'start_days_ago' => 1395,
                    'stop_days_ago' => 1060,
                    'informed_choice' => true,
                    'side_effects' => 'Irregular bleeding every month and weight gain',
                    'notes' => 'Injectable stopped after eight months; she chose the implant instead because she forgets the shot schedule.',
                ],
            ],
            'madonna@gmail.com' => [
                [
                    'status' => 'requested',
                    'client_entered' => true,
                    'preferred_method' => 'pill',
                    'pregnancy_intention' => 'spacing',
                    'requested_days_ago' => 4,
                    'notes' => 'Breastfeeding her five-month-old and asks which pill is safe while nursing.',
                ],
                [
                    'status' => 'referred',
                    'preferred_method' => 'iud',
                    'pregnancy_intention' => 'spacing',
                    'counseling_days_ago' => 30,
                    'follow_up_offset_days' => 12,
                    'informed_choice' => true,
                    'notes' => 'Referred to the city health office for IUD insertion; the RHU will recheck her after the procedure.',
                ],
                [
                    'status' => 'active',
                    'preferred_method' => 'condom',
                    'method_given' => 'condom',
                    'pregnancy_intention' => 'spacing',
                    'counseling_days_ago' => 120,
                    'start_days_ago' => 118,
                    'follow_up_offset_days' => -6,
                    'informed_choice' => true,
                    'notes' => 'Dual protection with her partner while she waits for the IUD appointment. Condom supply is running out.',
                ],
            ],
            'maria.clara@example.test' => [
                [
                    'status' => 'requested',
                    'preferred_method' => 'implant',
                    'pregnancy_intention' => 'spacing',
                    'requested_days_ago' => 6,
                    'is_postpartum_plan' => true,
                    'pregnancy_lookup' => 'open',
                    'postpartum_start_timing' => 'before_discharge',
                    'informed_choice' => true,
                    'notes' => 'Antenatal plan made at her prenatal visit: she prefers the implant, to be started before she left the facility.',
                ],
                [
                    'status' => 'stopped',
                    'preferred_method' => 'pill',
                    'method_given' => 'pill',
                    'pregnancy_intention' => 'trying',
                    'counseling_days_ago' => 520,
                    'start_days_ago' => 515,
                    'stop_days_ago' => 300,
                    'informed_choice' => true,
                    'notes' => 'Stopped the pill when she and her husband decided to conceive. Now plans spacing after this pregnancy.',
                ],
            ],
            'elena.santos.highrisk@example.test' => [
                [
                    'status' => 'requested',
                    'preferred_method' => 'iud',
                    'pregnancy_intention' => 'spacing',
                    'requested_days_ago' => 9,
                    'is_postpartum_plan' => true,
                    'pregnancy_lookup' => 'open',
                    'postpartum_start_timing' => 'six_week_visit',
                    'informed_choice' => true,
                    'notes' => 'High-risk pregnancy: the midwife will place the IUD only after her six-week check clears her.',
                ],
                [
                    'status' => 'stopped',
                    'preferred_method' => 'injectable',
                    'method_given' => 'injectable',
                    'pregnancy_intention' => 'spacing',
                    'counseling_days_ago' => 700,
                    'start_days_ago' => 695,
                    'stop_days_ago' => 560,
                    'informed_choice' => true,
                    'side_effects' => 'Blood pressure rose during use, so the team advised stopping',
                    'notes' => 'Injectable discontinued on medical advice before this pregnancy; cycles were monitored afterward.',
                ],
            ],
        ];

        $created = 0;
        foreach ($histories as $email => $definitions) {
            $client = User::where('email', $email)->where('role', 'user')->first();
            if (! $client) {
                continue;
            }

            foreach ($definitions as $definition) {
                if ($this->createRecord($client, $definition)) {
                    $created++;
                }
            }
        }

        return $created;
    }

    /**
     * Postpartum plans are what the postpartum page and the pregnancy page
     * show, so every delivered woman gets the plan she made before birth and a
     * slice of the open pregnancies get an antenatal plan to review later.
     */
    private function seedPostpartumPlans(): int
    {
        $pregnancies = Pregnancy::whereNotNull('delivery_date')
            ->orderByDesc('delivery_date')
            ->get()
            ->unique('user_id');

        $antenatal = Pregnancy::whereNull('ended_at')
            ->whereNull('delivery_date')
            ->orderByDesc('lmp')
            ->limit(40)
            ->get()
            ->unique('user_id')
            ->reject(fn (Pregnancy $p) => $pregnancies->contains(fn (Pregnancy $delivered) => $delivered->user_id === $p->user_id));

        $created = 0;
        foreach ([[$pregnancies, true], [$antenatal, false]] as [$group, $isDelivered]) {
            foreach ($group->values()->all() as $index => $pregnancy) {
                $client = $pregnancy->woman;
                if (! $client || $client->role !== 'user') {
                    continue;
                }

                $method = self::METHODS[($index + ($isDelivered ? 1 : 4)) % count(self::METHODS)];
                $timing = $this->pick(self::TIMINGS, $index + ($isDelivered ? 0 : 2));

                $definition = $isDelivered
                    ? [
                        'status' => 'active',
                        'preferred_method' => $method,
                        'method_given' => $method,
                        'pregnancy_intention' => 'spacing',
                        'counseling_days_ago' => 60 + ($index % 90),
                        'start_days_ago' => 20 + ($index % 40),
                        'follow_up_offset_days' => 40 + ($index % 120),
                        'informed_choice' => true,
                        'is_postpartum_plan' => true,
                        'pregnancy_id' => $pregnancy->id,
                        'postpartum_start_timing' => $timing,
                        'notes' => $this->label($method) . ' started ' . $this->timingPhrase($timing)
                            . ' (delivered ' . Carbon::parse($pregnancy->delivery_date)->format('M j, Y')
                            . '). Counseling and consent were recorded before discharge.',
                    ]
                    : [
                        'status' => 'requested',
                        'preferred_method' => $method,
                        'pregnancy_intention' => 'spacing',
                        'requested_days_ago' => 5 + ($index % 40),
                        'is_postpartum_plan' => true,
                        'pregnancy_id' => $pregnancy->id,
                        'postpartum_start_timing' => $timing,
                        'informed_choice' => false,
                        'notes' => 'Antenatal plan made with the midwife: she prefers ' . $this->label($method)
                            . ', to be started ' . $this->timingPhrase($timing) . '.',
                    ];

                if ($this->createRecord($client, $definition)) {
                    $created++;
                }
            }
        }

        return $created;
    }

    private function createRecord(User $client, array $definition): bool
    {
        $notes = $definition['notes'] ?? null;
        if (! $notes) {
            return false;
        }

        $clientEntered = $definition['client_entered'] ?? false;

        $existing = FamilyPlanningRecord::firstOrCreate(
            ['user_id' => $client->id, 'notes' => $notes],
            [
                'pregnancy_id' => $definition['pregnancy_id'] ?? $this->lookupPregnancy($client, $definition['pregnancy_lookup'] ?? null),
                'is_postpartum_plan' => $definition['is_postpartum_plan'] ?? false,
                'postpartum_start_timing' => $definition['postpartum_start_timing'] ?? null,
                'recorded_by_id' => $clientEntered ? null : $this->recorderFor($client)?->id,
                'preferred_method' => $definition['preferred_method'] ?? null,
                'method_given' => $definition['method_given'] ?? null,
                'pregnancy_intention' => $definition['pregnancy_intention'] ?? null,
                'counseling_date' => $this->dateOrNull($definition['counseling_days_ago'] ?? null),
                'start_date' => $this->dateOrNull($definition['start_days_ago'] ?? null),
                'stop_date' => $this->dateOrNull($definition['stop_days_ago'] ?? null),
                'next_follow_up' => $this->dateOrNull(null, $definition['follow_up_offset_days'] ?? null),
                'side_effects' => $definition['side_effects'] ?? null,
                'informed_choice_confirmed' => $definition['informed_choice'] ?? false,
                'status' => $definition['status'],
            ]
        );

        if (! $existing->wasRecentlyCreated) {
            return false;
        }

        $stamp = isset($definition['requested_days_ago'])
            ? now()->subDays($definition['requested_days_ago'])
            : now()->subDays($definition['counseling_days_ago'] ?? 30)->subHour();

        $existing->forceFill(['created_at' => $stamp, 'updated_at' => $stamp])->saveQuietly();

        return true;
    }

    /**
     * A midwife or BHW whose jurisdiction covers the client's barangay, so the
     * "recorded by" name always belongs to the team that can actually see her.
     */
    private function recorderFor(User $client): ?User
    {
        $key = strtolower(trim((string) $client->barangay));
        if (array_key_exists($key, $this->recorderCache)) {
            return $this->recorderCache[$key];
        }

        $staff = User::whereIn('role', ['midwife', 'bhw'])
            ->where('status', 'approved')
            ->where(fn ($query) => $query->whereNotNull('barangay')->orWhereNotNull('assigned_barangay'))
            ->get();

        $match = $staff->first(function (User $worker) use ($key) {
            $scope = strtolower(trim((string) ($worker->assigned_barangay ?: $worker->barangay)));

            return $scope !== '' && str_contains($key, $scope);
        });

        return $this->recorderCache[$key] = ($match ?: $staff->first());
    }

    private function supplyDays(string $method): int
    {
        return self::SUPPLY_DAYS[$method] ?? 90;
    }

    private function lookupPregnancy(User $client, ?string $lookup): ?int
    {
        if ($lookup === 'delivered') {
            return Pregnancy::where('user_id', $client->id)
                ->whereNotNull('delivery_date')
                ->latest('delivery_date')
                ->value('id');
        }

        return $lookup === 'open'
            ? Pregnancy::where('user_id', $client->id)->whereNull('ended_at')->latest('lmp')->value('id')
            : null;
    }

    /** Days in the past (positive) or the future (negative, used for follow-ups). */
    private function dateOrNull(?int $daysAgo, ?int $offset = null): ?string
    {
        if ($daysAgo !== null) {
            return now()->subDays($daysAgo)->toDateString();
        }

        return $offset === null ? null : now()->addDays($offset)->toDateString();
    }

    private function ageInYears(User $client): int
    {
        return $client->date_of_birth
            ? max(15, (int) now()->diffInYears($client->date_of_birth))
            : 28;
    }

    private function label(string $value): string
    {
        $labels = [
            'natural_method' => 'Natural Family Planning',
            'iud' => 'IUD',
            'other' => 'another method',
            'immediately_after_delivery' => 'Immediately after delivery',
            'before_discharge' => 'Before discharge',
            'six_week_visit' => 'Six-week visit',
            'undecided' => 'Still undecided',
        ];

        return $labels[$value] ?? ucfirst(str_replace('_', ' ', $value));
    }

    /** Same timings, phrased so they read inside a sentence. */
    private function timingPhrase(string $timing): string
    {
        return [
            'immediately_after_delivery' => 'right after delivery',
            'before_discharge' => 'before she left the facility',
            'six_week_visit' => 'at the six-week postpartum visit',
            'undecided' => 'once she and her partner decide',
        ][$timing] ?? $timing;
    }

    private function pick(array $options, int $index): string
    {
        return $options[$index % count($options)];
    }
}
