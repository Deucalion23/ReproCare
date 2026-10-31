<?php

namespace App\Services;

use App\Models\Cycle;
use Carbon\Carbon;

class CyclePredictionService
{
    /** Start-to-start gaps outside this range are missing history, not cycles. */
    private const MIN_PLAUSIBLE_CYCLE = 15;
    private const MAX_PLAUSIBLE_CYCLE = 90;

    /** Ovulation counting back 14 days is only meaningful for longer cycles. */
    private const MIN_OVULATION_CYCLE = 21;

    private const DEFAULT_CYCLE_LENGTH = 28;

    /** Safety cap when advancing a stale history to the current cycle. */
    private const MAX_CYCLES_AHEAD = 36;

    /**
     * Predict the next period date based on cycle history
     *
     * @param int $userId
     * @return Carbon|null
     */
    public function predictNextPeriod(int $userId): ?Carbon
    {
        $history = $this->analyzeHistory($userId, 6);

        if ($history['cycles']->isEmpty()) {
            return null;
        }

        return $this->projectedStart(
            $history['cycles']->first()->period_start_date,
            $history['average'],
            $history['band_low'],
            $history['band_high']
        );
    }

    /**
     * One recency-weighted read of the plausible start-to-start intervals.
     * Every surface (summary, calendar, reminders, exports) must use this so
     * a single history cannot produce two different windows.
     *
     * @return array<string,mixed>
     */
    private function analyzeHistory(int $userId, int $limit = 6): array
    {
        $cycles = $this->getRecentCycles($userId, $limit);
        $lengths = $cycles->pluck('cycle_length')->filter()->values()->all();

        return array_merge(['cycles' => $cycles, 'lengths' => $lengths], $this->summarizeLengths($lengths));
    }

    /**
     * Recency-weighted interval statistics.
     *
     * Newer gaps carry more weight so a prediction recovers from a one-off
     * long or short cycle instead of being anchored by it for six cycles.
     * The window is a ~68% prediction interval (mean ± 1 SD, inflated for
     * the uncertainty of estimating the mean), not the observed min–max.
     *
     * @param array<int,int> $lengths newest interval first
     * @return array{count:int,average:int,shortest:int,longest:int,spread:int,weighted_sd:float,band_low:int,band_high:int,probability:?int}
     */
    private function summarizeLengths(array $lengths): array
    {
        if ($lengths === []) {
            return [
                'count' => 0,
                'average' => self::DEFAULT_CYCLE_LENGTH,
                'shortest' => self::DEFAULT_CYCLE_LENGTH,
                'longest' => self::DEFAULT_CYCLE_LENGTH,
                'spread' => 0,
                'weighted_sd' => 0.0,
                'band_low' => self::DEFAULT_CYCLE_LENGTH,
                'band_high' => self::DEFAULT_CYCLE_LENGTH,
                'probability' => null,
            ];
        }

        $count = count($lengths);
        $totalWeight = $count * ($count + 1) / 2;
        $weightedSum = 0;

        foreach ($lengths as $index => $length) {
            // Newest interval gets weight n, the oldest gets 1.
            $weightedSum += $length * ($count - $index);
        }

        $average = (int) round($weightedSum / $totalWeight);
        $average = max(self::MIN_PLAUSIBLE_CYCLE, min(self::MAX_PLAUSIBLE_CYCLE, $average));

        $variance = 0.0;
        foreach ($lengths as $index => $length) {
            $variance += ($count - $index) * pow($length - $average, 2);
        }

        $sd = sqrt($variance / $totalWeight);
        $margin = $sd * sqrt(1 + 1 / $count);

        $low = max(self::MIN_PLAUSIBLE_CYCLE, (int) floor($average - $margin));
        $high = min(self::MAX_PLAUSIBLE_CYCLE, (int) ceil($average + $margin));

        return [
            'count' => $count,
            'average' => $average,
            'shortest' => min($lengths),
            'longest' => max($lengths),
            'spread' => max($lengths) - min($lengths),
            'weighted_sd' => $sd,
            'band_low' => min($low, $average),
            'band_high' => max($high, $average),
            'probability' => $count >= 3 && $sd > 0 ? 68 : null,
        ];
    }

