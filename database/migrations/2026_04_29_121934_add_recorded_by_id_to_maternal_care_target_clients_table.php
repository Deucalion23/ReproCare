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
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete()->after('pregnancy_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->dropForeign(['recorded_by_id']);
            $table->dropColumn('recorded_by_id');
        });
    }
};
