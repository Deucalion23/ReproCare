<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            // Add multiple dates for 2nd trimester
            $table->date('prenatal_second_trimester_date_1')->nullable()->after('prenatal_second_trimester_date');
            $table->date('prenatal_second_trimester_date_2')->nullable()->after('prenatal_second_trimester_date_1');

            // Add multiple dates for 3rd trimester
            $table->date('prenatal_third_trimester_date_1')->nullable()->after('prenatal_third_trimester_date');
            $table->date('prenatal_third_trimester_date_2')->nullable()->after('prenatal_third_trimester_date_1');
            $table->date('prenatal_third_trimester_date_3')->nullable()->after('prenatal_third_trimester_date_2');
            $table->date('prenatal_third_trimester_date_4')->nullable()->after('prenatal_third_trimester_date_3');
            $table->date('prenatal_third_trimester_date_5')->nullable()->after('prenatal_third_trimester_date_4');
        });
    }

    public function down(): void
    {
        Schema::table('maternal_care_target_clients', function (Blueprint $table) {
            $table->dropColumn([
                'prenatal_second_trimester_date_1',
                'prenatal_second_trimester_date_2',
                'prenatal_third_trimester_date_1',
                'prenatal_third_trimester_date_2',
                'prenatal_third_trimester_date_3',
                'prenatal_third_trimester_date_4',
                'prenatal_third_trimester_date_5',
            ]);
        });
    }
};
