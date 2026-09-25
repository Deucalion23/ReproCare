@extends('midwife.layout')

@section('title', 'Woman Details - ReproCare')

@section('midwife-content')

{{-- ═══════════════════════════════
     PAGE HERO HEADER
═══════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div class="d-flex align-items-center gap-3">
            <x-patient-avatar :patient="$woman" :size="64" />
            <div>
                <h1 class="page-hero-title mb-1" style="font-size:1.75rem;">{{ $woman->name }}</h1>
                <p class="page-hero-subtitle">
                    <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                    &nbsp;·&nbsp; Patient ID: #{{ $woman->id }} &nbsp;·&nbsp; <span class="badge bg-light text-dark border px-2 py-0.5" style="border-radius:8px;">{{ $woman->barangay ? 'Brgy. ' . $woman->barangay : 'San Carlos City' }}</span>
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('midwife.patients') }}" class="btn-hero-secondary">
                <i class="bi bi-arrow-left"></i> Back to Women
            </a>
            <a href="{{ route('midwife.checkups.create', $woman->id) }}" class="btn-hero-primary">
                <i class="bi bi-calendar-plus-fill"></i> Schedule Checkup
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
         style="background:color-mix(in srgb, var(--color-success) 12%, transparent); border:1px solid color-mix(in srgb, var(--color-success) 30%, transparent); color:var(--color-success-text); border-radius:14px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@include('includes.risk-alert-status')
@include('midwife.partials.decision-support')

