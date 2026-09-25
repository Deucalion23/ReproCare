@extends('rhu.layout')

@section('title', 'BHW Presidents - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">BHW Presidents
            </div>
            <p class="page-hero-subtitle">
                Manage accounts for Barangay Health Worker Presidents.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('rhu.staff-transitions.index', ['type' => 'president_replace']) }}" class="btn btn-filter">
                <i class="bi bi-arrow-left-right me-1"></i> Replace President
            </a>
            <a href="{{ route('rhu.bhw-presidents.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus-fill me-1"></i> Register BHW President
            </a>
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
        @if($presidents->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Full Name</th>
                            <th>Email Address</th>
                            <th>Contact Number</th>
                            <th>Assigned Barangay</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($presidents as $p)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $p->profile_image_url }}"
                                             alt="{{ $p->name }}"
                                             class="rounded-circle"
                                             style="width:38px; height:38px; object-fit:cover;"
                                             onerror="this.onerror=null;this.src='{{ $p->gender === 'male' ? '/images/avatars/avatar-male.svg' : '/images/avatars/avatar-female.svg' }}';">
                                        <div>
                                            <span class="fw-700" style="color:var(--text);">{{ $p->name }}</span>
                                            <div style="font-size:0.75rem; color:var(--text-muted);">Registered {{ $p->created_at->format('M j, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size:0.875rem;">{{ $p->email }}</span>
                                </td>
                                <td style="font-size:0.875rem;">
                                    {{ $p->contact_number ?? 'N/A' }}
                                </td>
                                <td>
                                    <span class="fw-600">{{ $p->barangay ?? 'Barangay Burgos' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $p->status === 'approved' ? 'success' : 'warning' }} text-white text-xs">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('rhu.bhw-presidents.show', $p->id) }}" class="btn btn-xs btn-outline-primary" style="font-size:0.75rem;">
                                            <i class="bi bi-eye"></i> Details
                                        </a>
                                        <a href="{{ route('rhu.bhw-presidents.edit', $p->id) }}" class="btn btn-xs btn-outline-warning" style="font-size:0.75rem;">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <x-archive-form :action="route('rhu.bhw-presidents.destroy', $p->id)" label="Archive" title="Archive president (retained for audit)" btnClass="btn btn-xs btn-outline-warning" icon="bi bi-archive" :confirmText="'Archive BHW President ' . $p->name . '? Sessions are revoked and history is retained.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($presidents->hasPages())
                <div class="card-footer bg-transparent border-top">
                    {{ $presidents->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-person-badge" style="font-size:3rem; color:var(--text-muted);"></i>
                <h5 class="mt-3">No BHW Presidents Registered</h5>
                <p class="text-muted text-xs">Register new BHW President accounts to manage barangay reports.</p>
            </div>
        @endif
    </div>
</div>

@endsection
