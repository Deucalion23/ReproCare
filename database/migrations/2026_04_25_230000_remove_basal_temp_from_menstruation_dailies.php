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
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            if (Schema::hasColumn('menstruation_dailies', 'basal_temp')) {
                $table->dropColumn('basal_temp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menstruation_dailies', function (Blueprint $table) {
            $table->decimal('basal_temp', 4, 2)->nullable()->after('discharge');
        });
    }
};
