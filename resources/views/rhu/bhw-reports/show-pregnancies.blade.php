@extends('rhu.layout')

@section('title', 'View BHW Pregnancy Report - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">{{ $report->title }}
            </div>
            <p class="page-hero-subtitle">
                <span class="badge bg-info text-dark">{{ $report->reportPeriod }}</span>
                <span class="badge bg-{{ $report->submission_status === 'approved_by_midwife' ? 'success' : (in_array($report->submission_status, ['rejected', 'needs_revision']) ? 'danger' : 'warning') }}">
                    {{ ucwords(str_replace('_', ' ', $report->submission_status)) }}
                </span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('rhu.bhw-reports.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Reports
            </a>
            <a href="{{ route('rhu.bhw-reports.print', $report->id) }}" class="btn btn-primary btn-sm text-white" target="_blank">
                <i class="bi bi-printer-fill me-1"></i> Print Report
            </a>
        </div>
    </div>
</div>

<div class="card fade-in-card mb-4">
    <div class="card-body">
        <h6 class="mb-3 fw-700 text-dark">BHW &amp; Submission Information</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex justify-content-between mb-2 pb-1 border-bottom text-xs">
                    <span class="text-muted">BHW Name:</span>
                    <span class="fw-bold text-dark">{{ $report->bhw->name ?? 'Unknown' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 pb-1 border-bottom text-xs">
                    <span class="text-muted">Email:</span>
                    <span class="text-dark">{{ $report->bhw->email ?? 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between text-xs">
                    <span class="text-muted">Barangay:</span>
                    <span class="text-dark">{{ $report->bhw->barangay ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between mb-2 pb-1 border-bottom text-xs">
                    <span class="text-muted">Report Period:</span>
                    <span class="fw-bold text-dark">{{ $report->reportPeriod }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 pb-1 border-bottom text-xs">
                    <span class="text-muted">Total Records:</span>
                    <span class="fw-bold text-dark">{{ $report->total_records }}</span>
                </div>
                <div class="d-flex justify-content-between text-xs">
                    <span class="text-muted">Unique Patients:</span>
                    <span class="text-dark">{{ $uniquePatients }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@if($report->description)
    <div class="alert alert-info fade-in-card mb-4" style="border-radius:10px;">
        <i class="bi bi-info-circle-fill me-2"></i><strong>Description:</strong> {{ $report->description }}
    </div>
@endif

<!-- Statistics Grid -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-purple py-3 fade-in-card">
            <div class="stat-icon" style="font-size:1.5rem;"><i class="bi bi-file-medical-fill"></i></div>
            <div class="stat-label" style="font-size:0.75rem;">Total Records</div>
            <div class="stat-number" style="font-size:1.8rem;">{{ $report->total_records }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-cyan py-3 fade-in-card">
            <div class="stat-icon" style="font-size:1.5rem;"><i class="bi bi-people-fill"></i></div>
            <div class="stat-label" style="font-size:0.75rem;">Unique Patients</div>
            <div class="stat-number" style="font-size:1.8rem;">{{ $uniquePatients }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-rose py-3 fade-in-card">
            <div class="stat-icon" style="font-size:1.5rem;"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-label" style="font-size:0.75rem;">High Risk Cases</div>
            <div class="stat-number" style="font-size:1.8rem;">{{ $riskDistribution['high'] }}</div>
        </div>
    </div>
</div>

<!-- Pregnancies Table -->
<div class="card fade-in-card mb-4">
    <div class="card-header bg-transparent py-3">
        <h5 class="mb-0 fw-700 text-dark">Pregnancy Registry Entries</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-xs">
                <thead>
                    <tr>
                        <th class="px-4">Date Logged</th>
                        <th>Patient</th>
                        <th>LMP</th>
                        <th>EDD</th>
                        <th>G-T-P-A-L</th>
                        <th>High Risk</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pregnancies as $preg)
                        <tr>
                            <td class="px-4">
                                <span class="fw-700 text-dark d-block">{{ $preg->created_at->format('M d, Y') }}</span>
                            </td>
                            <td>
                                <span class="fw-700 text-dark d-block">{{ $preg->woman->name ?? 'Unknown' }}</span>
                                <span class="text-muted">{{ $preg->woman->email ?? '' }}</span>
                            </td>
                            <td>{{ $preg->lmp ? $preg->lmp->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $preg->edd ? $preg->edd->format('M d, Y') : 'N/A' }}</td>
                            <td>G{{ $preg->gravida ?? 0 }} P{{ $preg->parity ?? 0 }} (T{{ $preg->term ?? 0 }} P{{ $preg->preterm ?? 0 }} A{{ $preg->abortion ?? 0 }} L{{ $preg->living ?? 0 }})</td>
                            <td>
                                <span class="badge bg-{{ $preg->is_high_risk ? 'danger' : 'success' }}">
                                    {{ $preg->is_high_risk ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-white text-xs">
                                    {{ ucfirst($preg->workflow_status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size:3rem; color:color-mix(in srgb, var(--color-text) 10%, transparent);"></i>
                                <p class="text-muted mt-2">No pregnancy entries found for this report period</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pregnancies->hasPages())
            <div class="d-flex justify-content-center p-3 border-top">
                {{ $pregnancies->links() }}
            </div>
        @endif
    </div>
</div>

<div class="d-flex justify-content-between mb-4">
    <x-archive-form :action="route('rhu.bhw-reports.destroy', $report->id)" label="Archive Report" title="Archive report (retained for audit)" btnClass="btn btn-outline-warning" icon="bi bi-archive" confirmText="Archive this report? It will be retained for audit and can be restored." />
    <a href="{{ route('rhu.bhw-reports.print', $report->id) }}" class="btn btn-primary text-white" target="_blank">
        <i class="bi bi-printer-fill me-1"></i> Print Report
    </a>
</div>

@endsection
