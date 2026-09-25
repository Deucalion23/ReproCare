@extends('bhw-president.layout')

@use('Carbon\Carbon')

@section('title', 'Report Details - BHW President Portal | ReproCare')

@section('bhw-president-content')

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">{{ $report->title }}</div>
            <p class="page-hero-subtitle">
                {{ optional($report->bhw)->name ?? 'Unknown BHW' }} · 
                {{ Carbon::create()->month($report->report_month)->format('F') }} {{ $report->report_year }}
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
            <div class="stat-icon"><i class="bi bi-file-medical-fill"></i></div>
            <div class="stat-label">Total Records</div>
            <div class="stat-number">{{ $healthRecords->count() }}</div>
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
        <h5 class="mb-0">Health Records</h5>
    </div>
    <div class="card-body">
        @if($healthRecords->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Date</th>
                        <th>BP</th>
                        <th>Weight</th>
                        <th>Heart Rate</th>
                        <th>Temperature</th>
                        <th>Risk Level</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($healthRecords as $record)
                    <tr>
                        <td>{{ optional($record->woman)->name ?? 'Unknown' }}</td>
                        <td>{{ $record->created_at->format('M j, Y') }}</td>
                        <td>{{ $record->bp ?? 'N/A' }}</td>
                        <td>{{ $record->weight ?? 'N/A' }} kg</td>
                        <td>{{ $record->heart_rate ?? 'N/A' }} bpm</td>
                        <td>{{ $record->temperature ?? 'N/A' }} °C</td>
                        <td>
                            <span class="badge bg-{{ $record->risk_level === 'high' ? 'danger' : ($record->risk_level === 'medium' ? 'warning' : 'success') }}">
                                {{ ucfirst($record->risk_level ?? 'low') }}
                            </span>
                        </td>
                        <td>{{ optional($record->recordedBy)->name ?? 'Unknown' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-file-medical" style="font-size:2rem;color:var(--text-muted);"></i>
            <p class="mb-0 mt-2">No health records in this report</p>
        </div>
        @endif
    </div>
</div>

@endsection
