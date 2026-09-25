<?php

namespace App\Services;

use App\Models\Cycle;
use Carbon\Carbon;

class CyclePredictionService
{
    /**
     * Predict the next period date based on cycle history
     *
     * @param int $userId
     * @return Carbon|null
     */
    public function predictNextPeriod(int $userId): ?Carbon
    {
        $cycles = $this->getRecentCycles($userId, 6);

        if ($cycles->isEmpty()) {
            return null;
        }

        $averageCycle = $this->calculateAverageCycleLength($cycles);
        $lastCycle = $cycles->first();

        return $lastCycle->period_start_date->copy()->addDays($averageCycle);
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

        $averageCycle = $this->calculateAverageCycleLength($cycles);
        $averagePeriod = $this->getAveragePeriodLength($userId);
        $nextPeriod = $this->predictNextPeriod($userId);

        if (!$nextPeriod) {
            return [];
        }

        // Irregular-aware range: shortest/longest observed cycle bounds the window.
        $detail = $this->getPredictionDetail($userId);
        $shortest = $detail['shortest_cycle'] ?? $averageCycle;
        $longest = $detail['longest_cycle'] ?? $averageCycle;
        $regularity = $detail['regularity'] ?? 'insufficient_data';
        $confidence = $detail['confidence'] ?? 'low';

        $predictions = [];

        for ($i = 0; $i < $count; $i++) {
            $start = $nextPeriod->copy()->addDays($i * $averageCycle);
            $end = $start->copy()->addDays($averagePeriod - 1);

            // Prediction window widens for irregular cycles (shortest..longest).
            // For cycle #1 the window is anchored on the last period; later
            // cycles accumulate uncertainty (+/- spread per cycle).
            $earliest = $cycles->first()->period_start_date->copy()->addDays(($i + 1) * $shortest);
            $latest = $cycles->first()->period_start_date->copy()->addDays(($i + 1) * $longest);

            // Ovulation estimate (avg - 14) is unreliable when irregular, so
            // expose it with confidence and widen the fertile window instead
            // of pretending precision.
            $ovulation = $start->copy()->addDays($averageCycle - 14);
            $fertileWindowStart = $ovulation->copy()->subDays($regularity === 'irregular' ? 7 : 5);
            $fertileWindowEnd = $ovulation->copy()->addDays($regularity === 'irregular' ? 2 : 1);

            $predictions[] = [
                'period_start' => $start,
                'period_end' => $end,
                'period_start_earliest' => $earliest,
                'period_start_latest' => $latest,
                'cycle_number' => $i + 1,
                'ovulation' => $regularity === 'regular' ? $ovulation : null,
                'fertile_start' => $regularity === 'regular' ? $fertileWindowStart : null,
                'fertile_end' => $regularity === 'regular' ? $fertileWindowEnd : null,
                'regularity' => $regularity,
                'confidence' => $confidence,
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
        $cycles = $this->getRecentCycles($userId, 6);
        $lengths = $cycles->pluck('cycle_length')->filter()->values();

        $average = $this->calculateAverageCycleLength($cycles);
        $shortest = $lengths->isNotEmpty() ? (int) $lengths->min() : $average;
        $longest = $lengths->isNotEmpty() ? (int) $lengths->max() : $average;
        $regularity = $this->getCycleRegularity($userId);
        $spread = $longest - $shortest;

        $confidence = match (true) {
            $lengths->count() < 3 => 'low',
            $regularity === 'regular' => 'high',
            $regularity === 'somewhat_irregular' => 'medium',
            default => 'low',
        };

        $next = $this->predictNextPeriod($userId);
        $lastCycle = $cycles->first();

        return [
            'average_cycle' => $average,
            'shortest_cycle' => $shortest,
            'longest_cycle' => $longest,
            'spread_days' => $spread,
            'regularity' => $regularity,
            'confidence' => $confidence,
            'cycles_used' => $lengths->count(),
            'next_predicted' => $next?->copy(),
            'next_earliest' => ($lastCycle && $lengths->count() >= 2)
                ? $lastCycle->period_start_date->copy()->addDays($shortest)
                : $next?->copy(),
            'next_latest' => ($lastCycle && $lengths->count() >= 2)
                ? $lastCycle->period_start_date->copy()->addDays($longest)
                : $next?->copy(),
        ];
    }

    /**
     * Calculate average cycle length from cycle history
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

        $cycleLengths = $cycles->pluck('cycle_length')->filter();

        if ($cycleLengths->isEmpty()) {
            return 28; // Default
        }

        return (int) round($cycleLengths->avg());
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
            'shortest_cycle' => $cycles->min('cycle_length') ?? $detail['shortest_cycle'],
            'longest_cycle' => $cycles->max('cycle_length') ?? $detail['longest_cycle'],
            'regularity' => $detail['regularity'],
            'confidence' => $detail['confidence'],
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

        // Add current cycle ovulation and fertile window if cycle exists in this month
        $currentCyclePrediction = null;
        if ($cycles->isNotEmpty()) {
            $lastCycle = $cycles->last();
            $averageCycle = $this->getAverageCycleLength($userId);
            if ($averageCycle && $lastCycle->period_end_date && $this->getCycleRegularity($userId) === 'regular') {
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
            'average_cycle_length' => $this->getAverageCycleLength($userId),
            'average_period_length' => $this->getAveragePeriodLength($userId),
            'regularity' => $this->getCycleRegularity($userId),
            'prediction_detail' => $this->getPredictionDetail($userId),
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
            $cycle->cycle_length = $length > 0 ? $length : null;
        }
        return $cycles->take($limit);
    }

    /**
     * Calculate average from cycle collection
     *
     * @param \Illuminate\Database\Eloquent\Collection $cycles
     * @return int
     */
    private function calculateAverageCycleLength($cycles): int
    {
        $validCycles = $cycles->filter(function ($cycle) {
            return $cycle->cycle_length !== null;
        });

        if ($validCycles->isEmpty()) {
            return 28; // Default cycle length
        }

        return (int) round($validCycles->avg('cycle_length'));
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
