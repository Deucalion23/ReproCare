@extends('bhw-president.layout')

@section('title', 'Health Record Review - BHW President Portal | ReproCare')

@section('bhw-president-content')
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Health Record Review</div>
            <p class="page-hero-subtitle">Review BHW-submitted records, update details, message the assigned BHW, and pass records to the midwife.</p>
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
            <table class="table table-hover align-middle mb-0">
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
                            <td class="px-4 py-3">
                                <div class="fw-semibold">{{ $record->patient_name }}</div>
                                <div class="small text-muted">{{ $record->patient_barangay ?? 'No barangay' }}</div>
                            </td>
                            <td class="px-4 py-3">{{ optional($record->recordedBy)->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-3">{{ $record->created_at->format('M j, Y g:i A') }}</td>
                            <td class="px-4 py-3">
                                BP: {{ $record->bp ?? '—' }}<br>
                                Weight: {{ $record->weight ?? '—' }} kg<br>
                                Risk: {{ $record->risk_level ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge bg-{{ $record->workflow_status === 'submitted_to_midwife' ? 'success' : ($record->workflow_status === 'accepted_by_midwife' ? 'primary' : 'warning') }}">
                                    {{ str_replace('_', ' ', $record->workflow_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                    @if($record->recordedBy)
                                        <a href="{{ route('bhw-president.messages.create', ['to' => $record->recordedBy->id, 'role' => 'bhw']) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-chat-text me-1"></i>Message Recorder
                                        </a>
                                    @endif
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
