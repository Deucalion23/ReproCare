@extends('bhw.layout')

@section('title', 'Profile & Settings - BHW Portal | ReproCare')

@push('styles')
<style>
    .settings-wrap         { display:flex; gap:1.5rem; align-items:flex-start; }
    .settings-sidebar      { width:250px; flex-shrink:0; position:sticky; top:80px; }
    .settings-content      { flex:1; min-width:0; }
    .settings-nav          { background:var(--color-surface); border:1px solid var(--color-border); border-radius:18px; overflow:hidden; padding:0.5rem; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent); }
    .settings-nav-item     { display:flex; align-items:center; gap:0.75rem; padding:0.7rem 0.9rem; border-radius:12px; font-size:0.875rem; font-weight:500; color:var(--color-text-muted); cursor:pointer; text-decoration:none; transition:all 0.2s ease; border:1px solid transparent; margin-bottom:0.15rem; }
    .settings-nav-item i   { font-size:1rem; width:1.15rem; text-align:center; flex-shrink:0; }
    .settings-nav-item:hover { background:var(--color-secondary-soft); color:var(--color-text); }
    .settings-nav-item.active { background:var(--color-secondary-soft); color:var(--color-secondary-text); border-color:var(--color-secondary-soft); font-weight:700; }
    .settings-section      { display:none; }
    .settings-section.active { display:block; }
    .pref-card             { background:var(--color-surface); border:1px solid var(--color-border); border-radius:18px; margin-bottom:1.25rem; overflow:hidden; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent); }
    .pref-card-header      { padding:1.1rem 1.4rem; border-bottom:1px solid var(--color-border); display:flex; align-items:center; gap:0.75rem; }
    .pref-card-header-icon { width:38px; height:38px; border-radius:11px; background:var(--color-secondary-soft); border:1px solid var(--color-secondary-soft); display:flex; align-items:center; justify-content:center; font-size:1rem; color:var(--color-secondary-text); flex-shrink:0; }
    .pref-card-header h6   { margin:0; font-family:'Plus Jakarta Sans', sans-serif; font-weight:700; font-size:0.95rem; color:var(--color-text); }
    .pref-card-header p    { margin:0; font-size:0.78rem; color:var(--color-text-muted); }
    .pref-card-body        { padding:1.25rem 1.4rem; }
    .pref-row { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:0.85rem 0; border-bottom:1px solid var(--color-border); }
    .pref-row:last-child { border-bottom:none; padding-bottom:0; }
    .pref-row-label h6 { margin:0 0 0.15rem; font-size:0.9rem; font-weight:600; color:var(--color-text); }
    .pref-row-label p  { margin:0; font-size:0.8rem; color:var(--color-text-muted); }
    .locked-note { display:flex; align-items:center; gap:.45rem; font-size:.78rem; color:var(--color-text-muted); background:var(--color-bg); border:1px dashed var(--color-border); border-radius:10px; padding:.55rem .8rem; margin-top:.9rem; }
    .stat-chip { display:flex; align-items:center; gap:.7rem; background:var(--color-bg); border:1px solid var(--color-border); border-radius:14px; padding:.85rem 1rem; }
    .stat-chip .n { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.5rem; font-weight:800; color:var(--color-text); line-height:1; }
    .stat-chip .l { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted); }
    .verified-badge { display:inline-flex; align-items:center; gap:.35rem; font-weight:700; border-radius:9999px; padding:0.45rem 0.9rem; font-size:0.75rem; border:1px solid; white-space:nowrap; }
    .verified-badge.ok  { background:var(--color-success-soft); color:var(--color-success-text); border-color:var(--color-success-soft); }
    .verified-badge.warn{ background:var(--color-warning-soft); color:var(--color-warning-text); border-color:var(--color-warning); }
    .verified-badge.info{ background:var(--color-secondary-soft); color:var(--color-secondary-text); border-color:var(--color-secondary-soft); }
    .theme-option-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1rem; }
    .theme-option { border:2px solid var(--color-border); border-radius:14px; padding:1rem; cursor:pointer; text-align:center; transition:all 0.25s ease; position:relative; background:var(--color-bg); }
    .theme-option:hover { border-color:var(--color-secondary); transform:translateY(-2px); }
    .theme-option.active { border-color:var(--color-secondary); background:var(--color-secondary-soft); }
    .theme-preview { border-radius:10px; overflow:hidden; height:70px; display:flex; margin-bottom:0.75rem; border:1px solid var(--color-border); }
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
    .theme-toggle-row { display:flex; align-items:center; gap:0.9rem; margin-top:1.25rem; padding:0.85rem 1rem; background:var(--color-bg); border:1px solid var(--color-border); border-radius:12px; }
    .theme-toggle-row span { font-size:0.875rem; color:var(--color-text-muted); font-weight:500; display:flex; align-items:center; gap:0.4rem; }
    @media (max-width: 768px) {
        .settings-wrap { flex-direction:column; }
        .settings-sidebar { width:100%; position:static; }
    }
