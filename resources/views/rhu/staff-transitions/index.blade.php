@extends('rhu.layout')

@section('title', 'Staff Transitions - RHU | ReproCare')

@section('rhu-content')

@php
    $typeLabels = ['midwife_replace' => 'Replace Midwife', 'president_replace' => 'Replace President', 'bhw_transfer' => 'Transfer BHW Roster'];
@endphp

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Staff Transitions</div>
            <p class="page-hero-subtitle">Replace personnel with bulk reassignment, archiving, and audit logging. History is never deleted.</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-3" style="position:relative;z-index:1;">
        @foreach($typeLabels as $key => $label)
            <a href="{{ route('rhu.staff-transitions.index', ['type' => $key]) }}"
               class="btn btn-sm {{ $type === $key ? 'btn-filter' : 'btn-outline-secondary' }}" style="border-radius:10px;">{{ $label }}</a>
        @endforeach
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card fade-in-card mb-4">
            <div class="card-header"><h5 class="mb-0">{{ $typeLabels[$type] }}</h5></div>
            <div class="card-body">
                <form method="GET" action="{{ route('rhu.staff-transitions.index') }}" class="row g-2 align-items-end mb-3">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <div class="col-md-8">
                        <label class="form-label">Outgoing Account</label>
                        <select class="form-select" name="outgoing_id" onchange="this.form.submit()">
                            <option value="">— Select outgoing —</option>
                            @foreach($outgoings as $o)
                                <option value="{{ $o->id }}" {{ $outgoing && (int) $outgoing->id === (int) $o->id ? 'selected' : '' }}>
                                    {{ $o->name }} — {{ $o->email }}{{ $o->barangay ? ' (' . $o->barangay . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                @if(!$outgoing)
                    <p class="text-muted text-xs mb-0">Select an outgoing account to preview exactly what will move.</p>
                @else
                    <div class="alert alert-dark d-flex gap-2 flex-wrap">
                        @foreach($counts as $k => $v)
                            <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $k)) }}: <strong>{{ $v }}</strong></span>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('rhu.staff-transitions.execute') }}">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" name="outgoing_id" value="{{ $outgoing->id }}">

                        @if($type === 'midwife_replace')
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="incoming_mode" value="existing" id="mwEx" checked>
                                        <label class="form-check-label fw-600" for="mwEx">Existing midwife absorbs cases</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="incoming_mode" value="new" id="mwNew">
                                        <label class="form-check-label fw-600" for="mwNew">Register new midwife</label>
                                    </div>
                                </div>
                                <div class="col-12" id="mwExistingBox">
                                    <label class="form-label">Incoming Midwife</label>
                                    <select class="form-select" name="incoming_user_id">
                                        <option value="">— Select —</option>
                                        @foreach($candidates as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }} — {{ $c->email }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 d-none" id="mwNewBox">
                                    <div class="row g-2">
                                        <div class="col-md-4"><label class="form-label">First Name</label><input type="text" class="form-control" name="first_name"></div>
                                        <div class="col-md-2"><label class="form-label">M.I.</label><input type="text" class="form-control" name="middle_initial" maxlength="5"></div>
                                        <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" class="form-control" name="last_name"></div>
                                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
                                        <div class="col-md-6"><label class="form-label">Temp Password</label><input type="password" class="form-control" name="new_password" autocomplete="new-password"></div>
                                        <div class="col-md-4"><label class="form-label">Birthdate</label><input type="date" class="form-control" name="date_of_birth"></div>
                                        <div class="col-md-4"><label class="form-label">Gender</label>
                                            <select class="form-select" name="gender"><option value="">—</option><option value="female">Female</option><option value="male">Male</option></select>
                                        </div>
                                        <div class="col-md-4"><label class="form-label">Contact</label><input type="text" class="form-control" name="contact_number"></div>
                                        <div class="col-md-4"><label class="form-label">PRC / DOH License</label><input type="text" class="form-control" name="license_number"></div>
                                        <div class="col-md-4"><label class="form-label">License Expiry</label><input type="date" class="form-control" name="license_expiry"></div>
                                        <div class="col-md-4"><label class="form-label">Facility</label><input type="text" class="form-control" name="rhu_assignment" value="Rural Health Unit 1"></div>
                                        <div class="col-md-6"><label class="form-label">Primary Barangay</label>
                                            <select class="form-select" name="assigned_barangay"><option value="">—</option>
                                                @foreach($barangays as $b)<option value="{{ $b->name }}">{{ $b->name }}</option>@endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6"><label class="form-label">Catchment Barangays</label>
                                            <select class="form-select" name="catchment_barangays[]" multiple size="3">
                                                @foreach($barangays as $b)<option value="{{ $b->name }}">{{ $b->name }}</option>@endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($type === 'president_replace')
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Outgoing Disposition ({{ $outgoing->barangay }})</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="disposition" value="demote" id="dispDemote" checked>
                                        <label class="form-check-label" for="dispDemote">Demote to regular BHW (keeps login)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="disposition" value="inactive" id="dispInactive">
                                        <label class="form-check-label" for="dispInactive">Flag as Inactive (revokes login + sessions)</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="incoming_mode" value="existing" id="prEx" checked>
                                        <label class="form-check-label fw-600" for="prEx">Promote BHW of {{ $outgoing->barangay }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="incoming_mode" value="new" id="prNew">
                                        <label class="form-check-label fw-600" for="prNew">Register new president</label>
                                    </div>
                                </div>
                                <div class="col-12" id="prExistingBox">
                                    <label class="form-label">Incoming BHW (same barangay enforced)</label>
                                    <select class="form-select" name="incoming_user_id">
                                        <option value="">— Select —</option>
                                        @foreach($candidates as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }} — {{ $c->email }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 d-none" id="prNewBox">
                                    <div class="row g-2">
                                        <div class="col-md-6"><label class="form-label">First Name</label><input type="text" class="form-control" name="first_name"></div>
                                        <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" class="form-control" name="last_name"></div>
                                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
                                        <div class="col-md-6"><label class="form-label">Temp Password</label><input type="password" class="form-control" name="new_password" autocomplete="new-password"></div>
                                        <div class="col-md-6"><label class="form-label">Contact</label><input type="text" class="form-control" name="contact_number"></div>
                                        <div class="col-md-6"><label class="form-label">Barangay (auto)</label><input type="text" class="form-control" value="{{ $outgoing->barangay }}" readonly></div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($type === 'bhw_transfer')
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Incoming BHWs (same barangay enforced)</label>
                                    @forelse($candidates as $c)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="incoming_ids[]" value="{{ $c->id }}" id="inc{{ $c->id }}">
                                            <label class="form-check-label" for="inc{{ $c->id }}">{{ $c->name }} — {{ $c->email }}</label>
                                        </div>
                                    @empty
                                        <p class="text-muted text-xs">No same-barangay BHW available. Register one first.</p>
                                    @endforelse
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="scope" value="all" id="scopeAll" checked>
                                        <label class="form-check-label fw-600" for="scopeAll">Transfer entire roster ({{ $patients->count() }} patients)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="scope" value="selected" id="scopeSel">
                                        <label class="form-check-label fw-600" for="scopeSel">Select patients</label>
                                    </div>
                                </div>
                                <div class="col-12 d-none border rounded-3 p-2" id="patientBox" style="max-height:220px;overflow:auto;">
                                    @foreach($patients as $p)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="patient_ids[]" value="{{ $p->id }}" id="pat{{ $p->id }}">
                                            <label class="form-check-label" for="pat{{ $p->id }}">{{ $p->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-3">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Execute this irreversible transfer? History is preserved; access changes apply immediately.')">
                                <i class="bi bi-arrow-left-right me-1"></i> Execute Transfer
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card fade-in-card mb-4">
            <div class="card-header"><h5 class="mb-0">Transition Ledger</h5></div>
            <div class="card-body p-0">
                @forelse($history as $h)
                    <div class="p-3 border-bottom">
                        <div class="fw-700 text-xs text-uppercase text-muted">{{ str_replace('_', ' ', $h->type) }}</div>
                        <div class="fw-600" style="font-size:0.85rem;">{{ $h->outgoing_name }} → {{ $h->incoming_names }}</div>
                        <div class="text-xs text-muted">{{ $h->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                @empty
                    <p class="text-xs text-muted p-3 mb-0">No transitions recorded yet.</p>
                @endforelse
            </div>
        </div>
        <div class="card fade-in-card mb-4">
            <div class="card-header"><h5 class="mb-0">Principles</h5></div>
            <div class="card-body text-xs text-muted">
                <p class="mb-1">• Outgoing accounts are archived, never deleted.</p>
                <p class="mb-1">• Only actionable items move; completed history stays.</p>
                <p class="mb-1">• Every transfer writes a protected ROLE_CHANGE_EVENT.</p>
                <p class="mb-0">• One active President per barangay is enforced.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function on(id, fn) { var el = document.getElementById(id); if (el) el.addEventListener('change', fn); }
    function swap(exId, newId, exBox, newBox) {
        var isNew = document.getElementById(newId) && document.getElementById(newId).checked;
        var a = document.getElementById(exBox), b = document.getElementById(newBox);
        if (a) a.classList.toggle('d-none', !!isNew);
        if (b) b.classList.toggle('d-none', !isNew);
    }
    on('mwEx', function () { swap(0, 'mwNew', 'mwExistingBox', 'mwNewBox'); });
    on('mwNew', function () { swap(0, 'mwNew', 'mwExistingBox', 'mwNewBox'); });
    on('prEx', function () { swap(0, 'prNew', 'prExistingBox', 'prNewBox'); });
    on('prNew', function () { swap(0, 'prNew', 'prExistingBox', 'prNewBox'); });
    on('scopeAll', function () { document.getElementById('patientBox').classList.add('d-none'); });
    on('scopeSel', function () { document.getElementById('patientBox').classList.remove('d-none'); });
})();
</script>
@endpush

@endsection
