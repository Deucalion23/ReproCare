@extends('user.layout')

@section('title', 'Settings & Profile - ReproCare')

@push('styles')
<style>
    .settings-wrap         { display:flex; gap:1.5rem; align-items:flex-start; }
    .settings-sidebar      { width:250px; flex-shrink:0; position:sticky; top:80px; }
    .settings-content      { flex:1; min-width:0; }
    .settings-nav          { background:var(--bg-card); border:1px solid var(--border); border-radius:18px; overflow:hidden; padding:0.5rem; }
    .settings-nav-item     { display:flex; align-items:center; gap:0.75rem; padding:0.7rem 0.9rem; border-radius:12px; font-size:0.875rem; font-weight:500; color:var(--text-muted); cursor:pointer; text-decoration:none; transition:all 0.2s ease; border:1px solid transparent; margin-bottom:0.15rem; }
    .settings-nav-item i   { font-size:1rem; width:1.15rem; text-align:center; flex-shrink:0; }
    .settings-nav-item:hover { background:var(--primary-subtle); color:var(--text); }
    .settings-nav-item.active { background:linear-gradient(135deg, var(--primary-subtle), color-mix(in srgb, var(--color-primary) 10%, transparent)); color:var(--primary-light); border-color:var(--border-glass); font-weight:600; }
    .settings-section      { display:none; }
    .settings-section.active { display:block; }
    .pref-card             { background:var(--bg-card); border:1px solid var(--border); border-radius:18px; margin-bottom:1.25rem; overflow:hidden; transition:background 0.4s ease; }
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
    .theme-option { border:2px solid var(--border); border-radius:14px; padding:1rem; cursor:pointer; text-align:center; transition:all 0.25s ease; position:relative; background:var(--bg-card2); }
    .theme-option:hover { border-color:var(--primary); transform:translateY(-2px); }
    .theme-option.active { border-color:var(--primary); background:var(--primary-subtle); }
    .theme-preview { border-radius:10px; overflow:hidden; height:70px; display:flex; margin-bottom:0.75rem; border:1px solid var(--border); }
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

    /* View Mode Info Grid */
    .info-grid-2 { display:grid; grid-template-columns:repeat(auto-fill, minmax(230px, 1fr)); gap:0.85rem; }
    .info-card-box {
        background:var(--bg-card2);
        border:1px solid var(--border);
        border-radius:14px;
        padding:0.85rem 1rem;
        transition:all 0.2s ease;
    }
    .info-card-box:hover { border-color:var(--primary); transform:translateY(-1px); }
    .info-card-label {
        font-size:0.72rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:0.5px;
        color:var(--text-muted);
        margin-bottom:0.25rem;
        display:flex;
        align-items:center;
        gap:0.4rem;
    }
    .info-card-label i { color:var(--primary-light); font-size:0.85rem; }
    .info-card-value {
        font-size:0.94rem;
        font-weight:600;
        color:var(--text);
        word-break:break-word;
    }

    /* Barangay combobox dropdown in settings */
    .brgy-combobox-wrap { position:relative; }
    .brgy-dropdown-list {
        position:absolute; top:calc(100% + 4px); left:0; right:0;
        background:var(--bg-card); border:1px solid var(--border);
        border-radius:12px; box-shadow:0 10px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);
        z-index:9999; max-height:240px; overflow:hidden; display:flex; flex-direction:column;
    }
    .brgy-list-header {
        padding:0.5rem 0.9rem; font-size:0.74rem; font-weight:700;
        color:var(--text-muted); background:var(--bg-card2);
        border-bottom:1px solid var(--border); display:flex; justify-content:space-between;
    }
    .brgy-scroll-container { overflow-y:auto; max-height:195px; }
    .brgy-option-item {
        padding:0.55rem 0.9rem; font-size:0.85rem; cursor:pointer;
        display:flex; align-items:center; gap:0.5rem; color:var(--text);
        border-bottom:1px solid var(--border); transition:background 0.15s;
    }
    .brgy-option-item:last-child { border-bottom:none; }
    .brgy-option-item:hover, .brgy-option-item.selected {
        background:var(--primary-subtle); color:var(--primary-light);
    }
    .brgy-option-item.hidden { display:none; }
    .brgy-empty-state { padding:1rem; text-align:center; color:var(--text-muted); font-size:0.82rem; }

    @media (max-width: 768px) {
        .settings-wrap { flex-direction:column; }
        .settings-sidebar { width:100%; position:static; }
        .theme-option-grid { grid-template-columns:1fr 1fr; }
        .info-grid-2 { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('user-content')

<div class="settings-page-header fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>Settings &amp; Profile</h1>
            <p>Manage your health information, personal profile, address, and preferences</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="border-radius:14px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" style="border-radius:14px;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Please check the form for errors.
        <ul class="mb-0 mt-1 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="settings-wrap">

    {{-- LEFT NAV --}}
    <div class="settings-sidebar fade-in-card">
        <div class="settings-nav">
            <a class="settings-nav-item active" onclick="showSection('profile', this)" href="#">
                <i class="bi bi-person-circle" style="color:var(--primary-light);"></i> Personal Profile
            </a>
            <a class="settings-nav-item" onclick="showSection('address', this)" href="#">
                <i class="bi bi-geo-alt-fill" style="color:var(--secondary);"></i> Address &amp; Location
            </a>
            <a class="settings-nav-item" onclick="showSection('health', this)" href="#">
                <i class="bi bi-heart-pulse-fill" style="color:var(--accent-pink);"></i> Health Profile
            </a>
            <a class="settings-nav-item" onclick="showSection('emergency', this)" href="#">
                <i class="bi bi-people-fill" style="color:var(--info);"></i> Emergency Contacts
            </a>
            <a class="settings-nav-item" onclick="showSection('appearance', this)" href="#">
                <i class="bi bi-palette-fill" style="color:var(--primary-light);"></i> Appearance
            </a>
            <a class="settings-nav-item" onclick="showSection('security', this)" href="#">
                <i class="bi bi-shield-lock-fill" style="color:var(--warning);"></i> Security
            </a>
            <a class="settings-nav-item" onclick="showSection('notifications', this)" href="#">
                <i class="bi bi-bell-fill" style="color:var(--success);"></i> Notifications
            </a>
            <a class="settings-nav-item" onclick="showSection('privacy', this)" href="#">
                <i class="bi bi-lock-fill" style="color:var(--danger);"></i> Privacy
            </a>
        </div>
    </div>

    <div class="settings-content">

        {{-- 1. PERSONAL PROFILE --}}
        <div class="settings-section active" id="section-profile">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-info) 12%, transparent);color:var(--info);"><i class="bi bi-person-circle"></i></div>
                        <div><h6>Personal Profile</h6><p>Your personal identity, contact, and partner details</p></div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="editProfileBtn" onclick="toggleProfileEdit(true)">
                            <i class="bi bi-pencil-square me-1"></i> Edit Profile
                        </button>
                    </div>
                </div>
                <div class="pref-card-body">

                    {{-- VIEW MODE (Default) --}}
                    <div id="profileViewMode">
                        <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-4" style="background:var(--bg-card2);border:1px solid var(--border);">
                            <img src="{{ auth()->user()->profile_image_url }}" alt="{{ auth()->user()->name }}"
                                 style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid var(--primary);flex-shrink:0;"
                                 onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                            <div style="flex:1;">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h5 class="mb-0" style="font-weight:800;color:var(--text);">{{ auth()->user()->name }}</h5>
                                    <span class="badge bg-primary-subtle text-primary border px-2.5 py-1" style="font-size:0.75rem;">
                                        <i class="bi bi-person-heart me-1"></i> Patient / Woman
                                    </span>
                                </div>
                                <p class="text-muted small mb-0 mt-1">
                                    <i class="bi bi-envelope me-1"></i>{{ auth()->user()->email }}
                                </p>
                            </div>
                        </div>

                        <div class="info-grid-2">
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-person-fill"></i> First Name</div>
                                <div class="info-card-value">{{ auth()->user()->first_name ?? '—' }}</div>
                            </div>
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-type"></i> Middle Initial</div>
                                <div class="info-card-value">{{ auth()->user()->middle_initial ?? '—' }}</div>
                            </div>
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-person-fill"></i> Last Name</div>
                                <div class="info-card-value">{{ auth()->user()->last_name ?? '—' }}</div>
                            </div>
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-envelope-fill"></i> Email Address</div>
                                <div class="info-card-value">{{ auth()->user()->email ?? '—' }}</div>
                            </div>
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-telephone-fill"></i> Phone Number</div>
                                <div class="info-card-value">{{ auth()->user()->contact_number ?? auth()->user()->phone ?? '—' }}</div>
                            </div>
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-calendar-event"></i> Date of Birth</div>
                                <div class="info-card-value">
                                    {{ auth()->user()->date_of_birth ? \Carbon\Carbon::parse(auth()->user()->date_of_birth)->format('F d, Y') : '—' }}
                                    @if(auth()->user()->age)
                                        <span class="text-muted small">({{ auth()->user()->age }} yrs old)</span>
                                    @endif
                                </div>
                            </div>
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-gender-ambiguous"></i> Gender</div>
                                <div class="info-card-value">{{ ucfirst(auth()->user()->gender ?? 'Female') }}</div>
                            </div>
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-people-fill"></i> Partner / Spouse</div>
                                <div class="info-card-value">{{ auth()->user()->partner_name ?? 'Not specified' }}</div>
                            </div>
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-telephone-plus"></i> Partner Contact</div>
                                <div class="info-card-value">{{ auth()->user()->partner_contact ?? 'Not specified' }}</div>
                            </div>
                        </div>

                        <div class="mt-4 pt-2 border-top">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleProfileEdit(true)">
                                <i class="bi bi-pencil me-1"></i> Edit Profile Information
                            </button>
                        </div>
                    </div>

                    {{-- EDIT MODE (Hidden by default) --}}
                    <div id="profileEditMode" style="display:none;">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="redirect_to" value="{{ route('user.settings') }}#profile">
                            <input type="hidden" name="_section" value="profile">

                            {{-- Avatar Row in Edit Mode --}}
                            <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-4" style="background:var(--bg-card2);border:1px solid var(--border);">
                                <img src="{{ auth()->user()->profile_image_url }}" alt="{{ auth()->user()->name }}"
                                     style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid var(--primary);flex-shrink:0;"
                                     onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                                <div style="flex:1;">
                                    <h6 class="mb-1" style="font-weight:700;color:var(--text);font-size:0.92rem;">Change Profile Photo</h6>
                                    <p class="text-muted small mb-2">Upload a picture to personalize your profile (PNG, JPG max 5MB).</p>
                                    <input type="file" name="profile_image" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg" style="max-width:320px;">
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', auth()->user()->first_name) }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">MI</label>
                                    <input type="text" name="middle_initial" class="form-control" value="{{ old('middle_initial', auth()->user()->middle_initial) }}" maxlength="2">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', auth()->user()->last_name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="contact_number" class="form-control" value="{{ old('contact_number', auth()->user()->phone ?: auth()->user()->contact_number) }}" placeholder="09XXXXXXXXX">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', auth()->user()->date_of_birth ? \Carbon\Carbon::parse(auth()->user()->date_of_birth)->format('Y-m-d') : '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="female" {{ old('gender', auth()->user()->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="male" {{ old('gender', auth()->user()->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Partner / Spouse Full Name <span class="text-muted fw-normal">(Optional)</span></label>
                                    <input type="text" name="partner_name" class="form-control" value="{{ old('partner_name', auth()->user()->partner_name) }}" placeholder="Partner's full name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Partner Contact Number <span class="text-muted fw-normal">(Optional)</span></label>
                                    <input type="tel" name="partner_contact" class="form-control" value="{{ old('partner_contact', auth()->user()->partner_contact) }}" placeholder="09XXXXXXXXX">
                                </div>
                                <div class="col-12 d-flex gap-2 pt-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Changes</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggleProfileEdit(false)"><i class="bi bi-x-circle me-1"></i> Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        {{-- 2. ADDRESS & LOCATION --}}
        <div class="settings-section" id="section-address">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-secondary) 12%, transparent);color:var(--secondary);"><i class="bi bi-geo-alt-fill"></i></div>
                        <div><h6>Address &amp; Location</h6><p>Your residential location in San Carlos City</p></div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="editAddressBtn" onclick="toggleAddressEdit(true)">
                            <i class="bi bi-pencil-square me-1"></i> Edit Address
                        </button>
                    </div>
                </div>
                <div class="pref-card-body">

                    {{-- VIEW MODE (Default) --}}
                    <div id="addressViewMode">
                        <div class="p-3 rounded-4 mb-4" style="background:var(--bg-card2);border:1px solid var(--border);">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <span style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);letter-spacing:0.5px;">Current Saved Address</span>
                                    <div style="font-weight:700;color:var(--text);font-size:1.05rem;margin-top:4px;">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        {{ auth()->user()->address ?: (auth()->user()->barangay ? auth()->user()->barangay . ', San Carlos City, Pangasinan' : 'No address specified yet') }}
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success border px-3 py-2">
                                    <i class="bi bi-shield-check me-1"></i> San Carlos City, Pangasinan
                                </span>
                            </div>
                        </div>

                        <div class="info-grid-2">
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-pin-map-fill"></i> Barangay</div>
                                <div class="info-card-value">{{ auth()->user()->barangay ?? 'Not assigned' }}</div>
                            </div>
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-geo-alt"></i> Purok</div>
                                <div class="info-card-value">{{ auth()->user()->purok?->name ?? 'Not assigned' }}</div>
                            </div>
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-building"></i> City / Municipality</div>
                                <div class="info-card-value">San Carlos City</div>
                            </div>
                            <div class="info-card-box">
                                <div class="info-card-label"><i class="bi bi-map"></i> Province</div>
                                <div class="info-card-value">Pangasinan</div>
                            </div>
                        </div>

                        <div class="mt-4 pt-2 border-top">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleAddressEdit(true)">
                                <i class="bi bi-pencil me-1"></i> Edit Address Details
                            </button>
                        </div>
                    </div>

                    {{-- EDIT MODE (Hidden by default) --}}
                    <div id="addressEditMode" style="display:none;">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="redirect_to" value="{{ route('user.settings') }}#address">
                            <input type="hidden" name="_section" value="address">
                            <div class="row g-3">
                                {{-- Searchable & Scrollable Barangay dropdown --}}
                                <div class="col-12">
                                    <label class="form-label">Barangay in San Carlos City <span class="text-danger">*</span></label>
                                    <div class="brgy-combobox-wrap position-relative">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-geo-alt text-primary"></i></span>
                                            <input type="text" id="settingBrgyInput" name="barangay" class="form-control border-start-0"
                                                   value="{{ old('barangay', auth()->user()->barangay) }}"
                                                   placeholder="Type to search or scroll to pick from all {{ count($barangays ?? []) }} San Carlos City barangays..."
                                                   autocomplete="off" required>
                                            <button type="button" class="btn btn-outline-secondary" id="settingBrgyToggle" title="Click to view all barangays">
                                                <i class="bi bi-chevron-down" id="settingBrgyArrow"></i>
                                            </button>
                                        </div>
                                        <div class="brgy-dropdown-list" id="settingBrgyList" style="display:none;">
                                            <div class="brgy-list-header">
                                                <span><i class="bi bi-pin-map-fill me-1"></i> San Carlos City Barangays</span>
                                                <span class="badge bg-primary rounded-pill" id="settingBrgyMatchCount">{{ count($barangays ?? []) }}</span>
                                            </div>
                                            <div class="brgy-scroll-container" id="settingBrgyScroll">
                                                @foreach($barangays ?? [] as $b)
                                                    <div class="brgy-option-item {{ auth()->user()->barangay === $b ? 'selected' : '' }}" data-value="{{ $b }}">
                                                        <i class="bi bi-geo-alt-fill text-primary"></i>
                                                        <span>{{ $b }}</span>
                                                    </div>
                                                @endforeach
                                                <div class="brgy-empty-state" id="settingBrgyEmpty" style="display:none;">
                                                    <i class="bi bi-search me-1"></i> No matching barangay found
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Searchable dropdown with all {{ count($barangays ?? []) }} San Carlos City barangays.</small>
                                </div>

                                {{-- House / Unit No. --}}
                                <div class="col-md-6">
                                    <label class="form-label">House / Unit No. <span class="text-muted fw-normal">(Optional)</span></label>
                                    <input type="text" name="house_number" class="form-control"
                                           value="{{ old('house_number') }}"
                                           placeholder="e.g. 123, Unit 4B, Lot 7">
                                </div>

                                {{-- Purok (free text, adapting to sign up page) --}}
                                <div class="col-md-6">
                                    <label class="form-label">Purok <span class="text-muted fw-normal">(Optional)</span></label>
                                    <input type="text" name="purok" class="form-control"
                                           value="{{ old('purok') }}"
                                           placeholder="e.g. Purok 1, Purok Centro">
                                    <small class="text-muted">Enter your purok name or number if applicable.</small>
                                </div>

                                {{-- Sitio / Street --}}
                                <div class="col-md-6">
                                    <label class="form-label">Sitio / Street <span class="text-muted fw-normal">(Optional)</span></label>
                                    <input type="text" name="sitio" class="form-control"
                                           value="{{ old('sitio') }}"
                                           placeholder="e.g. Sitio Malaya, Rizal St.">
                                </div>

                                {{-- City & Province --}}
                                <div class="col-md-6">
                                    <label class="form-label">City &amp; Province</label>
                                    <input type="text" class="form-control" value="San Carlos City, Pangasinan" readonly style="background:var(--bg-card2);color:var(--text-muted);">
                                </div>

                                <div class="col-12 d-flex gap-2 pt-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Address Changes</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="toggleAddressEdit(false)"><i class="bi bi-x-circle me-1"></i> Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        {{-- 3. HEALTH PROFILE --}}
        <div class="settings-section" id="section-health">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-secondary) 12%, transparent);color:var(--accent-pink);"><i class="bi bi-heart-pulse-fill"></i></div>
                    <div><h6>Health Profile &amp; Clinical Info</h6><p>Update your health parameters and clinical notes</p></div>
                </div>
                <div class="pref-card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="redirect_to" value="{{ route('user.settings') }}#health">
                        <input type="hidden" name="_section" value="health">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Blood Type</label>
                                <select name="blood_type" class="form-select">
                                    <option value="">Select blood type</option>
                                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                                        <option value="{{ $bt }}" {{ (auth()->user()->blood_type ?? '') === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Height (cm)</label>
                                <input type="number" name="height" class="form-control" value="{{ auth()->user()->height ?? '' }}" placeholder="e.g. 160">
                                <small class="text-muted">Below 122 cm (4 ft) is flagged as short-stature risk in pregnancy assessments.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Weight (kg)</label>
                                <input type="number" name="weight" step="0.1" class="form-control" value="{{ auth()->user()->weight ?? '' }}" placeholder="e.g. 55.5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Average Cycle Length (days)</label>
                                <input type="number" name="cycle_length" class="form-control" value="{{ auth()->user()->cycle_length ?? 28 }}" min="21" max="45">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Allergies / Medical Notes</label>
                                <textarea name="medical_history" class="form-control" rows="3" placeholder="Note any allergies, previous surgeries, chronic illnesses, or dietary restrictions...">{{ auth()->user()->medical_history ?? auth()->user()->medical_notes ?? '' }}</textarea>
                            </div>
                            <div class="col-12 d-flex gap-2 pt-2">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Health Profile</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 4. EMERGENCY CONTACTS --}}
        <div class="settings-section" id="section-emergency">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-secondary) 12%, transparent);color:var(--info);"><i class="bi bi-people-fill"></i></div>
                    <div><h6>Emergency Contact Persons</h6><p>Manage your emergency contact details</p></div>
                </div>
                <div class="pref-card-body">
                    <form action="{{ route('profile.emergency-contacts.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        {{-- Primary Contact --}}
                        <div class="mb-4 pb-3" style="border-bottom:1px solid var(--border);">
                            <h6 class="mb-3" style="color:var(--primary-light); font-weight:700; font-size:0.9rem;">Primary Emergency Contact
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="emergency_name_1" class="form-control" value="{{ old('emergency_name_1', auth()->user()->primaryEmergencyContact?->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Relationship <span class="text-danger">*</span></label>
                                    <input type="text" name="emergency_relationship_1" class="form-control" value="{{ old('emergency_relationship_1', auth()->user()->primaryEmergencyContact?->relationship) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                    <input type="text" name="emergency_contact_number_1" class="form-control" value="{{ old('emergency_contact_number_1', auth()->user()->primaryEmergencyContact?->contact_number) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="emergency_address_1" class="form-control" value="{{ old('emergency_address_1', auth()->user()->primaryEmergencyContact?->address) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Secondary Contact --}}
                        <div class="mb-3">
                            <h6 class="mb-3" style="color:var(--text-muted); font-weight:700; font-size:0.9rem;">Secondary Emergency Contact (Optional)
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="emergency_name_2" class="form-control" value="{{ old('emergency_name_2', auth()->user()->secondaryEmergencyContact?->name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Relationship</label>
                                    <input type="text" name="emergency_relationship_2" class="form-control" value="{{ old('emergency_relationship_2', auth()->user()->secondaryEmergencyContact?->relationship) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" name="emergency_contact_number_2" class="form-control" value="{{ old('emergency_contact_number_2', auth()->user()->secondaryEmergencyContact?->contact_number) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="emergency_address_2" class="form-control" value="{{ old('emergency_address_2', auth()->user()->secondaryEmergencyContact?->address) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Tertiary Contact --}}
                        <div class="mb-3">
                            <h6 class="mb-3" style="color:var(--text-muted); font-weight:700; font-size:0.9rem;">Tertiary Emergency Contact (Optional)
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="emergency_name_3" class="form-control" value="{{ old('emergency_name_3', auth()->user()->tertiaryEmergencyContact?->name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Relationship</label>
                                    <input type="text" name="emergency_relationship_3" class="form-control" value="{{ old('emergency_relationship_3', auth()->user()->tertiaryEmergencyContact?->relationship) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Number</label>
                                    <input type="text" name="emergency_contact_number_3" class="form-control" value="{{ old('emergency_contact_number_3', auth()->user()->tertiaryEmergencyContact?->contact_number) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="emergency_address_3" class="form-control" value="{{ old('emergency_address_3', auth()->user()->tertiaryEmergencyContact?->address) }}">
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Emergency Contacts</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 5. APPEARANCE --}}
        <div class="settings-section" id="section-appearance">
            <div class="pref-card fade-in-card">
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
                        <span><i class="bi bi-sun-fill" style="color:var(--warning);"></i> Light</span>
                        <label class="rc-switch mb-0 mx-auto">
                            <input type="checkbox" class="theme-switch-input" onchange="setRcTheme(this.checked ? 'dark' : 'light')">
                            <span class="rc-track"><span class="rc-thumb"></span></span>
                        </label>
                        <span><i class="bi bi-moon-stars-fill" style="color:var(--accent-violet);"></i> Dark</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 6. SECURITY --}}
        <div class="settings-section" id="section-security">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-warning) 12%, transparent);color:var(--warning);"><i class="bi bi-shield-lock-fill"></i></div>
                    <div><h6>Change Password</h6><p>Ensure your account uses a strong password</p></div>
                </div>
                <div class="pref-card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="redirect_to" value="{{ route('user.settings') }}">
                        <input type="hidden" name="first_name" value="{{ auth()->user()->first_name }}">
                        <input type="hidden" name="last_name" value="{{ auth()->user()->last_name }}">
                        <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" placeholder="Enter current password" autocomplete="current-password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" placeholder="At least 8 characters" autocomplete="new-password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter new password">
                            </div>
                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-key-fill me-1"></i> Update Password</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-success) 12%, transparent);color:var(--success);"><i class="bi bi-shield-fill-check"></i></div>
                    <div><h6>Active Sessions</h6><p>Devices currently logged into your account</p></div>
                </div>
                <div class="pref-card-body">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.9rem;background:var(--bg-card2);border:1px solid var(--border);border-radius:12px;">
                        <div>
                            <div style="font-size:0.875rem;font-weight:600;color:var(--text);">Current Session</div>
                            <div style="font-size:0.78rem;color:var(--text-muted);">{{ request()->ip() }} &nbsp;·&nbsp; {{ now()->format('M j, Y') }}</div>
                        </div>
                        <span class="session-badge"><i class="bi bi-circle-fill" style="font-size:0.45rem;"></i> Active</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 7. NOTIFICATIONS --}}
        <div class="settings-section" id="section-notifications">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-success) 12%, transparent);color:var(--success);"><i class="bi bi-envelope-fill"></i></div>
                    <div><h6>Health Reminders</h6><p>Stay on top of your health with smart alerts</p></div>
                </div>
                <div class="pref-card-body">
                    <form action="{{ route('profile.update') }}" method="POST" id="reminderPrefsForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="redirect_to" value="{{ route('user.settings') }}#notifications">
                        <input type="hidden" name="first_name" value="{{ auth()->user()->first_name }}">
                        <input type="hidden" name="last_name" value="{{ auth()->user()->last_name }}">
                        <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                        <input type="hidden" name="pref_checkup_reminders" value="0">
                        @php $checkupOn = old('pref_checkup_reminders', auth()->user()->pref_checkup_reminders ?? true); @endphp
                        @foreach([
                            ['id'=>'period_reminder',   'title'=>'Period Reminders',         'desc'=>'Get reminded 3 days before predicted period',   'on'=>true,  'device'=>true],
                            ['id'=>'checkup_reminder',  'title'=>'Checkup Reminders',        'desc'=>'Remind me about upcoming scheduled checkups',   'on'=>(bool) $checkupOn, 'device'=>false, 'name'=>'pref_checkup_reminders'],
                            ['id'=>'ovulation_alert',   'title'=>'Ovulation Window Alerts',  'desc'=>'Notify when fertile window is approaching',     'on'=>true,  'device'=>true],
                            ['id'=>'pregnancy_updates', 'title'=>'Pregnancy Weekly Updates', 'desc'=>'Weekly milestone updates during pregnancy',     'on'=>false, 'device'=>true],
                            ['id'=>'forum_activity',    'title'=>'Forum Activity',            'desc'=>'Replies and mentions in forum discussions',     'on'=>false, 'device'=>true],
                        ] as $t)
                        <div class="pref-row">
                            <div class="pref-row-label"><h6>{{ $t['title'] }}</h6><p>{{ $t['desc'] }}</p></div>
                            <label class="rc-switch">
                                <input type="checkbox" id="{{ $t['id'] }}" class="device-pref" data-device="{{ $t['device'] ? '1' : '0' }}"
                                    @if(!$t['device']) name="{{ $t['name'] }}" value="1" @endif
                                    {{ $t['on'] ? 'checked' : '' }}>
                                <span class="rc-track"><span class="rc-thumb"></span></span>
                            </label>
                        </div>
                        @endforeach
                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle me-1"></i> Save Reminder Preferences</button>
                            <small class="text-muted ms-2"><i class="bi bi-info-circle me-1"></i>Display options are also remembered on this device.</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 8. PRIVACY --}}
        <div class="settings-section" id="section-privacy">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-primary) 12%, transparent);color:var(--accent-violet);"><i class="bi bi-eye-fill"></i></div>
                    <div><h6>Privacy Controls</h6><p>Control who can see your health data</p></div>
                </div>
                <div class="pref-card-body">
                    @foreach([
                        ['id'=>'share_with_midwife','title'=>'Share Data with Midwife','desc'=>'Allow your midwife to view your health records','on'=>true],
                        ['id'=>'share_with_bhw',    'title'=>'Share Data with BHW',    'desc'=>'Allow BHW staff to view your health records',    'on'=>true],
                        ['id'=>'public_forum',      'title'=>'Public Forum Profile',    'desc'=>'Show your name in community forum posts',       'on'=>true],
                    ] as $t)
                    <div class="pref-row">
                        <div class="pref-row-label"><h6>{{ $t['title'] }}</h6><p>{{ $t['desc'] }}</p></div>
                        <label class="rc-switch">
                            <input type="checkbox" id="{{ $t['id'] }}" class="device-pref" data-device="1" {{ $t['on'] ? 'checked' : '' }}>
                            <span class="rc-track"><span class="rc-thumb"></span></span>
                        </label>
                    </div>
                    @endforeach
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Display options are remembered on this device. Clinical record access for your assigned care team is governed by RHU policy.</small>
                </div>
            </div>
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-danger) 12%, transparent);color:var(--danger);"><i class="bi bi-database-fill"></i></div>
                    <div><h6>Data Management</h6><p>Download your health data or deactivate your account</p></div>
                </div>
                <div class="pref-card-body">
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="{{ route('profile.download') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-download me-1"></i> Download My Data</a>
                    </div>
                    <div class="danger-zone">
                        <h6>Danger Zone</h6>
                        <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:0.85rem;">Deactivating your account signs you out immediately and archives your portal access. Your clinical history is retained by the RHU for medical records compliance — contact them anytime to restore access.</p>
                        <form action="{{ route('profile.account.destroy') }}" method="POST" onsubmit="return confirm('Deactivate your account? You will be signed out immediately and lose portal access (restorable via the RHU).');">
                            @csrf
                            @method('DELETE')
                            <div class="row g-2 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label">Confirm current password</label>
                                    <input type="password" name="current_password" class="form-control form-control-sm" placeholder="Enter current password" required autocomplete="current-password">
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-warning btn-sm text-white"><i class="bi bi-archive-fill me-1"></i> Deactivate Account</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function showSection(id, el) {
    if (el && window.event) event.preventDefault();
    document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.settings-nav-item').forEach(n => n.classList.remove('active'));
    const target = document.getElementById('section-' + id);
    if (target) target.classList.add('active');
    if (el) el.classList.add('active');
    if (target) {
        target.querySelectorAll('.fade-in-card').forEach((c,i) => {
            c.classList.remove('visible');
            setTimeout(() => c.classList.add('visible'), i * 60 + 30);
        });
    }
}

