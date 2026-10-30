<?php

namespace Database\Seeders;

use App\Models\Checkup;
use App\Models\CheckupReferral;
use App\Models\ChildCheckup;
use App\Models\ChildRecord;
use App\Models\Cycle;
use App\Models\EmergencyContact;
use App\Models\ForumComment;
use App\Models\ForumLike;
use App\Models\ForumPost;
use App\Models\HealthRecord;
use App\Models\MaternalCareTargetClient;
use App\Models\Message;
use App\Models\Newborn;
use App\Models\Notification;
use App\Models\PostpartumVisit;
use App\Models\Pregnancy;
use App\Models\Purok;
use App\Models\SupplyRequest;
use App\Models\Task;
use App\Models\User;
use App\Models\BhwMonthlyReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Opt-in, fictional test fixtures. Never call from DatabaseSeeder: that seeder
 * also runs on production boots. Re-running this seeder leaves existing rows
 * (including any edits made while testing) alone.
 */
class TestScenarioAccountsSeeder extends Seeder
{
    private const LOCATIONS = [
        ['Cacaritan', 'RHU 1'],
        ['Palaming', 'RHU 1'],
        ['Abanon', 'RHU 2'],
    ];

    private const SCENARIOS = [
        'onboarding', 'regular_cycle', 'irregular_cycle', 'early_pregnancy',
        'second_trimester', 'high_risk', 'pregnancy_review', 'pregnancy_revision',
        'postpartum', 'postpartum_alert', 'referral',
    ];

    public function run(): void
    {
        // Fixed pseudo-random permutation: names are varied, but a second run
        // maps every email to the same person on every machine.
        $firstNames = [
            'Amara', 'Belen', 'Carina', 'Dalia', 'Elisa', 'Faye', 'Giselle', 'Hazel',
            'Inez', 'Janelle', 'Katrina', 'Liza', 'Mariel', 'Nerissa', 'Oriana',
            'Paula', 'Rhea', 'Selena', 'Tessa', 'Yvette',
        ];
        $lastNames = [
            'Abad', 'Bautista', 'Castillo', 'Domingo', 'Espino', 'Ferrer', 'Garcia',
            'Herrera', 'Isidro', 'Jimenez', 'Lorenzo', 'Mercado', 'Navarro', 'Ocampo',
            'Pascual', 'Quizon', 'Rivera', 'Soriano', 'Tolentino', 'Villanueva',
        ];
        $roles = [
            'cho', 'rhu', 'rhu', 'midwife', 'midwife', 'midwife',
            'bhw_president', 'bhw_president', 'bhw_president', 'bhw', 'bhw', 'bhw',
        ];
        $password = Hash::make('TestAccount123!');
        $created = 0;

        DB::transaction(function () use ($firstNames, $lastNames, $roles, $password, &$created) {
            $puroks = [];
            foreach (self::LOCATIONS as $location) {
                $puroks[] = Purok::firstOrCreate(
                    ['name' => 'Purok 1', 'barangay' => $location[0]],
                    ['description' => 'Test scenario catchment']
                );
            }

            $staff = [];
            for ($i = 0; $i < 12; $i++) {
                $location = $i < 3 ? $i : ($i - 3) % 3;
                $staff[$roles[$i]][$location] = $this->account(
                    $i + 1, $roles[$i], $location, $firstNames, $lastNames, $puroks,
                    $password, $created
                );
            }

            $patients = [];
            for ($i = 0; $i < 88; $i++) {
                $number = $i + 13;
                $scenario = self::SCENARIOS[intdiv($i, 8)];
                $variant = $i % 8;
                $location = $i % 3;
                $email = sprintf('reprocare-test-%03d@example.test', $number);
                $existing = User::withTrashed()->where('email', $email)->exists();
                $patient = $this->account(
                    $number, 'user', $location, $firstNames, $lastNames, $puroks,
                    $password, $created, $scenario, $variant
                );
                $patients[] = $patient;

                if (!$existing) {
                    $this->patientScenario(
                        $patient, $scenario, $variant,
                        $staff['bhw'][$location], $staff['midwife'][$location],
                        $staff['bhw_president'][$location]
                    );
                }

                if (str_starts_with($scenario, 'postpartum')) {
                    $this->childScenario($patient, $staff['midwife'][$location]);
                }
            }

            $this->staffScenarios($staff);

            // These fixtures provide a visible forum thread and inbox/reply
            // without creating extra accounts or sending real messages.
            $author = $patients[8]; // approved, regular-cycle patient
            $post = ForumPost::firstOrCreate(
                ['user_id' => $author->id, 'content' => '[TEST] How do you track cycle changes?'],
                ['status' => 'active']
            );
            ForumComment::firstOrCreate(
                ['post_id' => $post->id, 'user_id' => $patients[16]->id],
                ['content' => '[TEST] I log the dates and compare each month.']
            );
            if (!ForumLike::where('post_id', $post->id)->where('user_id', $patients[17]->id)->exists()) {
                $like = new ForumLike(['post_id' => $post->id, 'user_id' => $patients[17]->id]);
                if (Schema::hasColumn('forum_likes', 'user_id_old__deprecated')) {
                    $like->setAttribute('user_id_old__deprecated', $patients[17]->id);
                }
                $like->save();
            }
            $inbox = $this->message(
                ['sender_id' => $author->id, 'receiver_id' => $staff['midwife'][8 % 3]->id,
                    'subject' => '[TEST] Appointment question'],
                ['body' => 'Fictional test message about scheduling.', 'is_read' => false, 'is_sent' => true]
            );
            $this->message(
                ['reply_to_id' => $inbox->id, 'sender_id' => $inbox->receiver_id, 'receiver_id' => $author->id],
                ['subject' => 'Re: [TEST] Appointment question', 'body' => 'Please check your schedule in the portal.',
                    'is_read' => false, 'is_sent' => true]
            );
        });

        $this->command?->info("Test scenario accounts: {$created} created; 100 available (12 staff, 88 patients).");
        $this->command?->line('Email: reprocare-test-001@example.test through reprocare-test-100@example.test');
        $this->command?->line('Shared password: TestAccount123!');
    }

