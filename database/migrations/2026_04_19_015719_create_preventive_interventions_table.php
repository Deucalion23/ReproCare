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
        Schema::create('preventive_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('risk_level', ['Low', 'Medium', 'High']);
            $table->text('trigger_reason');
            $table->text('recommendations')->nullable();
            $table->string('intervention_type'); // hypertension_management, anemia_prevention, etc.
            $table->enum('status', ['triggered', 'in_progress', 'completed', 'cancelled'])->default('triggered');
            $table->timestamp('triggered_at');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preventive_interventions');
    }
};
