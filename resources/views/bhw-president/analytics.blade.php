@extends('bhw-president.layout')

@section('title', 'Analytics - BHW President Portal | ReproCare')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cho-analytics.css') }}?v={{ filemtime(public_path('css/cho-analytics.css')) }}">
@endpush

@section('bhw-president-content')

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Maternal Health Analytics</div>
            <p class="page-hero-subtitle">Comprehensive health indicators and statistics</p>
        </div>
    </div>
</div>

{{-- Maternal Health Indicators --}}
<div class="card fade-in-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Maternal Health Indicators</h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="stat-card stat-purple fade-in-card">
                    <div class="stat-icon"><i class="bi bi-heart-fill"></i></div>
                    <div class="stat-label">Total Pregnancies</div>
                    <div class="stat-number">{{ $maternalStats['totalPregnancies'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-cyan fade-in-card">
                    <div class="stat-icon"><i class="bi bi-heart-pulse-fill"></i></div>
                    <div class="stat-label">Active Pregnancies</div>
                    <div class="stat-number">{{ $maternalStats['activePregnancies'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-green fade-in-card">
                    <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
                    <div class="stat-label">Completed</div>
                    <div class="stat-number">{{ $maternalStats['completedPregnancies'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-amber fade-in-card">
                    <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div class="stat-label">High Risk</div>
                    <div class="stat-number">{{ $maternalStats['highRiskCount'] }}</div>
                    <div class="stat-trend">{{ $maternalStats['highRiskPercentage'] }}% of active</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Service Utilization --}}
<div class="card fade-in-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Service Utilization</h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="stat-card stat-purple fade-in-card">
                    <div class="stat-icon"><i class="bi bi-calendar-check-fill"></i></div>
                    <div class="stat-label">Checkups Completed</div>
                    <div class="stat-number">{{ $serviceStats['checkupsCompleted'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-rose fade-in-card">
                    <div class="stat-icon"><i class="bi bi-calendar-x-fill"></i></div>
                    <div class="stat-label">Checkups Missed</div>
                    <div class="stat-number">{{ $serviceStats['checkupsMissed'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-cyan fade-in-card">
                    <div class="stat-icon"><i class="bi bi-file-medical-fill"></i></div>
                    <div class="stat-label">Health Records</div>
                    <div class="stat-number">{{ $serviceStats['healthRecordsTotal'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-green fade-in-card">
                    <div class="stat-icon"><i class="bi bi-calculator"></i></div>
                    <div class="stat-label">Avg Checkups/Patient</div>
                    <div class="stat-number">{{ $serviceStats['averageCheckupsPerPatient'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Monthly trends chart --}}
<div class="card fade-in-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Monthly Trends (Last 6 Months)</h5>
    </div>
    <div class="card-body">
        @include('cho.partials.analytics-chart', [
            'chartId' => 'monthly',
            'chartTitle' => 'Monthly pregnancies, checkups and health records',
            'chartLabels' => array_column($monthlyTrends, 'month'),
            'emptyDescription' => 'No pregnancies, checkups or records in the last 6 months.',
            'chartSeries' => [
                ['label' => 'Pregnancies', 'color' => 'var(--color-secondary)', 'values' => array_column($monthlyTrends, 'pregnancies')],
                ['label' => 'Checkups', 'color' => 'var(--color-info)', 'values' => array_column($monthlyTrends, 'checkups')],
                ['label' => 'Health Records', 'color' => 'var(--color-primary)', 'values' => array_column($monthlyTrends, 'healthRecords')],
            ],
        ])
        <details class="mt-3">
            <summary class="text-muted small">Monthly data table</summary>
            <div class="table-responsive mt-2">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Pregnancies</th>
                            <th>Checkups</th>
                            <th>Health Records</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyTrends as $trend)
                        <tr>
                            <td>{{ $trend['month'] }}</td>
                            <td>{{ $trend['pregnancies'] }}</td>
                            <td>{{ $trend['checkups'] }}</td>
                            <td>{{ $trend['healthRecords'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </div>
</div>

<div class="row g-4">
    {{-- Risk distribution chart --}}
    <div class="col-lg-6">
        <div class="card fade-in-card h-100">
            <div class="card-header">
                <h5 class="mb-0">Active Pregnancies by Risk Level</h5>
            </div>
            <div class="card-body">
                @include('cho.partials.analytics-chart', [
                    'chartId' => 'risk',
                    'chartTitle' => 'Active pregnancies by stored risk level',
                    'chartLabels' => $riskDistribution['labels'],
                    'emptyDescription' => 'No active pregnancies right now.',
                    'chartSeries' => [
                        ['label' => 'Low', 'color' => 'var(--color-success)', 'values' => [$riskDistribution['values'][0], 0, 0, 0]],
                        ['label' => 'Medium', 'color' => 'var(--color-warning)', 'values' => [0, $riskDistribution['values'][1], 0, 0]],
                        ['label' => 'High', 'color' => 'var(--color-danger)', 'values' => [0, 0, $riskDistribution['values'][2], 0]],
                        ['label' => 'Critical', 'color' => 'var(--color-danger-text)', 'values' => [0, 0, 0, $riskDistribution['values'][3]]],
                    ],
                ])
                <p class="text-muted small mb-0 mt-2">{{ $maternalStats['highRiskPercentage'] }}% of active pregnancies are high-risk.</p>
            </div>
        </div>
    </div>
    {{-- Checkup outcomes chart --}}
    <div class="col-lg-6">
        <div class="card fade-in-card h-100">
            <div class="card-header">
                <h5 class="mb-0">Checkup Outcomes</h5>
            </div>
            <div class="card-body">
                @include('cho.partials.analytics-chart', [
                    'chartId' => 'outcomes',
                    'chartTitle' => 'Checkups by outcome status',
                    'chartLabels' => $checkupOutcomes['labels'],
                    'emptyDescription' => 'No checkups recorded yet.',
                    'chartSeries' => [
                        ['label' => 'Scheduled', 'color' => 'var(--color-warning)', 'values' => [$checkupOutcomes['values'][0], 0, 0, 0]],
                        ['label' => 'Completed', 'color' => 'var(--color-success)', 'values' => [0, $checkupOutcomes['values'][1], 0, 0]],
                        ['label' => 'Missed', 'color' => 'var(--color-danger)', 'values' => [0, 0, $checkupOutcomes['values'][2], 0]],
                        ['label' => 'Cancelled', 'color' => 'var(--color-text-muted)', 'values' => [0, 0, 0, $checkupOutcomes['values'][3]]],
                    ],
                ])
                <p class="text-muted small mb-0 mt-2">{{ $serviceStats['averageCheckupsPerPatient'] }} completed checkups per patient on average.</p>
            </div>
        </div>
    </div>
    {{-- BHW workload chart --}}
    <div class="col-12">
        <div class="card fade-in-card h-100">
            <div class="card-header">
                <h5 class="mb-0">BHW Workload — Records Filed (Top 8)</h5>
            </div>
            <div class="card-body">
                @include('cho.partials.analytics-chart', [
                    'chartId' => 'areas',
                    'chartTitle' => 'Health records filed per BHW, top 8',
                    'chartLabels' => $bhwWorkload['labels'],
                    'emptyDescription' => 'No BHW-filed records yet.',
                    'chartSeries' => [
                        ['label' => 'Records filed', 'color' => 'var(--color-secondary)', 'values' => $bhwWorkload['values']],
                    ],
                ])
            </div>
        </div>
    </div>
</div>

@endsection
