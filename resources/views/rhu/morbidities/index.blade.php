@extends('rhu.layout')

@section('title', 'Near-Miss Complications & Morbidities - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Near-Miss Complications &amp; Morbidities</div>
            <p class="page-hero-subtitle">
                <i class="bi bi-heart-pulse-fill me-1"></i> Surveillance of life-threatening maternal near-miss complications.
            </p>
        </div>
        <a href="{{ route('rhu.morbidities.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Record Near-Miss Event
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 fade-in-card" role="alert" style="border-radius:12px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card fade-in-card">
    <div class="card-header bg-transparent py-3">
        <h5 class="mb-0 fw-700 text-dark">Surveillance Records
        </h5>
    </div>
    <div class="card-body p-0">
        @if($morbidities->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="px-4">Patient</th>
                            <th>Complication</th>
                            <th>Date &amp; Place</th>
                            <th>Outcome</th>
                            <th>Audit Review</th>
                            <th class="text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($morbidities as $morb)
                            <tr>
                                <td class="px-4">
                                    <span class="fw-700 text-dark d-block">{{ $morb->patient_name }}</span>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">
                                        {{ $morb->barangay }} &bull; {{ $morb->user_id ? 'Enrolled' : 'Unlinked' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-danger-soft text-danger fw-600">
                                        {{ str_replace('_', ' ', ucfirst($morb->complication_type)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="d-block fw-600 text-xs" style="color:var(--text);">
                                        {{ $morb->event_date->format('M j, Y') }}
                                    </span>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">
                                        {{ str_replace('_', ' ', ucfirst($morb->place_of_event)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $morb->outcome === 'died' ? 'danger' : ($morb->outcome === 'transferred_to_higher_facility' ? 'warning' : 'success') }} text-white text-xs">
                                        {{ str_replace('_', ' ', ucfirst($morb->outcome)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $morb->review_status === 'reviewed' ? 'success' : ($morb->review_status === 'pending' ? 'secondary' : 'info') }} text-white text-xs">
                                        {{ ucfirst($morb->review_status) }}
                                    </span>
                                </td>
                                <td class="text-end px-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('rhu.morbidities.show', $morb->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($morb->review_status !== 'reviewed')
                                            <a href="{{ route('rhu.morbidities.edit', $morb->id) }}" class="btn btn-sm btn-icon btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <x-archive-form :action="route('rhu.morbidities.destroy', $morb->id)" label="" title="Archive case (retained for audit)" btnClass="btn btn-sm btn-icon btn-outline-warning" icon="bi bi-archive" confirmText="Archive this near-miss case? It will be retained for audit and can be restored." />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <div class="text-xs text-muted">
                    Showing {{ $morbidities->firstItem() }} to {{ $morbidities->lastItem() }} of {{ $morbidities->total() }} entries
                </div>
                <div>
                    {{ $morbidities->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-heart-pulse" style="font-size:3rem; color:var(--text-muted);"></i>
                <h5 class="mt-3 mb-1 fw-700">No Morbidity Cases Logged</h5>
                <p class="text-muted text-xs px-4">No near-miss complications or severe maternal morbidity events have been logged yet.</p>
            </div>
        @endif
    </div>
</div>

@endsection
