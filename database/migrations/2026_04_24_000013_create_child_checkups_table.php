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
        Schema::create('child_checkups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('child_records')->onDelete('cascade');
            $table->date('checkup_date');
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('head_circumference', 5, 2)->nullable();
            $table->text('developmental_milestones')->nullable();
            $table->json('vaccinations_given')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('conducted_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('child_id');
            $table->index('checkup_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_checkups');
    }
};
