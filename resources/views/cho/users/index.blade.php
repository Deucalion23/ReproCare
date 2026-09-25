@extends('cho.layout')

@section('title', 'User Management - CHO Portal | ReproCare')

@push('styles')
<style>
    .usr-filter-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; }
    .usr-filter-card .card-body { border:none; }
    .usr-group { background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; border:none !important; border-radius:12px !important; overflow:hidden; }
    .usr-group .input-group-text { background:transparent !important; border:none !important; color:var(--color-text-muted) !important; }
    .usr-group .form-control, .usr-group .form-select { background:transparent !important; border:none !important; box-shadow:none !important; color:var(--color-text) !important; }
    .usr-group .form-control:focus, .usr-group .form-select:focus { background:var(--color-surface) !important; background-color:var(--color-surface) !important; box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent) !important; }
    .usr-group .form-control::placeholder { color:var(--color-text-muted); }
    .usr-apply-btn { border:none !important; border-radius:999px !important; font-weight:800 !important; background:var(--color-surface-strong) !important; background-color:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent); }
    .usr-apply-btn:hover { background:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; }
    .usr-table-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    .usr-table-card table thead th { border:none !important; background:transparent !important; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-muted) !important; }
    .usr-table-card table tbody td { border:none !important; }
    .usr-table-card table tbody tr:hover { background:var(--color-bg); }
</style>
@endpush

@section('cho-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">User Management
            </div>
            <p class="page-hero-subtitle" style="font-weight:600;">Review, approve, and manage administrative and healthcare worker accounts.</p>
        </div>
        <a href="{{ route('cho.users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i> Register New Staff
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:12px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Filters Card --}}
<div class="card fade-in-card mb-4 usr-filter-card">
    <div class="card-body" style="border:none;">
        <form method="GET" action="{{ route('cho.users.index') }}" class="row g-3">
            <div class="col-md-5">
                <div class="input-group usr-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search by name, email...">
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group usr-group">
                    <span class="input-group-text">
                        <i class="bi bi-funnel"></i>
                    </span>
                    <select name="role" class="form-select">
                        <option value="all" {{ $role === 'all' ? 'selected' : '' }}>All Roles</option>
                        <option value="rhu" {{ $role === 'rhu' ? 'selected' : '' }}>RHU Admin</option>
                        <option value="midwife" {{ $role === 'midwife' ? 'selected' : '' }}>Midwife</option>
                        <option value="bhw_president" {{ $role === 'bhw_president' ? 'selected' : '' }}>BHW President</option>
                        <option value="bhw" {{ $role === 'bhw' ? 'selected' : '' }}>BHW</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn usr-apply-btn">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Users List Card --}}
<div class="card fade-in-card usr-table-card">
    <div class="card-body p-0">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Full Name</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Contact</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $u->profile_image_url }}"
                                             alt="{{ $u->name }}"
                                             class="rounded-circle"
                                             style="width:38px; height:38px; object-fit:cover;"
                                             onerror="this.onerror=null;this.src='{{ $u->gender === 'male' ? '/images/avatars/avatar-male.svg' : '/images/avatars/avatar-female.svg' }}';">
                                        <div>
                                            <span class="fw-700" style="color:var(--text);">{{ $u->name }}</span>
                                            <div style="font-size:0.75rem; color:var(--text-muted);">Registered {{ $u->created_at->format('M j, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size:0.875rem;">{{ $u->email }}</span>
                                </td>
                                <td>
                                    @php
                                        $roleClass = match($u->role) {
                                            'cho' => 'badge-role-cho',
                                            'rhu' => 'badge-role-rhu',
                                            'midwife' => 'badge-role-midwife',
                                            'bhw_president' => 'badge-role-bhw-president',
                                            'bhw' => 'badge-role-bhw',
                                            default => 'badge-role-user'
                                        };
                                        $roleLabel = match($u->role) {
                                            'cho' => 'CHO Admin',
                                            'rhu' => 'RHU Admin',
                                            'midwife' => 'Midwife',
                                            'bhw_president' => 'BHW President',
                                            'bhw' => 'BHW',
                                            default => ucfirst($u->role)
                                        };
                                    @endphp
                                    <span class="badge {{ $roleClass }} px-2.5 py-1 rounded-pill" style="font-size:0.75rem;">
                                        {{ $roleLabel }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $u->status === 'approved' ? 'success' : ($u->status === 'suspended' ? 'danger' : 'warning') }} text-white text-xs">
                                        {{ ucfirst($u->status) }}
                                    </span>
                                </td>
                                <td style="font-size:0.875rem;">
                                    {{ $u->contact_number ?? 'N/A' }}
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('cho.users.show', $u->id) }}" class="btn btn-xs btn-outline-primary" style="font-size:0.75rem;">
                                            <i class="bi bi-eye"></i> Details
                                        </a>

                                        @if($u->status === 'pending')
                                            <form method="POST" action="{{ route('cho.users.approve', $u->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-success text-white" style="font-size:0.75rem;">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('cho.users.reject', $u->id) }}" class="d-inline" onsubmit="return confirm('Reject and archive this user\'s account? The record will be retained but hidden.');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-danger text-white" style="font-size:0.75rem;">
                                                    <i class="bi bi-archive"></i> Reject
                                                </button>
                                            </form>
                                        @elseif($u->status === 'approved')
                                            <form method="POST" action="{{ route('cho.users.deactivate', $u->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to suspend this account?');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-danger" style="font-size:0.75rem;">
                                                    <i class="bi bi-person-x"></i> Suspend
                                                </button>
                                            </form>
                                        @elseif($u->status === 'suspended')
                                            <form method="POST" action="{{ route('cho.users.activate', $u->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-success" style="font-size:0.75rem;">
                                                    <i class="bi bi-person-check"></i> Activate
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="card-footer bg-transparent border-top">
                    {{ $users->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-people" style="font-size:3rem; color:var(--text-muted);"></i>
                <h5 class="mt-3">No Staff Accounts Found</h5>
                <p class="text-muted text-xs">Try adjusting your filters or search query.</p>
            </div>
        @endif
    </div>
</div>

@endsection
