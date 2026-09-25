<?php

namespace App\Services;

use App\Models\Checkup;
use App\Models\Barangay;
use App\Models\MaternalDeath;
use App\Models\MaternalMorbidity;
use App\Models\Pregnancy;
use App\Models\User;
use App\Models\WalkInPatient;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/** Read-only decision support. Never evaluates risk, updates records, or sends alerts. */
class MaternalAnalyticsService
{
    public const UNKNOWN_AREA = '__unrecorded__';

    private const RISK_ORDER = ['Critical' => 5, 'High' => 4, 'Medium' => 3, 'Unassessed' => 2, 'Low' => 1];

    public function report(array $filters): array
    {
        $from = Carbon::parse($filters['from'])->startOfDay();
        $to = Carbon::parse($filters['to'])->endOfDay();
        $area = $filters['barangay'] ?? null;
        $scope = app(AnalyticsScope::class);
        $catchment = $scope->areas($filters['rhu'] ?? null);
        $relations = ['woman:id,first_name,last_name,barangay', 'walkInPatient:id,first_name,last_name,barangay'];

        $registrations = Pregnancy::with($relations)->whereBetween('created_at', [$from, $to])->get();
        // An EDD passing is not evidence of delivery. Keep overdue/open records visible.
        $open = Pregnancy::with($relations)->whereNull('ended_at')->whereNull('delivery_date')
            ->where(fn ($q) => $q->whereNull('outcome')->orWhere('outcome', ''))
            ->where('created_at', '<=', now())->get();
        $knownDeaths = MaternalDeath::whereDate('death_date', '<=', today())
            ->get(['pregnancy_id', 'user_id', 'walk_in_patient_id']);
        $deceasedPregnancies = array_fill_keys($knownDeaths->pluck('pregnancy_id')->filter()->all(), true);
        $deceasedUsers = array_fill_keys($knownDeaths->pluck('user_id')->filter()->all(), true);
        $deceasedWalkIns = array_fill_keys($knownDeaths->pluck('walk_in_patient_id')->filter()->all(), true);
        $open = $open->reject(fn ($p) => isset($deceasedPregnancies[$p->id])
            || isset($deceasedUsers[$p->user_id]) || isset($deceasedWalkIns[$p->walk_in_patient_id]));

        $registrations = $this->filterArea($registrations, $area, fn ($p) => $this->pregnancyArea($p));
        $open = $this->filterArea($open, $area, fn ($p) => $this->pregnancyArea($p));
        $deaths = $this->filterArea(MaternalDeath::whereBetween('death_date', [$from->toDateString(), $to->toDateString()])
            ->get(['death_date', 'barangay', 'cause_category', 'audit_status']), $area, fn ($d) => $this->area($d->barangay));
        $complications = $this->filterArea(MaternalMorbidity::whereBetween('event_date', [$from->toDateString(), $to->toDateString()])
            ->get(['event_date', 'barangay']), $area, fn ($m) => $this->area($m->barangay));

        if ($catchment !== null) {
            $registrations = $registrations->filter(fn ($p) => in_array($scope->key($this->pregnancyArea($p)), $catchment, true));
            $open = $open->filter(fn ($p) => in_array($scope->key($this->pregnancyArea($p)), $catchment, true));
            $deaths = $deaths->filter(fn ($d) => in_array($scope->key($d->barangay), $catchment, true));
            $complications = $complications->filter(fn ($m) => in_array($scope->key($m->barangay), $catchment, true));
        }

        $open->load(['healthRecords' => fn ($q) => $q
            ->where('created_at', '<=', now())->orderByDesc('id')
            ->select(['id', 'pregnancy_id', 'risk_level', 'is_emergency', 'created_at'])]);
        // Only appointments explicitly linked to this pregnancy belong to its queue entry.
        $checkups = Checkup::whereIn('pregnancy_id', $open->pluck('id'))
            ->whereDate('scheduled_date', '<', today())->whereIn('status', ['Missed', 'Scheduled'])
            ->get(['pregnancy_id', 'scheduled_date'])->groupBy('pregnancy_id');
        $queue = $open->map(fn ($p) => $this->queueEntry($p, $checkups->get($p->id, collect())))
            ->sort(function ($a, $b) {
                return ($b['emergency'] <=> $a['emergency'])
                    ?: (self::RISK_ORDER[$b['risk']] <=> self::RISK_ORDER[$a['risk']])
                    ?: ($b['care_gap_count'] <=> $a['care_gap_count'])
                    ?: (($a['edd'] ?? '9999-12-31') <=> ($b['edd'] ?? '9999-12-31'))
                    ?: ($a['pregnancy_id'] <=> $b['pregnancy_id']);
            })->values();

        $riskCounts = array_fill_keys(array_keys(self::RISK_ORDER), 0);
        foreach ($queue as $entry) {
            $riskCounts[$entry['risk']]++;
        }

        $monthly = [];
        for ($month = $from->copy()->startOfMonth(); $month->lte($to); $month->addMonth()) {
            $monthly[$month->format('Y-m')] = ['label' => $month->format('M Y'), 'registrations' => 0, 'deaths' => 0, 'complications' => 0];
        }
        $areas = [];
        foreach (['registrations' => $registrations, 'deaths' => $deaths, 'complications' => $complications] as $kind => $records) {
            foreach ($records as $record) {
                $date = match ($kind) {
                    'registrations' => $record->created_at,
                    'deaths' => $record->death_date,
                    'complications' => $record->event_date,
                };
                $monthly[$date->format('Y-m')][$kind]++;
                $key = $kind === 'registrations' ? $this->pregnancyArea($record) : $this->area($record->barangay);
                $areas[$key] ??= $this->emptyArea($key);
                $areas[$key][$kind]++;
            }
        }

        // A selected RHU must show its complete official catchment even before
        // any patients or events have been recorded there.
        if (!empty($filters['rhu'])) {
            Barangay::active()->forRhu($filters['rhu'])->pluck('name')->each(function ($name) use (&$areas) {
                $key = $this->area($name);
                $areas[$key] ??= $this->emptyArea($key);
            });
        }
        foreach ($queue as $entry) {
            $key = $entry['area_key'];
            $areas[$key] ??= $this->emptyArea($key);
            $areas[$key]['open']++;
            $areas[$key]['risk_counts'][$entry['risk']]++;
            $areas[$key]['emergencies'] += (int) $entry['emergency'];
            if (in_array($entry['risk'], ['Critical', 'High'], true)) {
                $areas[$key]['high_risk']++;
            }
        }

        return [
            'filters' => ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'barangay' => $area, 'rhu' => $filters['rhu'] ?? null],
            'scope_label' => $filters['rhu'] ?? 'City-wide · All RHUs',
            'scope_notice' => $catchment === [] ? 'No active barangays are mapped to this RHU. Configure barangay RHU assignments before interpreting these empty results.' : ($catchment !== null ? 'RHU totals include only matching mapped barangays. Unmapped or differently named areas are excluded; CHO can review them in the city-wide view.' : 'City-wide totals also include records with unmapped or unrecorded barangays.'),
            'generated_at' => now()->format('Y-m-d H:i'),
            'area_label' => $area ? $this->areaLabel($area) : 'All barangays',
            'totals' => [
                'open' => $queue->count(), 'high_risk' => $riskCounts['Critical'] + $riskCounts['High'],
                'emergency' => $queue->where('emergency', true)->count(),
                'registrations' => $registrations->count(), 'deaths' => $deaths->count(),
                'complications' => $complications->count(),
                'pending_death_reviews' => $deaths->whereIn('audit_status', ['pending', 'under_review'])->count(),
                'care_gaps' => $queue->where('care_gap_count', '>', 0)->count(),
                'past_due' => $queue->filter(fn ($r) => $r['edd'] && $r['edd'] < today()->toDateString())->count(),
                'unassessed' => $riskCounts['Unassessed'],
            ],
            'risk_counts' => $riskCounts,
            'monthly' => array_values($monthly),
            'areas' => collect($areas)->sortBy([['high_risk', 'desc'], ['deaths', 'desc'], ['label', 'asc']])->values()->all(),
            'causes' => $deaths->countBy(fn ($d) => $d->cause_category ?: 'Unrecorded')->all(),
            'queue' => $queue,
        ];
    }

    public function areaOptions(?string $rhu = null): array
    {
        $scope = app(AnalyticsScope::class);
        $catchment = $scope->areas($rhu);
        $configuredAreas = Barangay::active()
            ->when($rhu, fn ($query) => $query->forRhu($rhu))
            ->pluck('name');

        return $configuredAreas
            ->merge(User::where('role', 'user')->pluck('barangay'))
            ->merge(WalkInPatient::pluck('barangay'))
            ->merge(MaternalDeath::pluck('barangay'))->merge(MaternalMorbidity::pluck('barangay'))
            ->map(fn ($value) => $this->area($value))->push(self::UNKNOWN_AREA)->unique()->sort()
            ->filter(fn ($key) => $catchment === null || in_array($scope->key($key), $catchment, true))
            ->mapWithKeys(fn ($key) => [$key => $this->areaLabel($key)])->all();
    }

    /** Patient-level, read-only support using the same recorded facts as analytics. */
    public function patientSupport(User|WalkInPatient $patient, ?int $pregnancyId = null): Collection
    {
        $foreignKey = $patient instanceof User ? 'user_id' : 'walk_in_patient_id';
        if (MaternalDeath::where($foreignKey, $patient->id)->whereDate('death_date', '<=', today())->exists()) {
            return collect();
        }
        $pregnancies = Pregnancy::where($foreignKey, $patient->id)
            ->when($pregnancyId, fn ($q) => $q->whereKey($pregnancyId))
            ->whereNull('ended_at')->whereNull('delivery_date')
            ->where(fn ($q) => $q->whereNull('outcome')->orWhere('outcome', ''))
            ->where('created_at', '<=', now())
            ->whereNotIn('id', MaternalDeath::whereNotNull('pregnancy_id')->whereDate('death_date', '<=', today())->select('pregnancy_id'))
            ->with(['woman', 'walkInPatient', 'healthRecords' => fn ($q) => $q->where('created_at', '<=', now())->reorder()->orderByDesc('id')])
            ->latest('id')->get();
        $appointments = Checkup::whereIn('pregnancy_id', $pregnancies->pluck('id'))->get();
        return $pregnancies->map(function ($pregnancy) use ($appointments) {
            $linked = $appointments->where('pregnancy_id', $pregnancy->id);
            $gaps = $linked->filter(fn ($c) => in_array($c->status, ['Missed', 'Scheduled'], true)
                && $c->scheduled_date && Carbon::parse($c->scheduled_date)->startOfDay()->lt(today()));
            $entry = $this->queueEntry($pregnancy, $gaps);
            $next = $linked->filter(fn ($c) => $c->status === 'Scheduled' && $c->scheduled_date
                && Carbon::parse($c->scheduled_date)->startOfDay()->gte(today()))->sortBy('scheduled_date')->first();
            $entry['next_appointment'] = $next ? Carbon::parse($next->scheduled_date)->toDateString() : null;
            return $entry;
        })->sortByDesc(fn ($entry) => ($entry['emergency'] ? 10 : 0) + self::RISK_ORDER[$entry['risk']])->values();
    }

    private function queueEntry(Pregnancy $pregnancy, Collection $gaps): array
    {
        $latest = $pregnancy->healthRecords->first();
        // Preserve the more serious stored flag for review; do not infer a diagnosis.
        $levels = collect([$pregnancy->risk_level, $latest?->risk_level])
            ->filter(fn ($level) => isset(self::RISK_ORDER[$level]) && $level !== 'Unassessed');
        if ((bool) $pregnancy->getRawOriginal('is_high_risk')) {
            $levels->push('High');
        }
        $risk = $levels->sortByDesc(fn ($level) => self::RISK_ORDER[$level])->first() ?? 'Unassessed';
        $emergency = (bool) $latest?->is_emergency;
        $reasons = [$risk === 'Unassessed' ? 'No recognized risk assessment recorded.' : "Recorded risk: {$risk}."];
        if ($emergency) {
            $reasons[] = 'Latest pregnancy health record is marked as an emergency.';
        }
        if ($gaps->isNotEmpty()) {
            $reasons[] = $gaps->count().' missed or overdue appointment(s) linked to this pregnancy.';
        }
        $days = $pregnancy->edd ? (int) today()->diffInDays($pregnancy->edd, false) : null;
        if ($days === null) {
            $reasons[] = 'Expected delivery date is missing.';
        } elseif ($days < 0) {
            $reasons[] = 'Expected delivery date passed; pregnancy outcome needs confirmation.';
        } elseif ($days <= 14) {
            $reasons[] = "Expected delivery is in {$days} day(s).";
        }
        $action = match (true) {
            $emergency, $risk === 'Critical' => 'Contact the responsible clinician promptly to review the recorded alert and referral status.',
            $risk === 'High' => 'Prioritize a clinician review and confirm the existing follow-up plan.',
            $risk === 'Unassessed' => 'Ask the assigned clinician to complete or verify the risk assessment.',
            $gaps->isNotEmpty() => 'Ask the assigned health worker to confirm attendance and arrange follow-up.',
            default => 'Review the existing care plan and confirm the next scheduled contact.',
        };
        if ($days !== null && $days < 0) {
            $action .= ' Confirm whether delivery occurred and update the outcome.';
        }
        $key = $this->pregnancyArea($pregnancy);

        return [
            'pregnancy_id' => $pregnancy->id,
            'name' => preg_replace('/\s+/u', ' ', trim($pregnancy->patient_name)),
            'patient_type' => $pregnancy->user_id ? 'Registered patient' : 'Walk-in patient',
            'area_key' => $key, 'area' => $this->areaLabel($key),
            'risk' => $risk, 'emergency' => $emergency,
            'edd' => $pregnancy->edd?->toDateString(), 'care_gap_count' => $gaps->count(),
            'assessment_date' => $latest?->created_at?->toDateString(),
            'reasons' => $reasons, 'action' => $action,
        ];
    }

    private function area(?string $value): string
    {
        return app(AnalyticsScope::class)->canonicalArea($value) ?: self::UNKNOWN_AREA;
    }

    private function pregnancyArea(Pregnancy $pregnancy): string
    {
        return $this->area($pregnancy->woman?->barangay ?? $pregnancy->walkInPatient?->barangay);
    }

    private function areaLabel(string $key): string
    {
        return $key === self::UNKNOWN_AREA ? 'Unrecorded barangay' : $key;
    }

    private function filterArea(Collection $records, ?string $area, callable $key): Collection
    {
        return $area ? $records->filter(fn ($row) => app(AnalyticsScope::class)->key($key($row)) === app(AnalyticsScope::class)->key($area))->values() : $records;
    }

    private function emptyArea(string $key): array
    {
        return ['key' => $key, 'label' => $this->areaLabel($key), 'registrations' => 0, 'open' => 0, 'high_risk' => 0, 'deaths' => 0, 'complications' => 0,
            'risk_counts' => array_fill_keys(array_keys(self::RISK_ORDER), 0), 'emergencies' => 0];
    }
}
