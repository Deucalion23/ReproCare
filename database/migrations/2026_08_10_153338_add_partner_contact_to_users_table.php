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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'partner_name')) {
                $table->string('partner_name')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'partner_contact')) {
                $table->string('partner_contact')->nullable()->after('partner_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter(['partner_name', 'partner_contact'], fn ($column) => Schema::hasColumn('users', $column));

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
