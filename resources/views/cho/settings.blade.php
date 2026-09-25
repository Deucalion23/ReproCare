@extends('cho.layout')

@section('title', 'Settings - CHO Portal | ReproCare')

@push('styles')
<style>
    .settings-wrap         { display:flex; gap:1.5rem; align-items:flex-start; }
    .settings-sidebar      { width:240px; flex-shrink:0; position:sticky; top:80px; }
    .settings-content      { flex:1; min-width:0; }

    .settings-nav          { background:var(--color-surface); background-color:var(--color-surface); border:none; border-radius:18px; overflow:hidden; padding:0.5rem; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent); }
    .settings-nav-item     { display:flex; align-items:center; gap:0.75rem; padding:0.7rem 0.9rem; border-radius:12px; font-size:0.875rem; font-weight:600; color:var(--color-text); cursor:pointer; text-decoration:none; transition:all 0.2s ease; border:none; margin-bottom:0.15rem; }
    .settings-nav-item i   { font-size:1rem; width:1.15rem; text-align:center; flex-shrink:0; color:var(--color-text-muted) !important; }
    .settings-nav-item:hover { background:var(--color-bg); background-color:var(--color-bg); color:var(--color-text); }
    .settings-nav-item.active { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:var(--color-secondary-text); border:none; font-weight:800; }
    .settings-nav-item.active i { color:var(--color-secondary-text) !important; }

    .settings-section      { display:none; }
    .settings-section.active { display:block; }

    .pref-card             { background:var(--color-surface); background-color:var(--color-surface); border:none; border-radius:18px; margin-bottom:1.25rem; overflow:hidden; transition:background 0.4s ease, border-color 0.3s ease; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent); }
    .pref-card-header      { padding:1.1rem 1.4rem; border:none; display:flex; align-items:center; gap:0.75rem; }
    .pref-card-header-icon { width:38px; height:38px; border-radius:12px; background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; border:none !important; display:flex; align-items:center; justify-content:center; font-size:1rem; color:var(--color-text) !important; flex-shrink:0; }
    .pref-card-header h6   { margin:0; font-family:'Plus Jakarta Sans', sans-serif; font-weight:800; font-size:0.95rem; color:var(--color-text); }
    .pref-card-header p    { margin:0; font-size:0.78rem; color:var(--color-text-muted); }
    .pref-card-body        { padding:1.25rem 1.4rem; }
    .pref-card-body .form-control, .pref-card-body .form-select { background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; border:none !important; border-radius:12px !important; color:var(--color-text) !important; }
    .pref-card-body .form-control:focus, .pref-card-body .form-select:focus { background:var(--color-surface) !important; background-color:var(--color-surface) !important; border:none !important; box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent) !important; }
    .pref-card-body .form-control::placeholder { color:var(--color-text-muted); }
    .pref-card-body .form-label { color:var(--color-text); font-weight:700; font-size:0.82rem; }

    .pref-row { display:flex; align-items:center; justify-content:space-between; padding:0.85rem 0; border-bottom:1px solid var(--border); }
    .pref-row:last-child { border-bottom:none; padding-bottom:0; }
    .pref-row-label h6 { margin:0 0 0.15rem; font-size:0.9rem; font-weight:600; color:var(--text); }
    .pref-row-label p  { margin:0; font-size:0.8rem; color:var(--text-muted); }

    .theme-option-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1rem; }
    .theme-option {
        border:2px solid var(--border);
        border-radius:14px;
        padding:1rem;
        cursor:pointer;
        text-align:center;
        transition:all 0.25s ease;
        position:relative;
        background:var(--bg-card2);
    }
    .theme-option:hover { border-color:var(--primary); transform:translateY(-2px); }
    .theme-option.active { border-color:var(--primary); background:var(--primary-subtle); }

    .theme-preview {
        border-radius:10px;
        overflow:hidden;
        height:70px;
        display:flex;
        margin-bottom:0.75rem;
        border:1px solid var(--border);
    }
    .preview-sidebar { width:28%; background:var(--color-primary-text); }
    .preview-body { flex:1; padding:0.4rem; display:flex; flex-direction:column; gap:0.25rem; background:var(--color-bg); }
    .preview-card-sm { height:14px; background:var(--color-surface-strong); border-radius:4px; }
    .preview-card-sm.wide { width:100%; }
    .preview-card-sm.half { width:60%; }

    .light-preview .preview-sidebar { background:var(--color-primary-soft); }
    .light-preview .preview-body    { background:var(--color-primary-soft); }
    .light-preview .preview-card-sm { background:var(--color-surface); }

    .theme-option-label { font-size:0.85rem; font-weight:600; color:var(--text); }
    .theme-check { position:absolute; top:0.6rem; right:0.6rem; font-size:1rem; color:var(--primary); display:none; }
    .theme-option.active .theme-check { display:inline-block; }

    .theme-toggle-row { display:flex; align-items:center; gap:0.9rem; margin-top:1.25rem; padding:0.85rem 1rem; background:var(--bg-card2); border:1px solid var(--border); border-radius:12px; }
    .theme-toggle-row span { font-size:0.875rem; color:var(--text-muted); font-weight:500; display:flex; align-items:center; gap:0.4rem; }

    .danger-zone { border:1px solid color-mix(in srgb, var(--color-danger) 25%, transparent); border-radius:16px; padding:1.25rem 1.4rem; background:color-mix(in srgb, var(--color-danger) 5%, transparent); }
    .danger-zone h6 { color:var(--color-danger-text); font-weight:700; margin-bottom:0.75rem; display:flex; align-items:center; gap:0.5rem; }

    .session-badge { display:inline-flex; align-items:center; gap:0.4rem; padding:0.25rem 0.7rem; background:color-mix(in srgb, var(--color-success) 12%, transparent); border:1px solid color-mix(in srgb, var(--color-success) 30%, transparent); border-radius:20px; font-size:0.78rem; font-weight:600; color:var(--color-success-text); }

    .settings-page-header { margin-bottom:1.75rem; }
    .settings-page-header h1 { font-family:'Plus Jakarta Sans', sans-serif; font-size:1.5rem; font-weight:800; color:var(--text); margin-bottom:0.2rem; }
    .settings-page-header p  { font-size:0.875rem; color:var(--text-muted); margin:0; }

    @media (max-width: 768px) {
        .settings-wrap { flex-direction:column; }
        .settings-sidebar { width:100%; position:static; }
        .theme-option-grid { grid-template-columns:1fr 1fr; }
    }
