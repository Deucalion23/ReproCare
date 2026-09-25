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
        // Step 1: Drop unused tables
        Schema::dropIfExists('patient_immunizations');
        Schema::dropIfExists('immunization_schedules');
        Schema::dropIfExists('immunizations');
        Schema::dropIfExists('patient_transfers');
        Schema::dropIfExists('vitamin_distributions');
        Schema::dropIfExists('vitamins');

        // Step 2: Fix bhw_assignments foreign keys
        Schema::table('bhw_assignments', function (Blueprint $table) {
            // Drop old foreign keys
            $table->dropForeign(['bhw_id']);
            $table->dropForeign(['assigned_by_id']);
            
            // Add new foreign keys pointing to users table
            $table->foreign('bhw_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Step 3: Fix tasks foreign keys
        Schema::table('tasks', function (Blueprint $table) {
            // Drop old foreign keys
            $table->dropForeign(['assigned_to_id']);
            $table->dropForeign(['assigned_by_id']);
            
            // Add new foreign keys pointing to users table
            $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily rolled back due to data loss
        throw new \Exception('This migration cannot be rolled back. Restore from database backup if needed.');
    }
};
