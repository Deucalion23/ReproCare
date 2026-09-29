@extends('bhw-president.layout')

@section('title', 'Health Record Review - BHW President Portal | ReproCare')

@push('styles')
<style>
    @media (max-width: 767.98px) {
        .page-hero { padding:0.9rem 1rem !important; border-radius:14px !important; margin-bottom:0.9rem !important; }
        .page-hero .page-hero-title { font-size:1.02rem !important; }
        .page-hero .page-hero-subtitle { font-size:0.74rem !important; }
        .table { font-size:0.76rem !important; }
        .table th, .table td { padding:0.5rem 0.6rem !important; }
        .table .fw-semibold { font-size:0.78rem !important; }
        .table .small, .table small { font-size:0.66rem !important; }
        .table .badge { font-size:0.62rem !important; }
        .table .btn { font-size:0.7rem !important; padding:0.35rem 0.65rem !important; }
        .card-footer { font-size:0.74rem !important; }
    }
</style>
@endpush

@section('bhw-president-content')
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Health Record Review</div>
            <p class="page-hero-subtitle">Review BHW-submitted records, update details, and pass records to the midwife.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card fade-in-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-cards-mobile">
                <thead>
                    <tr>
                        <th class="px-4 py-3">Patient</th>
                        <th class="px-4 py-3">BHW</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Vitals</th>
                        <th class="px-4 py-3">Workflow</th>
                        <th class="px-4 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($healthRecords as $record)
                        <tr>
                            <td class="px-4 py-3 no-card-label" data-label="Patient">
                                <div class="fw-semibold">{{ $record->patient_name }}</div>
                                <div class="small text-muted">{{ $record->patient_barangay ?? 'No barangay' }}</div>
                            </td>
                            <td class="px-4 py-3" data-label="BHW">{{ optional($record->recordedBy)->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-3" data-label="Date" style="white-space:nowrap;">{{ $record->created_at->format('M j, Y g:i A') }}</td>
                            <td class="px-4 py-3" data-label="Vitals">
                                BP: {{ $record->bp ?? '—' }}<br>
                                Weight: {{ $record->weight ?? '—' }} kg<br>
                                Risk: {{ $record->risk_level ?? '—' }}
                            </td>
                            <td class="px-4 py-3" data-label="Workflow">
                                <span class="badge bg-{{ $record->workflow_status === 'submitted_to_midwife' ? 'success' : ($record->workflow_status === 'accepted_by_midwife' ? 'primary' : 'warning') }}">
                                    {{ str_replace('_', ' ', $record->workflow_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end no-card-label" data-label="Actions">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                    @if($record->workflow_status !== 'submitted_to_midwife' && $record->workflow_status !== 'accepted_by_midwife')
                                        <form action="{{ route('bhw-president.health-records.pass', $record->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-send-check me-1"></i>Pass to Midwife
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No BHW-submitted health records available for review.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-transparent">
        {{ $healthRecords->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
