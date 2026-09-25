<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Who performed the action
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_role')->nullable();   // snapshot of role at time of action
            $table->string('user_name')->nullable();   // snapshot of name (for history even if user deleted)

            // What was done. Plain string (not enum): later migrations widen
            // the allowed set (handover, role_change, ...) via MySQL-only
            // MODIFY; a CHECK/enum here would reject those values on
            // Postgres/SQLite fresh installs.
            $table->string('action', 50)->default('other');

            // What it was done to
            $table->string('model_type')->nullable();       // e.g. HealthRecord, Pregnancy, User
            $table->unsignedBigInteger('model_id')->nullable();

            // Human-readable description
            $table->string('description');

            // Technical info
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('action');
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
            $table->index('user_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
