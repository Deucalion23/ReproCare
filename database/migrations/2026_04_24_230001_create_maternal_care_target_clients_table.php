<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maternal_care_target_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('woman_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->date('date_of_registration')->nullable();
            $table->string('family_serial_no')->nullable();
            $table->date('prenatal_first_trimester_date')->nullable();
            $table->date('prenatal_second_trimester_date')->nullable();
            $table->date('prenatal_third_trimester_date')->nullable();
            $table->date('td1_date')->nullable();
            $table->date('td2_date')->nullable();
            $table->date('td3_date')->nullable();
            $table->date('td4_date')->nullable();
            $table->date('td5_date')->nullable();
            $table->boolean('fim_status')->nullable();
            $table->date('iron_folic_first_visit_date')->nullable();
            $table->unsignedInteger('iron_folic_first_visit_tablets')->nullable();
            $table->date('iron_folic_second_visit_date')->nullable();
            $table->unsignedInteger('iron_folic_second_visit_tablets')->nullable();
            $table->date('iron_folic_third_visit_date')->nullable();
            $table->unsignedInteger('iron_folic_third_visit_tablets')->nullable();
            $table->date('iron_folic_fourth_visit_date')->nullable();
            $table->unsignedInteger('iron_folic_fourth_visit_tablets')->nullable();
            $table->date('calcium_second_visit_date')->nullable();
            $table->unsignedInteger('calcium_second_visit_tablets')->nullable();
            $table->date('calcium_third_visit_date')->nullable();
            $table->unsignedInteger('calcium_third_visit_tablets')->nullable();
            $table->date('calcium_fourth_visit_date')->nullable();
            $table->unsignedInteger('calcium_fourth_visit_tablets')->nullable();
            $table->date('iodine_date')->nullable();
            $table->unsignedInteger('iodine_capsules_given')->nullable();
            $table->enum('nutritional_assessment_status', ['low', 'normal', 'high'])->nullable();
            $table->date('deworming_date')->nullable();
            $table->date('syphilis_screening_date')->nullable();
            $table->enum('syphilis_screening_result', ['positive', 'negative'])->nullable();
            $table->date('hepatitis_b_screening_date')->nullable();
            $table->enum('hepatitis_b_screening_result', ['positive', 'negative'])->nullable();
            $table->date('hiv_screening_date')->nullable();
            $table->date('gestational_diabetes_screening_date')->nullable();
            $table->enum('gestational_diabetes_result', ['positive', 'negative'])->nullable();
            $table->date('cbc_screening_date')->nullable();
            $table->enum('cbc_anemia_status', ['with_anemia', 'without_anemia'])->nullable();
            $table->boolean('cbc_given_iron')->nullable();
            $table->date('pregnancy_terminated_date')->nullable();
            $table->enum('pregnancy_outcome', ['ft', 'pt', 'fd', 'ab'])->nullable();
            $table->enum('pregnancy_outcome_sex', ['M', 'F'])->nullable();
            $table->enum('delivery_type', ['cs', 'vd'])->nullable();
            $table->enum('birth_weight_category', ['low', 'normal', 'unknown'])->nullable();
            $table->string('facility_delivery_type')->nullable();
            $table->string('facility_bemonc_capable')->nullable();
            $table->enum('facility_ownership', ['public', 'private'])->nullable();
            $table->string('non_health_facility_code')->nullable();
            $table->string('birth_attendant_code', 50)->nullable();
            $table->text('delivery_remarks')->nullable();
            $table->date('delivery_date')->nullable();
            $table->time('delivery_time')->nullable();
            $table->date('postpartum_within_24_hours_date')->nullable();
            $table->date('postpartum_within_7_days_date')->nullable();
            $table->string('postpartum_iron_first_month')->nullable();
            $table->string('postpartum_iron_second_month')->nullable();
            $table->string('postpartum_iron_third_month')->nullable();
            $table->date('vitamin_a_date')->nullable();
            $table->text('postpartum_remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maternal_care_target_clients');
    }
};
