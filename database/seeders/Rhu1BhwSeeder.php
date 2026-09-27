<?php

namespace Database\Seeders;

use App\Models\Purok;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds one BHW per RHU 1 barangay (except Burgos St, covered by Ana Malasan)
 * with realistic Philippine details.
 *
 * Fully idempotent (updateOrCreate) so it is safe to run on every production
 * boot via DatabaseSeeder. Uses NO faker (unavailable in production builds).
 * Shared password is password123, same as all other demo accounts.
 */
class Rhu1BhwSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        $bhws = [
            // first, middle, last, dob, contact, barangay
            ['Rosa', null, 'Aquino', '1978-04-12', '09126638145', 'Bonifacio St'],
            ['Divina', null, 'Ramos', '1981-09-25', '09217749203', 'Cacaritan'],
            ['Nena', null, 'Castillo', '1975-12-03', '09328850317', 'Calomboyan'],
            ['Lilia', null, 'Mendoza', '1983-06-17', '09439961428', 'Capataan'],
            ['Cora', null, 'Villanueva', '1979-02-28', '09540072539', 'Lucban St'],
            ['Digna', null, 'Torres', '1986-11-09', '09651183640', 'Mamarlao'],
            ['Pacing', null, 'Flores', '1972-07-14', '09762294751', 'Naguilayan'],
            ['Miling', null, 'Cruz', '1990-01-30', '09083305862', 'Pagal'],
            ['Salud', null, 'Reyes', '1984-05-21', '09194416973', 'Palaming'],
            ['Trining', null, 'Garcia', '1977-10-06', '09205527084', 'Pangalangan'],
            ['Naty', null, 'Bautista', '1980-03-19', '09316638195', 'Pangpang'],
            ['Pina', null, 'Domingo', '1987-08-08', '09427749206', 'Quintong'],
            ['Mely', null, 'Salazar', '1992-12-16', '09538850317', 'Roxas Blvd'],
            ['Celing', null, 'Dizon', '1976-04-29', '09649961428', 'San Pedro St'],
            ['Andeng', null, 'Marquez', '1989-09-11', '09750072539', 'Tandoc'],
        ];

        foreach ($bhws as [$first, $middle, $last, $dob, $contact, $barangay]) {
            $email = preg_replace('/[^a-z0-9]/', '', strtolower($first . $last)) . '@gmail.com';

            $purok = Purok::firstOrCreate(
                ['name' => 'Purok 1', 'barangay' => $barangay],
                ['description' => 'BHW catchment purok.']
            );

            User::updateOrCreate(
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
                    'role' => 'bhw',
                    'status' => 'approved',
                ]
            );
        }

        $this->command?->info('Seeded 15 RHU 1 BHWs, one per barangay (shared password: password123).');
    }
}
