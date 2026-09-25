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
        // Delete orphaned cycles with NULL owner (IDs 19, 20, 21, 22)
        // These are duplicates of user 4's cycles (IDs 3, 4, 5, 6).
        // NOTE: at this point the owner column is still woman_id (renamed to
        // user_id only by a later migration).
        try {
            $owner = Schema::hasColumn('cycles', 'woman_id') ? 'woman_id'
                : (Schema::hasColumn('cycles', 'user_id') ? 'user_id' : null);
            if ($owner) {
                DB::table('cycles')
                    ->whereIn('id', [19, 20, 21, 22])
                    ->whereNull($owner)
                    ->delete();
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the deleted cycles (for rollback purposes)
        // These were duplicates of user 4's cycles
        DB::table('cycles')->insert([
            [
                'id' => 19,
                'user_id' => null,
                'period_start_date' => '2026-01-01',
                'period_end_date' => '2026-01-04',
                'cycle_length' => null,
                'notes' => null,
                'created_at' => '2026-04-16 00:36:03',
                'updated_at' => '2026-04-16 00:36:03',
            ],
            [
                'id' => 20,
                'user_id' => null,
                'period_start_date' => '2026-02-11',
                'period_end_date' => '2026-02-15',
                'cycle_length' => null,
                'notes' => null,
                'created_at' => '2026-04-16 00:41:28',
                'updated_at' => '2026-04-16 00:41:28',
            ],
            [
                'id' => 21,
                'user_id' => null,
                'period_start_date' => '2026-03-10',
                'period_end_date' => '2026-03-16',
                'cycle_length' => null,
                'notes' => null,
                'created_at' => '2026-04-16 00:42:42',
                'updated_at' => '2026-04-16 00:42:42',
            ],
            [
                'id' => 22,
                'user_id' => null,
                'period_start_date' => '2026-04-11',
                'period_end_date' => '2026-04-14',
                'cycle_length' => null,
                'notes' => null,
                'created_at' => '2026-04-16 00:43:46',
                'updated_at' => '2026-04-16 00:43:46',
            ],
        ]);
    }
};
