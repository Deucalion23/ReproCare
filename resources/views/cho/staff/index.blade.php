@extends('cho.layout')

@section('title', 'Staff Directory - CHO Portal | ReproCare')

@section('cho-content')
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Staff Directory</div>
            <p class="page-hero-subtitle">RHU admins, midwives, presidents, and BHWs city-wide.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('cho.staff.midwives') }}" class="btn btn-outline-primary btn-sm">Midwives</a>
            <a href="{{ route('cho.staff.bhws') }}" class="btn btn-outline-primary btn-sm">BHWs</a>
        </div>
    </div>
</div>

<div class="card fade-in-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('cho.staff.index') }}" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search name or email...">
            </div>
            <div class="col-md-4">
                <select name="role" class="form-select">
                    <option value="all" {{ ($role ?? 'all') === 'all' ? 'selected' : '' }}>All Roles</option>
                    <option value="rhu" {{ ($role ?? '') === 'rhu' ? 'selected' : '' }}>RHU Admin</option>
                    <option value="midwife" {{ ($role ?? '') === 'midwife' ? 'selected' : '' }}>Midwife</option>
                    <option value="bhw_president" {{ ($role ?? '') === 'bhw_president' ? 'selected' : '' }}>BHW President</option>
                    <option value="bhw" {{ ($role ?? '') === 'bhw' ? 'selected' : '' }}>BHW</option>
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
                <thead><tr><th class="px-4">Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($staff as $member)
                        <tr>
                            <td class="px-4 fw-semibold">{{ $member->name }}</td>
                            <td>{{ $member->email }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $member->role)) }}</td>
                            <td><span class="badge bg-{{ ($member->status ?? 'approved') === 'approved' ? 'success' : 'warning' }}">{{ ucfirst($member->status ?? 'approved') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No staff found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="p-3 border-top d-flex justify-content-center">{{ $staff->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
