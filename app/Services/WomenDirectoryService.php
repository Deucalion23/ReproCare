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

    /** Sort keys available in the directory UI. */
    public const SORTS = [
        'date_desc' => 'Newest first',
        'date_asc' => 'Oldest first',
        'name_asc' => 'Name (A–Z)',
        'name_desc' => 'Name (Z–A)',
        'dob_asc' => 'Age (oldest)',
        'dob_desc' => 'Age (youngest)',
    ];

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
     *     unregisteredCount: int, totalCount: int, pregnantCount: int,
     *     pendingCount: int, resultCount: int,
     *     barangays: \Illuminate\Support\Collection,
     *     statuses: array, scopeLabel: string, catchment: ?array,
     *     sort: string, sorts: array, search: string, type: string,
     *     status: string, barangay: string, dateFrom: string, dateTo: string,
     *     pregnant: bool}
     */
    public function directory(User $user, array $filters = []): array
    {
        $catchment = $this->catchmentFor($user);

        $search = mb_strtolower(trim((string) ($filters['search'] ?? '')));
        $type = in_array(($filters['type'] ?? 'all'), self::TYPES, true) ? $filters['type'] : 'all';
        $status = trim((string) ($filters['status'] ?? 'all'));
        $barangay = trim((string) ($filters['barangay'] ?? ''));
        $sort = array_key_exists($filters['sort'] ?? '', self::SORTS) ? $filters['sort'] : 'date_desc';
        $dateFrom = $this->normaliseDate($filters['date_from'] ?? null);
        $dateTo = $this->normaliseDate($filters['date_to'] ?? null);
        $pregnant = filter_var($filters['pregnant'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $active = [
            'search' => $search,
            'status' => $status,
            'barangay' => $barangay,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'pregnant' => $pregnant,
        ];

        // Scope totals feed the summary cards and stay independent of the
        // filters currently applied to the table.
        $registeredTotal = $this->scopedCount(
            User::query()->where('role', 'user'),
            $catchment
        );
        $unregisteredTotal = $this->scopedCount(
            WalkInPatient::query()->whereNull('user_id')->whereNull('converted_to_user_id'),
            $catchment
        );
        $pregnantTotal = $this->scopedCount(
            User::query()->where('role', 'user')->whereHas('pregnancies', fn ($query) => $query->active()),
            $catchment
        ) + $this->scopedCount(
            WalkInPatient::query()
                ->whereNull('user_id')
                ->whereNull('converted_to_user_id')
                ->whereHas('pregnancies', fn ($query) => $query->active()),
            $catchment
        );
        $pendingTotal = $this->scopedCount(
            User::query()->where('role', 'user')->where('status', 'pending'),
            $catchment
        );

        $registered = $this->registeredRows($catchment, $active);
        $unregistered = $this->unregisteredRows($catchment, $active);

        // The source filter narrows the table. The account-status filter only
        // exists for portal accounts, so it never hides walk-in women.
        if ($type === 'registered') {
            $unregistered = collect();
        } elseif ($type === 'unregistered') {
            $registered = collect();
        }

        $nameOf = fn ($woman) => mb_strtolower($woman->type === 'unregistered' ? $woman->full_name : $woman->name);

        $all = $registered->concat($unregistered);

        $all = match ($sort) {
            'name_asc' => $all->sortBy($nameOf)->values(),
            'name_desc' => $all->sortBy($nameOf)->reverse()->values(),
            'dob_asc' => $all
                ->sortBy(fn ($woman) => $woman->date_of_birth?->timestamp ?? PHP_INT_MAX)
                ->values(),
            'dob_desc' => $all
                ->sortBy(fn ($woman) => $woman->date_of_birth?->timestamp ?? -1)
                ->reverse()
                ->values(),
            'date_asc' => $all->sortBy('created_at')->values(),
            default => $all->sortByDesc('created_at')->values(),
        };

        $resultCount = $all->count();

        $page = max(1, (int) request()->input('page', 1));
        $perPage = 15;
        $pageRows = $all->forPage($page, $perPage)->values();

        // Only the rows on the current page are hydrated with their
        // relations, so a long directory never loads every record twice.
        $registeredMap = User::query()
            ->with(['purok', 'pregnancies' => fn ($query) => $query->active()])
            ->whereIn('id', $pageRows->where('type', 'registered')->pluck('id')->all())
            ->get()
            ->keyBy('id');
        $walkInMap = WalkInPatient::query()
            ->with(['purok', 'pregnancies' => fn ($query) => $query->active()])
            ->whereIn('id', $pageRows->where('type', 'unregistered')->pluck('id')->all())
            ->get()
            ->keyBy('id');

        $rows = $pageRows->map(function ($row) use ($registeredMap, $walkInMap) {
            $model = $row->type === 'registered'
                ? $registeredMap->get($row->id)
                : $walkInMap->get($row->id);

            if ($model) {
                $model->type = $row->type;
            }

            return $model;
        })->filter()->values();

        $women = new LengthAwarePaginator(
            $rows,
            $resultCount,
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
            'registeredCount' => $registeredTotal,
            'unregisteredCount' => $unregisteredTotal,
            'totalCount' => $registeredTotal + $unregisteredTotal,
            'pregnantCount' => $pregnantTotal,
            'pendingCount' => $pendingTotal,
            'resultCount' => $resultCount,
            'barangays' => $barangays,
            'statuses' => ['approved', 'pending', 'rejected', 'suspended', 'inactive', 'archived'],
            'scopeLabel' => $this->scopeLabel($user),
            'catchment' => $catchment,
            'sort' => $sort,
            'sorts' => self::SORTS,
            'search' => $search,
            'type' => $type,
            'status' => $status,
            'barangay' => $barangay,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'pregnant' => $pregnant,
        ];
    }

    /** Registered women (portal accounts) matching the active filters. */
    private function registeredRows(?array $catchment, array $filters)
    {
        $query = User::query()->where('role', 'user');
        $this->applyScope($query, $catchment, 'barangay');
        $this->applyRegisteredSearch($query, $filters['search']);
        $this->applyCommonFilters($query, $filters, true);

        return $query->get([
            'id', 'first_name', 'middle_initial', 'last_name', 'barangay', 'purok_id',
            'contact_number', 'email', 'address', 'house_number', 'date_of_birth',
            'status', 'created_at',
        ])
            ->filter(fn (User $woman) => $this->inScope($woman->barangay, $catchment))
            ->each(fn (User $woman) => $woman->type = 'registered')
            ->values();
    }

    /** Unregistered women (walk-ins without a portal account). */
    private function unregisteredRows(?array $catchment, array $filters)
    {
        $query = WalkInPatient::query()
            ->whereNull('user_id')
            ->whereNull('converted_to_user_id');
        $this->applyScope($query, $catchment, 'barangay');
        $this->applyUnregisteredSearch($query, $filters['search']);
        $this->applyCommonFilters($query, $filters, false);

        return $query->get([
            'id', 'first_name', 'middle_initial', 'last_name', 'barangay', 'purok_id',
            'contact_number', 'address', 'date_of_birth', 'created_at',
        ])
            ->filter(fn (WalkInPatient $woman) => $this->inScope($woman->barangay, $catchment))
            ->each(fn (WalkInPatient $woman) => $woman->type = 'unregistered')
            ->values();
    }

    /**
     * Constrain a query to the caller's catchment. The SQL pass is a superset
     * of the exact PHP check, so every result is verified again in PHP before
     * it can be shown.
     */
    private function applyScope($query, ?array $catchment, string $column): void
    {
        if ($catchment === null) {
            return;
        }

        $patterns = [];
        foreach ($catchment as $key) {
            foreach ($this->scope->matchPatterns($key) as $pattern) {
                $patterns[] = $pattern;
            }
        }

        if ($patterns === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($builder) use ($patterns, $column) {
            foreach ($patterns as $pattern) {
                $builder->orWhereRaw("LOWER({$column}) LIKE ?", [$pattern]);
            }
        });
    }

    private function scopedCount($query, ?array $catchment): int
    {
        $this->applyScope($query, $catchment, 'barangay');

        return $query->count();
    }

    private function applyRegisteredSearch($query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $like = '%' . $this->escapeLike($search) . '%';

        $query->where(function ($builder) use ($like) {
            $builder->whereRaw('LOWER(first_name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(middle_initial) LIKE ?', [$like])
                ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(email) LIKE ?', [$like])
                ->orWhereRaw('LOWER(contact_number) LIKE ?', [$like])
                ->orWhereRaw('LOWER(address) LIKE ?', [$like])
                ->orWhereRaw('LOWER(house_number) LIKE ?', [$like])
                ->orWhereRaw('LOWER(barangay) LIKE ?', [$like])
                ->orWhereHas('purok', fn ($purok) => $purok->whereRaw('LOWER(name) LIKE ?', [$like]));
        });
    }

    private function applyUnregisteredSearch($query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $like = '%' . $this->escapeLike($search) . '%';

        $query->where(function ($builder) use ($like) {
            $builder->whereRaw('LOWER(first_name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(middle_initial) LIKE ?', [$like])
                ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(contact_number) LIKE ?', [$like])
                ->orWhereRaw('LOWER(address) LIKE ?', [$like])
                ->orWhereRaw('LOWER(barangay) LIKE ?', [$like])
                ->orWhereHas('purok', fn ($purok) => $purok->whereRaw('LOWER(name) LIKE ?', [$like]));
        });
    }

    private function applyCommonFilters($query, array $filters, bool $registered): void
    {
        if ($filters['barangay'] !== '') {
            $patterns = $this->scope->matchPatterns($this->scope->key($filters['barangay']));

            $query->where(function ($builder) use ($patterns) {
                foreach ($patterns as $pattern) {
                    $builder->orWhereRaw('LOWER(barangay) LIKE ?', [$pattern]);
                }
            });
        }

        // Account status only exists for portal accounts.
        if ($registered && $filters['status'] !== '' && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if ($filters['dateFrom'] !== '') {
            $query->whereDate('created_at', '>=', $filters['dateFrom']);
        }

        if ($filters['dateTo'] !== '') {
            $query->whereDate('created_at', '<=', $filters['dateTo']);
        }

        if ($filters['pregnant']) {
            $query->whereHas('pregnancies', fn ($pregnancy) => $pregnancy->active());
        }
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }

    private function normaliseDate($value): string
    {
        $value = trim((string) $value);

        if ($value === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return '';
        }

        [$year, $month, $day] = array_map('intval', explode('-', $value));

        return checkdate($month, $day, $year) ? $value : '';
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
}
