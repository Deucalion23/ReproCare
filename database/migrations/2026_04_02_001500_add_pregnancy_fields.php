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
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->boolean('is_high_risk')->default(false)->after('para');
            $table->text('notes')->nullable()->after('is_high_risk');
            $table->date('ended_at')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropColumn(['is_high_risk', 'notes', 'ended_at']);
        });
    }
};
