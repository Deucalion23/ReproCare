@extends('rhu.layout')

@section('title', 'Health Record #' . $record->id . ' - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="flex-1">
            <div class="page-hero-title">Health Record #{{ $record->id }}</div>
            <p class="page-hero-subtitle">{{ $record->patient_name }} · {{ $record->woman?->barangay ?? $record->walkInPatient?->barangay ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('rhu.health-records.index') }}" class="btn btn-outline-secondary no-print"><i class="bi bi-arrow-left me-1"></i> Back to tracking</a>
        <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer me-1"></i> Print Record</button>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .navbar, .sidebar, .no-print { display:none !important; }
        .layout-wrapper { display:block !important; }
        .main-content { margin-left:0 !important; padding-left:0 !important; }
        .mw-container { max-width:100% !important; padding:0 !important; }
        .page-hero { background:#fff !important; box-shadow:none !important; border:0 !important; margin-bottom:0.5rem !important; }
        .card { border:1px solid #ccc !important; box-shadow:none !important; break-inside:avoid; }
        .card-header { background:#f5f5f5 !important; color:#000 !important; border-bottom:1px solid #ccc !important; }
        body { background:#fff !important; color:#000 !important; font-size:11pt; }
        a { color:#000 !important; text-decoration:none !important; }
        .badge { border:1px solid #000 !important; }
    }
</style>
@endpush

<div class="row g-3">
    <!-- Visit Details -->
    <div class="col-md-6">
        <div class="card fade-in-card h-100">
            <div class="card-header"><strong>Visit details</strong></div>
            <div class="card-body">
                <dl class="row mb-0" style="font-size:0.9rem;">
                    <dt class="col-sm-5">Woman</dt><dd class="col-sm-7">{{ $record->patient_name }}</dd>
                    <dt class="col-sm-5">Pregnancy</dt><dd class="col-sm-7">{{ $record->pregnancy ? 'Pregnancy #' . $record->pregnancy->id : 'Not linked' }}</dd>
                    <dt class="col-sm-5">BP / Weight</dt><dd class="col-sm-7">{{ $record->bp ?? 'N/A' }} · {{ $record->weight ?? 'N/A' }} kg</dd>
                    <dt class="col-sm-5">Risk level</dt><dd class="col-sm-7"><span class="badge bg-{{ in_array($record->risk_level, ['High', 'Critical']) ? 'danger' : ($record->risk_level === 'Medium' ? 'warning' : 'success') }}">{{ $record->risk_level ?? 'Low' }}</span></dd>
                    <dt class="col-sm-5">Notes</dt><dd class="col-sm-7">{{ $record->notes ?? $record->risk_notes ?? '—' }}</dd>
                    <dt class="col-sm-5">Assigned worker</dt><dd class="col-sm-7">{{ $record->recordedBy?->name ?? 'N/A' }} ({{ $record->recordedBy?->role ? str_replace('_', ' ', $record->recordedBy->role) : '—' }})</dd>
                    <dt class="col-sm-5">Created</dt><dd class="col-sm-7">{{ $record->created_at?->format('M j, Y g:i A') }}</dd>
                    <dt class="col-sm-5">Last updated</dt><dd class="col-sm-7">{{ $record->updated_at?->format('M j, Y g:i A') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Medical Overview: Pregnancy + Emergency Contacts + Checkups -->
    <div class="col-md-6">
        <div class="card fade-in-card h-100">
            <div class="card-header"><strong>Medical overview</strong></div>
            <div class="card-body">

                @if ($record->pregnancy)
                    <h6 class="mb-2 text-muted small text-uppercase">Pregnancy</h6>
                    <dl class="row mb-3" style="font-size:0.85rem;">
                        <dt class="col-sm-5">LMP</dt><dd class="col-sm-7">{{ $record->pregnancy->lmp?->format('M j, Y') ?? '—' }}</dd>
                        <dt class="col-sm-5">EDD</dt><dd class="col-sm-7">{{ $record->pregnancy->edd?->format('M j, Y') ?? '—' }}</dd>
                        <dt class="col-sm-5">Gestational age</dt><dd class="col-sm-7">{{ $record->pregnancy->formatted_aog ?? '—' }}</dd>
                        <dt class="col-sm-5">Trimester</dt><dd class="col-sm-7">{{ $record->pregnancy->trimester_name ?? '—' }}</dd>
                        <dt class="col-sm-5">Gravida / Para</dt><dd class="col-sm-7">{{ $record->pregnancy->gravida ?? '—' }} / {{ $record->pregnancy->para ?? '—' }}</dd>
                        <dt class="col-sm-5">Pregnancy risk</dt><dd class="col-sm-7"><span class="badge bg-{{ in_array($record->pregnancy->risk_level, ['High', 'Critical']) ? 'danger' : ($record->pregnancy->risk_level === 'Medium' ? 'warning' : 'success') }}">{{ $record->pregnancy->risk_level ?? 'Low' }}</span></dd>
                        <dt class="col-sm-5">Status</dt><dd class="col-sm-7">{{ ucfirst($record->pregnancy->status ?? 'active') }}</dd>
                    </dl>
                @else
                    <h6 class="mb-2 text-muted small text-uppercase">Pregnancy</h6>
                    <p class="text-muted small mb-3">No linked pregnancy record.</p>
                @endif

                @php
                    $patient = $record->woman ?? null;
                @endphp

                @if($patient && $patient->emergencyContacts->count() > 0)
                    <h6 class="mb-2 text-muted small text-uppercase">Emergency contacts</h6>
                    <table class="table table-sm align-middle mb-3" style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th style="width:35%;">Name</th>
                                <th style="width:25%;">Relationship</th>
                                <th style="width:30%;">Phone</th>
                                <th style="width:10%;">Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patient->emergencyContacts as $ec)
                                <tr>
                                    <td>{{ $ec->name }}</td>
                                    <td>{{ $ec->relationship ?? '—' }}</td>
                                    <td>{{ $ec->contact_number ?? '—' }}</td>
                                    <td><span class="badge bg-{{ $ec->contact_order === 1 ? 'primary' : 'secondary' }}">{{ $ec->contact_order === 1 ? 'Primary' : 'Secondary' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif($patient)
                    <h6 class="mb-2 text-muted small text-uppercase">Emergency contacts</h6>
                    <p class="text-muted small mb-3">No emergency contacts on file.</p>
                @endif

                @if ($record->pregnancy && $record->pregnancy->checkups->count() > 0)
                    <h6 class="mb-2 text-muted small text-uppercase mt-4">Prenatal checkups</h6>
                    <table class="table table-sm align-middle mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th style="width:25%;">Date</th>
                                <th style="width:20%;">Purpose</th>
                                <th style="width:20%;">Status</th>
                                <th style="width:35%;">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($record->pregnancy->checkups as $checkup)
                                <tr>
                                    <td>{{ $checkup->actual_date?->format('M j, Y') ?? $checkup->scheduled_date?->format('M j, Y') ?? '—' }}</td>
                                    <td>{{ $checkup->purpose ?? '—' }}</td>
                                    <td><span class="badge bg-{{ $checkup->status === 'Completed' ? 'success' : ($checkup->status === 'Missed' ? 'danger' : 'warning') }}">{{ $checkup->status ?? '—' }}</span></td>
                                    <td>{{ $checkup->notes ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif($record->pregnancy)
                    <h6 class="mb-2 text-muted small text-uppercase mt-4">Prenatal checkups</h6>
                    <p class="text-muted small mb-0">No prenatal checkups recorded.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Full Visit History (All Records) -->
<div class="row g-3 mt-4">
    <div class="col-12">
        <div class="card fade-in-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Visit history for this woman (all records)</strong>
                <span class="badge bg-secondary">{{ $history->count() }} record{{ $history->count() !== 1 ? 's' : '' }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="font-size:0.82rem;">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width:15%;">Date</th>
                                <th style="width:12%;">Risk</th>
                                <th style="width:12%;">BP</th>
                                <th style="width:10%;">Weight</th>
                                <th style="width:10%;">Height</th>
                                <th style="width:8%;">HR</th>
                                <th style="width:8%;">Temp</th>
                                <th style="width:10%;">Gestational Age</th>
                                <th style="width:15%;">Recorded by</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $h)
                                <tr class="{{ $h->id === $record->id ? 'table-primary' : '' }}">
                                    <td class="ps-3">{{ $h->created_at?->format('M j, Y') }}</td>
                                    <td><span class="badge bg-{{ in_array($h->risk_level, ['High', 'Critical']) ? 'danger' : ($h->risk_level === 'Medium' ? 'warning' : 'success') }}">{{ $h->risk_level ?? 'Low' }}</span></td>
                                    <td>{{ $h->bp ?? '—' }}</td>
                                    <td>{{ $h->weight ?? '—' }} kg</td>
                                    <td>{{ $h->height ?? '—' }} cm</td>
                                    <td>{{ $h->heart_rate ?? '—' }} bpm</td>
                                    <td>{{ $h->temperature ?? '—' }}°C</td>
                                    <td>{{ $h->gestational_age ?? '—' }}</td>
                                    <td>{{ $h->recordedBy?->name ?? 'N/A' }}</td>
                                    <td>{{ $h->notes ?? $h->risk_notes ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-4">No visit history.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
