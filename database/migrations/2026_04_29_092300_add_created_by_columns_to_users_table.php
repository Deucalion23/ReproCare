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
        Schema::table('users', function (Blueprint $table) {
            // Add columns to track who created the user (for patients/women)
            if (!Schema::hasColumn('users', 'created_by_bhw_id')) {
                $table->foreignId('created_by_bhw_id')->nullable()->constrained('users')->nullOnDelete()->after('role');
            }
            if (!Schema::hasColumn('users', 'created_by_midwife_id')) {
                $table->foreignId('created_by_midwife_id')->nullable()->constrained('users')->nullOnDelete()->after('created_by_bhw_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'created_by_bhw_id')) {
                $table->dropForeign(['created_by_bhw_id']);
                $table->dropColumn('created_by_bhw_id');
            }
            if (Schema::hasColumn('users', 'created_by_midwife_id')) {
                $table->dropForeign(['created_by_midwife_id']);
                $table->dropColumn('created_by_midwife_id');
            }
        });
    }
};
