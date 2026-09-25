<?php

namespace App\Services;

use App\Models\User;
use App\Models\Checkup;
use App\Models\HealthRecord;
use App\Models\Notification;
use App\Models\PreventiveIntervention;
use Carbon\Carbon;

class RiskAnalysisService
{
    /**
     * Evaluate and update risk level for a patient based on multi-attribute clinical parameters.
     * Returns risk level: 'Low', 'Medium', 'High', or 'Critical'
     */
    public function evaluate(int $patientId): string
    {
        $patient = User::with(['checkups', 'healthRecords', 'pregnancies'])->find($patientId);
        if (!$patient) return 'Low';

        $score  = 0;
        $alerts = [];
        $recommendations = [];

        // ── 1. Maternal Age Assessment ─────────────────────────
        $age = $patient->age;
        if ($age !== null) {
            if ($age < 19) {
                $score += 3;
                $alerts[] = "Adolescent/Teenage pregnancy detected (Age: {$age}).";
                $recommendations[] = "👩‍⚕️ Adolescent Health Protocol: Provide supportive nutrition counseling, psychosocial support, and birth preparedness.";
            } elseif ($age >= 35) {
                $score += 2;
                $alerts[] = "Advanced maternal age (Age: {$age} years).";
                $recommendations[] = "🧬 Advanced Age Monitoring: Screen for gestational diabetes, chromosomal anomalies, and hypertensive disorders.";
            }
        }

        // ── 1b. Short Maternal Stature (< 4 ft / 121.92 cm) ───
        // Reads the latest health record first, then the active pregnancy,
        // so the flag follows the woman onto every risk page.
        $heightCm = $patient->healthRecords()->latest('id')->value('height')
            ?? $patient->pregnancies()->active()->first()?->height ?? null;
        if (\App\Services\MaternalRiskService::isShortStature($heightCm)) {
            $score += 2;
            $cm = (float) $heightCm;
            $alerts[] = "Short maternal stature detected (Height: {$cm} cm, below 4 ft).";
            $recommendations[] = "📏 Short Stature Protocol: Higher risk of cephalopelvic disproportion and obstructed labor — plan facility-based birth with surgical capacity and assess fetal size at each visit.";
        }

        // ── 2. Missed & Overdue Checkups ──────────────────────
        $missedCount = $patient->checkups()->missed()->count();
        if ($missedCount >= 3) {
            $score += 4;
            $alerts[] = "Patient has {$missedCount} missed checkups (high care gap).";
            $recommendations[] = "🚨 High Care Gap: Direct BHW home visitation required to reconnect patient with prenatal care.";
        } elseif ($missedCount >= 1) {
            $score += 2;
            $alerts[] = "Patient has {$missedCount} missed checkup(s).";
            $recommendations[] = "📅 Reschedule missed appointment as soon as possible.";
        }

        $overdueCount = $patient->checkups()
            ->where('status', 'Scheduled')
            ->whereDate('scheduled_date', '<', today())
            ->count();
        if ($overdueCount > 0) {
            $score += 2;
            $alerts[] = "{$overdueCount} scheduled checkup(s) are overdue.";
        }

        // ── 3. Blood Pressure Trends (Preeclampsia Assessment) ─
        $latestRecord = $patient->healthRecords()->latest('id')->first();
        if ($latestRecord && ($latestRecord->risk_assessment_mode ?? 'automatic') === 'manual') {
            $level = $latestRecord->risk_level ?? 'Low';
            app(SmartNotificationService::class)->notifyHighRisk($patient,
                $latestRecord->recommendations ?: 'Your health worker has flagged a concern. Please contact them for follow-up.', $level);
            return $level;
        }

        if ($latestRecord && $latestRecord->bp) {
            $bp = $this->parseBP($latestRecord->bp);
            // High band is CHO-configurable (settings: threshold.bp_systolic_high / threshold.bp_diastolic_high).
            $sysHigh = \App\Models\Setting::getFloat('threshold.bp_systolic_high', 140);
            $diaHigh = \App\Models\Setting::getFloat('threshold.bp_diastolic_high', 90);
            if ($bp) {
                if ($bp['systolic'] >= 160 || $bp['diastolic'] >= 110) {
                    $score += 6;
                    $alerts[] = "Severe hypertension / Preeclampsia crisis detected: {$latestRecord->bp} mmHg.";
                    $recommendations[] = "🚨 Critical BP: Immediate hospital referral for emergency antihypertensive therapy and preeclampsia workup.";
                } elseif ($bp['systolic'] >= $sysHigh || $bp['diastolic'] >= $diaHigh) {
                    $score += 4;
                    $alerts[] = "High blood pressure (Hypertension Stage 2): {$latestRecord->bp} mmHg.";
                    $recommendations[] = "🩺 Preeclampsia Protocol: Check for proteinuria, monitor BP daily, educate on warning signs (severe headache, visual disturbances, epigastric pain).";
                } elseif ($bp['systolic'] >= 130 || $bp['diastolic'] >= 80) {
                    $score += 2;
                    $alerts[] = "Elevated blood pressure: {$latestRecord->bp} mmHg.";
                    $recommendations[] = "🩺 Weekly BP checks recommended. Emphasize low-sodium diet and adequate rest.";
                }
            }
        }

        // ── 4. Hemoglobin & Anemia Evaluation ─────────────────
        // Anemia cutoff is CHO-configurable (settings: threshold.hemoglobin_low).
        if ($latestRecord && $latestRecord->hemoglobin) {
            $hb = (float)$latestRecord->hemoglobin;
            $hbLow = \App\Models\Setting::getFloat('threshold.hemoglobin_low', 10);
            if ($hb < 7.0) {
                $score += 5;
                $alerts[] = "Severe anemia detected: {$hb} g/dL (Hb < 7.0).";
                $recommendations[] = "🩸 Urgent Anemia Alert: Immediate referral for parenteral iron or blood transfusion preparation before delivery.";
            } elseif ($hb < $hbLow) {
                $score += 3;
                $alerts[] = "Anemia detected: {$hb} g/dL (Hb < {$hbLow}).";
                $recommendations[] = "🥗 Iron Supplementation: Increase oral ferrous sulfate + folic acid with Vitamin C. Repeat CBC in 2-4 weeks.";
            }
        }

        // ── 5. Obstetric History & Previous Complications ──────
        $obstetricHistory = strtolower((string)($latestRecord->medical_history ?? $patient->medical_history ?? ''));
        if (!empty($obstetricHistory)) {
            $highRiskComplications = ['preeclampsia', 'eclampsia', 'hemorrhage', 'stillbirth', 'postpartum hemorrhage', 'abruptio', 'placenta previa'];
            $moderateComplications = ['cesarean', 'c-section', 'miscarriage', 'preterm', 'premature'];

            foreach ($highRiskComplications as $comp) {
                if (str_contains($obstetricHistory, $comp)) {
                    $score += 4;
                    $alerts[] = "History of severe complication: " . ucfirst($comp);
                    $recommendations[] = "🏥 High-Risk Obstetric History: Plan facility-based birth at secondary/tertiary hospital with surgical capacity.";
                    break;
                }
            }

            foreach ($moderateComplications as $comp) {
                if (str_contains($obstetricHistory, $comp)) {
                    $score += 2;
                    $alerts[] = "History of prior delivery event: " . ucfirst($comp);
                    $recommendations[] = "📋 Prior Complication Note: Close monitoring of uterine activity and labor onset required.";
                    break;
                }
            }
        }

        // ── 6. Gestational Age & Third Trimester Status ───────
        $activePregnancy = $patient->pregnancies()->active()->first();
        if ($activePregnancy) {
            $aog = (int)($activePregnancy->aog_weeks ?? 0);
            // Post-term flag is CHO-configurable (settings: threshold.gestational_age_max).
            $postTerm = \App\Models\Setting::getInt('threshold.gestational_age_max', 42);
            if ($aog >= $postTerm) {
                $score += 6;
                $alerts[] = "Post-term pregnancy ({$aog} weeks, threshold {$postTerm} weeks).";
                $recommendations[] = "🏥 Post-Term Protocol: Urgent referral for induction assessment and fetal surveillance (NST/BPP).";
            }
            if ($aog >= 36) {
                $daysSinceCheckup = $patient->checkups()
                    ->completed()
                    ->where('scheduled_date', '>=', Carbon::now()->subDays(14))
                    ->count();
                if ($daysSinceCheckup === 0) {
                    $score += 3;
                    $alerts[] = "Third trimester pregnancy ({$aog} weeks) with no checkup in the last 14 days.";
                    $recommendations[] = "🤰 Late Gestation Protocol: Schedule weekly checkup, assess fetal presentation, and verify facility transport readiness.";
                }
            }
        }

        // ── Determine Overall Risk Tier ────────────────────────
        if ($score >= 8) {
            $riskLevel = 'Critical';
        } elseif ($score >= 5) {
            $riskLevel = 'High';
        } elseif ($score >= 2) {
            $riskLevel = 'Medium';
        } else {
            $riskLevel = 'Low';
        }

        // ── Single atomic record update (risk + recommendations together) ──
        if ($latestRecord) {
            $latestRecord->update([
                'risk_level' => $riskLevel,
                'recommendations' => !empty($recommendations) ? implode("\n", array_unique($recommendations)) : $latestRecord->recommendations,
            ]);
        }

        // ── Trigger Preventive Interventions ────────────────────
        if ($riskLevel !== 'Low') {
            $this->triggerPreventiveInterventions($patientId, $riskLevel, $alerts, $recommendations);
        }

        // ── Trigger Smart Notifications if Medium/High ─────────
        if ($riskLevel !== 'Low' && !empty($alerts)) {
            $notificationService = new SmartNotificationService();
            $notificationService->notifyHighRisk($patient, implode(' ', $alerts), $riskLevel);
        } elseif ($riskLevel === 'Low') {
            app(SmartNotificationService::class)->resolveRisk($patientId);
        }

        return $riskLevel;
    }

