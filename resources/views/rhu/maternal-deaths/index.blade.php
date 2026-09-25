@extends('rhu.layout')

@section('title', 'Maternal Death Surveillance - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Maternal Death Records
            </div>
            <p class="page-hero-subtitle">
                Log and monitor maternal mortality cases for clinic reviews.
            </p>
        </div>
        <a href="{{ route('rhu.maternal-deaths.create') }}" class="btn btn-danger">
            <i class="bi bi-plus-lg me-1"></i> Record Maternal Death
        </a>
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
                            <th>Cause Category</th>
                            <th>Audit Status</th>
                            <th class="pe-4 text-end">Actions</th>
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
                                <td>
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
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('rhu.maternal-deaths.show', $death->id) }}" class="btn btn-xs btn-outline-primary" style="font-size:0.75rem;">
                                            <i class="bi bi-eye"></i> Details
                                        </a>
                                        @if($death->audit_status !== 'closed')
                                            <a href="{{ route('rhu.maternal-deaths.edit', $death->id) }}" class="btn btn-xs btn-outline-warning" style="font-size:0.75rem;">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                        @endif
                                    </div>
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
                <h5 class="mt-3">No Maternal Death Records</h5>
                <p class="text-muted text-xs">No maternal mortality events have been logged for this health unit.</p>
            </div>
        @endif
    </div>
</div>

@endsection