{{-- ═══════════════════════════════
     WOMAN INFORMATION CARD
═══════════════════════════════ --}}
<div class="card fade-in-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Patient Profile &amp; Clinical Information
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon" style="margin:0; width:40px; height:40px; border-radius:12px; background:var(--color-primary-soft); color:var(--color-primary-text);">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Full Name</small>
                        <div style="font-weight:700; color:var(--color-text); font-size:1.05rem;">{{ $woman->name }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon" style="margin:0; width:40px; height:40px; border-radius:12px; background:var(--color-info-soft); color:var(--color-info-text);">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Email Address</small>
                        <div style="font-weight:600; color:var(--color-text); font-size:0.95rem;">
                            @if($woman->email)
                                {{ $woman->email }}
                            @else
                                <span style="color:var(--color-text-muted); font-weight:400;">—</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon" style="margin:0; width:40px; height:40px; border-radius:12px; background:var(--color-success-soft); color:var(--color-success-text);">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Contact Number</small>
                        <div style="font-weight:600; color:var(--color-text); font-size:0.95rem;">
                            @if($woman->contact_number)
                                <a href="tel:{{ $woman->contact_number }}" style="color:var(--color-primary-text); text-decoration:none;">{{ $woman->contact_number }}</a>
                            @else
                                <span style="color:var(--color-text-muted); font-weight:400;">—</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon" style="margin:0; width:40px; height:40px; border-radius:12px; background:var(--color-peach-soft); color:var(--color-peach-text);">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Barangay &amp; Address</small>
                        <div style="font-weight:600; color:var(--color-text); font-size:0.95rem;">
                            {{ $woman->address ?: ($woman->barangay ? app(\App\Services\AnalyticsScope::class)->canonicalArea($woman->barangay).', San Carlos City, Pangasinan' : 'Not recorded') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon" style="margin:0; width:40px; height:40px; border-radius:12px; background:var(--color-success-soft); color:var(--color-success-text);">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Account Status</small>
                        <div class="mt-1">
                            <span class="badge" style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text); border-radius:20px; font-weight:700; padding:0.35em 0.8em;">
                                <i class="bi bi-check-circle-fill me-1"></i> Active Record
                            </span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon" style="margin:0; width:40px; height:40px; border-radius:12px; background:var(--color-primary-soft); color:var(--color-primary-text);">
                        <i class="bi bi-calendar-plus"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Registered Date</small>
                        <div style="font-weight:600; color:var(--color-text); font-size:0.95rem;">
                            @if($woman->created_at)
                                {{ $woman->created_at->format('M j, Y') }}
                            @else
                                <span style="color:var(--color-text-muted); font-weight:400;">—</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon" style="margin:0; width:40px; height:40px; border-radius:12px; background:var(--color-peach-soft); color:var(--color-peach-text);">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Last Portal Login</small>
                        <div style="font-weight:600; font-size:0.95rem; color:{{ $woman->last_login_at ? 'var(--color-text)' : 'var(--color-text-muted)' }};">
                            @if($woman->last_login_at)
                                {{ $woman->last_login_at->format('M j, Y h:i A') }}
                            @else
                                <span style="color:var(--color-text-muted); font-weight:400;">—</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($woman->recorded_by_bhw_id)
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="stat-icon" style="margin:0; width:40px; height:40px; border-radius:12px; background:var(--color-info-soft); color:var(--color-info-text);">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Recorded By BHW</small>
                        <div style="font-weight:600; color:var(--color-text); font-size:0.95rem;">{{ $woman->recordedBy?->name ?? 'Assigned BHW' }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     EMERGENCY CONTACTS (3-COLUMN HORIZONTAL GRID)
═══════════════════════════════ --}}
<div class="card fade-in-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Emergency Contacts
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            {{-- Primary Contact --}}
            <div class="col-md-4">
                <div class="p-3.5 h-100 rounded-3 border" style="background:var(--color-surface); border-color:var(--color-border) !important; border-radius:16px;">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="mb-0 fw-bold" style="color:var(--color-primary-text); font-size:0.88rem;">Primary Contact
                        </h6>
                        <span class="badge bg-light text-primary border" style="font-size:0.7rem;">Primary</span>
                    </div>
                    @if($woman->primaryEmergencyContact)
                        <div class="d-flex flex-column gap-2" style="font-size:0.9rem;">
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700;">Name</small>
                                <span class="fw-bold" style="color:var(--color-text);">{{ $woman->primaryEmergencyContact->name }}</span>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700;">Relationship</small>
                                <span class="fw-semibold" style="color:var(--color-text);">{{ $woman->primaryEmergencyContact->relationship }}</span>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700;">Contact Number</small>
                                <a href="tel:{{ $woman->primaryEmergencyContact->contact_number }}" class="fw-bold" style="color:var(--color-primary-text); text-decoration:none;">
                                    <i class="bi bi-telephone-fill me-1"></i>{{ $woman->primaryEmergencyContact->contact_number }}
                                </a>
                            </div>
                            @if($woman->primaryEmergencyContact->address)
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700;">Address</small>
                                <span style="color:var(--color-text-muted);">{{ $woman->primaryEmergencyContact->address }}</span>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="p-3 rounded-3" style="background:var(--color-warning-soft); border:1px solid var(--color-warning); color:var(--color-warning-text); font-size:0.85rem;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> No primary emergency contact recorded.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Secondary Contact --}}
            <div class="col-md-4">
                <div class="p-3.5 h-100 rounded-3 border" style="background:var(--color-surface); border-color:var(--color-border) !important; border-radius:16px;">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="mb-0 fw-bold" style="color:var(--color-text-muted); font-size:0.88rem;">Secondary Contact
                        </h6>
                        <span class="badge bg-light text-muted border" style="font-size:0.7rem;">Optional</span>
                    </div>
                    @if($woman->secondaryEmergencyContact)
                        <div class="d-flex flex-column gap-2" style="font-size:0.9rem;">
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700;">Name</small>
                                <span class="fw-bold" style="color:var(--color-text);">{{ $woman->secondaryEmergencyContact->name }}</span>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700;">Relationship</small>
                                <span class="fw-semibold" style="color:var(--color-text);">{{ $woman->secondaryEmergencyContact->relationship }}</span>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700;">Contact Number</small>
                                <a href="tel:{{ $woman->secondaryEmergencyContact->contact_number }}" class="fw-bold" style="color:var(--color-primary-text); text-decoration:none;">
                                    <i class="bi bi-telephone-fill me-1"></i>{{ $woman->secondaryEmergencyContact->contact_number }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-3" style="background:var(--color-bg); border:1px solid var(--color-border); color:var(--color-text-muted); font-size:0.85rem;">
                            <i class="bi bi-info-circle me-1"></i> No secondary emergency contact.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tertiary Contact --}}
            <div class="col-md-4">
                <div class="p-3.5 h-100 rounded-3 border" style="background:var(--color-surface); border-color:var(--color-border) !important; border-radius:16px;">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="mb-0 fw-bold" style="color:var(--color-text-muted); font-size:0.88rem;">Tertiary Contact
                        </h6>
                        <span class="badge bg-light text-muted border" style="font-size:0.7rem;">Optional</span>
                    </div>
                    @if($woman->tertiaryEmergencyContact)
                        <div class="d-flex flex-column gap-2" style="font-size:0.9rem;">
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700;">Name</small>
                                <span class="fw-bold" style="color:var(--color-text);">{{ $woman->tertiaryEmergencyContact->name }}</span>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700;">Relationship</small>
                                <span class="fw-semibold" style="color:var(--color-text);">{{ $woman->tertiaryEmergencyContact->relationship }}</span>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem; text-transform:uppercase; font-weight:700;">Contact Number</small>
                                <a href="tel:{{ $woman->tertiaryEmergencyContact->contact_number }}" class="fw-bold" style="color:var(--color-primary-text); text-decoration:none;">
                                    <i class="bi bi-telephone-fill me-1"></i>{{ $woman->tertiaryEmergencyContact->contact_number }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-3" style="background:var(--color-bg); border:1px solid var(--color-border); color:var(--color-text-muted); font-size:0.85rem;">
                            <i class="bi bi-info-circle me-1"></i> No tertiary emergency contact.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     CONSOLIDATED PREGNANCY HISTORY CARD
═══════════════════════════════ --}}
<div class="card fade-in-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Pregnancy &amp; Maternal History
        </h5>
        <a href="{{ route('midwife.pregnancies.create', $woman->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Record Pregnancy
        </a>
    </div>
    <div class="card-body p-4">
        @if($pregnancies->count() > 0)
            @php $activePreg = $pregnancies->firstWhere('is_active', true); @endphp
            @if($activePreg)
                {{-- Active Pregnancy Header Stats Banner --}}
                <div class="mb-4 p-4 rounded-3 border" style="background:var(--color-success-soft); border-color:var(--color-success-soft) !important; border-radius:20px;">
                    <div class="row align-items-center g-3">
                        <div class="col-md-3 text-center border-end">
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.72rem; letter-spacing:0.5px;">Gestational Age (AOG)</small>
                            <h3 class="fw-bold mb-1" style="color:var(--color-success-text);">{{ $activePreg->formatted_aog }}</h3>
                            <span class="badge rounded-pill bg-white text-success border px-2.5 py-1 fw-bold" style="font-size:0.75rem;">
                                {{ $activePreg->trimester_name }}
                            </span>
                        </div>
                        <div class="col-md-3 text-center border-end">
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.72rem; letter-spacing:0.5px;">Estimated Due Date</small>
                            <h4 class="fw-bold mb-1" style="color:var(--color-text);">{{ $activePreg->edd ? $activePreg->edd->format('M j, Y') : 'N/A' }}</h4>
                            <small style="color:var(--color-success-text); font-weight:600;"><i class="bi bi-hourglass-split me-1"></i>{{ $activePreg->days_until_due }} days left</small>
                        </div>
                        <div class="col-md-3 text-center border-end">
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.72rem; letter-spacing:0.5px;">Clinical Triage</small>
                            @if($activePreg->is_overdue)
                                <div class="badge bg-danger rounded-pill px-3 py-1.5 fw-bold my-1">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Overdue
                                </div>
                            @else
                                <div class="badge rounded-pill px-3 py-1.5 fw-bold my-1" style="background:var(--color-success-text); color:var(--color-on-solid);">
                                    <i class="bi bi-heart-pulse-fill me-1"></i> Active Case
                                </div>
                            @endif
                            <small class="d-block text-muted">Gravida: {{ $activePreg->gravida }} | Para: {{ $activePreg->para }}</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <a href="{{ route('midwife.pregnancies.show', $activePreg->id) }}" class="btn btn-sm btn-hero-secondary">
                                <i class="bi bi-eye me-1"></i> Full Clinical Details
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size:0.78rem; font-weight:700; border-bottom:1px solid var(--color-border);">
                            <th class="py-3">LMP</th>
                            <th class="py-3">EDD</th>
                            <th class="py-3">AOG</th>
                            <th class="py-3">Gravida / Para</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pregnancies as $pregnancy)
                            <tr style="border-bottom:1px solid var(--color-border);">
                                <td class="py-3 fw-semibold" style="color:var(--color-text);">{{ $pregnancy->lmp ? $pregnancy->lmp->format('M j, Y') : 'N/A' }}</td>
                                <td class="py-3">{{ $pregnancy->edd ? $pregnancy->edd->format('M j, Y') : 'N/A' }}</td>
                                <td class="py-3">{{ $pregnancy->formatted_aog ?? 'N/A' }}</td>
                                <td class="py-3">G{{ $pregnancy->gravida }} P{{ $pregnancy->para }}</td>
                                <td class="py-3">
                                    @if($pregnancy->is_active ?? false)
                                        <span class="badge rounded-pill" style="background:var(--color-success-soft); color:var(--color-success-text); border:1px solid var(--color-success-soft); font-weight:700; padding:0.35em 0.75em;">
                                            <i class="bi bi-heart-pulse-fill me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-light text-muted border font-semibold px-2.5 py-1">
                                            Completed
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 text-end">
                                    <a href="{{ route('midwife.pregnancies.show', $pregnancy->id) }}" class="btn btn-sm btn-light border" style="border-radius:8px;">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <div style="width:52px;height:52px;border-radius:14px;background:var(--color-primary-soft);color:var(--color-primary-text);display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                    <i class="bi bi-heart" style="font-size:1.5rem;"></i>
                </div>
                <h6 class="fw-bold mb-1" style="color:var(--color-text);">No pregnancy records found</h6>
                <p class="text-muted mb-3" style="font-size:0.875rem;">Record the first pregnancy case for this patient.</p>
                <a href="{{ route('midwife.pregnancies.create', $woman->id) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus me-1"></i> Record First Pregnancy
                </a>
            </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════
     CHECKUP HISTORY CARD
