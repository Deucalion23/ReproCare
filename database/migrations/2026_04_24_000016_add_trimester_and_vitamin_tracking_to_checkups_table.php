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
        Schema::table('checkups', function (Blueprint $table) {
            $table->enum('trimester', ['first', 'second', 'third'])->nullable()->after('status');
            $table->boolean('vitamins_given')->default(false)->after('trimester');
            $table->text('vitamin_notes')->nullable()->after('vitamins_given');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkups', function (Blueprint $table) {
            $table->dropColumn(['trimester', 'vitamins_given', 'vitamin_notes']);
        });
    }
};