</style>
@endpush

@section('bhw-content')

@php
    $bhwUser = auth()->user();
    $reportStatusMap = [
        'draft' => ['Draft', 'warn'],
        'submitted_to_president' => ['Submitted · awaiting President', 'warn'],
        'approved_by_president' => ['Endorsed by President', 'ok'],
        'submitted_to_midwife' => ['Submitted · awaiting Midwife', 'warn'],
        'approved_by_midwife' => ['Approved', 'ok'],
        'rejected' => ['Returned for revision', 'warn'],
        'needs_revision' => ['Needs revision — see reviewer note', 'warn'],
    ];
    $reportStatus = $currentReport ? ($reportStatusMap[$currentReport->submission_status] ?? ['Submitted', 'info']) : null;
@endphp

<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="position-relative flex-shrink-0">
                <img src="{{ $bhwUser->profile_image_url }}"
                     alt="{{ $bhwUser->name }}"
                     style="width:56px;height:56px;border-radius:16px;object-fit:cover;border:2px solid var(--color-secondary-soft);"
                     onerror="this.onerror=null;this.src='{{ $bhwUser->gender === 'male' ? '/images/avatars/avatar-male.svg' : '/images/avatars/avatar-female.svg' }}';">
            </div>
            <div>
                <div class="page-hero-title">{{ $bhwUser->name }}</div>
                <p class="page-hero-subtitle mb-0">
                    <i class="bi bi-person-workspace me-1"></i>Barangay Health Worker
                    @if($bhwUser->purok)&nbsp;·&nbsp; {{ $bhwUser->purok->name }}@endif
                </p>
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
            <a class="settings-nav-item active" onclick="showSection('profile', this)" href="#">
                <i class="bi bi-person-badge-fill" style="color:var(--color-secondary-text);"></i> Personal Profile
            </a>
            <a class="settings-nav-item" onclick="showSection('field', this)" href="#">
                <i class="bi bi-pin-map-fill" style="color:var(--color-info-text);"></i> Field Assignment
            </a>
            <a class="settings-nav-item" onclick="showSection('tasks', this)" href="#">
                <i class="bi bi-bell-fill" style="color:var(--color-success-text);"></i> Task &amp; Notifications
            </a>
            <a class="settings-nav-item" onclick="showSection('performance', this)" href="#">
                <i class="bi bi-bar-chart-fill" style="color:var(--color-primary-text);"></i> Performance &amp; Reports
            </a>
            <a class="settings-nav-item" onclick="showSection('security', this)" href="#">
                <i class="bi bi-shield-lock-fill" style="color:var(--color-peach-text);"></i> Account Security
            </a>
            <a class="settings-nav-item" onclick="showSection('activity', this)" href="#">
                <i class="bi bi-clock-history" style="color:var(--color-warning-text);"></i> Recent Activity
            </a>
            <a class="settings-nav-item" onclick="showSection('appearance', this)" href="#">
                <i class="bi bi-palette-fill" style="color:var(--color-primary-text);"></i> Appearance
            </a>
        </div>
    </div>

    <div class="settings-content">

        {{-- ══ 1. PERSONAL PROFILE ══ --}}
        <div class="settings-section active" id="section-profile">
            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-person-badge-fill"></i></div>
                    <div class="d-flex justify-content-between align-items-center flex-grow-1 flex-wrap gap-2">
                        <div><h6>Personal Profile</h6><p>Field-worker identity &amp; contact channels</p></div>
                        <span class="verified-badge {{ $bhwUser->isApproved() ? 'ok' : 'warn' }}">
                            <i class="bi bi-{{ $bhwUser->isApproved() ? 'patch-check-fill' : 'hourglass-split' }}"></i>
                            {{ $bhwUser->isApproved() ? 'Active Field Worker' : 'Pending Verification' }}
                        </span>
                    </div>
                </div>
                <div class="pref-card-body">
                    {{-- Full profile show card, displayed inside settings --}}
                    @include('bhw.profile.partials.profile-card', ['user' => $bhwUser, 'actions' => ['edit']])

                    <form method="POST" action="{{ route('bhw.settings.update') }}" class="mt-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="profile">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold" style="font-size:0.8rem;">Full Name</label>
                                <input type="text" class="form-control" value="{{ $bhwUser->name }}" readonly style="background:var(--color-bg);font-weight:600;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" style="font-size:0.8rem;">Birthday</label>
                                <input type="text" class="form-control" value="{{ $bhwUser->date_of_birth ? $bhwUser->date_of_birth->format('F d, Y') : 'Not set' }}" readonly style="background:var(--color-bg);font-weight:600;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.8rem;">Primary Email (login)</label>
                                <input type="email" class="form-control" value="{{ $bhwUser->email }}" readonly style="background:var(--color-bg);font-weight:600;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.8rem;">Secondary Email</label>
                                <input type="email" name="secondary_email" class="form-control" value="{{ old('secondary_email', $bhwUser->secondary_email) }}" placeholder="Backup email for coordination">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.8rem;">Primary Phone</label>
                                <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $bhwUser->contact_number) }}" placeholder="09xx xxx xxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size:0.8rem;">Secondary Phone</label>
                                <input type="text" name="secondary_contact" class="form-control" value="{{ old('secondary_contact', $bhwUser->secondary_contact) }}" placeholder="Backup phone for field coordination">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold" style="font-size:0.8rem;">Residential Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $bhwUser->address) }}" placeholder="House / street within assigned barangay">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-primary" style="border-radius:10px;font-weight:700;">
                                    <i class="bi bi-check-circle me-1"></i> Save Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ══ 2. COMMUNITY FIELD ASSIGNMENT ══ --}}
        <div class="settings-section" id="section-field">
            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:var(--color-info-soft);border-color:var(--color-info-soft);color:var(--color-info-text);"><i class="bi bi-pin-map-fill"></i></div>
                    <div><h6>Community Field Assignment</h6><p>Your operational context — managed by RHU Admin / BHW President</p></div>
                </div>
                <div class="pref-card-body">
                    <h6 class="fw-800 mb-2" style="font-size:0.82rem;">Assigned Purok(s)</h6>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @forelse($assignedPuroks as $purok)
                            <span class="verified-badge info"><i class="bi bi-geo-alt-fill"></i> {{ $purok->name }}{{ $purok->barangay ? ' · '.$purok->barangay : '' }}</span>
                        @empty
                            <span class="text-muted" style="font-size:0.85rem;">No purok assigned yet — contact your BHW President.</span>
                        @endforelse
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 h-100" style="background:var(--color-bg);border:1px solid var(--color-border);">
                                <small class="text-uppercase fw-bold text-muted" style="font-size:0.72rem;letter-spacing:.05em;">Designated BHW President</small>
                                <div class="fw-bold mt-1" style="color:var(--color-text);">{{ $president?->name ?? 'Not assigned' }}</div>
                                <small class="text-muted">{{ $president?->contact_number ?? 'Reports & tasks flow through this officer' }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 h-100" style="background:var(--color-bg);border:1px solid var(--color-border);">
                                <small class="text-uppercase fw-bold text-muted" style="font-size:0.72rem;letter-spacing:.05em;">Assigned Rural Health Unit</small>
                                <div class="fw-bold mt-1" style="color:var(--color-text);">{{ $bhwUser->rhu_assignment ?? 'San Carlos RHU Main' }}</div>
                                <small class="text-muted">City Health Office — San Carlos City</small>
                            </div>
                        </div>
                    </div>
                    <div class="locked-note"><i class="bi bi-lock-fill"></i> These assignments are read-only. Changes are made by the RHU Admin or BHW President.</div>
                </div>
            </div>
        </div>

        {{-- ══ 3. OPERATIONAL TASK & NOTIFICATION PREFERENCES ══ --}}
        <div class="settings-section" id="section-tasks">
            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:var(--color-success-soft);border-color:var(--color-success-soft);color:var(--color-success-text);"><i class="bi bi-bell-fill"></i></div>
                    <div><h6>Operational Task &amp; Notification Preferences</h6><p>Control daily field alerts and report reminders</p></div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('bhw.settings.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="preferences">
                        <h6 class="fw-800 mb-1" style="font-size:0.85rem;color:var(--color-text);">New Patient Registration Alerts</h6>
                        <p class="text-muted small mb-2">When new patients are assigned to your purok.</p>
                        <div class="pref-row"><div class="pref-row-label"><h6>Email alerts</h6><p>Registration notices to your inbox</p></div>
                            <label class="rc-switch"><input type="checkbox" name="pref_registration_email" value="1" {{ $bhwUser->pref_registration_email ? 'checked' : '' }}><span class="rc-track"><span class="rc-thumb"></span></span></label></div>
                        <div class="pref-row"><div class="pref-row-label"><h6>SMS alerts</h6><p>Text notices to your contact number</p></div>
                            <label class="rc-switch"><input type="checkbox" name="pref_registration_sms" value="1" {{ $bhwUser->pref_registration_sms ? 'checked' : '' }}><span class="rc-track"><span class="rc-thumb"></span></span></label></div>
                        <div class="pref-row"><div class="pref-row-label"><h6>Dashboard alerts</h6><p>In-portal banners for new assignments</p></div>
                            <label class="rc-switch"><input type="checkbox" name="pref_registration_dashboard" value="1" {{ $bhwUser->pref_registration_dashboard ? 'checked' : '' }}><span class="rc-track"><span class="rc-thumb"></span></span></label></div>

                        <h6 class="fw-800 mt-4 mb-1" style="font-size:0.85rem;color:var(--color-text);">Patient Risk Alerts (Decision Support)</h6>
                        <p class="text-muted small mb-2">Rule-based clinical risks in your patients (e.g. high BP, low Hgb).</p>
                        <div class="pref-row"><div class="pref-row-label"><h6>Email alerts</h6><p>Risk flags to your inbox</p></div>
                            <label class="rc-switch"><input type="checkbox" name="pref_high_risk_email" value="1" {{ $bhwUser->pref_high_risk_email ? 'checked' : '' }}><span class="rc-track"><span class="rc-thumb"></span></span></label></div>
                        <div class="pref-row"><div class="pref-row-label"><h6>SMS alerts</h6><p>Urgent risk flags by text</p></div>
                            <label class="rc-switch"><input type="checkbox" name="pref_high_risk_sms" value="1" {{ $bhwUser->pref_high_risk_sms ? 'checked' : '' }}><span class="rc-track"><span class="rc-thumb"></span></span></label></div>
                        <div class="pref-row"><div class="pref-row-label"><h6>Dashboard alerts</h6><p>Risk banners inside the portal</p></div>
                            <label class="rc-switch"><input type="checkbox" name="pref_high_risk_dashboard" value="1" {{ $bhwUser->pref_high_risk_dashboard ? 'checked' : '' }}><span class="rc-track"><span class="rc-thumb"></span></span></label></div>

                        <h6 class="fw-800 mt-4 mb-1" style="font-size:0.85rem;color:var(--color-text);">Care Facilitation</h6>
                        <div class="pref-row"><div class="pref-row-label"><h6>Maternal checkup reminders</h6><p>Reminders to facilitate prenatal / postnatal care visits</p></div>
                            <label class="rc-switch"><input type="checkbox" name="pref_checkup_reminders" value="1" {{ $bhwUser->pref_checkup_reminders ? 'checked' : '' }}><span class="rc-track"><span class="rc-thumb"></span></span></label></div>

                        <h6 class="fw-800 mt-4 mb-1" style="font-size:0.85rem;color:var(--color-text);">Monthly Report Submission Reminders</h6>
                        <div class="mb-3" style="max-width:320px;">
                            <label class="form-label fw-bold" style="font-size:0.8rem;">Reminder frequency</label>
                            <select name="pref_report_summary" class="form-select" style="border-radius:12px;">
                                @foreach(['monthly' => 'Monthly digest', 'weekly' => 'Weekly digest', 'off' => 'Off'] as $val => $label)
                                    <option value="{{ $val }}" {{ ($bhwUser->pref_report_summary ?? 'monthly') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary" style="border-radius:10px;font-weight:700;">
                            <i class="bi bi-check-circle me-1"></i> Save Preferences
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ══ 4. MONTHLY PERFORMANCE & REPORTS ══ --}}
        <div class="settings-section" id="section-performance">
            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:var(--color-primary-soft);border-color:var(--color-border);color:var(--color-primary-text);"><i class="bi bi-bar-chart-fill"></i></div>
                    <div class="d-flex justify-content-between align-items-center flex-grow-1 flex-wrap gap-2">
                        <div><h6>Monthly Performance Summary</h6><p>Your field-work indicators at a glance</p></div>
                        @if($currentReport)
                            <span class="verified-badge {{ $reportStatus[1] === 'ok' ? 'ok' : 'warn' }}">
                                <i class="bi bi-file-earmark-text-fill"></i> {{ now()->format('F Y') }}: {{ $reportStatus[0] }}
                            </span>
                        @else
                            <span class="verified-badge warn"><i class="bi bi-exclamation-triangle-fill"></i> {{ now()->format('F Y') }}: No report yet</span>
                        @endif
                    </div>
                </div>
                <div class="pref-card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3"><div class="stat-chip"><div><div class="n">{{ $stats['registered'] }}</div><div class="l">Patients Enrolled</div></div></div></div>
                        <div class="col-6 col-md-3"><div class="stat-chip"><div><div class="n">{{ $stats['checkupsFacilitated'] }}</div><div class="l">Checkups Facilitated</div></div></div></div>
                        <div class="col-6 col-md-3"><div class="stat-chip"><div><div class="n">{{ $stats['recordsAdded'] }}</div><div class="l">Records Added</div></div></div></div>
                        <div class="col-6 col-md-3"><div class="stat-chip"><div><div class="n">{{ $stats['highRiskFlags'] }}</div><div class="l">High-Risk Flags</div></div></div></div>
                    </div>
                    <a href="{{ route('bhw.reports.index') }}" class="btn btn-sm btn-primary" style="border-radius:10px;font-weight:700;">
                        <i class="bi bi-journal-text me-1"></i> View Full Report History
                    </a>
                </div>
            </div>
        </div>

        {{-- ══ 5. ACCOUNT SECURITY ══ --}}
        <div class="settings-section" id="section-security">
            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:var(--color-warning-soft);border-color:var(--color-warning);color:var(--color-warning-text);"><i class="bi bi-key-fill"></i></div>
                    <div><h6>Change Password</h6><p>Protect access to patient data</p></div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('bhw.settings.update') }}" class="row g-3">
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
                    <div><h6>Two-Factor Authentication (2FA)</h6><p>Extra protection for your field account</p></div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('bhw.settings.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="2fa">
                        <div class="pref-row">
                            <div class="pref-row-label">
                                <h6>Require 2FA on login {!! $bhwUser->pref_2fa_enabled ? '<span class="verified-badge ok ms-1">Enabled</span>' : '<span class="verified-badge warn ms-1">Disabled</span>' !!}</h6>
                                <p>Sign-in asks for an authenticator-app code plus your password</p>
                            </div>
                            <label class="rc-switch"><input type="checkbox" name="pref_2fa_enabled" value="1" {{ $bhwUser->pref_2fa_enabled ? 'checked' : '' }} onchange="this.form.submit()"><span class="rc-track"><span class="rc-thumb"></span></span></label>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ══ 6. RECENT ACTIVITY & AUDIT LOG ══ --}}
        <div class="settings-section" id="section-activity">
            <div class="pref-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-clock-history"></i></div>
                    <div><h6>Recent Activity</h6><p>Your essential system actions for accountability</p></div>
                </div>
                <div class="pref-card-body">
                    @forelse($recentActivity as $log)
                        <div class="pref-row">
                            <div class="pref-row-label">
                                <h6>{{ $log->description }}</h6>
                                <p>{{ ucfirst($log->action) }} · {{ $log->created_at->format('M j, Y g:i A') }} · {{ $log->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="verified-badge {{ in_array($log->action, ['approve', 'create']) ? 'ok' : 'info' }}">{{ ucfirst($log->action) }}</span>
                        </div>
                    @empty
                        <div class="text-muted" style="font-size:0.85rem;">No activity recorded yet — registrations and report submissions will appear here.</div>
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

    </div>
</div>

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
        link.addEventListener('click', function (ev) { ev.preventDefault(); });
    });
});
</script>
@endpush

@endsection
