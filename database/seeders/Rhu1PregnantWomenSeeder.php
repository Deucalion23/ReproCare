<?php

namespace Database\Seeders;

use App\Models\Pregnancy;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds 13 pregnant women (with partners) from RHU 1 barangays of
 * San Carlos City with realistic Philippine details.
 *
 * Fully idempotent (updateOrCreate / firstOrNew) so it is safe to run on
 * every production boot via DatabaseSeeder. Uses NO faker (unavailable in
 * production builds) — every value below is curated.
 */
class Rhu1PregnantWomenSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        $women = [
            // first, middle, last, partner, dob, contact, partner contact, barangay, purok#, house#, lmp
            ['Ma. Alison', null, 'Valdez', 'Nelson Valdez', '1994-05-18', '09175510234', '09175510235', 'Bonifacio St', 1, 12, '2026-01-12'],
            ['Cristina', null, 'Austria', 'Jefferson Austria', '1991-09-03', '09204561128', '09204561129', 'Burgos St', 2, 7, '2026-02-02'],
            ['Vicky', null, 'Romero', 'Edgardo Romero', '1998-12-25', '09395541007', '09395541008', 'Cacaritan', 1, 23, '2026-02-20'],
            ['Emily', null, 'Gutierez', 'Erickson Gutierez', '1996-04-14', '09475528931', '09475528932', 'Calomboyan', 3, 5, '2026-03-05'],
            ['Teresa', null, 'Gutierez', 'Edmund Gutierez', '2001-07-30', '09615537842', '09615537843', 'Capataan', 1, 9, '2026-03-22'],
            ['Joanna', null, 'Cancino', 'Fil Cancino', '1989-11-11', '09755504219', '09755504220', 'Lucban St', 2, 14, '2026-04-06'],
            ['Margarita', null, 'Ferrer', 'Chestney Ferrer', '1993-02-27', '09085517364', '09085517365', 'Mamarlao', 1, 3, '2026-04-25'],
            ['Mackie', null, 'Austria', 'Chester Austria', '2003-06-19', '09195448026', '09195448027', 'Naguilayan', 4, 11, '2026-05-11'],
            ['Daniele', null, 'Abad', 'Jordan Abad', '1999-10-08', '09275563917', '09275563918', 'Pagal', 1, 6, '2026-06-01'],
            ['Lian', null, 'Aquino', 'Duke Ferrer', '2005-01-17', '09365572490', '09365572491', 'Palaming', 2, 19, '2026-06-20'],
            ['Loreta', null, 'Tuazon', 'Kevin Tuazon', '1988-08-22', '09505589133', '09505589134', 'Pangalangan', 1, 8, '2026-07-08'],
            ['Alexandra', null, 'Fernandez', 'David Fernandez', '1997-03-30', '09095526078', '09095526079', 'Pangpang', 3, 15, '2026-08-03'],
            ['Gladis', null, 'Fernandez', 'Harvey Fernandez', '2002-12-05', '09915594752', '09915594753', 'Quintong', 1, 10, '2026-08-25'],
        ];

        foreach ($women as [$first, $middle, $last, $partner, $dob, $contact, $partnerContact, $barangay, $purokNo, $houseNo, $lmp]) {
            // Lowercase FIRST so capitals survive (Ma. Alison Valdez →
            // maalisonvaldez@gmail.com, not aalisonaldez@gmail.com).
            $email = preg_replace('/[^a-z0-9]/', '', strtolower($first . $last)) . '@gmail.com';

            $purok = Purok::firstOrCreate(
                ['name' => 'Purok ' . $purokNo, 'barangay' => $barangay],
                ['description' => 'RHU 1 demonstration address.']
            );

            // seedAccount: never overwrite patient edits on reseed.
            $user = User::seedAccount(
                ['email' => $email],
                [
                    'first_name' => $first,
                    'middle_initial' => $middle,
                    'last_name' => $last,
                    'email' => $email,
                    'password' => $password,
                    'date_of_birth' => $dob,
                    'gender' => 'female',
                    'contact_number' => $contact,
                    'address' => "House {$houseNo}, Purok {$purokNo}, {$barangay}, San Carlos City, Pangasinan",
                    'barangay' => $barangay,
                    'purok_id' => $purok->id,
                    'role' => 'user',
                    'status' => 'approved',
                    'partner_name' => $partner,
                    'partner_contact' => $partnerContact,
                ]
            );

            $pregnancy = Pregnancy::firstOrNew([
                'user_id' => $user->id,
                'ended_at' => null,
            ]);
            $pregnancy->walk_in_patient_id = null;
            $pregnancy->lmp = $lmp;
            $pregnancy->risk_level = 'Low';
            $pregnancy->risk_assessment_mode = 'manual';
            $pregnancy->risk_notes = 'Sample pregnancy record';
            $pregnancy->is_high_risk = false;
            $pregnancy->notes = 'Generated sample data';
            $pregnancy->workflow_status = 'draft';
            $pregnancy->save();
        }

        $this->command?->info('Seeded 13 RHU 1 pregnant women with partners (shared password: password123).');
    }
}
