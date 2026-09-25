@extends('cho.layout')

@section('title', 'CHO Role Handover - ReproCare')

@push('styles')
<style>
    .ho-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    .ho-card .card-header { border:none !important; background:var(--color-surface) !important; background-color:var(--color-surface) !important; padding:1.15rem 1.5rem 0.4rem !important; }
    .ho-card .card-header h5 { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800 !important; font-size:1.05rem !important; color:var(--color-text) !important; }
    .ho-card .card-body { padding:1.25rem 1.5rem 1.5rem !important; }
    .ho-info { background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; border:none !important; border-radius:14px !important; color:var(--color-text); }
    .ho-info strong { color:var(--color-text); }
    .ho-step { font-weight:800; font-size:0.92rem; color:var(--color-text); display:flex; align-items:center; gap:0.5rem; }
    .ho-step-num { width:22px; height:22px; border-radius:50%; background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid); font-size:0.7rem; font-weight:800; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
    .ho-step-num.red { background:var(--color-danger); background-color:var(--color-danger); }
    .ho-card .form-control, .ho-card .form-select { background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; border:none !important; border-radius:12px !important; color:var(--color-text) !important; }
    .ho-card .form-control:focus, .ho-card .form-select:focus { background:var(--color-surface) !important; background-color:var(--color-surface) !important; border:none !important; box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent) !important; }
    .ho-card .form-label { color:var(--color-text); font-weight:700; font-size:0.82rem; }
    .ho-card .form-check-input { accent-color:var(--color-secondary-text); }
    /* Minimalist acknowledge checkbox */
    #ackBox { width:0.85rem; height:0.85rem; margin-top:0.2rem; border-radius:4px; border:1px solid var(--color-input-border); box-shadow:none !important; cursor:pointer; }
    #ackBox:checked { background-color:var(--color-secondary-text); border-color:var(--color-secondary-text); }
    #ackBox:focus { box-shadow:none !important; border-color:var(--color-secondary-text); }
    #ackBox:focus-visible { outline:none !important; box-shadow:none !important; }
    /* Segmented pill options — aligned, borderless, no floating radios */
    .ho-opts { display:flex; gap:0.5rem; flex-wrap:wrap; background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:none; border-radius:999px; padding:0.3rem; width:fit-content; max-width:100%; }
    .ho-opts .form-check { padding:0; margin:0; min-height:0; display:flex; }
    .ho-opts .form-check-input { display:none; }
    .ho-opts .form-check-label { border:none; border-radius:999px; font-weight:800; font-size:0.82rem; padding:0.5rem 1.2rem; color:var(--color-text); background:transparent; margin:0; cursor:pointer; white-space:nowrap; }
    .ho-opts .form-check-input:checked + .form-check-label { background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid); box-shadow:0 4px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent); }
    .ho-opts .form-check-input:focus-visible + .form-check-label { box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent); }
    .ho-card .table th, .ho-card .table td { border:none !important; }
    .ho-card .table thead th { font-size:0.68rem; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-text-muted); }
    .ho-exec-btn { border:none !important; border-radius:999px !important; padding:0.45rem 1.1rem !important; font-weight:700 !important; font-size:0.8rem !important; box-shadow:none !important; }
    .ho-dark-btn { border:none !important; border-radius:999px !important; padding:0.62rem 1.4rem !important; font-weight:800 !important; background:var(--color-surface-strong) !important; background-color:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent); }
    .ho-dark-btn:hover { background:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; }
    .ho-back-btn { border:none !important; border-radius:999px !important; padding:0.55rem 1.2rem !important; font-weight:800 !important; font-size:0.82rem !important; background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; color:var(--color-text) !important; }
    .ho-back-btn:hover { background:var(--color-border) !important; color:var(--color-text) !important; }
    .ho-hist-row { padding:0.8rem 1.5rem; }
    .ho-muted { color:var(--color-text-muted); font-size:0.82rem; }
</style>
@endpush

@section('cho-content')

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Super Admin Role Handover
            </div>
            <p class="page-hero-subtitle" style="font-weight:600;">Transfer the CHO Super Admin role without deleting accounts. History, audit trails, and past signatures are preserved.</p>
        </div>
        <a href="{{ route('cho.settings') }}" class="btn btn-sm ho-back-btn" style="position:relative;z-index:1;">
            <i class="bi bi-gear me-1"></i> Back to Settings
        </a>
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

