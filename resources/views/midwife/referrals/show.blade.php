@extends('midwife.layout')

@section('title', 'Referral Details - ReproCare')

@section('midwife-content')
@php
    $isWalkIn = (bool) $referral->walk_in_patient_id;
    $patient = $isWalkIn ? $referral->walkInPatient : $referral->woman;
    $patientName = $isWalkIn ? ($patient->full_name ?? $referral->patient_name) : ($patient->name ?? $referral->patient_name);
    $patientSubtitle = $isWalkIn ? ($patient->contact_number ?? 'Unlinked woman') : ($patient->email ?? 'Enrolled woman');
    $initials = strtoupper(substr($patientName, 0, 2));
@endphp

<div class="py-2">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <h1 class="page-hero-title">Referral Details</h1>
                <p class="page-hero-subtitle">Accepting this referral converts it into a formal checkup schedule.</p>
            </div>
            <a href="{{ route('midwife.referrals.index') }}" class="btn-hero-secondary">
                <i class="bi bi-arrow-left"></i> Back to Referrals
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4 fade-in-card" style="border-radius:14px;">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100 fade-in-card">
                <div class="card-body p-4 text-center">
                    <div style="width:76px;height:76px;border-radius:50%;background:linear-gradient(135deg, var(--color-primary), var(--color-primary-text));display:flex;align-items:center;justify-content:center;color:var(--color-on-solid);font-weight:800;font-size:1.6rem;margin:0 auto;box-shadow:0 6px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);">
                        {{ $initials }}
                    </div>
                    <h4 class="mt-3 mb-1 fw-bold" style="color:var(--color-text);">{{ $patientName }}</h4>
                    <p class="mb-2" style="color:var(--color-text-muted); font-size:0.9rem;">{{ $patientSubtitle }}</p>
                    <span class="badge rounded-pill px-3 py-1 font-semibold {{ $isWalkIn ? 'bg-light text-primary border' : 'bg-primary text-white' }}">
                        {{ $isWalkIn ? 'Unlinked Profile' : 'Enrolled Patient' }}
                    </span>

                    <div class="text-start d-grid gap-3 pt-3 mt-3 border-top">
                        <div>
                            <div class="text-xs text-uppercase fw-bold" style="color:var(--color-text-muted); font-size:0.72rem; letter-spacing:0.5px;">Urgency</div>
                            <div class="text-sm font-semibold" style="color:var(--color-text);">{{ ucfirst($referral->urgency) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-uppercase fw-bold" style="color:var(--color-text-muted); font-size:0.72rem; letter-spacing:0.5px;">Status</div>
                            <div class="text-sm font-semibold" style="color:var(--color-text);">{{ ucfirst($referral->status) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-uppercase fw-bold" style="color:var(--color-text-muted); font-size:0.72rem; letter-spacing:0.5px;">Referred By</div>
                            <div class="text-sm font-semibold" style="color:var(--color-text);">{{ $referral->referredByBhw?->name ?? 'Assigned BHW' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-uppercase fw-bold" style="color:var(--color-text-muted); font-size:0.72rem; letter-spacing:0.5px;">Created</div>
                            <div class="text-sm font-semibold" style="color:var(--color-text);">{{ $referral->created_at->format('M j, Y h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-4 fade-in-card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold" style="color:var(--color-text);">Referral Clinical Case Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">Primary Reason</small>
                            <div class="fw-bold" style="color:var(--color-text); font-size:1rem;">{{ ucfirst(trim($referral->reason)) }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">BHW Referral Notes</small>
                            <div style="color:var(--color-text);">
                                @if($referral->bhw_notes)
                                    {{ $referral->bhw_notes }}
                                @else
                                    <span style="color:var(--color-text-muted); font-weight:400;">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">Patient Address</small>
                            <div style="color:var(--color-text);">{{ $patient->address ?? 'San Carlos City' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">Barangay</small>
                            <div style="color:var(--color-text);">
                                @if($patient->barangay)
                                    Barangay {{ $patient->barangay }}
                                @else
                                    <span style="color:var(--color-text-muted); font-weight:400;">—</span>
                                @endif
                            </div>
                        </div>
                        @if($referral->midwife_notes)
                            <div class="col-12 mt-2 pt-2 border-top">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">Midwife Notes</small>
                                <div style="color:var(--color-text);">{{ $referral->midwife_notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($referral->pregnancy)
                <div class="card mb-4 fade-in-card">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold" style="color:var(--color-text);">Reported Pregnancy</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">LMP</small>
                                <div class="fw-semibold" style="color:var(--color-text);">{{ $referral->pregnancy->lmp?->format('M d, Y') ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">EDD</small>
                                <div class="fw-semibold" style="color:var(--color-text);">{{ $referral->pregnancy->edd?->format('M d, Y') ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">AOG</small>
                                <div class="fw-semibold" style="color:var(--color-text);">{{ $referral->pregnancy->formatted_aog ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">Risk</small>
                                <span class="badge {{ $referral->pregnancy->is_high_risk ? 'bg-danger' : 'bg-success' }}">{{ $referral->pregnancy->is_high_risk ? 'High Risk' : 'Normal' }}</span>
                            </div>
                            @if($referral->pregnancy->notes)
                                <div class="col-12">
                                    <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">Pregnancy Notes</small>
                                    <div style="color:var(--color-text);">{{ $referral->pregnancy->notes }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="card mb-4 fade-in-card">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold" style="color:var(--color-text);">Attached Health Records ({{ $attachedRecords->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    @if($attachedRecords->count())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                                <thead>
                                    <tr class="text-muted text-uppercase" style="font-size:0.75rem;">
                                        <th class="ps-4 py-3">Recorded</th>
                                        <th class="py-3">BP</th>
                                        <th class="py-3">Risk</th>
                                        <th class="py-3">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attachedRecords as $record)
                                        <tr style="border-bottom:1px solid var(--color-border);">
                                            <td class="ps-4 py-3">{{ $record->created_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                            <td class="py-3">{{ $record->bp ?? '—' }}</td>
                                            <td class="py-3"><span class="badge bg-light text-dark border">{{ $record->risk_level ?? 'Low' }}</span></td>
                                            <td class="py-3">{{ \Illuminate\Support\Str::limit($record->notes ?? '—', 80) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted p-4 mb-0">No health records were attached to this referral.</p>
                    @endif
                </div>
            </div>

            @if($referral->convertedCheckup)
                <div class="card mb-4 fade-in-card" style="background:var(--color-success-soft); border:1px solid var(--color-success-soft);">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="mb-1 fw-bold" style="color:var(--color-success-text);">Referral Converted to Checkup</h5>
                            <p class="mb-0 text-muted" style="font-size:0.875rem;">This referral now has a scheduled checkup session. History preserved.</p>
                        </div>
                        <a href="{{ route('midwife.checkups.show', $referral->convertedCheckup->id) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-calendar-check me-1"></i> View Checkup Record
                        </a>
                    </div>
                </div>
            @endif

            @if(in_array($referral->status, ['pending', 'reviewed'], true) && !$referral->convertedCheckup)
                <div class="card fade-in-card">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold" style="color:var(--color-text);">Clinical Review Actions</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex gap-2 flex-wrap">
                            @if($referral->status === 'pending')
                                <form method="POST" action="{{ route('midwife.referrals.review', $referral->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-hero-secondary">
                                        <i class="bi bi-check2-circle me-1"></i> Mark Reviewed
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('midwife.referrals.convert', $referral->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-calendar-plus me-1"></i> Accept &amp; Create Checkup
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#declineModal" style="border-radius:14px; font-weight:700;">
                                <i class="bi bi-x-circle me-1"></i> Decline
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="declineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:20px; border:1px solid var(--color-border);">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Decline Referral</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('midwife.referrals.decline', $referral->id) }}">
                @csrf
                <div class="modal-body">
                    <label for="midwife_notes" class="form-label fw-bold">Reason for declining</label>
                    <textarea name="midwife_notes" id="midwife_notes" class="form-control" rows="4" placeholder="State clinical or jurisdictional reason..." required style="border-radius:12px;"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn btn-danger" style="border-radius:10px; font-weight:700;">Decline Referral</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
