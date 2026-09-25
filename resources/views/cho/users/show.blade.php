@extends('cho.layout')

@section('title', 'User Details - CHO Portal | ReproCare')

@section('cho-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Staff Profile Details
            </div>
            <p class="page-hero-subtitle">
                Detailed profile info and account status for health worker.
            </p>
        </div>
        <a href="{{ route('cho.users.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to User Management
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Profile Left Card --}}
    <div class="col-lg-4">
        <div class="card fade-in-card text-center py-4">
            <div class="card-body">
                <img src="{{ $user->profile_image_url }}"
                     alt="{{ $user->name }}"
                     class="rounded-circle mb-3 border border-4 border-light"
                     style="width:110px; height:110px; object-fit:cover;"
                     onerror="this.onerror=null;this.src='{{ $user->gender === 'male' ? '/images/avatars/avatar-male.svg' : '/images/avatars/avatar-female.svg' }}';">
                
                <h4 class="fw-700 mb-1" style="color:var(--text); font-family:'Plus Jakarta Sans',sans-serif;">{{ $user->name }}</h4>
                <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:1rem;">
                    {{ $user->email }}
                </div>

                @php
                    $roleLabel = match($user->role) {
                        'cho' => 'CHO Admin',
                        'rhu' => 'RHU Admin',
                        'midwife' => 'Midwife',
                        'bhw_president' => 'BHW President',
                        'bhw' => 'BHW',
                        default => ucfirst($user->role)
                    };
                    $roleClass = match($user->role) {
                        'cho' => 'badge-role-cho',
                        'rhu' => 'badge-role-rhu',
                        'midwife' => 'badge-role-midwife',
                        'bhw_president' => 'badge-role-bhw-president',
                        'bhw' => 'badge-role-bhw',
                        default => 'badge-role-user'
                    };
                @endphp

                <div class="mb-3 d-flex justify-content-center gap-2">
                    <span class="badge {{ $roleClass }} py-2 px-3 rounded-pill" style="font-size:0.8rem;">
                        {{ $roleLabel }}
                    </span>
                    <span class="badge bg-{{ $user->status === 'approved' ? 'success' : ($user->status === 'suspended' ? 'danger' : 'warning') }} text-white py-2 px-3 rounded-pill" style="font-size:0.8rem;">
                        {{ ucfirst($user->status) }}
                    </span>
                </div>

                <div class="pt-3 border-top d-grid gap-2">
                    @if($user->status === 'pending')
                        <form method="POST" action="{{ route('cho.users.approve', $user->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success text-white w-100">
                                <i class="bi bi-check-lg me-1"></i> Approve Account
                            </button>
                        </form>
                        <form method="POST" action="{{ route('cho.users.reject', $user->id) }}" onsubmit="return confirm('Reject and archive this user\'s account? The record will be retained but hidden.');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-archive me-1"></i> Reject Account
                            </button>
                        </form>
                    @elseif($user->status === 'approved')
                        <form method="POST" action="{{ route('cho.users.deactivate', $user->id) }}" onsubmit="return confirm('Are you sure you want to suspend this account?');">
                            @csrf
                            <button type="submit" class="btn btn-danger text-white w-100">
                                <i class="bi bi-person-x me-1"></i> Suspend Account
                            </button>
                        </form>
                    @elseif($user->status === 'suspended')
                        <form method="POST" action="{{ route('cho.users.activate', $user->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success text-white w-100">
                                <i class="bi bi-person-check me-1"></i> Activate Account
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Details Right Card --}}
    <div class="col-lg-8">
        <div class="card fade-in-card">
            <div class="card-header">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif;">Personal & Station Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="label-heading text-muted text-xs mb-1">First Name</div>
                        <div class="fw-600" style="color:var(--text);">{{ $user->first_name }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="label-heading text-muted text-xs mb-1">M.I.</div>
                        <div class="fw-600" style="color:var(--text);">{{ $user->middle_initial ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="label-heading text-muted text-xs mb-1">Last Name</div>
                        <div class="fw-600" style="color:var(--text);">{{ $user->last_name }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="label-heading text-muted text-xs mb-1">Date of Birth</div>
                        <div class="fw-600" style="color:var(--text);">{{ $user->date_of_birth ? $user->date_of_birth->format('F j, Y') : 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="label-heading text-muted text-xs mb-1">Gender</div>
                        <div class="fw-600" style="color:var(--text);">{{ ucfirst($user->gender) }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="label-heading text-muted text-xs mb-1">Contact Number</div>
                        <div class="fw-600" style="color:var(--text);">{{ $user->contact_number ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="label-heading text-muted text-xs mb-1">Assigned Station / Office</div>
                        <div class="fw-600" style="color:var(--text);">
                            @if($user->role === 'rhu')
                                {{ $user->rhu_assignment ?? 'Unassigned RHU' }}
                            @elseif($user->role === 'cho')
                                {{ $user->cho_office ?? 'City Health Office' }}
                            @else
                                {{ $user->barangay ?? 'Barangay Burgos, San Carlos City' }}
                            @endif
                        </div>
                    </div>

                    <div class="col-12 pt-3 border-top">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="label-heading text-muted text-xs mb-1">Registered On</div>
                                <div style="font-size:0.875rem; color:var(--text);">{{ $user->created_at->format('l, F j, Y \a\t g:i A') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="label-heading text-muted text-xs mb-1">Last Profile Update</div>
                                <div style="font-size:0.875rem; color:var(--text);">{{ $user->updated_at->format('l, F j, Y \a\t g:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
