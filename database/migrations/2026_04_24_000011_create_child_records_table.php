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
        if (!Schema::hasTable('child_records')) {
            Schema::create('child_records', function (Blueprint $table) {
                $table->id();
                
                // Only add mother_id foreign key if users table exists
                if (Schema::hasTable('users')) {
                    $table->foreignId('mother_id')->constrained('users')->onDelete('cascade');
                } else {
                    $table->foreignId('mother_id')->nullable();
                }
                
                $table->foreignId('pregnancy_id')->nullable()->constrained('pregnancies')->onDelete('set null');
                $table->string('first_name');
                $table->string('last_name');
                $table->date('date_of_birth');
                $table->enum('gender', ['male', 'female']);
                $table->decimal('birth_weight', 5, 2)->nullable();
                $table->decimal('birth_length', 5, 2)->nullable();
                $table->string('apgar_score')->nullable();
                $table->enum('delivery_type', ['normal', 'cesarean', 'assisted'])->default('normal');
                $table->text('complications')->nullable();
                $table->string('barangay')->nullable();
                $table->foreignId('purok_id')->nullable()->constrained('puroks')->onDelete('set null');
                $table->enum('status', ['active', 'transferred', 'deceased'])->default('active');
                $table->timestamps();
                
                $table->index('mother_id');
                $table->index('pregnancy_id');
                $table->index('barangay');
                $table->index('purok_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_records');
    }
};
