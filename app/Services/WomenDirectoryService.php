<?php

namespace App\Services;

use App\Models\Barangay;
use App\Models\Checkup;
use App\Models\CheckupReferral;
use App\Models\HealthRecord;
use App\Models\Pregnancy;
use App\Models\User;
use App\Models\WalkInPatient;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Combined registered + unregistered women directory.
 *
 * RHU accounts are constrained to the barangays mapped to their assigned
 * RHU. CHO accounts are city-wide (null catchment). The same helpers are
 * reused by the record pages so a woman outside the caller's catchment can
 * never be opened by guessing her id.
 */
class WomenDirectoryService
{
    public const TYPES = ['registered', 'unregistered'];

    public function __construct(private AnalyticsScope $scope)
    {
    }

    /** Null means city-wide; an array is the caller's allowed barangay keys. */
    public function catchmentFor(User $user): ?array
    {
        if ($user->role === 'cho') {
            return null;
        }

        abort_unless($user->role === 'rhu', 403);

        $assigned = $this->scope->normalizeRhu($user->rhu_assignment);
        abort_unless($assigned, 403, 'Your RHU assignment is missing. Ask CHO to assign your account to RHU 1–5.');

        return $this->scope->areas($assigned);
    }

    public function scopeLabel(User $user): string
    {
        if ($user->role === 'cho') {
            return 'City-wide · All barangays';
        }

        return $this->scope->normalizeRhu($user->rhu_assignment) . ' catchment';
    }

    public function inScope(?string $barangay, ?array $catchment): bool
    {
        return $catchment === null
            || in_array($this->scope->key($barangay), $catchment, true);
    }

