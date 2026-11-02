@extends('bhw.layout')

@section('title', 'Women - BHW Portal | ReproCare')

@push('styles')
@include('includes.women-directory-styles')
<style>
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
    <div class="card-header bg-transparent p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
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

            <form method="GET" action="{{ route('bhw.patients') }}" class="wd-filters">
                <input type="hidden" name="filter" value="{{ $scope }}">

                <div class="wd-field wd-search-field">
                    <label for="bhwWomenSearch">Search</label>
                    <div class="wd-search">
                        <input type="search" id="bhwWomenSearch" name="search" class="form-control"
                               value="{{ request('search') }}" placeholder="Name, contact, address...">
                        <button class="btn" type="submit" title="Search"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <div class="wd-actions d-flex gap-2">
                    <button type="submit" class="btn btn-sm wd-apply-btn">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if (request('search'))
                        <a href="{{ $clearUrl }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                            <i class="bi bi-x-lg me-1"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 border-bottom">
            <span class="wd-count">
                Showing <strong>{{ $patients->count() }}</strong> of <strong>{{ $patients->total() }}</strong>
                {{ Str::plural('woman', $patients->total()) }}
                <span class="text-muted">· your barangay</span>
                @if (request('search'))
                    <span class="text-muted">· results for "{{ request('search') }}"</span>
                @endif
            </span>
            @if (request('search'))
                <a href="{{ $clearUrl }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset filters
                </a>
            @endif
        </div>

        @if($patients->count() > 0)
            <div class="table-responsive" style="overflow:visible;">
                <table class="table table-hover align-middle table-sticky-head table-cards-mobile mb-0">
                    <thead>
                        <tr>
                            <th class="px-3 py-3">Woman</th>
                            <th class="py-3">Contact Number</th>
                            <th class="py-3">Barangay</th>
                            <th class="py-3">Account Type</th>
                            <th class="py-3">Pregnancy</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patients as $patient)
                            @php
                                $isWalkIn = $patient->type === 'unregistered';
                                $contact = $isWalkIn ? ($patient->contact_number ?? null) : ($patient->contact_number ?? $patient->phone ?? null);
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
                                <td data-label="Account Type">
                                    @if($isWalkIn)
                                        <span class="wd-pill wd-pill-unregistered" title="Managed Beneficiary · Field Record Only · no portal login">
                                            <i class="bi bi-person-plus"></i> Unlinked · BHW-Managed
                                        </span>
                                    @else
                                        <span class="wd-pill wd-pill-registered" title="Authenticated Patient · Direct Access · portal login active">
                                            <i class="bi bi-person-check"></i> Enrolled · Portal-Active
                                        </span>
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
@endsection
