@extends('bhw.layout')

@section('title', 'Create Woman - BHW Portal | ReproCare')

@push('styles')
<style>
    /* Patient-type selector: unmistakable selected state (beats portal-theme !important whites) */
    .patient-type-btn {
        flex:1 1 220px;
        text-align:left;
        position:relative;
        transition:all 0.2s ease;
    }
    .patient-type-btn .type-check {
        position:absolute;
        top:8px;
        right:10px;
        font-size:1.1rem;
        display:none;
    }
    #patient_registered:checked + label.patient-type-btn {
        background:linear-gradient(135deg, var(--color-secondary), var(--color-secondary-text)) !important;
        background-color:var(--color-secondary) !important;
        border-color:var(--color-secondary-text) !important;
        color:var(--color-on-solid) !important;
        box-shadow:0 6px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent) !important;
    }
    #patient_unregistered:checked + label.patient-type-btn {
        background:linear-gradient(135deg, var(--color-warning), var(--color-warning-text)) !important;
        background-color:var(--color-warning) !important;
        border-color:var(--color-warning-text) !important;
        color:var(--color-on-solid) !important;
        box-shadow:0 6px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent) !important;
    }
    #patient_registered:checked + label.patient-type-btn small,
    #patient_unregistered:checked + label.patient-type-btn small {
        color:color-mix(in srgb, var(--color-on-solid) 90%, transparent) !important;
    }
    #patient_registered:checked + label.patient-type-btn .type-check,
    #patient_unregistered:checked + label.patient-type-btn .type-check {
        display:inline-block;
    }
    #patient_registered:checked + label.patient-type-btn i.bi-person-badge,
    #patient_unregistered:checked + label.patient-type-btn i.bi-person-plus {
        color:var(--color-on-solid) !important;
    }
    /* Pregnancy toggle: self-contained switch (immune to theme form-check overrides) */
    .pregnancy-toggle {
        display:inline-flex;
        align-items:center;
        gap:0.75rem;
        cursor:pointer;
        user-select:none;
        font-weight:600;
        font-size:0.95rem;
        color:var(--text, var(--color-text));
        padding:0.25rem 0;
    }
    .pregnancy-toggle input {
        position:absolute;
        opacity:0;
        width:1px;
        height:1px;
        overflow:hidden;
        clip:rect(0 0 0 0);
    }
    .pregnancy-track {
        width:48px;
        height:26px;
        border-radius:9999px;
        background:var(--color-border);
        border:1.5px solid var(--color-border);
        position:relative;
        flex-shrink:0;
        transition:background 0.2s ease, border-color 0.2s ease;
    }
    .pregnancy-thumb {
        position:absolute;
        top:50%;
        left:3px;
        transform:translateY(-50%);
        width:18px;
        height:18px;
        border-radius:50%;
        background:var(--color-surface);
        box-shadow:0 1px 4px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);
        transition:left 0.2s ease, background 0.2s ease;
    }
    .pregnancy-toggle input:checked + .pregnancy-track {
        background:linear-gradient(135deg, var(--color-secondary), var(--color-secondary-text));
        border-color:var(--color-secondary-text);
    }
    .pregnancy-toggle input:checked + .pregnancy-track .pregnancy-thumb {
        left:23px;
    }
    .pregnancy-toggle input:focus-visible + .pregnancy-track {
        outline:3px solid color-mix(in srgb, var(--color-secondary) 35%, transparent);
        outline-offset:2px;
    }
</style>
@endpush

