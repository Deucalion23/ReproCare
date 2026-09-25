<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MidwifeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the default midwife account
        User::create([
            'first_name' => 'RHU',
            'last_name' => 'Midwife',
            'email' => 'midwife@reprocare.com',
            'password' => Hash::make('midwife123'), // Change this in production
            'role' => 'midwife',
            'status' => 'approved',
            'address' => 'Rural Health Unit',
            'barangay' => 'Poblacion',
        ]);

        $this->command->info('Default midwife account created successfully.');
        $this->command->info('Email: midwife@reprocare.com');
        $this->command->info('Password: midwife123');
    }
}
