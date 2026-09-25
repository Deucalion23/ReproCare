<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            INSERT INTO health_records (
                woman_id,
                pregnancy_id,
                bp,
                weight,
                height,
                bmi,
                heart_rate,
                temperature,
                gestational_age,
                smoking_status,
                alcohol_use,
                drug_use,
                lifestyle_notes,
                obstetric_history,
                notes,
                risk_level,
                risk_assessment_mode,
                risk_notes,
                created_at,
                updated_at
            )
            SELECT
                p.woman_id,
                p.id,
                p.blood_pressure,
                p.weight,
                p.height,
                p.bmi,
                NULL,
                NULL,
                p.aog,
                p.smoking_status,
                p.alcohol_status,
                p.drug_use_status,
                p.lifestyle_notes,
                p.obstetric_history,
                p.notes,
                COALESCE(p.risk_level, 'Low'),
                COALESCE(p.risk_assessment_mode, 'automatic'),
                p.risk_notes,
                p.created_at,
                p.updated_at
            FROM pregnancies p
            LEFT JOIN health_records hr
                ON hr.pregnancy_id = p.id
            WHERE hr.id IS NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            DELETE hr
            FROM health_records hr
            INNER JOIN pregnancies p ON p.id = hr.pregnancy_id
            WHERE hr.heart_rate IS NULL
              AND hr.temperature IS NULL
              AND hr.created_at = p.created_at
              AND (hr.bp <=> p.blood_pressure)
              AND (hr.weight <=> p.weight)
              AND (hr.height <=> p.height)
              AND (hr.bmi <=> p.bmi)
              AND (hr.gestational_age <=> p.aog)
              AND (hr.smoking_status <=> p.smoking_status)
              AND (hr.alcohol_use <=> p.alcohol_status)
              AND (hr.drug_use <=> p.drug_use_status)
              AND (hr.lifestyle_notes <=> p.lifestyle_notes)
              AND (hr.obstetric_history <=> p.obstetric_history)
              AND (hr.notes <=> p.notes)
              AND (hr.risk_level <=> p.risk_level)
              AND (hr.risk_assessment_mode <=> p.risk_assessment_mode)
              AND (hr.risk_notes <=> p.risk_notes)
        ");
    }
};
