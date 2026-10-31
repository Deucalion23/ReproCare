{{--
    Shared "All Women" directory body used by the RHU and CHO portals.

    Expects:
      $womenRoute   (string) base route used for every filter link
      $scopeLabel   (string) human readable scope, e.g. "RHU 1 catchment"
      $totalCount, $registeredCount, $unregisteredCount, $pregnantCount,
      $pendingCount, $resultCount (int)
      $search, $type, $status, $barangay, $sort, $dateFrom, $dateTo (string)
      $pregnant (bool), $sorts, $statuses, $barangays
      $women (LengthAwarePaginator), $detailUrl (callable)
--}}
@push('styles')
<style>
    .wd-mini .card { border:none !important; border-radius:18px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; transition:transform .2s ease, box-shadow .2s ease; }
    .wd-mini .card:hover { transform:translateY(-3px); box-shadow:0 10px 26px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent) !important; }
    .wd-mini .card.is-active { border:1px solid var(--color-primary) !important; box-shadow:0 10px 26px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent) !important; }
    .wd-mini .card a { color:inherit; text-decoration:none; }
    .wd-mini .card .text-muted { font-size:0.7rem !important; }
    .wd-wrap { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    .wd-wrap .card-header { border:none !important; padding-top:0.9rem; padding-bottom:0.9rem; }
    .wd-wrap .card-header > .d-flex { gap:0.5rem; }
    .wd-tabs .nav-link { border:none !important; border-radius:999px !important; font-weight:800 !important; font-size:0.78rem !important; padding:0.42rem 1rem !important; color:var(--color-text-muted) !important; background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; }
    .wd-tabs .nav-link.active { background:var(--color-primary) !important; background-color:var(--color-primary) !important; color:#ffffff !important; box-shadow:0 6px 16px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent); }
    .wd-search { background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; border:none !important; border-radius:999px !important; overflow:hidden; max-width:320px; }
    .wd-search .form-control { background:transparent !important; border:none !important; box-shadow:none !important; font-size:0.82rem; padding-left:0.9rem !important; }
    .wd-search .btn { border:none !important; background:transparent !important; color:var(--color-text-muted) !important; width:2.2rem; }
    .wd-filters { display:flex; flex-wrap:wrap; gap:0.5rem; align-items:flex-end; }
    .wd-filters .wd-field { display:flex; flex-direction:column; gap:0.25rem; }
    .wd-filters label { font-size:0.68rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-text-muted); }
    .wd-filters .form-control, .wd-filters .form-select { border-radius:10px !important; border-color:var(--color-input-border) !important; font-size:0.82rem; min-height:36px; }
    .wd-filters .wd-search-field { flex:1 1 240px; max-width:320px; }
    .wd-filters .wd-select-field { flex:0 1 150px; min-width:130px; }
    .wd-filters .wd-date-field { flex:0 1 150px; min-width:130px; }
    .wd-apply-btn { border:none !important; border-radius:999px !important; font-weight:800 !important; background:var(--color-primary) !important; background-color:var(--color-primary) !important; color:#ffffff !important; padding:0.45rem 1.15rem !important; font-size:0.8rem !important; }
    .wd-clear-btn { border:1px solid var(--color-border) !important; border-radius:999px !important; padding:0.45rem 0.9rem !important; font-size:0.8rem !important; font-weight:700; color:var(--color-text-muted) !important; background:transparent !important; }
    .wd-wrap table thead th { border:none !important; background:transparent !important; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-muted) !important; }
    .wd-wrap table tbody td { border:none !important; vertical-align:middle; }
    .wd-wrap table tbody tr:hover { background:var(--color-bg); }
    .wd-name { font-weight:700; color:var(--color-text); line-height:1.25; }
    .wd-sub { font-size:0.75rem; color:var(--color-text-muted); }
    .wd-pill { font-size:0.7rem; font-weight:800; padding:0.25em 0.75em; border-radius:999px; white-space:nowrap; display:inline-block; }
    .wd-pill-registered { background:var(--color-success-soft); color:var(--color-success-text); }
    .wd-pill-unregistered { background:var(--color-warning-soft, #fff7e6); color:var(--color-warning-text, #8a5a00); }
    .wd-pill-muted { background:var(--color-surface-soft); color:var(--color-text-muted); }
    .wd-pill-pregnant { background:var(--color-danger-soft); color:var(--color-danger-text); }
    .wd-count { font-size:0.78rem; font-weight:700; color:var(--color-text-muted); }
    @media (max-width: 991.98px) {
        .wd-filters .wd-search-field { max-width:none; flex:1 1 100%; }
        .wd-filters .wd-select-field, .wd-filters .wd-date-field { flex:1 1 calc(50% - 0.5rem); }
    }
    @media (max-width: 767.98px) {
        .wd-filters .wd-select-field, .wd-filters .wd-date-field { flex:1 1 100%; }
        .wd-wrap .table { font-size:0.78rem; }
        .wd-wrap .table th, .wd-wrap .table td { padding:0.5rem; }
    }
    /* Force table to fit screen without scroll */
    .wd-wrap .table-responsive { overflow:visible; }
    .wd-wrap .table { font-size:0.78rem; min-width:100%; table-layout:auto; }
    .wd-wrap .table th, .wd-wrap .table td { padding:0.35rem 0.5rem; white-space:nowrap; }
    .wd-wrap .table td .wd-sub { font-size:0.7rem; }
    .wd-wrap .table td .wd-name { font-size:0.8rem; }
    .wd-wrap .table td .wd-pill { font-size:0.65rem; padding:0.15em 0.5em; }
    .wd-wrap .table .btn { padding:0.25rem 0.6rem; font-size:0.7rem; }
</style>
@endpush

@php
    $filtersActive = $search !== '' || $barangay !== '' || $status !== 'all' || $dateFrom !== '' || $dateTo !== '' || $pregnant;
    $keep = array_filter([
        'search' => $search !== '' ? $search : null,
        'barangay' => $barangay !== '' ? $barangay : null,
        'sort' => $sort !== 'date_desc' ? $sort : null,
        'date_from' => $dateFrom !== '' ? $dateFrom : null,
        'date_to' => $dateTo !== '' ? $dateTo : null,
    ]);
    $typeLink = function (string $type) use ($keep, $status, $womenRoute) {
        $params = $keep;
        if ($type === 'all') {
            unset($params['type']);
        } else {
            $params['type'] = $type;
        }
        if ($type === 'unregistered') {
            unset($params['status']);
        } elseif ($status !== 'all') {
            $params['status'] = $status;
        }

        return route($womenRoute, $params);
    };
    $pregnantLink = $pregnant
        ? route($womenRoute, $keep)
        : route($womenRoute, $keep + ['pregnant' => 1]);
    $pendingLink = $status === 'pending'
        ? route($womenRoute, $keep)
        : route($womenRoute, $keep + ['status' => 'pending']);
@endphp

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">All Women</div>
            <p class="page-hero-subtitle mb-0" style="font-weight:600;">
                {{ $subtitle ?? 'Registered and unregistered women in your scope area · ' . $scopeLabel . '.' }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-secondary p-2 d-flex align-items-center">
                <i class="bi bi-geo-alt-fill me-1"></i> {{ $scopeLabel }}
            </span>
        </div>
    </div>
</div>

{{-- Summary cards --}}
<div class="row g-3 mb-4 wd-mini">
    <div class="col-6 col-md-4 col-lg">
        <a href="{{ $typeLink('all') }}" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $type === 'all' && ! $pregnant ? 'is-active' : '' }}">
                <i class="bi bi-people-fill text-primary mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">All Women</div>
                <h4 class="fw-800 text-primary mb-0 mt-1">{{ $totalCount }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <a href="{{ $typeLink('registered') }}" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $type === 'registered' ? 'is-active' : '' }}">
                <i class="bi bi-person-check-fill text-success mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Registered</div>
                <h4 class="fw-800 text-success mb-0 mt-1">{{ $registeredCount }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <a href="{{ $typeLink('unregistered') }}" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $type === 'unregistered' ? 'is-active' : '' }}">
                <i class="bi bi-person-plus-fill text-warning mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Unregistered</div>
                <h4 class="fw-800 text-warning mb-0 mt-1">{{ $unregisteredCount }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <a href="{{ $pregnantLink }}" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $pregnant ? 'is-active' : '' }}">
                <i class="bi bi-heart-pulse-fill text-danger mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">With Pregnancy</div>
                <h4 class="fw-800 text-danger mb-0 mt-1">{{ $pregnantCount }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <a href="{{ $pendingLink }}" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $status === 'pending' ? 'is-active' : '' }}">
                <i class="bi bi-hourglass-split text-info mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Pending Review</div>
                <h4 class="fw-800 text-info mb-0 mt-1">{{ $pendingCount }}</h4>
            </div>
        </a>
    </div>
