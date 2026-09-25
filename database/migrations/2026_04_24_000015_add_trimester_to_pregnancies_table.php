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
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->enum('trimester', ['first', 'second', 'third'])->nullable()->after('edd');
            $table->timestamp('trimester_calculated_at')->nullable()->after('trimester');
            $table->date('current_trimester_start_date')->nullable()->after('trimester_calculated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropColumn(['trimester', 'trimester_calculated_at', 'current_trimester_start_date']);
        });
    }
};