═══════════════════════════════ --}}
<div class="card fade-in-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Checkup Consultation History
        </h5>
        <a href="{{ route('midwife.checkups.create', $woman->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Schedule Checkup
        </a>
    </div>
    <div class="card-body p-4">
        @if($checkups->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size:0.78rem; font-weight:700; border-bottom:1px solid var(--color-border);">
                            <th class="py-3">Date</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Midwife</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Notes</th>
                            <th class="py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checkups as $checkup)
                            <tr style="border-bottom:1px solid var(--color-border);">
                                <td class="py-3 fw-bold" style="color:var(--color-text);">{{ $checkup->scheduled_date->format('M j, Y') }}</td>
                                <td class="py-3">{{ $checkup->type ?? 'General Prenatal' }}</td>
                                <td class="py-3">{{ $checkup->midwife->name ?? 'Assigned Midwife' }}</td>
                                <td class="py-3">
                                    @switch($checkup->status)
                                        @case('scheduled')
                                            <span class="badge rounded-pill" style="background:var(--color-peach-soft); color:var(--color-peach-text); border:1px solid var(--color-peach-soft); font-weight:700;">
                                                <i class="bi bi-clock me-1"></i> Scheduled
                                            </span>
                                            @break
                                        @case('completed')
                                            <span class="badge rounded-pill" style="background:var(--color-success-soft); color:var(--color-success-text); border:1px solid var(--color-success-soft); font-weight:700;">
                                                <i class="bi bi-check-circle-fill me-1"></i> Completed
                                            </span>
                                            @break
                                        @case('missed')
                                            <span class="badge rounded-pill bg-danger text-white font-semibold">
                                                <i class="bi bi-x-circle me-1"></i> Missed
                                            </span>
                                            @break
                                        @default
                                            <span class="badge rounded-pill bg-light text-muted border">{{ ucfirst($checkup->status) }}</span>
                                    @endswitch
                                </td>
                                <td class="py-3 text-muted">{{ \Illuminate\Support\Str::limit($checkup->notes ?? 'N/A', 50) }}</td>
                                <td class="py-3 text-end">
                                    <a href="{{ route('midwife.checkups.show', $checkup->id) }}" class="btn btn-sm btn-light border" style="border-radius:8px;">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <div style="width:52px;height:52px;border-radius:14px;background:var(--color-info-soft);color:var(--color-info-text);display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                    <i class="bi bi-calendar-check" style="font-size:1.5rem;"></i>
                </div>
                <h6 class="fw-bold mb-1" style="color:var(--color-text);">No checkup records recorded</h6>
                <p class="text-muted mb-3" style="font-size:0.875rem;">Schedule the first prenatal checkup session for this patient.</p>
                <a href="{{ route('midwife.checkups.create', $woman->id) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus me-1"></i> Schedule Checkup
                </a>
            </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════
     HEALTH RECORDS CARD
═══════════════════════════════ --}}
<div class="card fade-in-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Health &amp; Vital Records
        </h5>
        <a href="{{ route('midwife.health-records.create') }}?user_id={{ $woman->id }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Record
        </a>
    </div>
    <div class="card-body p-4">
        @if($healthRecords->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size:0.78rem; font-weight:700; border-bottom:1px solid var(--color-border);">
                            <th class="py-3">Date</th>
                            <th class="py-3">Recorded By</th>
                            <th class="py-3">Vitals Summary</th>
                            <th class="py-3">Risk Level</th>
                            <th class="py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($healthRecords as $record)
                            <tr style="border-bottom:1px solid var(--color-border);">
                                <td class="py-3 fw-bold" style="color:var(--color-text);">{{ $record->created_at->format('M j, Y') }}</td>
                                <td class="py-3">{{ $record->recordedBy?->name ?? 'Midwife' }}</td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        @if($record->bp)
                                            <span class="badge bg-light text-dark border px-2 py-1">BP: {{ $record->bp }}</span>
                                        @endif
                                        @if($record->heart_rate)
                                            <span class="badge bg-light text-dark border px-2 py-1">HR: {{ $record->heart_rate }} bpm</span>
                                        @endif
                                        @if($record->temperature)
                                            <span class="badge bg-light text-dark border px-2 py-1">Temp: {{ $record->temperature }}°C</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3">
                                    @if($record->risk_level === 'High')
                                        <span class="badge bg-danger text-white">High Risk</span>
                                    @elseif($record->risk_level === 'Medium')
                                        <span class="badge bg-warning text-dark">Medium Risk</span>
                                    @else
                                        <span class="badge bg-success text-white">Low Risk</span>
                                    @endif
                                </td>
                                <td class="py-3 text-end">
                                    <a href="{{ route('midwife.health-records.show', $record->id) }}" class="btn btn-sm btn-light border" style="border-radius:8px;">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <div style="width:52px;height:52px;border-radius:14px;background:var(--color-primary-soft);color:var(--color-primary-text);display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                    <i class="bi bi-file-earmark-medical" style="font-size:1.5rem;"></i>
                </div>
                <h6 class="fw-bold mb-1" style="color:var(--color-text);">No clinical health records yet</h6>
                <p class="text-muted mb-3" style="font-size:0.875rem;">Record vitals and clinical assessments for this patient.</p>
                <a href="{{ route('midwife.health-records.create') }}?user_id={{ $woman->id }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus me-1"></i> Add Health Record
                </a>
            </div>
        @endif
    </div>
</div>

@endsection
