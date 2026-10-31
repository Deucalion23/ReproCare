@extends('cho.layout')

@section('title', 'Patient Record - CHO Portal | ReproCare')

@push('styles')
<style>
    .rec-card { border:none !important; border-radius:16px !important; box-shadow:0 2px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent) !important; overflow:hidden; margin-bottom:0.65rem !important; }
    .rec-card .card-header { border:none !important; background:transparent !important; font-weight:800; font-size:0.75rem; padding:0.45rem 1rem !important; }
    .rec-card .card-body { padding:0.55rem 1rem !important; }
    .rec-table .card-body { padding:0 !important; }
    .rec-table table { font-size:0.7rem !important; margin-bottom:0 !important; }
    .rec-table table thead th { border:none !important; background:transparent !important; font-size:0.62rem !important; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-text-muted) !important; padding:0.35rem 0.5rem !important; }
    .rec-table table tbody td { border:none !important; padding:0.35rem 0.5rem !important; font-size:0.7rem; }
    .rec-label { font-size:0.65rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--color-text-muted); margin-bottom:2px; line-height:1; }
    .status-pill { font-size:0.65rem; font-weight:800; padding:0.15em 0.5em; border-radius:999px; line-height:1; }
    .status-approved { background:var(--color-success-soft); color:var(--color-success-text); }
    .status-other { background:var(--color-surface-soft); color:var(--color-text-muted); }
    .page-hero { padding:1rem 1.25rem !important; margin-bottom:0.75rem !important; }
    .page-hero-title { font-size:1.2rem !important; margin-bottom:0.2rem !important; }
    .page-hero-subtitle { font-size:0.78rem !important; margin:0 !important; }
    .id-img { width:100%; max-height:120px; object-fit:contain; border-radius:10px; border:1px solid var(--color-border); background:var(--color-surface-soft); }
    .text-muted { font-size:0.75rem !important; }
    .record-summary .summary-item { min-height:54px; padding:0.35rem 0.65rem; border-right:1px solid var(--color-border); }
    .record-summary .summary-item:last-child { border-right:0; }
    .summary-value { font-size:0.76rem; font-weight:700; line-height:1.3; }
    .profile-grid { display:grid; grid-template-columns:repeat(12, minmax(0, 1fr)); gap:0.9rem 1.25rem; }
    .profile-field { min-width:0; }
    .profile-field .profile-value { font-size:0.78rem; line-height:1.45; overflow-wrap:anywhere; }
    .profile-email { grid-column:span 6; }
    .profile-contact, .profile-status { grid-column:span 3; }
    .profile-birth, .profile-gender, .profile-barangay, .profile-partner { grid-column:span 3; }
    .profile-address, .profile-emergency { grid-column:span 6; }
    @media (max-width: 991.98px) {
        .profile-email, .profile-contact, .profile-status, .profile-birth, .profile-gender, .profile-barangay, .profile-partner, .profile-address, .profile-emergency { grid-column:span 6; }
    }
    @media (max-width: 575.98px) {
        .profile-grid { grid-template-columns:1fr; gap:0.8rem; }
        .profile-email, .profile-contact, .profile-status, .profile-birth, .profile-gender, .profile-barangay, .profile-partner, .profile-address, .profile-emergency { grid-column:span 1; }
    }
    @media (max-width: 767.98px) { .record-summary .summary-item { border-right:0; border-bottom:1px solid var(--color-border); } .record-summary .summary-item:last-child { border-bottom:0; } }
</style>
@endpush

@section('cho-content')

@php
    $latestPregnancy = $pregnancies->first();
    $latestCheckup = $checkups->first();
    $latestHealthRecord = $healthRecords->first();
@endphp

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">{{ $woman->first_name }} {{ $woman->last_name }}</div>
            <p class="page-hero-subtitle" style="font-weight:600;">Patient record file · ID #{{ $woman->id }} · read-only oversight.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if(($woman->status ?? '') !== 'archived')
                <form method="POST" action="{{ route('cho.patients.archive', $woman->id) }}" onsubmit="return choPromptArchiveReason(this);">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-sm" style="padding:0.3rem 0.7rem;font-size:0.75rem;">
                        <i class="bi bi-archive me-1"></i> Archive
                    </button>
                </form>
            @endif
            <a href="{{ route('cho.patients.index') }}" class="btn btn-outline-primary btn-sm" style="padding:0.3rem 0.7rem;font-size:0.75rem;">Back to directory</a>
        </div>
    </div>
</div>

