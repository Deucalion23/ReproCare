@extends('rhu.layout')

@section('title', 'Morbidity Near-Miss Case Details - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Near-Miss Case Details
            </div>
            <p class="page-hero-subtitle">
                Morbidity Surveillance and life-threatening maternal near-miss complication record.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('rhu.morbidities.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
            @if($morbidity->review_status !== 'reviewed')
                <a href="{{ route('rhu.morbidities.edit', $morbidity->id) }}" class="btn btn-warning btn-sm text-dark fw-600">
                    <i class="bi bi-pencil-fill me-1"></i> Edit Case
                </a>
                <x-archive-form :action="route('rhu.morbidities.destroy', $morbidity->id)" label="Archive" title="Archive case (retained for audit)" btnClass="btn btn-warning btn-sm text-white" icon="bi bi-archive-fill" confirmText="Archive this near-miss case? It will be retained for FHSIS/MNCHN audit and can be restored." />
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
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0 fw-700 text-dark">Clinical Complication Record
                </h5>
                <span class="badge bg-{{ $morbidity->outcome === 'died' ? 'danger' : ($morbidity->outcome === 'transferred_to_higher_facility' ? 'warning' : 'success') }} text-white text-xs py-1.5 px-3 rounded-pill">
                    Outcome: {{ str_replace('_', ' ', ucfirst($morbidity->outcome)) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Patient Name</div>
                        <div class="fw-700 text-lg text-dark">{{ $morbidity->patient_name }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">
                            {{ $morbidity->user_id ? 'Enrolled Patient' : 'Unlinked / External Patient' }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Purok &amp; Barangay</div>
                        <div class="fw-600 text-dark">{{ $morbidity->purok->name ?? 'N/A' }}</div>
                        <div style="font-size:0.78rem; color:var(--text-muted);">{{ $morbidity->barangay }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Complication Type</div>
                        <span class="badge bg-danger-soft text-danger fw-700 py-2 px-3 rounded text-sm d-inline-block">
                            {{ str_replace('_', ' ', ucfirst($morbidity->complication_type)) }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Date &amp; Time of Event</div>
                        <div class="fw-600 text-dark">{{ $morbidity->event_date->format('F j, Y') }}</div>
                        <div style="font-size:0.78rem; color:var(--text-muted);">
                            {{ $morbidity->event_time ? \Carbon\Carbon::parse($morbidity->event_time)->format('g:i A') : 'Time unrecorded' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Place of Event</div>
                        <div class="fw-600 text-dark">{{ str_replace('_', ' ', ucfirst($morbidity->place_of_event)) }}</div>
                    </div>
                    <div class="col-md-6">
                        @if($morbidity->outcome === 'died' && $morbidity->maternalDeath)
                            <div class="text-muted text-xs mb-1">Linked Maternal Death Record</div>
                            <a href="{{ route('rhu.maternal-deaths.show', $morbidity->maternal_death_id) }}" class="btn btn-xs btn-outline-danger py-1 px-2.5 rounded fw-600 text-xs mt-1">
                                <i class="bi bi-journal-x me-1"></i> View Mortality File
                            </a>
                        @endif
                    </div>

                    <div class="col-12 border-top pt-3">
                        <div class="text-muted text-xs mb-1">Clinical Presentation Description</div>
                        <p class="mb-0 p-3 rounded border bg-light text-dark" style="font-size:0.875rem; line-height:1.5; white-space:pre-wrap;">{{ $morbidity->description ?? 'No clinical description recorded.' }}</p>
                    </div>

                    <div class="col-12 border-top pt-3">
                        <div class="text-muted text-xs mb-1">Emergency Interventions Performed</div>
                        <p class="mb-0 p-3 rounded border bg-light text-dark" style="font-size:0.875rem; line-height:1.5; white-space:pre-wrap;">{{ $morbidity->interventions_done ?? 'No specific clinical interventions logged.' }}</p>
                    </div>

                    <div class="col-12 border-top pt-3">
                        <div class="text-muted text-xs mb-1">Surveillance Audit Notes</div>
                        <p class="mb-0 p-3 rounded border bg-light text-dark" style="font-size:0.875rem; line-height:1.5; font-style:italic;">
                            "{{ $morbidity->notes ?? 'No surveillance notes submitted.' }}"
                        </p>
                    </div>

                    <div class="col-12 border-top pt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-muted text-xs mb-1">Recorded By</div>
                                <div style="font-size:0.82rem; color:var(--text);">{{ $morbidity->recordedBy->name ?? 'Unknown' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted text-xs mb-1">Recorded At</div>
                                <div style="font-size:0.82rem; color:var(--text);">{{ $morbidity->created_at->format('F j, Y \a\t g:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Review audit column --}}
    <div class="col-lg-4">
        <div class="card fade-in-card h-100">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0 fw-700 text-dark">Surveillance Review Status
                </h5>
            </div>
            <div class="card-body">
                @if($morbidity->reviewed_by_id)
                    <div class="text-center py-4 mb-4">
                        <i class="bi bg-light p-3 rounded-circle text-{{ $morbidity->review_status === 'reviewed' ? 'success' : 'info' }} bi-shield-check-fill d-inline-block" style="font-size:2.5rem;"></i>
                        <h6 class="mt-3 fw-700">Review Status: {{ ucfirst($morbidity->review_status) }}</h6>
                        <p class="text-muted text-xs">
                            This near-miss morbidity event has been audited by the City Health Office (CHO).
                        </p>
                    </div>

                    <div class="border rounded p-3 bg-light">
                        <div class="text-muted text-xs mb-1">Review Audit Notes</div>
                        <p class="mb-0 text-dark" style="font-size:0.875rem; line-height:1.5; font-style:italic;">
                            "{{ $morbidity->review_notes ?? 'No clinical review notes provided.' }}"
                        </p>
                        <div class="text-muted text-xs mt-3">Reviewed By</div>
                        <div style="font-size:0.82rem; color:var(--text); font-weight:600;">{{ $morbidity->reviewedBy->name ?? 'Unknown' }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">
                            {{ $morbidity->reviewed_at ? $morbidity->reviewed_at->format('F j, Y \a\t g:i A') : 'N/A' }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-shield-slash text-muted" style="font-size:3rem;"></i>
                        <h6 class="mt-3 fw-700 text-dark">Pending Clinical Audit</h6>
                        <p class="text-muted text-xs px-2 mt-2">
                            This morbidity near-miss record is currently awaiting review and classification audit from the City Health Office (CHO) surveillance team.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
