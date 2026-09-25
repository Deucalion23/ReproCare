@extends('rhu.layout')

@section('title', 'Midwife Management - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Midwife Management
            </div>
            <p class="page-hero-subtitle">
                Manage registered midwife accounts for Rural Health Unit clinics.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('rhu.staff-transitions.index', ['type' => 'midwife_replace']) }}" class="btn btn-filter">
                <i class="bi bi-arrow-left-right me-1"></i> Replace Midwife
            </a>
            <a href="{{ route('rhu.midwives.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus-fill me-1"></i> Register Midwife
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:12px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card fade-in-card">
    <div class="card-body p-0">
        @if($midwives->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Full Name</th>
                            <th>Email Address</th>
                            <th>Contact Number</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($midwives as $m)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $m->profile_image_url }}"
                                             alt="{{ $m->name }}"
                                             class="rounded-circle"
                                             style="width:38px; height:38px; object-fit:cover;"
                                             onerror="this.onerror=null;this.src='{{ $m->gender === 'male' ? '/images/avatars/avatar-male.svg' : '/images/avatars/avatar-female.svg' }}';">
                                        <div>
                                            <span class="fw-700" style="color:var(--text);">{{ $m->name }}</span>
                                            <div style="font-size:0.75rem; color:var(--text-muted);">Registered {{ $m->created_at->format('M j, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size:0.875rem;">{{ $m->email }}</span>
                                </td>
                                <td style="font-size:0.875rem;">
                                    {{ $m->contact_number ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ ucfirst($m->gender) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $m->status === 'approved' ? 'success' : 'warning' }} text-white text-xs">
                                        {{ ucfirst($m->status) }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('rhu.midwives.show', $m->id) }}" class="btn btn-xs btn-outline-primary" style="font-size:0.75rem;">
                                            <i class="bi bi-eye"></i> Details
                                        </a>
                                        <a href="{{ route('rhu.midwives.edit', $m->id) }}" class="btn btn-xs btn-outline-warning" style="font-size:0.75rem;">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <x-archive-form :action="route('rhu.midwives.destroy', $m->id)" label="Archive" title="Archive midwife (retained for audit)" btnClass="btn btn-xs btn-outline-warning" icon="bi bi-archive" :confirmText="'Archive midwife ' . $m->name . '? Sessions are revoked and history is retained.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($midwives->hasPages())
                <div class="card-footer bg-transparent border-top">
                    {{ $midwives->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-people" style="font-size:3rem; color:var(--text-muted);"></i>
                <h5 class="mt-3">No Midwives Registered</h5>
                <p class="text-muted text-xs">Register new midwife accounts to deploy them to health centers.</p>
            </div>
        @endif
    </div>
</div>

@endsection