@section('bhw-content')
<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Add Woman
                </div>
                <p class="page-hero-subtitle">Create an <strong>Enrolled Account</strong> (Portal-Active · Authenticated Patient with app login) or save an <strong>Unlinked Profile</strong> (BHW-Managed · Field Record Only, no login).</p>
            </div>
            <a href="{{ route('bhw.patients') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> Back to Women
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4 fade-in-card">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('bhw.patients.store') }}" class="fade-in-card rc-adaptive-form">
        @csrf

        <div class="card mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <input type="radio" class="btn-check" name="patient_type" id="patient_registered" value="registered" {{ old('patient_type', 'registered') === 'registered' ? 'checked' : '' }} onchange="togglePatientType()" autocomplete="off">
                    <label class="btn btn-outline-primary patient-type-btn" for="patient_registered">
                        <i class="bi bi-check-circle-fill type-check"></i>
                        <i class="bi bi-person-badge me-1"></i> Enrolled Account
                        <small class="d-block text-muted" style="font-size:0.7rem;">Portal-Active · Direct Access · gets login</small>
                    </label>

                    <input type="radio" class="btn-check" name="patient_type" id="patient_unregistered" value="unregistered" {{ old('patient_type') === 'unregistered' ? 'checked' : '' }} onchange="togglePatientType()" autocomplete="off">
                    <label class="btn btn-outline-primary patient-type-btn" for="patient_unregistered">
                        <i class="bi bi-check-circle-fill type-check"></i>
                        <i class="bi bi-person-plus me-1"></i> Unlinked Profile
                        <small class="d-block text-muted" style="font-size:0.7rem;">BHW-Managed · Field Record Only · no login</small>
                    </label>
                </div>



                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Middle Initial</label>
                        <input type="text" name="middle_initial" class="form-control @error('middle_initial') is-invalid @enderror" value="{{ old('middle_initial') }}" maxlength="10">
                        @error('middle_initial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth') }}">
                        @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact Number *</label>
                        <input type="text" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror" value="{{ old('contact_number') }}" required>
                        @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Barangay *</label>
                        <select name="barangay" class="form-select @error('barangay') is-invalid @enderror" required>
                            <option value="">Select barangay</option>
                            @foreach(($barangays ?? collect()) as $brgy)
                                <option value="{{ $brgy->name }}" {{ old('barangay') === $brgy->name ? 'selected' : '' }}>{{ $brgy->name }}</option>
                            @endforeach
                        </select>
                        @error('barangay')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sitio / Street / Purok</label>
                        <input type="text" name="purok" class="form-control @error('purok') is-invalid @enderror" value="{{ old('purok') }}" placeholder="e.g. Sitio Malinis, Purok 3">
                        @error('purok')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">House No. / Street Address</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}" placeholder="e.g. 123 Sampaguita St">
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pregnancy Details</h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="pregnancy-toggle" for="is_pregnant">
                        <input type="checkbox" id="is_pregnant" name="is_pregnant" {{ old('is_pregnant') ? 'checked' : '' }} onchange="togglePregnancyFields()">
                        <span class="pregnancy-track" aria-hidden="true"><span class="pregnancy-thumb"></span></span>
                        <span>This woman is currently pregnant</span>
                    </label>
                </div>

                <div id="pregnancy-fields" class="d-none">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Last Menstrual Period (LMP) *</label>
                            <input type="date" name="lmp" class="form-control @error('lmp') is-invalid @enderror" value="{{ old('lmp') }}">
                            @error('lmp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expected Due Date (EDD)</label>
                            <input type="date" name="edd" class="form-control @error('edd') is-invalid @enderror" value="{{ old('edd') }}">
                            @error('edd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gravida</label>
                            <input type="number" name="gravida" class="form-control @error('gravida') is-invalid @enderror" value="{{ old('gravida', 1) }}" min="1">
                            @error('gravida')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Para</label>
                            <input type="number" name="para" class="form-control @error('para') is-invalid @enderror" value="{{ old('para', 0) }}" min="0">
                            @error('para')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">GTPAL</label>
                            <input type="text" name="gtpal" class="form-control @error('gtpal') is-invalid @enderror" value="{{ old('gtpal') }}" placeholder="G-T-P-A-L">
                            @error('gtpal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Obstetric History</label>
                            <textarea name="obstetric_history" class="form-control @error('obstetric_history') is-invalid @enderror" rows="3" placeholder="Previous complications, delivery notes, or follow-up reminders">{{ old('obstetric_history') }}</textarea>
                            @error('obstetric_history')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="registered-fields" class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Portal Account Credentials <small class="text-muted" style="font-size:0.75rem;">(Enrolled Account · Portal-Active)</small></h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div id="emergency-fields" class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Primary Emergency Contact</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Contact Name *</label>
                        <input type="text" name="emergency_name_1" class="form-control @error('emergency_name_1') is-invalid @enderror" value="{{ old('emergency_name_1') }}">
                        @error('emergency_name_1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="emergency_relationship_1_select">Relationship *</label>
                        <select id="emergency_relationship_1_select" class="form-select @error('emergency_relationship_1') is-invalid @enderror" onchange="syncEmergencyRelationship()">
                            <option value="">Select relationship</option>
                            @foreach(['Husband', 'Wife', 'Partner', 'Mother', 'Father', 'Sister', 'Brother', 'Daughter', 'Son', 'Guardian', 'Friend', 'Neighbor'] as $rel)
                                <option value="{{ $rel }}">{{ $rel }}</option>
                            @endforeach
                            <option value="__other">Others — type below</option>
                        </select>
                        <input type="text" name="emergency_relationship_1" id="emergency_relationship_1" class="form-control mt-2 d-none @error('emergency_relationship_1') is-invalid @enderror" value="{{ old('emergency_relationship_1') }}" placeholder="Type the relationship">
                        @error('emergency_relationship_1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact Number *</label>
                        <input type="text" name="emergency_contact_number_1" class="form-control @error('emergency_contact_number_1') is-invalid @enderror" value="{{ old('emergency_contact_number_1') }}">
                        @error('emergency_contact_number_1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div id="unregistered-fields" class="card mb-4 d-none">
            <div class="card-header">
                <h5 class="mb-0">Field Visit Information <small class="text-muted" style="font-size:0.75rem;">(Unlinked Profile · BHW-Managed)</small></h5>
            </div>
            <div class="card-body p-4">
                <label class="form-label">Reason for Visit *</label>
                <textarea name="reason_for_visit" class="form-control @error('reason_for_visit') is-invalid @enderror" rows="3" placeholder="Describe why the patient was recorded as a walk-in">{{ old('reason_for_visit') }}</textarea>
                @error('reason_for_visit')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle-fill me-1"></i> Save Woman
            </button>
            <a href="{{ route('bhw.patients') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
function togglePatientType() {
    const registered = document.getElementById('patient_registered').checked;
    const registeredFields = document.getElementById('registered-fields');
    const emergencyFields = document.getElementById('emergency-fields');
    const unregisteredFields = document.getElementById('unregistered-fields');
    const emailInput = document.querySelector('input[name="email"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const passwordConfirmInput = document.querySelector('input[name="password_confirmation"]');
    const reasonInput = document.querySelector('textarea[name="reason_for_visit"]');
    const emergencyInputs = document.querySelectorAll('#emergency-fields input');

    registeredFields.classList.toggle('d-none', !registered);
    if (emergencyFields) emergencyFields.classList.toggle('d-none', !registered);
    unregisteredFields.classList.toggle('d-none', registered);

    emailInput.required = registered;
    passwordInput.required = registered;
    passwordConfirmInput.required = registered;
    emergencyInputs.forEach(function (input) { input.required = registered; });
    reasonInput.required = !registered;
}

var EMERGENCY_RELATIONSHIP_PRESETS = ['Husband', 'Wife', 'Partner', 'Mother', 'Father', 'Sister', 'Brother', 'Daughter', 'Son', 'Guardian', 'Friend', 'Neighbor'];

function syncEmergencyRelationship() {
    var select = document.getElementById('emergency_relationship_1_select');
    var input = document.getElementById('emergency_relationship_1');
    if (!select || !input) return;

    if (select.value === '__other') {
        // Others: reveal the typing box
        input.classList.remove('d-none');
        input.focus();
    } else if (select.value !== '') {
        // Preset picked: store it and hide the typing box
        input.value = select.value;
        input.classList.add('d-none');
    } else {
        input.classList.remove('d-none');
    }
}

function initEmergencyRelationship() {
    var select = document.getElementById('emergency_relationship_1_select');
    var input = document.getElementById('emergency_relationship_1');
    if (!select || !input) return;

    var current = (input.value || '').trim();
    if (EMERGENCY_RELATIONSHIP_PRESETS.indexOf(current) !== -1) {
        select.value = current;
        input.classList.add('d-none');
    } else if (current !== '') {
        select.value = '__other';
        input.classList.remove('d-none');
    } else {
        select.value = '';
        input.classList.add('d-none');
    }
}

function togglePregnancyFields() {
    const isPregnant = document.getElementById('is_pregnant').checked;
    const pregnancyFields = document.getElementById('pregnancy-fields');
    const lmpInput = document.querySelector('input[name="lmp"]');

    pregnancyFields.classList.toggle('d-none', !isPregnant);
    lmpInput.required = isPregnant;
}

function calculateEDD() {
    const lmpInput = document.querySelector('input[name="lmp"]');
    const eddInput = document.querySelector('input[name="edd"]');

    if (!lmpInput.value) {
        eddInput.value = '';
        return;
    }

    const lmpDate = new Date(lmpInput.value);
    lmpDate.setDate(lmpDate.getDate() + 280);
    eddInput.value = lmpDate.toISOString().split('T')[0];
}

document.addEventListener('DOMContentLoaded', function () {
    togglePatientType();
    togglePregnancyFields();
    initEmergencyRelationship();

    const lmpInput = document.querySelector('input[name="lmp"]');
    if (lmpInput) {
        lmpInput.addEventListener('change', calculateEDD);
    }
});
</script>
@endsection
