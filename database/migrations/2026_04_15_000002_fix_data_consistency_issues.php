<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Phase 2: Fix data consistency issues
     * - Standardize symptoms to JSON
     * - Add health_records_enriched view
     * - Refresh stale AOG values
     * - Create archive table for health records
     */
    public function up(): void
    {
        // Step 1: Convert menstruation_records.symptoms to JSON format
        $this->standardizeSymptoms();

        // Step 2: Change column type to JSON
        try {
            Schema::table('menstruation_records', function (Blueprint $table) {
                $table->json('symptoms')->nullable()->change();
            });
        } catch (\Throwable $e) {
        }

        // Step 3: Refresh stale AOG values in pregnancies
        $this->refreshStaleAog();

        // Step 4: Create health_records_enriched view
        $this->createHealthRecordsEnrichedView();

        // Step 5: Create health_records_archived table
        Schema::create('health_records_archived', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('bp');
            $table->decimal('weight', 5, 2);
            $table->integer('heart_rate');
            $table->decimal('temperature', 4, 1);
            $table->text('notes')->nullable();
            $table->enum('risk_level', ['Low', 'Medium', 'High'])->default('Low');
            $table->enum('created_by_role', ['midwife', 'bhw']);
            $table->unsignedBigInteger('recorded_by_id');
            $table->timestamps();
            $table->timestamp('archived_at')->useCurrent();
            $table->string('archived_reason')->nullable();
            
            $table->index('user_id', 'idx_archived_health_records_user_id');
            $table->index('archived_at', 'idx_archived_health_records_date');
        });

        // Step 6: Deduplicate learning_materials
        $this->deduplicateLearningMaterials();

        // Step 7: Add unique constraint to learning_materials
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->unique(['title', 'material_type'], 'idx_unique_title_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove unique constraint
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropUnique('idx_unique_title_type');
        });

        // Drop archive table
        Schema::dropIfExists('health_records_archived');

        // Drop enriched view
        DB::statement('DROP VIEW IF EXISTS health_records_enriched');

        // Revert symptoms to TEXT
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->text('symptoms')->nullable()->change();
        });
    }

    /**
     * Standardize symptoms format to JSON
     */
    protected function standardizeSymptoms(): void
    {
        // Portable PHP-side conversion (works on mysql/pgsql/sqlite, no CONCAT).
        try {
            if (!Schema::hasTable('menstruation_records') || !Schema::hasColumn('menstruation_records', 'symptoms')) {
                return;
            }
            $rows = DB::table('menstruation_records')->whereNotNull('symptoms')->select('id', 'symptoms')->get();
            foreach ($rows as $row) {
                $s = trim((string) $row->symptoms);
                if ($s === '' || str_starts_with($s, '[')) {
                    continue;
                }
                if (str_contains($s, ',')) {
                    $parts = array_values(array_filter(array_map('trim', explode(',', $s))));
                } elseif (str_contains($s, '|')) {
                    $parts = array_values(array_filter(array_map('trim', explode('|', $s))));
                } else {
                    $parts = [$s];
                }
                DB::table('menstruation_records')->where('id', $row->id)->update(['symptoms' => json_encode($parts)]);
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * Refresh stale AOG values
     */
    protected function refreshStaleAog(): void
    {
        // MySQL-only date math — skip on pgsql/sqlite fresh installs.
        // AOG is recalculated at runtime by the app; safe to skip during migrate.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        try {
            DB::table('pregnancies')
                ->whereNull('ended_at')
                ->whereRaw('ABS(aog - ROUND(DATEDIFF(CURDATE(), lmp) / 7)) > 1')
                ->update([
                    'aog' => DB::raw('ROUND(DATEDIFF(CURDATE(), lmp) / 7)'),
                    'updated_at' => now(),
                ]);
        } catch (\Throwable $e) {
        }
    }

    /**
     * Create enriched view for health records
     */
    protected function createHealthRecordsEnrichedView(): void
    {
        $sql = "
            CREATE OR REPLACE VIEW health_records_enriched AS
            SELECT
                hr.*,
                u.role AS recorded_by_role_actual
            FROM health_records hr
            INNER JOIN users u ON hr.recorded_by_id = u.id
        ";
        try {
            DB::statement($sql);
        } catch (\Throwable $e) {
            try {
                DB::statement('DROP VIEW IF EXISTS health_records_enriched');
                DB::statement(preg_replace('/CREATE\s+OR\s+REPLACE\s+VIEW/i', 'CREATE VIEW', $sql));
            } catch (\Throwable $e2) {
            }
        }
    }

    /**
     * Deduplicate learning materials
     */
    protected function deduplicateLearningMaterials(): void
    {
        // Get duplicates
        $duplicates = DB::table('learning_materials')
            ->select('title', 'material_type', DB::raw('MIN(id) as keep_id'))
            ->groupBy('title', 'material_type')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('learning_materials')
                ->where('title', $dup->title)
                ->where('material_type', $dup->material_type)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }
    }
};
