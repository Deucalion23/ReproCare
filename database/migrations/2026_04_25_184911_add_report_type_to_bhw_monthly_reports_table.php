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
        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            $table->enum('report_type', ['health_records', 'pregnancies'])->default('health_records')->after('bhw_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            $table->dropColumn('report_type');
        });
    }
};
