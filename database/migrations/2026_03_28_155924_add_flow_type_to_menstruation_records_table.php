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
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->enum('flow_type', ['normal', 'heavy', 'irregular'])->default('normal')->after('end_date');
            $table->text('notes')->nullable()->after('symptoms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->dropColumn('flow_type');
            $table->dropColumn('notes');
        });
    }
};
