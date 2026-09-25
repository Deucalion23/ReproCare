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
        Schema::table('checkup_referrals', function (Blueprint $table) {
            $table->foreign('walk_in_patient_id')->references('id')->on('walk_in_patients')->nullOnDelete();
            $table->foreign('converted_checkup_id')->references('id')->on('checkups')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkup_referrals', function (Blueprint $table) {
            $table->dropForeign(['walk_in_patient_id']);
            $table->dropForeign(['converted_checkup_id']);
        });
    }
};
