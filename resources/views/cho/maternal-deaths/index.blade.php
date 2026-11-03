@extends('cho.layout')

@section('title', 'Maternal Death Audits - CHO Portal | ReproCare')

@push('styles')
@include('includes.women-directory-styles')
@include('includes.report-queue-styles')
@endpush

@section('cho-content')

<div class="workspace-stack rq-stack">

    @include('includes.report-queue-head', [
        'title' => 'Maternal Death Cases',
        'subtitle' => 'Conduct clinical reviews and maternal death audits (DOH MDSR standard).',
        'icon' => 'bi-clipboard2-pulse-fill',
        'chips' => [
            ['label' => ' cases logged', 'value' => $deaths->total(), 'tone' => 'primary'],
        ],
    ])

    <div class="card rq-wrap fade-in-card">
        <div class="card-body p-0">
            @if($deaths->isNotEmpty())
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Age</th>
                                <th>Date of death</th>
                                <th>Place</th>
                                <th>Cause</th>
                                <th>Audit</th>
                                <th>Recorded by</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deaths as $death)
                                <tr>
                                    <td>
                                        <div class="rq-truncate rq-title" title="{{ $death->patient_name }}">{{ $death->patient_name }}</div>
                                        <div class="rq-soft">{{ $death->barangay ?: '—' }}</div>
                                    </td>
                                    <td class="rq-num">{{ $death->age_at_death ?? '—' }}</td>
                                    <td class="rq-num">{{ $death->death_date->format('M j, Y') }}</td>
                                    <td>
                                        <span class="rq-truncate" style="max-width:130px;" title="{{ $death->place_of_death_label }}">
                                            {{ $death->place_of_death_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="summary-chip chip-danger rq-status">{{ $death->cause_category_label }}</span>
                                    </td>
                                    <td>
                                        <span class="summary-chip chip-{{ $death->audit_status === 'closed' ? 'success' : ($death->audit_status === 'reviewed' ? 'info' : 'warning') }} rq-status">
                                            {{ ucwords(str_replace('_', ' ', $death->audit_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="rq-truncate" style="max-width:150px;" title="{{ $death->recordedBy->name ?? 'Unknown' }}">
                                            {{ $death->recordedBy->name ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="rq-actions">
                                            <a href="{{ route('cho.maternal-deaths.show', $death->id) }}" class="btn btn-sm btn-outline-primary"
                                               title="Audit case" aria-label="Audit case for {{ $death->patient_name }}">
                                                <i class="bi bi-search" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($deaths->hasPages())
                    <div class="rq-foot">{{ $deaths->links('vendor.pagination.arrows-only') }}</div>
                @endif
            @else
                <div class="rq-empty">
                    <i class="bi bi-journal-x" style="color:var(--color-text-muted);" aria-hidden="true"></i>
                    <h6 class="fw-bold mt-2 mb-1" style="color:var(--color-text);">No maternal death cases</h6>
                    <p class="text-muted mb-0" style="font-size:0.82rem;">There are no recorded maternal mortality cases in the system.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