    /**
     * Prioritize patients across the system based on risk and other factors.
     * Pure read-only sort: uses stored risk levels, never re-evaluates
     * (evaluate() sends notifications/SMS and creates follow-ups).
     * Returns an ordered collection: highest priority first
     */
    public function prioritize(array $patientIds)
    {
        $patients = User::whereIn('id', $patientIds)->with(['pregnancies', 'checkups', 'healthRecords'])->get();

        $scored = $patients->map(function ($patient) {
            $risk = $patient->healthRecords->first()?->risk_level
                ?? $patient->pregnancies->first()?->risk_level ?? 'Low';
            $score = 0;

            switch ($risk) {
                case 'Critical': $score += 15; break;
                case 'High':     $score += 10; break;
                case 'Medium':   $score += 5; break;
                default:         $score += 1; break;
            }

            // Teenage pregnancy weight
            if ($patient->age !== null && $patient->age < 19) {
                $score += 5;
            }

            // Missed appointments weight
            $missed = $patient->checkups()->missed()->count();
            $score += min(6, $missed * 2);

            // Days until expected delivery (priority if within 14 days)
            $preg = $patient->pregnancies()->active()->first();
            if ($preg && $preg->expected_delivery_date) {
                $days = now()->diffInDays($preg->expected_delivery_date, false);
                if ($days >= 0 && $days <= 14) {
                    $score += 8;
                } elseif ($days >= 0 && $days <= 30) {
                    $score += 4;
                }
            }

            return ['patient' => $patient, 'risk_level' => $risk, 'priority_score' => $score];
        });

        return collect($scored)->sortByDesc('priority_score')->values();
    }

