<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $barangay = 'Barangay Burgos Padlan, San Carlos City, Pangasinan';

        foreach (range(1, 5) as $number) {
            DB::table('puroks')->updateOrInsert(
                [
                    'name' => 'Purok ' . $number,
                    'barangay' => $barangay,
                ],
                [
                    'description' => 'Residential area for ' . $barangay,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('puroks')
            ->where('barangay', 'Barangay Burgos Padlan, San Carlos City, Pangasinan')
            ->whereIn('name', ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5'])
            ->delete();
    }
};
