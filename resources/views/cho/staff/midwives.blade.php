@extends('cho.layout')

@section('title', 'Midwives Directory - CHO Portal | ReproCare')

@section('cho-content')
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Midwives Directory</div>
            <p class="page-hero-subtitle">Clinical providers across the city health system.</p>
        </div>
        <a href="{{ route('cho.staff.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left me-1"></i> All Staff</a>
    </div>
</div>

<div class="card fade-in-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('cho.staff.midwives') }}" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search name or email...">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="all" {{ ($status ?? 'approved') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="approved" {{ ($status ?? 'approved') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ ($status ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th class="px-4">Name</th><th>Email</th><th>License</th><th>Contact</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($midwives as $midwife)
                        <tr>
                            <td class="px-4 fw-semibold">{{ $midwife->name }}</td>
                            <td>{{ $midwife->email }}</td>
                            <td>{{ $midwife->license_number ?? '—' }}</td>
                            <td>{{ $midwife->contact_number ?? '—' }}</td>
                            <td><span class="badge bg-{{ ($midwife->status ?? 'approved') === 'approved' ? 'success' : 'warning' }}">{{ ucfirst($midwife->status ?? 'approved') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No midwives found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="p-3 border-top d-flex justify-content-center">{{ $midwives->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
