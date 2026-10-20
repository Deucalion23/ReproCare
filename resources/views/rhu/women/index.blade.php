@extends('rhu.layout')

@section('title', 'All Women - RHU Portal | ReproCare')

@push('styles')
<style>
    .wd-filter-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; }
    .wd-group { background:var(--color-surface-soft) !important; border:none !important; border-radius:12px !important; overflow:hidden; }
    .wd-group .input-group-text { background:transparent !important; border:none !important; color:var(--color-text-muted) !important; }
    .wd-group .form-control, .wd-group .form-select { background:transparent !important; border:none !important; box-shadow:none !important; color:var(--color-text) !important; }
    .wd-group .form-control:focus, .wd-group .form-select:focus { background:var(--color-surface) !important; box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent) !important; }
    .wd-apply-btn { border:none !important; border-radius:999px !important; font-weight:800 !important; background:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; }
    .wd-table-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    .wd-table-card table thead th { border:none !important; background:transparent !important; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-muted) !important; }
    .wd-table-card table tbody td { border:none !important; vertical-align:middle; }
    .wd-table-card table tbody tr:hover { background:var(--color-bg); }
    .wd-name { font-weight:700; color:var(--color-text); line-height:1.25; }
    .wd-sub { font-size:0.75rem; color:var(--color-text-muted); }
    .wd-pill { font-size:0.7rem; font-weight:800; padding:0.25em 0.75em; border-radius:999px; white-space:nowrap; }
    .wd-pill-registered { background:var(--color-success-soft); color:var(--color-success-text); }
    .wd-pill-unregistered { background:var(--color-warning-soft, #fff7e6); color:var(--color-warning-text, #8a5a00); }
    .wd-pill-muted { background:var(--color-surface-soft); color:var(--color-text-muted); }
    .wd-type-pills { display:flex; flex-wrap:wrap; gap:0.5rem; }
    .wd-type-pill { display:inline-flex; align-items:center; gap:0.4rem; padding:0.55rem 0.9rem; border-radius:999px; border:1px solid var(--color-border); background:var(--color-surface); color:var(--color-text-muted); font-size:0.78rem; font-weight:800; text-decoration:none; }
    .wd-type-pill:hover { border-color:var(--color-primary); color:var(--color-primary-text); }
    .wd-type-pill.active { border-color:var(--color-primary); background:var(--color-primary-soft); color:var(--color-primary-text); }
    @media (max-width: 767.98px) {
        .wd-table-card .table { font-size:0.78rem; }
        .wd-table-card .table th, .wd-table-card .table td { padding:0.5rem; }
    }
</style>
@endpush

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">All Women</div>
            <p class="page-hero-subtitle mb-0">
                Registered and unregistered women in your scope area · {{ $scopeLabel }}.
            </p>
        </div>
        <a href="{{ route('rhu.patients.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i> Register Woman
        </a>
    </div>
</div>

<div class="card fade-in-card mb-4 wd-filter-card">
    <div class="card-body">
        <form method="GET" action="{{ route('rhu.women.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-4">
                <div class="input-group wd-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" class="form-control" value="{{ $search }}"
                           placeholder="Search name, contact, address...">
                </div>
            </div>
            <div class="col-md-3 col-lg-2">
                <div class="input-group wd-group">
                    <span class="input-group-text"><i class="bi bi-people"></i></span>
                    <select name="type" class="form-select">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All types</option>
                        <option value="registered" {{ $type === 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="unregistered" {{ $type === 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-lg-2">
                <div class="input-group wd-group">
                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                    <select name="barangay" class="form-select">
                        <option value="">All barangays</option>
                        @foreach ($barangays as $area)
                            <option value="{{ $area }}" {{ $barangay === $area ? 'selected' : '' }}>{{ $area }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-lg-2">
                <div class="input-group wd-group">
                    <span class="input-group-text"><i class="bi bi-funnel"></i></span>
                    <select name="status" class="form-select">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All statuses</option>
                        @foreach ($statuses as $option)
                            <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn wd-apply-btn flex-fill">Apply</button>
                @if ($search !== '' || $type !== 'all' || $status !== 'all' || $barangay !== '')
                    <a href="{{ route('rhu.women.index') }}" class="btn btn-outline-secondary" title="Clear filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="wd-type-pills mt-3">
            <a href="{{ route('rhu.women.index', array_filter(['search' => $search, 'barangay' => $barangay, 'status' => $status])) }}"
               class="wd-type-pill {{ $type === 'all' ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> All · {{ $registeredCount + $unregisteredCount }}
            </a>
            <a href="{{ route('rhu.women.index', array_filter(['search' => $search, 'barangay' => $barangay, 'status' => $status, 'type' => 'registered'])) }}"
               class="wd-type-pill {{ $type === 'registered' ? 'active' : '' }}">
                <i class="bi bi-person-check-fill"></i> Registered · {{ $registeredCount }}
            </a>
            <a href="{{ route('rhu.women.index', array_filter(['search' => $search, 'barangay' => $barangay, 'status' => $status, 'type' => 'unregistered'])) }}"
               class="wd-type-pill {{ $type === 'unregistered' ? 'active' : '' }}">
                <i class="bi bi-person-plus-fill"></i> Unregistered · {{ $unregisteredCount }}
            </a>
        </div>
    </div>
</div>

<div class="card fade-in-card wd-table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="px-3 py-3">Woman</th>
                    <th class="py-3">Type</th>
                    <th class="py-3">Age / Date of birth</th>
                    <th class="py-3">Contact</th>
                    <th class="py-3">Barangay / Purok</th>
                    <th class="py-3">Pregnancy</th>
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
                                <span class="wd-pill wd-pill-registered">Pregnant</span>
                                <div class="wd-sub mt-1">EDD {{ $pregnancy->edd ? $pregnancy->edd->format('M d, Y') : 'unknown' }}</div>
                            @else
                                <span class="wd-pill wd-pill-muted">Not pregnant</span>
                            @endif
                        </td>
                        <td class="text-end px-3">
                            <a href="{{ route('rhu.women.show', [$woman->type, $woman->id]) }}"
                               class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;white-space:nowrap;">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people" style="font-size:2rem;"></i>
                            <p class="mt-2 mb-0">No women found in your scope area.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($women->hasPages())
        <div class="p-3">{{ $women->links() }}</div>
    @endif
</div>

@endsection
