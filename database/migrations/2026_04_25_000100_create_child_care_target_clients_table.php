<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_care_target_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->unique()->constrained('child_records')->cascadeOnDelete();
            $table->date('date_of_registration')->nullable();
            $table->string('family_serial_number')->nullable();
            $table->boolean('cpab_tt2_tt4')->nullable();
            $table->boolean('cpab_tt3_tt5')->nullable();
            $table->enum('newborn_status', ['low', 'normal', 'unknown'])->nullable();
            $table->date('breastfeeding_initiated_date')->nullable();
            $table->date('bcg_date')->nullable();
            $table->date('hepa_b_bd_date')->nullable();
            $table->string('assessment_1_3_age_months')->nullable();
            $table->decimal('assessment_1_3_length_cm', 6, 2)->nullable();
            $table->date('assessment_1_3_length_date')->nullable();
            $table->decimal('assessment_1_3_weight_kg', 6, 2)->nullable();
            $table->date('assessment_1_3_weight_date')->nullable();
            $table->enum('assessment_1_3_status', ['S', 'W-MAM', 'W-SAM', 'O', 'N'])->nullable();
            $table->date('low_birth_weight_iron_1_month_date')->nullable();
            $table->date('low_birth_weight_iron_2_month_date')->nullable();
            $table->date('low_birth_weight_iron_3_month_date')->nullable();
            $table->date('dpt_hepb_hib_1_date')->nullable();
            $table->date('dpt_hepb_hib_2_date')->nullable();
            $table->date('dpt_hepb_hib_3_date')->nullable();
            $table->date('opv_1_date')->nullable();
            $table->date('opv_2_date')->nullable();
            $table->date('opv_3_date')->nullable();
            $table->date('pcv_1_date')->nullable();
            $table->date('pcv_2_date')->nullable();
            $table->date('pcv_3_date')->nullable();
            $table->date('ipv_1_date')->nullable();
            $table->boolean('exclusive_breastfeeding_1_5_months')->nullable();
            $table->boolean('exclusive_breastfeeding_2_5_months')->nullable();
            $table->boolean('exclusive_breastfeeding_3_5_months')->nullable();
            $table->boolean('exclusive_breastfeeding_4_5_months')->nullable();
            $table->boolean('exclusive_breastfeeding_5_9_months')->nullable();
            $table->string('assessment_6_11_age_months')->nullable();
            $table->decimal('assessment_6_11_length_cm', 6, 2)->nullable();
            $table->date('assessment_6_11_length_date')->nullable();
            $table->decimal('assessment_6_11_weight_kg', 6, 2)->nullable();
            $table->date('assessment_6_11_weight_date')->nullable();
            $table->enum('assessment_6_11_status', ['S', 'W-MAM', 'W-SAM', 'O', 'N'])->nullable();
            $table->boolean('exclusive_breastfed_up_to_6_months')->nullable();
            $table->boolean('complementary_feeding_introduced')->nullable();
            $table->date('vitamin_a_date')->nullable();
            $table->date('mnp_date')->nullable();
            $table->unsignedInteger('mnp_sachets_given')->nullable();
            $table->date('mmr_1_date')->nullable();
            $table->date('ipv_2_date')->nullable();
            $table->string('assessment_12_age_months')->nullable();
            $table->decimal('assessment_12_length_cm', 6, 2)->nullable();
            $table->date('assessment_12_length_date')->nullable();
            $table->decimal('assessment_12_weight_kg', 6, 2)->nullable();
            $table->date('assessment_12_weight_date')->nullable();
            $table->enum('assessment_12_status', ['S', 'W-MAM', 'W-SAM', 'O', 'N'])->nullable();
            $table->date('mmr_2_date')->nullable();
            $table->date('fic_date')->nullable();
            $table->date('cic_date')->nullable();
            $table->boolean('man_admitted_sfp')->nullable();
            $table->boolean('man_cured')->nullable();
            $table->boolean('man_defaulted')->nullable();
            $table->boolean('man_died')->nullable();
            $table->boolean('sam_admitted_otc')->nullable();
            $table->boolean('sam_cured')->nullable();
            $table->boolean('sam_defaulted')->nullable();
            $table->boolean('sam_died')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_care_target_clients');
    }
};