    /**
     * @return array{women: LengthAwarePaginator, registeredCount: int,
     *     unregisteredCount: int, barangays: \Illuminate\Support\Collection,
     *     statuses: array, scopeLabel: string, catchment: ?array}
     */
    public function directory(User $user, array $filters = []): array
    {
        $catchment = $this->catchmentFor($user);
        $search = mb_strtolower(trim((string) ($filters['search'] ?? '')));
        $type = in_array(($filters['type'] ?? 'all'), self::TYPES, true) ? $filters['type'] : 'all';
        $status = (string) ($filters['status'] ?? 'all');
        $barangay = trim((string) ($filters['barangay'] ?? ''));

        $registered = User::query()
            ->where('role', 'user')
            ->with(['purok', 'pregnancies' => fn ($query) => $query->active()])
            ->latest()
            ->get()
            ->filter(fn (User $woman) => $this->inScope($woman->barangay, $catchment))
            ->each(fn (User $woman) => $woman->type = 'registered');

        $unregistered = WalkInPatient::query()
            ->with(['purok', 'recordedBy', 'pregnancies' => fn ($query) => $query->active()])
            ->whereNull('user_id')
            ->whereNull('converted_to_user_id')
            ->latest()
            ->get()
            ->filter(fn (WalkInPatient $woman) => $this->inScope($woman->barangay, $catchment))
            ->each(fn (WalkInPatient $woman) => $woman->type = 'unregistered');

        $registeredCount = $registered->count();
        $unregisteredCount = $unregistered->count();

        if ($search !== '') {
            $registered = $registered->filter(fn (User $woman) => $this->matchesSearch($search, [
                $woman->name, $woman->email, $woman->contact_number,
                $woman->address, $woman->barangay, $woman->purok?->name,
            ]));
            $unregistered = $unregistered->filter(fn (WalkInPatient $woman) => $this->matchesSearch($search, [
                $woman->full_name, $woman->contact_number,
                $woman->address, $woman->barangay, $woman->purok?->name,
            ]));
        }

        if ($barangay !== '') {
            $key = $this->scope->key($barangay);
            $registered = $registered->filter(fn (User $woman) => $this->scope->key($woman->barangay) === $key);
            $unregistered = $unregistered->filter(fn (WalkInPatient $woman) => $this->scope->key($woman->barangay) === $key);
        }

        // Account status only exists for registered women. Choosing one
        // therefore narrows the directory to portal accounts.
        if ($status !== 'all') {
            $registered = $registered->where('status', $status);
            $unregistered = collect();
        }

        if ($type === 'registered') {
            $unregistered = collect();
        } elseif ($type === 'unregistered') {
            $registered = collect();
        }

        $all = $registered->concat($unregistered)
            ->sortBy(fn ($woman) => mb_strtolower($woman->type === 'unregistered' ? $woman->full_name : $woman->name))
            ->values();

        $page = max(1, (int) request()->input('page', 1));
        $perPage = 15;
        $women = new LengthAwarePaginator(
            $all->forPage($page, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $barangays = Barangay::query()->active()->orderBy('name')->get(['name'])
            ->filter(fn ($area) => $catchment === null
                || in_array($this->scope->key($area->name), $catchment, true))
            ->pluck('name')
            ->values();

        return [
            'women' => $women,
            'registeredCount' => $registeredCount,
            'unregisteredCount' => $unregisteredCount,
            'barangays' => $barangays,
            'statuses' => ['approved', 'pending', 'rejected', 'suspended', 'inactive', 'archived'],
            'scopeLabel' => $this->scopeLabel($user),
            'catchment' => $catchment,
        ];
    }

    /**
     * Load one woman plus her history, enforcing the caller's catchment.
     *
     * @return array{woman: User|WalkInPatient, pregnancies: \Illuminate\Database\Eloquent\Collection,
     *     checkups: \Illuminate\Database\Eloquent\Collection,
     *     healthRecords: \Illuminate\Database\Eloquent\Collection,
     *     referrals: \Illuminate\Database\Eloquent\Collection}
     */
    public function findForUser(User $user, string $type, int $id): array
    {
        abort_unless(in_array($type, self::TYPES, true), 404);

        $catchment = $this->catchmentFor($user);

        if ($type === 'registered') {
            $woman = User::query()
                ->where('role', 'user')
                ->with(['purok', 'emergencyContacts'])
                ->findOrFail($id);

            abort_unless($this->inScope($woman->barangay, $catchment), 404);

            return [
                'woman' => $woman,
                'pregnancies' => Pregnancy::where('user_id', $id)->latest()->get(),
                'checkups' => Checkup::where('user_id', $id)
                    ->with('midwife:id,first_name,middle_initial,last_name')
                    ->latest()
                    ->limit(50)
                    ->get(),
                'healthRecords' => HealthRecord::where('user_id', $id)
                    ->with('recordedBy:id,first_name,middle_initial,last_name')
                    ->latest()
                    ->limit(50)
                    ->get(),
                'referrals' => collect(),
            ];
        }

        $woman = WalkInPatient::query()
            ->with(['purok', 'recordedBy'])
            ->whereNull('user_id')
            ->whereNull('converted_to_user_id')
            ->findOrFail($id);

        abort_unless($this->inScope($woman->barangay, $catchment), 404);

        return [
            'woman' => $woman,
            'pregnancies' => Pregnancy::where('walk_in_patient_id', $id)->latest()->get(),
            'checkups' => Checkup::where('walk_in_patient_id', $id)
                ->with('midwife:id,first_name,middle_initial,last_name')
                ->latest()
                ->limit(50)
                ->get(),
            'healthRecords' => HealthRecord::where('walk_in_patient_id', $id)
                ->with('recordedBy:id,first_name,middle_initial,last_name')
                ->latest()
                ->limit(50)
                ->get(),
            'referrals' => CheckupReferral::where('walk_in_patient_id', $id)->latest()->get(),
        ];
    }

    private function matchesSearch(string $needle, array $values): bool
    {
        foreach ($values as $value) {
            if ($value !== null && str_contains(mb_strtolower((string) $value), $needle)) {
                return true;
            }
        }

        return false;
    }
}
