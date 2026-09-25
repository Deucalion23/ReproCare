@extends('rhu.layout')

@section('title', 'Settings - RHU Portal | ReproCare')

@push('styles')
<style>
    .settings-wrap         { display:flex; gap:1.5rem; align-items:flex-start; }
    .settings-sidebar      { width:240px; flex-shrink:0; position:sticky; top:80px; }
    .settings-content      { flex:1; min-width:0; }

    .settings-nav          { background:var(--bg-card); border:1px solid var(--border); border-radius:18px; overflow:hidden; padding:0.5rem; }
    .settings-nav-item     { display:flex; align-items:center; gap:0.75rem; padding:0.7rem 0.9rem; border-radius:12px; font-size:0.875rem; font-weight:500; color:var(--text-muted); cursor:pointer; text-decoration:none; transition:all 0.2s ease; border:1px solid transparent; margin-bottom:0.15rem; }
    .settings-nav-item i   { font-size:1rem; width:1.15rem; text-align:center; flex-shrink:0; }
    .settings-nav-item:hover { background:var(--primary-subtle); color:var(--text); }
    .settings-nav-item.active { background:linear-gradient(135deg, var(--primary-subtle), color-mix(in srgb, var(--color-primary) 10%, transparent)); color:var(--primary-light); border-color:var(--border-glass); font-weight:600; }

    .settings-section      { display:none; }
    .settings-section.active { display:block; }

    .pref-card             { background:var(--bg-card); border:1px solid var(--border); border-radius:18px; margin-bottom:1.25rem; overflow:hidden; transition:background 0.4s ease, border-color 0.3s ease; }
    .pref-card-header      { padding:1.1rem 1.4rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:0.75rem; }
    .pref-card-header-icon { width:38px; height:38px; border-radius:11px; background:var(--primary-subtle); border:1px solid var(--border-glass); display:flex; align-items:center; justify-content:center; font-size:1rem; color:var(--primary-light); flex-shrink:0; }
    .pref-card-header h6   { margin:0; font-family:'Plus Jakarta Sans', sans-serif; font-weight:700; font-size:0.95rem; color:var(--text); }
    .pref-card-header p    { margin:0; font-size:0.78rem; color:var(--text-muted); }
    .pref-card-body        { padding:1.25rem 1.4rem; }

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

@section('rhu-content')

<div class="settings-page-header fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>Settings — Facility &amp; Operational Level</h1>
            <p>Facility profile, staff security, president lock, escalation alerts, and report templates for {{ \App\Models\Setting::get('rhu.station_name', 'your RHU station') }}</p>
        </div>
    </div>
</div>

