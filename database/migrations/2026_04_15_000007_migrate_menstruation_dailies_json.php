<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate symptoms from JSON to normalized structure
        $dailies = DB::table('menstruation_dailies')->whereNotNull('symptoms')->get();

        foreach ($dailies as $daily) {
            $symptoms = json_decode($daily->symptoms, true);

            if (is_array($symptoms)) {
                foreach ($symptoms as $symptomName) {
                    // Find or create the symptom
                    $symptom = DB::table('symptoms')->where('name', $symptomName)->first();

                    if (!$symptom) {
                        // Create new symptom if it doesn't exist
                        $symptomId = DB::table('symptoms')->insertGetId([
                            'name' => $symptomName,
                            'category' => 'physical',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $symptomId = $symptom->id;
                    }

                    // Insert into pivot table
                    DB::table('menstruation_daily_symptoms')->insert([
                        'menstruation_daily_id' => $daily->id,
                        'symptom_id' => $symptomId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all pivot table entries
        DB::table('menstruation_daily_symptoms')->truncate();

        // Remove any newly created symptoms that weren't in the original seed
        // This is a simplified rollback - in production you might want to track which were auto-created
    }
};
