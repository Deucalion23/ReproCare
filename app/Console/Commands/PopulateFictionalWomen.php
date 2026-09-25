<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PopulateFictionalWomen extends Command
{
    protected $signature = 'demo:populate-women {--apply}';
    protected $description = 'Add fictional pregnancy scenarios only to the generated example.test patient accounts.';

    public function handle(): int
    {
        if (is_file(storage_path('app/private/demo-accounts/custom-population.json'))) {
            $this->warn('The sample population was customized. Automatic repopulation is disabled to preserve existing records.');
            return self::SUCCESS;
        }
        $users = User::where('role', 'user')->where('email', 'like', '%.b%.w%@example.test')->orderBy('id')->get()
            ->filter(fn ($u) => preg_match('/^(?:demo|[a-z]+\.[a-z]+)\.b\d+\.w\d{2}@example\.test$/', $u->email));
        $plans = [];
        $tag = 'FICTIONAL-SEED-v1';
        foreach ($users as $user) {
            if (DB::table('pregnancies')->where('user_id', $user->id)->exists()) continue;
            preg_match('/\.b(\d+)\.w(\d+)@example\.test$/', $user->email, $match);
            $area = (int) $match[1]; $slot = (int) $match[2];
            $closed = $slot >= 9;
            $ageDays = $closed ? random_int(290, 380) : random_int(84, 252);
            $lmp = today()->subDays($ageDays);
            $registered = $lmp->copy()->addDays(random_int(42, 70));
            $delivery = $closed ? $lmp->copy()->addDays(280) : null;
            $latest = $closed ? $delivery->copy()->subDays(7) : today()->subDays(random_int(1, 6));
            $risk = !$closed && $area % 3 === 0 && $slot <= 2 ? 'High'
                : (!$closed && $area % 3 !== 1 && $slot <= 4 ? 'Medium' : 'Low');
            $gravida = random_int(1, 3); $para = $gravida - 1;
            $pregnancy = [
                'user_id' => $user->id, 'lmp' => $lmp->toDateString(), 'edd' => $lmp->copy()->addDays(280)->toDateString(),
                'aog' => (int) floor(($closed ? 280 : $ageDays) / 7), 'gravida' => $gravida, 'para' => $para,
                'risk_level' => $risk, 'risk_assessment_mode' => 'manual', 'is_high_risk' => $risk === 'High',
                'risk_notes' => "$tag: Assigned fictional $risk scenario for interface testing; not a clinical assessment.",
                'notes' => "$tag: Entire history is synthetic. Not an actual patient or clinical encounter.",
                'workflow_status' => 'draft', 'created_at' => $registered->toDateTimeString(), 'updated_at' => now(),
                'delivery_date' => $delivery?->toDateString(), 'ended_at' => $delivery?->toDateTimeString(),
                'outcome' => $closed ? 'live_birth' : null, 'is_locked' => $closed,
            ];
            Validator::make($pregnancy, ['user_id' => 'required|exists:users,id', 'lmp' => 'required|date|before:today',
                'edd' => 'required|date|after:lmp', 'gravida' => 'required|integer|min:1', 'para' => 'required|integer|min:0',
                'risk_level' => 'required|in:Low,Medium,High'])->validate();
            $plans[] = compact('user', 'pregnancy', 'registered', 'latest', 'delivery', 'lmp', 'risk', 'gravida', 'para', 'slot', 'closed');
        }
        $this->info(count($plans).' accounts eligible; existing pregnancy records are preserved.');
        if (!$this->option('apply')) { $this->line('Preview validated. Add --apply to save.'); return self::SUCCESS; }
        $result = DB::transaction(function () use ($plans, $tag) {
            $counts = ['profiles' => 0, 'open_pregnancies' => 0, 'completed_pregnancies' => 0, 'health_records' => 0, 'checkups' => 0];
            foreach ($plans as $plan) {
                extract($plan);
                $id = DB::table('pregnancies')->insertGetId($pregnancy);
                $counts[$closed ? 'completed_pregnancies' : 'open_pregnancies']++;
                // Query builder intentionally bypasses notification/risk observers for synthetic fixtures.
                DB::table('users')->where('id', $user->id)->update([
                    'partner_name' => ['Miguel', 'Rafael', 'Gabriel', 'Daniel', 'Paolo'][$slot % 5].' '.$user->last_name,
                    'sms_opt_out' => true, 'contact_number' => null, 'partner_contact' => null,
                    'updated_at' => now(),
                ]);
                $counts['profiles']++;
                DB::table('maternal_care_target_clients')->insert([
                    'user_id' => $user->id, 'pregnancy_id' => $id, 'date_of_registration' => $registered->toDateString(),
                    'family_serial_no' => 'FICTIONAL-'.$user->id, 'gravida' => $gravida, 'parity' => $para,
                    'gtpal_term' => $para, 'gtpal_preterm' => 0, 'gtpal_abortions' => 0, 'gtpal_living_children' => $para,
                    'pregnancy_terminated_date' => $delivery?->toDateString(),
                    'delivery_date' => $delivery?->toDateString(),
                    'created_at' => $registered->toDateTimeString(), 'updated_at' => now(),
                ]);
                $height = random_int(150, 170); $weight = random_int(50, 72);
                foreach ([$registered, $latest] as $visitIndex => $visit) {
                    DB::table('health_records')->insert([
                        'user_id' => $user->id, 'pregnancy_id' => $id,
                        'bp' => ['110/70', '112/72', '118/76', '116/74'][$slot % 4],
                        'height' => $height, 'weight' => $weight + $visitIndex * 3,
                        'bmi' => round(($weight + $visitIndex * 3) / (($height / 100) ** 2), 2),
                        'heart_rate' => random_int(72, 90), 'temperature' => 36.7,
                        'gestational_age' => (int) floor($lmp->diffInDays($visit) / 7),
                        'risk_level' => $risk, 'risk_assessment_mode' => 'manual', 'is_emergency' => false,
                        'risk_notes' => "$tag: Simulated $risk classification; not inferred from these sample vital signs.",
                        'notes' => "$tag: Fictional prenatal visit for software testing.",
                        'workflow_status' => 'draft', 'created_at' => $visit->toDateTimeString(), 'updated_at' => now(),
                    ]);
                    DB::table('checkups')->insert([
                        'user_id' => $user->id, 'pregnancy_id' => $id, 'scheduled_date' => $visit->toDateString(),
                        'scheduled_time' => '09:00:00', 'actual_date' => $visit->toDateString(),
                        'purpose' => 'Prenatal review (fictional)', 'status' => 'Completed', 'notes' => $tag,
                        'created_at' => $registered->toDateTimeString(), 'updated_at' => now(),
                    ]);
                    $counts['health_records']++; $counts['checkups']++;
                }
                if (!$closed) {
                    $missed = $slot % 4 === 0;
                    DB::table('checkups')->insert([
                        'user_id' => $user->id, 'pregnancy_id' => $id,
                        'scheduled_date' => ($missed ? today()->subDay() : today()->addDays(random_int(3, 14)))->toDateString(),
                        'scheduled_time' => '10:00:00', 'purpose' => 'Follow-up review (fictional)',
                        'status' => $missed ? 'Missed' : 'Scheduled', 'notes' => $tag,
                        'created_at' => $latest->toDateTimeString(), 'updated_at' => now(),
                    ]);
                    $counts['checkups']++;
                }
            }
            return $counts;
        });
        $this->info(json_encode($result));
        $this->line('Synthetic records are labelled FICTIONAL-SEED-v1. Credentials unchanged; no messages sent.');
        return self::SUCCESS;
    }
}
