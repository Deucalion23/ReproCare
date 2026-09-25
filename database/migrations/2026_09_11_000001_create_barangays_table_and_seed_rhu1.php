<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Canonical barangay registry for RHU 1 (San Carlos City, Pangasinan).
     */
    private array $rhu1Barangays = [
        'Bonifacio St',
        'Burgos - Padlan St',
        'Cacaritan',
        'Calomboyan',
        'Capataan',
        'Lucban St',
        'Mamarlao',
        'Naguilayan',
        'Pagal',
        'Palaming',
        'Pangalangan',
        'Pangpang',
        'Quintong',
        'Roxas Blvd',
        'San pedro - Taloy St',
        'Tandoc',
    ];

    public function up(): void
    {
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('rhu_assignment')->default('RHU 1');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        foreach ($this->rhu1Barangays as $name) {
            DB::table('barangays')->updateOrInsert(
                ['name' => $name],
                ['rhu_assignment' => 'RHU 1', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );

            // One default purok per barangay so BHWs can be assigned under each.
            $exists = DB::table('puroks')
                ->where('name', 'Purok 1')
                ->where('barangay', $name)
                ->exists();
            if (!$exists) {
                DB::table('puroks')->insert([
                    'name' => 'Purok 1',
                    'barangay' => $name,
                    'description' => 'Default purok seeded with the RHU 1 barangay registry.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Tag the current RHU admin account(s) without an assignment to RHU 1.
        DB::table('users')
            ->where('role', 'rhu')
            ->whereNull('rhu_assignment')
            ->update(['rhu_assignment' => 'RHU 1']);
    }

    public function down(): void
    {
        Schema::dropIfExists('barangays');
    }
};
