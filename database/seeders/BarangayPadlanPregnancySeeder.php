<?php

namespace Database\Seeders;

use App\Models\MaternalCareTargetClient;
use App\Models\Pregnancy;
use App\Models\Purok;
use App\Models\User;
use App\Services\MaternalRiskService;
use App\Services\WorkflowService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * City Population Office "List of Pregnant Women" — Barangay Burgos Padlan.
 *
 * Loads the 8 paper records through the application's OWN domain flows
 * (Eloquent models, risk assessment, delivery workflow) — never raw SQL:
 *
 * - Women with future EDCs enter as ACTIVE pregnancies. Their paper LMPs are
 *   stale snapshots, so LMP is back-computed from TODAY to preserve each
 *   woman's gestational stage (early cases look newly pregnant, late cases
 *   look close to giving birth). EDD/AOG derive automatically (LMP + 280d).
 * - Women whose paper EDC already passed (Sendie, Jasmeni, Ashley,
 *   Kristine) enter as DELIVERED cases: pregnancy created with the paper LMP
 *   and then closed via WorkflowService::handleDeliveryOutcome() with the
 *   paper EDC as delivery date (postpartum schedule auto-generated).
 *
 * Woman accounts mirror staff-registered (BHW) enrollments: approved and
 * email-verified (verified in person), random unusable password, placeholder
 * @reprocare.local email (the paper has no emails/contact numbers). If a
 * woman later self-registers with her real email, RHU links the duplicate
 * through the existing pending-patients queue.
 *
 * IDEMPOTENT: re-running skips women/pregnancies that already exist.
 *
 * PRODUCTION RUN (no Render shell on free plan — run from your machine):
 *   1. Back up first: Render Dashboard → Databases → reprocare-db → Backups.
 *   2. composer dump-autoload
 *   3. Back up your local .env, then point it at production:
 *        DB_CONNECTION=pgsql
 *        DB_HOST=<render-external-host>
 *        DB_PORT=5432
 *        DB_DATABASE=reprocare_db
 *        DB_USERNAME=<user>
 *        DB_PASSWORD=<password>
 *      (values come from the Render DB's "External Database URL").
 *   4. php artisan db:seed --class=Database\\Seeders\\BarangayPadlanPregnancySeeder
 *   5. Restore your local .env afterwards.
 */
class BarangayPadlanPregnancySeeder extends Seeder
{
    /**
     * @return array<int, array{first: string, mi: ?string, last: string, dob: string, mode: string, aog_weeks: ?int, lmp: ?string, edc: ?string, gravida: int, para: int, weight: ?float}>
     */
    protected function records(): array
    {
        return [
            // ── Active: LMP back-computed from today to preserve stage ──
            ['first' => 'Danna', 'mi' => 'A', 'last' => 'Resuello', 'dob' => '2010-09-13',
                'mode' => 'active', 'aog_weeks' => 15, 'lmp' => null, 'edc' => null,
                'gravida' => 1, 'para' => 0, 'weight' => 52],
            ['first' => 'Dianna', 'mi' => null, 'last' => 'Felix', 'dob' => '1994-09-16',
                'mode' => 'active', 'aog_weeks' => 19, 'lmp' => null, 'edc' => null,
                'gravida' => 4, 'para' => 3, 'weight' => 61],
            ['first' => 'Alexandra', 'mi' => null, 'last' => 'Valdez', 'dob' => '1995-07-16',
                'mode' => 'active', 'aog_weeks' => 28, 'lmp' => null, 'edc' => null,
                'gravida' => 2, 'para' => 1, 'weight' => 78],
            ['first' => 'Joy', 'mi' => 'C', 'last' => 'de Cano', 'dob' => '1990-09-17',
                'mode' => 'active', 'aog_weeks' => 16, 'lmp' => null, 'edc' => null,
                'gravida' => 4, 'para' => 3, 'weight' => null],
            // ── Delivered: paper dates kept, closed via delivery workflow ──
            ['first' => 'Sendie', 'mi' => null, 'last' => 'Laurea', 'dob' => '1982-03-31',
                'mode' => 'delivered', 'aog_weeks' => null, 'lmp' => '2025-12-15', 'edc' => '2026-09-20',
                'gravida' => 10, 'para' => 9, 'weight' => 50],
            ['first' => 'Jasmeni', 'mi' => null, 'last' => 'Diaz', 'dob' => '1997-03-01',
                'mode' => 'delivered', 'aog_weeks' => null, 'lmp' => '2025-11-20', 'edc' => '2026-08-28',
                'gravida' => 3, 'para' => 2, 'weight' => 80],
            ['first' => 'Ashley', 'mi' => null, 'last' => 'Reyes', 'dob' => '2009-04-14',
                'mode' => 'delivered', 'aog_weeks' => null, 'lmp' => '2025-11-02', 'edc' => '2026-08-09',
                'gravida' => 1, 'para' => 0, 'weight' => 51],
            ['first' => 'Kristine', 'mi' => null, 'last' => 'Quaresma', 'dob' => '2006-01-14',
                'mode' => 'delivered', 'aog_weeks' => null, 'lmp' => '2025-10-24', 'edc' => '2026-07-31',
                'gravida' => 1, 'para' => 0, 'weight' => 53],
        ];
    }

    public function run(): void
    {
        $created = $skipped = $delivered = 0;
        $errors = [];

        foreach ($this->records() as $row) {
            try {
                $result = $row['mode'] === 'delivered'
                    ? $this->seedDelivered($row)
                    : $this->seedActive($row);

                if ($result === 'skipped') {
                    $skipped++;
                    $this->command->warn("SKIP  {$row['first']} {$row['last']} (already in system)");
                } else {
                    $row['mode'] === 'delivered' ? $delivered++ : $created++;
                    $this->command->info(($row['mode'] === 'delivered' ? 'DELIVERED ' : 'ACTIVE    ')
                        . "{$row['first']} {$row['last']} — {$result}");
                }
            } catch (\Throwable $e) {
                $errors[] = "{$row['first']} {$row['last']}: {$e->getMessage()}";
                $this->command->error("FAIL  {$row['first']} {$row['last']}: {$e->getMessage()}");
            }
        }

        $this->command->info("Done: {$created} active, {$delivered} delivered, {$skipped} skipped, " . count($errors) . ' errors.');
        if ($errors) {
            $this->command->error('Errors (re-run is safe — completed rows are skipped):');
            foreach ($errors as $error) {
                $this->command->error(' - ' . $error);
            }
        }
    }

    protected function findWoman(array $row): ?User
    {
        return User::where('role', 'user')
            ->where('first_name', $row['first'])
            ->where('last_name', $row['last'])
            ->whereDate('date_of_birth', $row['dob'])
            ->first();
    }

    protected function placeholderEmail(array $row): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $row['first'] . '.' . $row['last']));

        return $slug . '@reprocare.local';
    }

    /**
     * Woman account exactly like a BHW staff enrollment: approved + verified
     * in person, no approval queue (mirrors BhwController::storeWoman).
     */
    protected function seedWoman(array $row): User
    {
        $existing = $this->findWoman($row)
            ?? User::where('email', $this->placeholderEmail($row))->first();
        if ($existing) {
            return $existing;
        }

        $barangay = 'Burgos St';

        return User::create([
            'first_name' => $row['first'],
            'middle_initial' => $row['mi'],
            'last_name' => $row['last'],
            'date_of_birth' => $row['dob'],
            'gender' => 'female',
            'contact_number' => null,
            'address' => $barangay . ', San Carlos City, Pangasinan',
            'barangay' => $barangay,
            'purok_id' => Purok::resolveIdFromText(null, $barangay),
            'email' => $this->placeholderEmail($row),
            'password' => Str::random(40),
            'role' => 'user',
            'status' => 'approved',
            'email_verified_at' => now(),
            'is_profile_complete' => false,
        ]);
    }

    protected function assessRisk(User $woman, array $row): array
    {
        return app(MaternalRiskService::class)->assess([
            'age' => $woman->age,
            'blood_pressure' => null,
            'weight' => $row['weight'],
            'height' => null,
            'smoking_status' => null,
            'alcohol_status' => null,
            'drug_use_status' => null,
            'obstetric_history' => "G{$row['gravida']}P{$row['para']}",
        ]);
    }

    protected function seedMaternalTarget(User $woman, Pregnancy $pregnancy, array $row): void
    {
        MaternalCareTargetClient::updateOrCreate(
            ['user_id' => $woman->id, 'pregnancy_id' => $pregnancy->id],
            ['gravida' => $row['gravida'], 'parity' => $row['para']]
        );
    }

    protected function seedActive(array $row): string
    {
        $woman = $this->seedWoman($row);

        if ($woman->pregnancies()->active()->exists()) {
            return 'skipped';
        }

        // Preserve the paper gestational stage relative to TODAY so the
        // record reads as a genuinely new (or near-term) pregnancy.
        $lmp = Carbon::today()->subWeeks($row['aog_weeks'])->toDateString();
        $assessment = $this->assessRisk($woman, $row);

        $pregnancy = Pregnancy::create([
            'user_id' => $woman->id,
            'lmp' => $lmp, // EDD auto-derives (LMP + 280 days) via the model mutator.
            'gravida' => $row['gravida'],
            'para' => $row['para'],
            'risk_assessment_mode' => 'automatic',
            'risk_level' => $assessment['risk_level'],
            'risk_notes' => $assessment['reasons'] ? implode(' ', $assessment['reasons']) : null,
            'is_high_risk' => $assessment['is_high_risk'],
            'workflow_status' => 'draft',
            'notes' => 'Seeded from City Population Office paper list (Barangay Burgos Padlan).',
        ]);

        $this->seedMaternalTarget($woman, $pregnancy, $row);

        return "LMP {$lmp}, EDD {$pregnancy->edd->format('Y-m-d')}, risk {$assessment['risk_level']}";
    }

    protected function seedDelivered(array $row): string
    {
        $woman = $this->seedWoman($row);

        $existing = $woman->pregnancies()->whereDate('lmp', $row['lmp'])->first();
        if ($existing && $existing->ended_at) {
            return 'skipped';
        }

        $assessment = $this->assessRisk($woman, $row);

        $pregnancy = $existing ?? Pregnancy::create([
            'user_id' => $woman->id,
            'lmp' => $row['lmp'], // Paper dates kept; EDD auto-derives.
            'gravida' => $row['gravida'],
            'para' => $row['para'],
            'risk_assessment_mode' => 'automatic',
            'risk_level' => $assessment['risk_level'],
            'risk_notes' => $assessment['reasons'] ? implode(' ', $assessment['reasons']) : null,
            'is_high_risk' => $assessment['is_high_risk'],
            'workflow_status' => 'draft',
            'notes' => 'Seeded from City Population Office paper list (Barangay Burgos Padlan).',
        ]);

        $this->seedMaternalTarget($woman, $pregnancy, $row);

        // Close through the real delivery workflow: outcome, MCTC outcome
        // flags, and the postpartum visit schedule are all generated —
        // identical to logging the delivery in the UI.
        app(WorkflowService::class)->handleDeliveryOutcome($pregnancy, [
            'delivery_date' => $row['edc'],
            'outcome' => 'delivered',
            'delivery_notes' => 'Seeded delivery from City Population Office paper list.',
        ]);

        return "delivered {$row['edc']} (LMP {$row['lmp']})";
    }
}
