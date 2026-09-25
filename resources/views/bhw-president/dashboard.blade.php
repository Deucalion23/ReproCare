@extends('bhw-president.layout')

@section('title', 'Dashboard - BHW President Portal | ReproCare')

@section('bhw-president-content')

@php
    $hour = now()->hour;
    $timeOfDay = $hour < 12 ? 'Morning' : ($hour < 17 ? 'Afternoon' : 'Evening');
@endphp

{{-- ═══════════════════════════════
     PAGE HERO
═══════════════════════════════ --}}
<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">
                Good {{ $timeOfDay }}, {{ auth()->user()->name }}!
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
            </p>
        </div>
    </div>

    {{-- Today's Summary --}}
    <div class="d-flex flex-wrap gap-2 mt-3" style="position:relative;z-index:1;">
        <span class="summary-chip">
            <i class="bi bi-people-fill"></i>
            {{ $totalPatients }} Total Patients
        </span>
        <span class="summary-chip">
            <i class="bi bi-person-workspace"></i>
            {{ $totalBhws }} BHWs
        </span>
        @if($highRiskPregnanciesCount > 0)
        <span class="summary-chip" style="background:color-mix(in srgb, var(--color-danger) 30%, transparent);">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ $highRiskPregnanciesCount }} High Risk
        </span>
        @endif
        <span class="summary-chip">
            <i class="bi bi-calendar-check"></i>
            {{ $scheduledCheckups }} Scheduled
        </span>
    </div>
</div>

{{-- ═══════════════════════════════
     STAT CARDS
═══════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-purple fade-in-card">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-label">Total Patients</div>
            <div class="stat-number" data-count="{{ $totalPatients }}">0</div>
            <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> Enrolled patients</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-cyan fade-in-card">
            <div class="stat-icon"><i class="bi bi-person-workspace"></i></div>
            <div class="stat-label">Active BHWs</div>
            <div class="stat-number" data-count="{{ $totalBhws }}">0</div>
            <div class="stat-trend"><i class="bi bi-users"></i> Health workers</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-rose fade-in-card">
            <div class="stat-icon"><i class="bi bi-heart-pulse-fill"></i></div>
            <div class="stat-label">Active Pregnancies</div>
            <div class="stat-number" data-count="{{ $activePregnancies }}">0</div>
            <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> Currently monitoring</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-amber fade-in-card">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-label">High Risk</div>
            <div class="stat-number" data-count="{{ $highRiskPregnanciesCount }}">0</div>
            <div class="stat-trend danger"><i class="bi bi-exclamation"></i> Requires attention</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-green fade-in-card">
            <div class="stat-icon"><i class="bi bi-calendar-check-fill"></i></div>
            <div class="stat-label">Scheduled Checkups</div>
            <div class="stat-number" data-count="{{ $scheduledCheckups }}">0</div>
            <div class="stat-trend"><i class="bi bi-clock"></i> Upcoming sessions</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-violet fade-in-card">
            <div class="stat-icon"><i class="bi bi-calendar-x-fill"></i></div>
            <div class="stat-label">Missed Checkups</div>
            <div class="stat-number" data-count="{{ $missedCheckups }}">0</div>
            <div class="stat-trend danger"><i class="bi bi-exclamation-circle"></i> Follow-up needed</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-purple fade-in-card">
            <div class="stat-icon"><i class="bi bi-clipboard-check-fill"></i></div>
            <div class="stat-label">Completed Checkups</div>
            <div class="stat-number" data-count="{{ $completedCheckups }}">0</div>
            <div class="stat-trend up"><i class="bi bi-check-circle"></i> Successfully completed</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-cyan fade-in-card">
            <div class="stat-icon"><i class="bi bi-file-medical-fill"></i></div>
            <div class="stat-label">Health Records</div>
            <div class="stat-number" data-count="{{ $totalHealthRecords }}">0</div>
            <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> Total records</div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     HIGH RISK ALERTS
═══════════════════════════════ --}}
@if($highRiskPregnancies->count() > 0)
<div class="card mb-4 fade-in-card" style="border-left:4px solid var(--warning);">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">High-Risk Pregnancies Requiring Attention</h5>
        <a href="{{ route('bhw-president.high-risk') }}" class="btn btn-sm btn-outline-warning">View All</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Gestational Age</th>
                        <th>Risk Factors</th>
                        <th>Last Checkup</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($highRiskPregnancies as $pregnancy)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--warning),var(--danger));display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:var(--color-on-solid);">
                                    {{ strtoupper(substr(optional($pregnancy->woman)->name ?? 'U', 0, 1)) }}
                                </div>
                                <span style="font-weight:500;">{{ optional($pregnancy->woman)->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td>{{ $pregnancy->gestational_age ?? 'N/A' }} weeks</td>
                        <td><span class="badge bg-danger">High Risk</span></td>
                        <td>{{ optional($pregnancy->checkups->first())->scheduled_date?->format('M j, Y') ?? 'No checkups' }}</td>
                        <td>
                            <a href="{{ route('midwife.patient-details', $pregnancy->user_id) }}" class="btn btn-sm btn-view">View Details</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════
     RECENT ACTIVITY
═══════════════════════════════ --}}
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card fade-in-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Checkups</h5>
                <a href="{{ route('midwife.checkups.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentCheckups->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentCheckups as $checkup)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent-violet));display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:var(--color-on-solid);">
                                            {{ strtoupper(substr(optional($checkup->woman)->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <span style="font-weight:500;">{{ optional($checkup->woman)->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td>{{ $checkup->scheduled_date->format('M j, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $checkup->status === 'Completed' ? 'success' : ($checkup->status === 'Missed' ? 'danger' : 'primary') }}">
                                        {{ $checkup->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x" style="font-size:2rem;color:var(--text-muted);"></i>
                    <p class="mb-0 mt-2">No recent checkups</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card fade-in-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Health Records</h5>
                <a href="{{ route('midwife.health-records.index') }}" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentHealthRecords->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Recorded By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentHealthRecords as $record)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--success),var(--color-success));display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:var(--color-on-solid);">
                                            {{ strtoupper(substr(optional($record->woman)->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <span style="font-weight:500;">{{ optional($record->woman)->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td>{{ optional($record->recordedBy)->name ?? 'Unknown' }}</td>
                                <td>{{ $record->created_at->format('M j, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-file-medical" style="font-size:2rem;color:var(--text-muted);"></i>
                    <p class="mb-0 mt-2">No recent health records</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Animate stat numbers
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const target = parseInt(stat.dataset.count);
        if (target > 0) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = target;
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.floor(current);
                }
            }, 30);
        }
    });
});
</script>
@endpush

@endsection
