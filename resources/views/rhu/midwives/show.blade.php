@extends('rhu.layout')

@section('title', 'Midwife Details - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Midwife Details
            </div>
            <p class="page-hero-subtitle">
                Staff profile information and assignment details.
            </p>
        </div>
        <a href="{{ route('rhu.midwives.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Midwife Management
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Left Card --}}
    <div class="col-lg-4">
        <div class="card fade-in-card text-center py-4">
            <div class="card-body">
                <img src="{{ $midwife->profile_image_url }}"
                     alt="{{ $midwife->name }}"
                     class="rounded-circle mb-3 border border-4 border-light"
                     style="width:110px; height:110px; object-fit:cover;"
                     onerror="this.onerror=null;this.src='{{ $midwife->gender === 'male' ? '/images/avatars/avatar-male.svg' : '/images/avatars/avatar-female.svg' }}';">
                
                <h4 class="fw-700 mb-1" style="color:var(--text); font-family:'Plus Jakarta Sans',sans-serif;">{{ $midwife->name }}</h4>
                <div style="font-size:0.875rem; color:var(--text-muted); margin-bottom:1.5rem;">
                    {{ $midwife->email }}
                </div>

                <div class="mb-4">
                    <span class="badge bg-purple text-white py-2 px-3 rounded-pill" style="font-size:0.8rem;">
                        Clinical Midwife
                    </span>
                    <span class="badge bg-{{ $midwife->status === 'approved' ? 'success' : 'warning' }} text-white py-2 px-3 rounded-pill" style="font-size:0.8rem;">
                        {{ ucfirst($midwife->status) }}
                    </span>
                </div>

                <div class="pt-3 border-top">
                    <a href="{{ route('rhu.midwives.edit', $midwife->id) }}" class="btn btn-primary w-100">
                        <i class="bi bi-pencil-fill me-1"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Card --}}
    <div class="col-lg-8">
        <div class="card fade-in-card">
            <div class="card-header">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif;">Personal Profile & System Log
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="text-muted text-xs mb-1">First Name</div>
                        <div class="fw-600" style="color:var(--text);">{{ $midwife->first_name }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted text-xs mb-1">M.I.</div>
                        <div class="fw-600" style="color:var(--text);">{{ $midwife->middle_initial ?? '-' }}</div>
                    </div>
                    <div class="col-md-5">
                        <div class="text-muted text-xs mb-1">Last Name</div>
                        <div class="fw-600" style="color:var(--text);">{{ $midwife->last_name }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Date of Birth</div>
                        <div class="fw-600" style="color:var(--text);">{{ $midwife->date_of_birth ? $midwife->date_of_birth->format('F j, Y') : 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Gender</div>
                        <div class="fw-600" style="color:var(--text);">{{ ucfirst($midwife->gender) }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Contact Number</div>
                        <div class="fw-600" style="color:var(--text);">{{ $midwife->contact_number ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Assigned Health Office</div>
                        <div class="fw-600" style="color:var(--text);">Rural Health Unit 1 (RHU1)</div>
                    </div>

                    <div class="col-12 pt-3 border-top">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-muted text-xs mb-1">Registered On</div>
                                <div style="font-size:0.875rem; color:var(--text);">{{ $midwife->created_at->format('l, F j, Y \a\t g:i A') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted text-xs mb-1">Last Updated</div>
                                <div style="font-size:0.875rem; color:var(--text);">{{ $midwife->updated_at->format('l, F j, Y \a\t g:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
