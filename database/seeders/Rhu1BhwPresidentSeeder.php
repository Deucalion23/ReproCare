<?php

namespace Database\Seeders;

use App\Models\Purok;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds one BHW President per RHU 1 barangay (except Burgos St, whose
 * president is the demo pres@gmail.com account) with realistic details.
 *
 * Fully idempotent (updateOrCreate) so it is safe to run on every production
 * boot via DatabaseSeeder. Uses NO faker (unavailable in production builds).
 * Shared password is password123, same as all other demo accounts.
 */
class Rhu1BhwPresidentSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        $presidents = [
            // first, middle, last, dob, contact, barangay
            ['Corazon', null, 'Reyes', '1974-05-20', '09137749256', 'Bonifacio St'],
            ['Adela', null, 'Cruz', '1971-08-14', '09228850367', 'Cacaritan'],
            ['Benita', null, 'Santos', '1969-12-01', '09339961478', 'Calomboyan'],
            ['Consuelo', null, 'Dela Cruz', '1973-03-27', '09440072589', 'Capataan'],
            ['Damiana', null, 'Flores', '1970-06-11', '09551183690', 'Lucban St'],
            ['Esperanza', null, 'Gomez', '1975-10-22', '09662294701', 'Mamarlao'],
            ['Felisa', null, 'Navarro', '1972-01-05', '09773305812', 'Naguilayan'],
            ['Gloria', null, 'Ortiz', '1978-07-19', '09084416923', 'Pagal'],
            ['Hermana', null, 'Paz', '1976-11-30', '09195527034', 'Palaming'],
            ['Imelda', null, 'Ramos', '1974-02-14', '09206638145', 'Pangalangan'],
            ['Juliana', null, 'Torres', '1981-09-08', '09317749256', 'Pangpang'],
            ['Karina', null, 'Uy', '1983-04-25', '09428850367', 'Quintong'],
            ['Ligaya', null, 'Villanueva', '1979-12-12', '09539961478', 'Roxas Blvd'],
            ['Marilou', null, 'Castro', '1971-05-06', '09640072589', 'San Pedro St'],
            ['Norma', null, 'Diaz', '1977-08-17', '09751183690', 'Tandoc'],
        ];

        foreach ($presidents as [$first, $middle, $last, $dob, $contact, $barangay]) {
            $email = preg_replace('/[^a-z0-9]/', '', strtolower($first . $last)) . '@gmail.com';

            $purok = Purok::firstOrCreate(
                ['name' => 'Purok 1', 'barangay' => $barangay],
                ['description' => 'BHW President catchment purok.']
            );

            // seedAccount: never overwrite staff edits on reseed.
            User::seedAccount(
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
                    'address' => "Purok 1, {$barangay}, San Carlos City, Pangasinan",
                    'barangay' => $barangay,
                    'purok_id' => $purok->id,
                    'assigned_barangay' => $barangay,
                    'role' => 'bhw_president',
                    'status' => 'approved',
                ]
            );
        }

        $this->command?->info('Seeded 15 RHU 1 BHW Presidents, one per barangay (shared password: password123).');
    }
}