</div>

<div class="card fade-in-card wd-wrap">
    <div class="card-header bg-transparent p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <ul class="nav nav-pills wd-tabs gap-2 flex-wrap" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $type === 'all' ? 'active' : '' }}" href="{{ $typeLink('all') }}">
                        <i class="bi bi-people me-1"></i> All ({{ $totalCount }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $type === 'registered' ? 'active' : '' }}" href="{{ $typeLink('registered') }}">
                        <i class="bi bi-person-check me-1"></i> Registered ({{ $registeredCount }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $type === 'unregistered' ? 'active' : '' }}" href="{{ $typeLink('unregistered') }}">
                        <i class="bi bi-person-plus me-1"></i> Unregistered ({{ $unregisteredCount }})
                    </a>
                </li>
            </ul>

            <form method="GET" action="{{ route($womenRoute) }}" class="wd-filters">
            <div class="wd-field wd-search-field">
                <label for="wd-search">Search</label>
                <div class="input-group input-group-sm wd-search">
                    <input type="search" id="wd-search" name="search" class="form-control" value="{{ $search }}"
                           placeholder="Name, contact, email, purok...">
                    <button class="btn" type="submit" title="Search">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>

            <div class="wd-field wd-select-field">
                <label for="wd-barangay">Barangay</label>
                <select id="wd-barangay" name="barangay" class="form-select form-select-sm">
                    <option value="">All barangays</option>
                    @foreach ($barangays as $area)
                        <option value="{{ $area }}" {{ $barangay === $area ? 'selected' : '' }}>{{ $area }}</option>
                    @endforeach
                </select>
            </div>

            <div class="wd-field wd-select-field">
                <label for="wd-status">Account status</label>
                <select id="wd-status" name="status" class="form-select form-select-sm" {{ $type === 'unregistered' ? 'disabled' : '' }}>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All statuses</option>
                    @foreach ($statuses as $option)
                        <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="wd-field wd-select-field">
                <label for="wd-sort">Sort by</label>
                <select id="wd-sort" name="sort" class="form-select form-select-sm">
                    @foreach ($sorts as $key => $label)
                        <option value="{{ $key }}" {{ $sort === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="wd-field wd-date-field">
                <label for="wd-date-from">Added from</label>
                <input type="date" id="wd-date-from" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}" max="{{ date('Y-m-d') }}">
            </div>

            <div class="wd-field wd-date-field">
                <label for="wd-date-to">Added to</label>
                <input type="date" id="wd-date-to" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}" max="{{ date('Y-m-d') }}">
            </div>

            @if ($type !== 'all')
                <input type="hidden" name="type" value="{{ $type }}">
            @endif
            @if ($pregnant)
                <input type="hidden" name="pregnant" value="1">
            @endif

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-sm wd-apply-btn">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route($womenRoute) }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                    <i class="bi bi-x-lg me-1"></i> Clear
                </a>
            </div>
        </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 border-bottom">
            <span class="wd-count">
                Showing <strong>{{ $resultCount }}</strong>
                {{ Str::plural('woman', $resultCount) }}
                @if ($filtersActive)
                    <span class="text-muted">matching the current filters</span>
                @else
                    <span class="text-muted">in {{ $scopeLabel }}</span>
                @endif
            </span>
            @if ($filtersActive)
                <a href="{{ route($womenRoute) }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset filters
                </a>
            @endif
        </div>

        <div class="table-responsive" style="overflow:visible;">
            <table class="table table-hover align-middle mb-0" style="font-size:0.78rem;">
                <thead>
                    <tr>
                        <th class="px-3 py-3">Woman</th>
                        <th class="py-3">Type / Status</th>
                        <th class="py-3">Age / DOB</th>
                        <th class="py-3">Contact</th>
                        <th class="py-3">Barangay / Purok</th>
                        <th class="py-3">Pregnancy</th>
                        <th class="py-3">Added</th>
                        <th class="py-3 text-end px-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($women as $woman)
                        @php
                            $isWalkIn = $woman->type === 'unregistered';
                            $displayName = $isWalkIn ? $woman->full_name : $woman->name;
                            $pregnancy = $woman->pregnancies->first();
                        @endphp
                        <tr>
                            <td class="px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <x-patient-avatar :patient="$woman" :name="$displayName" :size="34" />
                                    <div style="min-width:0;">
                                        <div class="wd-name">{{ $displayName ?: 'Unknown' }}</div>
                                        <div class="wd-sub">{{ $isWalkIn ? 'Field-only profile' : ($woman->email ?: 'No email') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($isWalkIn)
                                    <span class="wd-pill wd-pill-unregistered"><i class="bi bi-person-plus"></i> Unregistered</span>
                                @else
                                    <span class="wd-pill wd-pill-registered"><i class="bi bi-person-check"></i> Registered</span>
                                    <div class="wd-sub mt-1">{{ ucfirst($woman->status ?? 'approved') }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($woman->date_of_birth)
                                    <div>{{ $woman->age }} y/o</div>
                                    <div class="wd-sub">{{ $woman->date_of_birth->format('M d, Y') }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">{{ $woman->contact_number ?: '—' }}</td>
                            <td>
                                <div>{{ $woman->barangay ?: '—' }}</div>
                                <div class="wd-sub">{{ $woman->purok?->name ?: 'No purok' }}</div>
                            </td>
                            <td>
                                @if ($pregnancy)
                                    <span class="wd-pill wd-pill-pregnant">Pregnant</span>
                                    <div class="wd-sub mt-1">EDD {{ $pregnancy->edd ? $pregnancy->edd->format('M d, Y') : 'unknown' }}</div>
                                @else
                                    <span class="wd-pill wd-pill-muted">Not pregnant</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">
                                {{ $woman->created_at ? $woman->created_at->format('M d, Y') : '—' }}
                            </td>
                            <td class="text-end px-3">
                                <a href="{{ $detailUrl($woman) }}" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;white-space:nowrap;">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-people" style="font-size:2rem;"></i>
                                <p class="mt-2 mb-0">
                                    {{ $filtersActive ? 'No women match the current filters.' : 'No women found in your scope area.' }}
                                </p>
                                @if ($filtersActive)
                                    <a href="{{ route($womenRoute) }}" class="btn btn-sm btn-outline-secondary wd-clear-btn mt-2">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset filters
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($women->hasPages())
            <div class="p-3">{{ $women->links('vendor.pagination.numbers-only') }}</div>
        @endif
    </div>
</div>
