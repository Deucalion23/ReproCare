<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('midwife_id')->constrained('midwives')->onDelete('cascade');
            $table->date('scheduled_date');
            $table->string('purpose');
            // Plain string (not enum): later migrations widen the allowed set
            // (cancelled, Rescheduled, ...) via MySQL-only MODIFY; a CHECK/enum
            // here would reject those values on Postgres/SQLite fresh installs.
            $table->string('status', 50)->default('Scheduled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkups');
    }
};
