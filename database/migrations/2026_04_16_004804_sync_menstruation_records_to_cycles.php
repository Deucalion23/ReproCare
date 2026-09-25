<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\MenstruationRecord;
use App\Models\Cycle;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sync existing MenstruationRecord data to Cycle records
        $records = MenstruationRecord::all();
        
        foreach ($records as $record) {
            // Check if Cycle record already exists for this MenstruationRecord
            $existingCycle = Cycle::where('user_id', $record->user_id)
                ->where('period_start_date', $record->start_date)
                ->first();
            
            if (!$existingCycle) {
                // Create Cycle record from MenstruationRecord
                Cycle::create([
                    'user_id' => $record->user_id,
                    'period_start_date' => $record->start_date,
                    'period_end_date' => $record->end_date,
                    'flow_intensity' => 'medium', // Default value
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove synced Cycle records
        // This is a one-time sync, so we can leave the records or add logic to identify synced ones
    }
};
