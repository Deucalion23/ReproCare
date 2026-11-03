@extends('rhu.layout')

@section('title', 'Near-Miss Complications & Morbidities - RHU Portal | ReproCare')

@push('styles')
@include('includes.women-directory-styles')
@include('includes.report-queue-styles')
@endpush

@section('rhu-content')

<div class="workspace-stack rq-stack">

    @include('includes.report-queue-head', [
        'title' => 'Near-Miss Complications & Morbidities',
        'subtitle' => 'Surveillance of life-threatening maternal near-miss complications.',
        'icon' => 'bi-heart-pulse-fill',
        'action_href' => route('rhu.morbidities.create'),
        'action_label' => 'Record Near-Miss Event',
        'action_icon' => 'bi-plus-lg',
    ])

    @include('includes.list-toolbar', [
        'action' => route('rhu.morbidities.index'),
        'tabs' => $tabs,
        'active' => $status,
        'search' => $search,
        'placeholder' => 'Patient, barangay or complication...',
        'label' => 'Review status',
    ])

    <div class="card rq-wrap fade-in-card">
        <div class="card-body p-0">
            @if($morbidities->isNotEmpty())
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Complication</th>
                                <th>Date &amp; place</th>
                                <th>Outcome</th>
                                <th>Review</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($morbidities as $morb)
                                <tr>
                                    <td>
                                        <div class="rq-truncate rq-title" title="{{ $morb->patient_name }}">{{ $morb->patient_name }}</div>
                                        <div class="rq-soft">{{ $morb->barangay ?: '—' }} &bull; {{ $morb->user_id ? 'Enrolled' : 'Unlinked' }}</div>
                                    </td>
                                    <td>
                                        <span class="summary-chip chip-danger rq-status">{{ $morb->complication_type_label }}</span>
                                    </td>
                                    <td>
                                        <div class="rq-num" style="font-weight:700;">{{ $morb->event_date->format('M j, Y') }}</div>
                                        <div class="rq-soft">{{ $morb->place_of_event_label }}</div>
                                    </td>
                                    <td>
                                        <span class="summary-chip chip-{{ $morb->outcome === 'died' ? 'danger' : ($morb->outcome === 'transferred_to_higher_facility' ? 'warning' : 'success') }} rq-status">
                                            {{ $morb->outcome_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="summary-chip chip-{{ $morb->review_status === 'reviewed' ? 'success' : ($morb->review_status === 'pending' ? 'info' : 'warning') }} rq-status">
                                            {{ ucwords(str_replace('_', ' ', $morb->review_status)) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="rq-actions">
                                            <a href="{{ route('rhu.morbidities.show', $morb->id) }}" class="btn btn-sm btn-outline-primary"
                                               title="View case" aria-label="View case for {{ $morb->patient_name }}">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </a>
                                            @if($morb->review_status !== 'reviewed')
                                                <a href="{{ route('rhu.morbidities.edit', $morb->id) }}" class="btn btn-sm btn-outline-warning"
                                                   title="Edit case" aria-label="Edit case for {{ $morb->patient_name }}">
                                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                                </a>
                                                <x-archive-form :action="route('rhu.morbidities.destroy', $morb->id)" label="" title="Archive case (retained for audit)" btnClass="btn btn-sm btn-outline-warning" icon="bi bi-archive" confirmText="Archive this near-miss case? It will be retained for FHSIS/MNCHN audit and can be restored." />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($morbidities->hasPages())
                    <div class="rq-foot">{{ $morbidities->links('vendor.pagination.arrows-only') }}</div>
                @endif
            @else
                <div class="rq-empty">
                    <i class="bi bi-heart-pulse" style="color:var(--color-text-muted);" aria-hidden="true"></i>
                    <h6 class="fw-bold mt-2 mb-1" style="color:var(--color-text);">No morbidity cases logged</h6>
                    <p class="text-muted mb-0" style="font-size:0.82rem;">
                        {{ $search !== '' ? 'No near-miss event matched your search.' : 'No near-miss complications or severe maternal morbidity events have been logged yet.' }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