    /**
     * How many cycles ahead the next usable window is (1 = current window).
     * The window is advanced by whole cycles only once its latest possible
     * end has passed, so a stale log never renders a window in the past.
     *
     * @return array{0:int,1:bool} cycles ahead (1-based) and whether history is stale
     */
    private function projectionWindow(Carbon $lastPeriod, int $average, int $bandLow, int $bandHigh): array
    {
        $cyclesAhead = 1;

        while (
            $cyclesAhead < self::MAX_CYCLES_AHEAD
            && $lastPeriod->copy()->addDays($cyclesAhead * $bandHigh)->lt(today())
        ) {
            $cyclesAhead++;
        }

        return [$cyclesAhead, $cyclesAhead > 1];
    }

    private function projectedStart(Carbon $lastPeriod, int $average, int $bandLow, int $bandHigh): Carbon
    {
        [$cyclesAhead] = $this->projectionWindow($lastPeriod, $average, $bandLow, $bandHigh);

        return $lastPeriod->copy()->addDays($cyclesAhead * $average);
    }

    /**
     * Leave-one-out accuracy check against the patient's own logged starts.
     * Read-only: for each start with at least three prior gaps, compare the
     * actual start with the window the app would have shown at that time.
     *
     * @return array{checked:int,hits:int,rate:?int}
     */
    public function backtestWindow(int $userId, int $maxCycles = 12): array
    {
        $cycles = Cycle::where('user_id', $userId)
            ->whereDate('period_start_date', '<=', today())
            ->orderBy('period_start_date', 'desc')
            ->orderByDesc('id')
            ->take($maxCycles + 1)
            ->get();

        $gaps = [];
        foreach ($cycles as $index => $cycle) {
            $previous = $cycles->get($index + 1);
            if (!$previous) {
                break;
            }
            $gap = (int) $previous->period_start_date->diffInDays($cycle->period_start_date);
            $gaps[$index] = $gap >= self::MIN_PLAUSIBLE_CYCLE && $gap <= self::MAX_PLAUSIBLE_CYCLE
                ? $gap
                : null;
        }

        $checked = 0;
        $hits = 0;

        foreach ($cycles as $index => $cycle) {
            $anchor = $cycles->get($index + 1);
            if (!$anchor) {
                break;
            }

            // Gaps known when this cycle's predecessor was logged.
            $priors = [];
            for ($j = $index + 1; $j < count($gaps) && count($priors) < 6; $j++) {
                if ($gaps[$j] !== null) {
                    $priors[] = $gaps[$j];
                }
            }

            if (count($priors) < 3) {
                continue;
            }

            $stats = $this->summarizeLengths($priors);
            $earliest = $anchor->period_start_date->copy()->addDays($stats['band_low']);
            $latest = $anchor->period_start_date->copy()->addDays($stats['band_high']);

            $checked++;
            if ($cycle->period_start_date->gte($earliest) && $cycle->period_start_date->lte($latest)) {
                $hits++;
            }
        }

        return [
            'checked' => $checked,
            'hits' => $hits,
            'rate' => $checked > 0 ? (int) round($hits / $checked * 100) : null,
        ];
    }

