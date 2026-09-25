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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to_id')->constrained('bhws')->onDelete('cascade');
            $table->foreignId('assigned_by_id')->constrained('bhw_presidents')->onDelete('cascade');
            $table->enum('task_type', ['patient_visit', 'data_collection', 'follow_up', 'report_submission', 'other'])->default('other');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('assigned_to_id');
            $table->index('assigned_by_id');
            $table->index('status');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
