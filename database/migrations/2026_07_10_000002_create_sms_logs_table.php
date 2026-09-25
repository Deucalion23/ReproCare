<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone_number', 30);
            $table->text('message');
            $table->enum('type', [
                'appointment_reminder',
                'high_risk_alert',
                'missed_checkup',
                'custom',
                'broadcast',
            ])->default('custom');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->string('twilio_sid', 64)->nullable()->comment('Twilio Message SID for tracking');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
