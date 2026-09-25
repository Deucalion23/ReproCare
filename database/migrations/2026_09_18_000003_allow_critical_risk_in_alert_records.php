<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->enum('risk_level', ['Low', 'Medium', 'High', 'Critical'])->default('Low')->change();
        });
        Schema::table('preventive_interventions', function (Blueprint $table) {
            $table->enum('risk_level', ['Low', 'Medium', 'High', 'Critical'])->change();
        });
    }

    public function down(): void
    {
        foreach (['health_records', 'preventive_interventions'] as $name) {
            DB::table($name)->where('risk_level', 'Critical')->update(['risk_level' => 'High']);
            Schema::table($name, function (Blueprint $table) use ($name) {
                $column = $table->enum('risk_level', ['Low', 'Medium', 'High']);
                if ($name === 'health_records') $column->default('Low');
                $column->change();
            });
        }
    }
};