    /**
     * Trigger preventive interventions and log them
     */
    private function triggerPreventiveInterventions(int $patientId, string $riskLevel, array $alerts, array $recommendations): void
    {
        // One open episode per patient+level: update the reason text instead of
        // spawning a new row on every wording change.
        $existing = PreventiveIntervention::where('user_id', $patientId)
            ->where('risk_level', $riskLevel)->where('status', 'triggered')->latest('id')->first();
        if ($existing) {
            $existing->update([
                'trigger_reason' => implode('; ', $alerts),
                'recommendations' => implode("\n", array_unique($recommendations)),
                'intervention_type' => $this->determineInterventionType($alerts),
            ]);
        } else {
            PreventiveIntervention::create([
                'user_id' => $patientId,
                'risk_level' => $riskLevel,
                'trigger_reason' => implode('; ', $alerts),
                'recommendations' => implode("\n", array_unique($recommendations)),
                'intervention_type' => $this->determineInterventionType($alerts),
                'status' => 'triggered',
                'triggered_at' => now(),
            ]);
        }

        // Create follow-up tasks based on risk level
        if ($riskLevel === 'Critical') {
            $this->createFollowUpCheckup($patientId, 1, 'URGENT CRITICAL: Immediate clinical follow-up required within 24 hours');
        } elseif ($riskLevel === 'High') {
            $this->createFollowUpCheckup($patientId, 2, 'Urgent: High risk patient follow-up required within 48 hours');
        } elseif ($riskLevel === 'Medium') {
            $this->createFollowUpCheckup($patientId, 7, 'Medium risk patient follow-up recommended within 1 week');
        }
    }

