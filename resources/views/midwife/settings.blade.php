@extends('midwife.layout')

@section('title', 'Settings - Midwife Portal | ReproCare')

@push('styles')
<style>
    /* ── Settings Layout ── */
    .settings-wrap         { display:flex; gap:1.5rem; align-items:flex-start; }
    .settings-sidebar      { width:250px; flex-shrink:0; position:sticky; top:80px; }
    .settings-content      { flex:1; min-width:0; }

    /* Settings nav */
    .settings-nav          { background:var(--color-surface); border:1px solid var(--color-border); border-radius:18px; overflow:hidden; padding:0.5rem; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent); }
    .settings-nav-item     { display:flex; align-items:center; gap:0.75rem; padding:0.7rem 0.9rem; border-radius:12px; font-size:0.875rem; font-weight:500; color:var(--color-text-muted); cursor:pointer; text-decoration:none; transition:all 0.2s ease; border:1px solid transparent; margin-bottom:0.15rem; }
    .settings-nav-item i   { font-size:1rem; width:1.15rem; text-align:center; flex-shrink:0; }
    .settings-nav-item:hover { background:var(--color-secondary-soft); color:var(--color-text); }
    .settings-nav-item.active { background:var(--color-secondary-soft); color:var(--color-secondary-text); border-color:var(--color-secondary-soft); font-weight:700; }

    /* Settings sections */
    .settings-section      { display:none; }
    .settings-section.active { display:block; }

    /* Pref cards */
    .pref-card             { background:var(--color-surface); border:1px solid var(--color-border); border-radius:18px; margin-bottom:1.25rem; overflow:hidden; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent); }
    .pref-card-header      { padding:1.1rem 1.4rem; border-bottom:1px solid var(--color-border); display:flex; align-items:center; gap:0.75rem; }
    .pref-card-header-icon { width:38px; height:38px; border-radius:11px; background:var(--color-secondary-soft); border:1px solid var(--color-secondary-soft); display:flex; align-items:center; justify-content:center; font-size:1rem; color:var(--color-secondary-text); flex-shrink:0; }
    .pref-card-header h6   { margin:0; font-family:'Plus Jakarta Sans', sans-serif; font-weight:700; font-size:0.95rem; color:var(--color-text); }
    .pref-card-header p    { margin:0; font-size:0.78rem; color:var(--color-text-muted); }
    .pref-card-body        { padding:1.25rem 1.4rem; }

    /* Pref row (toggle list item) */
    .pref-row { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:0.85rem 0; border-bottom:1px solid var(--color-border); }
    .pref-row:last-child { border-bottom:none; padding-bottom:0; }
    .pref-row-label h6 { margin:0 0 0.15rem; font-size:0.9rem; font-weight:600; color:var(--color-text); }
    .pref-row-label p  { margin:0; font-size:0.8rem; color:var(--color-text-muted); }

    /* Theme option cards */
    .theme-option-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1rem; }
    .theme-option {
        border:2px solid var(--color-border);
        border-radius:14px;
        padding:1rem;
        cursor:pointer;
        text-align:center;
        transition:all 0.25s ease;
        position:relative;
        background:var(--color-bg);
    }
    .theme-option:hover { border-color:var(--color-secondary); transform:translateY(-2px); }
    .theme-option.active { border-color:var(--color-secondary); background:var(--color-secondary-soft); }

    .theme-preview {
        border-radius:10px;
        overflow:hidden;
        height:70px;
        display:flex;
        margin-bottom:0.75rem;
        border:1px solid var(--color-border);
    }
    .preview-sidebar { width:28%; background:var(--color-primary-text); }
    .preview-body { flex:1; padding:0.4rem; display:flex; flex-direction:column; gap:0.25rem; background:var(--color-bg); }
    .preview-card-sm { height:14px; background:var(--color-surface-strong); border-radius:4px; }
    .preview-card-sm.wide { width:100%; }
    .preview-card-sm.half { width:60%; }

    .light-preview .preview-sidebar { background:var(--color-primary-soft); }
    .light-preview .preview-body    { background:var(--color-primary-soft); }
    .light-preview .preview-card-sm { background:var(--color-surface); }

    .theme-option-label { font-size:0.85rem; font-weight:600; color:var(--color-text); }
    .theme-check { position:absolute; top:0.6rem; right:0.6rem; font-size:1rem; color:var(--color-secondary-text); display:none; }
    .theme-option.active .theme-check { display:inline-block; }

    /* Toggle quick row */
    .theme-toggle-row { display:flex; align-items:center; gap:0.9rem; margin-top:1.25rem; padding:0.85rem 1rem; background:var(--color-bg); border:1px solid var(--color-border); border-radius:12px; }
    .theme-toggle-row span { font-size:0.875rem; color:var(--color-text-muted); font-weight:500; display:flex; align-items:center; gap:0.4rem; }

    /* Verified / status badges */
    .verified-badge { display:inline-flex; align-items:center; gap:.35rem; font-weight:700; border-radius:9999px; padding:0.45rem 0.9rem; font-size:0.75rem; border:1px solid; white-space:nowrap; }
    .verified-badge.ok  { background:var(--color-success-soft); color:var(--color-success-text); border-color:var(--color-success-soft); }
    .verified-badge.warn{ background:var(--color-warning-soft); color:var(--color-warning-text); border-color:var(--color-warning); }
    .verified-badge.bad { background:var(--color-danger-soft); color:var(--color-danger-text); border-color:var(--color-danger-soft); }

    /* Session badge */
    .session-badge { display:inline-flex; align-items:center; gap:0.4rem; padding:0.25rem 0.7rem; background:color-mix(in srgb, var(--color-success) 12%, transparent); border:1px solid color-mix(in srgb, var(--color-success) 30%, transparent); border-radius:20px; font-size:0.78rem; font-weight:600; color:var(--color-success-text); }

    /* Page title row */
    .settings-page-header { margin-bottom:1.5rem; }

    /* Readonly clinical inputs */
    .clinical-input[readonly] { background:var(--color-bg) !important; font-weight:600; color:var(--color-text); }

    /* Oversight tables */
    .oversight-table thead th { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--color-text-muted); padding:.6rem .9rem; border-bottom:1px solid var(--color-border); }
    .oversight-table tbody td { padding:.6rem .9rem; font-size:.85rem; border-bottom:1px solid var(--color-border); vertical-align:middle; }
    .oversight-table tbody tr:last-child td { border-bottom:none; }

    @media (max-width: 768px) {
        .settings-wrap { flex-direction:column; }
        .settings-sidebar { width:100%; position:static; }
        .theme-option-grid { grid-template-columns:1fr 1fr; }
    }
