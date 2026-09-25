<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('patients') && !Schema::hasTable('women')) {
            Schema::rename('patients', 'women');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('women') && !Schema::hasTable('patients')) {
            Schema::rename('women', 'patients');
        }
    }
};
