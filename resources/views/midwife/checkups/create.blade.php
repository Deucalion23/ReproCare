@extends('midwife.layout')

@section('title', 'Schedule Checkup - ReproCare')

@push('styles')
<style>
    .ck-label { font-size:0.72rem; font-weight:800; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:0.6px; display:flex; align-items:center; gap:0.45rem; }
    .ck-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; display:inline-block; }
    .ck-dot.rose { background:var(--color-secondary-text); }
    .ck-dot.peach { background:var(--color-peach); }
    .ck-dot.mint { background:var(--color-success-text); }
    .ck-dot.lav { background:var(--color-primary); }
    .ck-avatar { width:32px; height:32px; border-radius:50%; background:var(--color-secondary-text); background-color:var(--color-secondary-text); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .ck-input { background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; border:none !important; color:var(--color-text) !important; border-radius:10px !important; }
    .ck-input:focus { background:var(--color-surface) !important; background-color:var(--color-surface) !important; border:none !important; box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent) !important; }
    .ck-input::placeholder { color:var(--color-text-muted); }
    .ptype-wrap { display:inline-flex; gap:0.4rem; background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:none; border-radius:999px; padding:0.22rem; }
    .ptype-label { border:none !important; border-radius:999px !important; font-weight:800 !important; font-size:0.8rem !important; padding:0.34rem 1.05rem !important; color:var(--color-text) !important; background:transparent !important; margin:0 !important; cursor:pointer; }
    .ptype-check:checked + .ptype-label { background:var(--color-secondary-text) !important; background-color:var(--color-secondary-text) !important; color:var(--color-on-solid) !important; box-shadow:0 4px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent); }
    #patient_type_unregistered:checked + .ptype-label { background:var(--color-peach) !important; background-color:var(--color-peach) !important; color:var(--color-on-solid) !important; box-shadow:0 4px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent); }
    .ck-hint { color:var(--color-text-muted); font-size:0.72rem; margin-top:0.2rem; }
    .ck-btn-dark { border:none; border-radius:999px; padding:0.5rem 1.25rem; font-weight:800; font-size:0.84rem; background:var(--color-surface-strong) !important; background-color:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent); }
    .ck-btn-dark:hover { background:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; transform:translateY(-1px); }
    .ck-btn-soft { border:none; border-radius:999px; padding:0.5rem 1.05rem; font-weight:800; font-size:0.84rem; background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; color:var(--color-text) !important; }
    .ck-btn-soft:hover { background:var(--color-border) !important; color:var(--color-text) !important; }

    /* ── Compaction ──
       The layout pins the hero and the card body with !important, so these need
       the same body .main-content prefix plus !important to land. Same recipe as
       the bhw checkups index. */
    body .main-content .page-hero { padding:0.85rem 1.25rem !important; margin-bottom:1rem !important; border-radius:16px !important; }
    body .main-content .page-hero .page-hero-title { font-size:1.15rem !important; margin-bottom:0.15rem !important; }
    body .main-content .page-hero .page-hero-subtitle { font-size:0.78rem !important; margin-bottom:0 !important; }
    body .main-content .page-hero .btn-hero-secondary { padding:0.4rem 0.95rem !important; font-size:0.8rem !important; }
    .ck-card { border:none !important; border-radius:20px !important; overflow:hidden; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; }
    body .main-content .ck-card .card-body { padding:1rem 1.25rem 1.15rem !important; }
    body .main-content .ck-card .form-label { margin-bottom:0.28rem; }
    .ck-section + .ck-section { border-top:1px solid var(--color-border); margin-top:1rem; padding-top:1rem; }
    .ck-section > .ck-label { margin-bottom:0.7rem; }
    .ck-actions { border-top:1px solid var(--color-border); margin-top:1rem; padding-top:1rem; gap:0.5rem; }
    /* The layout forces every .col-md-* / .col-lg-* inside .rc-adaptive-form to
       full width below 1024px, so pairing fields there only works on a desktop.
       This grid is untouched by that rule and pairs from 576px up. */
    .ck-fields { display:grid; gap:0.75rem 1rem; grid-template-columns:minmax(0,1fr); }
    .ck-fields > * { min-width:0; }
    @media (min-width: 576px) {
        .ck-fields { grid-template-columns:repeat(2, minmax(0,1fr)); }
        .ck-fields > .ck-full { grid-column:1 / -1; }
    }
    @media (min-width: 1024px) {
        .ck-fields { grid-template-columns:repeat(12, minmax(0,1fr)); }
        /* Same .ck-fields > prefix as .ck-full above, so a cell that is both
           ck-full (spans on a phone) and ck-w6 (a third on a desktop) resolves. */
        .ck-fields > .ck-w3 { grid-column:span 3; }
        .ck-fields > .ck-w4 { grid-column:span 4; }
        .ck-fields > .ck-w6 { grid-column:span 6; }
        .ck-fields > .ck-w8 { grid-column:span 8; }
    }
    @media (max-width: 575.98px) {
        .ck-actions { flex-direction:column; align-items:stretch; }
        .ck-actions .btn { width:100%; justify-content:center; margin-left:0 !important; }
    }
    .ck-optional { font-size:0.62rem; font-weight:800; letter-spacing:0.04em; text-transform:uppercase; color:var(--color-text-muted); background:var(--color-surface-soft); background-color:var(--color-surface-soft); border-radius:999px; padding:0.12rem 0.45rem; }
    .ck-locked { background:var(--color-bg); background-color:var(--color-bg); border-radius:12px; }
