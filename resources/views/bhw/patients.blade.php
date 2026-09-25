@extends('bhw.layout')

@section('title', 'Women - BHW Portal | ReproCare')

@push('styles')
<style>
    .filter-pills { display:flex; gap:0.75rem; flex-wrap:wrap; }
    .filter-pill {
        display:inline-flex; align-items:center; gap:0.45rem;
        padding:0.7rem 1rem; border-radius:999px; text-decoration:none;
        border:1px solid var(--border); color:var(--text-muted); background:var(--bg-card2);
        font-weight:700; transition:all 0.18s ease;
    }
    .filter-pill:hover { border-color:var(--primary); color:var(--primary-light); background:var(--primary-subtle); }
    .filter-pill.active { border-color:var(--primary); color:var(--primary-light); background:var(--primary-subtle); box-shadow:0 0 0 3px var(--focus-ring); }
    .patient-avatar {
        width:36px; height:36px; border-radius:50%;
        background:linear-gradient(135deg, var(--primary), var(--accent-violet));
        display:flex; align-items:center; justify-content:center;
        font-size:0.82rem; font-weight:800; color:var(--color-on-solid);
        flex-shrink:0; box-shadow:0 2px 6px var(--primary-glow);
    }
    .patient-name-cell { display:flex; align-items:center; gap:0.75rem; }
    .patient-name-cell .name { font-weight:700; color:var(--text); font-size:0.9rem; line-height:1.3; }
    .patient-name-cell .sub  { font-size:0.75rem; color:var(--text-muted); }
    .search-bar {
        display:flex; gap:0.5rem; align-items:center;
        background:var(--bg-card2); border:1px solid var(--border);
        border-radius:12px; padding:0.4rem 0.6rem; flex:1; max-width:320px;
        transition:border-color 0.2s ease;
    }
    .search-bar:focus-within { border-color:var(--primary); box-shadow:0 0 0 3px var(--focus-ring); }
    .search-bar input {
        border:none; background:transparent; color:var(--text);
        font-size:0.875rem; outline:none; flex:1; min-width:0;
    }
    .search-bar input::placeholder { color:var(--placeholder); }
    .search-bar button {
        border:none; background:transparent; color:var(--text-muted);
        cursor:pointer; padding:0.2rem 0.35rem; border-radius:8px;
        transition:background 0.15s ease, color 0.15s ease;
    }
    .search-bar button:hover { background:var(--primary-subtle); color:var(--primary-light); }
    .btn-action {
        width:32px; height:32px; border-radius:8px;
        display:inline-flex; align-items:center; justify-content:center;
        font-size:0.8rem; text-decoration:none; border:1px solid var(--border);
        background:var(--bg-card2); color:var(--text-muted);
        transition:all 0.15s ease; cursor:pointer; flex-shrink:0;
    }
    .btn-action:hover { transform:scale(1.12); }
    .btn-action-view { color:var(--primary-light); border-color:var(--border-glass); }
    .btn-action-view:hover { background:var(--primary-subtle); border-color:var(--primary); color:var(--primary-light); }
    .btn-action-add { color:var(--success); }
    .btn-action-add:hover { background:color-mix(in srgb, var(--color-success) 12%, transparent); border-color:var(--success); color:var(--success); }
</style>
@endpush

@section('bhw-content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 fade-in-card">
    <div>
        <h1 class="page-title">All Women
        </h1>
        <p class="page-subtitle">
            Enrolled Accounts (Portal-Active · Authenticated Patients) and Unlinked Profiles (BHW-Managed · Field Records) under your care
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <form method="GET" action="{{ route('bhw.patients') }}" class="d-flex">
            <input type="hidden" name="filter" value="{{ request('filter', 'all') }}">
            <div class="search-bar">
                <input type="search" name="search" placeholder="Search women..." value="{{ request('search') }}">
                <button type="submit" title="Search"><i class="bi bi-search"></i></button>
            </div>
        </form>
        <a href="{{ route('bhw.patients.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i> Add Woman
        </a>
        <a href="{{ route('bhw.walk-in-patients.create') }}" class="btn btn-outline-primary">
            <i class="bi bi-person-add me-1"></i> Record Walk-in
        </a>
        <a href="{{ route('bhw.walk-in-patients.trash') }}" class="btn btn-outline-secondary position-relative" title="Deleted walk-in profiles">
            <i class="bi bi-trash3 me-1"></i> Trash
            @if(($trashCount ?? 0) > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $trashCount }}</span>
            @endif
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 fade-in-card">
        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
        {{ session('success') }}
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex flex-wrap gap-2 mb-4 fade-in-card">
    <div class="filter-pills">
        <a href="{{ route('bhw.patients', ['filter' => 'all']) }}" class="filter-pill {{ request('filter', 'all') === 'all' ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            All: {{ $patients->total() }}
        </a>
        <a href="{{ route('bhw.patients', ['filter' => 'registered']) }}" class="filter-pill {{ request('filter') === 'registered' ? 'active' : '' }}" title="Authenticated Patients with portal login (Direct Access)">
            <i class="bi bi-person-heart"></i>
            Enrolled: {{ $registeredCount }}
        </a>
        <a href="{{ route('bhw.patients', ['filter' => 'unregistered']) }}" class="filter-pill {{ request('filter') === 'unregistered' ? 'active' : '' }}" title="Managed Beneficiaries without login (Field Record Only)">
            <i class="bi bi-person-plus"></i>
            Unlinked: {{ $unregisteredCount }}
        </a>
    </div>
    <span class="summary-chip chip-success">
        <i class="bi bi-heart-pulse"></i>
        Scheduled Checkups: {{ $scheduledCheckups }}
    </span>
    <span class="summary-chip chip-danger">
        <i class="bi bi-exclamation-circle"></i>
        Missed: {{ $missedCheckups }}
    </span>
    @if(request('search'))
        <span class="summary-chip chip-warning">
            <i class="bi bi-search"></i>
            Results for "{{ request('search') }}"
            <a href="{{ route('bhw.patients', ['filter' => request('filter', 'all')]) }}" style="color:inherit;margin-left:0.25rem;"><i class="bi bi-x-circle"></i></a>
        </span>
    @endif
