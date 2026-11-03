@extends('rhu.layout')

@section('title', 'Maternal Death Details - RHU Portal | ReproCare')

@push('styles')
@include('includes.report-queue-styles')
@include('includes.case-record-styles')
@endpush

@section('rhu-content')

<div class="workspace-stack rq-stack">

    <div class="rq-bar fade-in-card">
        <div class="rq-bar-id">
            <div class="rq-bar-title"><i class="bi bi-journal-x" aria-hidden="true"></i>Maternal Death Details</div>
            <p class="rq-bar-subtitle">Maternal Death Surveillance and Response (MDSR) clinical record.</p>
        </div>

        <div class="rq-chips" aria-label="Case status">
            <span class="summary-chip chip-{{ $death->audit_status === 'closed' ? 'success' : ($death->audit_status === 'reviewed' ? 'info' : 'warning') }} rq-chip">
                Audit: {{ ucwords(str_replace('_', ' ', $death->audit_status)) }}
            </span>
        </div>

        <a href="{{ route('rhu.maternal-deaths.index') }}" class="rq-action">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>Back to list
        </a>
        @if($death->audit_status !== 'closed')
            <a href="{{ route('rhu.maternal-deaths.edit', $death->id) }}" class="rq-action">
                <i class="bi bi-pencil-fill" aria-hidden="true"></i>Edit record
            </a>
            <x-archive-form :action="route('rhu.maternal-deaths.destroy', $death->id)" label="Archive" title="Archive case (retained for audit)" btnClass="btn btn-sm btn-warning text-white" icon="bi bi-archive-fill" confirmText="Archive this maternal death case? It is legal medical history and will be retained for audit." />
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="cr-body">
        {{-- Case record --}}
        <div class="card cr-card fade-in-card">
            <div class="card-header">
                <h6 class="cr-card-title">Clinical surveillance record</h6>
            </div>
            <div class="card-body">
                <div class="cr-grid">
                    <div class="cr-item">
                        <div class="cr-label">Patient name</div>
                        <div class="cr-value">{{ $death->patient_name }}</div>
                        <div class="cr-sub">{{ $death->user_id ? 'Enrolled patient' : 'Unlinked / external patient' }}</div>
                    </div>
                    <div class="cr-item">
                        <div class="cr-label">Age at death</div>
                        <div class="cr-value">{{ $death->age_at_death ?? '—' }} years</div>
                    </div>

                    <div class="cr-item">
                        <div class="cr-label">Barangay &amp; purok</div>
                        <div class="cr-value">{{ $death->purok->name ?? '—' }}</div>
                        <div class="cr-sub">{{ $death->barangay }}</div>
                    </div>
                    <div class="cr-item">
                        <div class="cr-label">Date &amp; time of death</div>
                        <div class="cr-value">{{ $death->death_date->format('M j, Y') }}</div>
                        <div class="cr-sub">{{ $death->death_time ? \Carbon\Carbon::parse($death->death_time)->format('g:i A') : 'Time unrecorded' }}</div>
                    </div>

                    <div class="cr-item">
                        <div class="cr-label">Place of death</div>
                        <div class="cr-value">{{ $death->place_of_death_label }}</div>
                    </div>
                    <div class="cr-item">
                        <div class="cr-label">Timing of death</div>
                        <div class="cr-value">{{ $death->death_timing_label }}</div>
                    </div>

                    <div class="cr-item">
                        <div class="cr-label">Primary cause category</div>
                        <div><span class="summary-chip chip-danger rq-chip">{{ $death->cause_category_label }}</span></div>
                    </div>
                    <div class="cr-item">
                        <div class="cr-label">Recorded by</div>
                        <div class="cr-value" style="font-size:0.78rem;">{{ $death->recordedBy->name ?? 'Unknown' }}</div>
                        <div class="cr-sub">{{ $death->created_at->format('M j, Y g:i A') }}</div>
                    </div>

                    <div class="cr-item cr-wide">
                        <div class="cr-label">Clinical cause details</div>
                        <p class="cr-note">{{ $death->cause_of_death }}</p>
                    </div>

                    <div class="cr-item cr-wide">
                        <div class="cr-label">Additional surveillance notes</div>
                        <p class="cr-note cr-note-quiet">{{ $death->notes ?: 'No additional clinical notes submitted.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHO audit panel --}}
        <div class="card cr-card fade-in-card">
            <div class="card-header">
                <h6 class="cr-card-title">CHO case audit review</h6>
            </div>
            <div class="card-body">
                @if($death->reviewed_by_id)
                    <div class="cr-state mb-2">
                        <i class="bi bi-shield-check-fill text-{{ $death->audit_status === 'closed' ? 'success' : 'info' }}" aria-hidden="true"></i>
                        <div class="cr-state-body">
                            <div class="cr-state-title">Audited — {{ ucwords(str_replace('_', ' ', $death->audit_status)) }}</div>
                            <p class="cr-state-sub">Reviewed by the City Health Office under the MDSR standard.</p>
                        </div>
                    </div>

                    <div class="cr-item">
                        <div class="cr-label">Audit notes</div>
                        <p class="cr-note cr-note-quiet">{{ $death->audit_notes ?: 'No notes provided.' }}</p>
                    </div>
                    <div class="cr-item mt-2">
                        <div class="cr-label">Audited by</div>
                        <div class="cr-value" style="font-size:0.78rem;">{{ $death->reviewedBy->name ?? 'Unknown' }}</div>
                        <div class="cr-sub">{{ $death->reviewed_at ? $death->reviewed_at->format('M j, Y g:i A') : '—' }}</div>
                    </div>
                @else
                    <div class="cr-state">
                        <i class="bi bi-hourglass-split" style="color:var(--color-warning-text);" aria-hidden="true"></i>
                        <div class="cr-state-body">
                            <div class="cr-state-title">Pending CHO review</div>
                            <p class="cr-state-sub">Logged, awaiting review and audit confirmation by the City Health Office.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
