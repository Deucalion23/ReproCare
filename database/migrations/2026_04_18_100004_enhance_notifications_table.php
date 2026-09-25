<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('title')->nullable()->after('user_id');
            $table->enum('type', ['info', 'warning', 'danger', 'success'])->default('info')->after('title');
            $table->string('action_url')->nullable()->after('message');
            $table->timestamp('read_at')->nullable()->after('is_read');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['title', 'type', 'action_url', 'read_at']);
        });
    }
};
