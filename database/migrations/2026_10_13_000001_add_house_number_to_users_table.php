<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structured house number for patient home addresses.
 *
 * Registration / onboarding / profile forms already collect a house
 * number, but it was only ever folded into the free-text `address`
 * string — staff could never filter on it and "missing house number"
 * could not be detected reliably. The composed `address` string keeps
 * working exactly as before; this column is the structured source.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'house_number')) {
                $table->string('house_number', 100)->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'house_number')) {
                $table->dropColumn('house_number');
            }
        });
    }
};