    /**
     * Predict multiple future periods with fertility information
     *
     * @param int $userId
     * @param int $count
     * @return array
     */
    public function predictFuturePeriods(int $userId, int $count = 3): array
    {
        $cycles = $this->getRecentCycles($userId, 6);

        if ($cycles->isEmpty()) {
            return [];
        }

        $detail = $this->getPredictionDetail($userId);
        $averageCycle = $detail['average_cycle'];
        $averagePeriod = max(1, $this->getAveragePeriodLength($userId));

        // Same recency-weighted band the summary uses, so the calendar and the
        // hero can never show different windows for the same history.
        $bandLow = $detail['window_low_days'];
        $bandHigh = $detail['window_high_days'];
        $regularity = $detail['regularity'];
        $confidence = $detail['confidence'];
        $strengthLevel = $detail['strength_level'];
        $strengthLabel = $detail['strength_label'];

        // Start from the same window the summary shows (already advanced past
        // stale history), then accumulate uncertainty for later cycles.
        $anchor = $cycles->first()->period_start_date;
        [$cyclesAhead] = $this->projectionWindow($anchor, $averageCycle, $bandLow, $bandHigh);

        $predictions = [];

        for ($i = 0; $i < $count; $i++) {
            $periodsAhead = $cyclesAhead + $i;
            $start = $anchor->copy()->addDays($periodsAhead * $averageCycle);
            $end = $start->copy()->addDays($averagePeriod - 1);

            $earliest = $anchor->copy()->addDays($periodsAhead * $bandLow);
            $latest = $anchor->copy()->addDays($periodsAhead * $bandHigh);

            // Ovulation count-back is unreliable for irregular or very short
            // cycles, so hide it instead of showing a physiologically
            // impossible day.
            $fertilityReliable = $regularity === 'regular' && $averageCycle >= self::MIN_OVULATION_CYCLE;
            $ovulation = $fertilityReliable ? $start->copy()->addDays($averageCycle - 14) : null;
            $fertileWindowStart = $ovulation?->copy()->subDays(5);
            $fertileWindowEnd = $ovulation?->copy()->addDays(1);

            $predictions[] = [
                'period_start' => $start,
                'period_end' => $end,
                'period_start_earliest' => $earliest,
                'period_start_latest' => $latest,
                'cycle_number' => $i + 1,
                'ovulation' => $ovulation,
                'fertile_start' => $fertileWindowStart,
                'fertile_end' => $fertileWindowEnd,
                'regularity' => $regularity,
                'confidence' => $confidence,
                'strength_level' => $strengthLevel,
                'strength_label' => $strengthLabel,
                'note' => $regularity === 'irregular'
                    ? 'Irregular cycle detected — treat dates as a range, not an exact day. Log consistently and consult your midwife if variation persists.'
                    : ($regularity === 'somewhat_irregular'
                        ? 'Slightly irregular cycle — prediction may shift by a few days.'
                        : ($regularity === 'insufficient_data' ? 'Limited history: this is a low-confidence estimate, using 28 days when no completed interval is available.' : null)),
            ];
        }

        return $predictions;
    }

    /**
     * Irregular-aware prediction summary for one patient.
     *
     * Returns average/shortest/longest cycle, regularity, confidence
     * (high|medium|low) and the earliest/latest next-period window so
     * views can render a RANGE for irregular patients instead of a
     * single misleading date. Works with any history length:
     * 1-2 cycles → low confidence average fallback; 3+ → measured.
     */
    public function getPredictionDetail(int $userId): array
    {
        $history = $this->analyzeHistory($userId, 6);
        $cycles = $history['cycles'];
        $regularity = $this->getCycleRegularity($userId);

        $confidence = match (true) {
            $history['count'] < 3 => 'low',
            $regularity === 'regular' => 'high',
            $regularity === 'somewhat_irregular' => 'medium',
            default => 'low',
        };

        $strength = $this->getStrengthLevel($history['count'], $regularity, $history['spread']);

        // A 68% band is only honest once there are enough gaps to estimate
        // variability, and not for fully irregular cycles with heavy tails.
        $probability = in_array($regularity, ['regular', 'somewhat_irregular'], true)
            ? $history['probability']
            : null;

        $lastCycle = $cycles->first();
        $next = null;
        $earliest = null;
        $latest = null;
        $isStale = false;

        if ($lastCycle) {
            [$cyclesAhead, $isStale] = $this->projectionWindow(
                $lastCycle->period_start_date,
                $history['average'],
                $history['band_low'],
                $history['band_high']
            );
            $base = $lastCycle->period_start_date->copy();
            $next = $base->copy()->addDays($cyclesAhead * $history['average']);
            $earliest = $base->copy()->addDays($cyclesAhead * $history['band_low']);
            $latest = $base->copy()->addDays($cyclesAhead * $history['band_high']);
        }

        return [
            'average_cycle' => $history['average'],
            'shortest_cycle' => $history['shortest'],
            'longest_cycle' => $history['longest'],
            'spread_days' => $history['spread'],
            'window_low_days' => $history['band_low'],
            'window_high_days' => $history['band_high'],
            'probability' => $probability,
            'regularity' => $regularity,
            'confidence' => $confidence,
            'strength_level' => $strength['level'],
            'strength_label' => $strength['label'],
            'cycles_used' => $history['count'],
            'last_period_date' => $lastCycle?->period_start_date?->copy(),
            'is_stale' => $isStale,
            'next_predicted' => $next,
            'next_earliest' => $earliest,
            'next_latest' => $latest,
        ];
    }

