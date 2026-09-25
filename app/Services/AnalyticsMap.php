<?php

namespace App\Services;

use App\Models\Barangay;
use App\Models\Purok;
use Illuminate\Support\Facades\Schema;

class AnalyticsMap
{
    /** Map the same area totals as the tables, without patient coordinates. */
    public function build(array $report): array
    {
        $scope = app(AnalyticsScope::class);
        $locations = Schema::hasTable('puroks') && Schema::hasColumn('puroks', 'latitude')
            ? Purok::whereNotNull('latitude')->whereNotNull('longitude')->get(['barangay', 'latitude', 'longitude'])
                ->filter(fn ($p) => is_numeric($p->latitude) && is_numeric($p->longitude)
                    && abs((float) $p->latitude) <= 90 && abs((float) $p->longitude) <= 180
                    && ((float) $p->latitude !== 0.0 || (float) $p->longitude !== 0.0))
                ->groupBy(fn ($p) => $scope->key($p->barangay)) : collect();
        $rhuAssignments = Schema::hasTable('barangays')
            ? Barangay::active()->get(['name', 'rhu_assignment'])
                ->mapWithKeys(fn ($barangay) => [$scope->key($barangay->name) => $barangay->rhu_assignment])
            : collect();
        $mapped = []; $unmapped = []; $statuses = [];
        foreach ($report['areas'] as $area) {
            $areaKey = $scope->key($area['label']);
            $points = $locations->get($areaKey);
            $reference = config('analytics_locations')[$areaKey] ?? null;
            $rhu = $rhuAssignments->get($areaKey, 'Not assigned');
            $position = $points && $points->isNotEmpty()
                ? ['lat' => $points->avg('latitude'), 'lng' => $points->avg('longitude'), 'location_basis' => 'Average of stored purok coordinates', 'location_source' => null]
                : ($reference ? ['lat' => $reference['lat'], 'lng' => $reference['lng'], 'location_basis' => 'Published approximate barangay reference point', 'location_source' => $reference['source']] : null);
            $risk = $area['risk_counts'] ?? [];
            [$token, $label] = match (true) {
                ($area['emergencies'] ?? 0) > 0 => ['danger', 'Emergency flag recorded'],
                ($area['high_risk'] ?? 0) > 0 => ['danger', 'High / Critical risk recorded'],
                ($risk['Medium'] ?? 0) > 0 => ['warning', 'Medium risk recorded'],
                ($risk['Unassessed'] ?? 0) > 0 => ['text-muted', 'Assessment incomplete'],
                ($risk['Low'] ?? 0) > 0 => ['success', 'Low risk recorded'],
                default => ['text-muted', 'No current assessments'],
            };
            $statuses[] = $area + ['rhu_assignment' => $rhu, 'status_color' => $token, 'status_label' => $label,
                'has_coordinates' => $position !== null];
            if (!$position) { $unmapped[] = $area['label']; continue; }
            $mapped[] = $area + ['rhu_assignment' => $rhu, 'status_color' => $token, 'status_label' => $label,
                ...$position];
        }
        return ['areas' => $mapped, 'unmapped' => $unmapped, 'statuses' => $statuses];
    }
}
