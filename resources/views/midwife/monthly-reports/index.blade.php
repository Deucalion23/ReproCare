@extends('midwife.layout')

@section('title', 'Midwife Monthly Reports - Midwife Portal | ReproCare')

@section('midwife-content')
<div class="workspace-stack">
    <div class="page-hero fade-in-card">
        <div class="workspace-toolbar" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Midwife Monthly Reports</div>
                <p class="page-hero-subtitle">Validation queue for reports approved by the BHW president and waiting for midwife action.</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-0">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="metric-grid mb-4" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
        <div class="card p-3 fade-in-card bg-light border-0">
            <div class="text-muted text-xs uppercase tracking-wider mb-1">Reports in Queue</div>
            <div class="fw-800 text-dark text-2xl">{{ $reports->total() }}</div>
        </div>
        <div class="card p-3 fade-in-card bg-light border-0">
            <div class="text-muted text-xs uppercase tracking-wider mb-1">Awaiting Validation</div>
            <div class="fw-800 text-dark text-2xl">{{ $reports->getCollection()->where('submission_status', 'submitted_to_midwife')->count() }}</div>
        </div>
        <div class="card p-3 fade-in-card bg-light border-0">
            <div class="text-muted text-xs uppercase tracking-wider mb-1">Validated</div>
            <div class="fw-800 text-dark text-2xl">{{ $reports->getCollection()->where('submission_status', 'approved_by_midwife')->count() }}</div>
        </div>
        <div class="card p-3 fade-in-card bg-light border-0">
            <div class="text-muted text-xs uppercase tracking-wider mb-1">Returned to President</div>
            <div class="fw-800 text-dark text-2xl">{{ $reports->getCollection()->where('submission_status', 'returned_to_president')->count() }}</div>
        </div>
    </div>

    <div class="card fade-in-card mb-4">
        <div class="card-header bg-transparent py-3">
            <h5 class="mb-0 fw-700 text-dark">Filter Queue
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('midwife.monthly-reports.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label text-xs fw-600 text-muted">Report Type</label>
                    <select name="filter" class="form-select">
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Reports</option>
                        <option value="health_records" {{ $filter === 'health_records' ? 'selected' : '' }}>Health Records</option>
                        <option value="pregnancies" {{ $filter === 'pregnancies' ? 'selected' : '' }}>Pregnancies</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Apply Filter</button>
                    <a href="{{ route('midwife.monthly-reports.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card fade-in-card">
        <div class="card-header bg-transparent py-3">
            <h5 class="mb-0 fw-700 text-dark">Review List
            </h5>
        </div>
        <div class="card-body p-0">
            @if($reports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="px-4">Report Title</th>
                                <th>BHW</th>
                                <th>Period</th>
                                <th>Records</th>
                                <th>Submission Status</th>
                                <th>President Notes</th>
                                <th class="text-end px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-700 text-dark">{{ $report->title }}</div>
                                        @if($report->description)
                                            <div style="font-size:0.75rem; color:var(--text-muted);">{{ \Illuminate\Support\Str::limit($report->description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-600 text-dark">{{ $report->bhw->name ?? 'Unknown' }}</div>
                                        <div style="font-size:0.72rem; color:var(--text-muted);">{{ ucfirst(str_replace('_', ' ', $report->report_type ?? 'report')) }}</div>
                                    </td>
                                    <td><span class="badge bg-info text-white">{{ $report->reportPeriod }}</span></td>
                                    <td>{{ $report->total_records }} records</td>
                                    <td>
                                        @php
                                            $statusChip = match($report->submission_status) {
                                                'approved_by_midwife' => 'bg-success',
                                                'returned_to_president' => 'bg-info',
                                                'rejected', 'needs_revision' => 'bg-danger',
                                                default => 'bg-warning',
                                            };
                                            $statusLabel = match($report->submission_status) {
                                                'approved_by_midwife' => 'Validated',
                                                'returned_to_president' => 'Returned to President',
                                                'rejected', 'needs_revision' => 'Needs Revision',
                                                'approved_by_rhu' => 'Approved by RHU',
                                                'submitted_to_midwife' => 'Awaiting Validation',
                                                default => 'Pending Review',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusChip }} text-white text-xs">{{ $statusLabel }}</span>
                                    </td>
                                    <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $report->president_notes ?? 'No notes' }}</td>
                                    <td class="text-end px-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('midwife.monthly-reports.show', $report->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                            @if($report->submission_status === 'submitted_to_midwife')
                                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#validateModal{{ $report->id }}" title="Validate and forward to RHU"><i class="bi bi-check-lg"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#returnModal{{ $report->id }}" title="Return to BHW President"><i class="bi bi-reply"></i></button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center p-3 border-top">
                    {{ $reports->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-file-earmark-x text-muted" style="font-size:3rem;"></i>
                    <h5 class="mt-3 mb-1 fw-700">No reports found</h5>
                    <p class="text-muted text-xs">Reports approved by the BHW president will appear here when they reach the queue.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@foreach($reports as $report)
    @if($report->submission_status === 'submitted_to_midwife')
        <div class="modal fade report-action-modal" id="validateModal{{ $report->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('midwife.monthly-reports.approve', $report->id) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Validate Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted text-xs mb-3">Validate this report and forward it to the RHU?</p>
                            <label class="form-label text-xs fw-600">Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success text-white">Validate Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade report-action-modal" id="returnModal{{ $report->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('midwife.monthly-reports.return', $report->id) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Return to BHW President</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted text-xs mb-3">Return this report to the BHW president for re-check.</p>
                            <label class="form-label text-xs fw-600 required-label">Reason for return</label>
                            <textarea name="notes" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">Return Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.report-action-modal').forEach(function(modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });
});
</script>
@endpush

@endsection