    /**
     * Recency-weighted average cycle length, same number the window uses.
     *
     * @param int $userId
     * @param int $maxCycles
     * @return int|null
     */
    public function getAverageCycleLength(int $userId, int $maxCycles = 6): ?int
    {
        $cycles = $this->getRecentCycles($userId, $maxCycles);

        if ($cycles->count() < 1) {
            return null;
        }

        $lengths = $cycles->pluck('cycle_length')->filter()->values()->all();

        return $this->summarizeLengths($lengths)['average'];
    }

    /**
     * Calculate average period length
     *
     * @param int $userId
     * @param int $maxCycles
     * @return int
     */
    public function getAveragePeriodLength(int $userId, int $maxCycles = 6): int
    {
        $cycles = Cycle::where('user_id', $userId)
            ->whereNotNull('period_end_date')
            ->orderBy('period_start_date', 'desc')
            ->take($maxCycles)
            ->get();

        if ($cycles->isEmpty()) {
            return 5; // Default
        }

        $totalDays = $cycles->sum(function ($cycle) {
            return $cycle->period_length ?? 0;
        });

        return (int) round($totalDays / $cycles->count());
    }

    /**
     * Four-level prediction strength.
     *
     * Level 4 (Strong) requires at least three completed intervals with a
     * tight spread (≤3 days) — regularity alone is not enough evidence.
     * Level 3 (High): 3+ regular intervals with wider spread; Level 2
     * (Moderate): 3+ slightly irregular intervals; Level 1 (Weak):
     * limited or irregular history.
     *
     * @return array{level:int,label:string}
     */
    private function getStrengthLevel(int $intervalCount, string $regularity, int $spread): array
    {
        $level = match (true) {
            $intervalCount >= 3 && $regularity === 'regular' && $spread <= 3 => 4,
            $intervalCount >= 3 && $regularity === 'regular' => 3,
            $intervalCount >= 3 && $regularity === 'somewhat_irregular' => 2,
            default => 1,
        };

        return [
            'level' => $level,
            'label' => [1 => 'Weak', 2 => 'Moderate', 3 => 'High', 4 => 'Strong'][$level],
        ];
    }

    /**
     * Get cycle regularity status
     *
     * @param int $userId
     * @return string
     */
    public function getCycleRegularity(int $userId): string
    {
        $cycles = $this->getRecentCycles($userId, 6);

        if ($cycles->count() < 3) {
            return 'insufficient_data';
        }

        $cycleLengths = $cycles->pluck('cycle_length')->filter();

        if ($cycleLengths->count() < 3) {
            return 'insufficient_data';
        }

        $variance = $this->calculateVariance($cycleLengths->toArray());

        if ($variance <= 3) {
            return 'regular';
        } elseif ($variance <= 7) {
            return 'somewhat_irregular';
        } else {
            return 'irregular';
        }
    }

    /**
     * Get complete cycle statistics
     *
     * @param int $userId
     * @return array
     */
    public function getCycleStatistics(int $userId): array
    {
        $cycles = $this->getRecentCycles($userId, 12);
        $nextPeriod = $this->predictNextPeriod($userId);
        $futurePeriods = $this->predictFuturePeriods($userId, 3);
        $detail = $this->getPredictionDetail($userId);

        return [
            'total_cycles_recorded' => Cycle::where('user_id', $userId)->count(),
            'average_cycle_length' => $this->getAverageCycleLength($userId),
            'average_period_length' => $this->getAveragePeriodLength($userId),
            'shortest_cycle' => $detail['shortest_cycle'],
            'longest_cycle' => $detail['longest_cycle'],
            'regularity' => $detail['regularity'],
            'confidence' => $detail['confidence'],
            'strength_level' => $detail['strength_level'],
            'strength_label' => $detail['strength_label'],
            'next_predicted_period' => $nextPeriod?->format('Y-m-d'),
            'next_earliest' => $detail['next_earliest']?->format('Y-m-d'),
            'next_latest' => $detail['next_latest']?->format('Y-m-d'),
            'days_until_next' => $nextPeriod ? (int) today()->diffInDays($nextPeriod, false) : null,
            'future_predictions' => $futurePeriods,
            'recent_cycles' => $cycles->take(6),
        ];
    }

