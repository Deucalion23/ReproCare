@extends('cho.layout')

@section('title', 'Maternal Death Case Audit - CHO Portal | ReproCare')

@section('cho-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Maternal Death Case Audit
            </div>
            <p class="page-hero-subtitle">
                Review clinical factors and register maternal death surveillance reviews.
            </p>
        </div>
        <a href="{{ route('cho.maternal-deaths.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Maternal Deaths
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:12px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:12px;">
        <ul class="mb-0 text-xs">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    {{-- Case details --}}
    <div class="col-lg-7">
        <div class="card fade-in-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">Clinical Surveillance Record
                </h5>
                <span class="badge bg-{{ $death->audit_status === 'closed' ? 'success' : ($death->audit_status === 'reviewed' ? 'info' : 'warning') }} text-white text-xs py-1.5 px-3 rounded-pill">
                    Audit: {{ ucfirst($death->audit_status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Patient Name</div>
                        <div class="fw-700 text-lg" style="color:var(--text);">{{ $death->patient_name }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">{{ $death->user_id ? 'Enrolled Woman' : 'Unlinked / External Patient' }}</div>
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
                        <p class="mb-0 p-3 rounded border" style="font-size:0.875rem; color:var(--text); background:var(--bg-card2); line-height:1.5;">
                            {{ $death->cause_of_death }}
                        </p>
                    </div>

                    <div class="col-12 border-top pt-3">
                        <div class="text-muted text-xs mb-1">Additional Surveillance Notes</div>
                        <p class="mb-0 p-3 rounded border" style="font-size:0.875rem; color:var(--text); background:var(--bg-card2); line-height:1.5; font-style:italic;">
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

    {{-- Audit surveillance form --}}
    <div class="col-lg-5">
        <div class="card fade-in-card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">CHO Case Audit Review
                </h5>
            </div>
            <div class="card-body">
                @if($death->audit_status !== 'closed')
                    <form method="POST" action="{{ route('cho.maternal-deaths.audit', $death->id) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label required-label">Surveillance Status</label>
                            <select name="audit_status" class="form-select" required>
                                <option value="reviewed" {{ $death->audit_status === 'reviewed' ? 'selected' : '' }}>Reviewed (Under Active Investigation)</option>
                                <option value="closed" {{ $death->audit_status === 'closed' ? 'selected' : '' }}>Closed (Surveillance Complete)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label required-label">Audit Investigation Notes</label>
                            <textarea name="audit_notes" rows="6" class="form-control" placeholder="Input case notes, clinic review outcome, and surveillance conclusions." required>{{ $death->audit_notes }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle me-1"></i> Submit Audit Verification
                        </button>
                    </form>
                @else
                    <div class="text-center py-4 mb-4">
                        <i class="bi bi-shield-check-fill text-success" style="font-size:3rem;"></i>
                        <h6 class="mt-2 fw-700">Audit Status: Closed</h6>
                        <p class="text-muted text-xs">
                            This case review has been completed and surveillance is officially closed.
                        </p>
                    </div>

                    <div class="border rounded p-3 bg-light">
                        <div class="text-muted text-xs mb-1">Audit Notes Logged</div>
                        <p class="mb-0" style="font-size:0.875rem; color:var(--text); line-height:1.5; font-style:italic;">
                            "{{ $death->audit_notes }}"
                        </p>
                        <div class="text-muted text-xs mt-3">Audited By</div>
                        <div style="font-size:0.82rem; color:var(--text); font-weight:600;">{{ $death->reviewedBy->name ?? 'Unknown' }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">{{ $death->reviewed_at ? $death->reviewed_at->format('F j, Y \a\t g:i A') : 'N/A' }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
