<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('family_planning_records', function (Blueprint $table) {
            $table->foreignId('pregnancy_id')->nullable()->after('user_id')->constrained('pregnancies')->nullOnDelete();
            $table->boolean('is_postpartum_plan')->default(false)->after('pregnancy_id');
            $table->enum('postpartum_start_timing', ['immediately_after_delivery', 'before_discharge', 'six_week_visit', 'undecided'])->nullable()->after('is_postpartum_plan');
            $table->index(['pregnancy_id', 'is_postpartum_plan']);
        });
    }

    public function down(): void
    {
        Schema::table('family_planning_records', function (Blueprint $table) {
            $table->dropIndex(['pregnancy_id', 'is_postpartum_plan']);
            $table->dropConstrainedForeignId('pregnancy_id');
            $table->dropColumn(['is_postpartum_plan', 'postpartum_start_timing']);
        });
    }
};