    /**
     * Determine intervention type based on alerts
     */
    private function determineInterventionType(array $alerts): string
    {
        $text = strtolower(implode(' ', $alerts));
        if (str_contains($text, 'hypertension') || str_contains($text, 'blood pressure') || str_contains($text, 'preeclampsia')) {
            return 'hypertension_management';
        }
        if (str_contains($text, 'anemia') || str_contains($text, 'hemoglobin')) {
            return 'anemia_prevention';
        }
        if (str_contains($text, 'teenage') || str_contains($text, 'adolescent')) {
            return 'adolescent_care';
        }
        if (str_contains($text, 'checkup') || str_contains($text, 'gap')) {
            return 'care_continuity';
        }
        if (str_contains($text, 'trimester') || str_contains($text, 'gestation')) {
            return 'pregnancy_monitoring';
        }
        if (str_contains($text, 'stature') || str_contains($text, 'disproportion')) {
            return 'pregnancy_monitoring';
        }
        return 'general_health';
    }

    /**
     * Create automatic follow-up checkup if one is not already scheduled
     */
    private function createFollowUpCheckup(int $patientId, int $daysFromNow, string $notes): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($patientId, $daysFromNow, $notes) {
            $locked = Checkup::where('user_id', $patientId)->where('status', 'Scheduled')
                ->whereDate('scheduled_date', '>=', today())->lockForUpdate()->exists();
            if ($locked) {
                return;
            }
            $inWindow = Checkup::where('user_id', $patientId)
                ->whereDate('scheduled_date', '>=', today())
                ->whereDate('scheduled_date', '<=', today()->addDays($daysFromNow))
                ->where('status', 'Scheduled')
                ->exists();
            if (!$inWindow) {
                Checkup::create([
                    'user_id' => $patientId,
                    'scheduled_date' => now()->addDays($daysFromNow),
                    'status' => 'Scheduled',
                    'purpose' => 'Follow-up: Risk-based clinical care',
                    'notes' => $notes,
                    'scheduled_by_id' => User::find($patientId)?->healthRecords()->whereNotNull('recorded_by_id')->latest('id')->value('recorded_by_id'),
                ]);
            }
        });
    }

    /**
     * Parse BP string "120/80" → ['systolic' => 120, 'diastolic' => 80]
     */
    private function parseBP(string $bp): ?array
    {
        $parts = explode('/', $bp);
        if (count($parts) === 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]))) {
            return [
                'systolic'  => (int)trim($parts[0]),
                'diastolic' => (int)trim($parts[1]),
            ];
        }
        return null;
    }
}