    /**
     * Get calendar data with periods and predictions
     *
     * @param int $userId
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getCalendarData(int $userId, int $year, int $month): array
    {
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Get cycles overlapping with this month
        $cycles = Cycle::where('user_id', $userId)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('period_start_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('period_end_date', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->where('period_start_date', '<=', $startOfMonth)
                            ->where('period_end_date', '>=', $endOfMonth);
                    });
            })
            ->orderBy('period_start_date', 'asc')
            ->get();

        // Get predictions for future periods
        $predictions = $this->predictFuturePeriods($userId, 3);
        $detail = $this->getPredictionDetail($userId);

        // Add current cycle ovulation and fertile window if cycle exists in this month
        $currentCyclePrediction = null;
        if ($cycles->isNotEmpty()) {
            $lastCycle = $cycles->last();
            $averageCycle = $detail['average_cycle'];
            if ($averageCycle >= self::MIN_OVULATION_CYCLE && $lastCycle->period_end_date && $detail['regularity'] === 'regular') {
                // Simple ovulation calculation (14 days before next period)
                $ovulation = $lastCycle->period_start_date->copy()->addDays($averageCycle - 14);
                $fertileWindowStart = $ovulation->copy()->subDays(5);
                $fertileWindowEnd = $ovulation->copy()->addDays(1);
                
                $currentCyclePrediction = [
                    'period_start' => $lastCycle->period_start_date,
                    'period_end' => $lastCycle->period_end_date,
                    'ovulation' => $ovulation,
                    'fertile_start' => $fertileWindowStart,
                    'fertile_end' => $fertileWindowEnd,
                ];
            }
        }

        // Generate calendar days
        $calendarDays = $this->generateCalendarDays($cycles, $predictions, $year, $month, $currentCyclePrediction);

        return [
            'cycles' => $cycles,
            'predictions' => $predictions,
            'current_cycle_prediction' => $currentCyclePrediction,
            'calendar_days' => $calendarDays,
            'average_cycle_length' => $detail['average_cycle'],
            'average_period_length' => $this->getAveragePeriodLength($userId),
            'regularity' => $detail['regularity'],
            'prediction_detail' => $detail,
        ];
    }

    /**
     * Generate calendar days with period and prediction data
     *
     * @param \Illuminate\Database\Eloquent\Collection $cycles
     * @param array $predictions
     * @param int $year
     * @param int $month
     * @param array|null $currentCyclePrediction
     * @return array
     */
    private function generateCalendarDays($cycles, array $predictions, int $year, int $month, $currentCyclePrediction = null): array
    {
        $calendarDays = [];
        $firstDayOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
        $current = $firstDayOfMonth->copy()->subDay($firstDayOfMonth->dayOfWeek);
        
        while ($current <= $lastDayOfMonth->copy()->addDay(6 - $lastDayOfMonth->dayOfWeek)) {
            $dayData = [
                'date' => $current->copy(),
                'is_current_month' => $current->month == $month,
                'is_today' => $current->isToday(),
                'period' => false,
                'predicted_period' => false,
                'ovulation' => false,
                'fertile' => false,
                'non_fertile' => false,
            ];
            
            // Check if this day is in a recorded period
            foreach ($cycles as $cycle) {
                $periodEnd = $cycle->period_end_date ?? $cycle->period_start_date;
                if ($current->between($cycle->period_start_date, $periodEnd)) {
                    $dayData['period'] = true;
                    break;
                }
            }
            
            // Check if this day is in a predicted period or fertile window
            foreach ($predictions as $prediction) {
                // Check predicted period
                if (isset($prediction['period_start']) && isset($prediction['period_end'])) {
                    $earliest = $prediction['period_start_earliest'] ?? $prediction['period_start'];
                    $latestEnd = ($prediction['period_start_latest'] ?? $prediction['period_start'])->copy()
                        ->addDays((int) $prediction['period_start']->diffInDays($prediction['period_end']));
                    if ($current->between($earliest, $latestEnd)) {
                        $dayData['predicted_period'] = true;
                        break;
                    }
                }
                
                // Check ovulation (null for irregular cycles — no single day)
                if (!empty($prediction['ovulation']) && $current->equalTo($prediction['ovulation'])) {
                    $dayData['ovulation'] = true;
                }
                
                // Check fertile window
                if (isset($prediction['fertile_start']) && isset($prediction['fertile_end'])) {
                    if ($current->between($prediction['fertile_start'], $prediction['fertile_end'])) {
                        $dayData['fertile'] = true;
                    }
                }
                
                // Check non-fertile windows in predictions
                if (isset($prediction['non_fertile_windows'])) {
                    foreach ($prediction['non_fertile_windows'] as $nonFertileWindow) {
                        if (isset($nonFertileWindow['start']) && isset($nonFertileWindow['end'])) {
                            if ($current->between($nonFertileWindow['start'], $nonFertileWindow['end'])) {
                                $dayData['non_fertile'] = true;
                                break;
                            }
                        }
                    }
                }
            }
            
            // Check current cycle ovulation and fertile window
            if ($currentCyclePrediction) {
                // Check ovulation
                if (isset($currentCyclePrediction['ovulation']) && $current->equalTo($currentCyclePrediction['ovulation'])) {
                    $dayData['ovulation'] = true;
                }
                
                // Check fertile window
                if (isset($currentCyclePrediction['fertile_start']) && isset($currentCyclePrediction['fertile_end'])) {
                    if ($current->between($currentCyclePrediction['fertile_start'], $currentCyclePrediction['fertile_end'])) {
                        $dayData['fertile'] = true;
                    }
                }
                
            }
            
            $calendarDays[] = $dayData;
            $current->addDay();
        }
        
        return $calendarDays;
    }

