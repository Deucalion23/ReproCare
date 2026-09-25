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
        Schema::create('health_records_archived', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('walk_in_patient_id')->nullable()->constrained('walk_in_patients')->nullOnDelete();
            $table->foreignId('pregnancy_id')->nullable()->constrained('pregnancies')->nullOnDelete();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Health measurements
            $table->string('bp')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('bmi', 8, 2)->nullable();
            $table->integer('heart_rate')->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('hemoglobin', 5, 2)->nullable();
            
            // Pregnancy-related
            $table->integer('gestational_age')->nullable();
            $table->string('immunization_status')->nullable();
            $table->string('contraceptive_use')->nullable();
            $table->text('lab_results')->nullable();
            
            // Lifestyle factors
            $table->enum('smoking_status', ['none', 'former', 'current'])->nullable();
            $table->enum('alcohol_use', ['none', 'former', 'current'])->nullable();
            $table->enum('drug_use', ['none', 'former', 'current'])->nullable();
            $table->text('lifestyle_notes')->nullable();
            $table->text('obstetric_history')->nullable();
            
            // Notes and recommendations
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            
            // Risk assessment
            $table->enum('risk_level', ['Low', 'Medium', 'High'])->nullable();
            $table->enum('risk_assessment_mode', ['automatic', 'manual'])->nullable();
            $table->text('risk_notes')->nullable();
            
            // Workflow fields
            $table->foreignId('bhw_president_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('bhw_president_approved_at')->nullable();
            $table->enum('workflow_status', ['recorded_by_bhw', 'submitted_to_bhw_president', 'accepted_by_midwife', 'submitted_to_midwife'])->default('recorded_by_bhw');
            $table->timestamp('submitted_to_bhw_president_at')->nullable();
            $table->timestamp('submitted_to_midwife_at')->nullable();
            $table->timestamp('midwife_accepted_at')->nullable();
            $table->text('workflow_notes')->nullable();
            
            // Archiving metadata
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->text('archived_reason')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('walk_in_patient_id');
            $table->index('recorded_by_id');
            $table->index('archived_at');
            $table->index('is_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_records_archived');
    }
};
