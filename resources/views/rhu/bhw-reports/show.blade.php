@extends('rhu.layout')

@section('title', 'View BHW Monthly Report - RHU Portal | ReproCare')

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

<!-- Risk Distribution details -->
<div class="card fade-in-card mb-4">
    <div class="card-body">
        <h6 class="mb-3 fw-700 text-dark">Risk Level Distribution</h6>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2 text-xs fw-600">
                    <span class="text-dark"><i class="bi bi-circle-fill text-success me-1"></i> Low Risk</span>
                    <span class="badge bg-success">{{ $riskDistribution['low'] }}</span>
                </div>
                <div class="progress" style="height:6px;">
                    @php
                        $total = array_sum($riskDistribution);
                        $lowPct = $total > 0 ? ($riskDistribution['low'] / $total) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-success" style="width:{{ $lowPct }}%"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2 text-xs fw-600">
                    <span class="text-dark"><i class="bi bi-circle-fill text-warning me-1"></i> Medium Risk</span>
                    <span class="badge bg-warning text-dark">{{ $riskDistribution['medium'] }}</span>
                </div>
                <div class="progress" style="height:6px;">
                    @php
                        $medPct = $total > 0 ? ($riskDistribution['medium'] / $total) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-warning" style="width:{{ $medPct }}%"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2 text-xs fw-600">
                    <span class="text-dark"><i class="bi bi-circle-fill text-danger me-1"></i> High Risk</span>
                    <span class="badge bg-danger">{{ $riskDistribution['high'] }}</span>
                </div>
                <div class="progress" style="height:6px;">
                    @php
                        $highPct = $total > 0 ? ($riskDistribution['high'] / $total) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-danger" style="width:{{ $highPct }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Health Records Table -->
<div class="card fade-in-card mb-4">
    <div class="card-header bg-transparent py-3">
        <h5 class="mb-0 fw-700 text-dark">Health Records</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-xs">
                <thead>
                    <tr>
                        <th class="px-4">Date</th>
                        <th>Patient</th>
                        <th>Blood Pressure</th>
                        <th>Weight</th>
                        <th>Heart Rate</th>
                        <th>Temp</th>
                        <th>Risk Level</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($healthRecords as $record)
                        <tr>
                            <td class="px-4">
                                <span class="fw-700 text-dark d-block">{{ $record->created_at->format('M d, Y') }}</span>
                                <span class="text-muted">{{ $record->created_at->format('g:i A') }}</span>
                            </td>
                            <td>
                                <span class="fw-700 text-dark d-block">{{ $record->user->name ?? 'Unknown' }}</span>
                                <span class="text-muted">{{ $record->user->email ?? '' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $record->bp }}
                                </span>
                            </td>
                            <td>{{ $record->weight }} kg</td>
                            <td>{{ $record->heart_rate }} bpm</td>
                            <td>{{ $record->temperature }}°C</td>
                            <td>
                                @if($record->risk_level === 'High')
                                    <span class="badge bg-danger">High</span>
                                @elseif($record->risk_level === 'Medium')
                                    <span class="badge bg-warning text-dark">Medium</span>
                                @else
                                    <span class="badge bg-success">Low</span>
                                @endif
                            </td>
                        </tr>
                        @if($record->notes)
                            <tr class="bg-light">
                                <td colspan="7" class="px-4 py-2 text-xs italic text-muted">
                                    <strong>Notes:</strong> {{ $record->notes }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size:3rem; color:color-mix(in srgb, var(--color-text) 10%, transparent);"></i>
                                <p class="text-muted mt-2">No health records found for this report period</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($healthRecords->hasPages())
            <div class="d-flex justify-content-center p-3 border-top">
                {{ $healthRecords->links() }}
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
