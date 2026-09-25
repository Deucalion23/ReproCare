<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maternal_deaths', function (Blueprint $table) {
            $table->id();

            // Patient (registered or walk-in)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('walk_in_patient_id')->nullable()->constrained('walk_in_patients')->nullOnDelete();
            $table->foreignId('pregnancy_id')->nullable()->constrained('pregnancies')->nullOnDelete();

            // Staff who recorded & reviewed
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();

            // Location
            $table->foreignId('purok_id')->nullable()->constrained('puroks')->nullOnDelete();
            $table->string('barangay')->nullable();

            // Death details
            $table->date('death_date');
            $table->time('death_time')->nullable();
            $table->integer('age_at_death')->nullable(); // in years

            $table->enum('place_of_death', [
                'home',
                'barangay_health_station',
                'rhu',
                'city_hospital',
                'provincial_hospital',
                'private_hospital',
                'in_transit',
                'other'
            ]);

            $table->string('cause_of_death')->nullable(); // free text
            $table->enum('cause_category', [
                'hemorrhage',
                'hypertension_eclampsia',
                'sepsis',
                'obstructed_labor',
                'unsafe_abortion',
                'embolism',
                'other_direct',
                'indirect_cause',
                'unknown'
            ])->nullable();

            // Timing relative to pregnancy
            $table->enum('death_timing', [
                'during_pregnancy',
                'during_delivery',
                'within_24_hours_postpartum',
                'within_7_days_postpartum',
                'within_42_days_postpartum',
                'unknown'
            ])->nullable();

            $table->text('notes')->nullable();

            // Audit process
            $table->enum('audit_status', [
                'pending',
                'under_review',
                'reviewed',
                'closed'
            ])->default('pending');

            $table->text('audit_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('pregnancy_id');
            $table->index('purok_id');
            $table->index('death_date');
            $table->index('cause_category');
            $table->index('audit_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maternal_deaths');
    }
};