function toggleProfileEdit(showEdit) {
    const viewEl = document.getElementById('profileViewMode');
    const editEl = document.getElementById('profileEditMode');
    const btn = document.getElementById('editProfileBtn');
    if (viewEl && editEl) {
        viewEl.style.display = showEdit ? 'none' : 'block';
        editEl.style.display = showEdit ? 'block' : 'none';
        if (btn) btn.style.display = showEdit ? 'none' : 'inline-block';
    }
}

function toggleAddressEdit(showEdit) {
    const viewEl = document.getElementById('addressViewMode');
    const editEl = document.getElementById('addressEditMode');
    const btn = document.getElementById('editAddressBtn');
    if (viewEl && editEl) {
        viewEl.style.display = showEdit ? 'none' : 'block';
        editEl.style.display = showEdit ? 'block' : 'none';
        if (btn) btn.style.display = showEdit ? 'none' : 'inline-block';
    }
}

// Searchable Barangay Dropdown in Settings
function setupSettingsBrgyDropdown() {
    const input = document.getElementById('settingBrgyInput');
    const toggleBtn = document.getElementById('settingBrgyToggle');
    const dropdown = document.getElementById('settingBrgyList');
    const items = document.querySelectorAll('#settingBrgyScroll .brgy-option-item');
    const matchCountEl = document.getElementById('settingBrgyMatchCount');
    const emptyState = document.getElementById('settingBrgyEmpty');

    if (!input || !dropdown) return;

    function openDropdown() {
        dropdown.style.display = 'flex';
    }

    function closeDropdown() {
        dropdown.style.display = 'none';
    }

    function filterList(query) {
        const q = (query || '').trim().toLowerCase();
        let count = 0;
        items.forEach(item => {
            const val = (item.dataset.value || '').toLowerCase();
            if (!q || val.includes(q)) {
                item.classList.remove('hidden');
                count++;
            } else {
                item.classList.add('hidden');
            }
        });
        if (matchCountEl) matchCountEl.textContent = count;
        if (emptyState) emptyState.style.display = count === 0 ? 'block' : 'none';
    }

    input.addEventListener('focus', function() {
        openDropdown();
        filterList(this.value);
    });

    input.addEventListener('input', function() {
        openDropdown();
        filterList(this.value);
    });

    toggleBtn?.addEventListener('click', function(e) {
        e.stopPropagation();
        if (dropdown.style.display === 'flex') {
            closeDropdown();
        } else {
            openDropdown();
            filterList(input.value);
        }
    });

    items.forEach(item => {
        item.addEventListener('click', function() {
            input.value = this.dataset.value;
            items.forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            closeDropdown();
        });
    });

    document.addEventListener('click', function(e) {
        const wrap = document.querySelector('.brgy-combobox-wrap');
        if (wrap && !wrap.contains(e.target)) {
            closeDropdown();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    setupSettingsBrgyDropdown();

    document.querySelectorAll('#section-profile .fade-in-card').forEach((c,i) => {
        setTimeout(() => c.classList.add('visible'), i * 80);
    });
        @include('includes.theme-toggle')
    // Device-level display preferences (notification + privacy toggles
    // without a server column persist per-device and restore on load).
    document.querySelectorAll('.device-pref[data-device="1"]').forEach(function (box) {
        var key = 'rc_pref_' + box.id;
        try {
            var saved = localStorage.getItem(key);
            if (saved !== null) box.checked = (saved === '1');
        } catch (e) {}
        box.addEventListener('change', function () {
            try { localStorage.setItem(key, box.checked ? '1' : '0'); } catch (e) {}
        });
    });
    const hash = window.location.hash.replace('#', '');
    if (hash) {
        const navItem = document.querySelector(`.settings-nav-item[onclick*="${hash}"]`);
        if (navItem) showSection(hash, navItem);
    }

    @if($errors->any())
        @if(old('_section') === 'address' || $errors->has('barangay') || $errors->has('house_number') || $errors->has('purok') || $errors->has('sitio'))
            const addrNav = document.querySelector('.settings-nav-item[onclick*="address"]');
            showSection('address', addrNav);
            toggleAddressEdit(true);
        @elseif(old('_section') === 'profile' || $errors->has('first_name') || $errors->has('last_name') || $errors->has('email') || $errors->has('profile_image'))
            const profNav = document.querySelector('.settings-nav-item[onclick*="profile"]');
            showSection('profile', profNav);
            toggleProfileEdit(true);
        @endif
    @endif
});
</script>
@endpush

@endsection
