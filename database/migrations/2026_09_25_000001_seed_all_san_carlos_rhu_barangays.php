<?php

use App\Models\Barangay;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * City Health Office, San Carlos City, Pangasinan RHU allocation list.
     * The list contains all 86 barangays: RHU I 16, II 15, III 20, IV 16, V 19.
     */
    public function up(): void
    {
        if (!Schema::hasTable('barangays')) return;

        $renamed = [
            'Burgos - Padlan St' => 'Burgos St',
            'San pedro - Taloy St' => 'San Pedro St',
        ];

        foreach ($renamed as $old => $new) {
            if (DB::table('barangays')->where('name', $new)->exists()) {
                DB::table('barangays')->where('name', $old)->delete();
            } else {
                DB::table('barangays')->where('name', $old)->update(['name' => $new, 'updated_at' => now()]);
            }
            foreach (['users', 'puroks', 'walk_in_patients', 'maternal_deaths', 'maternal_morbidities'] as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'barangay')) {
                    DB::table($table)->where('barangay', $old)->update(['barangay' => $new]);
                }
            }
        }

        foreach (Barangay::RHU_CATCHMENTS as $rhu => $barangays) {
            foreach ($barangays as $name) {
                DB::table('barangays')->updateOrInsert(
                    ['name' => $name],
                    ['rhu_assignment' => $rhu, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
                );

                // Permit BHW assignment before a barangay's named puroks are entered.
                if (Schema::hasTable('puroks') && !DB::table('puroks')->where('barangay', $name)->exists()) {
                    DB::table('puroks')->insert([
                        'name' => 'Purok 1',
                        'barangay' => $name,
                        'description' => 'Default purok seeded from the City Health Office RHU allocation list.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Registry data is retained to avoid orphaning people, records, or puroks.
    }
};