</style>
@endpush

@section('midwife-content')

@php
    $mwUser = auth()->user();
    $isVerified = $mwUser->isApproved();
    $licenseValid = is_null($licenseDaysLeft) ? null : $licenseDaysLeft >= 0;
    $pendingTotal = $pendingPatients + $pendingRecords + $pendingReports;
@endphp

{{-- Page Header --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div>
                <div class="page-hero-title">Settings</div>
                <p class="page-hero-subtitle mb-0">Clinical identity, workflow preferences, security &amp; audit</p>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius:14px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" style="border-radius:14px;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <ul class="mb-0 mt-1 ps-3">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="settings-wrap">

    {{-- ── LEFT NAV ── --}}
    <div class="settings-sidebar">
        <div class="settings-nav">
            <a class="settings-nav-item active" onclick="showSection('credentials', this)" href="#">
                <i class="bi bi-award-fill" style="color:var(--color-secondary-text);"></i>
                Clinical Identification
            </a>
            <a class="settings-nav-item" onclick="showSection('workflow', this)" href="#">
                <i class="bi bi-bell-fill" style="color:var(--color-success-text);"></i>
                Workflow &amp; Notifications
                @if($pendingTotal > 0)
                    <span class="badge ms-auto" style="background:var(--color-secondary-soft);color:var(--color-secondary-text);border-radius:999px;">{{ $pendingTotal }}</span>
                @endif
            </a>
            <a class="settings-nav-item" onclick="showSection('security', this)" href="#">
                <i class="bi bi-shield-lock-fill" style="color:var(--color-peach-text);"></i>
                Account Security
            </a>
            <a class="settings-nav-item" onclick="showSection('location', this)" href="#">
                <i class="bi bi-geo-alt-fill" style="color:var(--color-info-text);"></i>
                Assigned Location
            </a>
            <a class="settings-nav-item" onclick="showSection('activity', this)" href="#">
                <i class="bi bi-clock-history" style="color:var(--color-warning-text);"></i>
                Activity Log
            </a>
            <a class="settings-nav-item" onclick="showSection('appearance', this)" href="#">
                <i class="bi bi-palette-fill" style="color:var(--color-primary-text);"></i>
                Appearance
            </a>
        </div>
    </div>

    {{-- ── RIGHT CONTENT ── --}}
    <div class="settings-content">

        {{-- ══ 1. CLINICAL & PROFESSIONAL IDENTIFICATION ══ --}}
        <div class="settings-section active" id="section-credentials">
            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-award-fill"></i></div>
                    <div class="d-flex justify-content-between align-items-center flex-grow-1 flex-wrap gap-2">
                        <div>
                            <h6>Clinical &amp; Professional Identification</h6>
                            <p>PRC practitioner profile — your approved signature validates field data</p>
                        </div>
                        @if($isVerified)
                            <span class="verified-badge ok"><i class="bi bi-patch-check-fill"></i> PRC Verified Active</span>
                        @else
                            <span class="verified-badge warn"><i class="bi bi-hourglass-split"></i> Pending Verification</span>
                        @endif
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('midwife.settings.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="profile">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.8rem;">PRC License Number</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:var(--color-secondary-soft);border-color:var(--color-secondary-soft);color:var(--color-secondary-text);"><i class="bi bi-card-heading"></i></span>
                                    <input type="text" name="license_number" class="form-control clinical-input" value="{{ old('license_number', $mwUser->license_number) }}" placeholder="e.g., 0084921" {{ $isVerified ? 'readonly' : '' }}>
                                </div>
                                @if($isVerified)
                                    <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Verified by RHU Admin — contact admin to change.</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.8rem;">License Expiration Date</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:var(--color-success-soft);border-color:var(--color-success-soft);color:var(--color-success-text);"><i class="bi bi-calendar-check"></i></span>
                                    <input type="text" class="form-control clinical-input" value="{{ $mwUser->license_expiry ? $mwUser->license_expiry->format('F d, Y') : 'Not set' }}" readonly>
                                </div>
                                @if(is_null($licenseDaysLeft))
                                    <small class="text-muted">No expiry on record — ask RHU Admin to verify.</small>
                                @elseif($licenseDaysLeft < 0)
                                    <small><span class="verified-badge bad mt-1"><i class="bi bi-exclamation-octagon-fill"></i> Expired {{ abs($licenseDaysLeft) }} days ago</span></small>
                                @elseif($licenseDaysLeft <= 90)
                                    <small><span class="verified-badge warn mt-1"><i class="bi bi-exclamation-triangle-fill"></i> Expires in {{ $licenseDaysLeft }} days</span></small>
                                @else
                                    <small class="text-muted"><i class="bi bi-check-circle-fill me-1" style="color:var(--color-success-text);"></i>Valid for {{ $licenseDaysLeft }} more days.</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.8rem;">Degree / Credentials</label>
                                <input type="text" name="specialization" class="form-control clinical-input" value="{{ old('specialization', $mwUser->specialization ?? 'Registered Midwife (RM)') }}" placeholder="e.g., Registered Midwife (RM)" {{ $isVerified ? 'readonly' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.8rem;">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $mwUser->contact_number) }}" placeholder="09xx xxx xxxx">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm" style="border:none; border-radius:999px; font-weight:800; padding:0.55rem 1.35rem; background:var(--color-secondary-text); background-color:var(--color-secondary-text); color:var(--color-on-solid); box-shadow:0 6px 16px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent);">
                                    <i class="bi bi-check-circle me-1"></i> Save Identification
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ══ 2. WORKFLOW & NOTIFICATION PREFERENCES ══ --}}
        <div class="settings-section" id="section-workflow">
            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:var(--color-success-soft);border-color:var(--color-success-soft);color:var(--color-success-text);"><i class="bi bi-bell-fill"></i></div>
                    <div class="d-flex justify-content-between align-items-center flex-grow-1 flex-wrap gap-2">
                        <div>
                            <h6>Workflow &amp; Notification Preferences</h6>
                            <p>How high-risk alerts and approval reminders reach you</p>
                        </div>
                        <span class="verified-badge {{ $pendingTotal > 0 ? 'warn' : 'ok' }}">
                            <i class="bi bi-inbox-fill"></i> {{ $pendingTotal }} awaiting validation
                        </span>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('midwife.settings.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="preferences">

                        <h6 class="fw-800 mb-1" style="font-size:0.85rem;color:var(--color-text);">High-Risk Alert Notifications</h6>
                        <p class="text-muted small mb-2">Fired when rule-based clinical risks are detected (e.g. BP &gt; 140/90, severe anemia, missed checkups).</p>
                        <div class="pref-row">
                            <div class="pref-row-label"><h6>Email alerts</h6><p>Send high-risk notifications to your inbox</p></div>
                            <label class="rc-switch"><input type="checkbox" name="pref_high_risk_email" value="1" {{ $mwUser->pref_high_risk_email ? 'checked' : '' }}><span class="rc-track"><span class="rc-thumb"></span></span></label>
                        </div>
                        <div class="pref-row">
                            <div class="pref-row-label"><h6>SMS alerts</h6><p>Text high-risk notifications to your contact number</p></div>
                            <label class="rc-switch"><input type="checkbox" name="pref_high_risk_sms" value="1" {{ $mwUser->pref_high_risk_sms ? 'checked' : '' }}><span class="rc-track"><span class="rc-thumb"></span></span></label>
                        </div>
                        <div class="pref-row">
                            <div class="pref-row-label"><h6>Dashboard alerts</h6><p>Show high-risk banners inside the midwife portal</p></div>
                            <label class="rc-switch"><input type="checkbox" name="pref_high_risk_dashboard" value="1" {{ $mwUser->pref_high_risk_dashboard ? 'checked' : '' }}><span class="rc-track"><span class="rc-thumb"></span></span></label>
                        </div>

                        <h6 class="fw-800 mt-4 mb-1" style="font-size:0.85rem;color:var(--color-text);">Pending Approval Summary</h6>
                        <p class="text-muted small mb-2">Reminder frequency for records waiting for your final validation.</p>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="verified-badge warn"><i class="bi bi-person-check-fill"></i> {{ $pendingPatients }} patient approvals</span>
                            <span class="verified-badge warn"><i class="bi bi-clipboard2-pulse-fill"></i> {{ $pendingRecords }} health records</span>
                            <span class="verified-badge warn"><i class="bi bi-file-earmark-text-fill"></i> {{ $pendingReports }} BHW reports</span>
                        </div>
                        <div class="mb-3" style="max-width:320px;">
                            <label class="form-label fw-bold" style="font-size:0.8rem;">Reminder frequency</label>
                            <select name="pref_approval_summary" class="form-select" style="border-radius:12px;">
                                @foreach(['immediate' => 'Immediate (as they arrive)', 'daily' => 'Daily digest', 'weekly' => 'Weekly digest', 'off' => 'Off'] as $val => $label)
                                    <option value="{{ $val }}" {{ ($mwUser->pref_approval_summary ?? 'daily') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <h6 class="fw-800 mt-4 mb-1" style="font-size:0.85rem;color:var(--color-text);">BHW President Escalations</h6>
                        <div class="pref-row">
                            <div class="pref-row-label"><h6>Escalation notifications</h6><p>Notify me when a BHW President escalates a case for clinical judgment</p></div>
                            <label class="rc-switch"><input type="checkbox" name="pref_escalation_alerts" value="1" {{ $mwUser->pref_escalation_alerts ? 'checked' : '' }}><span class="rc-track"><span class="rc-thumb"></span></span></label>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-sm btn-primary" style="border-radius:10px;font-weight:700;">
                                <i class="bi bi-check-circle me-1"></i> Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ══ 3. ACCOUNT SECURITY ══ --}}
        <div class="settings-section" id="section-security">
            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:var(--color-warning-soft);border-color:var(--color-warning);color:var(--color-warning-text);"><i class="bi bi-key-fill"></i></div>
                    <div><h6>Change Password</h6><p>Protect access to sensitive patient data</p></div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('midwife.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="password">
                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size:0.8rem;">Current Password</label>
                            <input type="password" name="current_password" class="form-control" placeholder="Enter current password" style="border-radius:12px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:0.8rem;">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" style="border-radius:12px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:0.8rem;">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password" style="border-radius:12px;">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-sm btn-primary" style="border-radius:10px;font-weight:700;">
                                <i class="bi bi-shield-lock me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:var(--color-success-soft);border-color:var(--color-success-soft);color:var(--color-success-text);"><i class="bi bi-shield-fill-check"></i></div>
                    <div><h6>Two-Factor Authentication (2FA)</h6><p>Recommended to secure your clinical approval authority</p></div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('midwife.settings.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="2fa">
                        <div class="pref-row">
                            <div class="pref-row-label">
                                <h6>Require 2FA on login {!! $mwUser->pref_2fa_enabled ? '<span class="verified-badge ok ms-1">Enabled</span>' : '<span class="verified-badge warn ms-1">Disabled</span>' !!}</h6>
                                <p>When enabled, sign-in asks for an authenticator-app code in addition to your password</p>
                            </div>
                            <label class="rc-switch"><input type="checkbox" name="pref_2fa_enabled" value="1" {{ $mwUser->pref_2fa_enabled ? 'checked' : '' }} onchange="this.form.submit()"><span class="rc-track"><span class="rc-thumb"></span></span></label>
                        </div>
                    </form>
                    <div class="mt-3 p-3 rounded-3" style="background:var(--color-bg);border:1px solid var(--color-border);font-size:0.82rem;color:var(--color-text-muted);">
                        <i class="bi bi-info-circle-fill me-1" style="color:var(--color-secondary-text);"></i>
                        Use any authenticator app (Google Authenticator, Microsoft Authenticator, Authy) to scan the setup code issued at your next sign-in after enabling.
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ 4. SYSTEM & ASSIGNED LOCATION ══ --}}
        <div class="settings-section" id="section-location">
            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:var(--color-info-soft);border-color:var(--color-info-soft);color:var(--color-info-text);"><i class="bi bi-geo-alt-fill"></i></div>
                    <div><h6>Assigned RHU Context</h6><p>Your operational unit within San Carlos City</p></div>
                </div>
                <div class="pref-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:0.8rem;">Supervising Body</label>
                            <input type="text" class="form-control clinical-input" value="City Health Office (CHO) — San Carlos City" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:0.8rem;">Assigned RHU</label>
                            <input type="text" class="form-control clinical-input" value="{{ $mwUser->rhu_assignment ?? 'Rural Health Unit (RHU) I — San Carlos City' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:0.8rem;">Station Barangay</label>
                            <input type="text" class="form-control clinical-input" value="{{ $mwUser->barangay ? 'Barangay '.$mwUser->barangay : 'Not assigned' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:0.8rem;">Catchment Assignment</label>
                            <input type="text" class="form-control clinical-input" value="{{ $mwUser->assigned_barangay ?? 'San Carlos City Central & Surrounding Puroks' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-pin-map-fill"></i></div>
                    <div class="d-flex justify-content-between align-items-center flex-grow-1 flex-wrap gap-2">
                        <div><h6>Barangay / Purok Oversight</h6><p>Areas whose data you are currently reviewing</p></div>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="verified-badge ok"><i class="bi bi-people-fill"></i> {{ $totalPatients }} patients</span>
                            <span class="verified-badge ok"><i class="bi bi-person-workspace"></i> {{ $bhwCount }} BHWs</span>
                        </div>
                    </div>
                </div>
                <div class="pref-card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-800 mb-2" style="font-size:0.82rem;">Patients by Barangay</h6>
                            <div class="table-responsive" style="border:1px solid var(--color-border);border-radius:12px;">
                                <table class="table mb-0 oversight-table">
                                    <thead><tr><th>Barangay</th><th class="text-end">Patients</th></tr></thead>
                                    <tbody>
                                        @forelse($barangayStats as $row)
                                            <tr><td>{{ $row->barangay ?: 'Unspecified' }}</td><td class="text-end fw-bold">{{ $row->total }}</td></tr>
                                        @empty
                                            <tr><td colspan="2" class="text-muted">No patient coverage yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-800 mb-2" style="font-size:0.82rem;">Patients by Purok</h6>
                            <div class="table-responsive" style="border:1px solid var(--color-border);border-radius:12px;">
                                <table class="table mb-0 oversight-table">
                                    <thead><tr><th>Purok</th><th>Barangay</th><th class="text-end">Patients</th></tr></thead>
                                    <tbody>
                                        @forelse($purokStats as $row)
                                            <tr><td>{{ $row->name }}</td><td class="text-muted">{{ $row->barangay ?? '—' }}</td><td class="text-end fw-bold">{{ $row->patients }}</td></tr>
                                        @empty
                                            <tr><td colspan="3" class="text-muted">No purok data yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ 5. USER ACTIVITY LOG (AUDIT TRAIL) ══ --}}
        <div class="settings-section" id="section-activity">
            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:var(--color-warning-soft);border-color:var(--color-warning);color:var(--color-warning-text);"><i class="bi bi-clock-history"></i></div>
                    <div><h6>Recent Approval</h6><p>Your latest clinical sign-off</p></div>
                </div>
                <div class="pref-card-body">
                    @if($lastApproval)
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:var(--color-success-soft);border:1px solid var(--color-success-soft);">
                            <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;border-radius:12px;background:var(--color-surface);color:var(--color-success-text);border:1px solid var(--color-success-soft);">
                                <i class="bi bi-patch-check-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold" style="color:var(--color-text);font-size:0.9rem;">Last Approved Case</div>
                                <div class="text-muted small">{{ $lastApproval->description }}</div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <div class="fw-bold" style="font-size:0.82rem;color:var(--color-success-text);">{{ $lastApproval->created_at->format('M j, Y g:i A') }}</div>
                                <small class="text-muted">{{ $lastApproval->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-3 text-muted" style="background:var(--color-bg);border:1px dashed var(--color-border);font-size:0.85rem;">
                            <i class="bi bi-info-circle me-1"></i> No approval actions recorded yet — approvals you sign off will appear here with timestamps.
                        </div>
                    @endif
                </div>
            </div>

            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:var(--color-info-soft);border-color:var(--color-info-soft);color:var(--color-info-text);"><i class="bi bi-hdd-network-fill"></i></div>
                    <div class="d-flex justify-content-between align-items-center flex-grow-1 flex-wrap gap-2">
                        <div><h6>Active Session Monitoring</h6><p>Devices currently signed in to this account</p></div>
                        <span class="verified-badge ok"><i class="bi bi-circle-fill" style="font-size:0.45rem;"></i> {{ $sessions->count() }} active</span>
                    </div>
                </div>
                <div class="pref-card-body">
                    <div class="d-flex flex-column gap-2">
                        @forelse($sessions as $sess)
                            @php $isCurrent = $sess->id === $currentSessionId; @endphp
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background:{{ $isCurrent ? 'var(--color-secondary-soft)' : 'var(--color-bg)' }};border:1px solid {{ $isCurrent ? 'var(--color-secondary-soft)' : 'var(--color-border)' }};">
                                <div>
                                    <div style="font-size:0.875rem;font-weight:700;color:var(--color-text);">
                                        {{ $isCurrent ? 'This device (current session)' : 'Signed-in device' }}
                                    </div>
                                    <div style="font-size:0.78rem;color:var(--color-text-muted);">
                                        {{ $sess->ip_address }} &nbsp;·&nbsp; {{ \Illuminate\Support\Str::limit($sess->user_agent, 60) }}
                                        &nbsp;·&nbsp; active {{ \Carbon\Carbon::createFromTimestamp($sess->last_activity)->diffForHumans() }}
                                    </div>
                                </div>
                                @if($isCurrent)
                                    <span class="session-badge"><i class="bi bi-circle-fill" style="font-size:0.45rem;"></i> Active</span>
                                @else
                                    <span class="verified-badge warn">Idle</span>
                                @endif
                            </div>
                        @empty
                            <div class="text-muted" style="font-size:0.85rem;">No active sessions found.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-list-check"></i></div>
                    <div><h6>Recent Activity</h6><p>Your latest actions in the portal</p></div>
                </div>
                <div class="pref-card-body">
                    @forelse($recentActivity as $log)
                        <div class="pref-row">
                            <div class="pref-row-label">
                                <h6>{{ $log->description }}</h6>
                                <p>{{ ucfirst($log->action) }} · {{ $log->created_at->format('M j, Y g:i A') }} · {{ $log->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="verified-badge {{ $log->action === 'approve' ? 'ok' : 'warn' }}">{{ ucfirst($log->action) }}</span>
                        </div>
                    @empty
                        <div class="text-muted" style="font-size:0.85rem;">No activity recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ══ APPEARANCE ══ --}}
        <div class="settings-section" id="section-appearance">
            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-palette-fill"></i></div>
                    <div><h6>Color Theme</h6><p>Choose your preferred interface appearance</p></div>
                </div>
                <div class="pref-card-body">
                    <div class="theme-option-grid">
                        <div class="theme-option" data-theme="light" id="theme-opt-light" onclick="setRcTheme('light')" role="button" tabindex="0">
                            <div class="theme-preview light-preview">
                                <div class="preview-sidebar"></div>
                                <div class="preview-body">
                                    <div class="preview-card-sm wide"></div>
                                    <div class="preview-card-sm wide"></div>
                                    <div class="preview-card-sm half"></div>
                                </div>
                            </div>
                            <div class="theme-option-label">☀️ Light Mode</div>
                            <i class="bi bi-check-circle-fill theme-check" id="check-light"></i>
                        </div>
                        <div class="theme-option" data-theme="dark" id="theme-opt-dark" onclick="setRcTheme('dark')" role="button" tabindex="0">
                            <div class="theme-preview dark-preview">
                                <div class="preview-sidebar"></div>
                                <div class="preview-body">
                                    <div class="preview-card-sm wide"></div>
                                    <div class="preview-card-sm wide"></div>
                                    <div class="preview-card-sm half"></div>
                                </div>
                            </div>
                            <div class="theme-option-label">🌙 Dark Mode</div>
                            <i class="bi bi-check-circle-fill theme-check" id="check-dark"></i>
                        </div>
                    </div>
                    <div class="theme-toggle-row">
                        <span><i class="bi bi-sun-fill" style="color:var(--color-warning-text);"></i> Light</span>
                        <label class="rc-switch mb-0 mx-auto">
                            <input type="checkbox" class="theme-switch-input" onchange="setRcTheme(this.checked ? 'dark' : 'light')">
                            <span class="rc-track"><span class="rc-thumb"></span></span>
                        </label>
                        <span><i class="bi bi-moon-stars-fill" style="color:var(--color-primary-text);"></i> Dark</span>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /.settings-content --}}
</div>{{-- /.settings-wrap --}}

@push('scripts')
<script>
function showSection(id, el, ev) {
    if (ev) ev.preventDefault();
    document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.settings-nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById('section-' + id).classList.add('active');
    el.classList.add('active');
}
        @include('includes.theme-toggle')
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.settings-nav-item').forEach(link => {
        link.addEventListener('click', function (ev) {
            // Let real navigation links (e.g. Learning Materials) work normally.
            if (link.classList.contains('settings-nav-link')) return;
            ev.preventDefault();
        });
    });
});
</script>
@endpush

@endsection
