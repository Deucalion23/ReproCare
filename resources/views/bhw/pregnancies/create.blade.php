@extends('bhw.layout')

@section('bhw-content')
<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Report Pregnancy</div>
                <p class="page-hero-subtitle">Report an enrolled woman or unlinked profile as pregnant. The midwife is notified instantly with your contact details.</p>
            </div>
            <a href="{{ route('bhw.pregnancies.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> Back to Pregnancies
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
                    <div class="mt-2">
                        <a href="{{ route('bhw.pregnancies.index') }}" class="alert-link">Open Pregnancies</a>
                        to update the existing record instead.
                    </div>
                </div>
            @endif

            <div class="alert alert-info">
                <i class="bi bi-info-circle me-1"></i>
                Already tracked with an active pregnancy? Open that record in
                <a href="{{ route('bhw.pregnancies.index') }}" class="alert-link">Pregnancies</a>
                instead of reporting again.
            </div>

            <form method="POST" action="{{ route('bhw.pregnancies.store') }}">
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
                            <label class="form-label">Contact Number *</label>
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
                            <label class="form-label">Sitio / Street / Purok</label>
                            <input type="text" name="purok" class="form-control" value="{{ old('purok') }}" placeholder="e.g. Sitio Malinis, Purok 3">
                        </div>
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
                                    {{ $woman->name }}{{ $woman->purok?->name ? ' - ' . $woman->purok->name : '' }}{{ $woman->pregnancies->isNotEmpty() ? ' (already tracked)' : '' }}
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
                                    {{ $patient->full_name }}{{ $patient->purok?->name ? ' - ' . $patient->purok->name : '' }}{{ $patient->pregnancies->isNotEmpty() ? ' (already tracked)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Last Menstrual Period (LMP) *</label>
                        <input type="date" name="lmp" class="form-control" required max="{{ today()->subDay()->toDateString() }}" value="{{ old('lmp') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expected Delivery (EDD)</label>
                        <input type="date" name="edd" class="form-control" value="{{ old('edd') }}">
                        <small class="text-muted">Leave blank to auto-compute (LMP + 280 days).</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gravida</label>
                        <input type="number" name="gravida" class="form-control" min="1" value="{{ old('gravida') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Para</label>
                        <input type="number" name="para" class="form-control" min="0" value="{{ old('para') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observed Symptoms / Danger Signs</label>
                        <textarea name="symptoms" class="form-control" rows="2" placeholder="e.g. nausea, swelling, headache, bleeding...">{{ old('symptoms') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes for the Midwife</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Anything the midwife should know">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 flex-wrap">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill me-1"></i> Send Report to Midwife
                    </button>
                    <a href="{{ route('bhw.pregnancies.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePatientType() {
        var checked = document.querySelector('input[name="patient_type"]:checked');
        var type = checked ? checked.value : 'registered';
        var isNew = type === 'new_walk_in';
        document.getElementById('existing-patient-section').classList.toggle('d-none', isNew);
        document.getElementById('new-walk-in-section').classList.toggle('d-none', !isNew);
        document.getElementById('registered-patient-group').classList.toggle('d-none', type !== 'registered');
        document.getElementById('walk-in-patient-group').classList.toggle('d-none', type !== 'walk_in');
        document.getElementById('registered-patient-select').required = type === 'registered';
        document.getElementById('registered-patient-select').disabled = isNew;
        document.getElementById('unregistered-patient-select').required = type === 'walk_in';
        document.getElementById('unregistered-patient-select').disabled = isNew;
    }
    function filterOptions(searchId, selectId) {
        var q = document.getElementById(searchId).value.toLowerCase();
        document.querySelectorAll('#' + selectId + ' option').forEach(function (opt) {
            opt.hidden = q !== '' && opt.text.toLowerCase().indexOf(q) === -1;
        });
    }
    document.addEventListener('DOMContentLoaded', togglePatientType);
</script>
@endsection
