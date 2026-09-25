@extends('bhw.layout')

@section('title', 'Walk-in Patient Details - ReproCare')

@section('bhw-content')
<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3" style="position:relative;z-index:1;">
                        <div class="d-flex align-items-center gap-3">
                <x-patient-avatar :patient="$patient" :name="$patient->full_name" :size="64" />
                <div>
                    <div class="page-hero-title">{{ $patient->full_name }}</div>
                    <p class="page-hero-subtitle">{{ $patient->contact_number ?? 'No contact' }} | {{ $patient->barangay ?? 'Barangay not set' }}</p>
                </div>
            </div>
            @php
                // Return to where the user came from (Pregnancies / Women hub),
                // never strand them on the unlisted walk-in index.
                $backRoute = request('from') === 'pregnancies'
                    ? route('bhw.pregnancies.index')
                    : route('bhw.patients');
                $editRoute = request('from')
                    ? route('bhw.walk-in-patients.edit', [$patient->id, 'from' => request('from')])
                    : route('bhw.walk-in-patients.edit', $patient->id);
            @endphp
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ $editRoute }}" class="btn btn-light">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                </a>
                @if(!$patient->linkedUserId())
                    <a href="{{ route('bhw.walk-in-patients.convert', $patient->id) }}" class="btn btn-light" title="Generate credentials and upgrade to Enrolled Account">
                        <i class="bi bi-person-check-fill me-1"></i> Activate Account
                    </a>
                @endif
                <a href="{{ $backRoute }}" class="btn-hero-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-cyan fade-in-card">
                <i class="bi bi-journal-medical stat-icon"></i>
                <div class="stat-label">Referrals</div>
                <div class="stat-number">{{ $patient->checkupReferrals->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-green fade-in-card">
                <i class="bi bi-geo-alt-fill stat-icon"></i>
                <div class="stat-label">Purok</div>
                <div class="stat-number" style="font-size:1.1rem;">{{ $patient->purok?->name ?? 'Not set' }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-amber fade-in-card">
                <i class="bi bi-person-badge-fill stat-icon"></i>
                <div class="stat-label">Portal Status</div>
                <div class="stat-number" style="font-size:1.1rem; text-wrap:balance;">{{ $patient->portalStatusLabel() }}</div>
            </div>
        </div>
    </div>

    <div class="card fade-in-card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Profile Summary</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Full Name</strong><div class="text-muted mt-1">{{ $patient->full_name }}</div></div>
                <div class="col-md-4"><strong>Age</strong><div class="text-muted mt-1">{{ $patient->age ?? 'Not available' }}</div></div>
                <div class="col-md-4"><strong>Date of Birth</strong><div class="text-muted mt-1">{{ $patient->date_of_birth?->format('M j, Y') ?? 'Not available' }}</div></div>
                <div class="col-md-4"><strong>Contact Number</strong><div class="text-muted mt-1">{{ $patient->contact_number ?? 'Not provided' }}</div></div>
                <div class="col-md-4"><strong>Barangay</strong><div class="text-muted mt-1">{{ $patient->barangay ?? 'Barangay Burgos Padlan, San Carlos City, Pangasinan' }}</div></div>
                <div class="col-md-4"><strong>Purok</strong><div class="text-muted mt-1">{{ $patient->purok?->name ?? 'Not assigned' }}</div></div>
                <div class="col-md-6"><strong>Address</strong><div class="text-muted mt-1">{{ $patient->address ?? 'Not provided' }}</div></div>
                <div class="col-md-6"><strong>Reason for Visit</strong><div class="text-muted mt-1">{{ $patient->reason_for_visit ?? 'No reason recorded' }}</div></div>
                <div class="col-md-12"><strong>Notes</strong><div class="text-muted mt-1">{{ $patient->notes ?? 'No notes recorded' }}</div></div>
            </div>
        </div>
    </div>

    <div class="card fade-in-card mb-4">
        <div class="card-header"><h5 class="mb-0"><i class="bi bi-chat-text me-1"></i> SMS Alerts</h5></div>
        <div class="card-body">
            @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif
            @if($patient->contact_number)
                <p class="small text-muted">Texts to <strong>{{ $patient->contact_number }}</strong> cover checkup and risk alerts (max 1/day per type). Manual SMS sending lives with the midwife and RHU admin.</p>
                @if(isset($smsLogs) && $smsLogs->count())
                    <hr><small class="text-uppercase text-muted">Recent SMS</small>
                    @foreach($smsLogs as $log)
                        <div class="small mt-2"><span class="badge {{ $log->status === 'sent' ? 'bg-success' : ($log->status === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ ucfirst($log->status) }}</span> {{ $log->type }} · {{ $log->created_at->format('M j, g:i A') }}<div class="text-muted">{{ \Illuminate\Support\Str::limit($log->message, 120) }}</div></div>
                    @endforeach
                @endif
            @else
                <div class="alert alert-warning py-2 mb-0">No contact number — <a href="{{ route('bhw.walk-in-patients.edit', $patient->id) }}">add one</a> so SMS alerts can reach this patient.</div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card fade-in-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Recorded By</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3"><strong>Name</strong><div class="text-muted mt-1">{{ $patient->recordedBy?->name ?? 'Unknown' }}</div></div>
                    <div class="mb-3"><strong>Created At</strong><div class="text-muted mt-1">{{ $patient->created_at->format('M j, Y g:i A') }}</div></div>
                    @if($patient->converted_at)
                        <div><strong>Converted At</strong><div class="text-muted mt-1">{{ $patient->converted_at->format('M j, Y g:i A') }}</div></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card fade-in-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Referral History</h5>
                </div>
                <div class="card-body p-0">
                    @if($patient->checkupReferrals->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Reason</th>
                                        <th>Urgency</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patient->checkupReferrals as $referral)
                                        <tr>
                                            <td>{{ $referral->reason }}</td>
                                            <td>{{ ucfirst($referral->urgency) }}</td>
                                            <td>{{ ucfirst($referral->status) }}</td>
                                            <td>{{ $referral->created_at->format('M j, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="bi bi-send-x empty-state-icon"></i>
                            <h6>No referrals yet</h6>
                            <p>This walk-in patient has not been referred yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
