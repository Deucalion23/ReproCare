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
        // MySQL only — on pgsql/sqlite the status column is VARCHAR, no change needed.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE checkups MODIFY COLUMN status ENUM('scheduled', 'completed', 'missed', 'cancelled', 'Rescheduled') DEFAULT 'scheduled'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE checkups MODIFY COLUMN status ENUM('scheduled', 'completed', 'missed', 'cancelled') DEFAULT 'scheduled'");
    }
};
