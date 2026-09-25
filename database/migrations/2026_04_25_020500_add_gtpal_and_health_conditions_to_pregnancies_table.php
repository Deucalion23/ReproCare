<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            if (!Schema::hasColumn('pregnancies', 'gtpal_term')) {
                $table->unsignedInteger('gtpal_term')->default(0)->after('para');
            }
            if (!Schema::hasColumn('pregnancies', 'gtpal_preterm')) {
                $table->unsignedInteger('gtpal_preterm')->default(0)->after('gtpal_term');
            }
            if (!Schema::hasColumn('pregnancies', 'gtpal_abortions')) {
                $table->unsignedInteger('gtpal_abortions')->default(0)->after('gtpal_preterm');
            }
            if (!Schema::hasColumn('pregnancies', 'gtpal_living_children')) {
                $table->unsignedInteger('gtpal_living_children')->default(0)->after('gtpal_abortions');
            }
            if (!Schema::hasColumn('pregnancies', 'health_conditions')) {
                $table->json('health_conditions')->nullable()->after('gtpal_living_children');
            }
            if (!Schema::hasColumn('pregnancies', 'health_condition_other')) {
                $table->string('health_condition_other')->nullable()->after('health_conditions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $columns = [
                'gtpal_term',
                'gtpal_preterm',
                'gtpal_abortions',
                'gtpal_living_children',
                'health_conditions',
                'health_condition_other',
            ];

            $existing = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('pregnancies', $column)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
