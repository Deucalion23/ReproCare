@extends('cho.layout')

@section('title', 'Maternal Death Case Audit - CHO Portal | ReproCare')

@push('styles')
@include('includes.report-queue-styles')
@include('includes.case-record-styles')
@endpush

@section('cho-content')

<div class="workspace-stack rq-stack">

    <div class="rq-bar fade-in-card">
        <div class="rq-bar-id">
            <div class="rq-bar-title"><i class="bi bi-clipboard2-pulse-fill" aria-hidden="true"></i>Maternal Death Case Audit</div>
            <p class="rq-bar-subtitle">Review clinical factors and register the surveillance review (DOH MDSR).</p>
        </div>

        <div class="rq-chips" aria-label="Case status">
            <span class="summary-chip chip-{{ $death->audit_status === 'closed' ? 'success' : ($death->audit_status === 'reviewed' ? 'info' : 'warning') }} rq-chip">
                Audit: {{ ucwords(str_replace('_', ' ', $death->audit_status)) }}
            </span>
        </div>

        <a href="{{ route('cho.maternal-deaths.index') }}" class="rq-action">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>Back to cases
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
            <ul class="mb-0" style="font-size:0.78rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
                        <div class="cr-sub">{{ $death->user_id ? 'Enrolled woman' : 'Unlinked / external patient' }}</div>
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

        {{-- Audit form --}}
        <div class="card cr-card fade-in-card">
            <div class="card-header">
                <h6 class="cr-card-title">CHO case audit review</h6>
            </div>
            <div class="card-body">
                @if($death->audit_status !== 'closed')
                    <form method="POST" action="{{ route('cho.maternal-deaths.audit', $death->id) }}" class="cr-form">
                        @csrf
                        <div>
                            <label class="form-label required-label" for="audit_status">Surveillance status</label>
                            <select id="audit_status" name="audit_status" class="form-select" required>
                                <option value="reviewed" {{ $death->audit_status === 'reviewed' ? 'selected' : '' }}>Reviewed (under active investigation)</option>
                                <option value="closed" {{ $death->audit_status === 'closed' ? 'selected' : '' }}>Closed (surveillance complete)</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label required-label" for="audit_notes">Audit investigation notes</label>
                            <textarea id="audit_notes" name="audit_notes" rows="5" class="form-control"
                                      placeholder="Case notes, clinic review outcome, and surveillance conclusions." required>{{ $death->audit_notes }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle me-1" aria-hidden="true"></i>Submit audit verification
                        </button>
                    </form>
                @else
                    <div class="cr-state mb-2">
                        <i class="bi bi-shield-check-fill" style="color:var(--color-success-text);" aria-hidden="true"></i>
                        <div class="cr-state-body">
                            <div class="cr-state-title">Audit closed</div>
                            <p class="cr-state-sub">Review complete and surveillance officially closed.</p>
                        </div>
                    </div>

                    <div class="cr-item">
                        <div class="cr-label">Audit notes</div>
                        <p class="cr-note cr-note-quiet">{{ $death->audit_notes ?: '—' }}</p>
                    </div>
                    <div class="cr-item mt-2">
                        <div class="cr-label">Audited by</div>
                        <div class="cr-value" style="font-size:0.78rem;">{{ $death->reviewedBy->name ?? 'Unknown' }}</div>
                        <div class="cr-sub">{{ $death->reviewed_at ? $death->reviewed_at->format('M j, Y g:i A') : '—' }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
