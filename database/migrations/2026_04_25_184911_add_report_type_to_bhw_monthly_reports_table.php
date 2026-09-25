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
        // The original history has no CREATE TABLE migration for
        // bhw_monthly_reports (local databases got it from a dump restore),
        // so fresh installs must create the base table here. Later migrations
        // add filters / submission workflow / soft deletes (all guarded).
        if (!Schema::hasTable('bhw_monthly_reports')) {
            Schema::create('bhw_monthly_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bhw_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->integer('report_month')->nullable();
                $table->integer('report_year')->nullable();
                $table->integer('total_records')->default(0);
                $table->string('status')->default('draft');
                $table->timestamp('printed_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('bhw_monthly_reports', 'report_type')) {
            return;
        }

        Schema::table('bhw_monthly_reports', function (Blueprint $table) {
            $table->enum('report_type', ['health_records', 'pregnancies'])->default('health_records');
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
