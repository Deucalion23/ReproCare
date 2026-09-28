@extends('midwife.layout')

@use('Carbon\Carbon')

@section('title', 'Midwife Monthly Pregnancy Report - Midwife Portal | ReproCare')

@section('midwife-content')

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">{{ $report->title }}</div>
            <p class="page-hero-subtitle">
                {{ optional($report->bhw)->name ?? 'Unknown BHW' }} ·
                {{ Carbon::create()->month($report->report_month)->format('F') }} {{ $report->report_year }}
                <span class="badge bg-info ms-2">Pregnancies</span>
            </p>
        </div>
        <a href="{{ route('midwife.monthly-reports.index') }}" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card fade-in-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">Validation Trail</h5>
        @if($report->submission_status === 'submitted_to_midwife')
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-success text-white" data-bs-toggle="modal" data-bs-target="#validateModal"><i class="bi bi-check-lg me-1"></i>Validate &amp; Forward to RHU</button>
                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#returnModal"><i class="bi bi-reply me-1"></i>Return to President</button>
            </div>
        @endif
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted text-xs">President Notes</div>
                <div class="fw-600">{{ $report->president_notes ?? 'No notes' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted text-xs">Validated By President</div>
                <div class="fw-600">{{ optional($report->approvedByPresident)->name ?? '—' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted text-xs">Submission Status</div>
                <div class="fw-600">{{ ucwords(str_replace('_', ' ', $report->submission_status)) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-purple fade-in-card">
            <div class="stat-icon"><i class="bi bi-heart-fill"></i></div>
            <div class="stat-label">Total Pregnancies</div>
            <div class="stat-number">{{ $pregnancies->count() }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-cyan fade-in-card">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-label">Unique Patients</div>
            <div class="stat-number">{{ $uniquePatients }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green fade-in-card">
            <div class="stat-icon"><i class="bi bi-shield-check"></i></div>
            <div class="stat-label">Low Risk</div>
            <div class="stat-number">{{ $riskDistribution['low'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-amber fade-in-card">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-label">High Risk</div>
            <div class="stat-number">{{ $riskDistribution['high'] }}</div>
        </div>
    </div>
</div>

@if($report->description)
<div class="card fade-in-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Description</h5>
    </div>
    <div class="card-body">
        <p>{{ $report->description }}</p>
    </div>
</div>
@endif

<div class="card fade-in-card">
    <div class="card-header">
        <h5 class="mb-0">Active Pregnancies</h5>
    </div>
    <div class="card-body">
        @if($pregnancies->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Date Recorded</th>
                        <th>LMP</th>
                        <th>EDD</th>
                        <th>AOG</th>
                        <th>Trimester</th>
                        <th>Risk Level</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pregnancies as $pregnancy)
                    <tr>
                        <td>{{ optional($pregnancy->woman)->name ?? 'Unknown' }}</td>
                        <td>{{ $pregnancy->created_at->format('M j, Y') }}</td>
                        <td>{{ $pregnancy->lmp ? $pregnancy->lmp->format('M j, Y') : 'N/A' }}</td>
                        <td>{{ $pregnancy->edd ? $pregnancy->edd->format('M j, Y') : 'N/A' }}</td>
                        <td>{{ $pregnancy->formatted_aog ?? 'N/A' }}</td>
                        <td>{{ $pregnancy->trimester_name ?? 'N/A' }}</td>
                        <td>
                            @if($pregnancy->is_high_risk)
                                <span class="badge bg-danger">High Risk</span>
                            @else
                                <span class="badge bg-success">Normal</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-heart" style="font-size:2rem;color:var(--text-muted);"></i>
            <p class="mb-0 mt-2">No pregnancies in this report</p>
        </div>
        @endif
    </div>
</div>

@if($report->submission_status === 'submitted_to_midwife')
<div class="modal fade" id="validateModal" tabindex="-1">
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

<div class="modal fade" id="returnModal" tabindex="-1">
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

@endsection