<div class="settings-wrap">
    <div class="settings-sidebar fade-in-card">
        <div class="settings-nav">
            <a class="settings-nav-item active" onclick="showSection('myprofile', this)" href="#">
                <i class="bi bi-person-circle" style="color:var(--primary-light);"></i>
                My Profile
            </a>
            <a class="settings-nav-item" onclick="showSection('facility', this)" href="#">
                <i class="bi bi-hospital-fill" style="color:var(--primary-light);"></i>
                Facility Profile
            </a>
            <a class="settings-nav-item" onclick="showSection('staff', this)" href="#">
                <i class="bi bi-people-fill" style="color:var(--info);"></i>
                Staff &amp; Security
            </a>
            <a class="settings-nav-item" onclick="showSection('presidents', this)" href="#">
                <i class="bi bi-person-badge-fill" style="color:var(--success);"></i>
                President Lock
            </a>
            <a class="settings-nav-item" onclick="showSection('alerts', this)" href="#">
                <i class="bi bi-bell-fill" style="color:var(--warning);"></i>
                Escalation Alerts
            </a>
            <a class="settings-nav-item" onclick="showSection('templates', this)" href="#">
                <i class="bi bi-file-earmark-text-fill" style="color:var(--accent-violet);"></i>
                Report Templates
            </a>
            <a class="settings-nav-item" onclick="showSection('appearance', this)" href="#">
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
        </div>
    </div>

    <div class="settings-content">
        {{-- Global save feedback — every section below redirects back here --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{-- MY PROFILE (RHU Admin) --}}
        <div class="settings-section active" id="section-myprofile">
            @php $me = auth()->user(); @endphp
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-person-badge-fill"></i></div>
                    <div>
                        <h6>Personal &amp; Facility Identification</h6>
                        <p>Full name, administrative title, staff ID, and assigned facility</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3">
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
                            <label class="form-label">Administrative Title</label>
                            <input type="text" class="form-control" name="official_title" value="{{ old('official_title', $me->official_title) }}" placeholder="e.g. RHU Administrator">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Staff ID</label>
                            <input type="text" class="form-control" name="employee_id" value="{{ old('employee_id', $me->employee_id) }}" placeholder="e.g. RHU1-2024-010">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned Facility (read-only)</label>
                            <input type="text" class="form-control" value="{{ $me->rhu_assignment ?? 'Rural Health Unit 1' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Medical License No. (if applicable)</label>
                            <input type="text" class="form-control" name="license_number" value="{{ old('license_number', $me->license_number) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">License Expiry (if applicable)</label>
                            <input type="date" class="form-control" name="license_expiry" value="{{ old('license_expiry', optional($me->license_expiry)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save My Profile</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-secondary) 12%, transparent);color:var(--color-secondary-text);"><i class="bi bi-person-square"></i></div>
                    <div>
                        <h6>Profile Photo</h6>
                        <p>Shown in the sidebar and verification queues · JPG/PNG, max 2MB</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('rhu.settings.update') }}" enctype="multipart/form-data" class="row g-3 align-items-center">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="myprofile">
                        <input type="hidden" name="first_name" value="{{ $me->first_name }}">
                        <input type="hidden" name="last_name" value="{{ $me->last_name }}">
                        <div class="col-auto">
                            <img id="rhuPhotoPreview" src="{{ $me->profile_image_url }}"
                                 alt="Profile photo"
                                 style="width:84px;height:84px;border-radius:20px;object-fit:cover;border:2px solid var(--border);"
                                 onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                        </div>
                        <div class="col">
                            <input type="file" class="form-control" name="profile_image" accept="image/jpeg,image/png,image/jpg" onchange="if(this.files[0]){document.getElementById('rhuPhotoPreview').src=URL.createObjectURL(this.files[0]);}">
                            <div class="form-text">Upload a new photo, then press Save Photo.</div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Photo</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-info) 12%, transparent);color:var(--color-info-text);"><i class="bi bi-telephone-fill"></i></div>
                    <div>
                        <h6>Operational Contact Details</h6>
                        <p>Work email, coordination mobile, and emergency station contact</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="myprofile">
                        <input type="hidden" name="first_name" value="{{ $me->first_name }}">
                        <input type="hidden" name="last_name" value="{{ $me->last_name }}">
                        <div class="col-md-6">
                            <label class="form-label">Primary Work Email (login)</label>
                            <input type="email" class="form-control" value="{{ $me->email }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Secondary Email</label>
                            <input type="email" class="form-control" name="secondary_email" value="{{ old('secondary_email', $me->secondary_email) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Official Mobile (coordination)</label>
                            <input type="text" class="form-control" name="contact_number" value="{{ old('contact_number', $me->contact_number) }}" placeholder="09xxxxxxxxx">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Secondary Contact</label>
                            <input type="text" class="form-control" name="secondary_contact" value="{{ old('secondary_contact', $me->secondary_contact) }}" placeholder="09xxxxxxxxx">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Emergency Station Contact</label>
                            <input type="text" class="form-control" name="station_contact" value="{{ old('station_contact', $me->station_contact) }}" placeholder="e.g. (075) 555-0100">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Contact Details</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-success) 12%, transparent);color:var(--color-success-text);"><i class="bi bi-bell-fill"></i></div>
                    <div>
                        <h6>Operational Notification Preferences</h6>
                        <p>Station management alert channels for this account</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="rhu_notifications">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="pref_bhw_conflicts" value="1" id="rhuNotifConflict" {{ $me->pref_bhw_conflicts ? 'checked' : '' }}>
                                <label class="form-check-label" for="rhuNotifConflict">BHW President assignment conflicts</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="pref_pending_reports" value="1" id="rhuNotifPending" {{ $me->pref_pending_reports ? 'checked' : '' }}>
                                <label class="form-check-label" for="rhuNotifPending">Pending monthly report submissions</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="pref_highrisk_escalation" value="1" id="rhuNotifHigh" {{ $me->pref_highrisk_escalation ? 'checked' : '' }}>
                                <label class="form-check-label" for="rhuNotifHigh">Unresolved high-risk patient escalations</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="pref_escalation_alerts" value="1" id="rhuNotifEsc" {{ $me->pref_escalation_alerts ? 'checked' : '' }}>
                                <label class="form-check-label" for="rhuNotifEsc">General escalation alerts</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Preferences</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-warning) 12%, transparent);color:var(--color-warning-text);"><i class="bi bi-arrow-left-right"></i></div>
                    <div>
                        <h6>Delegation / Out-of-Office Status</h6>
                        <p>Route urgent requests to a backup staff member during leave</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    @if($me->out_of_office && $me->delegateTo)
                        <div class="alert alert-warning d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>Out-of-office is <strong>ON</strong> — urgent requests route to <strong>{{ $me->delegateTo->name }}</strong>{{ $me->ooo_note ? ' (' . e($me->ooo_note) . ')' : '' }}.</div>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="delegation">
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="out_of_office" value="1" id="rhuOOO" {{ $me->out_of_office ? 'checked' : '' }}>
                                <label class="form-check-label" for="rhuOOO">Out-of-Office / Acting Admin mode</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Backup Staff (Acting Admin)</label>
                            <select class="form-select" name="delegate_to_user_id">
                                <option value="">— No backup assigned —</option>
                                @foreach(($delegationCandidates ?? collect()) as $candidate)
                                    <option value="{{ $candidate->id }}" {{ (int) old('delegate_to_user_id', $me->delegate_to_user_id) === (int) $candidate->id ? 'selected' : '' }}>
                                        {{ $candidate->name }} ({{ ucfirst($candidate->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Handover Note (optional)</label>
                            <input type="text" class="form-control" name="ooo_note" value="{{ old('ooo_note', $me->ooo_note) }}" placeholder="e.g. On leave until Friday; contact backup for approvals" maxlength="255">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Delegation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- FACILITY PROFILE --}}
        <div class="settings-section" id="section-facility">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-hospital-fill"></i></div>
                    <div>
                        <h6>RHU Facility Profile</h6>
                        <p>Station identity, hours, and catchment barangays</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    @php
                        $catchment = json_decode(\App\Models\Setting::get('rhu.catchment_barangays', '[]'), true) ?: [];
                    @endphp
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="facility">
                        <div class="col-md-6">
                            <label class="form-label">Station name</label>
                            <input type="text" name="station_name" class="form-control" value="{{ old('station_name', \App\Models\Setting::get('rhu.station_name', 'RHU I')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact number</label>
                            <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', \App\Models\Setting::get('rhu.contact_number')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Operating hours</label>
                            <input type="text" name="operating_hours" class="form-control" value="{{ old('operating_hours', \App\Models\Setting::get('rhu.operating_hours')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catchment barangays</label>
                            <div class="row g-2">
                                @foreach($barangays as $brgy)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" name="catchment_barangays[]" value="{{ $brgy->name }}" class="form-check-input" id="cb-{{ $brgy->id }}" {{ in_array($brgy->name, old('catchment_barangays', $catchment)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="cb-{{ $brgy->id }}">{{ $brgy->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Facility Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- STAFF & SECURITY --}}
        <div class="settings-section" id="section-staff">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <h6>User Management &amp; Security Rules</h6>
                        <p>Reset staff passwords and activate / deactivate midwives and BHWs</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3 mb-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="staff_reset">
                        <div class="col-md-6">
                            <label class="form-label">Staff member</label>
                            <select name="user_id" class="form-select" required>
                                <option value="" disabled selected>Select staff</option>
                                @foreach($staff as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }} — {{ ucfirst(str_replace('_', ' ', $member->role)) }} ({{ $member->status }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Temporary password (min. 8 chars)</label>
                            <input type="text" name="temp_password" class="form-control" required minlength="8" autocomplete="off">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Reset this staff password? Share the temporary password securely.');">
                                <i class="bi bi-key me-1"></i> Set Temporary Password
                            </button>
                        </div>
                    </form>
                    <hr>
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="staff_status">
                        <div class="col-md-6">
                            <label class="form-label">Staff member</label>
                            <select name="user_id" class="form-select" required>
                                <option value="" disabled selected>Select staff</option>
                                @foreach($staff as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }} — {{ ucfirst(str_replace('_', ' ', $member->role)) }} ({{ $member->status }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New status</label>
                            <select name="status" class="form-select" required>
                                <option value="approved">Active (approved)</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Apply Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- PRESIDENT LOCK --}}
        <div class="settings-section" id="section-presidents">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-person-badge-fill"></i></div>
                    <div>
                        <h6>BHW President Verification &amp; Lock</h6>
                        <p>Bind validated presidents to barangays — strict 1-President-per-Barangay lock</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <div class="table-responsive mb-4">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>President</th><th>Barangay</th><th>Status</th><th class="text-end">Unbind</th></tr></thead>
                            <tbody>
                                @forelse($presidents as $president)
                                    <tr>
                                        <td class="fw-semibold">{{ $president->name }}</td>
                                        <td>{{ $president->barangay ?? '—' }}</td>
                                        <td>{{ ucfirst($president->status) }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('rhu.settings.update') }}" onsubmit="return confirm('Unbind {{ $president->name }}? The barangay slot reopens.');">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="section" value="president_unbind">
                                                <input type="hidden" name="president_id" value="{{ $president->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-warning">Unbind</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted text-center">No active presidents.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="president_bind">
                        <div class="col-md-6">
                            <label class="form-label">Promote approved BHW</label>
                            <select name="bhw_id" class="form-select" required>
                                <option value="" disabled selected>Select BHW</option>
                                @foreach($promotableBhws as $bhw)
                                    <option value="{{ $bhw->id }}">{{ $bhw->name }}{{ $bhw->barangay ? ' — ' . $bhw->barangay : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bind to barangay</label>
                            <select name="barangay" class="form-select" required>
                                <option value="" disabled selected>Select Barangay</option>
                                @foreach($barangays as $brgy)
                                    <option value="{{ $brgy->name }}">Barangay {{ $brgy->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Bind President</button>
                        </div>
                    </form>
                    <p class="text-muted small mt-2 mb-0">Occupied barangays are blocked automatically by the assignment lock.</p>
                </div>
            </div>
        </div>

        {{-- ESCALATION ALERTS --}}
        <div class="settings-section" id="section-alerts">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-warning) 12%, transparent);color:var(--warning);"><i class="bi bi-bell-fill"></i></div>
                    <div>
                        <h6>Station Escalation Alerts</h6>
                        <p>Choose which RHU staff receive instant alerts on high-risk flags in this jurisdiction</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    @php
                        $escalationIds = json_decode(\App\Models\Setting::get('rhu.escalation_recipients', '[]'), true) ?: [];
                    @endphp
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="alerts">
                        <div class="col-12">
                            @foreach($staff->whereIn('role', ['rhu', 'midwife']) as $member)
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="recipients[]" value="{{ $member->id }}" class="form-check-input" id="esc-{{ $member->id }}" {{ in_array($member->id, $escalationIds) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="esc-{{ $member->id }}">{{ $member->name }} — {{ ucfirst($member->role) }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Recipients</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- REPORT TEMPLATES --}}
        <div class="settings-section" id="section-templates">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                    <div>
                        <h6>Report Schedule Templates</h6>
                        <p>Monthly / quarterly templates required by the city level, plus submission deadline</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="templates">
                        <div class="col-md-4">
                            <label class="form-label">BHW submission deadline (day of month)</label>
                            <input type="number" name="deadline_day" class="form-control" min="1" max="28" value="{{ old('deadline_day', \App\Models\Setting::get('reports.deadline_day', 25)) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Monthly template (placeholders: {station} {month} {year} {preparer})</label>
                            <textarea name="template_monthly" rows="4" class="form-control">{{ old('template_monthly', \App\Models\Setting::get('reports.template_monthly')) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Quarterly template (placeholders: {station} {quarter} {year} {preparer})</label>
                            <textarea name="template_quarterly" rows="4" class="form-control">{{ old('template_quarterly', \App\Models\Setting::get('reports.template_quarterly')) }}</textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Templates</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- APPEARANCE --}}
        <div class="settings-section" id="section-appearance">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-palette-fill"></i></div>
                    <div>
                        <h6>Color Theme</h6>
                        <p>Choose your preferred interface appearance</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    <div class="theme-option-grid">
                        <div class="theme-option" data-theme="light" id="theme-opt-light" onclick="setRcTheme('light')" role="button" tabindex="0" aria-label="Switch to light mode">
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
                        <div class="theme-option" data-theme="dark" id="theme-opt-dark" onclick="setRcTheme('dark')" role="button" tabindex="0" aria-label="Switch to dark mode">
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
                        <span><i class="bi bi-sun-fill" style="color:var(--warning);"></i> Light</span>
                        <label class="rc-switch mb-0 mx-auto">
                            <input type="checkbox" class="theme-switch-input" id="rhuThemeSwitch" onchange="setRcTheme(this.checked ? 'dark' : 'light')">
                            <span class="rc-track"><span class="rc-thumb"></span></span>
                        </label>
                        <span><i class="bi bi-moon-stars-fill" style="color:var(--accent-violet);"></i> Dark</span>
                    </div>
                </div>
            </div>
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
                    <div class="d-flex align-items-center gap-3 flex-wrap mb-3">
                        <img src="{{ auth()->user()->profile_image_url }}"
                             alt="{{ auth()->user()->name }}"
                             style="width:64px;height:64px;border-radius:18px;object-fit:cover;border:2px solid var(--border);"
                             onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                        <div>
                            <div class="fw-bold" style="font-size:1.05rem;">{{ auth()->user()->name }}</div>
                            <div class="text-muted small">{{ auth()->user()->email }} · RHU Admin · {{ auth()->user()->rhu_assignment ?? 'Rural Health Unit 1' }}</div>
                        </div>
                    </div>
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
                            <input type="text" class="form-control" value="RHU Admin" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned Station</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->rhu_assignment ?? 'Rural Health Unit 1' }}" readonly>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" onclick="showSection('myprofile', document.querySelector('.settings-nav-item'));document.getElementById('section-myprofile').scrollIntoView({behavior:'smooth'});">
                                <i class="bi bi-pencil-square me-1"></i> Edit Profile &amp; Photo in My Profile
                            </button>
                        </div>
                    </div>
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
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="password">
                        <div class="col-12">
                            <label class="form-label">Current Password</label>
                            <!-- Save feedback renders in the global banner above the sections -->
                            <input type="password" class="form-control" name="current_password" placeholder="Enter current password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" placeholder="Min. 8 characters">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Repeat new password">
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
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="d-flex align-items-center gap-3 flex-wrap">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="twofa">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="pref_2fa_enabled" value="1" id="rhu2fa" {{ auth()->user()->pref_2fa_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="rhu2fa">{{ auth()->user()->pref_2fa_enabled ? '2FA is enabled' : '2FA is disabled' }}</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle me-1"></i> Save 2FA Setting</button>
                    </form>
                </div>
            </div>
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-info) 12%, transparent);color:var(--color-info-text);">
                        <i class="bi bi-question-circle-fill"></i>
                    </div>
                    <div>
                        <h6>Password Recovery Security Questions</h6>
                        <p>Used to verify your identity for password recovery</p>
                    </div>
                </div>
                <div class="pref-card-body">
                    @php $me2 = auth()->user(); @endphp
                    @if($me2->recovery_question_1)
                        <div class="alert alert-success d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>Recovery questions are set{{ $me2->recovery_question_2 ? ' (2 configured)' : ' (1 configured)' }}. Saving new answers replaces them.</div>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="recovery">
                        <div class="col-md-6">
                            <label class="form-label">Security Question 1</label>
                            <input type="text" class="form-control" name="recovery_question_1" value="{{ old('recovery_question_1', $me2->recovery_question_1) }}" placeholder="e.g. What is your mother's maiden name?" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Answer 1</label>
                            <input type="password" class="form-control" name="recovery_answer_1" placeholder="Min. 3 characters" required autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Security Question 2 (optional)</label>
                            <input type="text" class="form-control" name="recovery_question_2" value="{{ old('recovery_question_2', $me2->recovery_question_2) }}" placeholder="e.g. What city were you born in?">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Answer 2 (optional)</label>
                            <input type="password" class="form-control" name="recovery_answer_2" placeholder="Min. 3 characters" autocomplete="new-password">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Recovery Questions</button>
                        </div>
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
                    <form method="POST" action="{{ route('rhu.settings.update') }}" class="row g-3">
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
function currentRcTheme() {
    return localStorage.getItem('rc_theme') === 'dark' ? 'dark' : 'light';
}
function paintRcThemeControls(mode) {
    const optLight = document.getElementById('theme-opt-light');
    const optDark  = document.getElementById('theme-opt-dark');
    const sw = document.getElementById('rhuThemeSwitch');
    if (optLight) optLight.classList.toggle('active', mode === 'light');
    if (optDark)  optDark.classList.toggle('active',  mode === 'dark');
    if (sw) sw.checked = (mode === 'dark');
}
// Appearance controls — writes the same rc_theme key the global layout
// reads pre-paint, so the choice persists across every portal page.
function setRcTheme(mode) {
    mode = (mode === 'dark') ? 'dark' : 'light';
    localStorage.setItem('rc_theme', mode);
    document.documentElement.setAttribute('data-theme', mode);
    document.documentElement.classList.toggle('dark', mode === 'dark');
    paintRcThemeControls(mode);
}
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#section-appearance .fade-in-card').forEach((c, i) => {
        setTimeout(() => c.classList.add('visible'), i * 80);
    });
    paintRcThemeControls(currentRcTheme());
    // Keyboard access for the theme cards.
    ['theme-opt-light', 'theme-opt-dark'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                setRcTheme(el.getAttribute('data-theme'));
            }
        });
    });

    document.querySelectorAll('.settings-nav-item').forEach(link => {
        link.addEventListener('click', function (ev) {
            ev.preventDefault();
        });
    });
});
</script>
@endpush

@endsection
