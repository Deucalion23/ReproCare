@extends('bhw-president.layout')

@section('title', 'Settings - BHW President Portal | ReproCare')

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
    @media (max-width: 768px) {
        .settings-wrap { flex-direction:column; }
        .settings-sidebar { width:100%; position:static; }
        .theme-option-grid { grid-template-columns:1fr 1fr; }
    }
</style>
@endpush

@section('bhw-president-content')

<div class="settings-page-header fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>Settings — Barangay &amp; Team Level</h1>
            <p>Team coordination for {{ $barangay !== '' ? 'Barangay ' . $barangay : 'your barangay' }} · BHW reporting, alerts, and your profile</p>
        </div>
        <a href="{{ route('bhw-president.dashboard') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-house-door me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="settings-wrap">

    {{-- LEFT NAV --}}
    <div class="settings-sidebar fade-in-card">
        <div class="settings-nav">
            <a class="settings-nav-item active" onclick="showSection('context', this)" href="#">
                <i class="bi bi-geo-alt-fill" style="color:var(--primary-light);"></i> Barangay Context
            </a>
            <a class="settings-nav-item" onclick="showSection('deadlines', this)" href="#">
                <i class="bi bi-calendar-check-fill" style="color:var(--info);"></i> Team Deadlines
            </a>
            <a class="settings-nav-item" onclick="showSection('notifications', this)" href="#">
                <i class="bi bi-bell-fill" style="color:var(--success);"></i> Barangay Alerts
            </a>
            <a class="settings-nav-item" onclick="showSection('account', this)" href="#">
                <i class="bi bi-person-fill" style="color:var(--info);"></i> Profile
            </a>
            <a class="settings-nav-item" onclick="showSection('security', this)" href="#">
                <i class="bi bi-shield-lock-fill" style="color:var(--warning);"></i> Security
            </a>
            <a class="settings-nav-item" onclick="showSection('appearance', this)" href="#">
                <i class="bi bi-palette-fill" style="color:var(--primary-light);"></i> Appearance
            </a>
        </div>
    </div>

    <div class="settings-content">

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

        {{-- BARANGAY CONTEXT --}}
        <div class="settings-section active" id="section-context">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <div><h6>Barangay Context</h6><p>Read-only confirmation of your jurisdiction and support chain</p></div>
                </div>
                <div class="pref-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Assigned barangay</label>
                            <input type="text" class="form-control" value="{{ $barangay !== '' ? 'Barangay ' . $barangay : 'Not assigned' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Designated RHU station</label>
                            <input type="text" class="form-control" value="{{ \App\Models\Setting::get('rhu.station_name', 'RHU I') }}{{ \App\Models\Setting::get('rhu.contact_number') ? ' · ' . \App\Models\Setting::get('rhu.contact_number') : '' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Active midwife</label>
                            <input type="text" class="form-control" value="{{ $midwife?->name ?? 'None assigned' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Midwife contact</label>
                            <input type="text" class="form-control" value="{{ $midwife?->contact_number ?? $midwife?->phone ?? '—' }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Team BHWs</label>
                            <input type="text" class="form-control" value="{{ $teamBhws->count() }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Puroks in barangay</label>
                            <input type="text" class="form-control" value="{{ $puroks->count() }} ({{ $unassignedPuroks->count() }} unassigned)" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pending reports / high-risk</label>
                            <input type="text" class="form-control" value="{{ $pendingReports }} / {{ $highRiskCount }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TEAM DEADLINES --}}
        <div class="settings-section" id="section-deadlines">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-info) 12%, transparent);color:var(--info);"><i class="bi bi-calendar-check-fill"></i></div>
                    <div><h6>Team Reporting Deadlines</h6><p>Monthly BHW field-report submission deadline for your team</p></div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('bhw-president.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="deadline">
                        <div class="col-md-6">
                            <label class="form-label">Submission deadline (day of month)</label>
                            <input type="number" name="deadline_day" class="form-control" min="1" max="28" value="{{ old('deadline_day', $deadlineDay) }}" required>
                            <small class="text-muted">BHWs should submit field reports by this day each month. City default: {{ \App\Models\Setting::get('reports.deadline_day', 25) }}.</small>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Deadline</button>
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

        {{-- ACCOUNT --}}
        <div class="settings-section" id="section-account">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-info) 12%, transparent);color:var(--info);"><i class="bi bi-person-fill"></i></div>
                    <div><h6>Account Information</h6><p>Update your personal details</p></div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('bhw-president.settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="profile">
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
                                <label class="form-label">Contact number (team coordination)</label>
                                <input type="tel" name="contact_number" class="form-control" value="{{ old('contact_number', auth()->user()->contact_number ?? auth()->user()->phone ?? '') }}" placeholder="Enter contact number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profile photo</label>
                                <input type="file" name="profile_image" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" value="{{ auth()->user()->date_of_birth ?? '' }}" readonly>
                            </div>
                            <div class="col-12">
                                <div style="border:1px solid var(--border); border-radius:16px; overflow:hidden; background:var(--bg-card2);">
                                    <div style="padding:1rem 1rem 0.75rem; border-bottom:1px solid var(--border);">
                                        <div style="font-size:0.92rem; font-weight:700; color:var(--text);">
                                            <i class="bi bi-geo-alt-fill me-2" style="color:var(--info);"></i>Coverage Location
                                        </div>
                                        <div style="font-size:0.82rem; color:var(--text-muted); margin-top:0.25rem;">
                                            Barangay Burgos Padlan, San Carlos City, Pangasinan
                                        </div>
                                    </div>
                                    <div style="padding:1rem;">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Barangay</label>
                                                <input type="text" class="form-control" value="{{ auth()->user()->barangay ?? 'Not assigned' }}" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control" value="San Carlos City" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Province</label>
                                                <input type="text" class="form-control" value="Pangasinan" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-flex gap-2 pt-1">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Changes</button>
                                <button type="button" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i> Cancel</button>
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
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-warning) 12%, transparent);color:var(--warning);"><i class="bi bi-key-fill"></i></div>
                    <div><h6>Change Password</h6><p>Update your login password</p></div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('bhw-president.settings.update') }}" class="row g-3">
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
                            <button type="submit" class="btn btn-primary"><i class="bi bi-shield-lock me-1"></i> Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-success) 12%, transparent);color:var(--success);"><i class="bi bi-shield-fill-check"></i></div>
                    <div><h6>Security Options</h6><p>Extra layers of account protection</p></div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('bhw-president.settings.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="2fa">
                        <div class="pref-row">
                            <div class="pref-row-label">
                                <h6>Two-Factor Authentication</h6>
                                <p>Add an extra layer of security to your login</p>
                            </div>
                            <label class="rc-switch">
                                <input type="checkbox" name="pref_2fa_enabled" value="1" {{ auth()->user()->pref_2fa_enabled ? 'checked' : '' }} onchange="this.form.submit()">
                                <span class="rc-track"><span class="rc-thumb"></span></span>
                            </label>
                        </div>
                    </form>
                    <div class="mt-3 pt-2">
                        <h6 style="font-size:0.85rem;font-weight:700;color:var(--text);margin-bottom:0.75rem;">Active Sessions</h6>
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
        </div>

        {{-- BARANGAY ALERTS --}}
        <div class="settings-section" id="section-notifications">
            <div class="pref-card fade-in-card">
                <div class="pref-card-header">
                    <div class="pref-card-header-icon" style="background:color-mix(in srgb, var(--color-success) 12%, transparent);color:var(--success);"><i class="bi bi-bell-fill"></i></div>
                    <div><h6>Barangay Alert Preferences</h6><p>Toggles for team coordination alerts in your barangay</p></div>
                </div>
                <div class="pref-card-body">
                    <form method="POST" action="{{ route('bhw-president.settings.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="section" value="alerts">
                        @php
                            $alertRows = [
                                ['key' => 'alert_unassigned_puroks', 'title' => 'Unassigned puroks', 'desc' => "Notify about puroks with no active BHW assignment ({$unassignedPuroks->count()} now)", 'on' => $alertPrefs['unassigned_puroks']],
                                ['key' => 'alert_pending_reports', 'title' => 'Pending BHW report submissions', 'desc' => "Notify about reports awaiting review ({$pendingReports} now)", 'on' => $alertPrefs['pending_reports']],
                                ['key' => 'alert_high_risk', 'title' => 'Unresolved high-risk flags', 'desc' => 'Notify about active high-risk pregnancies needing attention', 'on' => $alertPrefs['high_risk']],
                            ];
                        @endphp
                        @foreach($alertRows as $t)
                            <div class="pref-row">
                                <div class="pref-row-label"><h6>{{ $t['title'] }}</h6><p>{{ $t['desc'] }}</p></div>
                                <label class="rc-switch">
                                    <input type="checkbox" name="{{ $t['key'] }}" value="1" {{ $t['on'] ? 'checked' : '' }}>
                                    <span class="rc-track"><span class="rc-thumb"></span></span>
                                </label>
                            </div>
                        @endforeach
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Alert Preferences</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function showSection(id, el) {
    event.preventDefault();
    document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.settings-nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById('section-' + id).classList.add('active');
    el.classList.add('active');
    document.querySelectorAll('#section-' + id + ' .fade-in-card').forEach((c,i) => {
        c.classList.remove('visible');
        setTimeout(() => c.classList.add('visible'), i * 60 + 30);
    });
}
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#section-appearance .fade-in-card').forEach((c,i) => {
        setTimeout(() => c.classList.add('visible'), i * 80);
    });
        @include('includes.theme-toggle')
});
</script>
@endpush

@endsection