</div>

<div class="table-card fade-in-card">
    @if($patients->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover table-sticky-head mb-0">
                <thead>
                    <tr>
                        <th>Woman</th>
                        <th>Contact Number</th>
                        <th>Barangay</th>
                        <th>Account Type</th>
                        <th>Pregnancy</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patients as $patient)
                    <tr>
                        <td>
                            <div class="patient-name-cell">
                                <x-patient-avatar :patient="$patient" :size="36" />
                                <div>
                                    <div class="name">{{ $patient->type === 'unregistered' ? $patient->full_name : $patient->name }}</div>
                                    @if($patient->pregnancies && $patient->pregnancies->count() > 0)
                                        <span class="status-active" style="font-size:0.65rem;padding:0.1em 0.55em;">
                                            <i class="bi bi-heart-fill"></i> Pregnant
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.875rem;">
                            @php
                                $contact = $patient->type === 'unregistered' 
                                    ? ($patient->contact_number ?? null) 
                                    : ($patient->contact_number ?? $patient->phone ?? null);
                            @endphp
                            {{ $contact ?: '—' }}
                        </td>
                        <td>
                            @if($patient->barangay)
                                <div style="font-size:0.875rem;color:var(--text);">
                                    <i class="bi bi-house-fill me-1"></i>{{ $patient->barangay }}
                                </div>
                            @else
                                <span style="font-size:0.875rem;color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($patient->type === 'unregistered')
                                <span class="badge bg-warning" title="Managed Beneficiary · Field Record Only · no portal login">Unlinked · BHW-Managed</span>
                            @else
                                <span class="badge bg-success" title="Authenticated Patient · Direct Access · portal login active">Enrolled · Portal-Active</span>
                            @endif
                        </td>
                        <td>
                            @if($patient->type === 'unregistered')
                                <span style="font-size:0.8rem;color:var(--text-muted);">
                                    Not Pregnant
                                </span>
                            @elseif($patient->pregnancies && $patient->pregnancies->count() > 0)
                                @foreach($patient->pregnancies as $pregnancy)
                                    <div style="font-size:0.8rem;color:var(--success);display:flex;align-items:center;gap:0.3rem;">
                                        <i class="bi bi-calendar-heart"></i>
                                        EDD: {{ $pregnancy->edd ? $pregnancy->edd->format('M j, Y') : 'Unknown' }}
                                    </div>
                                    @if(!$loop->last)<div style="margin-top:2px;"></div>@endif
                                @endforeach
                            @else
                                <span style="font-size:0.8rem;color:var(--text-muted);">Not Pregnant</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ $patient->type === 'unregistered' ? route('bhw.walk-in-patients.show', [$patient->id, 'from' => 'women']) : route('bhw.patient-details', $patient->id) }}"
                                   class="btn-action btn-action-view" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($patient->type === 'unregistered')
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

        <div class="d-flex justify-content-center" style="padding:1.25rem;">
            {{ $patients->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-people empty-state-icon"></i>
            <h6>No Women Found</h6>
            <p>
                @if(request('search'))
                    No women match "{{ request('search') }}".
                    <a href="{{ route('bhw.patients', ['filter' => request('filter', 'all')]) }}">Clear search</a>
                @else
                    No {{ request('filter') === 'registered' ? 'enrolled (Portal-Active)' : (request('filter') === 'unregistered' ? 'unlinked (BHW-Managed)' : '') }} women found.
                    @if(request('filter') !== 'all')
                        <a href="{{ route('bhw.patients', ['filter' => 'all']) }}">View all women</a>
                    @endif
                @endif
            </p>
            <a href="{{ route('bhw.patients.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus-fill me-1"></i> Add First Woman
            </a>
        </div>
    @endif
</div>

@endsection
