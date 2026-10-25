<?php

namespace App\Services;

/** A strict outbound projection. Never send model serialization or arbitrary record text. */
class CloudAnalyticsContext
{
    public const TOPICS = [
        // These name the likely focus; they never replace the staff's question.
        'priorities' => 'Focus area: recorded follow-up priorities for staff review.',
        'mortality' => 'Focus area: recorded maternal-death and complication events.',
        'trends' => 'Focus area: pregnancy registration patterns across the selected months.',
        'areas' => 'Focus area: area workloads for outreach planning.',
        'general' => 'Answer the staff question directly. Use the supplied report only when it is relevant; otherwise answer from general knowledge and be clear about uncertainty.',
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
        $filters = $report['filters'] ?? [];
        $from = $filters['from'] ?? 'unspecified';
        $to = $filters['to'] ?? 'unspecified';
        $scopeLabel = $report['scope_label'] ?? 'City-wide · All RHUs';
        $areaLabel = $report['area_label'] ?? 'All areas';

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
                'scope_label' => $scopeLabel,
                'area_label' => $areaLabel,
                'date_range' => "{$from} to {$to}",
                'applied_filters' => array_filter([
                    'barangay' => $filters['barangay'] ?? null,
                    'rhu' => $filters['rhu'] ?? null,
                ]),
                'scope' => (empty($report['filters']['barangay']) ? 'all selected areas' : 'one selected area')
                    .' across the '.count($monthly).' selected month(s)',
                'definitions' => [
                    'current' => 'Open pregnancy, risk and follow-up counts describe today; they do not describe the historical period.',
                    'historical' => 'Registrations use record creation dates; deaths and complications use event dates within the selected period.',
                    'months' => 'Month 1 is the first selected calendar month, in chronological order. First and last months may be partial.',
                    'privacy' => 'Every count is an exact recorded number from the applied filters. Quote only these exact numbers; never invent counts.',
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
            $result[$key] = $this->exact($values[$key] ?? null);
        }

        return $result;
    }

    private function exact(mixed $value): mixed
    {
        if (is_int($value) && $value >= 0) {
            return $value;
        }
        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return 'Unavailable';
    }
}
