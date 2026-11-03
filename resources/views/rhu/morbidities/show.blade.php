@extends('rhu.layout')

@section('title', 'Morbidity Near-Miss Case Details - RHU Portal | ReproCare')

@push('styles')
@include('includes.report-queue-styles')
@include('includes.case-record-styles')
@endpush

@section('rhu-content')

<div class="workspace-stack rq-stack">

    <div class="rq-bar fade-in-card">
        <div class="rq-bar-id">
            <div class="rq-bar-title"><i class="bi bi-heart-pulse-fill" aria-hidden="true"></i>Near-Miss Case Details</div>
            <p class="rq-bar-subtitle">Morbidity surveillance record for a life-threatening maternal complication.</p>
        </div>

        <div class="rq-chips" aria-label="Case status">
            <span class="summary-chip chip-{{ $morbidity->outcome === 'died' ? 'danger' : ($morbidity->outcome === 'transferred_to_higher_facility' ? 'warning' : 'success') }} rq-chip">
                Outcome: {{ $morbidity->outcome_label }}
            </span>
            <span class="summary-chip chip-{{ $morbidity->review_status === 'reviewed' ? 'success' : ($morbidity->review_status === 'pending' ? 'info' : 'warning') }} rq-chip">
                Review: {{ ucwords(str_replace('_', ' ', $morbidity->review_status)) }}
            </span>
        </div>

        <a href="{{ route('rhu.morbidities.index') }}" class="rq-action">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>Back to list
        </a>
        @if($morbidity->review_status !== 'reviewed')
            <a href="{{ route('rhu.morbidities.edit', $morbidity->id) }}" class="rq-action">
                <i class="bi bi-pencil-fill" aria-hidden="true"></i>Edit case
            </a>
            <x-archive-form :action="route('rhu.morbidities.destroy', $morbidity->id)" label="Archive" title="Archive case (retained for audit)" btnClass="btn btn-sm btn-warning text-white" icon="bi bi-archive-fill" confirmText="Archive this near-miss case? It will be retained for FHSIS/MNCHN audit and can be restored." />
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="cr-body">
        {{-- Complication record --}}
        <div class="card cr-card fade-in-card">
            <div class="card-header">
                <h6 class="cr-card-title">Clinical complication record</h6>
            </div>
            <div class="card-body">
                <div class="cr-grid">
                    <div class="cr-item">
                        <div class="cr-label">Patient name</div>
                        <div class="cr-value">{{ $morbidity->patient_name }}</div>
                        <div class="cr-sub">{{ $morbidity->user_id ? 'Enrolled patient' : 'Unlinked / external patient' }}</div>
                    </div>
                    <div class="cr-item">
                        <div class="cr-label">Purok &amp; barangay</div>
                        <div class="cr-value">{{ $morbidity->purok->name ?? '—' }}</div>
                        <div class="cr-sub">{{ $morbidity->barangay }}</div>
                    </div>

                    <div class="cr-item">
                        <div class="cr-label">Complication type</div>
                        <div><span class="summary-chip chip-danger rq-chip">{{ $morbidity->complication_type_label }}</span></div>
                    </div>
                    <div class="cr-item">
                        <div class="cr-label">Date &amp; time of event</div>
                        <div class="cr-value">{{ $morbidity->event_date->format('M j, Y') }}</div>
                        <div class="cr-sub">{{ $morbidity->event_time ? \Carbon\Carbon::parse($morbidity->event_time)->format('g:i A') : 'Time unrecorded' }}</div>
                    </div>

                    <div class="cr-item">
                        <div class="cr-label">Place of event</div>
                        <div class="cr-value">{{ $morbidity->place_of_event_label }}</div>
                    </div>
                    @if($morbidity->outcome === 'died' && $morbidity->maternalDeath)
                        <div class="cr-item">
                            <div class="cr-label">Linked maternal death record</div>
                            <a href="{{ route('rhu.maternal-deaths.show', $morbidity->maternal_death_id) }}" class="btn btn-sm btn-outline-danger mt-1">
                                <i class="bi bi-journal-x me-1" aria-hidden="true"></i>View mortality file
                            </a>
                        </div>
                    @endif

                    <div class="cr-item">
                        <div class="cr-label">Recorded by</div>
                        <div class="cr-value" style="font-size:0.78rem;">{{ $morbidity->recordedBy->name ?? 'Unknown' }}</div>
                        <div class="cr-sub">{{ $morbidity->created_at->format('M j, Y g:i A') }}</div>
                    </div>

                    <div class="cr-item cr-wide">
                        <div class="cr-label">Clinical presentation</div>
                        <p class="cr-note">{{ $morbidity->description ?: 'No clinical description recorded.' }}</p>
                    </div>

                    <div class="cr-item cr-wide">
                        <div class="cr-label">Emergency interventions performed</div>
                        <p class="cr-note">{{ $morbidity->interventions_done ?: 'No specific clinical interventions logged.' }}</p>
                    </div>

                    <div class="cr-item cr-wide">
                        <div class="cr-label">Surveillance audit notes</div>
                        <p class="cr-note cr-note-quiet">{{ $morbidity->notes ?: 'No surveillance notes submitted.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Review panel --}}
        <div class="card cr-card fade-in-card">
            <div class="card-header">
                <h6 class="cr-card-title">Surveillance review status</h6>
            </div>
            <div class="card-body">
                @if($morbidity->reviewed_by_id)
                    <div class="cr-state mb-2">
                        <i class="bi bi-shield-check-fill text-{{ $morbidity->review_status === 'reviewed' ? 'success' : 'info' }}" aria-hidden="true"></i>
                        <div class="cr-state-body">
                            <div class="cr-state-title">Reviewed — {{ ucwords(str_replace('_', ' ', $morbidity->review_status)) }}</div>
                            <p class="cr-state-sub">Audited by the City Health Office surveillance team.</p>
                        </div>
                    </div>

                    <div class="cr-item">
                        <div class="cr-label">Review audit notes</div>
                        <p class="cr-note cr-note-quiet">{{ $morbidity->review_notes ?: 'No clinical review notes provided.' }}</p>
                    </div>
                    <div class="cr-item mt-2">
                        <div class="cr-label">Reviewed by</div>
                        <div class="cr-value" style="font-size:0.78rem;">{{ $morbidity->reviewedBy->name ?? 'Unknown' }}</div>
                        <div class="cr-sub">{{ $morbidity->reviewed_at ? $morbidity->reviewed_at->format('M j, Y g:i A') : '—' }}</div>
                    </div>
                @else
                    <div class="cr-state">
                        <i class="bi bi-shield-slash" style="color:var(--color-text-muted);" aria-hidden="true"></i>
                        <div class="cr-state-body">
                            <div class="cr-state-title">Pending clinical audit</div>
                            <p class="cr-state-sub">Awaiting review and classification audit from the City Health Office surveillance team.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
