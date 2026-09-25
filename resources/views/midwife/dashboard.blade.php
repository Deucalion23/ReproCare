@extends('midwife.layout')

@section('title', 'Dashboard - Midwife Portal | ReproCare')

@section('midwife-content')

@php
    $hour = now()->hour;
    $timeOfDay = $hour < 12 ? 'Morning' : ($hour < 17 ? 'Afternoon' : 'Evening');
@endphp

{{-- PAGE HERO : white card, vertically centered --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div>
                <div class="page-hero-title">
                    Good {{ $timeOfDay }}, {{ auth()->user()->name }}!
                </div>
                <p class="page-hero-subtitle mb-0">
                    <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('midwife.checkups.create') }}" class="btn-hero-primary">
                <i class="bi bi-calendar-plus-fill"></i> Schedule Checkup
            </a>
        </div>
    </div>

    {{-- Summary Chips Bar --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3" style="border-top:1px solid var(--color-border);">
        <span class="summary-chip chip-primary">
            <i class="bi bi-clipboard-check"></i>
            {{ $scheduledCheckups }} Checkups Scheduled
        </span>
        @if($highRiskPatients > 0)
        <span class="summary-chip chip-danger">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ $highRiskPatients }} High Risk
        </span>
        @endif
        @if($missedCheckups > 0)
        <span class="summary-chip chip-warning">
            <i class="bi bi-calendar-x"></i>
            {{ $missedCheckups }} Missed Checkup{{ $missedCheckups > 1 ? 's' : '' }}
        </span>
        @endif
        <span class="summary-chip chip-success">
            <i class="bi bi-people-fill"></i>
            {{ $totalPatients }} Total Patients
        </span>
    </div>
</div>

{{-- ATTENTION ALERTS (conditional) --}}
@if($highRiskPatients > 0 || $missedCheckups > 0)
<div class="card mb-4">
    <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;border-radius:12px;background:var(--color-warning-soft);border:1px solid var(--color-warning);color:var(--color-warning-text);font-size:1.25rem;">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div class="flex-grow-1">
            <div style="font-weight:700;font-size:0.95rem;color:var(--color-text);font-family:'Plus Jakarta Sans',sans-serif;">Clinical Attention Required</div>
            <div style="font-size:0.85rem;color:var(--color-text-muted);">
                @if($highRiskPatients > 0)
                    <strong style="color:var(--color-danger-text);">{{ $highRiskPatients }} high-risk patient(s)</strong>
                    @if($missedCheckups > 0) &nbsp;·&nbsp; @endif
                @endif
                @if($missedCheckups > 0)
                    <strong style="color:var(--color-warning-text);">{{ $missedCheckups }} missed checkup(s)</strong>
                @endif
                &nbsp;require your review and follow-up consultation.
            </div>
        </div>
        <a href="{{ route('midwife.pregnancies.index') }}" class="btn-hero-secondary flex-shrink-0">
            View High Risk <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
</div>
@endif

