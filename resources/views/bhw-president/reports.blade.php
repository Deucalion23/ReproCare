@extends('bhw-president.layout')

@use('Carbon\Carbon')

@section('title', 'Reports - BHW President Portal | ReproCare')

@push('styles')
<style>
    .report-actions-col {
        width:160px;
        min-width:160px;
    }
    .report-table-actions {
        display:inline-flex;
        align-items:center;
        justify-content:flex-end;
        gap:0.55rem;
        flex-wrap:nowrap;
    }
    .report-table-actions form {
        margin:0;
    }
    .report-action-btn {
        width:38px;
        height:38px;
        padding:0;
        border-radius:12px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
    }
    @media (max-width: 768px) {
        .report-actions-col {
            min-width:146px;
        }
    }
</style>
@endpush

@section('bhw-president-content')
@php
    $healthReports = $reports->getCollection()->filter(fn($report) => $report->report_type === 'health_records' || !$report->report_type);
    $pregnancyReports = $reports->getCollection()->filter(fn($report) => $report->report_type === 'pregnancies');
@endphp

<div class="workspace-stack">
    <div class="page-hero fade-in-card">
        <div class="workspace-toolbar" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Monthly Reports</div>
                <p class="page-hero-subtitle">Review submissions from BHWs, add notes, and forward approved reports to the midwife.</p>
            </div>
        </div>
    </div>

    <div class="metric-grid">
        <div class="metric-card metric-card-primary fade-in-card">
            <i class="bi bi-folder2-open metric-card-icon"></i>
            <div class="metric-card-label">Reports in View</div>
            <div class="metric-card-value">{{ $reports->total() }}</div>
            <div class="metric-card-note">Reports currently loaded in the moderation queue.</div>
        </div>
        <div class="metric-card metric-card-cyan fade-in-card">
            <i class="bi bi-file-medical-fill metric-card-icon"></i>
            <div class="metric-card-label">Health Reports</div>
            <div class="metric-card-value">{{ $healthReports->count() }}</div>
            <div class="metric-card-note">Submissions focused on health records.</div>
        </div>
        <div class="metric-card metric-card-rose fade-in-card">
            <i class="bi bi-heart-fill metric-card-icon"></i>
            <div class="metric-card-label">Pregnancy Reports</div>
            <div class="metric-card-value">{{ $pregnancyReports->count() }}</div>
            <div class="metric-card-note">Submissions focused on pregnancy monitoring.</div>
        </div>
        <div class="metric-card metric-card-amber fade-in-card">
            <i class="bi bi-hourglass-split metric-card-icon"></i>
            <div class="metric-card-label">Need Review</div>
            <div class="metric-card-value">{{ $reports->getCollection()->where('submission_status', 'submitted_to_president')->count() }}</div>
            <div class="metric-card-note">Reports waiting for your approval or rejection.</div>
        </div>
    </div>

    <div class="workspace-panel fade-in-card">
        <div class="workspace-panel-header">
            <h2 class="workspace-panel-title">Filter Queue</h2>
            <p class="workspace-panel-subtitle">Focus on health reports or pregnancy reports only.</p>
        </div>
        <div class="workspace-panel-body">
            <form method="GET" action="{{ route('bhw-president.reports.index') }}" class="workspace-filter-grid">
                <div class="span-4">
                    <label class="form-label">Report Type</label>
                    <select name="filter" class="form-select">
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Reports</option>
                        <option value="health_records" {{ $filter === 'health_records' ? 'selected' : '' }}>Health Records</option>
                        <option value="pregnancies" {{ $filter === 'pregnancies' ? 'selected' : '' }}>Pregnancies</option>
                    </select>
                </div>
                <div class="span-8 workspace-filter-actions">
                    <button type="submit" class="btn btn-filter"><i class="bi bi-funnel me-1"></i>Apply Filter</button>
                    <a href="{{ route('bhw-president.reports.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="split-panels">
        @foreach([
            ['title' => 'Health Record Reports', 'icon' => 'bi-file-medical-fill', 'items' => $healthReports],
            ['title' => 'Pregnancy Reports', 'icon' => 'bi-heart-fill', 'items' => $pregnancyReports],
        ] as $section)
            <div class="workspace-panel fade-in-card">
                <div class="workspace-panel-header">
                    <h2 class="workspace-panel-title">{{ $section['title'] }}</h2>
                </div>
                <div class="workspace-panel-body pt-3">
                    @if($section['items']->count() > 0)
                        <div class="modern-table-wrap">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Report</th>
                                        <th>BHW</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>Submission</th>
                                        <th class="text-end report-actions-col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($section['items'] as $report)
                                        <tr>
                                            <td>
                                                <div class="table-title">{{ $report->title }}</div>
                                                <div class="table-subtitle">{{ \Illuminate\Support\Str::limit($report->description, 50) }}</div>
                                            </td>
                                            <td>{{ optional($report->bhw)->name ?? 'Unknown' }}</td>
                                            <td>{{ Carbon::create()->month($report->report_month)->format('F') }} {{ $report->report_year }}</td>
                                            <td><span class="summary-chip {{ $report->status === 'completed' ? 'chip-success' : 'chip-warning' }}">{{ ucfirst($report->status) }}</span></td>
                                            <td><span class="summary-chip chip-primary">{{ ucwords(str_replace('_', ' ', $report->submission_status)) }}</span></td>
                                            <td class="text-end report-actions-col">
                                                <div class="report-table-actions">
                                                    <a href="{{ route('bhw-president.reports.show', $report->id) }}" class="btn btn-sm btn-outline-primary report-action-btn" title="View report"><i class="bi bi-eye"></i></a>
                                                    @if($report->submission_status === 'submitted_to_president')
                                                        <button type="button" class="btn btn-sm btn-outline-success report-action-btn" data-bs-toggle="modal" data-bs-target="#approveModal{{ $report->id }}" title="Approve report"><i class="bi bi-check-lg"></i></button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger report-action-btn" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $report->id }}" title="Reject report"><i class="bi bi-x-lg"></i></button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state-panel">
                            <i class="bi {{ $section['icon'] }}"></i>
                            <h5>No reports in this group</h5>
                            <p>Submitted reports will appear here once BHWs finish their monthly summaries.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="workspace-panel fade-in-card">
        <div class="workspace-panel-header">
            <h2 class="workspace-panel-title">All Reports</h2>
            <p class="workspace-panel-subtitle">Full list with quick access to review and archive actions.</p>
        </div>
        <div class="workspace-panel-body pt-3">
            @if($reports->count() > 0)
                <div class="modern-table-wrap">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Report Title</th>
                                <th>Type</th>
                                <th>BHW</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end report-actions-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr>
                                    <td>
                                        <div class="table-title">{{ $report->title }}</div>
                                        @if($report->description)
                                            <div class="table-subtitle">{{ \Illuminate\Support\Str::limit($report->description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td><span class="summary-chip {{ $report->report_type === 'pregnancies' ? 'chip-info' : 'chip-primary' }}">{{ $report->report_type === 'pregnancies' ? 'Pregnancies' : 'Health Records' }}</span></td>
                                    <td>{{ optional($report->bhw)->name ?? 'Unknown' }}</td>
                                    <td>{{ Carbon::create()->month($report->report_month)->format('F') }} {{ $report->report_year }}</td>
                                    <td><span class="summary-chip {{ $report->status === 'completed' ? 'chip-success' : 'chip-warning' }}">{{ ucfirst($report->status) }}</span></td>
                                    <td>{{ $report->created_at->format('M j, Y') }}</td>
                                    <td class="text-end report-actions-col">
                                        <div class="report-table-actions">
                                            <a href="{{ route('bhw-president.reports.show', $report->id) }}" class="btn btn-sm btn-outline-primary report-action-btn" title="View report"><i class="bi bi-eye"></i></a>
                                            <form action="{{ route('bhw-president.reports.delete', $report->id) }}" method="POST" onsubmit="return confirm('Archive this report? It will be hidden but not permanently deleted.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary report-action-btn" title="Archive report"><i class="bi bi-archive"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center pt-4">
                    {{ $reports->links() }}
                </div>
            @else
                <div class="empty-state-panel">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <h3>No reports yet</h3>
                    <p>No monthly reports have been submitted by BHWs in the current view.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@foreach($reports as $report)
    @if($report->submission_status === 'submitted_to_president')
        <div class="modal fade report-action-modal" id="approveModal{{ $report->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('bhw-president.reports.approve', $report->id) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Approve Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Approve this report and forward it to the midwife?</p>
                            <label class="form-label">Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Approve and Forward</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade report-action-modal" id="rejectModal{{ $report->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('bhw-president.reports.reject', $report->id) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Reject Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Reject this report and return it to the BHW.</p>
                            <label class="form-label">Reason for rejection</label>
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
