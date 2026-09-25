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
        Schema::table('fertility_logs', function (Blueprint $table) {
            if (Schema::hasColumn('fertility_logs', 'basal_body_temp')) {
                $table->dropColumn('basal_body_temp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fertility_logs', function (Blueprint $table) {
            $table->decimal('basal_body_temp', 4, 2)->nullable()->after('cervical_mucus');
        });
    }
};
