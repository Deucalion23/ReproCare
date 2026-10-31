@extends('cho.layout')

@php
    $displayName = $woman->full_name;
@endphp

@section('title', $displayName . ' - CHO Portal | ReproCare')

@push('styles')
<style>
    .rec-card { border:none !important; border-radius:16px !important; box-shadow:0 2px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent) !important; overflow:hidden; margin-bottom:0.75rem !important; }
    .rec-card > .card-header { background:transparent !important; border:none !important; font-weight:800 !important; font-size:0.78rem; padding:0.55rem 1rem 0.3rem; }
    .rec-card > .card-body { padding:0.3rem 1rem 0.7rem; }
    .rec-field { padding:0.25rem 0; }
    .rec-label { font-size:0.63rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-text-muted); margin-bottom:2px; }
    .rec-value { font-size:0.78rem; font-weight:500; color:var(--color-text); line-height:1.3; }
    .rec-back { display:inline-flex; align-items:center; gap:0.4rem; border-radius:999px; font-weight:700; font-size:0.82rem; padding:0.45rem 1.05rem; background:var(--color-surface); border:1px solid var(--color-border); color:var(--color-text); text-decoration:none; box-shadow:0 2px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent); }
    .rec-back:hover { border-color:var(--color-primary); color:var(--color-primary-text); background:var(--color-surface); }
    .rec-table table { font-size:0.72rem; }
    .rec-table table thead th { border:none !important; background:transparent !important; font-size:0.62rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-text-muted) !important; padding:0.4rem 0.65rem !important; }
    .rec-table table tbody td { border:none !important; padding:0.4rem 0.65rem !important; }
    .rec-table table tbody tr:hover { background:var(--color-bg); }
    .rec-table .table-responsive { padding:0 0.25rem 0.35rem; }
    .status-pill { font-size:0.72rem; font-weight:800; padding:0.25em 0.8em; border-radius:999px; display:inline-block; }
    .status-unregistered { background:var(--color-warning-soft, #fff7e6); color:var(--color-warning-text, #8a5a00); }
    .risk-pill { font-size:0.7rem; font-weight:800; padding:0.22em 0.7em; border-radius:999px; display:inline-block; white-space:nowrap; }
    .risk-Critical, .risk-High { background:var(--color-danger-soft); color:var(--color-danger-text); }
    .risk-Medium { background:var(--color-warning-soft, #fff7e6); color:var(--color-warning-text, #8a5a00); }
    .risk-Low { background:var(--color-success-soft); color:var(--color-success-text); }
    .risk-Unassessed { background:var(--color-surface-soft); color:var(--color-text-muted); }
    @media (max-width: 767.98px) {
        .rec-card > .card-body { padding:0.35rem 1rem 1rem; }
        .rec-table table { font-size:0.78rem; }
        .rec-table table thead th, .rec-table table tbody td { padding:0.55rem 0.6rem !important; }
    }
</style>
@endpush

@section('cho-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <x-patient-avatar :patient="$woman" :name="$displayName" :size="58" />
            <div>
                <div class="page-hero-title">{{ $displayName }}</div>
                <p class="page-hero-subtitle" style="font-weight:600;">
                    Unregistered field profile · {{ $woman->barangay ?: 'Barangay not recorded' }} · no portal account.
                </p>
            </div>
        </div>
        <a href="{{ route('cho.patients.index') }}" class="rec-back">
            <i class="bi bi-arrow-left"></i> All Women
        </a>
    </div>
</div>

<div class="card fade-in-card mb-4 rec-card">
    <div class="card-header fw-bold">Profile &amp; Contact</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-4 rec-field">
                <div class="rec-label">Record type</div>
                <span class="status-pill status-unregistered">Unregistered · Field-only</span>
            </div>
            <div class="col-6 col-md-4 rec-field">
                <div class="rec-label">Date of birth</div>
                <div class="rec-value">{{ $woman->date_of_birth ? $woman->date_of_birth->format('M d, Y') . ' (' . $woman->age . ' y/o)' : 'N/A' }}</div>
            </div>
            <div class="col-6 col-md-4 rec-field">
                <div class="rec-label">Gender</div>
                <div class="rec-value">{{ $woman->gender ? ucfirst($woman->gender) : 'N/A' }}</div>
            </div>
            <div class="col-6 col-md-4 rec-field">
                <div class="rec-label">Contact number</div>
                <div class="rec-value">{{ $woman->contact_number ?: 'Not provided' }}</div>
            </div>
            <div class="col-6 col-md-4 rec-field">
                <div class="rec-label">Barangay</div>
                <div class="rec-value">{{ $woman->barangay ?: 'N/A' }}</div>
            </div>
            <div class="col-6 col-md-4 rec-field">
                <div class="rec-label">Purok</div>
                <div class="rec-value">{{ $woman->purok?->name ?: 'Not assigned' }}</div>
            </div>
            <div class="col-6 col-md-4 rec-field">
                <div class="rec-label">Recorded by</div>
                <div class="rec-value">{{ $woman->recordedBy?->name ?? 'Unknown' }}</div>
            </div>
            <div class="col-6 col-md-4 rec-field">
                <div class="rec-label">Recorded</div>
                <div class="rec-value">{{ $woman->created_at?->format('M d, Y') ?? 'N/A' }}</div>
            </div>
            <div class="col-12 rec-field">
                <div class="rec-label">Address</div>
                <div class="rec-value">{{ $woman->address ?: 'Not provided' }}</div>
            </div>
            <div class="col-12 rec-field">
                <div class="rec-label">Reason for visit</div>
                <div class="rec-value">{{ $woman->reason_for_visit ?: 'No reason recorded' }}</div>
            </div>
            <div class="col-12 rec-field">
                <div class="rec-label">Notes</div>
                <div class="rec-value">{{ $woman->notes ?: 'No notes recorded' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card fade-in-card mb-4 rec-card rec-table">
    <div class="card-header fw-bold">Pregnancies ({{ $pregnancies->count() }})</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th class="px-3">LMP</th><th>EDD</th><th>Risk</th><th>Outcome / Status</th></tr></thead>
            <tbody>
                @forelse($pregnancies as $pregnancy)
                    <tr>
                        <td class="px-3">{{ $pregnancy->lmp ? $pregnancy->lmp->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $pregnancy->edd ? $pregnancy->edd->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            @php $risk = $pregnancy->risk_level ?? 'Unassessed'; @endphp
                            <span class="risk-pill risk-{{ $risk }}">{{ $risk }}</span>
                            @if($pregnancy->is_high_risk)
                                <span class="risk-pill risk-High">High-risk</span>
                            @endif
                        </td>
                        <td>{{ $pregnancy->outcome ?? ($pregnancy->ended_at ? 'Ended ' . $pregnancy->ended_at->format('M d, Y') : 'Active') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">No pregnancies recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card fade-in-card mb-4 rec-card rec-table">
    <div class="card-header fw-bold">Checkups ({{ $checkups->count() }})</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th class="px-3">Date</th><th>Purpose</th><th>Midwife</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($checkups as $checkup)
                    <tr>
                        <td class="px-3">{{ $checkup->scheduled_date ? $checkup->scheduled_date->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $checkup->purpose }}</td>
                        <td>{{ $checkup->midwife?->name ?? 'N/A' }}</td>
                        <td>{{ $checkup->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">No checkups recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card fade-in-card mb-4 rec-card rec-table">
    <div class="card-header fw-bold">Recent Health Records (latest {{ $healthRecords->count() }})</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th class="px-3">Recorded</th><th>BP</th><th>Weight</th><th>Recorded by</th></tr></thead>
            <tbody>
                @forelse($healthRecords as $record)
                    <tr>
                        <td class="px-3">{{ $record->created_at ? $record->created_at->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $record->bp ?? 'N/A' }}</td>
                        <td>{{ $record->weight ?? 'N/A' }}</td>
                        <td>{{ $record->recordedBy?->name ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">No health records.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card fade-in-card mb-4 rec-card rec-table">
    <div class="card-header fw-bold">Referral History ({{ $referrals->count() }})</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th class="px-3">Reason</th><th>Urgency</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($referrals as $referral)
                    <tr>
                        <td class="px-3">{{ $referral->reason }}</td>
                        <td>{{ ucfirst($referral->urgency) }}</td>
                        <td>{{ ucfirst($referral->status) }}</td>
                        <td>{{ $referral->created_at?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">No referrals recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
