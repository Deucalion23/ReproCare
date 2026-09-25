<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - migrate existing symptoms data from JSON/text to pivot table.
     */
    public function up(): void
    {
        // Get all menstruation records with symptoms
        $records = DB::table('menstruation_records')
            ->whereNotNull('symptoms')
            ->where('symptoms', '!=', '')
            ->get();
        
        foreach ($records as $record) {
            $symptoms = $record->symptoms;
            
            // Parse symptoms (could be JSON array or comma-separated string)
            $symptomList = [];
            if (is_string($symptoms)) {
                // Try JSON first
                $decoded = json_decode($symptoms, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $symptomList = $decoded;
                } else {
                    // Fallback to comma-separated
                    $symptomList = array_map('trim', explode(',', $symptoms));
                }
            } elseif (is_array($symptoms)) {
                $symptomList = $symptoms;
            }
            
            // Insert into pivot table
            foreach ($symptomList as $symptomName) {
                if (empty($symptomName)) continue;
                
                // Find symptom ID from symptoms table
                $symptom = DB::table('symptoms')
                    ->where('name', strtolower(trim($symptomName)))
                    ->first();
                
                if ($symptom) {
                    // Check if already exists
                    $exists = DB::table('menstruation_record_symptoms')
                        ->where('menstruation_record_id', $record->id)
                        ->where('symptom_id', $symptom->id)
                        ->exists();
                    
                    if (!$exists) {
                        DB::table('menstruation_record_symptoms')->insert([
                            'menstruation_record_id' => $record->id,
                            'symptom_id' => $symptom->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear the pivot table data
        DB::table('menstruation_record_symptoms')->truncate();
    }
};
