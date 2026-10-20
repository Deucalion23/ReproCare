@extends('cho.layout')

@php
    $displayName = $woman->full_name;
@endphp

@section('title', $displayName . ' - CHO Portal | ReproCare')

@push('styles')
<style>
    .rec-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    .rec-label { font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-text-muted); margin-bottom:2px; }
    .rec-table table thead th { border:none !important; background:transparent !important; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-muted) !important; }
    .rec-table table tbody td { border:none !important; }
    .status-pill { font-size:0.72rem; font-weight:800; padding:0.25em 0.8em; border-radius:999px; }
    .status-unregistered { background:var(--color-warning-soft, #fff7e6); color:var(--color-warning-text, #8a5a00); }
    .status-approved { background:var(--color-success-soft); color:var(--color-success-text); }
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
        <a href="{{ route('cho.patients.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to All Women
        </a>
    </div>
</div>

<div class="card fade-in-card mb-4 rec-card">
    <div class="card-header fw-bold">Profile &amp; Contact</div>
    <div class="card-body">
        <div class="row g-3" style="font-size:0.875rem;">
            <div class="col-md-4">
                <div class="rec-label">Record type</div>
                <span class="status-pill status-unregistered">Unregistered · Field-only</span>
            </div>
            <div class="col-md-4"><div class="rec-label">Date of birth</div><div>{{ $woman->date_of_birth ? $woman->date_of_birth->format('M d, Y') . ' (' . $woman->age . ' y/o)' : 'N/A' }}</div></div>
            <div class="col-md-4"><div class="rec-label">Gender</div><div>{{ $woman->gender ? ucfirst($woman->gender) : 'N/A' }}</div></div>
            <div class="col-md-4"><div class="rec-label">Contact number</div><div>{{ $woman->contact_number ?: 'Not provided' }}</div></div>
            <div class="col-md-4"><div class="rec-label">Barangay</div><div>{{ $woman->barangay ?: 'N/A' }}</div></div>
            <div class="col-md-4"><div class="rec-label">Purok</div><div>{{ $woman->purok?->name ?: 'Not assigned' }}</div></div>
            <div class="col-md-6"><div class="rec-label">Address</div><div>{{ $woman->address ?: 'Not provided' }}</div></div>
            <div class="col-md-6"><div class="rec-label">Reason for visit</div><div>{{ $woman->reason_for_visit ?: 'No reason recorded' }}</div></div>
            <div class="col-md-6"><div class="rec-label">Recorded by</div><div>{{ $woman->recordedBy?->name ?? 'Unknown' }}</div></div>
            <div class="col-md-6"><div class="rec-label">Recorded</div><div>{{ $woman->created_at?->format('M d, Y') ?? 'N/A' }}</div></div>
            <div class="col-12"><div class="rec-label">Notes</div><div>{{ $woman->notes ?: 'No notes recorded' }}</div></div>
        </div>
    </div>
</div>

<div class="card fade-in-card mb-4 rec-card rec-table">
    <div class="card-header fw-bold">Pregnancies ({{ $pregnancies->count() }})</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
            <thead class="table-light"><tr><th class="px-3 py-3">LMP</th><th class="py-3">EDD</th><th class="py-3">Risk</th><th class="py-3">Outcome / Status</th></tr></thead>
            <tbody>
                @forelse($pregnancies as $pregnancy)
                    <tr>
                        <td class="px-3">{{ $pregnancy->lmp ? $pregnancy->lmp->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $pregnancy->edd ? $pregnancy->edd->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $pregnancy->risk_level ?? 'N/A' }}{{ $pregnancy->is_high_risk ? ' (high-risk)' : '' }}</td>
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
        <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
            <thead class="table-light"><tr><th class="px-3 py-3">Date</th><th class="py-3">Purpose</th><th class="py-3">Midwife</th><th class="py-3">Status</th></tr></thead>
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
        <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
            <thead class="table-light"><tr><th class="px-3 py-3">Recorded</th><th class="py-3">BP</th><th class="py-3">Weight</th><th class="py-3">Recorded by</th></tr></thead>
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
        <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
            <thead class="table-light"><tr><th class="px-3 py-3">Reason</th><th class="py-3">Urgency</th><th class="py-3">Status</th><th class="py-3">Date</th></tr></thead>
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
