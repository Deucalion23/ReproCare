<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maternal_morbidities', function (Blueprint $table) {
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

            // Event details
            $table->date('event_date');
            $table->time('event_time')->nullable();

            $table->enum('complication_type', [
                'severe_hemorrhage',
                'eclampsia',
                'severe_preeclampsia',
                'sepsis',
                'ruptured_uterus',
                'severe_anemia',
                'obstructed_labor',
                'placenta_previa',
                'placental_abruption',
                'other'
            ]);

            $table->enum('place_of_event', [
                'home',
                'barangay_health_station',
                'rhu',
                'city_hospital',
                'provincial_hospital',
                'private_hospital',
                'in_transit',
                'other'
            ]);

            $table->enum('outcome', [
                'survived_no_intervention',
                'survived_with_intervention',
                'transferred_to_higher_facility',
                'died'  // links to maternal_deaths if died
            ])->default('survived_with_intervention');

            // Link to maternal death record if outcome was death
            $table->foreignId('maternal_death_id')->nullable()->constrained('maternal_deaths')->nullOnDelete();

            $table->text('description')->nullable();
            $table->text('interventions_done')->nullable();
            $table->text('notes')->nullable();

            // Review process
            $table->enum('review_status', [
                'pending',
                'under_review',
                'reviewed',
                'closed'
            ])->default('pending');

            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('pregnancy_id');
            $table->index('event_date');
            $table->index('complication_type');
            $table->index('review_status');
            $table->index('purok_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maternal_morbidities');
    }
};
