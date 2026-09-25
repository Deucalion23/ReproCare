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
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->unsignedInteger('gravida')->nullable();
            $table->unsignedInteger('parity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->dropColumn(['gravida', 'parity']);
        });
    }
};
