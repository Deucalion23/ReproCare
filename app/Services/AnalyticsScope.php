<?php

namespace App\Services;

use App\Models\Barangay;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AnalyticsScope
{
    public const RHUS = ['RHU 1', 'RHU 2', 'RHU 3', 'RHU 4', 'RHU 5'];

    /**
     * Legacy spellings that all resolve to one canonical area. Keys are the
     * lower-cased canonical name, values are the accepted raw spellings.
     */
    public const AREA_ALIASES = [
        'burgos st' => [
            'burgos st',
            'burgos street',
            'burgos - padlan st',
            'burgos-padlan st',
            'barangay burgos',
            'burgos padlan',
            'barangay burgos padlan',
            'barangay burgos padlan, san carlos city, pangasinan',
        ],
    ];

    public function forUser(User $user, array $filters): array
    {
        if ($user->role === 'rhu') {
            $assigned = $this->normalizeRhu($user->rhu_assignment);
            abort_unless($assigned, 403, 'Your RHU assignment is missing. Ask CHO to assign your account to RHU 1–5.');
            abort_if(!empty($filters['rhu']) && $filters['rhu'] !== $assigned, 403, 'You can only view analytics for your assigned RHU.');
            $filters['rhu'] = $assigned;
        } else {
            abort_unless($user->role === 'cho', 403);
        }
        return $filters;
    }

    public function normalizeRhu(?string $value): ?string
    {
        $value = strtoupper(trim((string) $value));
        $roman = ['RHU I' => 'RHU 1', 'RHU II' => 'RHU 2', 'RHU III' => 'RHU 3', 'RHU IV' => 'RHU 4', 'RHU V' => 'RHU 5'];
        $value = $roman[$value] ?? $value;
        return in_array($value, self::RHUS, true) ? $value : null;
    }

    /** Null means city-wide; an empty array means no configured catchment. */
    public function areas(?string $rhu): ?array
    {
        if (!$rhu) return null;
        if (!Schema::hasTable('barangays')) return [];
        return Barangay::active()->get(['name', 'rhu_assignment'])
            ->filter(fn ($area) => $this->normalizeRhu($area->rhu_assignment) === $rhu)
            ->pluck('name')->map(fn ($name) => $this->key($name))->unique()->values()->all();
    }

    public function key(?string $area): string
    {
        return mb_strtolower($this->canonicalArea($area));
    }

    public function canonicalArea(?string $area): string
    {
        $name = preg_replace('/\s+/u', ' ', trim((string) $area));
        $lower = mb_strtolower($name);

        foreach (self::AREA_ALIASES as $canonical => $aliases) {
            if (in_array($lower, $aliases, true)) {
                return ucwords($canonical);
            }
        }

        return $name;
    }

    /**
     * SQL LIKE patterns that may hold a row belonging to the given canonical
     * key. The list is intentionally a superset of the exact PHP match so a
     * row is never hidden by a spelling variant; callers still re-check the
     * exact key before returning a record.
     *
     * @return array<int, string>
     */
    public function matchPatterns(string $canonicalKey): array
    {
        $key = mb_strtolower(trim($canonicalKey));
        if ($key === '') {
            return [];
        }

        $variants = array_merge([$key], self::AREA_ALIASES[$key] ?? []);

        $patterns = [];
        foreach ($variants as $variant) {
            $patterns[] = '%' . str_replace(' ', '%', mb_strtolower($variant)) . '%';
        }

        return array_values(array_unique($patterns));
    }
}
