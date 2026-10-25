<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIInsightService
{
    /** Memoized RHU catchment keys per RHU label (null key = city-wide). */
    private array $catchments = [];

    /** Operational suggestions from recorded facts, with no invented clinical thresholds. */
    public function suggestions(array $report): array
    {
        $t = $report['totals'];
        $items = [];
        if ($t['emergency'] || $report['risk_counts']['Critical']) {
            $items[] = $this->item('danger', 'Review urgent recorded flags',
                "{$t['emergency']} emergency-marked record(s); {$report['risk_counts']['Critical']} critical-risk pregnancy record(s). These groups may overlap.",
                'Contact the responsible clinicians to confirm review and referral status.');
        }
        if ($t['high_risk']) {
            $items[] = $this->item('danger', 'Prioritize high-risk follow-up',
                "{$t['high_risk']} open pregnancy record(s) carry a High or Critical risk flag.",
                'Review the queue with the assigned midwife and confirm each existing care plan.');
        }
        if ($t['care_gaps']) {
            $items[] = $this->item('warning', 'Reconnect patients with scheduled care',
                "{$t['care_gaps']} open pregnancy record(s) have missed or overdue linked appointments.",
                'Ask the assigned BHW to verify attendance and coordinate follow-up.');
        }
        if ($t['past_due']) {
            $items[] = $this->item('warning', 'Confirm overdue pregnancy outcomes',
                "{$t['past_due']} open record(s) have an expected delivery date before today.",
                'Confirm the current pregnancy status with the care team and record any completed delivery.');
        }
        if ($t['deaths']) {
            $items[] = $this->item('info', 'Review recorded maternal deaths',
                "{$t['deaths']} death record(s) in the selected period; {$t['pending_death_reviews']} pending or under review.",
                'Review outstanding maternal-death audits and documented referral or service gaps. Counts alone do not establish a cause or trend.');
        }
        if ($t['complications']) {
            $items[] = $this->item('info', 'Review reported complications',
                "{$t['complications']} complication event(s) in the selected period.",
                'Review the documented events with the RHU team. They are event counts, not a confirmed near-miss rate.');
        }
        $top = collect($report['areas'])->where('key', '!=', MaternalAnalyticsService::UNKNOWN_AREA)->sortByDesc('high_risk')->first();
        if ($top && $top['high_risk'] > 0) {
            $items[] = $this->item('info', 'Plan staff coverage by area',
                "{$top['label']} has {$top['high_risk']} open High/Critical record(s), among the largest recorded counts in this selection.",
                'Check current staffing, outreach capacity and referral transport before reallocating resources. This is a count, not a population risk rate.');
        }
        if ($t['unassessed']) {
            $items[] = $this->item('warning', 'Complete missing assessments',
                "{$t['unassessed']} open pregnancy record(s) have no recognized risk assessment.",
                'Ask a clinician to review these records; missing risk data does not mean low risk.');
        }
        if (! $items) {
            $items[] = $this->item('info', 'Continue record review',
                $t['open'] ? 'No configured follow-up flags were found in the selected records.' : 'No open pregnancy records were found in this area.',
                'Check reporting completeness and maintain scheduled follow-up. An absence of recorded flags does not confirm an absence of risk.');
        }

        return $items;
    }

    public function chat(string $question, array $report): array
    {
        if (preg_match('/\b(?:what medicine to take|prescribe|dosage for|what dose should)\b/iu', $question)) {
            return ['answer' => 'I can explain general maternal and reproductive health topics, but cannot choose an individual’s medicine or dosage. Please ask the responsible clinician to review the patient’s care plan.',
                'source' => 'rules', 'error_code' => 'unsupported_topic', 'notice' => 'Individual treatment requires clinician review'];
        }
        if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}|(?:\+?63|0)9\d[\d\s-]{8,}|\bgsk_[A-Za-z0-9]+|\b(?:patient\s*(?:id|name)|medical record number)\s*[:#]/iu', $question)) {
            return ['answer' => 'Please remove names, patient identifiers, contact details, and credentials. Ask a general health, workflow, or report question instead.',
                'source' => 'rules', 'error_code' => 'private_question', 'notice' => 'Question kept on this server'];
        }
        // Meta questions ("what can you do?", "can you answer any question?")
        // get a capabilities answer instead of a report summary, so the model
        // is never asked to answer a question about itself from report data.
        if ($this->isCapabilityQuestion($question)) {
            return ['answer' => $this->capabilitiesAnswer(), 'source' => 'rules',
                'notice' => 'Local capabilities guide.'];
        }
        // Explicit data lists stay local. The cloud receives only aggregate,
        // aliased data and must never receive names from the review queue.
        if ($criteria = app(AnalyticsQuestion::class)->listing($question)) {
            return ['answer' => $this->listingAnswer($criteria, $report), 'source' => 'rules',
                'notice' => 'Local listing for the applied report scope.'];
        }
        // Month-aware, data-based answers run before any generic summary so
        // comparisons name exact months with real counts from the database.
        $monthAnswer = $this->monthAwareAnswer($question, $report);
        if (isset($monthAnswer['error_code'])) {
            return $monthAnswer;
        }
        $fallback = $monthAnswer['answer'] ?? $this->localAnswer($question, $report);
        $includeLocalData = $monthAnswer !== null || $this->isReportQuestion($question);
        if (config('services.analytics_ai.provider', 'rules') === 'openrouter') {
            return $this->openRouterAnswer($question, $report, $fallback, $includeLocalData);
        }
        $filters = $report['filters'] ?? [];
        $dateRange = (!empty($filters['from']) && !empty($filters['to']))
            ? ' for '.$filters['from'].' to '.$filters['to']
            : '';
        return ['answer' => $fallback, 'source' => 'rules', 'notice' => 'Answer based on the selected report'.$dateRange.'. Verify figures against local charts.'];
    }

    /** Configuration status only: rendering analytics must never call a model. */
    public function status(): array
    {
        return match (config('services.analytics_ai.provider', 'openrouter')) {
            'openrouter' => app(OpenRouterAnalyticsService::class)->configured()
                ? ['label' => 'Online AI · OpenRouter', 'description' => 'Custom questions are answered by OpenRouter. Report questions combine its context with verified local data.']
                : ['label' => 'OpenRouter needs setup', 'description' => 'Online AI is connected in the app but needs your server API key. Answers currently use local rules.'],
            default => ['label' => 'Local rules · AI off', 'description' => 'Answers currently use programmed rules. Your administrator can enable OpenRouter online AI.'],
        };
    }

    private function groqAnswer(string $question, array $report, string $fallback): array
    {
        $projection = app(CloudAnalyticsContext::class);
        $topic = $projection->topic($question) ?? 'general';
        $prepared = $projection->build($report);
        $result = app(GroqAnalyticsService::class)->summarize($prepared['context'], $topic, true, $question);
        if (! $result['ok']) {
            return ['answer' => $fallback, 'source' => 'rules', 'error_code' => $result['error_code'],
                'notice' => $result['message'].' Showing the local rules answer.'];
        }

        return [
            'answer' => $result['answer'], 'source' => 'groq', 'model' => $result['model'],
            'cached' => $result['cached'], 'generated_at' => $result['generated_at'],
            'topic' => CloudAnalyticsContext::TOPICS[$topic],
            'area_legend' => $prepared['area_legend'],
            'notice' => $this->aiNotice($result, $report),
        ];
    }

    private function geminiAnswer(string $question, array $report, string $fallback): array
    {
        $projection = app(CloudAnalyticsContext::class);
        $topic = $projection->topic($question) ?? 'general';
        $prepared = $projection->build($report);
        $result = app(GeminiAnalyticsService::class)->summarize($prepared['context'], $topic, true, $question);
        if (! $result['ok']) {
            return ['answer' => $fallback, 'source' => 'rules', 'error_code' => $result['error_code'],
                'notice' => $result['message'].' Showing the local rules answer.'];
        }

        return [
            'answer' => $result['answer'], 'source' => 'gemini', 'model' => $result['model'],
            'cached' => $result['cached'], 'generated_at' => $result['generated_at'],
            'topic' => CloudAnalyticsContext::TOPICS[$topic],
            'area_legend' => $prepared['area_legend'],
            'notice' => $this->aiNotice($result, $report),
        ];
    }

    private function openRouterAnswer(string $question, array $report, string $fallback, bool $includeLocalData = true): array
    {
        $projection = app(CloudAnalyticsContext::class);
        $topic = $projection->topic($question) ?? 'general';
        $prepared = $projection->build($report);
        $result = app(OpenRouterAnalyticsService::class)->summarize($prepared['context'], $topic, true, $question);
        if (! $result['ok']) {
            return ['answer' => $fallback, 'source' => 'rules', 'error_code' => $result['error_code'],
                'notice' => $result['message'].' Showing the local rules answer.'];
        }

        return [
            'answer' => $this->combinedAnswer($includeLocalData ? $fallback : null, $result['answer']),
            'source' => 'openrouter', 'combined' => $includeLocalData, 'model' => $result['model'],
            'cached' => $result['cached'], 'generated_at' => $result['generated_at'],
            'topic' => CloudAnalyticsContext::TOPICS[$topic],
            'area_legend' => $prepared['area_legend'],
            'notice' => $this->aiNotice($result, $report),
        ];
    }

    /** Exact report facts stay local; the model receives only the aggregate projection. */
    private function combinedAnswer(?string $localAnswer, string $aiAnswer): string
    {
        if ($localAnswer === null || trim($localAnswer) === '') {
            return $aiAnswer;
        }

        return "Verified local report data and guidance:\n{$localAnswer}\n\nAI context:\n{$aiAnswer}";
    }

    private function isReportQuestion(string $question): bool
    {
        return (bool) preg_match('/report|record|pregnan|maternal|death|died|mortality|risk|follow.?up|appointment|area|barangay|location|trend|month|registration|women|patient|clinic|queue|buntis|buwan|namatay|patay|health data|this selection|these filters/iu', $question);
    }

    /** Explicit allowlist: no patient names, IDs, contacts, notes or queue rows reach the model. */
    private function aggregateContext(array $report): array
    {
        return array_intersect_key($report, array_flip(['filters', 'area_label', 'totals', 'risk_counts', 'monthly', 'areas']));
    }

    private function aiNotice(array $result, array $report): string
    {
        return '';
    }

    /**
     * Data-based month/year answers for questions that name a period.
     * Returns null when the question names no period (caller falls back to
     * localAnswer / cloud providers), so every other question is answered
     * from the applied report filters instead of being sent back for a month.
     * Never invents numbers: counts come from the database inside the report's
     * date window and area scope; months outside the selected period are
     * refused with the period named, and zero rows are reported as zero.
     */
    private function monthAwareAnswer(string $question, array $report): ?array
    {
        $months = $this->extractMonthRefs($question);
        $isDataTopic = (bool) preg_match('/regist|death|died|mortality|high.?risk|follow.?up|priorit|pregnan|women|maternal|complication|trend|record|audit|risk/iu', $question);
        if ($months === [] && ! ($isDataTopic && $this->mentionsPeriod($question))) {
            return null;
        }

        $isCompare = (bool) preg_match('/compar|versus|\bvs\b|difference|change|trend|growth|increase|decrease|this month.*last month|last month.*this month/iu', $question);
        $isDeaths = (bool) preg_match('/death|died|mortality|namatay|patay/iu', $question);
        $isHighRiskArea = (bool) preg_match('/high.?risk.*(barangay|area|bayan)|which (barangay|area).*high.?risk/iu', $question);
        $isRegistrations = (bool) preg_match('/regist|new women|enrol|pregnancy record|pregnan/iu', $question) && ! $isDeaths && ! $isHighRiskArea;
        $isFollowUp = (bool) preg_match('/follow.?up|priorit|unahin|missed|overdue|panganib/iu', $question) && ! $isDeaths;

        $scopeLabel = $this->filterLabel($report);

        // A bare month question ("october 2026 records") names a period but
        // no topic — answer with a general overview of that month instead of
        // falling through to the generic assistant text.
        if (! $isCompare && ! $isDeaths && ! $isHighRiskArea && ! $isRegistrations && ! $isFollowUp) {
            if ($months !== []) {
                $window = $this->monthWindow($months[0], $report);
                if ($window === null) {
                    return $this->outsidePeriod([$months[0]], $report, $scopeLabel);
                }

                return ['answer' => $this->monthOverview($window, $report, $scopeLabel).$this->partialNote($window, $report),
                    'source' => 'rules', 'notice' => 'Exact recorded counts for the stated month.'];
            }

            return null;
        }

        // Comparison needs two explicit months. Relative phrases resolve
        // against today; a single month cannot be compared with nothing.
        if ($isCompare) {
            if (count($months) < 2) {
                $relative = $this->resolveRelativeMonths($question);
                $months = $relative ?? $months;
            }
            if (count($months) < 2) {
                return [
                    'answer' => 'To compare months I need two specific months. Which two months and years should I compare? For example: "Compare pregnancy records from September 2026 and October 2026."',
                    'source' => 'rules',
                    'error_code' => 'needs_period',
                    'notice' => 'Waiting for the months to analyze — no data was queried.',
                ];
            }
            [$a, $b] = [array_slice($months, 0, 1)[0], array_slice($months, 1, 1)[0]];
            $wa = $this->monthWindow($a, $report);
            $wb = $this->monthWindow($b, $report);
            if ($wa === null || $wb === null) {
                $outside = array_values(array_filter([$wa === null ? $a : null, $wb === null ? $b : null]));
                return $this->outsidePeriod($outside, $report, $scopeLabel);
            }
            $answer = $isDeaths
                ? $this->compareDeaths($wa, $wb, $report, $scopeLabel)
                : $this->compareRegistrations($wa, $wb, $report, $scopeLabel);

            return ['answer' => $answer.$this->partialNote($wa, $report).$this->partialNote($wb, $report),
                'source' => 'rules', 'notice' => 'Exact recorded counts for the stated months.'];
        }

        // Single-month questions: one explicit month, else ask which month.
        if ($months === []) {
            $relative = $this->resolveRelativeMonths($question);
            $months = $relative ? array_slice($relative, 0, 1) : [];
        }
        if ($months === []) {
            // A period like "last year" was named but no single month resolved.
            // Answer for the applied report period instead of asking the staff
            // member to restate a month the filters already cover.
            return null;
        }
        $month = $months[0];
        $window = $this->monthWindow($month, $report);
        if ($window === null) {
            return $this->outsidePeriod([$month], $report, $scopeLabel);
        }
        $note = $this->partialNote($window, $report);

        if ($isDeaths) {
            return ['answer' => $this->monthDeaths($window, $report, $scopeLabel).$note, 'source' => 'rules', 'notice' => 'Exact recorded counts for the stated month.'];
        }
        if ($isHighRiskArea) {
            return ['answer' => $this->monthHighRiskArea($window, $report, $scopeLabel).$note, 'source' => 'rules', 'notice' => 'Exact recorded counts for the stated month.'];
        }
        if ($isFollowUp) {
            return ['answer' => $this->priorityFollowUp($report, $scopeLabel), 'source' => 'rules', 'notice' => 'Based on recorded health records and system rules.'];
        }

        return ['answer' => $this->monthRegistrations($window, $report, $scopeLabel).$note, 'source' => 'rules', 'notice' => 'Exact recorded counts for the stated month.'];
    }

    /** True when the question names a month, a year, or this/last month style period. */
    private function mentionsPeriod(string $question): bool
    {
        if ($this->extractMonthRefs($question) !== []) {
            return true;
        }

        return (bool) preg_match('/\b(?:this|last|next|previous|coming)\s+(?:month|year|quarter)\b|\b20\d{2}\b/iu', $question);
    }

    /**
     * The part of a named month inside the applied date filter, or null when
     * the month sits entirely outside it. Counts never leave the selected
     * period, so answers always match the report's own monthly numbers.
     */
    private function monthWindow(string $ym, array $report): ?array
    {
        $monthStart = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $from = Carbon::parse($report['filters']['from'])->startOfDay();
        $to = Carbon::parse($report['filters']['to'])->endOfDay();
        if ($monthEnd->lt($from) || $monthStart->gt($to)) {
            return null;
        }
        $start = $monthStart->gt($from) ? $monthStart : $from;
        $end = $monthEnd->lt($to) ? $monthEnd : $to;

        return ['ym' => $ym, 'start' => $start, 'end' => $end,
            'partial' => ! $start->eq($monthStart) || ! $end->eq($monthEnd)];
    }

    /** A named month outside the applied date filter: state it, never invent counts. */
    private function outsidePeriod(array $yms, array $report, string $scopeLabel): array
    {
        $names = implode(' and ', array_map(fn (string $ym) => $this->monthLabel($ym), $yms));
        $verb = count($yms) > 1 ? 'are' : 'is';

        return [
            'answer' => "{$names} {$verb} outside the selected report period ("
                .$report['filters']['from'].' to '.$report['filters']['to'].'), so this report holds no data for '
                .(count($yms) > 1 ? 'them' : 'it').' under the current filters. '
                .'Apply a wider From–To range (up to three years) to include '
                .(count($yms) > 1 ? 'those months' : 'that month').'.'
                ."\nFilters: {$scopeLabel}.",
            'source' => 'rules',
            'error_code' => 'outside_period',
            'notice' => 'Outside the selected report period — no data was queried.',
        ];
    }

    /** Boundary months only count the days inside the applied period; say so. */
    private function partialNote(array $window, array $report): string
    {
        if (! $window['partial']) {
            return '';
        }

        return ' Note: only the part of '.$this->monthLabel($window['ym'])
            .' inside the selected period ('.$report['filters']['from'].' to '.$report['filters']['to'].') is counted.';
    }

    /** The filters driving this answer: area, period, and RHU or city-wide scope. */
    private function filterLabel(array $report): string
    {
        $filters = $report['filters'];

        return "{$report['area_label']}; {$filters['from']} to {$filters['to']}; ".($report['scope_label'] ?? 'City-wide · All RHUs');
    }

    /** Find explicit month+year references, e.g. "October 2026", "Oct 2026", "10/2026", "2026-10". */
    private function extractMonthRefs(string $question): array
    {
        $names = ['january' => 1, 'february' => 2, 'march' => 3, 'april' => 4, 'may' => 5, 'june' => 6,
            'july' => 7, 'august' => 8, 'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'jun' => 6, 'jul' => 7, 'aug' => 8,
            'sep' => 9, 'sept' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12];
        $found = [];
        $q = mb_strtolower($question);
        foreach ($names as $name => $m) {
            if (preg_match_all('/\b' . $name . '\b\s*(20\d{2})?/iu', $q, $mm, PREG_SET_ORDER)) {
                foreach ($mm as $hit) {
                    $year = isset($hit[1]) && $hit[1] !== '' ? (int) $hit[1] : (int) today()->format('Y');
                    $found[] = sprintf('%04d-%02d', $year, $m);
                }
            }
        }
        if (preg_match_all('/\b(20\d{2})-(0[1-9]|1[0-2])\b/', $question, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $hit) {
                $found[] = $hit[1] . '-' . $hit[2];
            }
        }
        if (preg_match_all('/\b(0?[1-9]|1[0-2])\/(20\d{2})\b/', $question, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $hit) {
                $found[] = $hit[2] . '-' . str_pad($hit[1], 2, '0', STR_PAD_LEFT);
            }
        }

        return array_values(array_unique($found));
    }

    /** Resolve "this month / last month" style phrases against today. */
    private function resolveRelativeMonths(string $question): ?array
    {
        $q = mb_strtolower($question);
        $now = today();
        if (preg_match('/this month.*last month|last month.*this month|compared?.*month|month.*compared?/iu', $question)
            || (str_contains($q, 'this month') && str_contains($q, 'last month'))) {
            return [$now->copy()->subMonthNoOverflow()->format('Y-m'), $now->format('Y-m')];
        }
        if (str_contains($q, 'last month')) {
            return [$now->copy()->subMonthNoOverflow()->format('Y-m')];
        }
        if (str_contains($q, 'this month')) {
            return [$now->format('Y-m')];
        }

        return null;
    }

    private function monthLabel(string $ym): string
    {
        return Carbon::createFromFormat('Y-m', $ym)->format('F Y');
    }

    /**
     * Whether a recorded area value falls inside the report's barangay and
     * RHU filters. Uses the same canonical key match as the report itself, so
     * legacy barangay spellings answer identically to the charts. Unknown or
     * unrecorded locations are only included when the report includes them.
     */
    private function areaInScope(?string $raw, array $report): bool
    {
        $scope = app(AnalyticsScope::class);
        $key = $scope->key($raw) ?: MaternalAnalyticsService::UNKNOWN_AREA;
        $selected = $report['filters']['barangay'] ?? null;
        if ($selected !== null && $selected !== '' && $key !== $scope->key($selected)) {
            return false;
        }
        $catchment = $this->catchment($report);

        return $catchment === null || in_array($key, $catchment, true);
    }

    /** Memoized RHU catchment keys (null = city-wide), shared by every row check. */
    private function catchment(array $report): ?array
    {
        $rhu = $report['filters']['rhu'] ?? null;
        if (! array_key_exists($rhu, $this->catchments)) {
            $this->catchments[$rhu] = app(AnalyticsScope::class)->areas($rhu);
        }

        return $this->catchments[$rhu];
    }

    /** Pregnancy records filed inside the window, inside the report's area scope. */
    private function pregnanciesBetween(array $window, array $report): Collection
    {
        return \App\Models\Pregnancy::with(['woman:id,barangay', 'walkInPatient:id,barangay'])
            ->whereBetween('created_at', [$window['start']->toDateTimeString(), $window['end']->toDateTimeString()])
            ->get(['id', 'user_id', 'walk_in_patient_id'])
            ->filter(fn ($pregnancy) => $this->areaInScope($pregnancy->woman?->barangay ?? $pregnancy->walkInPatient?->barangay, $report))
            ->values();
    }

    /** Maternal deaths inside the window, inside the report's area scope. */
    private function deathsBetween(array $window, array $report): Collection
    {
        return \App\Models\MaternalDeath::whereBetween('death_date', [$window['start']->toDateString(), $window['end']->toDateString()])
            ->get(['id', 'barangay', 'cause_category', 'audit_status'])
            ->filter(fn ($death) => $this->areaInScope($death->barangay, $report))
            ->values();
    }

    /** Women accounts created inside the window, inside the report's area scope. */
    private function womenBetween(array $window, array $report): int
    {
        return \App\Models\User::where('role', 'user')
            ->whereBetween('created_at', [$window['start']->toDateTimeString(), $window['end']->toDateTimeString()])
            ->get(['id', 'barangay'])
            ->filter(fn ($woman) => $this->areaInScope($woman->barangay, $report))
            ->count();
    }

    /** High-risk pregnancy records filed inside the window, inside the report's area scope. */
    private function highRiskBetween(array $window, array $report): Collection
    {
        return \App\Models\Pregnancy::with(['woman:id,barangay', 'walkInPatient:id,barangay'])
            ->whereBetween('pregnancies.created_at', [$window['start'], $window['end']])
            ->where(fn ($query) => $query->whereIn('risk_level', ['High', 'Critical'])->orWhere('is_high_risk', true))
            ->get(['id', 'user_id', 'walk_in_patient_id', 'risk_level', 'is_high_risk'])
            ->filter(fn ($pregnancy) => $this->areaInScope($pregnancy->woman?->barangay ?? $pregnancy->walkInPatient?->barangay, $report))
            ->values();
    }

    private function compareRegistrations(array $wa, array $wb, array $report, string $scopeLabel): string
    {
        $a = $wa['ym'];
        $b = $wb['ym'];
        $pa = $this->pregnanciesBetween($wa, $report)->count();
        $pb = $this->pregnanciesBetween($wb, $report)->count();
        $womenA = $this->womenBetween($wa, $report);
        $womenB = $this->womenBetween($wb, $report);
        $diffP = $pb - $pa;
        $diffW = $womenB - $womenA;

        return "Selected range: {$this->monthLabel($a)} vs {$this->monthLabel($b)}; filters: {$scopeLabel}. "
            . "Specific data: pregnancy records — {$this->monthLabel($a)}: {$pa}, {$this->monthLabel($b)}: {$pb}, "
            . "difference: " . ($diffP >= 0 ? '+' : '') . "{$diffP}" . $this->pct($pa, $pb) . ". "
            . "New women registered — {$this->monthLabel($a)}: {$womenA}, {$this->monthLabel($b)}: {$womenB}, "
            . "difference: " . ($diffW >= 0 ? '+' : '') . "{$diffW}" . $this->pct($womenA, $womenB) . ". "
            . 'Zero means no records were filed that month, not missing data. '
            . 'Recommended: confirm entry completeness for the lower month with the assigned midwife before treating the gap as a real decline.';
    }

    private function pct(int $prev, int $curr): string
    {
        if ($prev <= 0) {
            return ' (percentage change unavailable — previous month count is zero)';
        }
        $p = round((($curr - $prev) / $prev) * 100, 1);

        return ' (' . ($p >= 0 ? '+' : '') . "{$p}% vs previous month)";
    }

    private function monthRegistrations(array $window, array $report, string $scopeLabel): string
    {
        $month = $this->monthLabel($window['ym']);
        $p = $this->pregnanciesBetween($window, $report)->count();
        $w = $this->womenBetween($window, $report);

        return "Selected month: {$month}; filters: {$scopeLabel}. "
            . "Specific data: {$p} pregnancy record(s) filed and {$w} new women registered in {$month}. "
            . 'Zero means no records were filed that month, not missing data. '
            . 'Recommended: verify entry completeness with the assigned midwife and confirm follow-up for each new record.';
    }

    private function compareDeaths(array $wa, array $wb, array $report, string $scopeLabel): string
    {
        $a = $wa['ym'];
        $b = $wb['ym'];
        $da = $this->deathsBetween($wa, $report)->count();
        $db = $this->deathsBetween($wb, $report)->count();
        $diff = $db - $da;

        return "Selected range: {$this->monthLabel($a)} vs {$this->monthLabel($b)}; filters: {$scopeLabel}. "
            . "Specific data: maternal deaths — {$this->monthLabel($a)}: {$da}, {$this->monthLabel($b)}: {$db}, "
            . "difference: " . ($diff >= 0 ? '+' : '') . "{$diff}" . $this->pct($da, $db) . ". "
            . 'Counts are recorded deaths, not mortality rates. '
            . 'Recommended: review outstanding death audits and documented referral or service gaps; counts alone do not establish a cause or trend.';
    }

    private function monthDeaths(array $window, array $report, string $scopeLabel): string
    {
        $month = $this->monthLabel($window['ym']);
        $deaths = $this->deathsBetween($window, $report);
        $pending = $deaths->whereIn('audit_status', ['pending', 'under_review'])->count();
        $byArea = $deaths->countBy(fn ($d) => $d->barangay ?: 'Unrecorded barangay')->sortDesc();

        return "Selected month: {$month}; filters: {$scopeLabel}. "
            . "Specific data: {$deaths->count()} maternal death record(s), {$pending} audit(s) pending or under review. "
            . ($byArea->isNotEmpty() ? 'By barangay: ' . $byArea->map(fn ($c, $k) => "{$k} ({$c})")->take(5)->implode(', ') . '. ' : 'No deaths recorded in any barangay that month. ')
            . 'Zero means no deaths recorded, not missing data. '
            . 'Recommended: complete pending death audits and review referral and service gaps with the RHU team.';
    }

    /** General overview for a bare month question ("october 2026 records"). */
    private function monthOverview(array $window, array $report, string $scopeLabel): string
    {
        $month = $this->monthLabel($window['ym']);
        $preg = $this->pregnanciesBetween($window, $report)->count();
        $women = $this->womenBetween($window, $report);
        $deaths = $this->deathsBetween($window, $report);
        $highRisk = $this->highRiskBetween($window, $report);
        $topDeath = $deaths->countBy(fn ($d) => $d->barangay ?: 'Unrecorded barangay')->sortDesc();
        $topRisk = $highRisk->countBy(fn ($p) => ($p->woman?->barangay ?? $p->walkInPatient?->barangay) ?: 'Unrecorded barangay')->sortDesc();

        return "Selected month: {$month}; filters: {$scopeLabel}. "
            . "Specific data: {$preg} pregnancy record(s) filed, {$women} new women registered, "
            . "{$deaths->count()} maternal death record(s), {$highRisk->count()} high-risk pregnancy record(s). "
            . ($topRisk->isNotEmpty() ? "Highest high-risk count: {$topRisk->keys()->first()} ({$topRisk->first()}). " : 'No high-risk records filed that month. ')
            . ($topDeath->isNotEmpty() ? "Deaths by barangay: " . $topDeath->map(fn ($c, $k) => "{$k} ({$c})")->take(5)->implode(', ') . '. ' : '')
            . 'Zero means no records were filed that month, not missing data. '
            . 'Recommended: confirm entry completeness with the assigned midwife, prioritize the high-risk records for clinician review, '
            . 'and complete any pending death audits. Ask me to compare with another month, e.g. "Compare pregnancy records from September 2026 and October 2026."';
    }

    private function monthHighRiskArea(array $window, array $report, string $scopeLabel): string
    {
        $month = $this->monthLabel($window['ym']);
        $rows = $this->highRiskBetween($window, $report);
        $byArea = $rows->countBy(fn ($p) => ($p->woman?->barangay ?? $p->walkInPatient?->barangay) ?: 'Unrecorded barangay')->sortDesc();
        $top = $byArea->keys()->first();

        if (! $top) {
            return "Selected month: {$month}; filters: {$scopeLabel}. "
                . 'Specific data: 0 high-risk pregnancy records filed that month. '
                . 'An absence of recorded flags does not confirm an absence of risk — confirm reporting completeness and keep scheduled follow-up.';
        }

        return "Selected month: {$month}; filters: {$scopeLabel}. "
            . "Specific data: {$top} recorded the highest number of high-risk pregnancies ({$byArea[$top]} of {$rows->count()} high-risk records that month). "
            . ($byArea->count() > 1 ? 'Other areas: ' . $byArea->except($top)->map(fn ($c, $k) => "{$k} ({$c})")->take(5)->implode(', ') . '. ' : '')
            . 'Counts are recorded cases, not population risk rates. '
            . 'Recommended: confirm each care plan with the assigned midwife, check outreach staffing and referral transport for that barangay, and re-check its unassessed records.';
    }

    private function priorityFollowUp(array $report, string $scopeLabel): string
    {
        $queue = collect($report['queue'] ?? [])->take(5);
        if ($queue->isEmpty()) {
            return "Filters: {$scopeLabel}. Specific data: no open pregnancy records in this selection — no follow-up queue. "
                . 'Confirm reporting completeness and maintain scheduled follow-up.';
        }
        $lines = $queue->map(fn ($e) => "Pregnancy #{$e['pregnancy_id']} ({$e['area']}, recorded risk: {$e['risk']}"
            . ($e['emergency'] ? ', EMERGENCY-marked' : '')
            . ($e['edd'] ? ", EDD {$e['edd']}" : '')
            . '): ' . implode(' ', array_slice($e['reasons'] ?? [], 0, 2)))->implode(' | ');

        return "Filters: {$scopeLabel}. Specific data: {$report['totals']['open']} open record(s) — "
            . "{$report['totals']['high_risk']} High/Critical, {$report['totals']['emergency']} emergency-marked, "
            . "{$report['totals']['care_gaps']} with missed or overdue appointments, {$report['totals']['unassessed']} unassessed. "
            . "Top priority records: {$lines}. "
            . 'Recommended: contact the responsible clinicians to confirm review and referral status for emergency and High/Critical records first, '
            . 'ask the assigned BHW to verify attendance for missed appointments, and have a clinician complete the unassessed records.';
    }

    private function localAnswer(string $question, array $report): string
    {
        $q = mb_strtolower($question);
        $t = $report['totals'];
        $scope = $this->filterLabel($report).'. ';
        $ranked = collect($report['areas'])->where('key', '!=', MaternalAnalyticsService::UNKNOWN_AREA);
        if (preg_match('/death|died|mortality|namatay|patay/u', $q)) {
            $worst = $ranked->sortByDesc('deaths')->first();
            $where = ($worst && $worst['deaths'] > 0)
                ? "The largest recorded count is in {$worst['label']} ({$worst['deaths']} death record(s) in the period). "
                : 'No death records were found in this selection. ';

            return $scope."\nIn short: {$t['deaths']} mother(s) died and {$t['complications']} complications were recorded in this period."
                ."\nSpecific data:\n• {$t['deaths']} maternal death record(s)\n• {$t['complications']} reported complication event(s)\n"
                ."• {$t['pending_death_reviews']} death audit(s) pending or under review\n• ".$where
                ."\nRecommended:\n• Complete the pending death audits and review referral and service gaps with the RHU team.\n• Verify each record in the queue before acting."
                ."\nNote: exact recorded counts, not mortality rates or predictions; zero records may reflect incomplete reporting.";
        }
        if (preg_match('/high.?risk.*(area|barangay|bayan)|most.*high.?risk/u', $q)) {
            $worst = $ranked->sortByDesc('high_risk')->first();
            if (! $worst || $worst['high_risk'] <= 0) {
                return $scope."\nSpecific data: 0 open High/Critical records in this selection."
                    ."\nRecommended: confirm reporting completeness and keep scheduled follow-up; an absence of recorded flags does not confirm an absence of risk.";
            }

            return $scope."\nIn short: {$worst['label']} needs the most attention right now ({$worst['high_risk']} high-risk ongoing pregnancies)."
                ."\nSpecific data:\n• Top area: {$worst['label']} ({$worst['high_risk']} High/Critical open now)\n"
                ."• Selection totals: {$t['open']} open, {$t['high_risk']} High/Critical, {$t['unassessed']} unassessed. "
                ."\nRecommended:\n• Confirm each care plan with the assigned midwife.\n• Check outreach staffing and referral transport for that barangay.\n• Re-check its unassessed records."
                ."\nNote: exact recorded counts, not population risk rates.";
        }
        if (preg_match('/most.*(pregnan|women|open|buntis)|busiest|pinakamarami/u', $q)) {
            $worst = $ranked->sortByDesc('open')->first();
            if (! $worst || $worst['open'] <= 0) {
                return $scope."\nSpecific data: 0 open pregnancy records in this selection."
                    ."\nRecommended: confirm reporting completeness and maintain scheduled follow-up.";
            }

            return $scope."\nIn short: {$worst['label']} has the most ongoing pregnancies ({$worst['open']})."
                ."\nSpecific data:\n• Busiest area: {$worst['label']} ({$worst['open']} open now)\n"
                ."• Selection totals: {$t['open']} open, {$t['high_risk']} High/Critical, {$t['care_gaps']} with missed or overdue appointments. "
                ."\nRecommended:\n• Align BHW visit schedules and checkup capacity with that workload.\n• Verify reporting completeness before reallocating staff.\n• Review the high-risk and care-gap lists in the queue."
                ."\nNote: exact recorded counts, not comparisons of need across populations.";
        }
        if (preg_match('/trend|month|registration|buwan/u', $q)) {
            $peak = collect($report['monthly'])->sortByDesc('registrations')->first();

            return $scope."\nIn short: {$t['registrations']} new pregnancies were recorded"
                .($t['registrations'] ? ", most in {$peak['label']}." : ".")
                ."\nSpecific data:\n• {$t['registrations']} pregnancy registration(s) were recorded. "
                .($t['registrations'] ? "\n• Peak month: {$peak['label']} ({$peak['registrations']}; ties are possible). " : '')
                ."\nRecommended:\n• Keep entry recording complete during peak months.\n• Confirm the queue reflects current follow-up needs."
                ."\nNote: first and last months may be partial. Registration dates measure entry into the system, not conception; this report does not forecast future pregnancies.";
        }
        if (preg_match('/area|barangay|location|lugar/u', $q)) {
            $areas = collect($report['areas'])->take(5)->map(fn ($a) => "{$a['label']}: {$a['open']} open now, {$a['high_risk']} High/Critical now, {$a['deaths']} death record(s) in the period")->implode("\n• ");

            return $scope."\nSpecific data (top 5 areas):\n• ".($areas ?: 'No records found for this selection.').' '
                ."\nRecommended:\n• Prioritize outreach to the areas with the highest recorded high-risk and death counts.\n• Confirm staffing and transport, and verify the queue for each area."
                ."\nNote: areas are ordered by recorded high-risk count, then death count. Population denominators are unavailable, so these are not comparisons of risk rates.";
        }
        // Custom operational wording is mapped to the recorded numbers rather
        // than refused: name the connection, then answer what the report holds.
        if (preg_match('/staff|staffing|workload|capacity|outreach|manpower|burnout|coverage|schedule/u', $q)) {
            $busiest = $ranked->sortByDesc('open')->take(3)
                ->map(fn ($a) => "{$a['label']}: {$a['open']} open now, {$a['high_risk']} High/Critical")
                ->implode("\n• ");

            return $scope."\nHow this connects: staffing and outreach load appear here as open records, risk flags and care gaps by area."
                ."\nSpecific data:\n• {$t['open']} open pregnancy record(s), {$t['high_risk']} High/Critical, {$t['care_gaps']} with missed or overdue appointments"
                ."\nBusiest areas by open records:\n• ".($busiest ?: 'No open pregnancy records in this selection.')
                ."\nRecommended:\n• Align BHW visit schedules and checkup capacity with the busiest areas.\n• Confirm staffing and referral transport before reallocating staff.\n• Verify reporting completeness before treating these counts as workload totals."
                ."\nNote: recorded counts, not population need or staffing ratios.";
        }
        if (preg_match('/completeness|incomplete|missing|data quality|accuracy|accurate|reporting|compliance|target|goal|performance|kpi|backlog/u', $q)) {
            return $scope."\nHow this connects: reporting completeness shows in this report as unassessed records, missed appointments, past-due outcomes and pending audits."
                ."\nSpecific data:\n• {$t['unassessed']} open record(s) without a recognized risk assessment\n"
                ."• {$t['care_gaps']} with missed or overdue appointments\n"
                ."• {$t['past_due']} past the expected delivery date with no recorded outcome\n"
                ."• {$t['pending_death_reviews']} death audit(s) pending or under review"
                ."\nRecommended:\n• Ask a clinician to complete the unassessed records.\n• Ask assigned BHWs to confirm attendance and close missed appointments.\n• Complete pending death audits and record missing outcomes."
                ."\nNote: this report measures recorded entries; care delivered outside the system is invisible here.";
        }
        if (preg_match('/priorit|risk|suggest|decision|follow|summary|summar|recommend|unahin|panganib|missed|appointment/u', $q)) {
            return $scope."\nIn short: {$t['open']} ongoing pregnancies need attention, {$t['high_risk']} of them high-risk."
                ."\nSpecific data:\n• {$t['open']} open pregnancy record(s) now\n• {$t['high_risk']} High/Critical\n• {$t['emergency']} emergency-marked\n"
                ."• {$t['care_gaps']} with missed or overdue appointments\n• {$t['past_due']} past the expected delivery date\n• {$t['unassessed']} unassessed. "
                ."\nRecommended:\n• ".collect($this->suggestions($report))->take(3)->map(fn ($s) => $s['evidence'].' '.$s['action'])->implode("\n• ");
        }

        // Navigation help only when the question names an actual workflow step,
        // so "how can we improve compliance?" is answered from the data instead.
        if (preg_match('/how (do|can|to)|where|navigate|paano|saan/u', $q)
            && preg_match('/approve|submit|register|create|add|use the|using|portal|page|button|click|record|checkup|sign in|log in/u', $q)) {
            return "How to use ReproCare:\n• Patients (women portal): track pregnancy and menstrual cycles, view checkups, message your BHW, join the community forum, and read learning materials."
                ."\n• BHWs: register women, schedule checkups, record vitals, and submit records and pregnancy reports to your president."
                ."\n• BHW Presidents: review BHW submissions in the Record Review and Pregnancy Review queues, then approve or pass them to the midwife."
                ."\n• Midwives: verify records, manage checkups, and use decision support.\n• RHU/CHO: verify accounts, review reports, and read analytics."
                ."\nApprovals flow: BHW submits → president reviews → midwife verifies."
                ."\nAsk me about report numbers, areas, trends, or deaths for data answers.";
        }

        return $scope.'I could not map that question to a recorded metric, so this rules answer has nothing to check. '
            .'This assistant works from recorded counts: open pregnancies, high or critical risk, missed appointments, registrations, deaths, complications, audits and barangay totals. '
            .'Naming the topic with the area or month usually lets it answer. '
            .'Try "Which records need priority review?", "Which barangay has the most high-risk pregnancies and what should we do?", '
            .'"Which barangay has the most pregnant women right now?" or "Which barangay has the most maternal deaths?" '
            .'Ask "List all barangays with open pregnancies" for the full detailed breakdown. '
            .'Patient-specific treatment and future predictions are outside this report.';
    }

    /**
     * True for questions about the assistant itself ("can you answer any
     * question?", "what can you do?") that name no report topic. A question
     * that asks about data — even when it starts with "can you answer" — is
     * left to the report and the model.
     */
    private function isCapabilityQuestion(string $question): bool
    {
        $meta = (bool) preg_match('/\b(?:what can (?:you|u)(?: do| answer| help| tell| show)?|what can i ask|what (?:questions|else can you)|which questions|can you answer (?:any|all|every|this|that|my|me)|can you (?:answer|help|do) (?:any|all|every)|are you able to answer|do you (?:answer|know|access|have)|who are you|what are you|capabilit|anong kaya|kaya mo|commands?|^\s*help\s*$)/iu', $question);
        if (! $meta) {
            return false;
        }

        return ! preg_match('/regist|death|died|mortality|pregnan|barangay|risk|month|trend|follow|appointment|complication|patient|woman|women|audit/iu', $question);
    }

    private function capabilitiesAnswer(): string
    {
        return 'Yes, I can answer questions about this analytics report — but not any topic at all.'
            ."\nI can answer:"
            ."\n• Priorities — which records need review first"
            ."\n• Areas — busiest barangay, highest high-risk area, most deaths"
            ."\n• Trends — monthly registration patterns"
            ."\n• Details — full per-barangay or at-risk listings"
            ."\n• Portal help — how to use ReproCare and approve records"
            ."\nTry: \"Which barangay has the most high-risk pregnancies and what should we do?\""
            ."\nI cannot answer:"
            ."\n• Medicines, dosages or individual treatment decisions"
            ."\n• Individual patient identification"
            ."\n• Predictions about future pregnancies or outbreaks";
    }

    private function listingAnswer(array $criteria, array $report): string
    {
        $scope = $this->filterLabel($report).'.';
        $areas = collect($report['areas'])->where('key', '!=', MaternalAnalyticsService::UNKNOWN_AREA)->values();
        $metric = $criteria['metric'];

        if ($criteria['entity'] === 'patients') {
            if ($criteria['zero'] && $metric === 'open') {
                $patients = app(MaternalAnalyticsService::class)->registeredPatientsWithoutOpenPregnancy(
                    $report['filters'], $criteria['female_only']
                );
                if ($criteria['count']) {
                    return $scope."\n{$patients->count()} registered patient account(s) have no open pregnancy record."
                        ."\nRecommended next step: ask assigned BHWs to verify registry status and offer assessment or registration support where relevant.";
                }
                if ($patients->isEmpty()) {
                    return $scope."\nNo registered patient accounts without an open pregnancy record were found."
                        ."\nRecommended next step: maintain registration outreach and confirm reporting completeness.";
                }
                $label = $criteria['female_only'] ? 'Registered women' : 'Registered patients';
                $lines = $patients->map(fn ($patient) => "• {$patient->name} - ".app(AnalyticsScope::class)->canonicalArea($patient->barangay));
                return $scope."\n{$label} with no open pregnancy record (".$patients->count()."):\n".$lines->implode("\n")
                    ."\nRecommended next step: ask assigned BHWs to verify registry status and offer assessment or registration support where relevant."
                    ."\nFor authorized staff review only. No open record does not confirm that a patient is not pregnant.";
            }
            $cases = collect($report['queue'] ?? [])->filter(function ($entry) use ($metric) {
                return match ($metric) {
                    'high_risk' => in_array($entry['risk'] ?? null, ['High', 'Critical'], true),
                    'Critical', 'Medium', 'Low', 'Unassessed' => ($entry['risk'] ?? null) === $metric,
                    'care_gaps' => ($entry['care_gap_count'] ?? 0) > 0,
                    'past_due' => !empty($entry['edd']) && Carbon::parse($entry['edd'])->lt(today()),
                    'emergencies' => !empty($entry['emergency']),
                    default => true,
                };
            })->sortBy([['area', 'asc'], ['name', 'asc']])->values();
            if ($criteria['count']) {
                return $scope."\n{$cases->count()} matching open pregnancy record(s)."
                    ."\nUse the Pregnancy review queue to open an authorized record.";
            }
            if ($cases->isEmpty()) {
                return $scope."\nNo matching open pregnancy records were found."
                    ."\nConfirm reporting completeness before treating this as an absence of need.";
            }
            $lines = $cases->map(function ($entry) use ($criteria) {
                $edd = !empty($entry['edd']) ? 'EDD '.Carbon::parse($entry['edd'])->format('M d, Y') : 'EDD unrecorded';
                $line = "• {$entry['name']} - {$entry['area']}, {$edd} ({$entry['risk']})";
                return $criteria['recommendations'] ? $line.'; To do: '.$this->patientFollowUp($entry) : $line;
            });
            $title = $criteria['recommendations'] ? 'Matching women and recommended follow-up' : 'Matching women';
            return $scope."\n{$title} (".$cases->count()."):\n".$lines->implode("\n")
                ."\nFor authorized staff review only. Do not paste names into an external tool. Recommendations support staff review and do not replace clinician assessment.";
        }

        $matches = $areas->filter(fn ($area) => $criteria['zero'] ? $this->areaMetricValue($area, $metric) === 0 : $this->areaMetricValue($area, $metric) > 0)
            ->sortBy('label')->sortByDesc(fn ($area) => $this->areaMetricValue($area, $metric))->values();
        if ($criteria['count']) {
            return $scope."\n{$matches->count()} barangay(s) match this request."
                ."\nRecorded counts are not population risk rates.";
        }
        if ($matches->isEmpty()) {
            return $scope."\nNo barangays match this request."
                ."\nConfirm reporting completeness before treating zero recorded entries as an absence of need.";
        }
        $label = $criteria['zero'] ? 'Barangays with no recorded '.$this->metricLabel($metric) : 'Matching barangays';
        $lines = $matches->map(fn ($area) => "• {$area['label']}: {$this->areaMetricValue($area, $metric)} {$this->metricLabel($metric)}");
        return $scope."\n{$label} (".$matches->count()."):\n".$lines->implode("\n")
            ."\nNote: counts are recorded numbers, not population risk rates. Zero records may reflect missing reports.";
    }

    private function metricLabel(string $metric): string
    {
        return match ($metric) {
            'open' => 'open pregnancy record(s)',
            'high_risk' => 'High/Critical open record(s)',
            'emergencies' => 'emergency-marked record(s)',
            default => strtolower($metric).' record(s)',
        };
    }

    private function patientFollowUp(array $entry): string
    {
        if (!empty($entry['emergency'])) {
            return 'Immediately confirm the emergency and referral status with the responsible clinician.';
        }
        if (($entry['risk'] ?? null) === 'Critical') {
            return 'Arrange urgent clinician review today and confirm the referral status.';
        }
        if (($entry['risk'] ?? null) === 'Unassessed') {
            return 'Ask a clinician to complete or verify the risk assessment and confirm the next appointment.';
        }
        if (($entry['care_gap_count'] ?? 0) > 0) {
            return 'Ask the assigned BHW to confirm attendance and coordinate the overdue follow-up.';
        }

        return 'Confirm the care plan and next appointment with the assigned midwife.';
    }

    private function areaMetricValue(array $area, string $metric): int
    {
        if (in_array($metric, ['Critical', 'Medium', 'Low', 'Unassessed'], true)) {
            return (int) ($area['risk_counts'][$metric] ?? 0);
        }

        return (int) ($area[$metric] ?? 0);
    }

    private function unavailable(string $fallback): array
    {
        return ['answer' => $fallback, 'source' => 'rules',
            'notice' => 'Local AI is unavailable or not configured for a local model. Showing the free rule-based answer.'];
    }

    private function item(string $severity, string $title, string $evidence, string $action): array
    {
        return compact('severity', 'title', 'evidence', 'action');
    }
}
