<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'contact_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('contact_number', 20)->nullable()->after('phone');
            });
        }

        DB::table('users')
            ->whereNull('contact_number')
            ->whereNotNull('phone')
            ->update(['contact_number' => DB::raw('phone')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'contact_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('contact_number');
            });
        }
    }
};
