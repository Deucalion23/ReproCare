@extends('cho.layout')

@section('title', 'Registered Women - CHO Portal | ReproCare')

@push('styles')
<style>
    .usr-filter-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; }
    .usr-group { background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; border:none !important; border-radius:12px !important; overflow:hidden; }
    .usr-group .input-group-text { background:transparent !important; border:none !important; color:var(--color-text-muted) !important; }
    .usr-group .form-control, .usr-group .form-select { background:transparent !important; border:none !important; box-shadow:none !important; color:var(--color-text) !important; }
    .usr-group .form-control:focus, .usr-group .form-select:focus { background:var(--color-surface) !important; background-color:var(--color-surface) !important; box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent) !important; }
    .usr-apply-btn { border:none !important; border-radius:999px !important; font-weight:800 !important; background:var(--color-surface-strong) !important; background-color:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent); }
    .usr-table-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    .usr-table-card table thead th { border:none !important; background:transparent !important; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-muted) !important; }
    .usr-table-card table tbody td { border:none !important; }
    .usr-table-card table tbody tr:hover { background:var(--color-bg); }
    .status-pill { font-size:0.72rem; font-weight:800; padding:0.25em 0.8em; border-radius:999px; }
    .status-approved { background:var(--color-success-soft); color:var(--color-success-text); }
    .status-pending { background:var(--color-warning-soft, #fff7e6); color:var(--color-warning-text, #8a5a00); }
    .status-other { background:var(--color-surface-soft); color:var(--color-text-muted); }
</style>
@endpush

@section('cho-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Registered Women</div>
            <p class="page-hero-subtitle" style="font-weight:600;">City-wide patient directory with records. Read-only oversight — verification and archiving stay with RHU.</p>
        </div>
    </div>
</div>

{{-- Filters Card --}}
<div class="card fade-in-card mb-4 usr-filter-card">
    <div class="card-body" style="border:none;">
        <form method="GET" action="{{ route('cho.patients.index') }}" class="row g-3">
            <div class="col-md-5">
                <div class="input-group usr-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search by name, email, contact...">
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group usr-group">
                    <span class="input-group-text"><i class="bi bi-funnel"></i></span>
                    <select name="status" class="form-select">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All statuses</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group usr-group">
                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                    <select name="barangay" class="form-select">
                        <option value="">All barangays</option>
                        @foreach ($barangays as $b)
                            <option value="{{ $b }}" {{ $barangay === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="usr-apply-btn">Go</button>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in-card usr-table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
            <thead class="table-light">
                <tr>
                    <th class="px-3 py-3">Patient</th>
                    <th class="py-3">Contact / Email</th>
                    <th class="py-3">Barangay</th>
                    <th class="py-3">Status</th>
                    <th class="py-3">Active Pregnancy</th>
                    <th class="py-3 text-end px-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($women as $woman)
                    <tr>
                        <td class="px-3">
                            <div class="fw-700">{{ $woman->first_name }} {{ $woman->last_name }}</div>
                            <small class="text-muted">ID: #{{ $woman->id }}</small>
                        </td>
                        <td>
                            <div>{{ $woman->display_contact_number }}</div>
                            <small class="text-muted">{{ $woman->email }}</small>
                        </td>
                        <td>{{ $woman->display_barangay }}</td>
                        <td><span class="status-pill status-{{ in_array($woman->status, ['approved', 'pending']) ? $woman->status : 'other' }}">{{ ucfirst($woman->status ?? 'approved') }}</span></td>
                        <td>
                            @if($woman->active_pregnancies_count > 0)
                                <span class="status-pill status-approved">Yes</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end px-3">
                            <a href="{{ route('cho.patients.show', $woman->id) }}" class="btn btn-xs btn-outline-primary" style="font-size:0.75rem;">
                                <i class="bi bi-eye"></i> Records
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people" style="font-size:2rem;"></i>
                            <p class="mt-2 mb-0">No registered women found.</p>
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
