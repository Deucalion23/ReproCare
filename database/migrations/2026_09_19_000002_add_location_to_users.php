<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('users', 'address_label')) {
                $table->string('address_label', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'address_label']);
        });
    }
};
