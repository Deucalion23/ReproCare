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
        Schema::create('patient_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('child_id')->nullable()->constrained('child_records')->onDelete('cascade');
            $table->string('from_barangay');
            $table->string('to_barangay');
            $table->foreignId('from_purok_id')->nullable()->constrained('puroks')->onDelete('set null');
            $table->foreignId('to_purok_id')->nullable()->constrained('puroks')->onDelete('set null');
            $table->date('transfer_date');
            $table->text('reason')->nullable();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('child_id');
            $table->index('status');
            $table->index('transfer_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_transfers');
    }
};
