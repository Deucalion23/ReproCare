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
        Schema::create('checkup_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referred_by_bhw_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('woman_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('walk_in_patient_id')->nullable();
            $table->foreignId('assigned_midwife_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('converted_checkup_id')->nullable();
            $table->string('reason')->nullable();
            $table->enum('urgency', ['routine', 'urgent', 'emergency'])->default('routine');
            $table->text('bhw_notes')->nullable();
            $table->text('midwife_notes')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'scheduled', 'completed', 'declined'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkup_referrals');
    }
};
