@extends('cho.layout')

@section('title', 'All Women - CHO Portal | ReproCare')

@push('styles')
<style>
    .usr-filter-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; }
    .usr-filter-card .card-body { padding:1rem 1.25rem; }
    .wd-filters { display:flex; flex-wrap:wrap; gap:0.5rem; align-items:center; }
    .wd-search { flex:1 1 230px; max-width:320px; }
    .wd-select { flex:0 1 150px; min-width:125px; }
    .wd-filter-actions { display:flex; gap:0.4rem; align-items:center; margin-left:auto; }
    .usr-group { background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; border:none !important; border-radius:10px !important; overflow:hidden; }
    .usr-group .input-group-text { background:transparent !important; border:none !important; color:var(--color-text-muted) !important; padding:0.35rem 0.55rem; }
    .usr-group .form-control, .usr-group .form-select { background:transparent !important; border:none !important; box-shadow:none !important; color:var(--color-text) !important; padding:0.35rem 0.5rem; font-size:0.83rem; height:auto; min-height:34px; }
    .usr-group .form-control:focus, .usr-group .form-select:focus { background:var(--color-surface) !important; background-color:var(--color-surface) !important; box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent) !important; }
    .usr-apply-btn { border:none !important; border-radius:999px !important; font-weight:800 !important; background:var(--color-surface-strong) !important; background-color:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent); padding:0.32rem 1rem !important; font-size:0.8rem !important; }
    .wd-clear-btn { border-radius:999px !important; padding:0.32rem 0.55rem !important; font-size:0.8rem !important; }
    .usr-table-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    .usr-table-card table thead th { border:none !important; background:transparent !important; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-muted) !important; }
    .usr-table-card table tbody td { border:none !important; vertical-align:middle; }
    .usr-table-card table tbody tr:hover { background:var(--color-bg); }
    .status-pill { font-size:0.72rem; font-weight:800; padding:0.25em 0.8em; border-radius:999px; white-space:nowrap; }
    .status-approved { background:var(--color-success-soft); color:var(--color-success-text); }
    .status-pending { background:var(--color-warning-soft, #fff7e6); color:var(--color-warning-text, #8a5a00); }
    .status-other { background:var(--color-surface-soft); color:var(--color-text-muted); }
    .status-unregistered { background:var(--color-warning-soft, #fff7e6); color:var(--color-warning-text, #8a5a00); }
    .wd-sub { font-size:0.75rem; color:var(--color-text-muted); }
    .wd-type-pills { display:flex; flex-wrap:wrap; gap:0.4rem; margin-top:0.75rem; }
    .wd-type-pill { display:inline-flex; align-items:center; gap:0.35rem; padding:0.35rem 0.7rem; border-radius:999px; border:1px solid var(--color-border); background:var(--color-surface); color:var(--color-text-muted); font-size:0.74rem; font-weight:800; text-decoration:none; }
    .wd-type-pill:hover { border-color:var(--color-primary); color:var(--color-primary-text); }
    .wd-type-pill.active { border-color:var(--color-primary); background:var(--color-primary-soft); color:var(--color-primary-text); }
    @media (max-width: 767.98px) {
        .wd-search { max-width:none; }
        .wd-filter-actions { margin-left:0; }
    }
</style>
@endpush

@section('cho-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">All Women</div>
            <p class="page-hero-subtitle" style="font-weight:600;">
                City-wide directory of registered and unregistered women. Read-only oversight — verification and archiving stay with RHU.
            </p>
        </div>
    </div>
</div>

{{-- Filters Card --}}
<div class="card fade-in-card mb-4 usr-filter-card">
    <div class="card-body" style="border:none;">
        <form method="GET" action="{{ route('cho.patients.index') }}" class="wd-filters">
            <div class="wd-search">
                <div class="input-group usr-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" class="form-control" value="{{ $search }}"
                           placeholder="Search name, contact...">
                </div>
            </div>
            <div class="wd-select">
                <div class="input-group usr-group">
                    <span class="input-group-text"><i class="bi bi-people"></i></span>
                    <select name="type" class="form-select">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All types</option>
                        <option value="registered" {{ $type === 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="unregistered" {{ $type === 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                    </select>
                </div>
            </div>
            <div class="wd-select">
                <div class="input-group usr-group">
                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                    <select name="barangay" class="form-select">
                        <option value="">All barangays</option>
                        @foreach ($barangays as $area)
                            <option value="{{ $area }}" {{ $barangay === $area ? 'selected' : '' }}>{{ $area }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="wd-select">
                <div class="input-group usr-group">
                    <span class="input-group-text"><i class="bi bi-funnel"></i></span>
                    <select name="status" class="form-select">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All statuses</option>
                        @foreach ($statuses as $option)
                            <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="wd-select">
                <div class="input-group usr-group">
                    <span class="input-group-text"><i class="bi bi-arrow-down-up"></i></span>
                    <select name="sort" class="form-select" aria-label="Sort women by">
                        @foreach ($sorts as $key => $label)
                            <option value="{{ $key }}" {{ $sort === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="wd-filter-actions">
                <button type="submit" class="btn usr-apply-btn">Apply</button>
                @if ($search !== '' || $type !== 'all' || $status !== 'all' || $barangay !== '' || $sort !== 'date_desc')
                    <a href="{{ route('cho.patients.index') }}" class="btn btn-outline-secondary wd-clear-btn" title="Clear filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="wd-type-pills mt-3">
            <a href="{{ route('cho.patients.index', array_filter(['search' => $search, 'barangay' => $barangay, 'status' => $status, 'sort' => $sort])) }}"
               class="wd-type-pill {{ $type === 'all' ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> All · {{ $registeredCount + $unregisteredCount }}
            </a>
            <a href="{{ route('cho.patients.index', array_filter(['search' => $search, 'barangay' => $barangay, 'status' => $status, 'sort' => $sort, 'type' => 'registered'])) }}"
               class="wd-type-pill {{ $type === 'registered' ? 'active' : '' }}">
                <i class="bi bi-person-check-fill"></i> Registered · {{ $registeredCount }}
            </a>
            <a href="{{ route('cho.patients.index', array_filter(['search' => $search, 'barangay' => $barangay, 'status' => $status, 'sort' => $sort, 'type' => 'unregistered'])) }}"
               class="wd-type-pill {{ $type === 'unregistered' ? 'active' : '' }}">
                <i class="bi bi-person-plus-fill"></i> Unregistered · {{ $unregisteredCount }}
            </a>
        </div>
    </div>
</div>

<div class="card fade-in-card usr-table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
            <thead class="table-light">
                <tr>
                    <th class="px-3 py-3">Woman</th>
                    <th class="py-3">Type / Status</th>
                    <th class="py-3">Contact</th>
                    <th class="py-3">Barangay / Purok</th>
                    <th class="py-3">Pregnancy</th>
                    <th class="py-3">Age / Date of birth</th>
                    <th class="py-3 text-end px-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($women as $woman)
                    @php
                        $isWalkIn = $woman->type === 'unregistered';
                        $displayName = $isWalkIn ? $woman->full_name : $woman->name;
                        $pregnancy = $woman->pregnancies->first();
                    @endphp
                    <tr>
                        <td class="px-3">
                            <div class="d-flex align-items-center gap-2">
                                <x-patient-avatar :patient="$woman" :name="$displayName" :size="32" />
                                <div style="min-width:0;">
                                    <div class="fw-700">{{ $displayName ?: 'Unknown' }}</div>
                                    <small class="text-muted">{{ $isWalkIn ? 'Field-only profile' : ($woman->email ?: 'No email') }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($isWalkIn)
                                <span class="status-pill status-unregistered"><i class="bi bi-person-plus"></i> Unregistered</span>
                            @else
                                <span class="status-pill status-{{ in_array($woman->status, ['approved', 'pending']) ? $woman->status : 'other' }}">{{ ucfirst($woman->status ?? 'approved') }}</span>
                                <div class="wd-sub mt-1">Registered</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $woman->contact_number ?: '—' }}</div>
                            @if(!$isWalkIn && $woman->email)
                                <small class="text-muted">{{ $woman->email }}</small>
                            @endif
                        </td>
                        <td>
                            <div>{{ $woman->barangay ?: '—' }}</div>
                            <small class="text-muted">{{ $woman->purok?->name ?: 'No purok' }}</small>
                        </td>
                        <td>
                            @if($pregnancy)
                                <span class="status-pill status-approved">Pregnant</span>
                                <div class="wd-sub mt-1">EDD {{ $pregnancy->edd ? $pregnancy->edd->format('M d, Y') : 'unknown' }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($woman->date_of_birth)
                                <div>{{ $woman->age }} y/o</div>
                                <small class="text-muted">{{ $woman->date_of_birth->format('M d, Y') }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end px-3">
                            @if($isWalkIn)
                                <a href="{{ route('cho.patients.walk-in', $woman->id) }}" class="btn btn-xs btn-outline-primary" style="font-size:0.75rem;white-space:nowrap;">
                                    <i class="bi bi-eye"></i> Records
                                </a>
                            @else
                                <a href="{{ route('cho.patients.show', $woman->id) }}" class="btn btn-xs btn-outline-primary" style="font-size:0.75rem;white-space:nowrap;">
                                    <i class="bi bi-eye"></i> Records
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people" style="font-size:2rem;"></i>
                            <p class="mt-2 mb-0">No women found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($women->hasPages())
        <div class="p-3">{{ $women->links() }}</div>
    @endif
</div>
@endsection
