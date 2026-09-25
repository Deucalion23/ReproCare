<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fertility_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('log_date');
            $table->enum('cervical_mucus', ['dry', 'sticky', 'creamy', 'watery', 'egg_white'])->nullable();
            $table->decimal('basal_body_temp', 4, 2)->nullable(); // e.g., 36.50
            $table->enum('ovulation_test', ['negative', 'positive'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'log_date']);
            $table->unique(['user_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fertility_logs');
    }
};
