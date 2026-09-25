@extends('cho.layout')

@section('title', 'Maternal Death Audits - CHO Portal | ReproCare')

@section('cho-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Maternal Death Cases
            </div>
            <p class="page-hero-subtitle" style="font-weight:600;">Conduct clinical reviews and maternal death audits (DOH MDSR standard).</p>
        </div>
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
        @if($deaths->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Patient Name</th>
                            <th>Age</th>
                            <th>Date of Death</th>
                            <th>Place of Death</th>
                            <th>Cause of Death</th>
                            <th>Audit Status</th>
                            <th>Recorded By</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deaths as $death)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-700" style="color:var(--text);">{{ $death->patient_name }}</span>
                                    <div style="font-size:0.75rem; color:var(--text-muted);">{{ $death->barangay }}</div>
                                </td>
                                <td>
                                    <span style="font-size:0.875rem;">{{ $death->age_at_death ?? 'N/A' }}</span>
                                </td>
                                <td style="font-size:0.875rem;">
                                    {{ $death->death_date->format('M j, Y') }}
                                </td>
                                <td style="font-size:0.875rem;">
                                    {{ str_replace('_', ' ', ucfirst($death->place_of_death)) }}
                                </td>
                                <td>
                                    <span class="badge bg-danger text-white text-xs">
                                        {{ str_replace('_', ' ', ucfirst($death->cause_category)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $death->audit_status === 'closed' ? 'success' : ($death->audit_status === 'reviewed' ? 'info' : 'warning') }} text-white text-xs">
                                        {{ ucfirst($death->audit_status) }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size:0.875rem;">{{ $death->recordedBy->name ?? 'Unknown' }}</span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('cho.maternal-deaths.show', $death->id) }}" class="btn btn-xs btn-view text-white py-1 px-3" style="font-size:0.75rem; border-radius:8px;">
                                        Audit Case
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($deaths->hasPages())
                <div class="card-footer bg-transparent border-top">
                    {{ $deaths->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-journal-x" style="font-size:3rem; color:var(--text-muted);"></i>
                <h5 class="mt-3">No Maternal Death Cases Found</h5>
                <p class="text-muted text-xs">There are no recorded maternal mortality cases in the system.</p>
            </div>
        @endif
    </div>
</div>

@endsection
