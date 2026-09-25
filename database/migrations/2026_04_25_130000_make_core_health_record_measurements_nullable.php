<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->string('bp')->nullable()->change();
            $table->decimal('weight', 5, 2)->nullable()->change();
            $table->integer('heart_rate')->nullable()->change();
            $table->decimal('temperature', 4, 1)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->string('bp')->nullable(false)->change();
            $table->decimal('weight', 5, 2)->nullable(false)->change();
            $table->integer('heart_rate')->nullable(false)->change();
            $table->decimal('temperature', 4, 1)->nullable(false)->change();
        });
    }
};
