<?php

namespace Database\Seeders;

use App\Models\MaternalCareTargetClient;
use App\Models\Pregnancy;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HighRiskPregnancySeeder extends Seeder
{
    /** Create a complete fictional high-risk pregnancy for demonstrations. */
    public function run(): void
    {
        $purok = Purok::firstOrCreate(
            ['name' => 'Purok 1', 'barangay' => 'Cacaritan'],
            ['description' => 'RHU 1 local demonstration address.']
        );

        $woman = User::updateOrCreate(
            ['email' => 'elena.santos.highrisk@example.test'],
            [
                'first_name' => 'Elena',
                'middle_initial' => 'R',
                'last_name' => 'Santos',
                'password' => Hash::make('password123'),
                'date_of_birth' => '1986-02-18',
                'gender' => 'female',
                'contact_number' => '09178945621',
                'address' => 'Purok 1, Cacaritan, San Carlos City, Pangasinan',
                'barangay' => 'Cacaritan',
                'purok_id' => $purok->id,
                'rhu_assignment' => 'RHU 1',
                'partner_name' => 'Roberto Santos',
                'partner_contact' => '09181234567',
                'role' => 'user',
                'status' => 'approved',
            ]
        );

        $riskFactors = 'High-risk factors: maternal age 40; pre-existing hypertension with prenatal BP 150/96; history of preeclampsia and prior cesarean birth. Requires close prenatal monitoring and facility-based delivery planning.';

        $pregnancy = Pregnancy::firstOrNew([
            'user_id' => $woman->id,
            'ended_at' => null,
        ]);

        $pregnancy->walk_in_patient_id = null;
        $pregnancy->lmp = '2026-03-20';
        $pregnancy->gravida = 3;
        $pregnancy->para = 1;
        $pregnancy->risk_level = 'High';
        $pregnancy->risk_assessment_mode = 'manual';
        $pregnancy->risk_notes = $riskFactors;
        $pregnancy->is_high_risk = true;
        $pregnancy->notes = 'Fictional high-risk patient record for system demonstration only.';
        $pregnancy->workflow_status = 'draft';
        $pregnancy->save();

        MaternalCareTargetClient::updateOrCreate(
            ['pregnancy_id' => $pregnancy->id],
            [
                'user_id' => $woman->id,
                'date_of_registration' => '2026-09-26',
                'family_serial_no' => 'RHU1-DEMO-002',
                'gravida' => 3,
                'parity' => 1,
                'gtpal_term' => 1,
                'gtpal_preterm' => 0,
                'gtpal_abortions' => 1,
                'gtpal_living_children' => 1,
                'health_conditions' => ['pre_existing_hypertension'],
                'health_condition_other' => 'Previous preeclampsia; prior cesarean delivery; prenatal BP 150/96.',
                'nutritional_assessment_status' => 'high',
            ]
        );
    }
}
