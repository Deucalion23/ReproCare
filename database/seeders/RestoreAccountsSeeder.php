<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RestoreAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'City Health',
                'middle_initial' => 'O',
                'last_name' => 'Officer',
                'email' => 'cho@reprocare.com',
                'password' => bcrypt('password123'),
                'role' => 'cho',
                'status' => 'approved',
                'address' => 'City Health Office, San Carlos City',
                'barangay' => 'Poblacion',
                'profile_image' => null,
            ],
            [
                'first_name' => 'RHU',
                'middle_initial' => '1',
                'last_name' => 'Admin',
                'email' => 'rhu@reprocare.com',
                'password' => bcrypt('password123'),
                'role' => 'rhu',
                'status' => 'approved',
                'address' => 'Rural Health Unit 1, San Carlos City',
                'barangay' => 'Poblacion',
                'profile_image' => null,
            ],
            [
                'first_name' => 'RHU',
                'middle_initial' => null,
                'last_name' => 'Midwife',
                'email' => 'midwife@reprocare.com',
                'password' => bcrypt('password123'),
                'role' => 'midwife',
                'status' => 'approved',
                'address' => 'Rural Health Unit',
                'barangay' => 'Poblacion',
                'profile_image' => 'uploads/profile/69eb08aec6856_1777010862.jpg',
            ],
            [
                'first_name' => 'Maria',
                'middle_initial' => 'M',
                'last_name' => 'Santa',
                'email' => 'mariasanta@gmail.com',
                'password' => '$2y$12$3ie7s3QfR98/Tdw5saq6d.FMywQOAHXBRPwqQLlRr5JC77a0lIWkS',
                'role' => 'user',
                'status' => 'approved',
                'address' => 'Barangay Burgos San Carlos City Pangasinan',
                'barangay' => 'Barangay Burgos Padlan, San Carlos City, Pangasinan',
                'purok_id' => 4,
                'date_of_birth' => '1995-03-21',
                'contact_number' => '09123456785',
                'profile_image' => 'uploads/profile/69ebb2eb859fb_1777054443.jpg',
            ],
            [
                'first_name' => 'bhw',
                'middle_initial' => null,
                'last_name' => 'pres',
                'email' => 'pres@gmail.com',
                'password' => '$2y$12$r7zalZJud4imoqhOLZOx0.JyMY1uoj2GmpqofyOMl7/qtJW/FVicu',
                'role' => 'bhw_president',
                'status' => 'approved',
                'address' => ',mvc bnm',
                'barangay' => 'Burgos',
                'date_of_birth' => '1998-02-18',
                'gender' => 'male',
                'profile_image' => 'uploads/profile/69eb108e2d3f4_1777012878.jpg',
            ],
            [
                'first_name' => 'Madonna',
                'middle_initial' => 'M',
                'last_name' => 'De Vera',
                'email' => 'madonna@gmail.com',
                'password' => '$2y$12$pdC5c8bBRSdwt0Kp5/hvjuzGYsFkbZlux4K0IMeZSmeui9Re5cAcS',
                'role' => 'user',
                'status' => 'approved',
                'barangay' => 'Barangay Burgos Padlan, San Carlos City, Pangasinan',
                'purok_id' => 2,
                'date_of_birth' => '1997-11-21',
                'contact_number' => '09123456679',
                'profile_image' => 'uploads/profile/69ec5fd894d87_1777098712.jpg',
                'id_image_front' => 'uploads/ids/id_front_1777067345_69ebe55141352.jpeg',
                'id_image_back' => 'uploads/ids/id_back_1777067345_69ebe55148b27.jpeg',
            ],
            [
                'first_name' => 'Ana',
                'middle_initial' => 'M',
                'last_name' => 'Malasan',
                'email' => 'ana@gmail.com',
                'password' => '$2y$12$wtfldQoDd8yDcBlzYwI9m.W9owMCY/bYsgC/SSpH69WtS0Nw7AHH.',
                'role' => 'bhw',
                'status' => 'approved',
                'barangay' => 'Barangay Burgos Padlan, San Carlos City, Pangasinan',
                'date_of_birth' => '1981-02-02',
                'gender' => 'female',
                'contact_number' => '09234567891',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Restored ' . count($users) . ' user accounts with separated names (first_name, middle_initial, last_name).');
    }
}
