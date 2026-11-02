@extends('rhu.layout')

@section('title', 'Midwife Monthly Reports - RHU Portal | ReproCare')

@push('styles')
@include('includes.women-directory-styles')
@include('includes.report-queue-styles')
@endpush

@section('rhu-content')
<div class="workspace-stack rq-stack">
    @include('includes.report-queue-head', [
        'title' => 'Midwife Monthly Reports',
        'subtitle' => 'Final review queue for reports validated by the midwife and waiting for RHU Admin action.',
        'chips' => [
            ['label' => 'in queue', 'value' => $summary['total'], 'tone' => 'primary'],
            ['label' => 'pending', 'value' => $summary['pending'], 'tone' => 'warning'],
            ['label' => 'approved', 'value' => $summary['approved'], 'tone' => 'success'],
            ['label' => 'rejected', 'value' => $summary['rejected'], 'tone' => 'danger'],
        ],
    ])

    @include('includes.list-toolbar', [
        'action' => route('rhu.bhw-reports.index'),
        'tabs' => $tabs,
        'param' => 'filter',
        'active' => $filter,
        'search' => $search,
        'placeholder' => 'BHW or report title...',
        'label' => 'Report type',
    ])

    <div class="card rq-wrap fade-in-card">
        <div class="card-body p-0">
            @if($reports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Report</th>
                                <th>BHW</th>
                                <th>Period</th>
                                <th class="text-end">Records</th>
                                <th>Status</th>
                                <th>President Notes</th>
                                <th>Midwife Notes</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                @php
                                    $isPregnancy = str_contains((string) $report->report_type, 'pregnan');
                                    $statusTone = match($report->submission_status) {
                                        'approved_by_rhu', 'submitted_to_cho', 'received_by_cho' => 'success',
                                        'approved_by_midwife' => 'primary',
                                        'returned_to_president' => 'info',
                                        'rejected', 'needs_revision' => 'danger',
                                        default => 'warning',
                                    };
                                    $statusLabel = match($report->submission_status) {
                                        'approved_by_rhu' => 'Approved',
                                        'approved_by_midwife' => 'Validated',
                                        'returned_to_president' => 'Returned',
                                        'submitted_to_cho' => 'Passed to CHO',
                                        'received_by_cho' => 'Received by CHO',
                                        'rejected', 'needs_revision' => 'Needs Revision',
                                        default => 'Pending',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="rq-cell">
                                            <span class="rq-type{{ $isPregnancy ? ' rq-type-pregnancy' : '' }}"
                                                  title="{{ $isPregnancy ? 'Pregnancy report' : 'Health record report' }}">
                                                <i class="bi {{ $isPregnancy ? 'bi-heart-fill' : 'bi-clipboard2-pulse-fill' }}" aria-hidden="true"></i>
                                            </span>
                                            <span class="rq-truncate rq-title"
                                                  title="{{ $report->title }}{{ $report->description ? ' — ' . $report->description : '' }}">{{ $report->title }}</span>
                                        </div>
                                    </td>
                                    <td><span class="rq-truncate rq-w-name" title="{{ $report->bhw->name ?? 'Unknown' }}">{{ $report->bhw->name ?? 'Unknown' }}</span></td>
                                    <td><span class="rq-soft" title="{{ $report->reportPeriod }}">{{ $report->report_period_short }}</span></td>
                                    <td class="text-end rq-num">{{ number_format($report->total_records) }}</td>
                                    <td><span class="summary-chip chip-{{ $statusTone }} rq-status">{{ $statusLabel }}</span></td>
                                    <td><span class="rq-truncate rq-w-notes rq-soft" title="{{ $report->president_notes }}">{{ $report->president_notes ?: '—' }}</span></td>
                                    <td><span class="rq-truncate rq-w-notes rq-soft" title="{{ $report->midwife_notes }}">{{ $report->midwife_notes ?: '—' }}</span></td>
                                    <td>
                                        <div class="rq-actions">
                                            <a href="{{ route('rhu.bhw-reports.show', $report->id) }}" class="btn btn-sm btn-outline-primary" title="Open report"><i class="bi bi-eye"></i></a>
                                            @if($report->submission_status === 'approved_by_midwife')
                                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $report->id }}" title="Approve report"><i class="bi bi-check-lg"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $report->id }}" title="Reject report"><i class="bi bi-x-lg"></i></button>
                                            @endif
                                            @if($report->submission_status === 'approved_by_rhu')
                                                <form method="POST" action="{{ route('rhu.bhw-reports.send-to-cho', $report->id) }}" class="d-inline" onsubmit="return confirm('Pass this validated report to CHO City Reports?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Pass to CHO City Reports"><i class="bi bi-send"></i></button>
                                                </form>
                                            @endif
                                            @if(in_array($report->submission_status, ['submitted_to_cho', 'received_by_cho'], true))
                                                <span class="summary-chip chip-success rq-status" title="This report is with the CHO">CHO</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- One-page queues render no pager, and an empty bordered strip
                     would just be another band of dead space under the rows. --}}
                @if($reports->hasPages())
                    <div class="rq-foot">
                        {{ $reports->links() }}
                    </div>
                @endif
            @else
                <div class="rq-empty">
                    <i class="bi bi-file-earmark-x text-muted"></i>
                    <h6 class="mt-2 mb-1 fw-700">No reports found</h6>
                    <p class="rq-soft mb-0">Reports validated by the midwife will appear here when they reach the queue.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@foreach($reports as $report)
    @if($report->submission_status === 'approved_by_midwife')
        <div class="modal fade report-action-modal" id="approveModal{{ $report->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('rhu.bhw-reports.approve', $report->id) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Approve Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted text-xs mb-3">Approve this report and finalize its workflow?</p>
                            <label class="form-label text-xs fw-600">Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success text-white">Approve Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade report-action-modal" id="rejectModal{{ $report->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('rhu.bhw-reports.reject', $report->id) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Reject Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted text-xs mb-3">Reject this report and return it to the BHW with comments.</p>
                            <label class="form-label text-xs fw-600 required-label">Reason for rejection</label>
                            <textarea name="notes" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Reject Report</button>
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
