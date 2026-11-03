@extends('bhw.layout')

@section('title', 'Women - BHW Portal | ReproCare')

@push('styles')
@include('includes.women-directory-styles')
<style>
    /* Keep the BHW directory focused on the list: a slimmer title block,
       compact metrics, and one aligned toolbar above the table. */
    .bhw-women-page .page-hero {
        padding:1rem 1.35rem !important;
        margin-bottom:0.85rem !important;
        border-radius:16px !important;
    }
    .bhw-women-page .page-hero-title { font-size:1.35rem !important; margin-bottom:0.15rem !important; }
    .bhw-women-page .page-hero-subtitle { font-size:0.82rem !important; }
    .bhw-women-page .page-hero .btn { padding:0.55rem 0.9rem; font-size:0.78rem; }
    .bhw-women-page .wd-mini { --bs-gutter-x:0.65rem; --bs-gutter-y:0.65rem; margin-bottom:0.85rem !important; }
    .bhw-women-page .wd-mini .card { min-height:102px; padding:0.7rem 0.55rem !important; border-radius:14px !important; }
    .bhw-women-page .wd-mini .card > i { font-size:1.15rem !important; margin-bottom:0.15rem !important; }
    .bhw-women-page .wd-mini h4 { font-size:1.3rem; margin-top:0.15rem !important; }
    .bhw-women-page .wd-wrap { border:1px solid var(--color-border) !important; border-radius:16px !important; }
    .bhw-women-page .wd-wrap .card-header { padding:0.7rem 0.85rem !important; }
    .bhw-women-toolbar { display:flex; align-items:flex-end; flex-wrap:wrap; gap:0.75rem; width:100%; }
    .bhw-women-toolbar .wd-tabs { flex:1 1 100%; gap:0.4rem; }
    .bhw-women-toolbar .wd-tab { padding:0.4rem 0.85rem !important; }
    .bhw-women-filters { flex:1 1 640px; flex-wrap:wrap; gap:0.55rem; align-items:flex-end; }
    .bhw-women-filters .wd-search-field { flex:2 1 190px; min-width:170px; max-width:460px; }
    .bhw-women-filters .wd-select-field { flex:1 1 138px; min-width:126px; max-width:240px; }
    .bhw-women-filters .wd-actions { margin-left:0; }
    .bhw-women-filters .wd-select-field .form-select { min-height:36px; }
    .bhw-women-filters .wd-apply-btn, .bhw-women-filters .wd-clear-btn { min-height:36px; white-space:nowrap; }
    .bhw-women-page .wd-pill-pending { background:var(--color-warning-soft, #fff7e6); color:var(--color-warning-text, #8a5a00); }
    .bhw-women-page .wd-pill-inactive { background:var(--color-danger-soft); color:var(--color-danger-text); }
    .bhw-women-page .wd-count-row { padding:0.35rem 0.9rem !important; }
    .bhw-women-page .wd-wrap table thead th { padding-top:0.4rem !important; padding-bottom:0.4rem !important; }

    @media (max-width: 1199.98px) {
        .bhw-women-toolbar .wd-tabs { flex:1 1 100%; }
        .bhw-women-filters { flex:1 1 100%; }
    }

    /* Field workers tap these rows on phones, so the row actions stay 30px squares
       even though the shared directory shell is denser. */
    .wd-wrap .table .btn-action {
        min-width:30px; min-height:30px; padding:0.2rem 0.4rem !important;
        font-size:0.8rem !important; border-radius:8px;
        display:inline-flex; align-items:center; justify-content:center;
        text-decoration:none; border:1px solid var(--color-border);
        background:var(--color-surface-soft); color:var(--color-text-muted);
        cursor:pointer; flex-shrink:0; transition:all 0.15s ease;
    }
    .wd-wrap .table .btn-action:hover { transform:scale(1.12); }
    .wd-wrap .table .btn-action-view { color:var(--color-primary-text); }
    .wd-wrap .table .btn-action-view:hover { background:var(--color-primary-soft); border-color:var(--color-primary); color:var(--color-primary-text); }
    .wd-wrap .table .btn-action-add { color:var(--color-success-text); }
    .wd-wrap .table .btn-action-add:hover { background:var(--color-success-soft); border-color:var(--color-success); color:var(--color-success-text); }

    @media (max-width: 600px) {
        .bhw-women-page .page-hero { padding:0.85rem 0.9rem !important; margin-bottom:0.65rem !important; }
        .bhw-women-page .wd-mini { margin-bottom:0.65rem !important; }
        .bhw-women-page .wd-mini .card { min-height:88px; }
        .bhw-women-toolbar { align-items:stretch; gap:0.55rem; }
        .bhw-women-toolbar .wd-tabs { flex:1 1 100%; }
        .bhw-women-filters { flex:1 1 100%; gap:0.45rem; }
        .bhw-women-filters .wd-search-field { flex:1 1 100%; max-width:none; }
        .bhw-women-filters .wd-select-field { flex:1 1 100%; max-width:none; }
        .bhw-women-filters .wd-actions { flex:0 0 auto; }
        .page-hero .d-flex.justify-content-between { flex-direction:column; align-items:stretch !important; }
        .page-hero .d-flex.gap-2 { display:grid !important; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:0.4rem; width:100%; align-items:stretch; }
        .page-hero .d-flex.gap-2 .btn {
            min-width:0; max-width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
            display:flex !important; align-items:center !important; justify-content:center !important;
            text-align:center; line-height:1.2 !important;
            font-size:clamp(0.42rem, 2vw, 0.5rem);
            padding:0.5rem 0.15rem;
        }
        .page-hero .d-flex.gap-2 .btn i { flex-shrink:0; font-size:0.85em; line-height:1 !important; }
        .page-hero .d-flex.gap-2 .btn .me-1 { margin-right:0.15rem !important; }
        .wd-mini .card { padding:0.7rem 0.5rem !important; }
        .wd-mini h4 { font-size:1.15rem !important; }
        .wd-tabs { display:grid !important; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:0.45rem; width:100%; }
        .wd-tab { font-size:0.66rem !important; padding:0.5rem 0.55rem !important; justify-content:center; white-space:nowrap; }
        .wd-name { font-size:0.8rem !important; }
        .wd-sub { font-size:0.68rem !important; }
    }
</style>
@endpush

@section('bhw-content')
<div class="bhw-women-page">
<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">All Women</div>
            <p class="page-hero-subtitle">Browse enrolled women in your purok.</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="{{ route('bhw.patients.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus-fill me-1"></i> Add Woman
            </a>
            <a href="{{ route('bhw.walk-in-patients.trash') }}" class="btn btn-outline-secondary position-relative" title="Archived walk-in profiles">
                <i class="bi bi-archive me-1"></i> Archive
                @if(($trashCount ?? 0) > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $trashCount }}</span>
                @endif
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 fade-in-card">
        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
        {{ session('success') }}
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $scope = request('filter', 'all');
    $scopeKeep = request()->except(['page', 'filter']);
    $scopeUrl = fn (string $value) => route('bhw.patients', array_merge($scopeKeep, ['filter' => $value]));
    $clearUrl = route('bhw.patients', ['filter' => $scope]);
    $filtersActive = request()->filled('search') || collect([$status, $riskLevel, $ageRange, $trimester, $pregnancyStatus])->contains(fn ($value) => $value !== 'all');
@endphp

{{-- Summary cards --}}
<div class="row g-3 mb-4 wd-mini">
    <div class="col-6 col-md-4 col-lg">
        <a href="{{ $scopeUrl('all') }}" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $scope === 'all' ? 'is-active' : '' }}">
                <i class="bi bi-people-fill text-primary mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">All Women</div>
                <h4 class="fw-800 text-primary mb-0 mt-1">{{ $patients->total() }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <a href="{{ $scopeUrl('registered') }}" class="text-decoration-none" title="Authenticated Patients with portal login (Direct Access)">
            <div class="card fade-in-card p-3 text-center h-100 {{ $scope === 'registered' ? 'is-active' : '' }}">
                <i class="bi bi-person-heart text-success mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Enrolled</div>
                <h4 class="fw-800 text-success mb-0 mt-1">{{ $registeredCount }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <a href="{{ $scopeUrl('unregistered') }}" class="text-decoration-none" title="Managed Beneficiaries without login (Field Record Only)">
            <div class="card fade-in-card p-3 text-center h-100 {{ $scope === 'unregistered' ? 'is-active' : '' }}">
                <i class="bi bi-person-plus-fill text-warning mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Unlinked</div>
                <h4 class="fw-800 text-warning mb-0 mt-1">{{ $unregisteredCount }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="card fade-in-card p-3 text-center h-100 is-static">
            <i class="bi bi-heart-pulse-fill text-info mb-1" style="font-size:1.4rem;"></i>
            <div class="text-muted" style="font-size:0.75rem;">Scheduled Checkups</div>
            <h4 class="fw-800 text-info mb-0 mt-1">{{ $scheduledCheckups }}</h4>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="card fade-in-card p-3 text-center h-100 is-static">
            <i class="bi bi-exclamation-circle-fill text-danger mb-1" style="font-size:1.4rem;"></i>
            <div class="text-muted" style="font-size:0.75rem;">Missed</div>
            <h4 class="fw-800 text-danger mb-0 mt-1">{{ $missedCheckups }}</h4>
        </div>
    </div>
</div>

<div class="card fade-in-card wd-wrap">
    <div class="card-header bg-transparent">
        <div class="bhw-women-toolbar">
            <ul class="wd-tabs" role="tablist">
                <li>
                    <a class="wd-tab {{ $scope === 'all' ? 'active' : '' }}" href="{{ $scopeUrl('all') }}">
                        <i class="bi bi-people-fill"></i> All Women ({{ $patients->total() }})
                    </a>
                </li>
                <li>
                    <a class="wd-tab {{ $scope === 'registered' ? 'active' : '' }}" href="{{ $scopeUrl('registered') }}">
                        <i class="bi bi-person-heart"></i> Enrolled ({{ $registeredCount }})
                    </a>
                </li>
                <li>
                    <a class="wd-tab {{ $scope === 'unregistered' ? 'active' : '' }}" href="{{ $scopeUrl('unregistered') }}">
                        <i class="bi bi-person-plus"></i> Unlinked ({{ $unregisteredCount }})
                    </a>
                </li>
            </ul>

            <form method="GET" action="{{ route('bhw.patients') }}" class="wd-filters bhw-women-filters">
                <input type="hidden" name="filter" value="{{ $scope }}">

                <div class="wd-field wd-search-field">
                    <label for="bhwWomenSearch">Search</label>
                    <div class="wd-search">
                        <input type="search" id="bhwWomenSearch" name="search" class="form-control"
                               value="{{ request('search') }}" placeholder="Name, contact, address...">
                        <button class="btn" type="submit" title="Search"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <div class="wd-field wd-select-field wd-status-field">
                    <label for="bhwWomenStatus">Status</label>
                    <select id="bhwWomenStatus" name="status" class="form-select">
                        <option value="all" @selected($status === 'all')>All statuses</option>
                        <option value="approved" @selected($status === 'approved')>Approved</option>
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                        <option value="unlinked" @selected($status === 'unlinked')>Unlinked</option>
                    </select>
                </div>

                <div class="wd-field wd-select-field">
                    <label for="bhwWomenRisk">Risk Tier</label>
                    <select id="bhwWomenRisk" name="risk_level" class="form-select">
                        <option value="all" @selected($riskLevel === 'all')>All risk tiers</option>
                        <option value="high_risk_only" @selected($riskLevel === 'high_risk_only')>High risk only</option>
                        <option value="critical" @selected($riskLevel === 'critical')>Critical</option>
                        <option value="high" @selected($riskLevel === 'high')>High</option>
                        <option value="medium" @selected($riskLevel === 'medium')>Medium</option>
                        <option value="low" @selected($riskLevel === 'low')>Low</option>
                    </select>
                </div>

                <div class="wd-field wd-select-field">
                    <label for="bhwWomenAge">Age Tier</label>
                    <select id="bhwWomenAge" name="age_range" class="form-select">
                        <option value="all" @selected($ageRange === 'all')>All ages</option>
                        <option value="teen" @selected($ageRange === 'teen')>Adolescent (&lt;19)</option>
                        <option value="20_34" @selected($ageRange === '20_34')>20 to 34 yrs</option>
                        <option value="35_plus" @selected($ageRange === '35_plus')>35+ yrs</option>
                    </select>
                </div>

                <div class="wd-field wd-select-field">
                    <label for="bhwWomenTrimester">Trimester</label>
                    <select id="bhwWomenTrimester" name="trimester" class="form-select">
                        <option value="all" @selected($trimester === 'all')>All trimesters</option>
                        <option value="1" @selected($trimester === '1')>1st (Weeks 1–13)</option>
                        <option value="2" @selected($trimester === '2')>2nd (Weeks 14–26)</option>
                        <option value="3" @selected($trimester === '3')>3rd (Weeks 27+)</option>
                    </select>
                </div>

                <div class="wd-field wd-select-field">
                    <label for="bhwWomenPregnancy">Pregnancy</label>
                    <select id="bhwWomenPregnancy" name="pregnancy_status" class="form-select">
                        <option value="all" @selected($pregnancyStatus === 'all')>All status</option>
                        <option value="pregnant" @selected($pregnancyStatus === 'pregnant')>Pregnant</option>
                        <option value="not_pregnant" @selected($pregnancyStatus === 'not_pregnant')>Not pregnant</option>
                    </select>
                </div>

                <div class="wd-actions d-flex gap-2">
                    <button type="submit" class="btn btn-sm wd-apply-btn">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if ($filtersActive)
                        <a href="{{ $clearUrl }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                            <i class="bi bi-x-lg me-1"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 border-bottom wd-count-row">
            <span class="wd-count">
                Showing <strong>{{ $patients->count() }}</strong> of <strong>{{ $patients->total() }}</strong>
                {{ Str::plural('woman', $patients->total()) }}
                <span class="text-muted">· your barangay</span>
                @if ($status !== 'all')
                    <span class="text-muted">· {{ ucfirst($status) }}</span>
                @endif
                @if (request('search'))
                    <span class="text-muted">· results for "{{ request('search') }}"</span>
                @endif
            </span>
        </div>

        @if($patients->count() > 0)
            <div class="table-responsive" style="overflow:visible;">
                <table class="table table-hover align-middle table-sticky-head table-cards-mobile mb-0">
                    <thead>
                        <tr>
                            <th class="px-3 py-3">Woman</th>
                            <th class="py-3">Contact Number</th>
                            <th class="py-3">Barangay</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Pregnancy</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patients as $patient)
                            @php
                                $isWalkIn = $patient->type === 'unregistered';
                                $contact = $isWalkIn ? ($patient->contact_number ?? null) : ($patient->contact_number ?? $patient->phone ?? null);
                                $accountStatus = $isWalkIn ? 'unlinked' : ($patient->status ?? 'approved');
                            @endphp
                            <tr>
                                <td class="no-card-label px-3" data-label="Woman">
                                    <div class="d-flex align-items-center gap-2">
                                        <x-patient-avatar :patient="$patient" :size="34" />
                                        <div style="min-width:0;">
                                            <div class="wd-name">{{ $isWalkIn ? $patient->full_name : $patient->name }}</div>
                                            @if($patient->pregnancies && $patient->pregnancies->count() > 0)
                                                <span class="wd-pill wd-pill-pregnant"><i class="bi bi-heart-fill"></i> Pregnant</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="no-card-label" data-label="Contact Number" style="white-space:nowrap;">{{ $contact ?: '—' }}</td>
                                <td data-label="Barangay">
                                    @if($patient->barangay)
                                        <div><i class="bi bi-house-fill me-1"></i>{{ $patient->barangay }}</div>
                                    @else
                                        <span class="wd-sub">—</span>
                                    @endif
                                </td>
                                <td data-label="Status">
                                    @if($isWalkIn)
                                        <span class="wd-pill wd-pill-unregistered" title="BHW-managed profile without a portal account">
                                            <i class="bi bi-person-plus"></i> Unlinked
                                        </span>
                                    @elseif($accountStatus === 'pending')
                                        <span class="wd-pill wd-pill-pending"><i class="bi bi-hourglass-split"></i> Pending review</span>
                                    @elseif($accountStatus === 'inactive')
                                        <span class="wd-pill wd-pill-inactive"><i class="bi bi-pause-circle"></i> Inactive</span>
                                    @elseif($accountStatus === 'approved')
                                        <span class="wd-pill wd-pill-registered" title="Portal account approved">
                                            <i class="bi bi-person-check"></i> Approved
                                        </span>
                                    @else
                                        <span class="wd-pill wd-pill-muted">{{ Str::headline($accountStatus) }}</span>
                                    @endif
                                </td>
                                <td data-label="Pregnancy">
                                    @if($isWalkIn)
                                        <span class="wd-sub">Not Pregnant</span>
                                    @elseif($patient->pregnancies && $patient->pregnancies->count() > 0)
                                        @foreach($patient->pregnancies as $pregnancy)
                                            <div class="wd-sub" style="color:var(--color-success-text);">
                                                <i class="bi bi-calendar-heart"></i> EDD: {{ $pregnancy->edd ? $pregnancy->edd->format('M j, Y') : 'Unknown' }}
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="wd-sub">Not Pregnant</span>
                                    @endif
                                </td>
                                <td class="no-card-label text-center" data-label="Actions">
                                    <div class="d-inline-flex gap-1 justify-content-center">
                                        <a href="{{ $isWalkIn ? route('bhw.walk-in-patients.show', [$patient->id, 'from' => 'women']) : route('bhw.patient-details', $patient->id) }}"
                                           class="btn-action btn-action-view" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($isWalkIn)
                                            <a href="{{ route('bhw.walk-in-patients.edit', [$patient->id, 'from' => 'women']) }}"
                                               class="btn-action btn-action-add" title="Edit Patient">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('bhw.walk-in-patients.convert', $patient->id) }}"
                                               class="btn-action btn-action-add" title="Activate portal account (Unlinked → Enrolled)">
                                                <i class="bi bi-person-check"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('bhw.checkups.create', $patient->id) }}"
                                               class="btn-action btn-action-add" title="Schedule Checkup">
                                                <i class="bi bi-calendar-plus"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3">{{ $patients->links('vendor.pagination.arrows-only') }}</div>
        @else
            <div class="empty-state p-5 text-center">
                <i class="bi bi-people empty-state-icon" style="font-size:2.5rem;"></i>
                <h6 class="mt-2 fw-bold">No Women Found</h6>
                <p class="text-muted">
                    @if(request('search'))
                        No women match "{{ request('search') }}".
                        <a href="{{ $clearUrl }}">Clear search</a>
                    @else
                        No {{ $scope === 'registered' ? 'enrolled (Portal-Active)' : ($scope === 'unregistered' ? 'unlinked (BHW-Managed)' : '') }} women found.
                    @endif
                    @if($scope !== 'all')
                        <a href="{{ $scopeUrl('all') }}">View all women</a>
                    @endif
                </p>
                <a href="{{ route('bhw.patients.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill me-1"></i> Add First Woman
                </a>
            </div>
        @endif
    </div>
</div>
</div>
@endsection
