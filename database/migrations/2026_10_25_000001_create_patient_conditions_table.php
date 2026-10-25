<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patient_conditions')) {
            return;
        }

        Schema::create('patient_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('condition_name', 150);
            $table->date('diagnosed_on')->nullable();
            $table->string('status', 30)->default('Active');
            $table->string('verification_status', 40)->default('Awaiting Verification');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status'], 'patient_conditions_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_conditions');
    }
};
