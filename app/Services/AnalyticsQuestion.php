<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

/** Converts supported report questions into explicit, read-only query criteria. */
class AnalyticsQuestion
{
    public function filters(string $question, array $filters): array
    {
        preg_match_all('/\b(?:rhu|rural\s+health\s+unit)\s*[-#]?\s*(\d+|iv|iii|ii|i|v|one|two|three|four|five)\b/iu', $question, $matches);
        $numbers = ['i' => 1, 'ii' => 2, 'iii' => 3, 'iv' => 4, 'v' => 5,
            'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5];
        $rhus = [];
        foreach ($matches[1] as $value) {
            $number = $numbers[strtolower($value)] ?? (int) $value;
            if ($number < 1 || $number > 5) {
                throw ValidationException::withMessages(['question' => 'Please choose RHU 1, 2, 3, 4 or 5.']);
            }
            $rhus[] = 'RHU '.$number;
        }
        $rhus = array_values(array_unique($rhus));
        if (count($rhus) > 1 || preg_match('/\brhu\s*\d+\s*(?:and|or|to|[-–,&])\s*\d+\b/iu', $question)) {
            throw ValidationException::withMessages(['question' => 'Which single RHU should I use? Ask about one RHU at a time.']);
        }
        if ($rhus) {
            // Explicit question scope replaces the page's RHU/area selection.
            // The controller MUST reapply the user's access scope afterwards.
            $filters['rhu'] = $rhus[0];
            $filters['barangay'] = null;
        }

        return $filters;
    }

    public function listing(string $question): ?array
    {
        $q = mb_strtolower(trim($question));
        // Do not turn health education or workflow instructions into a data dump.
        if (preg_match('/\b(?:how (?:do|can|to|should)|what (?:is|are|causes)|explain|why|paano)\b/u', $q) && !preg_match('/\b(priority|priorities|review|list|show|which)\b/u', $q)) {
            return null;
        }
        $areas = (bool) preg_match('/\b(?:barangays?|areas?|locations?|brgy|lugar)\b/u', $q);
        $women = (bool) preg_match('/\b(?:women|woman|mothers?|babae|buntis)\b/u', $q);
        $patients = !$areas && ($women || (bool) preg_match('/\b(?:patients?|records?|priority|priorities|review|risk|critical|high[ -]?risk|medium[ -]?risk|low[ -]?risk|unassessed|emergency|emergencies|missed|overdue|appointment|past due|edd)\b/u', $q));
        if (!$areas && !$patients) {
            return null;
        }
        $patients = $patients || !$areas;
        $count = false; // Never just count for clinical/record review questions — always show specific data (who it is).
        $metric = match (true) {
            (bool) preg_match('#\b(?:priority|priorities|review|urgent)\b#u', $q) => 'high_risk',
            (bool) preg_match('#\b(?:deaths?|died|mortality|namatay)\b#u', $q) => 'deaths',
            (bool) preg_match('#\bcomplications?\b#u', $q) => 'complications',
            (bool) preg_match('#\bregistrations?\b#u', $q) => 'registrations',
            (bool) preg_match('#\b(?:high[ -]?risk|at[ -]?risk|in[ -]?risk|risky|panganib)\b#u', $q) => 'high_risk',
            (bool) preg_match('#\bcritical\b#u', $q) => 'Critical',
            (bool) preg_match('#\bmedium[ -]?risk\b#u', $q) => 'Medium',
            (bool) preg_match('#\blow[ -]?risk\b#u', $q) => 'Low',
            (bool) preg_match('#\b(?:emergency|emergencies)\b#u', $q) => 'emergencies',
            (bool) preg_match('#\b(?:unassessed|without assessment)\b#u', $q) => 'Unassessed',
            (bool) preg_match('#\b(?:missed|overdue|appointment|care gap)\b#u', $q) => 'care_gaps',
            (bool) preg_match('#\b(?:past|expected delivery|edd|overdue delivery)\b#u', $q) => 'past_due',
            default => 'open',
        };
        if ($metric === null) {
            return null;
        }
        if ($patients && !$areas && in_array($metric, ['deaths', 'complications', 'registrations'], true)) {
            return null; // The queue contains current pregnancy records, not these event registries.
        }
        $registrationYear = null;
        if (preg_match('/\blast year\b/u', $q)) {
            $registrationYear = today()->subYear()->year;
        } elseif (preg_match('/\b(?:in|during|for)\s+(20\d{2})\b/u', $q, $yearMatch)) {
            $registrationYear = (int) $yearMatch[1];
        }

        return [
            'entity' => $areas ? 'areas' : 'patients',
            'metric' => $metric,
            'registration_year' => $registrationYear,
            'zero' => $metric !== 'Unassessed' && (bool) preg_match('/\b(?:no|without|zero|empty|walang|wala)\b/u', $q),
            'count' => false,
            'female_only' => $women,
            // Patient listings are for staff follow-up, so always include recommendations and specific named results.
            'recommendations' => true,
        ];
    }
}
