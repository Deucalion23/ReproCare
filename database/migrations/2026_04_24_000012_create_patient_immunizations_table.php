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
        if (!Schema::hasTable('patient_immunizations')) {
            Schema::create('patient_immunizations', function (Blueprint $table) {
                $table->id();
                
                // Only add user_id foreign keys if users table exists
                if (Schema::hasTable('users')) {
                    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                    $table->foreignId('administered_by_id')->nullable()->constrained('users')->onDelete('set null');
                } else {
                    $table->foreignId('user_id')->nullable();
                    $table->foreignId('administered_by_id')->nullable();
                }
                
                $table->foreignId('child_id')->nullable()->constrained('child_records')->onDelete('cascade');
                $table->foreignId('immunization_id')->constrained('immunizations')->onDelete('restrict');
                $table->foreignId('schedule_id')->nullable()->constrained('immunization_schedules')->onDelete('set null');
                $table->timestamp('administered_at')->nullable();
                $table->string('batch_number')->nullable();
                $table->string('site')->nullable();
                $table->text('reaction')->nullable();
                $table->date('next_due_date')->nullable();
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('child_id');
                $table->index('immunization_id');
                $table->index('administered_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_immunizations');
    }
};
