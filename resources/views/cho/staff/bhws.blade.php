@extends('cho.layout')

@section('title', 'BHW Directory - CHO Portal | ReproCare')

@section('cho-content')
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">BHW Directory</div>
            <p class="page-hero-subtitle">Barangay Health Workers across catchment barangays.</p>
        </div>
        <a href="{{ route('cho.staff.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left me-1"></i> All Staff</a>
    </div>
</div>

<div class="card fade-in-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('cho.staff.bhws') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search name or email...">
            </div>
            <div class="col-md-3">
                <input type="text" name="barangay" class="form-control" value="{{ $barangay }}" placeholder="Filter by barangay...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="all" {{ ($status ?? 'approved') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="approved" {{ ($status ?? 'approved') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ ($status ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th class="px-4">Name</th><th>Email</th><th>Barangay</th><th>Contact</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($bhws as $bhw)
                        <tr>
                            <td class="px-4 fw-semibold">{{ $bhw->name }}</td>
                            <td>{{ $bhw->email }}</td>
                            <td>{{ $bhw->barangay ?? '—' }}</td>
                            <td>{{ $bhw->contact_number ?? '—' }}</td>
                            <td><span class="badge bg-{{ ($bhw->status ?? 'approved') === 'approved' ? 'success' : 'warning' }}">{{ ucfirst($bhw->status ?? 'approved') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No BHWs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="p-3 border-top d-flex justify-content-center">{{ $bhws->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
