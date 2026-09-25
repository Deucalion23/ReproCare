<?php

namespace App\Services;

use App\Models\Barangay;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AnalyticsScope
{
    public const RHUS = ['RHU 1', 'RHU 2', 'RHU 3', 'RHU 4', 'RHU 5'];

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
        return in_array(mb_strtolower($name), [
            'burgos st', 'burgos street', 'burgos - padlan st', 'burgos-padlan st',
            'barangay burgos', 'burgos padlan', 'barangay burgos padlan',
            'barangay burgos padlan, san carlos city, pangasinan',
        ], true) ? 'Burgos St' : $name;
    }
}
