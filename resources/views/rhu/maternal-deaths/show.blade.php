@extends('rhu.layout')

@section('title', 'Maternal Death Details - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Maternal Death Details
            </div>
            <p class="page-hero-subtitle">
                Maternal Death Surveillance and Response (MDSR) clinical record.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('rhu.maternal-deaths.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
            @if($death->audit_status !== 'closed')
                <a href="{{ route('rhu.maternal-deaths.edit', $death->id) }}" class="btn btn-warning btn-sm text-dark fw-600">
                    <i class="bi bi-pencil-fill me-1"></i> Edit Record
                </a>
                <x-archive-form :action="route('rhu.maternal-deaths.destroy', $death->id)" label="Archive" title="Archive case (retained for audit)" btnClass="btn btn-warning btn-sm text-white" icon="bi bi-archive-fill" confirmText="Archive this maternal death case? It is legal medical history and will be retained for audit." />
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:12px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    {{-- Case details --}}
    <div class="col-lg-8">
        <div class="card fade-in-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">Clinical Surveillance Record
                </h5>
                <span class="badge bg-{{ $death->audit_status === 'closed' ? 'success' : ($death->audit_status === 'reviewed' ? 'info' : 'warning') }} text-white text-xs py-1.5 px-3 rounded-pill">
                    Status: {{ ucfirst($death->audit_status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Patient Name</div>
                        <div class="fw-700 text-lg" style="color:var(--text);">{{ $death->patient_name }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">{{ $death->user_id ? 'Enrolled Patient' : 'Unlinked / External Patient' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Age at Death</div>
                        <div class="fw-600" style="color:var(--text);">{{ $death->age_at_death ?? 'N/A' }} years old</div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Barangay &amp; Purok</div>
                        <div class="fw-600" style="color:var(--text);">{{ $death->purok->name ?? 'N/A' }}</div>
                        <div style="font-size:0.78rem; color:var(--text-muted);">{{ $death->barangay }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Date &amp; Time of Death</div>
                        <div class="fw-600" style="color:var(--text);">{{ $death->death_date->format('F j, Y') }}</div>
                        <div style="font-size:0.78rem; color:var(--text-muted);">{{ $death->death_time ? \Carbon\Carbon::parse($death->death_time)->format('g:i A') : 'Time unrecorded' }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Place of Death</div>
                        <div class="fw-600" style="color:var(--text);">{{ str_replace('_', ' ', ucfirst($death->place_of_death)) }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Timing of Death</div>
                        <div class="fw-600" style="color:var(--text);">{{ str_replace('_', ' ', ucfirst($death->death_timing)) }}</div>
                    </div>

                    <div class="col-12 border-top pt-3">
                        <div class="text-muted text-xs mb-1">Primary Cause Category</div>
                        <span class="badge bg-danger text-white py-2 px-3 rounded mb-2" style="font-size:0.82rem;">
                            {{ str_replace('_', ' ', ucfirst($death->cause_category)) }}
                        </span>
                        <div class="text-muted text-xs mt-2 mb-1">Clinical Cause Details</div>
                        <p class="mb-0 p-3 rounded border bg-light text-dark" style="font-size:0.875rem; line-height:1.5;">
                            {{ $death->cause_of_death }}
                        </p>
                    </div>

                    <div class="col-12 border-top pt-3">
                        <div class="text-muted text-xs mb-1">Additional Surveillance Notes</div>
                        <p class="mb-0 p-3 rounded border bg-light text-dark" style="font-size:0.875rem; line-height:1.5; font-style:italic;">
                            "{{ $death->notes ?? 'No additional clinical notes submitted.' }}"
                        </p>
                    </div>

                    <div class="col-12 border-top pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-muted text-xs mb-1">Recorded By</div>
                                <div style="font-size:0.82rem; color:var(--text);">{{ $death->recordedBy->name ?? 'Unknown' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted text-xs mb-1">Recorded At</div>
                                <div style="font-size:0.82rem; color:var(--text);">{{ $death->created_at->format('F j, Y \a\t g:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Audit Review Details --}}
    <div class="col-lg-4">
        <div class="card fade-in-card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">CHO Case Audit Review
                </h5>
            </div>
            <div class="card-body">
                @if($death->reviewed_by_id)
                    <div class="text-center py-4 mb-4">
                        <i class="bi bg-light p-3 rounded-circle text-{{ $death->audit_status === 'closed' ? 'success' : 'info' }} bi-shield-check-fill d-inline-block" style="font-size:2.5rem;"></i>
                        <h6 class="mt-3 fw-700">Audit Status: {{ ucfirst($death->audit_status) }}</h6>
                        <p class="text-muted text-xs">
                            This case review has been audited by the City Health Office (CHO).
                        </p>
                    </div>

                    <div class="border rounded p-3 bg-light">
                        <div class="text-muted text-xs mb-1">Audit Notes Logged</div>
                        <p class="mb-0 text-dark" style="font-size:0.875rem; line-height:1.5; font-style:italic;">
                            "{{ $death->audit_notes ?? 'No notes provided.' }}"
                        </p>
                        <div class="text-muted text-xs mt-3">Audited By</div>
                        <div style="font-size:0.82rem; color:var(--text); font-weight:600;">{{ $death->reviewedBy->name ?? 'Unknown' }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">{{ $death->reviewed_at ? $death->reviewed_at->format('F j, Y \a\t g:i A') : 'N/A' }}</div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-hourglass-split text-warning" style="font-size:3rem;"></i>
                        <h6 class="mt-3 fw-700">Pending CHO Review</h6>
                        <p class="text-muted text-xs px-2">
                            This case has been logged but is currently awaiting review and audit confirmation by the City Health Office (CHO).
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
