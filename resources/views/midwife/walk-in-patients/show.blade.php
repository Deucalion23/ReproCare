@extends('midwife.layout')

@section('title', 'Walk-in Woman Details - Midwife Portal | ReproCare')

@section('midwife-content')
<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <h1 class="page-hero-title">{{ $patient->full_name }}
                </h1>
                <p class="page-hero-subtitle">Unlinked Profile (BHW-Managed · Field Record Only) — details and related referrals.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('midwife.patients', ['filter' => 'unregistered']) }}" class="btn-hero-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Women
                </a>
                @if(!$patient->linkedUserId())
                    <a href="{{ route('midwife.walk-in-patients.activate', $patient->id) }}" class="btn-hero-primary" title="Generate credentials and upgrade to Enrolled Account">
                        <i class="bi bi-person-check-fill"></i> Activate Account
                    </a>
                @endif
            </div>
        </div>
    </div>

    @include('midwife.partials.decision-support')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card fade-in-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Walk-in Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <small class="text-uppercase" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.72rem;">Date of Birth</small>
                            <div class="mt-1">{{ $patient->date_of_birth?->format('M d, Y') ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-uppercase" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.72rem;">Age</small>
                            <div class="mt-1">{{ $patient->age ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-uppercase" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.72rem;">Purok</small>
                            <div class="mt-1">{{ $patient->purok?->name ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-uppercase" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.72rem;">Barangay</small>
                            <div class="mt-1">{{ $patient->barangay ?? 'Burgos' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-uppercase" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.72rem;">Contact Number</small>
                            <div class="mt-1">{{ $patient->contact_number ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-uppercase" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.72rem;">Status</small>
                            <div class="mt-1">
                                @if($patient->isPortalActive())
                                    <span class="summary-chip chip-success">Enrolled · Portal-Active</span>
                                @else
                                    <span class="summary-chip chip-warning">Unlinked · BHW-Managed</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <small class="text-uppercase" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.72rem;">Reason for Visit</small>
                            <div class="mt-1">{{ $patient->reason_for_visit ?: 'Not specified' }}</div>
                        </div>
                        <div class="col-12">
                            <small class="text-uppercase" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.72rem;">Notes</small>
                            <div class="mt-1">{{ $patient->notes ?: 'No notes provided' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($patient->checkupReferrals->count() > 0)
                <div class="card fade-in-card">
                    <div class="card-header">
                        <h5 class="mb-0">Related Referrals</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Referred By</th>
                                        <th>Reason</th>
                                        <th>Urgency</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patient->checkupReferrals as $referral)
                                        <tr>
                                            <td>{{ $referral->referredByBhw?->name ?? '—' }}</td>
                                            <td>{{ $referral->reason }}</td>
                                            <td>{{ ucfirst($referral->urgency) }}</td>
                                            <td>{{ ucfirst($referral->status) }}</td>
                                            <td>{{ $referral->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card fade-in-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Record Info</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-uppercase" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.72rem;">Recorded On</small>
                        <div class="mt-1">{{ $patient->created_at->format('M d, Y g:i A') }}</div>
                    </div>
                    <div>
                        <small class="text-uppercase" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.72rem;">Recorded By</small>
                        <div class="mt-1">{{ $patient->recordedBy?->name ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="card fade-in-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-chat-text me-1"></i> SMS Alerts</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
                    @if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif
                    @if($patient->contact_number)
                        <p class="small text-muted">Walk-ins have no portal inbox — texts to <strong>{{ $patient->contact_number }}</strong> cover checkup reminders and risk findings (max 1/day per type).</p>
                        <form method="POST" action="{{ route('midwife.sms.walk-in') }}">
                            @csrf
                            <input type="hidden" name="walk_in_patient_id" value="{{ $patient->id }}">
                            <div class="mb-2"><textarea name="message_en" class="form-control" rows="3" maxlength="320" required placeholder="Type SMS alert in English..."></textarea></div>
                            <div class="mb-2"><textarea name="message_tl" class="form-control" rows="2" maxlength="320" placeholder="Optional Tagalog version..."></textarea></div>
                            <button class="btn btn-primary btn-sm w-100"><i class="bi bi-send me-1"></i> Send SMS to walk-in</button>
                        </form>
                        @if(isset($smsLogs) && $smsLogs->count())
                            <hr><small class="text-uppercase text-muted">Recent SMS</small>
                            @foreach($smsLogs as $log)
                                <div class="small mt-2"><span class="badge {{ $log->status === 'sent' ? 'bg-success' : ($log->status === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ ucfirst($log->status) }}</span> {{ $log->type }} · {{ $log->created_at->format('M j, g:i A') }}<div class="text-muted">{{ \Illuminate\Support\Str::limit($log->message, 120) }}</div></div>
                            @endforeach
                        @endif
                    @else
                        <div class="alert alert-warning py-2 mb-0">No contact number on file — add one via the recording BHW so SMS alerts can reach this patient.</div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
