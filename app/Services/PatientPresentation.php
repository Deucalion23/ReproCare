<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class PatientPresentation
{
    public function sampleIds(): array
    {
        $path = storage_path('app/private/demo-accounts/sample-record-registry.json');
        return is_file($path) ? (json_decode(file_get_contents($path), true)['user_ids'] ?? []) : [];
    }

    public function originalPatientsFirst($query, string $column = 'user_id')
    {
        $ids = array_map('intval', $this->sampleIds());
        if ($ids) $query->orderByRaw('CASE WHEN '.$column.' IN ('.implode(',', $ids).') THEN 1 ELSE 0 END');
        return $query;
    }

    public function sortPatients(Collection $patients): Collection
    {
        $ids = array_fill_keys($this->sampleIds(), true);
        return $patients->sortBy(fn ($p) => $p instanceof User && isset($ids[$p->id]) ? 1 : 0)->values();
    }

    public function barangayWorkers(?string $barangay): Collection
    {
        if (!trim((string) $barangay)) return collect();
        $key = app(AnalyticsScope::class)->key($barangay);
        $workers = request()->attributes->get('patient_bhw_directory');
        if ($workers === null) {
            $workers = User::where('role', 'bhw')->where('status', 'approved')->get();
            request()->attributes->set('patient_bhw_directory', $workers);
        }
        return $workers->filter(function ($worker) use ($key) {
            $areas = array_filter(array_merge([$worker->assigned_barangay], $worker->catchment_barangays ?? []));
            if (!$areas) $areas = [$worker->barangay]; // Legacy BHW accounts store their coverage here.
            return collect($areas)->contains(fn ($area) => app(AnalyticsScope::class)->key($area) === $key);
        })->values();
    }
}