{{-- STAT CARDS : equal height, aligned --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6 d-flex">
        <div class="stat-card w-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon mb-0"><i class="bi bi-people-fill"></i></div>
                <span class="summary-chip chip-primary" style="font-size:0.72rem;">Registry</span>
            </div>
            <div class="stat-label">Total Patients</div>
            <div class="stat-number" data-count="{{ $totalPatients }}">0</div>
            <div class="stat-trend"><i class="bi bi-check-circle-fill" style="color:var(--color-success-text);"></i> Active Community Records</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 d-flex">
        <div class="stat-card w-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon mb-0" style="background:var(--color-success-soft) !important;color:var(--color-success-text) !important;"><i class="bi bi-heart-pulse-fill"></i></div>
                <span class="summary-chip chip-success" style="font-size:0.72rem;">Prenatal</span>
            </div>
            <div class="stat-label">Active Pregnancies</div>
            <div class="stat-number" data-count="{{ $activePregnancies }}">0</div>
            <div class="stat-trend"><i class="bi bi-activity" style="color:var(--color-success-text);"></i> Currently Monitoring</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 d-flex">
        <div class="stat-card w-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon mb-0"><i class="bi bi-calendar-check-fill"></i></div>
                <span class="summary-chip chip-primary" style="font-size:0.72rem;">Visits</span>
            </div>
            <div class="stat-label">Scheduled Checkups</div>
            <div class="stat-number" data-count="{{ $scheduledCheckups }}">0</div>
            <div class="stat-trend"><i class="bi bi-calendar3" style="color:var(--color-secondary-text);"></i> Upcoming Sessions</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 d-flex">
        <div class="stat-card w-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon mb-0" style="background:var(--color-danger-soft) !important;color:var(--color-danger-text) !important;"><i class="bi bi-shield-exclamation"></i></div>
                <span class="summary-chip chip-danger" style="font-size:0.72rem;">Triage</span>
            </div>
            <div class="stat-label">High Risk Cases</div>
            <div class="stat-number" data-count="{{ $highRiskPatients }}">0</div>
            <div class="stat-trend">
                <i class="bi {{ $highRiskPatients > 0 ? 'bi-exclamation-circle-fill' : 'bi-shield-check' }}" style="color:{{ $highRiskPatients > 0 ? 'var(--color-danger-text)' : 'var(--color-success-text)' }};"></i>
                {{ $highRiskPatients > 0 ? 'Requires Follow-up' : 'All Cases Stable' }}
            </div>
        </div>
    </div>
</div>

{{-- MAIN CONTENT GRID --}}
<div class="row g-4 align-items-start">

    {{-- LEFT: Recent Checkups Table Card --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;background:var(--color-secondary-soft);color:var(--color-secondary-text);">
                        <i class="bi bi-clipboard2-pulse"></i>
                    </div>
                    <div>
                        <h5>Recent Consultations &amp; Checkups</h5>
                        <small style="color:var(--color-text-muted);font-size:0.8rem;">Direct patient checkup records</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="d-none d-sm-flex align-items-center gap-1 p-1 rounded-pill" style="border:1px solid var(--color-border);background:var(--color-bg);">
                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold text-white" style="background:var(--color-secondary);border:none;font-size:0.75rem;" onclick="filterTableRows('all', this)">All</button>
                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 text-muted fw-bold" style="font-size:0.75rem;background:transparent;border:none;" onclick="filterTableRows('scheduled', this)">Scheduled</button>
                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 text-muted fw-bold" style="font-size:0.75rem;background:transparent;border:none;" onclick="filterTableRows('completed', this)">Completed</button>
                    </div>
                    <a href="{{ route('midwife.checkups.index') }}" class="btn btn-sm btn-primary">
                        View All <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if($recentCheckups->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="recent-checkups-table">
                            <thead>
                                <tr>
                                    <th class="text-start">Patient Name</th>
                                    <th class="text-start">Scheduled Date</th>
                                    <th class="text-start">Purpose / Care Stage</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentCheckups as $checkup)
                                    <tr data-status="{{ strtolower($checkup->status) }}">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;border-radius:10px;background:var(--color-secondary-soft);border:1px solid var(--color-secondary-soft);color:var(--color-secondary-text);font-size:0.82rem;font-weight:800;">
                                                    {{ strtoupper(substr(optional($checkup->user)->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div style="font-weight:700;color:var(--color-text);font-size:0.9rem;">
                                                        {{ optional($checkup->user)->name ?? 'Unlinked Patient' }}
                                                    </div>
                                                    <div style="font-size:0.76rem;color:var(--color-text-muted);">
                                                        {{ optional($checkup->user)->phone_number ?? 'No contact record' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="color:var(--color-text-muted);font-weight:500;white-space:nowrap;">
                                            <i class="bi bi-calendar-event me-1" style="color:var(--color-secondary-text);"></i>
                                            {{ $checkup->scheduled_date->format('M j, Y') }}
                                        </td>
                                        <td style="font-size:0.86rem;font-weight:600;color:var(--color-text);">
                                            {{ $checkup->purpose ?? 'Routine Prenatal Consultation' }}
                                        </td>
                                        <td class="text-end">
                                            <span class="status-{{ strtolower($checkup->status) }}">
                                                <i class="bi {{ strtolower($checkup->status) === 'completed' ? 'bi-check-circle-fill' : (strtolower($checkup->status) === 'scheduled' ? 'bi-clock-fill' : 'bi-dash-circle-fill') }}"></i>
                                                {{ ucfirst($checkup->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state text-center py-5">
                        <div class="d-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px;border-radius:50%;background:var(--color-secondary-soft);color:var(--color-secondary-text);font-size:1.6rem;">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                        <h6 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;color:var(--color-text);margin-bottom:0.35rem;">No Recent Checkups Found</h6>
                        <p style="color:var(--color-text-muted);font-size:0.88rem;max-width:320px;margin:0 auto 1.25rem;">
                            Schedule your next prenatal or postnatal consultation session.
                        </p>
                        <a href="{{ route('midwife.checkups.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Schedule Checkup
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN: Alerts & Quick Actions --}}
    <div class="col-lg-4">
        {{-- Missed Checkups Box --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5>Missed Consultations</h5>
                @if($missedCheckups > 0)
                <span class="badge" style="background:var(--color-secondary-soft);color:var(--color-secondary-text);font-size:0.75rem;padding:0.35em 0.7em;border-radius:8px;font-weight:700;">
                    {{ $missedCheckups }}
                </span>
                @endif
            </div>
            <div class="card-body">
                @if($missedCheckups > 0)
                    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-exclamation-circle-fill flex-shrink-0 mt-1"></i>
                        <span style="font-size:0.86rem;"><strong>{{ $missedCheckups }}</strong> patient(s) missed their scheduled prenatal appointments. Follow-up SMS recommended.</span>
                    </div>
                    <a href="{{ route('midwife.checkups.index') }}" class="btn btn-primary w-100">
                        <i class="bi bi-eye me-1"></i> View Missed Checkups
                    </a>
                @else
                    <div class="empty-state text-center py-4">
                        <i class="bi bi-check-circle-fill" style="font-size:2.4rem;color:var(--color-success-text);display:block;margin-bottom:0.6rem;"></i>
                        <h6 style="color:var(--color-success-text);font-weight:700;font-family:'Plus Jakarta Sans',sans-serif;">All Clear &amp; On Track!</h6>
                        <p class="mb-0" style="font-size:0.84rem;color:var(--color-text-muted);">No missed consultations recorded for today.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header">
                <h5>Midwife Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6 d-flex">
                        <a href="{{ route('midwife.health-records.create') }}" class="quick-action-tile qa-teal w-100">
                            <i class="bi bi-clipboard2-plus-fill"></i>
                            <span>Health Record</span>
                        </a>
                    </div>
                    <div class="col-6 d-flex">
                        <a href="{{ route('midwife.pregnancies.create') }}" class="quick-action-tile qa-pink w-100">
                            <i class="bi bi-heart-fill"></i>
                            <span>Record Pregnancy</span>
                        </a>
                    </div>
                    <div class="col-6 d-flex">
                        <a href="{{ route('midwife.learning.create') }}" class="quick-action-tile qa-amber w-100">
                            <i class="bi bi-book-half"></i>
                            <span>Add Class / Learning</span>
                        </a>
                    </div>
                    <div class="col-6 d-flex">
                        <a href="{{ route('midwife.notifications.create') }}" class="quick-action-tile qa-red w-100">
                            <i class="bi bi-bell-fill"></i>
                            <span>Send Alert / SMS</span>
                        </a>
                    </div>
                    <div class="col-6 d-flex">
                        <a href="{{ route('midwife.patients') }}" class="quick-action-tile qa-blue w-100">
                            <i class="bi bi-people-fill"></i>
                            <span>All Women / Patients</span>
                        </a>
                    </div>
                    <div class="col-6 d-flex">
                        <a href="{{ route('midwife.reports.index') }}" class="quick-action-tile qa-violet w-100">
                            <i class="bi bi-file-earmark-bar-graph-fill"></i>
                            <span>Clinical Reports</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
// Animate stat number counters
function animateStatNumbers() {
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const target = parseInt(stat.dataset.count);
        if (target > 0) {
            let current = 0;
            const increment = Math.max(1, Math.ceil(target / 30));
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = target;
                    clearInterval(timer);
                } else {
                    stat.textContent = current;
                }
            }, 25);
        } else {
            stat.textContent = '0';
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    animateStatNumbers();
});

function filterTableRows(status, btn) {
    btn.parentElement.querySelectorAll('button').forEach(b => {
        b.classList.remove('text-white');
        b.classList.add('text-muted');
        b.style.background = 'transparent';
        b.style.color = '';
    });
    btn.classList.remove('text-muted');
    btn.classList.add('text-white');
    btn.style.background = 'var(--color-primary)';
    btn.style.color = 'var(--color-white)';
    document.querySelectorAll('#recent-checkups-table tbody tr').forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endpush

@endsection
