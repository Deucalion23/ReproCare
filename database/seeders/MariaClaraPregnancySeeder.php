<?php

namespace Database\Seeders;

use App\Models\Pregnancy;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MariaClaraPregnancySeeder extends Seeder
{
    /** Create a repeatable RHU 1 pregnancy record for local demonstrations. */
    public function run(): void
    {
        $purok = Purok::firstOrCreate(
            ['name' => 'Purok 1', 'barangay' => 'Bonifacio St'],
            ['description' => 'RHU 1 local demonstration address.']
        );

        $woman = User::updateOrCreate(
            ['email' => 'maria.clara@example.test'],
            [
                'first_name' => 'Maria',
                'middle_initial' => null,
                'last_name' => 'Clara',
                'password' => Hash::make('password123'),
                'date_of_birth' => '1997-06-12',
                'gender' => 'female',
                'contact_number' => '09567739968',
                'address' => 'Purok 1, Bonifacio St, San Carlos City, Pangasinan',
                'barangay' => 'Bonifacio St',
                'purok_id' => $purok->id,
                'rhu_assignment' => 'RHU 1',
                'role' => 'user',
                'status' => 'approved',
            ]
        );

        $pregnancy = Pregnancy::firstOrNew([
            'user_id' => $woman->id,
            'ended_at' => null,
        ]);

        $pregnancy->walk_in_patient_id = null;
        $pregnancy->lmp = '2026-05-15';
        $pregnancy->gravida = 1;
        $pregnancy->para = 0;
        $pregnancy->risk_level = 'Low';
        $pregnancy->risk_assessment_mode = 'manual';
        $pregnancy->risk_notes = 'Initial low-risk assessment created for local testing.';
        $pregnancy->is_high_risk = false;
        $pregnancy->notes = 'Local demonstration patient record.';
        $pregnancy->workflow_status = 'draft';
        $pregnancy->save();
    }
}
