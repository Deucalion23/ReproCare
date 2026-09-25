<?php

namespace App\Services;

/** A strict outbound projection. Never send model serialization or arbitrary record text. */
class CloudAnalyticsContext
{
    public const TOPICS = [
        'priorities' => 'Summarize recorded follow-up priorities for staff review.',
        'mortality' => 'Summarize the recorded maternal-death and complication pattern.',
        'trends' => 'Describe the pregnancy registration pattern across the selected months.',
        'areas' => 'Suggest which area workloads staff should review for outreach planning.',
        'general' => 'Answer the staff question about maternal or reproductive health, ReproCare workflows, or the supplied report. Decline unrelated questions.',
    ];

    private const TOTALS = ['open', 'high_risk', 'emergency', 'registrations', 'deaths', 'complications', 'pending_death_reviews', 'care_gaps', 'past_due', 'unassessed'];

    public function topic(string $question): ?string
    {
        $question = mb_strtolower($question);

        return match (true) {
            (bool) preg_match('/death|died|mortality|namatay|patay/u', $question) => 'mortality',
            (bool) preg_match('/trend|month|registration|buwan/u', $question) => 'trends',
            (bool) preg_match('/area|barangay|location|lugar/u', $question) => 'areas',
            (bool) preg_match('/priorit|risk|suggest|decision|follow|summary|summar|recommend|unahin|panganib|missed|appointment/u', $question) => 'priorities',
            default => null,
        };
    }

    public function build(array $report): array
    {
        $areas = [];
        $legend = [];
        foreach (array_slice($report['areas'] ?? [], 0, 10) as $index => $area) {
            $alias = 'Area '.($index + 1);
            $areas[] = ['area' => $alias] + $this->counts($area, ['registrations', 'open', 'high_risk', 'deaths', 'complications']);
            // Used only in the response to the authenticated CHO. Never sent to Groq.
            $legend[] = ['alias' => $alias, 'label' => (string) ($area['label'] ?? 'Unrecorded barangay')];
        }
        $monthly = [];
        foreach (array_slice($report['monthly'] ?? [], 0, 37) as $index => $month) {
            $monthly[] = ['month' => 'Month '.($index + 1)] + $this->counts($month, ['registrations', 'deaths', 'complications']);
        }

        return [
            'context' => [
                'scope' => empty($report['filters']['barangay']) ? 'all selected areas' : 'one selected area',
                'definitions' => [
                    'current' => 'Open pregnancy, risk and follow-up counts describe today; they do not describe the historical period.',
                    'historical' => 'Registrations use record creation dates; deaths and complications use event dates within the selected period.',
                    'months' => 'Month 1 is the first selected calendar month, in chronological order. First and last months may be partial.',
                    'privacy' => 'Every count is a range. Below 5 includes zero and does not confirm any event occurred. Never infer an exact count.',
                    'limitations' => 'Recorded counts depend on reporting completeness. Complications can overlap with deaths and are not confirmed near misses. No population denominators or forecasts are available.',
                ],
                'totals' => $this->counts($report['totals'] ?? [], self::TOTALS),
                'risk_counts' => $this->counts($report['risk_counts'] ?? [], ['Critical', 'High', 'Medium', 'Unassessed', 'Low']),
                'monthly' => $monthly,
                'areas' => $areas,
                'area_rows_omitted' => count($report['areas'] ?? []) > 10,
            ],
            'area_legend' => $legend,
        ];
    }

    private function counts(array $values, array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->band($values[$key] ?? null);
        }

        return $result;
    }

    private function band(mixed $value): string
    {
        if ((! is_int($value) && ! (is_string($value) && ctype_digit($value))) || $value < 0) {
            return 'Unavailable';
        }

        return match (true) {
            $value < 5 => 'Below 5 (includes zero)',
            $value < 10 => '5-9',
            $value < 20 => '10-19',
            $value < 50 => '20-49',
            $value < 100 => '50-99',
            $value < 250 => '100-249',
            $value < 500 => '250-499',
            $value < 1000 => '500-999',
            default => '1000 or more',
        };
    }
}