    private function account(
        int $number, string $role, int $location, array $firstNames, array $lastNames,
        array $puroks, string $password, int &$created,
        ?string $scenario = null, int $variant = 0
    ): User {
        $email = sprintf('reprocare-test-%03d@example.test', $number);
        $existing = User::withTrashed()->where('email', $email)->first();
        if ($existing) {
            return $existing;
        }

        [$barangay, $rhu] = self::LOCATIONS[$location];
        $status = 'approved';
        if ($scenario === 'onboarding') {
            $status = match ($variant) {
                0, 1 => 'pending',
                2 => 'rejected',
                3 => 'suspended',
                default => 'approved',
            };
        }

        // 100 unique, deterministic name pairs from a shuffled index space.
        $nameIndex = ($number * 73 + 19) % 400;
        $first = $firstNames[intdiv($nameIndex, 20)];
        $last = $lastNames[$nameIndex % 20];
        $attributes = [
            'first_name' => $first,
            'middle_initial' => chr(65 + $number % 26),
            'last_name' => $last,
            'password' => $password,
            'role' => $role,
            'status' => $status,
            'is_profile_complete' => $scenario !== 'onboarding' || $variant !== 5,
            'date_of_birth' => today()->subYears($scenario === 'high_risk' ? 39 : 19 + $number % 21)->subDays($number % 300),
            'gender' => 'female',
            'contact_number' => $scenario === 'onboarding' && $variant === 5 ? null : sprintf('09%09d', 700000000 + $number),
            'address' => "House {$number}, Purok 1, {$barangay}, San Carlos City",
            'barangay' => $barangay,
            'purok_id' => $puroks[$location]->id,
            'rhu_assignment' => $rhu,
            'sms_opt_out' => $role === 'user' && $number % 7 === 0,
        ];
        if (in_array($role, ['bhw', 'bhw_president', 'midwife'], true)) {
            $attributes['assigned_barangay'] = $barangay;
        }
        if ($role === 'user' && $scenario !== 'onboarding') {
            $attributes['partner_name'] = 'Test Partner ' . $number;
        }

        $account = User::create(['email' => $email] + $attributes);
        // email_verified_at is guarded on User, so fill() would silently
        // discard it and leave every otherwise-ready patient unverified.
        if ($scenario !== 'onboarding' || $variant !== 4) {
            $account->forceFill(['email_verified_at' => now()])->save();
        }
        $created++;

        return $account;
    }