    /**
     * Calculate cycle length for a given cycle
     *
     * @param Cycle $cycle
     * @return int|null
     */
    public function calculateCycleLength(Cycle $cycle): ?int
    {
        $previousCycle = Cycle::where('user_id', $cycle->user_id)
            ->where('period_start_date', '<', $cycle->period_start_date)
            ->orderBy('period_start_date', 'desc')
            ->first();

        if (!$previousCycle) {
            return null;
        }

        return $previousCycle->period_start_date->diffInDays($cycle->period_start_date);
    }

    /**
     * Recalculate all cycle lengths for a user
     *
     * @param int $userId
     * @return void
     */
    public function recalculateCycleLengths(int $userId): void
    {
        $cycles = Cycle::where('user_id', $userId)
            ->orderBy('period_start_date', 'asc')
            ->get();

        foreach ($cycles as $cycle) {
            $cycleLength = $this->calculateCycleLength($cycle);
            $cycle->cycle_length = $cycleLength;
            $cycle->saveQuietly();
        }
    }

    /**
     * Get recent cycles for a user
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentCycles(int $userId, int $limit = 6)
    {
        // Derive intervals from dates, including the preceding record. This also
        // repairs predictions made from older, stale cached cycle_length values.
        $cycles = Cycle::where('user_id', $userId)
            ->whereDate('period_start_date', '<=', today())
            ->orderBy('period_start_date', 'desc')
            ->orderByDesc('id')
            ->take($limit + 1)
            ->get();
        foreach ($cycles as $index => $cycle) {
            $previous = $cycles->get($index + 1);
            $length = $previous ? (int) $previous->period_start_date->diffInDays($cycle->period_start_date) : null;
            $plausible = $length !== null
                && $length >= self::MIN_PLAUSIBLE_CYCLE
                && $length <= self::MAX_PLAUSIBLE_CYCLE;
            $cycle->cycle_length = $plausible ? $length : null;
        }
        return $cycles->take($limit);
    }

    /**
     * Calculate variance for cycle regularity
     *
     * @param array $values
     * @return float
     */
    private function calculateVariance(array $values): float
    {
        if (count($values) < 2) {
            return 0;
        }

        $mean = array_sum($values) / count($values);
        $squaredDiffs = array_map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values);

        return sqrt(array_sum($squaredDiffs) / count($squaredDiffs));
    }
}
