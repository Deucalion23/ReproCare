<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            // Laravel generates invalid SQL for enum widening on Postgres
            // ("alter column .. type varchar .. check (..)" is a syntax
            // error), so drop the old CHECK and widen to plain VARCHAR.
            // Data, defaults and nullability are preserved.
            foreach (['health_records', 'preventive_interventions'] as $table) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'risk_level')) {
                    continue;
                }
                try {
                    DB::statement(sprintf('ALTER TABLE "%s" DROP CONSTRAINT IF EXISTS "%s_risk_level_check"', $table, $table));
                } catch (\Throwable $e) {
                }
                try {
                    DB::statement(sprintf('ALTER TABLE "%s" ALTER COLUMN "risk_level" TYPE varchar(255)', $table));
                } catch (\Throwable $e) {
                }
            }
            return;
        }
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
            if (DB::getDriverName() !== 'mysql') {
                continue;
            }
            Schema::table($name, function (Blueprint $table) use ($name) {
                $column = $table->enum('risk_level', ['Low', 'Medium', 'High']);
                if ($name === 'health_records') $column->default('Low');
                $column->change();
            });
        }
    }
};
