@extends('rhu.layout')

@section('title', 'Dashboard - RHU Portal | ReproCare')

@section('rhu-content')

@php
    $hour = now()->hour;
    $timeOfDay = $hour < 12 ? 'Morning' : ($hour < 17 ? 'Afternoon' : 'Evening');
@endphp

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

    <div class="d-flex flex-wrap gap-2 mt-3" style="position:relative;z-index:1;">
        <span class="summary-chip chip-primary" style="padding:0.35rem 0.75rem; border-radius:20px; font-size:0.8rem; font-weight:600;">
            <i class="bi bi-shield-check me-1"></i> ANC Coverage: {{ $ancCoverageRate }}%
        </span>
        <span class="summary-chip chip-success" style="padding:0.35rem 0.75rem; border-radius:20px; font-size:0.8rem; font-weight:600;">
            <i class="bi bi-people-fill me-1"></i> {{ $totalPatients }} Patients
        </span>
        @if($highRiskPatients > 0)
        <span class="summary-chip chip-danger" style="background:color-mix(in srgb, var(--color-danger) 25%, transparent); border:1px solid color-mix(in srgb, var(--color-danger) 40%, transparent); color:var(--color-danger-text); padding:0.35rem 0.75rem; border-radius:20px; font-size:0.8rem; font-weight:600;">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $highRiskPatients }} High-Risk Pregnancies
        </span>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-purple fade-in-card">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-label">Supervised Patients</div>
            <div class="stat-number" data-count="{{ $totalPatients }}">0</div>
            <div class="stat-trend">
                <i class="bi bi-activity"></i> Active maternal records
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-rose fade-in-card">
            <div class="stat-icon"><i class="bi bi-heart-pulse-fill"></i></div>
            <div class="stat-label">Active Pregnancies</div>
            <div class="stat-number" data-count="{{ $activePregnancies }}">0</div>
            <div class="stat-trend danger">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ $highRiskPatients }} high risk cases
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-cyan fade-in-card">
            <div class="stat-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div class="stat-label">BHW Monthly Reports</div>
            <div class="stat-number" data-count="{{ $pendingBhwReports }}">0</div>
            <div class="stat-trend up">
                <i class="bi bi-hourglass-split"></i> Pending midwife review
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-danger fade-in-card">
            <div class="stat-icon"><i class="bi bi-journal-x"></i></div>
            <div class="stat-label">Maternal Deaths</div>
            <div class="stat-number" data-count="{{ $maternalDeathsCount }}">0</div>
            <div class="stat-trend danger">
                <i class="bi bi-activity"></i> {{ $nearMissCount }} near-miss morbidities
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left column --}}
    <div class="col-lg-8">
        {{-- Supply Requests --}}
        <div class="card fade-in-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif;">Recent Supply Requests
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('rhu.supply-requests.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> New Request
                    </a>
                    <a href="{{ route('rhu.supply-requests.index') }}" class="btn btn-sm btn-outline-secondary">
                        View All
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if($recentSupplyRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Urgency</th>
                                    <th>Status</th>
                                    <th>Submitted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSupplyRequests as $req)
                                    <tr>
                                        <td>
                                            <span class="fw-600">{{ $req->supply_name }}</span>
                                            <div style="font-size:0.75rem; color:var(--text-muted);">{{ ucfirst($req->supply_category) }}</div>
                                        </td>
                                        <td>{{ $req->quantity_requested }} {{ $req->unit ?? 'pcs' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $req->urgency === 'emergency' ? 'danger' : ($req->urgency === 'urgent' ? 'warning' : 'info') }} text-white text-xs">
                                                {{ ucfirst($req->urgency) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $req->status === 'approved' ? 'success' : ($req->status === 'declined' ? 'danger' : 'secondary') }} text-white text-xs">
                                                {{ ucfirst($req->status) }}
                                            </span>
                                        </td>
                                        <td style="font-size:0.8rem; color:var(--text-muted);">
                                            {{ $req->submitted_at ? $req->submitted_at->format('M j, Y') : 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-box-seam" style="font-size:2rem; color:var(--text-muted);"></i>
                        <h6 class="mt-2">No Supply Requests</h6>
                        <p class="text-muted text-xs">You have not submitted any supply requests yet.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Morbidities / Near-Miss events --}}
        <div class="card fade-in-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif;">Recent Near-Miss / Morbidities
                </h5>
                <a href="{{ route('rhu.morbidities.index') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                @if($recentMorbidities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Complication</th>
                                    <th>Date</th>
                                    <th>Outcome</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentMorbidities as $morb)
                                    <tr>
                                        <td>
                                            <span class="fw-600">{{ $morb->patient_name }}</span>
                                            <div style="font-size:0.75rem; color:var(--text-muted);">{{ $morb->barangay }}</div>
                                        </td>
                                        <td style="font-size:0.875rem;">
                                            {{ str_replace('_', ' ', ucfirst($morb->complication_type)) }}
                                        </td>
                                        <td style="font-size:0.8rem; color:var(--text-muted);">
                                            {{ $morb->event_date->format('M j, Y') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $morb->outcome === 'died' ? 'danger' : 'success' }} text-white text-xs">
                                                {{ str_replace('_', ' ', ucfirst($morb->outcome)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-heart-pulse" style="font-size:2rem; color:var(--text-muted);"></i>
                        <h6 class="mt-2">No Near-Miss Cases</h6>
                        <p class="text-muted text-xs">No maternal morbidity events have been logged.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div class="col-lg-4">
        {{-- Quick Links --}}
        <div class="card fade-in-card mb-4">
            <div class="card-header">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif;">Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('rhu.midwives.create') }}" class="btn btn-outline-primary text-start">
                        <i class="bi bi-person-plus-fill me-2"></i> Register New Midwife
                    </a>
                    <a href="{{ route('rhu.bhw-presidents.create') }}" class="btn btn-outline-primary text-start">
                        <i class="bi bi-person-badge-fill me-2"></i> Appoint BHW President
                    </a>
                    <a href="{{ route('rhu.maternal-deaths.create') }}" class="btn btn-outline-danger text-start">
                        <i class="bi bi-journal-x me-2"></i> Record Maternal Mortality
                    </a>
                    <a href="{{ route('rhu.morbidities.create') }}" class="btn btn-outline-warning text-start">
                        <i class="bi bi-heart-pulse-fill me-2"></i> Record Morbidity Event
                    </a>
                    <a href="{{ route('rhu.reports.index') }}" class="btn btn-outline-success text-start">
                        <i class="bi bi-file-earmark-bar-graph-fill me-2"></i> FHSIS Reports Dashboard
                    </a>
                </div>
            </div>
        </div>

        {{-- Recent Maternal Deaths --}}
        <div class="card fade-in-card">
            <div class="card-header">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif;">Maternal Deaths
                </h5>
            </div>
            <div class="card-body p-0">
                @if($recentDeaths->count() > 0)
                    <ul class="list-group list-group-flush mb-0">
                        @foreach($recentDeaths as $death)
                            <li class="list-group-item bg-transparent" style="border-color:var(--border); padding:0.85rem 1.25rem;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div style="font-weight:600; font-size:0.875rem;">
                                            {{ $death->patient_name }}
                                        </div>
                                        <div style="font-size:0.75rem; color:var(--text-muted);">
                                            {{ $death->death_date->format('M j, Y') }} &bull; {{ $death->age_at_death ?? 'N/A' }} yrs old
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ $death->audit_status === 'closed' ? 'success' : 'warning' }} text-white text-xs">
                                        {{ ucfirst($death->audit_status) }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-shield-check" style="font-size:2rem; color:var(--success);"></i>
                        <p class="text-muted text-xs mt-2 mb-0">No maternal deaths recorded.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function animateStatNumbers() {
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
        } else {
            stat.textContent = 0;
        }
    });
}
document.addEventListener('DOMContentLoaded', animateStatNumbers);
</script>
@endpush

@endsection