@if(session('recovery_key_plain'))
    <div class="alert alert-warning" role="alert">
        <h6 class="fw-800 mb-1">Recovery Key — copy now, shown once</h6>
        <code class="fs-5 fw-800 user-select-all">{{ session('recovery_key_plain') }}</code>
        <p class="mb-0 mt-1 text-xs">Seal this with the city records. Generating a new key invalidates this one.</p>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card fade-in-card mb-4 ho-card">
            <div class="card-header">
                <h5 class="mb-0">Handover Wizard</h5>
            </div>
            <div class="card-body">
                <div class="alert ho-info d-flex gap-2 align-items-start">
                    <i class="bi bi-person-fill-exclamation fs-5"></i>
                    <div>
                        <strong>Outgoing: {{ $outgoing->name }}</strong> ({{ $outgoing->email }})<br>
                        <span style="font-size:0.82rem;">On execute: this account is archived (login revoked), its signature unbound from future documents, its alerts silenced, and all its sessions terminated. Past records stay attributed. You will be signed out.</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('cho.handover.execute') }}" id="handoverForm">
                    @csrf

                    {{-- STEP 1: incoming --}}
                    <h6 class="ho-step mt-2 mb-2"><span class="ho-step-num">1</span> Incoming CHO Account</h6>
                    <div class="mb-3">
                        <div class="ho-opts">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" value="existing" id="modeExisting" {{ old('mode', 'existing') === 'existing' ? 'checked' : '' }}>
                                <label class="form-check-label fw-600" for="modeExisting">Promote existing staff</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" value="new" id="modeNew" {{ old('mode') === 'new' ? 'checked' : '' }}>
                                <label class="form-check-label fw-600" for="modeNew">Register new CHO account</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3" id="existingFields">
                        <div class="col-12">
                            <label class="form-label">Incoming Staff (approved RHU / Midwife)</label>
                            <select class="form-select" name="incoming_user_id">
                                <option value="">— Select account —</option>
                                @foreach($candidates as $c)
                                    <option value="{{ $c->id }}" {{ (int) old('incoming_user_id') === (int) $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} — {{ $c->email }} ({{ ucfirst($c->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3 d-none" id="newFields">
                        <div class="col-md-4">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" value="{{ old('first_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">M.I.</label>
                            <input type="text" class="form-control" name="middle_initial" value="{{ old('middle_initial') }}" maxlength="5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" value="{{ old('last_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email (login)</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Temporary Password (min 8)</label>
                            <input type="password" class="form-control" name="new_password" autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" class="form-control" name="contact_number" value="{{ old('contact_number') }}">
                        </div>
                    </div>

                    {{-- STEP 2: outgoing auth --}}
                    <h6 class="ho-step mt-4 mb-2"><span class="ho-step-num">2</span> Outgoing Authorization</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Your Current Password ({{ $outgoing->email }})</label>
                            <input type="password" class="form-control" name="outgoing_password" required autocomplete="current-password">
                        </div>
                    </div>

                    {{-- STEP 3: second authorization --}}
                    <h6 class="ho-step mt-4 mb-2"><span class="ho-step-num">3</span> Second Authorization</h6>
                    <div class="mb-3">
                        <div class="ho-opts">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="auth_method" value="recovery_key" id="authKey" {{ old('auth_method', 'recovery_key') === 'recovery_key' ? 'checked' : '' }}>
                                <label class="form-check-label fw-600" for="authKey">System recovery key {{ $recoveryKeySet ? '(on file)' : '(not set up yet)' }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="auth_method" value="rhu_cosign" id="authCosign" {{ old('auth_method') === 'rhu_cosign' ? 'checked' : '' }}>
                                <label class="form-check-label fw-600" for="authCosign">RHU Admin co-signature</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3" id="keyFields">
                        <div class="col-md-6">
                            <label class="form-label">Recovery Key</label>
                            <input type="text" class="form-control" name="recovery_key" placeholder="RC-XXXX-XXXX-XXXX" autocomplete="off">
                        </div>
                    </div>
                    <div class="row g-3 mb-3 d-none" id="cosignFields">
                        <div class="col-md-6">
                            <label class="form-label">RHU Co-signer Email</label>
                            <input type="email" class="form-control" name="cosigner_email" value="{{ old('cosigner_email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">RHU Co-signer Password</label>
                            <input type="password" class="form-control" name="cosigner_password" autocomplete="off">
                        </div>
                    </div>

                    {{-- STEP 4: confirm + execute --}}
                    <h6 class="ho-step mt-4 mb-2"><span class="ho-step-num red">4</span> Confirm &amp; Execute</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Type the incoming account email to confirm</label>
                            <input type="email" class="form-control" name="confirm_email" value="{{ old('confirm_email') }}" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check d-flex gap-2 align-items-start" style="background:var(--color-bg); background-color:var(--color-bg); border:none; border-radius:12px; padding:0.85rem 1rem;">
                                <input class="form-check-input mt-1" type="checkbox" name="acknowledge" value="1" id="ackBox" required>
                                <label class="form-check-label" for="ackBox" style="font-size:0.85rem; color:var(--color-text);">
                                    I understand this archives <strong>{{ $outgoing->name }}</strong>, revokes its access and sessions,
                                    and makes the transfer permanent in the audit trail.
                                </label>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
                            <a href="{{ route('cho.settings') }}" class="btn btn-sm ho-back-btn">Cancel</a>
                            <button type="submit" class="btn btn-danger ho-exec-btn" onclick="return confirm('Execute irreversible CHO role handover now?')">
                                <i class="bi bi-arrow-left-right me-1"></i> Execute Handover
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card fade-in-card mb-4 ho-card">
            <div class="card-header"><h5 class="mb-0">Recovery Key</h5></div>
            <div class="card-body">
                <p class="ho-muted">Status: <strong style="color:var(--color-text);">{{ $recoveryKeySet ? 'On file' : 'Not set up' }}</strong>. Generate a fresh key before a planned transition.</p>
                <form method="POST" action="{{ route('cho.handover.recovery-key') }}" class="row g-2">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" name="current_password" required autocomplete="current-password">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-sm w-100 ho-dark-btn"><i class="bi bi-key me-1"></i> Generate New Key</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card fade-in-card mb-4 ho-card">
            <div class="card-header"><h5 class="mb-0">Transfer History</h5></div>
            <div class="card-body p-0">
                @forelse($history as $h)
                    <div class="ho-hist-row">
                        <div class="fw-700" style="font-size:0.82rem; color:var(--color-text);">{{ $h->outgoing_name }} → {{ $h->incoming_name }}</div>
                        <div class="ho-muted">{{ $h->created_at->format('M d, Y h:i A') }} · via {{ $h->auth_method === 'recovery_key' ? 'recovery key' : 'RHU co-sign' }}</div>
                    </div>
                @empty
                    <p class="ho-muted p-3 mb-0 px-4">No transfers recorded. The outgoing account remains the original CHO.</p>
                @endforelse
            </div>
        </div>

        <div class="card fade-in-card mb-4 ho-card">
            <div class="card-header"><h5 class="mb-0">Effect Matrix</h5></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size:0.78rem;">
                    <thead><tr><th>Action</th><th>Outgoing</th><th>Incoming</th></tr></thead>
                    <tbody>
                        <tr><td>System Access</td><td>Revoked (archived)</td><td>Granted (CHO role)</td></tr>
                        <tr><td>Historical Logs</td><td>Preserved (read-only)</td><td>Begins new log</td></tr>
                        <tr><td>Digital Signature</td><td>Inactivated</td><td>Active for new docs</td></tr>
                        <tr><td>Notifications</td><td>Disabled</td><td>All city-wide alerts</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function toggleMode() {
        var isNew = document.getElementById('modeNew').checked;
        document.getElementById('newFields').classList.toggle('d-none', !isNew);
        document.getElementById('existingFields').classList.toggle('d-none', isNew);
    }
    function toggleAuth() {
        var isCosign = document.getElementById('authCosign').checked;
        document.getElementById('cosignFields').classList.toggle('d-none', !isCosign);
        document.getElementById('keyFields').classList.toggle('d-none', isCosign);
    }
    document.getElementById('modeExisting').addEventListener('change', toggleMode);
    document.getElementById('modeNew').addEventListener('change', toggleMode);
    document.getElementById('authKey').addEventListener('change', toggleAuth);
    document.getElementById('authCosign').addEventListener('change', toggleAuth);
    toggleMode(); toggleAuth();
})();
</script>
@endpush

@endsection
