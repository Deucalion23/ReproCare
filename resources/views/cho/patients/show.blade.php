@extends('cho.layout')

@section('title', 'Patient Record - CHO Portal | ReproCare')

@push('styles')
<style>
    .rec-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    .rec-card .card-header { border:none !important; background:transparent !important; font-weight:800; }
    .rec-table table thead th { border:none !important; background:transparent !important; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-muted) !important; }
    .rec-table table tbody td { border:none !important; }
    .rec-label { font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-text-muted); margin-bottom:2px; }
    .status-pill { font-size:0.72rem; font-weight:800; padding:0.25em 0.8em; border-radius:999px; }
    .status-approved { background:var(--color-success-soft); color:var(--color-success-text); }
    .status-other { background:var(--color-surface-soft); color:var(--color-text-muted); }
</style>
@endpush

@section('cho-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">{{ $woman->first_name }} {{ $woman->last_name }}</div>
            <p class="page-hero-subtitle" style="font-weight:600;">Patient record file · ID #{{ $woman->id }} · read-only oversight.</p>
        </div>
        <a href="{{ route('cho.patients.index') }}" class="btn btn-outline-primary btn-sm">Back to directory</a>
    </div>
</div>

{{-- Profile --}}
<div class="card fade-in-card mb-4 rec-card">
    <div class="card-header">Profile &amp; Contact</div>
    <div class="card-body">
        <div class="row g-3" style="font-size:0.875rem;">
            <div class="col-md-4"><div class="rec-label">Email</div><div>{{ $woman->email }}</div></div>
            <div class="col-md-4"><div class="rec-label">Contact number</div><div>{{ $woman->display_contact_number }}</div></div>
            <div class="col-md-4"><div class="rec-label">Status</div><div><span class="status-pill {{ ($woman->status ?? 'approved') === 'approved' ? 'status-approved' : 'status-other' }}">{{ ucfirst($woman->status ?? 'approved') }}</span></div></div>
            <div class="col-md-4"><div class="rec-label">Date of birth</div><div>{{ $woman->date_of_birth ? $woman->date_of_birth->format('M d, Y') . ' (' . $woman->age . ' y/o)' : 'N/A' }}</div></div>
            <div class="col-md-4"><div class="rec-label">Gender</div><div>{{ $woman->gender ? ucfirst($woman->gender) : 'N/A' }}</div></div>
            <div class="col-md-4"><div class="rec-label">Barangay</div><div>{{ $woman->display_barangay }}</div></div>
            <div class="col-md-8"><div class="rec-label">Address</div><div>{{ $woman->display_address }}</div></div>
            <div class="col-md-4"><div class="rec-label">Partner</div><div>{{ $woman->partner_name ?? 'N/A' }}{{ $woman->partner_contact ? ' · ' . $woman->partner_contact : '' }}</div></div>
            <div class="col-md-12"><div class="rec-label">Emergency contacts</div>
                @forelse($woman->emergencyContacts as $contact)
                    <div>{{ $contact->name }} ({{ $contact->relationship }}) · {{ $contact->contact_number }}</div>
                @empty
                    <div class="text-muted">None recorded.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Pregnancies --}}
<div class="card fade-in-card mb-4 rec-card rec-table">
    <div class="card-header">Pregnancies ({{ $pregnancies->count() }})</div>
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

{{-- Checkups --}}
<div class="card fade-in-card mb-4 rec-card rec-table">
    <div class="card-header">Checkups ({{ $checkups->count() }})</div>
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

{{-- Health records --}}
<div class="card fade-in-card mb-4 rec-card rec-table">
    <div class="card-header">Recent Health Records (latest {{ $healthRecords->count() }})</div>
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
@endsection
