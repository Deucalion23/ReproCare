@extends('bhw-president.layout')

@use('Carbon\Carbon')

@section('title', 'Pregnancy Report Details - BHW President Portal | ReproCare')

@section('bhw-president-content')

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
        <a href="{{ route('bhw-president.reports.index') }}" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
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

@endsection
