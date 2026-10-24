<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_planning_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('preferred_method', 40)->nullable();
            $table->string('method_given', 40)->nullable();
            $table->enum('pregnancy_intention', ['trying', 'avoiding', 'undecided', 'spacing'])->nullable();
            $table->date('counseling_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('stop_date')->nullable();
            $table->date('next_follow_up')->nullable();
            $table->text('side_effects')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('informed_choice_confirmed')->default(false);
            $table->enum('status', ['requested', 'active', 'stopped', 'referred'])->default('requested');
            $table->timestamps();
            $table->index(['user_id', 'next_follow_up']);
            $table->index(['status', 'next_follow_up']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_planning_records');
    }
};
