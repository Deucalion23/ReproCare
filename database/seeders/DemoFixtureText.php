<?php

namespace Database\Seeders;

/**
 * Plain clinical wording for the fictional demo caseload.
 *
 * The demo seeders and the cleanup migration both read from here, so a freshly
 * seeded database and the rows already sitting on the deployed database end up
 * saying exactly the same thing.
 */
final class DemoFixtureText
{
    /** Scenario keys appear in notes, purposes and notification bodies. */
    private const NOTES = [
        'onboarding' => 'Newly registered; waiting for the first antenatal visit.',
        'regular_cycle' => 'Regular 28-day cycle, no complaints reported.',
        'irregular_cycle' => 'Irregular cycle pattern recorded for follow-up.',
        'early_pregnancy' => 'Antenatal booking in the first trimester.',
        'second_trimester' => 'Routine follow-up in the second trimester.',
        'mid_pregnancy' => 'Routine follow-up in the second trimester.',
        'late_pregnancy' => 'Third-trimester check, due date approaching.',
        'high_risk' => 'High-risk review: blood pressure raised, follow-up scheduled.',
        'high_risk_pregnancy' => 'High-risk review: blood pressure raised, follow-up scheduled.',
        'pregnancy_review' => 'Pregnancy record submitted for barangay review.',
        'pregnancy_revision' => 'Pregnancy record returned to the BHW for correction.',
        'postpartum' => 'Postpartum check within the first week after delivery.',
        'postpartum_alert' => 'Postpartum alert reviewed; mother and newborn stable.',
        'referral' => 'Referred to the RHU for specialist antenatal care.',
        'family_planning' => 'Family-planning counselling session recorded.',
        'fp_and_cycle' => 'Family planning with cycle tracking recorded together.',
        'forum_active' => 'Active in the community board, routine visit recorded.',
        'analytics_data' => 'Several visits recorded for the health dashboard.',
    ];

    /** Partner names for the demo women; picked by row id so re-runs are stable. */
    private const PARTNERS = [
        'Rommel Villanueva', 'Nestor Abad', 'Arnel Bautista', 'Marvin Domingo',
        'Edwin Ferrer', 'Rolando Garcia', 'Benjie Herrera', 'Nonoy Isidro',
        'Cesar Lorenzo', 'Dante Mercado', 'Fernando Navarro', 'Ignacio Ocampo',
        'Remigio Pascual', 'Teodorico Quizon', 'Manuel Rivera', 'Godofredo Soriano',
        'Ernesto Tolentino', 'Alfredo Villanueva', 'Rey Amargo', 'Jun Espejo',
    ];

    /** Sentences that only ever made sense as fixture labels. */
    private const REPLACEMENTS = [
        'Initial low-risk assessment created for local testing.'
            => 'Initial low-risk assessment; routine antenatal follow-up planned.',
        'Fictional test message about scheduling.'
            => 'I think I might be pregnant and would like to book my first prenatal visit. Which days can I come to the RHU?',
        'Fictional household visit' => 'Household visit to confirm the next antenatal checkup.',
        'Fictional inventory scenario' => 'Stock for the barangay prenatal day.',
        'Cycle logged for digital menstrual tracker testing' => 'Cycle logged in the menstrual tracker.',
        'Cycle logged for menstrual tracker testing' => 'Cycle logged in the menstrual tracker.',
        'Sample walk-in patient generated for testing.'
            => 'Walk-in patient registered during the barangay health day.',
        'Monthly health check' => 'Monthly health check',
        ' for analytics dashboard' => '',
        ' for analytics' => '',
    ];

    public static function note(string $scenario): string
    {
        return self::NOTES[$scenario] ?? 'Recorded during the barangay health visit.';
    }

    public static function partnerName(int|string $seed): string
    {
        return self::PARTNERS[abs((int) $seed) % count(self::PARTNERS)];
    }

    /**
     * Turn one stored fixture string into the text a real record would carry.
     * Returns the value unchanged when there is nothing to rewrite, so callers
     * can cheaply skip rows that were never tagged.
     */
    public static function strip(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $clean = str_replace(['[TEST] ', '[TEST]'], ['', ''], $value);
        $clean = strtr($clean, self::REPLACEMENTS);
        $clean = trim(preg_replace('/\s{2,}/', ' ', $clean));

        // "Health record for early_pregnancy" / "notification for early_pregnancy"
        // and bare scenario keys all collapse to the same readable sentence.
        if (preg_match('/^(?:Health record for|Fictional notification for|Monthly report)?\s*([a-z][a-z_]+)$/i', $clean, $m)
            && isset(self::NOTES[$m[1]])) {
            $clean = self::note($m[1]);
        }

        return $clean === '' ? $value : $clean;
    }
}
