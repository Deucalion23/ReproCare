@extends('midwife.layout')

@section('title', 'Checkup Referrals - ReproCare')

@section('midwife-content')
<div class="py-2">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <h1 class="page-hero-title">Checkup Referrals</h1>
                <p class="page-hero-subtitle">Review incoming referrals from Barangay Health Workers and schedule consultations.</p>
            </div>
            <div class="btn-hero-secondary">
                <i class="bi bi-inbox-fill me-1 text-primary"></i> {{ $referrals->total() }} Referrals Waiting
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4 fade-in-card" style="border-radius:14px;">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="card fade-in-card">
        <div class="card-body p-0">
            @if($referrals->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                        <thead>
                            <tr class="text-muted text-uppercase" style="font-size:0.78rem; font-weight:700; border-bottom:1px solid var(--color-border);">
                                <th class="ps-4 py-3">Patient</th>
                                <th class="py-3">Referred By</th>
                                <th class="py-3">Reason</th>
                                <th class="py-3">Urgency</th>
                                <th class="py-3">Date</th>
                                <th class="text-end pe-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referrals as $referral)
                                @php
                                    $isWalkIn = (bool) $referral->walk_in_patient_id;
                                    $patient = $isWalkIn ? $referral->walkInPatient : $referral->woman;
                                    $name = $isWalkIn ? ($patient->full_name ?? $referral->patient_name) : ($patient->name ?? $referral->patient_name);
                                    $subtitle = $isWalkIn ? ($patient->contact_number ?? 'Unlinked woman') : ($patient->email ?? 'Enrolled woman');
                                    $initial = strtoupper(substr($name, 0, 1));
                                    $reasonFormatted = ucfirst(trim($referral->reason ?? 'General referral'));
                                @endphp
                                <tr style="border-bottom:1px solid var(--color-border);">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="width:40px;height:40px;border-radius:12px;background:var(--color-primary-soft);border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;color:var(--color-primary-text);font-weight:700;">
                                                {{ $initial }}
                                            </div>
                                            <div>
                                                <div class="fw-bold" style="color:var(--color-text);">{{ $name }}</div>
                                                <small class="text-muted">{{ $subtitle }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-medium" style="color:var(--color-text);">{{ $referral->referredByBhw?->name ?? 'BHW Assigned' }}</span>
                                    </td>
                                    <td class="py-3">
                                        <span style="color:var(--color-text);">{{ \Illuminate\Support\Str::limit($reasonFormatted, 65) }}</span>
                                    </td>
                                    <td class="py-3">
                                        @if($referral->urgency === 'emergency')
                                            <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-danger-soft); border:1px solid var(--color-secondary-soft); color:var(--color-secondary-text);">
                                                <i class="bi bi-exclamation-octagon-fill me-1"></i> Emergency
                                            </span>
                                        @elseif($referral->urgency === 'urgent')
                                            <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-peach-soft); border:1px solid var(--color-peach-soft); color:var(--color-peach-text);">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Urgent
                                            </span>
                                        @else
                                            <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text);">
                                                <i class="bi bi-check-circle-fill me-1"></i> Routine
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-semibold" style="color:var(--color-text);">{{ $referral->created_at->format('M j, Y') }}</div>
                                        <small class="text-muted">{{ $referral->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td class="text-end pe-4 py-3">
                                        <a href="{{ route('midwife.referrals.show', $referral->id) }}" class="btn btn-sm btn-primary px-3 py-1" style="border-radius:8px; font-size:0.82rem; font-weight:700;">
                                            <i class="bi bi-eye me-1"></i> Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-top">{{ $referrals->links() }}</div>
            @else
                <div class="p-5 text-center text-muted">
                    <div style="width:52px;height:52px;border-radius:14px;background:var(--color-primary-soft);color:var(--color-primary-text);display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                        <i class="bi bi-clipboard2-heart" style="font-size:1.6rem;"></i>
                    </div>
                    <h6 class="fw-bold mb-1" style="color:var(--color-text);">No referrals waiting</h6>
                    <p class="text-muted mb-0" style="font-size:0.875rem;">All incoming checkup referrals have been reviewed.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
