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
        Schema::create('bhw_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bhw_id')->constrained('bhws')->onDelete('cascade');
            $table->foreignId('purok_id')->constrained('puroks')->onDelete('cascade');
            $table->foreignId('assigned_by_id')->constrained('bhw_presidents')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['bhw_id', 'purok_id']);
            $table->index(['bhw_id', 'is_active']);
            $table->index('assigned_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bhw_assignments');
    }
};
