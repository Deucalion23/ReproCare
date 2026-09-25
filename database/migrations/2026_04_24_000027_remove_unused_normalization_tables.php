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
        // Step 1: Drop foreign key columns from users table if they exist
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['feeding_method_id']);
                $table->dropForeign(['family_planning_method_id']);
                $table->dropForeign(['vitamins_id']);
            });
        } catch (\Exception $e) {
            // Foreign keys don't exist, continue
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('feeding_method_id');
                $table->dropColumn('family_planning_method_id');
                $table->dropColumn('vitamins_id');
            });
        } catch (\Exception $e) {
            // Columns don't exist, continue
        }

        // Step 2: Drop unused normalization tables in correct order (respecting foreign keys)
        Schema::dropIfExists('quiz_answers'); // Must drop before quiz_questions
        Schema::dropIfExists('quiz_questions'); // Has foreign key to learning_materials
        Schema::dropIfExists('user_barangays'); // Has foreign key to users
        Schema::dropIfExists('bhw_monthly_report_filters'); // Has foreign key to bhw_monthly_reports
        Schema::dropIfExists('lookup_values'); // No foreign keys
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily rolled back due to data loss
        throw new \Exception('This migration cannot be rolled back. Restore from database backup if needed.');
    }
};