    private function patientScenario(User $patient, string $scenario, int $variant, User $bhw, User $midwife, User $president): void
    {
        if ($scenario === 'onboarding') {
            return;
        }

        EmergencyContact::create([
            'user_id' => $patient->id, 'name' => 'Test Partner ' . $patient->id,
            'relationship' => 'Partner', 'contact_number' => sprintf('09%09d', 800000000 + $patient->id),
            'contact_order' => 1, 'is_primary' => true,
        ]);

        if (in_array($scenario, ['regular_cycle', 'irregular_cycle'], true)) {
            $days = $scenario === 'regular_cycle' ? [28, 28, 28, 28] : [23, 36, 25, 40];
            $start = today()->subDays(array_sum($days) + 17);
            foreach ($days as $length) {
                Cycle::create([
                    'user_id' => $patient->id,
                    'period_start_date' => $start->toDateString(),
                    'period_end_date' => $start->copy()->addDays(3 + $variant % 3)->toDateString(),
                    'notes' => '[TEST] ' . $scenario,
                ]);
                $start->addDays($length);
            }
        }

        $pregnancy = null;
        $pregnant = in_array($scenario, [
            'early_pregnancy', 'second_trimester', 'high_risk', 'pregnancy_review',
            'pregnancy_revision', 'postpartum', 'postpartum_alert', 'referral',
        ], true);
        if ($pregnant) {
            $postpartum = str_starts_with($scenario, 'postpartum');
            $weeks = match ($scenario) {
                'early_pregnancy' => 8 + $variant % 4,
                'second_trimester', 'pregnancy_review', 'pregnancy_revision' => 19 + $variant % 4,
                'high_risk', 'referral' => 30 + $variant % 5,
                default => 43,
            };
            $delivered = today()->subDays(7 + $variant * 3);
            $pregnancy = Pregnancy::create([
                'user_id' => $patient->id,
                'lmp' => ($postpartum ? $delivered->copy()->subDays(270) : today()->subWeeks($weeks))->toDateString(),
                'gravida' => $variant % 3 + 1,
                'para' => $postpartum ? 1 : 0,
                'risk_level' => $scenario === 'high_risk' ? 'High' : 'Low',
                'risk_assessment_mode' => 'manual',
                'is_high_risk' => $scenario === 'high_risk',
                'risk_notes' => $scenario === 'high_risk' ? '[TEST] Elevated blood pressure; review needed.' : null,
                'notes' => '[TEST] ' . $scenario,
                'workflow_status' => match ($scenario) {
                    'pregnancy_review' => 'submitted_to_bhw_president',
                    'pregnancy_revision' => 'bhw_president_rejected',
                    'postpartum', 'postpartum_alert' => 'completed',
                    default => 'draft',
                },
                'submitted_to_bhw_president_at' => in_array($scenario, ['pregnancy_review', 'pregnancy_revision']) ? now()->subDays(2) : null,
                'rejected_by_id' => $scenario === 'pregnancy_revision' ? $president->id : null,
                'rejection_reason' => $scenario === 'pregnancy_revision' ? '[TEST] Please confirm the LMP.' : null,
                'rejected_at' => $scenario === 'pregnancy_revision' ? now()->subDay() : null,
                'revision_count' => $scenario === 'pregnancy_revision' ? 1 : 0,
                'ended_at' => $postpartum ? $delivered : null,
                'delivery_date' => $postpartum ? $delivered : null,
                'outcome' => $postpartum ? 'live_birth' : null,
            ]);

            MaternalCareTargetClient::create([
                'user_id' => $patient->id, 'pregnancy_id' => $pregnancy->id,
                'recorded_by_id' => $midwife->id, 'date_of_registration' => today()->subDays(12),
                'family_serial_no' => 'TEST-' . $patient->id,
                'gravida' => $variant % 3 + 1, 'parity' => $postpartum ? 1 : 0,
                'health_conditions' => $scenario === 'high_risk' ? ['pre_existing_hypertension'] : [],
            ]);

            if ($postpartum) {
                $alert = $scenario === 'postpartum_alert';
                $newborn = Newborn::create([
                    'mother_id' => $patient->id, 'pregnancy_id' => $pregnancy->id,
                    'name' => 'Baby of ' . $patient->first_name,
                    'sex' => $variant % 2 ? 'male' : 'female', 'birth_date' => $delivered,
                    'birth_weight_kg' => $alert ? 2.20 : 3.10,
                    'feeding_type' => $alert ? 'mixed' : 'exclusive_breast',
                    'danger_signs' => $alert ? '[TEST] Feeding difficulty' : null,
                    'recorded_by_id' => $bhw->id,
                ]);
                $newborn->seedImmunizationSchedule();
                if (!$alert) {
                    $newborn->immunizations()->where('vaccine', 'BCG')->update([
                        'status' => 'given', 'given_date' => $delivered->toDateString(), 'recorded_by_id' => $bhw->id,
                    ]);
                }
                PostpartumVisit::create([
                    'user_id' => $patient->id, 'pregnancy_id' => $pregnancy->id,
                    'newborn_id' => $newborn->id, 'visit_date' => $delivered->copy()->addDays(1),
                    'visit_week' => 1, 'bp' => $alert ? '150/95' : '118/76',
                    'temperature' => $alert ? 38.2 : 36.7,
                    'bleeding' => $alert ? 'heavy' : 'spotting',
                    'depression_score' => $alert ? 7 : 1,
                    'breastfeeding' => $alert ? 'partial' : 'exclusive',
                    'notes' => '[TEST] Postpartum follow-up', 'recorded_by_id' => $bhw->id,
                ]);
            }
        }

        $risk = $scenario === 'high_risk' || $scenario === 'postpartum_alert';
        $record = new HealthRecord([
            'user_id' => $patient->id, 'pregnancy_id' => $pregnancy?->id,
            'bp' => $risk ? '150/96' : '116/74', 'weight' => 48 + $variant * 2,
            'height' => 155, 'heart_rate' => $risk ? 104 : 78,
            'temperature' => $risk ? 38.1 : 36.7,
            'risk_level' => $risk ? 'High' : 'Low', 'risk_assessment_mode' => 'manual',
            'risk_notes' => $risk ? '[TEST] Clinical review required' : null,
            'notes' => '[TEST] ' . $scenario,
            'recorded_by_id' => $bhw->id,
            'workflow_status' => match ($variant % 4) {
                0 => 'recorded_by_bhw', 1 => 'submitted_to_bhw_president',
                2 => 'submitted_to_midwife', default => 'accepted_by_midwife',
            },
            'is_emergency' => $scenario === 'high_risk' && $variant === 0,
        ]);
        // Older SQLite installations still carry NOT NULL legacy columns
        // alongside the current user_id / recorded_by_id columns.
        if (Schema::hasColumn('health_records', 'user_id_old__deprecated')) {
            $record->setAttribute('user_id_old__deprecated', $patient->id);
            $record->setAttribute('recorded_by_id_old__deprecated', $bhw->id);
        }
        $record->save();

        $checkupStatus = ['Scheduled', 'Completed', 'Missed', 'Rescheduled', 'Cancelled'][$variant % 5];
        $checkup = Checkup::create([
            'user_id' => $patient->id, 'midwife_id' => $midwife->id,
            'scheduled_by_id' => $bhw->id, 'bhw_president_id' => $president->id,
            'scheduled_date' => in_array($checkupStatus, ['Scheduled', 'Rescheduled'])
                ? today()->addDays(4 + $variant) : today()->subDays(4 + $variant),
            'scheduled_time' => '09:00',
            'actual_date' => $checkupStatus === 'Completed' ? today()->subDays(4 + $variant) : null,
            'purpose' => '[TEST] ' . (str_starts_with($scenario, 'postpartum')
                ? 'Postpartum follow-up' : ($pregnancy ? 'Prenatal follow-up' : 'Health consultation')),
            'status' => $checkupStatus,
        ]);

        if (in_array($scenario, ['high_risk', 'referral'], true)) {
            $status = ['pending', 'reviewed', 'scheduled', 'declined'][$variant % 4];
            CheckupReferral::create([
                'user_id' => $patient->id, 'pregnancy_id' => $pregnancy?->id,
                'referred_by_bhw_id' => $bhw->id, 'assigned_midwife_id' => $midwife->id,
                'health_record_ids' => [$record->id],
                'converted_checkup_id' => $status === 'scheduled' ? $checkup->id : null,
                'reason' => '[TEST] Follow-up requested',
                'urgency' => $scenario === 'high_risk' ? ($variant === 0 ? 'emergency' : 'urgent') : 'routine',
                'status' => $status,
                'reviewed_at' => $status !== 'pending' ? now()->subDay() : null,
                'scheduled_at' => $status === 'scheduled' ? now() : null,
            ]);
        }

        Notification::create([
            'user_id' => $patient->id, 'title' => '[TEST] Follow-up',
            'message' => 'Fictional notification for ' . $scenario,
            'type' => $risk ? 'warning' : 'info',
            'is_read' => $variant % 2 === 0,
            'read_at' => $variant % 2 === 0 ? now() : null,
        ]);
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

    private function childScenario(User $mother, User $midwife): void
    {
        $pregnancy = Pregnancy::where('user_id', $mother->id)->where('notes', 'like', '[TEST]%')->first();
        $newborn = Newborn::where('mother_id', $mother->id)->first();
        if (!$pregnancy || !$newborn) {
            return;
        }

        $child = ChildRecord::firstOrCreate(
            ['mother_id' => $mother->id, 'pregnancy_id' => $pregnancy->id],
            [
                'first_name' => $newborn->name, 'last_name' => $mother->last_name,
                'date_of_birth' => $newborn->birth_date, 'gender' => $newborn->sex,
                'birth_weight' => $newborn->birth_weight_kg, 'birth_length' => 49,
                'purok_id' => $mother->purok_id, 'status' => 'active',
            ]
        );
        ChildCheckup::firstOrCreate(
            ['child_id' => $child->id, 'checkup_date' => $newborn->birth_date->copy()->addDay()->toDateString()],
            [
                'weight' => $newborn->birth_weight_kg, 'height' => 49,
                'notes' => '[TEST] Newborn growth check', 'conducted_by_id' => $midwife->id,
            ]
        );
    }

    private function staffScenarios(array $staff): void
    {
        foreach (range(0, 2) as $location) {
            $bhw = $staff['bhw'][$location];
            $president = $staff['bhw_president'][$location];
            $rhu = $staff['rhu'][$location === 2 ? 2 : 1];
            $state = ['pending', 'in_progress', 'completed'][$location];

            Task::firstOrCreate(
                ['title' => '[TEST] Follow-up ' . $bhw->email, 'assigned_to_id' => $bhw->id],
                [
                    'assigned_by_id' => $president->id, 'task_type' => 'follow_up',
                    'description' => 'Fictional household visit', 'status' => $state,
                    'due_date' => today()->addDays(($location - 1) * 5),
                    'completed_at' => $state === 'completed' ? now() : null,
                ]
            );

            SupplyRequest::firstOrCreate(
                ['requested_by_id' => $rhu->id, 'supply_name' => '[TEST] Prenatal kits ' . $location],
                [
                    'supply_category' => 'other', 'quantity_requested' => 10 + $location,
                    'unit' => 'kits', 'urgency' => $location === 2 ? 'urgent' : 'routine',
                    'reason' => 'Fictional inventory scenario',
                    'status' => ['submitted', 'approved', 'declined'][$location],
                    'submitted_at' => now()->subDays(3),
                    'approved_by_id' => $location === 1 ? $staff['cho'][0]->id : null,
                    'reviewed_at' => $location > 0 ? now()->subDay() : null,
                ]
            );

            BhwMonthlyReport::firstOrCreate(
                ['bhw_id' => $bhw->id, 'title' => '[TEST] Monthly report ' . $location],
                [
                    'report_type' => 'health_records', 'report_month' => today()->month,
                    'report_year' => today()->year, 'total_records' => 8,
                    'status' => 'draft',
                    'submission_status' => ['draft', 'submitted_to_president', 'submitted_to_midwife'][$location],
                    'submitted_to_president_by' => $location > 0 ? $bhw->id : null,
                    'submitted_to_president_at' => $location > 0 ? now()->subDays(2) : null,
                    'approved_by_president' => $location === 2 ? $president->id : null,
                    'approved_by_president_at' => $location === 2 ? now()->subDay() : null,
                    'submitted_to_midwife_by' => $location === 2 ? $president->id : null,
                    'submitted_to_midwife_at' => $location === 2 ? now() : null,
                ]
            );
        }
    }
}
