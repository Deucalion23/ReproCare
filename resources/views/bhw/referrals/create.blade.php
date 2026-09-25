@extends('bhw.layout')

@section('bhw-content')
<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Create Checkup Referral</div>
                <p class="page-hero-subtitle">Search existing women fast or add a new walk-in referral from Barangay Burgos.</p>
            </div>
            <a href="{{ route('bhw.referrals.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> Back to Referrals
            </a>
        </div>
    </div>

    <div class="card fade-in-card">
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('bhw.referrals.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="form-label fw-semibold">Patient Source</label>
                    <div class="d-flex flex-wrap gap-2">
                        <input type="radio" class="btn-check" name="patient_type" id="patient_registered" value="registered" {{ old('patient_type', 'registered') === 'registered' ? 'checked' : '' }} onchange="togglePatientType()">
                        <label class="btn btn-outline-primary" for="patient_registered">Enrolled Woman</label>

                        <input type="radio" class="btn-check" name="patient_type" id="patient_walk_in" value="walk_in" {{ old('patient_type') === 'walk_in' ? 'checked' : '' }} onchange="togglePatientType()">
                        <label class="btn btn-outline-primary" for="patient_walk_in">Existing Unlinked</label>

                        <input type="radio" class="btn-check" name="patient_type" id="patient_new_walk_in" value="new_walk_in" {{ old('patient_type') === 'new_walk_in' ? 'checked' : '' }} onchange="togglePatientType()">
                        <label class="btn btn-outline-primary" for="patient_new_walk_in">New Unlinked</label>
                    </div>
                </div>

                <div id="existing-patient-section" class="mb-4">
                    <div id="registered-patient-group">
                        <label class="form-label fw-semibold">Search Enrolled Woman</label>
                        <input type="text" id="registered-search" class="form-control mb-2" placeholder="Search by name..." oninput="filterOptions('registered-search', 'registered-patient-select')">
                        <select name="user_id" id="registered-patient-select" class="form-select">
                            <option value="">Select enrolled woman</option>
                            @foreach($women as $woman)
                                <option value="{{ $woman->id }}" {{ (string) old('user_id') === (string) $woman->id ? 'selected' : '' }}>
                                    {{ $woman->name }}{{ $woman->purok?->name ? ' - ' . $woman->purok->name : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="walk-in-patient-group" class="d-none">
                        <label class="form-label fw-semibold">Search Existing Unlinked Profile</label>
                        <input type="text" id="walkin-search" class="form-control mb-2" placeholder="Search by name..." oninput="filterOptions('walkin-search', 'unregistered-patient-select')">
                        <select name="walk_in_patient_id" id="unregistered-patient-select" class="form-select">
                            <option value="">Select unlinked patient</option>
                            @foreach($walkInPatients as $patient)
                                <option value="{{ $patient->id }}" {{ (string) old('walk_in_patient_id') === (string) $patient->id ? 'selected' : '' }}>
                                    {{ $patient->full_name }}{{ $patient->purok?->name ? ' - ' . $patient->purok->name : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="new-walk-in-section" class="d-none">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Middle Initial</label>
                            <input type="text" name="middle_initial" class="form-control" maxlength="10" value="{{ old('middle_initial') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" maxlength="20" value="{{ old('contact_number') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Barangay *</label>
                            <select name="barangay" class="form-select">
                                <option value="">Select barangay</option>
                                @foreach(($barangays ?? collect()) as $brgy)
                                    <option value="{{ $brgy->name }}" {{ old('barangay') === $brgy->name ? 'selected' : '' }}>{{ $brgy->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sitio / Street / Purok *</label>
                            <input type="text" name="purok" class="form-control" value="{{ old('purok') }}" placeholder="e.g. Sitio Malinis, Purok 3">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reason for Visit</label>
                            <textarea name="reason_for_visit" class="form-control" rows="2">{{ old('reason_for_visit') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Receiving Midwife *</label>
                        <select name="assigned_midwife_id" class="form-select" required>
                            <option value="">Select midwife</option>
                            @foreach(($midwives ?? collect()) as $midwife)
                                <option value="{{ $midwife->id }}" {{ (string) old('assigned_midwife_id') === (string) $midwife->id ? 'selected' : '' }}>{{ $midwife->name }}</option>
                            @endforeach
                        </select>
                        @if(($midwives ?? collect())->isEmpty())
                            <small class="text-danger">No approved midwife on file — ask the RHU to register one first.</small>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Urgency Level *</label>
                        <select name="urgency" class="form-select" required>
                            <option value="routine" {{ old('urgency', 'routine') === 'routine' ? 'selected' : '' }}>Routine</option>
                            <option value="urgent" {{ old('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="emergency" {{ old('urgency') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Reason for Referral *</label>
                        <input type="text" name="reason" class="form-control" required placeholder="Example: prenatal checkup needed" value="{{ old('reason') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">BHW Notes</label>
                        <textarea name="bhw_notes" class="form-control" rows="3" placeholder="Observations, concerns, or follow-up notes">{{ old('bhw_notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4 flex-wrap gap-2">
                    <a href="{{ route('bhw.referrals.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill me-1"></i> Submit Referral
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePatientType() {
    const registered = document.getElementById('patient_registered').checked;
    const walkIn = document.getElementById('patient_walk_in').checked;
    const newWalkIn = document.getElementById('patient_new_walk_in').checked;
    const existingSection = document.getElementById('existing-patient-section');
    const registeredGroup = document.getElementById('registered-patient-group');
    const walkInGroup = document.getElementById('walk-in-patient-group');
    const newWalkInSection = document.getElementById('new-walk-in-section');
    const registeredSelect = document.getElementById('registered-patient-select');
    const walkInSelect = document.getElementById('unregistered-patient-select');
    const newWalkInFields = newWalkInSection.querySelectorAll('input, textarea, select');

    existingSection.classList.toggle('d-none', newWalkIn);
    registeredGroup.classList.toggle('d-none', !registered);
    walkInGroup.classList.toggle('d-none', !walkIn);
    newWalkInSection.classList.toggle('d-none', !newWalkIn);

    registeredSelect.required = registered;
    walkInSelect.required = walkIn;
    registeredSelect.disabled = !registered;
    walkInSelect.disabled = !walkIn;

    newWalkInFields.forEach((field) => {
        field.disabled = !newWalkIn;
    });

    document.querySelector('input[name="first_name"]').required = newWalkIn;
    document.querySelector('input[name="last_name"]').required = newWalkIn;
    document.querySelector('input[name="purok"]').required = newWalkIn;
    document.querySelector('select[name="barangay"]').required = newWalkIn;
}

function filterOptions(inputId, selectId) {
    const term = document.getElementById(inputId).value.toLowerCase();
    const options = document.getElementById(selectId).options;

    for (let index = 0; index < options.length; index++) {
        const option = options[index];
        const match = option.text.toLowerCase().includes(term) || option.value === '';
        option.hidden = !match;
    }
}

document.addEventListener('DOMContentLoaded', togglePatientType);
</script>
@endsection
