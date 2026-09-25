<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('category', 40)->nullable()->index();
            $table->string('event_key', 120)->nullable();
            $table->string('risk_fingerprint', 64)->nullable();
            $table->unsignedBigInteger('subject_user_id')->nullable()->index();
            $table->unsignedBigInteger('checkup_id')->nullable()->index();
            $table->unsignedBigInteger('parent_notification_id')->nullable()->index();
            $table->timestamp('last_reminded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unique(['user_id', 'event_key']);
        });
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('notification_id')->nullable()->index();
        });

        // Preserve acknowledgements from the previous title-based alert system.
        DB::table('notifications')->where('title', 'like', '%Risk Alert%')
            ->whereIn('user_id', DB::table('users')->where('role', 'user')->select('id'))
            ->update(['category' => 'risk']);
    }

    public function down(): void
    {
        Schema::table('sms_logs', fn (Blueprint $table) => $table->dropColumn('notification_id'));
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'event_key']);
            $table->dropColumn(['category', 'event_key', 'risk_fingerprint', 'subject_user_id',
                'checkup_id', 'parent_notification_id', 'last_reminded_at', 'resolved_at']);
        });
    }
};
