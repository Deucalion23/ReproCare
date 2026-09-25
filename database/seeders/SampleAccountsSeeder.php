<?php

namespace Database\Seeders;

use App\Models\Purok;
use App\Models\Pregnancy;
use App\Models\LearningMaterial;
use App\Models\User;
use App\Models\WalkInPatient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $puroks = collect(range(1, 5))->map(function (int $number) {
            return Purok::firstOrCreate(
                ['name' => 'Purok ' . $number],
                [
                    'barangay' => 'Barangay Burgos Padlan, San Carlos City, Pangasinan',
                    'description' => 'Sample purok ' . $number,
                ]
            );
        })->values();

        $password = Hash::make('password123');
        $profileImages = collect(glob(storage_path('app/public/uploads/profile/*.{jpg,jpeg,png,webp}'), GLOB_BRACE))
            ->map(fn (string $path) => 'uploads/profile/' . basename($path))
            ->values();
        $learningImages = collect(glob(storage_path('app/public/learning_materials/*.{jpg,jpeg,png,webp}'), GLOB_BRACE))
            ->map(fn (string $path) => 'learning_materials/' . basename($path))
            ->values();
        $midwifeProfileImage = 'uploads/profile/midwife-profile-pic.jpg';
        $bhwPresidentProfileImage = 'uploads/profile/bhw-president.jpg';
        $womenProfileImages = collect(range(1, 12))
            ->map(fn (int $number) => 'uploads/profile/women' . $number . '.jpg')
            ->filter(fn (string $path) => file_exists(storage_path('app/public/' . $path)))
            ->values();

        $midwifeAccounts = [
            ['first_name' => 'Luna', 'middle_initial' => 'S', 'last_name' => 'Navarro'],
            ['first_name' => 'Mara', 'middle_initial' => 'D', 'last_name' => 'Fernandez'],
        ];

        $bhwPresidentAccounts = [
            ['first_name' => 'Nina', 'middle_initial' => 'P', 'last_name' => 'Valdez'],
            ['first_name' => 'Olivia', 'middle_initial' => 'R', 'last_name' => 'Mercado'],
        ];

        $bhwAccounts = [
            ['first_name' => 'Mia', 'middle_initial' => 'R', 'last_name' => 'Dela Cruz'],
            ['first_name' => 'Jessa', 'middle_initial' => 'P', 'last_name' => 'Santos'],
            ['first_name' => 'Carla', 'middle_initial' => 'M', 'last_name' => 'Reyes'],
        ];

        $womenAccounts = [
            ['first_name' => 'Angela', 'middle_initial' => 'T', 'last_name' => 'Garcia', 'pregnant' => true, 'lmp' => '2026-01-20'],
            ['first_name' => 'Bianca', 'middle_initial' => 'L', 'last_name' => 'Mendoza', 'pregnant' => false, 'lmp' => null],
            ['first_name' => 'Camille', 'middle_initial' => 'A', 'last_name' => 'Torres', 'pregnant' => true, 'lmp' => '2026-02-08'],
            ['first_name' => 'Danica', 'middle_initial' => 'S', 'last_name' => 'Flores', 'pregnant' => false, 'lmp' => null],
            ['first_name' => 'Elise', 'middle_initial' => 'V', 'last_name' => 'Ramos', 'pregnant' => true, 'lmp' => '2026-03-01'],
            ['first_name' => 'Faith', 'middle_initial' => 'N', 'last_name' => 'Aquino', 'pregnant' => false, 'lmp' => null],
            ['first_name' => 'Giselle', 'middle_initial' => 'K', 'last_name' => 'Morales', 'pregnant' => false, 'lmp' => null],
            ['first_name' => 'Helena', 'middle_initial' => 'Q', 'last_name' => 'Bautista', 'pregnant' => false, 'lmp' => null],
            ['first_name' => 'Isabel', 'middle_initial' => 'C', 'last_name' => 'Salazar', 'pregnant' => false, 'lmp' => null],
            ['first_name' => 'Julia', 'middle_initial' => 'M', 'last_name' => 'Domingo', 'pregnant' => false, 'lmp' => null],
            ['first_name' => 'Karen', 'middle_initial' => 'F', 'last_name' => 'Lopez', 'pregnant' => false, 'lmp' => null],
            ['first_name' => 'Lara', 'middle_initial' => 'J', 'last_name' => 'Cruz', 'pregnant' => false, 'lmp' => null],
        ];

        $walkInPatients = [
            ['first_name' => 'Grace', 'middle_initial' => 'B', 'last_name' => 'Navarro', 'pregnant' => false, 'lmp' => null],
            ['first_name' => 'Hazel', 'middle_initial' => 'C', 'last_name' => 'Villanueva', 'pregnant' => true, 'lmp' => '2026-02-15'],
            ['first_name' => 'Ivy', 'middle_initial' => 'D', 'last_name' => 'Castro', 'pregnant' => false, 'lmp' => null],
            ['first_name' => 'Jasmine', 'middle_initial' => 'E', 'last_name' => 'Gutierrez', 'pregnant' => true, 'lmp' => '2026-03-12'],
        ];

        collect($midwifeAccounts)->each(function (array $midwife, int $index) use ($puroks, $password, $profileImages, $midwifeProfileImage) {
            $assignedPurok = $puroks[$index % $puroks->count()];
            $email = $this->buildEmail($midwife['first_name'], $midwife['last_name']);

            User::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $midwife['first_name'],
                    'middle_initial' => $midwife['middle_initial'],
                    'last_name' => $midwife['last_name'],
                    'email' => $email,
                    'password' => $password,
                    'date_of_birth' => '1988-02-14',
                    'gender' => 'female',
                    'contact_number' => '09' . fake()->numerify('#########'),
                    'address' => 'Rural Health Unit, ' . $assignedPurok->barangay,
                    'barangay' => $assignedPurok->barangay,
                    'purok_id' => $assignedPurok->id,
                    'role' => 'midwife',
                    'status' => 'approved',
                    'profile_image' => $this->preferredImage($midwifeProfileImage, $profileImages, $index),
                ]
            );
        });

        collect($bhwPresidentAccounts)->each(function (array $president, int $index) use ($puroks, $password, $profileImages, $bhwPresidentProfileImage) {
            $assignedPurok = $puroks[($index + 2) % $puroks->count()];
            $email = $this->buildEmail($president['first_name'], $president['last_name']);

            User::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $president['first_name'],
                    'middle_initial' => $president['middle_initial'],
                    'last_name' => $president['last_name'],
                    'email' => $email,
                    'password' => $password,
                    'date_of_birth' => '1987-06-10',
                    'gender' => 'female',
                    'contact_number' => '09' . fake()->numerify('#########'),
                    'address' => 'Barangay Hall, ' . $assignedPurok->barangay,
                    'barangay' => $assignedPurok->barangay,
                    'purok_id' => $assignedPurok->id,
                    'role' => 'bhw_president',
                    'status' => 'approved',
                    'profile_image' => $this->preferredImage($bhwPresidentProfileImage, $profileImages, $index + 2),
                ]
            );
        });

        $createdBhws = collect($bhwAccounts)->map(function (array $bhw, int $index) use ($puroks, $password, $profileImages) {
            $email = $this->buildEmail($bhw['first_name'], $bhw['last_name']);
            $assignedPurok = $puroks[$index % $puroks->count()];

            return User::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $bhw['first_name'],
                    'middle_initial' => $bhw['middle_initial'],
                    'last_name' => $bhw['last_name'],
                    'email' => $email,
                    'password' => $password,
                    'date_of_birth' => '1994-01-01',
                    'gender' => 'female',
                    'contact_number' => '09' . fake()->numerify('#########'),
                    'address' => $assignedPurok->name . ', ' . $assignedPurok->barangay,
                    'barangay' => $assignedPurok->barangay,
                    'purok_id' => $assignedPurok->id,
                    'role' => 'bhw',
                    'status' => 'approved',
                    'profile_image' => $this->pickImage($profileImages, $index + 4),
                ]
            );
        });

        collect($womenAccounts)->each(function (array $woman, int $index) use ($puroks, $password, $profileImages, $womenProfileImages) {
            $email = $this->buildEmail($woman['first_name'], $woman['last_name']);
            $assignedPurok = $puroks[$index % $puroks->count()];

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $woman['first_name'],
                    'middle_initial' => $woman['middle_initial'],
                    'last_name' => $woman['last_name'],
                    'email' => $email,
                    'password' => $password,
                    'date_of_birth' => now()->subYears(20 + $index)->format('Y-m-d'),
                    'gender' => 'female',
                    'contact_number' => '09' . fake()->numerify('#########'),
                    'address' => 'House ' . ($index + 1) . ', ' . $assignedPurok->name,
                    'barangay' => $assignedPurok->barangay,
                    'purok_id' => $assignedPurok->id,
                    'role' => 'user',
                    'status' => 'approved',
                    'profile_image' => $this->preferredImageFromList($womenProfileImages, $profileImages, $index, $index + 7),
                ]
            );

            if ($woman['pregnant']) {
                $this->upsertPregnancyForUser($user->id, $woman['lmp']);
            }
        });

        $recordedBy = $createdBhws->first()?->id ?? User::where('role', 'bhw')->value('id');

        collect($walkInPatients)->each(function (array $patient, int $index) use ($puroks, $recordedBy) {
            $assignedPurok = $puroks[$index % $puroks->count()];
            $walkInPatient = WalkInPatient::updateOrCreate(
                [
                    'first_name' => $patient['first_name'],
                    'middle_initial' => $patient['middle_initial'],
                    'last_name' => $patient['last_name'],
                ],
                [
                    'recorded_by_id' => $recordedBy,
                    'date_of_birth' => now()->subYears(19 + $index)->format('Y-m-d'),
                    'address' => 'Sitio ' . ($index + 1) . ', ' . $assignedPurok->name,
                    'barangay' => $assignedPurok->barangay,
                    'purok_id' => $assignedPurok->id,
                    'contact_number' => '09' . fake()->numerify('#########'),
                    'reason_for_visit' => $patient['pregnant'] ? 'Prenatal consultation' : 'General maternal health check',
                    'notes' => 'Sample walk-in patient generated for testing.',
                ]
            );

            if ($patient['pregnant']) {
                $this->upsertPregnancyForWalkIn($walkInPatient->id, $patient['lmp']);
            }
        });

        User::where('email', 'midwife@reprocare.com')->update([
            'password' => $password,
            'profile_image' => $this->preferredImage($midwifeProfileImage, $profileImages, 10),
        ]);

        User::where('email', 'pres@gmail.com')->update([
            'password' => $password,
            'profile_image' => $this->preferredImage($bhwPresidentProfileImage, $profileImages, 11),
        ]);

        User::where('email', 'ana@gmail.com')->update([
            'password' => $password,
            'profile_image' => $this->pickImage($profileImages, 12),
        ]);

        if ($learningImages->isNotEmpty()) {
            LearningMaterial::orderBy('id')->get()->each(function (LearningMaterial $material, int $index) use ($learningImages) {
                $material->update([
                    'image' => $this->pickImage($learningImages, $index),
                ]);
            });
        }

        $this->command?->info('Sample BHW, women, walk-in, and pregnancy records created.');
        $this->command?->line('Shared password: password123');
    }

    private function buildEmail(string $firstName, string $lastName): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/', '', $firstName . $lastName)) . '@gmail.com';
    }

    private function pickImage($images, int $index): ?string
    {
        if ($images->isEmpty()) {
            return null;
        }

        return $images[$index % $images->count()];
    }

    private function preferredImage(string $preferred, $images, int $fallbackIndex): ?string
    {
        if (file_exists(storage_path('app/public/' . $preferred))) {
            return $preferred;
        }

        return $this->pickImage($images, $fallbackIndex);
    }

    private function preferredImageFromList($preferredImages, $fallbackImages, int $preferredIndex, int $fallbackIndex): ?string
    {
        if ($preferredImages->isNotEmpty()) {
            return $preferredImages[$preferredIndex % $preferredImages->count()];
        }

        return $this->pickImage($fallbackImages, $fallbackIndex);
    }

    private function upsertPregnancyForUser(int $userId, string $lmp): void
    {
        $pregnancy = Pregnancy::firstOrNew([
            'user_id' => $userId,
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

    private function upsertPregnancyForWalkIn(int $walkInPatientId, string $lmp): void
    {
        $pregnancy = Pregnancy::firstOrNew([
            'walk_in_patient_id' => $walkInPatientId,
            'ended_at' => null,
        ]);

        $pregnancy->user_id = null;
        $pregnancy->lmp = $lmp;
        $pregnancy->risk_level = 'Low';
        $pregnancy->risk_assessment_mode = 'manual';
        $pregnancy->risk_notes = 'Sample pregnancy record';
        $pregnancy->is_high_risk = false;
        $pregnancy->notes = 'Generated sample data';
        $pregnancy->workflow_status = 'draft';
        $pregnancy->save();
    }
}
