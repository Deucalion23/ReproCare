<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Postpartum & Newborn Monitoring module: tracks mothers after a
     * recorded delivery date plus their newborns (vitals, danger
     * signs, immunization schedule).
     */
    public function up(): void
    {
        Schema::create('newborns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mother_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pregnancy_id')->nullable()->constrained('pregnancies')->nullOnDelete();
            $table->string('name')->nullable();
            $table->enum('sex', ['male', 'female'])->nullable();
            $table->date('birth_date');
            $table->decimal('birth_weight_kg', 4, 2)->nullable();
            $table->enum('feeding_type', ['exclusive_breast', 'mixed', 'formula'])->default('exclusive_breast');
            $table->text('danger_signs')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('postpartum_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pregnancy_id')->nullable()->constrained('pregnancies')->nullOnDelete();
            $table->foreignId('newborn_id')->nullable()->constrained('newborns')->nullOnDelete();
            $table->date('visit_date');
            $table->unsignedTinyInteger('visit_week')->default(1);
            $table->string('bp', 12)->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->enum('bleeding', ['none', 'spotting', 'heavy'])->default('none');
            $table->text('infection_signs')->nullable();
            $table->unsignedTinyInteger('depression_score')->nullable();
            $table->enum('breastfeeding', ['exclusive', 'partial', 'none'])->default('exclusive');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('newborn_immunizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newborn_id')->constrained('newborns')->onDelete('cascade');
            $table->string('vaccine', 40);
            $table->date('scheduled_date');
            $table->date('given_date')->nullable();
            $table->enum('status', ['scheduled', 'given', 'missed'])->default('scheduled');
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newborn_immunizations');
        Schema::dropIfExists('postpartum_visits');
        Schema::dropIfExists('newborns');
    }
};
