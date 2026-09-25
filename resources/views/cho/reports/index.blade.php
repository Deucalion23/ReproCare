@extends('cho.layout')

@section('title', 'City-Wide Reports - CHO Portal | ReproCare')

@section('cho-content')
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">City-Wide BHW Reports</div>
            <p class="page-hero-subtitle" style="font-weight:600;">Monthly submissions across all barangays for {{ date('F Y', strtotime($month . '-01')) }}.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('cho.reports.export.csv', ['month' => $month]) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-filetype-csv me-1"></i> CSV</a>
            <a href="{{ route('cho.reports.export.pdf', ['month' => $month]) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-filetype-pdf me-1"></i> PDF</a>
        </div>
    </div>
</div>

<div class="card fade-in-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('cho.reports.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="draft" {{ ($status ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted_to_president" {{ ($status ?? '') === 'submitted_to_president' ? 'selected' : '' }}>At President</option>
                    <option value="submitted_to_midwife" {{ ($status ?? '') === 'submitted_to_midwife' ? 'selected' : '' }}>At Midwife</option>
                    <option value="approved_by_midwife" {{ ($status ?? '') === 'approved_by_midwife' ? 'selected' : '' }}>Approved</option>
                    <option value="needs_revision" {{ ($status ?? '') === 'needs_revision' ? 'selected' : '' }}>Needs Revision</option>
                </select>
            </div>
            <div class="col-md-4 d-grid">
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th class="px-4">Report</th><th>BHW</th><th>Type</th><th>Records</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td class="px-4 fw-semibold">{{ $report->title ?? ('Report #' . $report->id) }}</td>
                            <td>{{ $report->bhw?->name ?? '—' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $report->report_type ?? 'report')) }}</td>
                            <td>{{ $report->total_records }}</td>
                            <td><span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $report->submission_status)) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No reports for this month.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="p-3 border-top d-flex justify-content-center">{{ $reports->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