</style>
@endpush

@section('cho-content')

<div class="settings-page-header pref-card fade-in-card p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>Settings — City-Wide System Level</h1>
            <p style="font-size:0.9rem; color:var(--color-text); font-weight:600; margin:0;">Clinical thresholds, office profile, maintenance, audit retention, and SMS gateway for all of San Carlos City</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="settings-wrap">
    <div class="settings-sidebar fade-in-card">
        <div class="settings-nav">
            <a class="settings-nav-item active" onclick="showSection('myprofile', this)" href="#">
                <i class="bi bi-person-circle" style="color:var(--primary-light);"></i>
                My Profile
            </a>
            <a class="settings-nav-item" onclick="showSection('thresholds', this)" href="#">
                <i class="bi bi-heart-pulse-fill" style="color:var(--color-danger-text);"></i>
                Risk Thresholds
            </a>
            <a class="settings-nav-item" onclick="showSection('office', this)" href="#">
                <i class="bi bi-building-fill" style="color:var(--info);"></i>
                Office Profile
            </a>
            <a class="settings-nav-item" onclick="showSection('maintenance', this)" href="#">
                <i class="bi bi-hdd-stack-fill" style="color:var(--warning);"></i>
                Maintenance &amp; Backups
            </a>
            <a class="settings-nav-item" onclick="showSection('audit', this)" href="#">
                <i class="bi bi-journal-text" style="color:var(--accent-violet);"></i>
                Audit Logs
            </a>
            <a class="settings-nav-item" onclick="showSection('sms', this)" href="#">
                <i class="bi bi-chat-dots-fill" style="color:var(--success);"></i>
                SMS Gateway
            </a>
            <a class="settings-nav-item" data-appearance-nav onclick="showSection('appearance', this)" href="#">
                <i class="bi bi-palette-fill" style="color:var(--primary-light);"></i>
                Appearance
            </a>
            <a class="settings-nav-item" onclick="showSection('account', this)" href="#">
                <i class="bi bi-person-fill" style="color:var(--info);"></i>
                Account
            </a>
            <a class="settings-nav-item" onclick="showSection('security', this)" href="#">
                <i class="bi bi-shield-lock-fill" style="color:var(--warning);"></i>
                Security
            </a>
            <a class="settings-nav-item" href="{{ route('cho.handover.index') }}">
                <i class="bi bi-arrow-left-right" style="color:var(--color-danger-text);"></i>
                Role Handover
            </a>
        </div>
    </div>

    <div class="settings-content">
        {{-- MY PROFILE (CHO Super Admin) --}}
        <div class="settings-section active" id="section-myprofile">
            @php $me = auth()->user(); @endphp
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-person-badge-fill"></i></div>
                    <div>
                        <h6>Personal &amp; Professional Identity</h6>
                        <p>Full name, official title, plantilla ID, and license credentials</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('cho.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="myprofile">
                        <div class="col-md-4">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" value="{{ old('first_name', $me->first_name) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">M.I.</label>
                            <input type="text" class="form-control" name="middle_initial" value="{{ old('middle_initial', $me->middle_initial) }}" maxlength="5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" value="{{ old('last_name', $me->last_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Official Title</label>
                            <input type="text" class="form-control" name="official_title" value="{{ old('official_title', $me->official_title) }}" placeholder="e.g. City Health Officer">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employee / Plantilla ID</label>
                            <input type="text" class="form-control" name="employee_id" value="{{ old('employee_id', $me->employee_id) }}" placeholder="e.g. CHO-2024-001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Medical License No.</label>
                            <input type="text" class="form-control" name="license_number" value="{{ old('license_number', $me->license_number) }}" placeholder="e.g. PRC-0123456">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">License Expiry</label>
                            <input type="date" class="form-control" name="license_expiry" value="{{ old('license_expiry', optional($me->license_expiry)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Specialization</label>
                            <input type="text" class="form-control" name="specialization" value="{{ old('specialization', $me->specialization) }}" placeholder="e.g. Public Health">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Primary Mobile (Emergency Escalations)</label>
                            <input type="text" class="form-control" name="contact_number" value="{{ old('contact_number', $me->contact_number) }}" placeholder="09xxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Emergency Mobile (High-Priority)</label>
                            <input type="text" class="form-control" name="emergency_mobile" value="{{ old('emergency_mobile', $me->emergency_mobile) }}" placeholder="09xxxxxxxxx">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Office Telephone Extension</label>
                            <input type="text" class="form-control" name="office_extension" value="{{ old('office_extension', $me->office_extension) }}" placeholder="e.g. loc. 102">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Government Email (login)</label>
                            <input type="email" class="form-control" value="{{ $me->email }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Secondary Email</label>
                            <input type="email" class="form-control" name="secondary_email" value="{{ old('secondary_email', $me->secondary_email) }}" placeholder="name@san-carlos.gov.ph">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save My Profile</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-info) 12%, transparent);color:var(--color-info-text);"><i class="bi bi-pen-fill"></i></div>
                    <div>
                        <h6>Official Digital Signature</h6>
                        <p>Auto-stamps mortality audits, monthly reports, and executive summaries</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    @if($me->signature_image_url)
                        <div class="d-flex align-items-center gap-3 mb-3 p-3 border rounded-3 bg-light">
                            <img src="{{ $me->signature_image_url }}" alt="Official signature" style="max-height:80px;max-width:260px;object-fit:contain;background:var(--color-surface);border:1px solid var(--border);border-radius:8px;padding:6px;">
                            <form method="POST" action="{{ route('cho.settings.update') }}" onsubmit="return confirm('Remove your official digital signature?')">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="section" value="signature">
                                <input type="hidden" name="remove_signature" value="1">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i> Remove</button>
                            </form>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('cho.settings.update') }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="signature">
                        <div class="col-md-8">
                            <label class="form-label">Upload Signature (PNG/JPG, max 2MB)</label>
                            <input type="file" class="form-control" name="signature_image" accept="image/png,image/jpeg" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-upload me-1"></i> Upload Signature</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-success) 12%, transparent);color:var(--color-success-text);"><i class="bi bi-bell-fill"></i></div>
                    <div>
                        <h6>Executive Notification Preferences</h6>
                        <p>High-level system alert channels for this account</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('cho.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="exec_notifications">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="pref_mortality_alerts" value="1" id="choNotifMort" {{ $me->pref_mortality_alerts ? 'checked' : '' }}>
                                <label class="form-check-label" for="choNotifMort">Maternal morbidity / mortality flags</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="pref_audit_warnings" value="1" id="choNotifAudit" {{ $me->pref_audit_warnings ? 'checked' : '' }}>
                                <label class="form-check-label" for="choNotifAudit">Critical system audit warnings</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="pref_compliance_updates" value="1" id="choNotifComp" {{ $me->pref_compliance_updates ? 'checked' : '' }}>
                                <label class="form-check-label" for="choNotifComp">City-wide reporting compliance updates</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="pref_escalation_alerts" value="1" id="choNotifEsc" {{ $me->pref_escalation_alerts ? 'checked' : '' }}>
                                <label class="form-check-label" for="choNotifEsc">High-risk escalation alerts</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Preferences</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- RISK THRESHOLDS --}}
        <div class="settings-section" id="section-thresholds">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-danger) 12%, transparent);color:var(--color-danger-text);"><i class="bi bi-heart-pulse-fill"></i></div>
                    <div>
                        <h6>Global Decision-Support Thresholds</h6>
                        <p>City-wide clinical risk rules governing automated triage flags</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('cho.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="thresholds">
                        <div class="col-md-6">
                            <label class="form-label">High systolic BP (mmHg)</label>
                            <input type="number" step="1" name="bp_systolic_high" class="form-control" value="{{ old('bp_systolic_high', \App\Models\Setting::get('threshold.bp_systolic_high', 140)) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">High diastolic BP (mmHg)</label>
                            <input type="number" step="1" name="bp_diastolic_high" class="form-control" value="{{ old('bp_diastolic_high', \App\Models\Setting::get('threshold.bp_diastolic_high', 90)) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Low hemoglobin (g/dL)</label>
                            <input type="number" step="0.1" name="hemoglobin_low" class="form-control" value="{{ old('hemoglobin_low', \App\Models\Setting::get('threshold.hemoglobin_low', 10)) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max gestational age (weeks)</label>
                            <input type="number" step="1" name="gestational_age_max" class="form-control" value="{{ old('gestational_age_max', \App\Models\Setting::get('threshold.gestational_age_max', 42)) }}" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Thresholds</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- OFFICE PROFILE --}}
        <div class="settings-section" id="section-office">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-building-fill"></i></div>
                    <div>
                        <h6>City Health Office Profile</h6>
                        <p>Official contact details and director signature used on formal reports</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('cho.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="office">
                        <div class="col-12">
                            <label class="form-label">Office name</label>
                            <input type="text" name="office_name" class="form-control" value="{{ old('office_name', \App\Models\Setting::get('cho.office_name')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact number</label>
                            <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', \App\Models\Setting::get('cho.contact_number')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City health director (report signature)</label>
                            <input type="text" name="director_name" class="form-control" value="{{ old('director_name', \App\Models\Setting::get('cho.director_name')) }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Office Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MAINTENANCE & BACKUPS --}}
        <div class="settings-section" id="section-maintenance">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-warning) 12%, transparent);color:var(--warning);"><i class="bi bi-hdd-stack-fill"></i></div>
                    <div>
                        <h6>System Maintenance &amp; Backups</h6>
                        <p>Scheduled backups, manual exports, and maintenance mode</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <div class="d-flex gap-2 flex-wrap mb-4">
                        <a href="{{ route('cho.database.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-database me-1"></i> Open Backup Manager
                        </a>
                        <a href="{{ route('cho.reports.export.csv') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-filetype-csv me-1"></i> Export Reports CSV
                        </a>
                    </div>
                    <form method="POST" action="{{ route('cho.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="maintenance">
                        <div class="col-md-6">
                            <label class="form-label">Maintenance mode ({{ app()->isDownForMaintenance() ? 'currently ON' : 'currently OFF' }})</label>
                            <select name="maintenance_mode" class="form-select" required>
                                <option value="off">Off — system live</option>
                                <option value="on">On — take system offline</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Toggle maintenance mode?');">
                                <i class="bi bi-exclamation-triangle me-1"></i> Apply
                            </button>
                        </div>
                    </form>
                    <p class="text-muted small mt-3 mb-0">Tip: schedule <code>php artisan activity-logs:prune</code> alongside your backup routine.</p>
                </div>
            </div>
        </div>

        {{-- AUDIT LOGS --}}
        <div class="settings-section" id="section-audit">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-journal-text"></i></div>
                    <div>
                        <h6>Global Audit Log Settings</h6>
                        <p>Retention policy for logins, record modifications, and risk overrides</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('cho.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="audit">
                        <div class="col-md-6">
                            <label class="form-label">Retain activity logs (days)</label>
                            <input type="number" name="retention_days" class="form-control" value="{{ old('retention_days', \App\Models\Setting::get('audit.retention_days', 365)) }}" min="30" max="3650" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="prune_now" value="1" class="form-check-input" id="pruneNow">
                                <label class="form-check-label" for="pruneNow">Prune old logs immediately</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Retention Policy</button>
                            <a href="{{ route('cho.logs.index') }}" class="btn btn-outline-secondary ms-2"><i class="bi bi-list-ul me-1"></i> View Audit Logs</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- SMS GATEWAY --}}
        <div class="settings-section" id="section-sms">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-success) 12%, transparent);color:var(--color-success-text);"><i class="bi bi-chat-dots-fill"></i></div>
                    <div>
                        <h6>SMS / Gateway Configuration</h6>
                        <p>Central gateway for automated patient and worker notifications (overrides .env when filled)</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('cho.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="sms">
                        <div class="col-md-6">
                            <label class="form-label">Provider</label>
                            <select name="provider" class="form-select" required>
                                <option value="movider" {{ \App\Models\Setting::get('sms.provider', 'movider') === 'movider' ? 'selected' : '' }}>Movider</option>
                                <option value="textbee" {{ \App\Models\Setting::get('sms.provider') === 'textbee' ? 'selected' : '' }}>TextBee (Android gateway)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mode</label>
                            <select name="mock" class="form-select" required>
                                <option value="1" {{ \App\Models\Setting::get('sms.mock', '1') === '1' ? 'selected' : '' }}>Mock — log only, no live SMS</option>
                                <option value="0" {{ \App\Models\Setting::get('sms.mock') === '0' ? 'selected' : '' }}>Live — send real SMS</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">TextBee API key</label>
                            <input type="text" name="textbee_api_key" class="form-control" value="{{ old('textbee_api_key', \App\Models\Setting::get('sms.textbee_api_key')) }}" autocomplete="off">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">TextBee device ID</label>
                            <input type="text" name="textbee_device_id" class="form-control" value="{{ old('textbee_device_id', \App\Models\Setting::get('sms.textbee_device_id')) }}" autocomplete="off">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save SMS Config</button>
                            <a href="{{ route('cho.sms.index') }}" class="btn btn-outline-secondary ms-2"><i class="bi bi-envelope me-1"></i> SMS Logs</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- APPEARANCE --}}
        <div class="settings-section" id="section-appearance">
            @include('cho.partials.appearance-settings')
        </div>

        {{-- ACCOUNT --}}
        <div class="settings-section" id="section-account">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-person-fill"></i></div>
                    <div>
                        <h6>Account Information</h6>
                        <p>Update your personal details</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="{{ auth()->user()->email }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="City Health Officer (CHO)" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Office</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->cho_office ?? 'City Health Office' }}" readonly>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- SECURITY --}}
        <div class="settings-section" id="section-security">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-warning) 12%, transparent);color:var(--warning);">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <div>
                        <h6>Change Password</h6>
                        <p>Update your login password</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('cho.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="password">
                        <div class="col-12">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" placeholder="Enter current password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" placeholder="Min. 8 characters" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Repeat new password" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-shield-lock me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-primary) 12%, transparent);color:var(--color-primary-text);">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h6>Two-Factor Authentication (2FA)</h6>
                        <p>Require an extra verification step on this admin account</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('cho.settings.update') }}" class="d-flex align-items-center gap-3 flex-wrap">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="twofa">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="pref_2fa_enabled" value="1" id="cho2fa" {{ auth()->user()->pref_2fa_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="cho2fa">{{ auth()->user()->pref_2fa_enabled ? '2FA is enabled' : '2FA is disabled' }}</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle me-1"></i> Save 2FA Setting</button>
                    </form>
                </div>
            </div>
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-danger-text) 10%, transparent);color:var(--color-danger-text);">
                        <i class="bi bi-phone-fill"></i>
                    </div>
                    <div>
                        <h6>Active Sessions</h6>
                        <p>Sign out this account on all other logged-in devices</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('cho.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="sessions">
                        <div class="col-md-6">
                            <label class="form-label">Confirm Current Password</label>
                            <input type="password" class="form-control" name="current_password" placeholder="Enter current password" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Sign out all other devices?')"><i class="bi bi-box-arrow-right me-1"></i> Revoke Other Sessions</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showSection(id, el, ev) {
    if (ev) { ev.preventDefault(); }
    document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.settings-nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById('section-' + id).classList.add('active');
    el.classList.add('active');
    document.querySelectorAll('#section-' + id + ' .fade-in-card').forEach(c => {
        c.classList.remove('visible');
        setTimeout(() => c.classList.add('visible'), 30);
    });
}
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#section-appearance .fade-in-card').forEach((c, i) => {
        setTimeout(() => c.classList.add('visible'), i * 80);
    });
        @include('includes.theme-toggle')
    document.querySelectorAll('.settings-nav-item').forEach(link => {
        link.addEventListener('click', function (ev) {
            if (link.hasAttribute('onclick')) {
                ev.preventDefault();
            }
        });
    });
});
</script>
@endpush

@endsection
