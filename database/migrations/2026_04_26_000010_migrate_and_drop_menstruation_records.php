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
        // Step 1: Migrate existing data from menstruation_records to cycles
        $records = DB::table('menstruation_records')->get();

        foreach ($records as $record) {
            // Check if a cycle already exists for this user with the same dates
            $existing = DB::table('cycles')
                ->where('user_id', $record->user_id)
                ->where('period_start_date', $record->start_date)
                ->first();

            if (!$existing) {
                DB::table('cycles')->insert([
                    'user_id' => $record->user_id,
                    'period_start_date' => $record->start_date,
                    'period_end_date' => $record->end_date,
                    'cycle_length' => null, // Will be calculated by the model's boot method
                    'notes' => $record->notes,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ]);
            }
        }

        // Step 2: Drop the menstruation_records table
        Schema::dropIfExists('menstruation_records');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the menstruation_records table
        Schema::create('menstruation_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('start_date');
        });
    }
};
