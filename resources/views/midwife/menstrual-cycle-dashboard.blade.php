@extends('midwife.layout')

@section('title', 'Menstrual Cycle Dashboard - ReproCare')

@section('midwife-content')

{{-- ═══════════════════════════════
     PAGE HERO
═══════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h1 class="page-hero-title">Menstrual Cycle Predictions
            </h1>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Overview of patient menstrual cycle predictions, fertility windows, and ovulation tracking
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('midwife.dashboard') }}" class="btn-hero-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     SUMMARY STATS (WHITE THEME)
════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card fade-in-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:var(--color-primary-soft); color:var(--color-primary-text);">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-number">{{ count($womanPredictions) }}</div>
                    <div class="stat-label">Patients Tracked</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card fade-in-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:var(--color-danger-soft); color:var(--color-secondary-text);">
                    <i class="bi bi-calendar-heart-fill"></i>
                </div>
                <div>
                    <div class="stat-number">{{ collect($womanPredictions)->where('days_until_period', '<=', 7)->count() }}</div>
                    <div class="stat-label">Period in 7 Days</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card fade-in-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:var(--color-success-soft); color:var(--color-success-text);">
                    <i class="bi bi-flower1"></i>
                </div>
                <div>
                    <div class="stat-number">{{ collect($womanPredictions)->where('days_until_period', '>=', 14)->where('days_until_period', '<=', 21)->count() }}</div>
                    <div class="stat-label">In Fertile Window</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card fade-in-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:var(--color-info-soft); color:var(--color-info-text);">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div>
                    <div class="stat-number">
                        {{ collect($womanPredictions)->avg('average_cycle') ? round(collect($womanPredictions)->avg('average_cycle')) . 'd' : '—' }}
                    </div>
                    <div class="stat-label">Avg Cycle Length</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     PATIENT PREDICTIONS TABLE
════════════════════════════════ --}}
<div class="card fade-in-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold" style="color:var(--color-text); font-family:'Plus Jakarta Sans',sans-serif;">Patient Predictions
        </h5>
        <span class="badge rounded-pill px-3 py-1 fw-bold" style="background:var(--color-primary-soft); border:1px solid var(--color-border); color:var(--color-primary-text); font-size:0.8rem;">
            {{ count($womanPredictions) }} Patients
        </span>
    </div>
    <div class="card-body p-0">
        @if(count($womanPredictions) > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size:0.78rem; font-weight:700; border-bottom:1px solid var(--color-border);">
                            <th class="ps-4 py-3">Patient Name</th>
                            <th class="py-3">Avg Cycle</th>
                            <th class="py-3">Next Period</th>
                            <th class="py-3">Countdown</th>
                            <th class="py-3">Est. Ovulation</th>
                            <th class="py-3">Fertile Window</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($womanPredictions as $prediction)
                            <tr style="border-bottom:1px solid var(--color-border);">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width:38px;height:38px;border-radius:12px;background:var(--color-primary-soft);border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;color:var(--color-primary-text);font-weight:700;flex-shrink:0;">
                                            {{ strtoupper(substr($prediction['woman']->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="color:var(--color-text);">{{ $prediction['woman']->name }}</div>
                                            <small class="text-muted">Brgy. {{ $prediction['woman']->barangay ?? 'San Carlos' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 fw-semibold" style="color:var(--color-text);">{{ $prediction['average_cycle'] }} days</td>
                                <td class="py-3 fw-semibold" style="color:var(--color-text);">{{ $prediction['next_period']->format('M d, Y') }}</td>
                                <td class="py-3">
                                    @if($prediction['days_until_period'] <= 7)
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-danger-soft); border:1px solid var(--color-secondary-soft); color:var(--color-secondary-text);">
                                            <i class="bi bi-hourglass-bottom me-1"></i> {{ round($prediction['days_until_period']) }} days
                                        </span>
                                    @elseif($prediction['days_until_period'] <= 14)
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-peach-soft); border:1px solid var(--color-peach-soft); color:var(--color-peach-text);">
                                            <i class="bi bi-clock me-1"></i> {{ round($prediction['days_until_period']) }} days
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text);">
                                            <i class="bi bi-check-circle me-1"></i> {{ round($prediction['days_until_period']) }} days
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 text-muted fw-medium">{{ $prediction['ovulation']->format('M d, Y') }}</td>
                                <td class="py-3 text-muted">
                                    {{ $prediction['fertile_window']['start']->format('M d') }} – {{ $prediction['fertile_window']['end']->format('M d') }}
                                </td>
                                <td class="py-3">
                                    @if($prediction['days_until_period'] <= 7)
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-danger-soft); border:1px solid var(--color-secondary-soft); color:var(--color-secondary-text);">
                                            <i class="bi bi-exclamation-circle me-1"></i> Period Soon
                                        </span>
                                    @elseif($prediction['days_until_period'] >= 14 && $prediction['days_until_period'] <= 21)
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text);">
                                            <i class="bi bi-flower1 me-1"></i> Fertile
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-info-soft); border:1px solid var(--color-info-soft); color:var(--color-info-text);">
                                            <i class="bi bi-check2 me-1"></i> Normal
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 text-end pe-4">
                                    <a href="{{ route('midwife.patient-details', $prediction['woman']->id) }}" 
                                       class="btn btn-sm btn-light border" 
                                       style="border-radius:8px; font-weight:600;">
                                        <i class="bi bi-eye me-1"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <div style="width:64px;height:64px;border-radius:18px;background:var(--color-primary-soft);color:var(--color-primary-text);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-calendar-x" style="font-size:1.75rem;"></i>
                </div>
                <h5 class="fw-bold mb-1" style="color:var(--color-text);">No Predictions Available</h5>
                <p class="text-muted mb-0" style="font-size:0.9rem; max-width:400px; margin:0 auto;">
                    No patients have recorded sufficient menstrual cycle entries for clinical cycle predictions yet.
                </p>
            </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════
     STATUS LEGEND
════════════════════════════════ --}}
<div class="card fade-in-card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold" style="color:var(--color-text); font-family:'Plus Jakarta Sans',sans-serif;">Prediction Status Legend
        </h6>
    </div>
    <div class="card-body p-4">
        <div class="d-flex flex-wrap gap-4 align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-danger-soft); border:1px solid var(--color-secondary-soft); color:var(--color-secondary-text);">
                    <i class="bi bi-exclamation-circle me-1"></i> Period Soon
                </span>
                <span class="text-muted" style="font-size:0.85rem;">Period expected within 7 days</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text);">
                    <i class="bi bi-flower1 me-1"></i> Fertile
                </span>
                <span class="text-muted" style="font-size:0.85rem;">Currently in peak or fertile window</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-info-soft); border:1px solid var(--color-info-soft); color:var(--color-info-text);">
                    <i class="bi bi-check2 me-1"></i> Normal
                </span>
                <span class="text-muted" style="font-size:0.85rem;">Outside critical tracking windows</span>
            </div>
        </div>
    </div>
</div>
@endsection
