@extends('cho.layout')

@section('title', 'Report File - CHO Portal | ReproCare')

@section('cho-content')
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">{{ $report->title ?? ('Report #' . $report->id) }}</div>
            <p class="page-hero-subtitle" style="font-weight:600;">
                <span class="badge bg-info text-dark">{{ $stats['period'] }}</span>
                <span class="badge {{ $report->submission_status === 'submitted_to_cho' ? 'bg-warning text-dark' : ($report->submission_status === 'received_by_cho' ? 'bg-success' : 'bg-secondary') }}">
                    {{ ucwords(str_replace('_', ' ', $report->submission_status)) }}
                </span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('cho.reports.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to City Reports
            </a>
            @if($report->submission_status === 'submitted_to_cho')
                <form method="POST" action="{{ route('cho.reports.receive', $report->id) }}" class="d-inline" onsubmit="return confirm('Acknowledge receipt of this validated report into City Reports?');">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm text-white"><i class="bi bi-check-lg me-1"></i> Receive</button>
                </form>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card fade-in-card mb-4">
    <div class="card-body">
        <h6 class="mb-3 fw-700 text-dark">Submission Information</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex justify-content-between mb-2 pb-1 border-bottom text-xs">
                    <span class="text-muted">BHW:</span>
                    <span class="fw-bold text-dark">{{ $report->bhw?->name ?? 'Unknown' }} ({{ $report->bhw?->barangay ?? 'N/A' }})</span>
                </div>
                <div class="d-flex justify-content-between mb-2 pb-1 border-bottom text-xs">
                    <span class="text-muted">Type:</span>
                    <span class="text-dark">{{ ucfirst(str_replace('_', ' ', $report->report_type ?? 'report')) }}</span>
                </div>
                <div class="d-flex justify-content-between text-xs">
                    <span class="text-muted">Records:</span>
                    <span class="text-dark">{{ $report->total_records }} ({{ $uniquePatients }} patients)</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between mb-2 pb-1 border-bottom text-xs">
                    <span class="text-muted">Passed to CHO:</span>
                    <span class="text-dark">{{ $stats['submitted_to_cho_at'] ? $stats['submitted_to_cho_at']->format('M d, Y h:i A') : 'Not yet passed' }}</span>
                </div>
                <div class="d-flex justify-content-between text-xs">
                    <span class="text-muted">Received by CHO:</span>
                    <span class="text-dark">{{ $stats['received_by_cho_at'] ? $stats['received_by_cho_at']->format('M d, Y h:i A') : 'Awaiting receipt' }}</span>
                </div>
            </div>
        </div>
        @if($report->description)
            <div class="alert alert-info mt-3 mb-0"><strong>Description:</strong> {{ $report->description }}</div>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card fade-in-card p-3 text-center h-100">
            <div class="text-muted" style="font-size:0.75rem;">Total Records</div>
            <h4 class="mb-0 mt-1">{{ $report->total_records }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card fade-in-card p-3 text-center h-100">
            <div class="text-muted" style="font-size:0.75rem;">Unique Patients</div>
            <h4 class="mb-0 mt-1">{{ $uniquePatients }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card fade-in-card p-3 text-center h-100">
            <div class="text-muted" style="font-size:0.75rem;">High Risk Cases</div>
            <h4 class="mb-0 mt-1">{{ $riskDistribution['high'] }}</h4>
        </div>
    </div>
</div>

<div class="card fade-in-card mb-4">
    <div class="card-header bg-transparent py-3">
        <h5 class="mb-0 fw-700 text-dark">{{ $report->report_type === 'health_records' ? 'Health Records' : 'Pregnancy Registry Entries' }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            @if($report->report_type === 'health_records')
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                    <thead><tr><th class="px-4">Date</th><th>Patient</th><th>Blood Pressure</th><th>Weight</th><th>Risk Level</th></tr></thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td class="px-4">{{ $record->created_at ? $record->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                <td>
                                    <span class="fw-700 text-dark d-block">{{ $record->patient?->name ?? $record->recordedBy?->name ?? 'Unknown' }}</span>
                                    <span class="text-muted">{{ $record->patient?->email ?? '' }}</span>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $record->bp ?? 'N/A' }}</span></td>
                                <td>{{ $record->weight ? $record->weight . ' kg' : 'N/A' }}</td>
                                <td><span class="badge bg-{{ ($record->risk_level ?? '') === 'High' ? 'danger' : (($record->risk_level ?? '') === 'Medium' ? 'warning' : 'success') }}">{{ $record->risk_level ?? 'N/A' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No records in this report.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                    <thead><tr><th class="px-4">Date Logged</th><th>Patient</th><th>LMP</th><th>EDD</th><th>High Risk</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($records as $preg)
                            <tr>
                                <td class="px-4">{{ $preg->created_at ? $preg->created_at->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <span class="fw-700 text-dark d-block">{{ $preg->woman?->name ?? 'Unknown' }}</span>
                                    <span class="text-muted">{{ $preg->woman?->email ?? '' }}</span>
                                </td>
                                <td>{{ $preg->lmp ? $preg->lmp->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $preg->edd ? $preg->edd->format('M d, Y') : 'N/A' }}</td>
                                <td><span class="badge bg-{{ $preg->is_high_risk ? 'danger' : 'success' }}">{{ $preg->is_high_risk ? 'Yes' : 'No' }}</span></td>
                                <td><span class="badge bg-secondary text-white">{{ ucfirst($preg->workflow_status ?? 'recorded') }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">No records in this report.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
        @if($records->hasPages())
            <div class="d-flex justify-content-center p-3 border-top">{{ $records->links() }}</div>
        @endif
    </div>
</div>
@endsection
