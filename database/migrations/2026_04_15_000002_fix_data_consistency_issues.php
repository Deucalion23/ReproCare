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
        Schema::table('menstruation_records', function (Blueprint $table) {
            $table->json('symptoms')->nullable()->change();
        });

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
        // Convert comma-separated symptoms to JSON array
        $affected = DB::statement("
            UPDATE menstruation_records
            SET symptoms = CONCAT('[\"', REPLACE(symptoms, ',', '\",\"'), '\"]')
            WHERE symptoms IS NOT NULL 
              AND symptoms LIKE '%,%'
              AND symptoms NOT LIKE '[%'
        ");

        // Convert pipe-separated symptoms to JSON array
        DB::statement("
            UPDATE menstruation_records
            SET symptoms = CONCAT('[\"', REPLACE(symptoms, '|', '\",\"'), '\"]')
            WHERE symptoms IS NOT NULL 
              AND symptoms LIKE '%|%'
              AND symptoms NOT LIKE '[%'
        ");

        // Wrap single symptoms in JSON array
        DB::statement("
            UPDATE menstruation_records
            SET symptoms = CONCAT('[\"', symptoms, '\"]')
            WHERE symptoms IS NOT NULL 
              AND symptoms NOT LIKE '[%'
              AND symptoms NOT LIKE '%,%'
              AND symptoms NOT LIKE '%|%'
        ");
    }

    /**
     * Refresh stale AOG values
     */
    protected function refreshStaleAog(): void
    {
        DB::table('pregnancies')
            ->whereNull('ended_at')
            ->whereRaw('ABS(aog - ROUND(DATEDIFF(CURDATE(), lmp) / 7)) > 1')
            ->update([
                'aog' => DB::raw('ROUND(DATEDIFF(CURDATE(), lmp) / 7)'),
                'updated_at' => now(),
            ]);
    }

    /**
     * Create enriched view for health records
     */
    protected function createHealthRecordsEnrichedView(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW health_records_enriched AS
            SELECT 
                hr.*,
                u.role AS recorded_by_role_actual
            FROM health_records hr
            INNER JOIN users u ON hr.recorded_by_id = u.id
        ");
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
