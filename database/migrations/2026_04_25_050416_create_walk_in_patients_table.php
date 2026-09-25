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
        Schema::create('walk_in_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recorded_by_bhw_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('middle_initial')->nullable();
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->string('address')->nullable();
            $table->string('barangay')->nullable();
            $table->foreignId('purok_id')->nullable()->constrained('puroks')->nullOnDelete();
            $table->string('contact_number')->nullable();
            $table->text('reason_for_visit')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('converted_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('walk_in_patients');
    }
};