</style>
@endpush

@section('midwife-content')

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">@if(isset($woman) && $woman)
                    Schedule Checkup for {{ $woman->name }}
                @else
                    Schedule New Checkup
                @endif
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            @if(isset($woman) && $woman)
                <a href="{{ route('midwife.patient-details', $woman->id) }}" class="btn-hero-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            @else
                <a href="{{ route('midwife.checkups.index') }}" class="btn-hero-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Checkups
                </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert"
         style="background:color-mix(in srgb, var(--color-success-text) 10%, transparent); border:1px solid color-mix(in srgb, var(--color-success-text) 30%, transparent); color:var(--success); border-radius:10px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter:invert(1);"></button>
    </div>
@endif

<div class="card fade-in-card ck-card">
    <div class="card-body">
        {{-- rc-adaptive-form: ≥1024px multi-column (Layout A) · <1024px strictly stacked (Layout B) --}}
        <form action="{{ route('midwife.checkups.store') }}" method="POST" class="rc-adaptive-form">
            @csrf

            {{-- ── PATIENT ── --}}
            <div class="ck-section">
                <div class="ck-label">
                    <span class="ck-dot rose"></span> Patient
                </div>

                @if(isset($woman) && $woman)
                    <input type="hidden" name="patient_type" value="registered">
                    <input type="hidden" name="user_id" value="{{ $woman->id }}">
                    <div class="ck-locked p-2 px-3 d-flex align-items-center gap-2">
                        <div class="ck-avatar">
                            <i class="bi bi-person" style="color:var(--color-on-solid);font-size:0.95rem;"></i>
                        </div>
                        <div style="min-width:0;">
                            <div style="font-weight:700; color:var(--color-text); line-height:1.15;">{{ $woman->name }}</div>
                            <small style="color:var(--color-text-muted);">{{ $woman->email }}</small>
                        </div>
                    </div>
                @else
                    <div class="ck-fields">
                        <div class="ck-w4">
                            <label class="form-label ck-label">
                                Patient Type <span style="color:var(--color-danger-text);">*</span>
                            </label>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="ptype-wrap">
                                <input type="radio" class="btn-check ptype-check" name="patient_type" id="patient_type_registered" value="registered" {{ old('patient_type', 'registered') === 'registered' ? 'checked' : '' }}>
                                <label class="btn ptype-label" for="patient_type_registered">Enrolled</label>
                                <input type="radio" class="btn-check ptype-check" name="patient_type" id="patient_type_unregistered" value="walk_in" {{ old('patient_type') === 'walk_in' ? 'checked' : '' }}>
                                <label class="btn ptype-label" for="patient_type_unregistered">Unlinked</label>
                                </span>
                            </div>
                        </div>

                        {{-- Only one search box is visible at a time, so d-none lets the
                             other one take the same slot in the grid. --}}
                        <div class="ck-w8" id="registeredSearchWrap">
                            <label for="patientSearch" class="form-label ck-label">
                                Search Patient
                            </label>
                            <input type="text" class="form-control ck-input" id="patientSearch"
                                   placeholder="Type to search patient by name or email...">
                        </div>
                        <div class="ck-w8 d-none" id="walkInSearchWrap">
                            <label for="walkInSearch" class="form-label ck-label">
                                Search Unlinked Woman
                            </label>
                            <input type="text" class="form-control ck-input" id="walkInSearch"
                                   placeholder="Type to search unlinked woman...">
                        </div>

                        <div class="ck-full" id="registeredSelectWrap">
                            <label for="user_id" class="form-label ck-label">
                                Select Patient <span style="color:var(--color-danger-text);">*</span>
                            </label>
                            <select class="form-select ck-input @error('user_id') is-invalid @enderror"
                                    id="user_id" name="user_id" required>
                                <option value="" style="background:var(--bg-card); color:var(--text);">Choose a patient...</option>
                                @if(isset($women) && $women)
                                    @foreach($women as $woman)
                                        <option value="{{ $woman->id }}"
                                                data-search="{{ strtolower($woman->name . ' ' . $woman->email) }}"
                                                {{ old('user_id') == $woman->id ? 'selected' : '' }}
                                                style="background:var(--bg-card); color:var(--text);">
                                            {{ $woman->name }} - {{ $woman->email }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="ck-full d-none" id="unregisteredSelectWrap">
                            <label for="walk_in_patient_id" class="form-label ck-label">
                                Select Unlinked Woman <span style="color:var(--color-danger-text);">*</span>
                            </label>
                            <select class="form-select ck-input @error('walk_in_patient_id') is-invalid @enderror"
                                    id="walk_in_patient_id" name="walk_in_patient_id">
                                <option value="">Choose an unlinked woman...</option>
                                @foreach(($walkInPatients ?? collect()) as $patient)
                                    <option value="{{ $patient->id }}" data-search="{{ strtolower(($patient->full_name ?? '') . ' ' . ($patient->barangay ?? '') . ' ' . ($patient->contact_number ?? '')) }}" {{ old('walk_in_patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->full_name }}{{ $patient->barangay ? ' - ' . $patient->barangay : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('walk_in_patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                @endif
            </div>

            {{-- ── SCHEDULE ── --}}
            <div class="ck-section">
                <div class="ck-label">
                    <span class="ck-dot mint"></span> Schedule
                </div>
                <div class="ck-fields">
                    <div class="ck-w3">
                        <label for="scheduled_date" class="form-label ck-label">
                            Date <span style="color:var(--color-danger-text);">*</span>
                        </label>
                        <input type="date" class="form-control ck-input @error('scheduled_date') is-invalid @enderror"
                               id="scheduled_date" name="scheduled_date" value="{{ old('scheduled_date') }}"
                               min="{{ now()->format('Y-m-d') }}" required>
                        @error('scheduled_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="ck-hint"><i class="bi bi-info-circle me-1"></i>Cannot be in the past</small>
                    </div>

                    <div class="ck-w3">
                        <label for="scheduled_time" class="form-label ck-label">
                            Time <span style="color:var(--color-danger-text);">*</span>
                        </label>
                        <input type="time" class="form-control ck-input @error('scheduled_time') is-invalid @enderror"
                               id="scheduled_time" name="scheduled_time" value="{{ old('scheduled_time') }}" required>
                        @error('scheduled_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="ck-hint"><i class="bi bi-clock me-1"></i>8:00 AM - 5:00 PM</small>
                    </div>

                    <div class="ck-full ck-w6">
                        <label for="purpose" class="form-label ck-label">
                            Purpose of Checkup <span style="color:var(--color-danger-text);">*</span>
                        </label>
                        <textarea class="form-control ck-input @error('purpose') is-invalid @enderror"
                                  id="purpose" name="purpose" rows="2" required
                                  placeholder="Describe the reason for this checkup...">{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ── NOTES ── --}}
            <div class="ck-section">
                <div class="ck-label">
                    <span class="ck-dot lav"></span> Notes <span class="ck-optional">optional</span>
                </div>
                <div class="ck-fields">
                    <div class="ck-full">
                        <label for="notes" class="form-label ck-label">
                            Special Instructions
                        </label>
                        <textarea class="form-control ck-input @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="2" placeholder="Any special instructions or preparations needed...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap ck-actions">
                <a href="{{ route('midwife.checkups.index') }}" class="btn ck-btn-soft">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
                <button type="reset" class="btn ck-btn-soft ms-auto">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
                <button type="submit" class="btn ck-btn-dark">
                    <i class="bi bi-check-circle me-1"></i> Schedule Checkup
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const searchInput = document.getElementById('patientSearch');
    const patientSelect = document.getElementById('user_id');
    const patientTypeRegistered = document.getElementById('patient_type_registered');
    const patientTypeUnregistered = document.getElementById('patient_type_unregistered');
    const registeredSearchWrap = document.getElementById('registeredSearchWrap');
    const registeredSelectWrap = document.getElementById('registeredSelectWrap');
    const unregisteredSelectWrap = document.getElementById('unregisteredSelectWrap');
    const walkInSelect = document.getElementById('walk_in_patient_id');
    const walkInSearchWrap = document.getElementById('walkInSearchWrap');
    const walkInSearch = document.getElementById('walkInSearch');

    function syncPatientType() {
        if (!patientTypeRegistered || !patientTypeUnregistered || !registeredSelectWrap || !unregisteredSelectWrap) {
            return;
        }

        const isRegistered = patientTypeRegistered.checked;
        registeredSearchWrap?.classList.toggle('d-none', !isRegistered);
        registeredSelectWrap.classList.toggle('d-none', !isRegistered);
        unregisteredSelectWrap.classList.toggle('d-none', isRegistered);
        walkInSearchWrap?.classList.toggle('d-none', isRegistered);

        if (patientSelect) {
            patientSelect.disabled = !isRegistered;
            patientSelect.required = isRegistered;
        }
        if (walkInSelect) {
            walkInSelect.disabled = isRegistered;
            walkInSelect.required = !isRegistered;
        }
    }

    if (searchInput && patientSelect) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const options = patientSelect.querySelectorAll('option');

            options.forEach(function(option) {
                if (option.value === '') {
                    option.style.display = 'block';
                    return;
                }

                const searchData = option.getAttribute('data-search') || '';
                if (searchData.includes(searchTerm)) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        });
    }

    if (walkInSearch && walkInSelect) {
        walkInSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const options = walkInSelect.querySelectorAll('option');

            options.forEach(function(option) {
                if (option.value === '') {
                    option.hidden = false;
                    return;
                }

                const searchData = option.getAttribute('data-search') || '';
                option.hidden = !searchData.includes(searchTerm);
            });
        });
    }

    patientTypeRegistered?.addEventListener('change', syncPatientType);
    patientTypeUnregistered?.addEventListener('change', syncPatientType);
    syncPatientType();
</script>
@endpush
@endsection
