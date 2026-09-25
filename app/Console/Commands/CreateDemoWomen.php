<?php

namespace App\Console\Commands;

use App\Models\Barangay;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateDemoWomen extends Command
{
    protected $signature = 'demo:create-women {--apply : Create ten demo women per active configured barangay}';
    protected $description = 'Preview or create clearly labelled demo patient accounts, without notifications or clinical records.';

    public function handle(): int
    {
        if (is_file(storage_path('app/private/demo-accounts/custom-population.json'))) {
            $this->warn('The sample population was customized. Automatic ten-per-barangay creation is disabled to preserve that allocation.');
            return self::SUCCESS;
        }
        $barangays = Barangay::active()->orderBy('id')->get();
        if ($barangays->isEmpty()) { $this->error('No active barangays are configured.'); return self::FAILURE; }
        $pending = [];
        foreach ($barangays as $barangay) {
            for ($i = 1; $i <= 10; $i++) {
                $email = sprintf('demo.b%d.w%02d@example.test', $barangay->id, $i);
                $existing = User::withTrashed()->where(function ($q) use ($email, $barangay, $i) {
                    $q->where('email', $email)->orWhere('email', 'like', sprintf('%%.b%d.w%02d@example.test', $barangay->id, $i));
                })->first();
                if ($existing) {
                    if ($existing->role !== 'user' || $existing->barangay !== $barangay->name || $existing->trashed() || $existing->archived_at) {
                        $this->error("Demo slot collision for barangay {$barangay->id}, slot {$i}. No accounts created.");
                        return self::FAILURE;
                    }
                    continue;
                }
                $pending[] = [$barangay, $email];
            }
        }
        $this->info($barangays->count().' active barangays; '.count($pending).' new demo accounts needed (10 per barangay).');
        if (!$this->option('apply')) { $this->line('Preview only. Add --apply to create accounts.'); return self::SUCCESS; }
        if (!$pending) { $this->info('All demo slots already exist; existing credentials were preserved.'); return self::SUCCESS; }
        $directory = storage_path('app/private/demo-accounts');
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new \RuntimeException('Could not create the private credentials directory.');
        }
        $path = $directory.'/women-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(3)).'.csv';
        $firstNames = ['Maria', 'Ana', 'Lucia', 'Elena', 'Rosa', 'Clara', 'Isabel', 'Carmen', 'Sofia', 'Teresa', 'Mila', 'Julia'];
        $lastNames = ['Santos', 'Reyes', 'Cruz', 'Garcia', 'Ramos', 'Flores', 'Mendoza', 'Torres', 'Rivera', 'Castro'];
        DB::transaction(function () use ($pending, $path, $firstNames, $lastNames) {
            $rows = [];
            foreach ($pending as [$barangay, $email]) {
                $purok = Purok::where('barangay', $barangay->name)->orderBy('id')->first();
                if (!$purok) {
                    $purok = Purok::withoutEvents(fn () => Purok::create(['name' => 'Demo Purok (synthetic)', 'barangay' => $barangay->name]));
                }
                $password = 'Demo!'.bin2hex(random_bytes(8)).'A7';
                $attributes = [
                    'first_name' => 'Demo '.$firstNames[random_int(0, count($firstNames) - 1)],
                    'middle_initial' => chr(random_int(65, 90)),
                    'last_name' => $lastNames[random_int(0, count($lastNames) - 1)],
                    'email' => $email, 'password' => $password,
                    'date_of_birth' => today()->subYears(random_int(19, 44))->subDays(random_int(0, 364))->toDateString(),
                    'gender' => 'female', 'contact_number' => null,
                    'address' => 'DEMO ONLY, '.$purok->name.', '.$barangay->name.', San Carlos City, Pangasinan',
                    'barangay' => $barangay->name, 'purok_id' => $purok->id,
                    'role' => 'user', 'status' => 'approved', 'sms_opt_out' => true,
                ];
                // Match required fields in RHU staff registration; phone is optional there.
                Validator::make($attributes + ['purok' => $purok->name, 'password_confirmation' => $password], [
                    'first_name' => 'required|string|max:255', 'middle_initial' => 'nullable|string|max:2',
                    'last_name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users',
                    'password' => 'required|string|min:8|confirmed', 'date_of_birth' => 'required|date|before:today',
                    'barangay' => 'required|string|max:255', 'purok' => 'required|string|max:100',
                    'address' => 'nullable|string|max:255', 'contact_number' => 'nullable|string|max:20',
                    'gender' => 'required|in:female',
                ])->validate();
                $attributes['password'] = Hash::make($password);
                $user = User::withoutEvents(fn () => User::create($attributes));
                $rows[] = [$user->id, $user->first_name, $user->last_name, $barangay->name, $purok->name, $email, $password, 'approved', 'Synthetic demo; no contact number; SMS opted out'];
            }
            $file = fopen($path, 'x');
            if (!$file) throw new \RuntimeException('Could not save the credentials export.');
            try {
                if (fputcsv($file, ['id', 'first_name', 'last_name', 'barangay', 'purok', 'email', 'password', 'status', 'notes']) === false) throw new \RuntimeException('Export failed.');
                foreach ($rows as $row) {
                    if (fputcsv($file, $row) === false) throw new \RuntimeException('Export failed.');
                }
            } finally { fclose($file); }
        });
        $this->info('Created '.count($pending).' approved demo patient accounts. No messages or clinical records were created.');
        $this->line('Private credentials CSV: '.$path);
        return self::SUCCESS;
    }
}
