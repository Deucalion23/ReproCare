@extends('midwife.layout')

@section('title', 'Admin Dashboard - Health Officials Analytics')

@section('midwife-content')

{{-- ═══════════════════════════════
     PAGE HERO
═══════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Analytics
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Health Officials Analytics & Reporting
            </p>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('midwife.admin.dashboard') }}" class="d-flex gap-2 align-items-center">
                <input type="date" name="start_date" class="form-control" 
                       style="background:color-mix(in srgb, var(--color-surface) 15%, transparent); border:1px solid color-mix(in srgb, var(--color-border) 30%, transparent); color:var(--color-on-solid); border-radius:12px; height:44px; min-width:140px;"
                       value="{{ $startDate->format('Y-m-d') }}">
                <input type="date" name="end_date" class="form-control" 
                       style="background:color-mix(in srgb, var(--color-surface) 15%, transparent); border:1px solid color-mix(in srgb, var(--color-border) 30%, transparent); color:var(--color-on-solid); border-radius:12px; height:44px; min-width:140px;"
                       value="{{ $endDate->format('Y-m-d') }}">
                <button type="submit" class="btn d-flex align-items-center justify-content-center gap-1" 
                        style="background:color-mix(in srgb, var(--color-surface) 25%, transparent); border:1px solid color-mix(in srgb, var(--color-border) 40%, transparent); color:var(--color-on-solid); border-radius:12px; height:44px; padding:0 1.25rem; font-weight:600; backdrop-filter:blur(10px); min-width:100px;">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
            </form>
        </div>
    </div>
</div>

    {{-- ═══════════════════════════════
         STATS CARDS
    ════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col">
            <div class="stat-card stat-blue fade-in-card" style="background:linear-gradient(135deg, var(--color-info) 0%, var(--color-info-text) 100%);">
                <i class="bi bi-heart-pulse stat-icon"></i>
                <div class="stat-label">Pregnant Women</div>
                <div class="stat-number" data-count="{{ $totalPregnantWomen }}">{{ $totalPregnantWomen }}</div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card stat-green fade-in-card">
                <i class="bi bi-calendar-check stat-icon"></i>
                <div class="stat-label">Completed Appointments</div>
                <div class="stat-number" data-count="{{ $completedAppointments }}">{{ $completedAppointments }}</div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card stat-amber fade-in-card">
                <i class="bi bi-calendar-x stat-icon"></i>
                <div class="stat-label">Missed Appointments</div>
                <div class="stat-number" data-count="{{ $missedAppointments }}">{{ $missedAppointments }}</div>
                <small style="color:color-mix(in srgb, var(--color-on-solid) 70%, transparent); font-size:0.75rem;">{{ $missedRate }}% rate</small>
            </div>
        </div>
        <div class="col">
            <div class="stat-card stat-lime fade-in-card">
                <i class="bi bi-shield-check stat-icon"></i>
                <div class="stat-label">Immunization Coverage</div>
                <div class="stat-number">{{ $immunizationCoverage }}%</div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════
         ADDITIONAL STATS
    ════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="stat-card stat-cyan fade-in-card">
                <i class="bi bi-exclamation-circle stat-icon"></i>
                <div class="stat-label">Medium Risk Cases</div>
                <div class="stat-number" data-count="{{ $mediumRiskCases }}">{{ $mediumRiskCases }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card stat-danger fade-in-card">
                <i class="bi bi-exclamation-triangle stat-icon"></i>
                <div class="stat-label">High Risk Cases</div>
                <div class="stat-number" data-count="{{ $highRiskCases }}">{{ $highRiskCases }}</div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════
         CHARTS SECTION
    ════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card fade-in-card" style="border:none; background:var(--bg-card);">
                <div class="card-header" style="background:transparent; border-bottom:1px solid var(--border-color); padding:1rem 1.5rem;">
                    <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">Pregnancy Trend
                    </h5>
                </div>
                <div class="card-body" style="padding:1.5rem;">
                    <canvas id="pregnancyChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card fade-in-card" style="border:none; background:var(--bg-card);">
                <div class="card-header" style="background:transparent; border-bottom:1px solid var(--border-color); padding:1rem 1.5rem;">
                    <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">Risk Distribution
                    </h5>
                </div>
                <div class="card-body" style="padding:1.5rem;">
                    <canvas id="riskChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Appointment Trend Chart --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card fade-in-card" style="border:none; background:var(--bg-card);">
                <div class="card-header" style="background:transparent; border-bottom:1px solid var(--border-color); padding:1rem 1.5rem;">
                    <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">Appointment Trend (Completed vs Missed)
                    </h5>
                </div>
                <div class="card-body" style="padding:1.5rem;">
                    <canvas id="appointmentChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Pregnancy Trend Chart
    const pregnancyCtx = document.getElementById('pregnancyChart').getContext('2d');
    new Chart(pregnancyCtx, {
        type: 'line',
        data: {
            labels: @json(array_keys($pregnancyTrend)),
            datasets: [{
                label: 'Pregnant Women',
                data: @json(array_values($pregnancyTrend)),
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Risk Distribution Chart
    const riskCtx = document.getElementById('riskChart').getContext('2d');
    new Chart(riskCtx, {
        type: 'doughnut',
        data: {
            labels: ['Low', 'Medium', 'High'],
            datasets: [{
                data: @json(array_values($riskDistribution)),
                rcColors: ['success', 'warning', 'danger']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Appointment Trend Chart
    const appointmentCtx = document.getElementById('appointmentChart').getContext('2d');
    const appointmentLabels = @json(array_keys($appointmentTrend));
    const completedData = @json(array_column($appointmentTrend, 'completed'));
    const missedData = @json(array_column($appointmentTrend, 'missed'));
    new Chart(appointmentCtx, {
        type: 'bar',
        data: {
            labels: appointmentLabels,
            datasets: [
                {
                    label: 'Completed',
                    data: completedData,
                    rcColor: 'success'
                },
                {
                    label: 'Missed',
                    data: missedData,
                    rcColor: 'danger'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: false },
                y: { stacked: false }
            }
        }
    });
</script>
@endsection
