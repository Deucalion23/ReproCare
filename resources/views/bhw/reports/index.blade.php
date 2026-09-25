@extends('bhw.layout')

@section('title', 'Monthly Reports - BHW Portal')

@push('styles')
<style>
    .report-actions-col {
        width:170px;
        min-width:170px;
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
            min-width:154px;
        }
    }
</style>
@endpush

@section('bhw-content')
@php
    $healthReports = $reports->getCollection()->filter(fn($report) => $report->report_type === 'health_records' || !$report->report_type);
    $pregnancyReports = $reports->getCollection()->filter(fn($report) => $report->report_type === 'pregnancies');
    $thisMonthCount = $reports->getCollection()->filter(fn($report) => $report->report_month == now()->month && $report->report_year == now()->year)->count();
    $printedCount = $reports->getCollection()->whereNotNull('printed_at')->count();
@endphp

<div class="workspace-stack">
    <div class="page-hero fade-in-card">
        <div class="workspace-toolbar" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Monthly Reports</div>
                <p class="page-hero-subtitle">Create, track, and submit your health records and pregnancy reports for review.</p>
            </div>
            <a href="{{ route('bhw.reports.create') }}" class="btn-hero-primary"><i class="bi bi-plus-circle-fill"></i>Create Report</a>
        </div>
    </div>

    <div class="metric-grid">
        <div class="metric-card metric-card-primary fade-in-card">
            <i class="bi bi-folder2-open metric-card-icon"></i>
            <div class="metric-card-label">Total Reports</div>
            <div class="metric-card-value">{{ $reports->total() }}</div>
            <div class="metric-card-note">All reports inside the current filter set.</div>
        </div>
        <div class="metric-card metric-card-cyan fade-in-card">
            <i class="bi bi-calendar-check-fill metric-card-icon"></i>
            <div class="metric-card-label">This Month</div>
            <div class="metric-card-value">{{ $thisMonthCount }}</div>
            <div class="metric-card-note">Reports created for {{ now()->format('F Y') }}.</div>
        </div>
        <div class="metric-card metric-card-green fade-in-card">
            <i class="bi bi-printer-fill metric-card-icon"></i>
            <div class="metric-card-label">Printed</div>
            <div class="metric-card-value">{{ $printedCount }}</div>
            <div class="metric-card-note">Reports already opened for printable output.</div>
        </div>
        <div class="metric-card metric-card-amber fade-in-card">
            <i class="bi bi-send-check-fill metric-card-icon"></i>
            <div class="metric-card-label">Draft Queue</div>
            <div class="metric-card-value">{{ $reports->getCollection()->where('submission_status', 'draft')->count() }}</div>
            <div class="metric-card-note">Reports still waiting to be submitted to the president.</div>
        </div>
    </div>

    <div class="workspace-panel fade-in-card">
        <div class="workspace-panel-header">
            <h2 class="workspace-panel-title">Filter Reports</h2>
            <p class="workspace-panel-subtitle">Sort by report type, date, and preparation status.</p>
        </div>
        <div class="workspace-panel-body">
            <form method="GET" action="{{ route('bhw.reports.index') }}" class="workspace-filter-grid">
                <div class="span-3">
                    <label class="form-label">Report Type</label>
                    <select name="filter" class="form-select">
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All</option>
                        <option value="health_records" {{ $filter === 'health_records' ? 'selected' : '' }}>Health Records</option>
                        <option value="pregnancies" {{ $filter === 'pregnancies' ? 'selected' : '' }}>Pregnancies</option>
                    </select>
                </div>
                <div class="span-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="span-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <option value="">All Years</option>
                        @foreach(range(2020, now()->year + 1) as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="span-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="span-12 workspace-filter-actions">
                    <button type="submit" class="btn btn-filter"><i class="bi bi-funnel me-1"></i>Apply Filters</button>
                    <a href="{{ route('bhw.reports.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="split-panels">
        <div class="workspace-panel fade-in-card">
            <div class="workspace-panel-header">
                <h2 class="workspace-panel-title">Health Record Reports</h2>
                <p class="workspace-panel-subtitle">Monthly summaries built from patient health records.</p>
            </div>
            <div class="workspace-panel-body pt-3">
                @if($healthReports->count() > 0)
                    <div class="modern-table-wrap">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Report</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Submission</th>
                                    <th class="text-end report-actions-col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($healthReports as $report)
                                    <tr>
                                        <td>
                                            <div class="table-title">{{ $report->title }}</div>
                                            <div class="table-subtitle">{{ \Illuminate\Support\Str::limit($report->description, 48) }}</div>
                                        </td>
                                        <td><span class="summary-chip chip-info">{{ $report->reportPeriod }}</span></td>
                                        <td><span class="summary-chip {{ $report->status === 'completed' ? 'chip-success' : 'chip-warning' }}">{{ ucfirst($report->status) }}</span></td>
                                        <td><span class="summary-chip chip-primary">{{ ucwords(str_replace('_', ' ', $report->submission_status)) }}</span></td>
                                        <td class="text-end report-actions-col">
                                            <div class="report-table-actions">
                                                <a href="{{ route('bhw.reports.show', $report->id) }}" class="btn btn-sm btn-outline-primary report-action-btn" title="View report"><i class="bi bi-eye"></i></a>
                                                @if($report->submission_status === 'draft')
                                                    <form method="POST" action="{{ route('bhw.reports.submit-to-president', $report->id) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success report-action-btn" title="Submit to BHW President"><i class="bi bi-send"></i></button>
                                                    </form>
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
                        <i class="bi bi-file-medical"></i>
                        <h5>No health record reports</h5>
                        <p>Create one to summarize patient visits and vital tracking for the month.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="workspace-panel fade-in-card">
            <div class="workspace-panel-header">
                <h2 class="workspace-panel-title">Pregnancy Reports</h2>
                <p class="workspace-panel-subtitle">Monthly summaries of active pregnancy monitoring.</p>
            </div>
            <div class="workspace-panel-body pt-3">
                @if($pregnancyReports->count() > 0)
                    <div class="modern-table-wrap">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Report</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Submission</th>
                                    <th class="text-end report-actions-col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pregnancyReports as $report)
                                    <tr>
                                        <td>
                                            <div class="table-title">{{ $report->title }}</div>
                                            <div class="table-subtitle">{{ \Illuminate\Support\Str::limit($report->description, 48) }}</div>
                                        </td>
                                        <td><span class="summary-chip chip-info">{{ $report->reportPeriod }}</span></td>
                                        <td><span class="summary-chip {{ $report->status === 'completed' ? 'chip-success' : 'chip-warning' }}">{{ ucfirst($report->status) }}</span></td>
                                        <td><span class="summary-chip chip-primary">{{ ucwords(str_replace('_', ' ', $report->submission_status)) }}</span></td>
                                        <td class="text-end report-actions-col">
                                            <div class="report-table-actions">
                                                <a href="{{ route('bhw.reports.show', $report->id) }}" class="btn btn-sm btn-outline-primary report-action-btn" title="View report"><i class="bi bi-eye"></i></a>
                                                @if($report->submission_status === 'draft')
                                                    <form method="POST" action="{{ route('bhw.reports.submit-to-president', $report->id) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success report-action-btn" title="Submit to BHW President"><i class="bi bi-send"></i></button>
                                                    </form>
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
                        <i class="bi bi-heart"></i>
                        <h5>No pregnancy reports</h5>
                        <p>Create one to summarize active maternal monitoring cases for the period.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="workspace-panel fade-in-card">
        <div class="workspace-panel-header">
            <h2 class="workspace-panel-title">All Reports</h2>
            <p class="workspace-panel-subtitle">Your full monthly report history with quick actions.</p>
        </div>
        <div class="workspace-panel-body pt-3">
            @if($reports->count() > 0)
                <div class="modern-table-wrap">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Report Title</th>
                                <th>Type</th>
                                <th>Period</th>
                                <th>Records</th>
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
                                    <td>
                                        <span class="summary-chip {{ $report->report_type === 'pregnancies' ? 'chip-info' : 'chip-primary' }}">
                                            {{ $report->report_type === 'pregnancies' ? 'Pregnancies' : 'Health Records' }}
                                        </span>
                                    </td>
                                    <td><span class="summary-chip chip-info">{{ $report->reportPeriod }}</span></td>
                                    <td>{{ $report->total_records }} records</td>
                                    <td><span class="summary-chip {{ $report->status === 'completed' ? 'chip-success' : 'chip-warning' }}">{{ ucfirst($report->status) }}</span></td>
                                    <td>{{ $report->created_at->format('M d, Y') }}</td>
                                    <td class="text-end report-actions-col">
                                        <div class="report-table-actions">
                                            <a href="{{ route('bhw.reports.show', $report->id) }}" class="btn btn-sm btn-outline-primary report-action-btn" title="View report"><i class="bi bi-eye"></i></a>
                                            <a href="{{ route('bhw.reports.print', $report->id) }}" class="btn btn-sm btn-outline-success report-action-btn" target="_blank" title="Print report"><i class="bi bi-printer"></i></a>
                                            <x-archive-form :action="route('bhw.reports.destroy', $report->id)" label="" title="Archive report (retained for audit)" btnClass="btn btn-sm btn-outline-warning report-action-btn" icon="bi bi-archive" confirmText="Archive this report? It will be retained for audit and can be restored." />
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
                    <i class="bi bi-file-earmark-text"></i>
                    <h3>No reports found</h3>
                    <p>Start by creating your first monthly report for health records or pregnancies.</p>
                    <a href="{{ route('bhw.reports.create') }}" class="btn btn-primary mt-3">Create Report</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
