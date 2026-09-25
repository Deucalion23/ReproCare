<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Existing and normal immediate messages must never be re-delivered.
            $table->boolean('is_sent')->default(true);
            $table->timestamp('scheduled_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('messages', fn (Blueprint $table) => $table->dropColumn(['is_sent', 'scheduled_at']));
    }
};
