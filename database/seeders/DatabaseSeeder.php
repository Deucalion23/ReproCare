<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LearningMaterial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Restore original accounts with separated names
        $this->call(RestoreAccountsSeeder::class);

        // Seed Learning Materials
        $this->call(LearningMaterialSeeder::class);
        $this->call(LearningVideoSeeder::class);
        $this->call(MariaClaraPregnancySeeder::class);
        $this->call(HighRiskPregnancySeeder::class);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Midwife: midwife@reprocare.com');
        $this->command->info('BHW President: pres@gmail.com');
        $this->command->info('BHW: ana@gmail.com');
        $this->command->info('Patients: mariasanta@gmail.com, madonna@gmail.com');
    }
}