{{-- A screenshot-friendly clinical overview; full histories remain below. --}}
<div class="card fade-in-card rec-card record-summary">
    <div class="card-header">Care at a Glance</div>
    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-md-4 summary-item">
                <div class="rec-label">Latest pregnancy</div>
                @if($latestPregnancy)
                    <div class="summary-value">{{ $latestPregnancy->risk_level ?? 'Unassessed' }} risk · {{ $latestPregnancy->ended_at ? 'Ended' : 'Active' }}</div>
                    <div class="text-muted">LMP {{ $latestPregnancy->lmp?->format('M d, Y') ?? 'N/A' }} · EDD {{ $latestPregnancy->edd?->format('M d, Y') ?? 'N/A' }}</div>
                @else
                    <div class="summary-value">No pregnancy record</div>
                @endif
            </div>
            <div class="col-md-4 summary-item">
                <div class="rec-label">Latest checkup</div>
                @if($latestCheckup)
                    <div class="summary-value">{{ $latestCheckup->scheduled_date?->format('M d, Y') ?? 'Date not set' }} · {{ $latestCheckup->status }}</div>
                    <div class="text-muted">{{ $latestCheckup->midwife?->name ?? 'No midwife assigned' }}</div>
                @else
                    <div class="summary-value">No checkup scheduled</div>
                @endif
            </div>
            <div class="col-md-4 summary-item">
                <div class="rec-label">Latest vital signs</div>
                @if($latestHealthRecord)
                    <div class="summary-value">BP {{ $latestHealthRecord->bp ?? 'N/A' }} · {{ $latestHealthRecord->weight ?? 'N/A' }} kg</div>
                    <div class="text-muted">{{ $latestHealthRecord->created_at?->format('M d, Y') ?? 'Date not recorded' }}</div>
                @else
                    <div class="summary-value">No health record</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Profile --}}
<div class="card fade-in-card rec-card mb-2">
    <div class="card-header">Profile &amp; Contact</div>
    <div class="card-body">
        <div class="profile-grid">
            <div class="profile-field profile-email"><div class="rec-label">Email</div><div class="profile-value">{{ $woman->email }}</div></div>
            <div class="profile-field profile-contact"><div class="rec-label">Contact</div><div class="profile-value">{{ $woman->display_contact_number }}</div></div>
            <div class="profile-field profile-status"><div class="rec-label">Status</div><div class="profile-value"><span class="status-pill {{ ($woman->status ?? 'approved') === 'approved' ? 'status-approved' : 'status-other' }}">{{ ucfirst($woman->status ?? 'approved') }}</span></div></div>
            <div class="profile-field profile-birth"><div class="rec-label">Birth / Age</div><div class="profile-value">{{ $woman->date_of_birth ? $woman->date_of_birth->format('M d, Y') . ' · ' . $woman->age . ' y/o' : 'N/A' }}</div></div>
            <div class="profile-field profile-gender"><div class="rec-label">Gender</div><div class="profile-value">{{ $woman->gender ? ucfirst($woman->gender) : 'N/A' }}</div></div>
            <div class="profile-field profile-barangay"><div class="rec-label">Barangay</div><div class="profile-value">{{ $woman->display_barangay }}</div></div>
            <div class="profile-field profile-partner"><div class="rec-label">Partner</div><div class="profile-value">{{ $woman->partner_name ?? 'N/A' }}{{ $woman->partner_contact ? ' · ' . $woman->partner_contact : '' }}</div></div>
            <div class="profile-field profile-address"><div class="rec-label">Address</div><div class="profile-value">{{ $woman->display_address }}</div></div>
            <div class="profile-field profile-emergency"><div class="rec-label">Emergency contacts</div><div class="profile-value">
                @forelse($woman->emergencyContacts as $contact)
                    <div>{{ $contact->name }} ({{ $contact->relationship }}) · {{ $contact->contact_number }}</div>
                @empty
                    <div class="text-muted">None recorded.</div>
                @endforelse
            </div></div>
        </div>
    </div>
</div>

{{-- Valid ID (captured/uploaded at signup; DB copy survives deploys) --}}
@if($woman->hasIdImages())
<div class="card fade-in-card rec-card mb-2">
    <div class="card-header">Valid ID Documents</div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <div class="rec-label mb-1">Front of ID</div>
                @if($woman->id_image_front_url)
                    <a href="{{ $woman->id_image_front_url }}" target="_blank" rel="noopener" title="Open full-size front of ID">
                        <img src="{{ $woman->id_image_front_url }}" alt="Front of valid ID" class="id-img">
                    </a>
                @else
                    <div class="text-muted">Front not on file.</div>
                @endif
            </div>
            <div class="col-12 col-md-6">
                <div class="rec-label mb-1">Back of ID</div>
                @if($woman->id_image_back_url)
                    <a href="{{ $woman->id_image_back_url }}" target="_blank" rel="noopener" title="Open full-size back of ID">
                        <img src="{{ $woman->id_image_back_url }}" alt="Back of valid ID" class="id-img">
                    </a>
                @else
                    <div class="text-muted">Back not on file.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- Pregnancies --}}
<div class="card fade-in-card rec-card rec-table mb-2">
    <div class="card-header">Pregnancies ({{ $pregnancies->count() }})</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
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
<div class="card fade-in-card rec-card rec-table mb-2">
    <div class="card-header">Checkups ({{ $checkups->count() }})</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
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
<div class="card fade-in-card rec-card rec-table mb-2">
    <div class="card-header">Recent Health Records (latest {{ $healthRecords->count() }})</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
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

@push('scripts')
<script>
    // Archive guardrail: require a reason so the audit trail records who / when / why.
    function choPromptArchiveReason(form) {
        const reason = prompt('Reason for archiving this record? (e.g. duplicate, test data, moved away)');
        if (reason === null) return false;
        if (reason.trim() === '') { alert('A reason for archiving is required.'); return false; }
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'reason'; input.value = reason.trim();
        form.appendChild(input);
        return confirm('Archive this patient record? Sessions will be revoked immediately. It stays restorable from Archived Records.');
    }
</script>
@endpush
