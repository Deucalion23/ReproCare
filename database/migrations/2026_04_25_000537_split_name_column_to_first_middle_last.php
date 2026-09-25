<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('id');
            $table->string('middle_initial')->nullable()->after('first_name');
            $table->string('last_name')->after('middle_initial');
        });

        // Migrate existing data: split name into first_name, middle_initial, last_name
        DB::statement("UPDATE users SET 
            first_name = SUBSTRING_INDEX(name, ' ', 1),
            last_name = CASE 
                WHEN LOCATE(' ', name) > 0 THEN SUBSTRING_INDEX(SUBSTRING_INDEX(name, ' ', -2), ' ', 1)
                ELSE ''
            END,
            middle_initial = CASE 
                WHEN (LENGTH(name) - LENGTH(REPLACE(name, ' ', ''))) >= 2 THEN UPPER(SUBSTRING(SUBSTRING_INDEX(SUBSTRING_INDEX(name, ' ', -2), ' ', 1), 1, 1))
                ELSE NULL
            END
        WHERE name IS NOT NULL AND name != ''");

        // Update last_name to get the actual last word
        DB::statement("UPDATE users SET 
            last_name = SUBSTRING_INDEX(name, ' ', -1)
        WHERE name IS NOT NULL AND name != ''");

        // Drop the old name column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
        });

        // Reconstruct name from first_name, middle_initial, last_name
        DB::statement("UPDATE users SET 
            name = CONCAT(
                COALESCE(first_name, ''), 
                CASE WHEN middle_initial IS NOT NULL AND middle_initial != '' THEN CONCAT(' ', middle_initial, '.') ELSE '' END, 
                ' ', 
                COALESCE(last_name, '')
            )
        WHERE first_name IS NOT NULL OR last_name IS NOT NULL");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_initial', 'last_name']);
        });
    }
};
