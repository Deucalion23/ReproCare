<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Optional precise coordinates for GIS heat mapping. When empty,
     * the map falls back to deterministic approximate positions
     * around San Carlos City.
     */
    public function up(): void
    {
        Schema::table('puroks', function (Blueprint $table) {
            if (!Schema::hasColumn('puroks', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('barangay');
            }
            if (!Schema::hasColumn('puroks', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('puroks', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
