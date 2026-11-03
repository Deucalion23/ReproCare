@extends('rhu.layout')

@section('title', 'Record Maternal Death - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Record Maternal Death
            </div>
            <p class="page-hero-subtitle">
                Enter details for DOH Maternal Death Surveillance and Response (MDSR).
            </p>
        </div>
        <a href="{{ route('rhu.maternal-deaths.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Maternal Deaths
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:12px;">
        <h6 class="alert-heading fw-bold mb-2">Please resolve the following errors:</h6>
        <ul class="mb-0 text-xs">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card fade-in-card">
    <div class="card-body">
        <form method="POST" action="{{ route('rhu.maternal-deaths.store') }}">
            @csrf

            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Patient Information
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label required-label">Patient Type</label>
                    <select name="patient_type" id="patientTypeSelect" class="form-select" required>
                        <option value="registered" {{ old('patient_type', 'registered') === 'registered' ? 'selected' : '' }}>Enrolled Patient</option>
                        <option value="walk_in" {{ old('patient_type') === 'walk_in' ? 'selected' : '' }}>Unlinked / External Patient</option>
                    </select>
                </div>

                <div class="col-md-8" id="registeredPatientCol">
                    <label class="form-label required-label">Select Enrolled Woman</label>
                    <select name="user_id" id="userSelect" class="form-select">
                        <option value="" disabled selected>-- Select Patient --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                    data-purok="{{ $user->purok_id }}"
                                    data-dob="{{ $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '' }}">
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-8 d-none" id="walkInPatientCol">
                    <label class="form-label required-label">Select Unlinked Patient</label>
                    <select name="walk_in_patient_id" id="walkInSelect" class="form-select">
                        <option value="" disabled selected>-- Select Unlinked Patient --</option>
                        @foreach($walkIns as $walkIn)
                            <option value="{{ $walkIn->id }}" data-purok="{{ $walkIn->purok_id }}" data-age="{{ $walkIn->age }}">
                                {{ $walkIn->name }} (Age: {{ $walkIn->age }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label required-label">Age at Death</label>
                    <input type="number" name="age_at_death" id="ageInput" class="form-control" value="{{ old('age_at_death') }}" min="10" max="60" required>
                </div>

                <div class="col-md-8">
                    <label class="form-label required-label">Purok</label>
                    <select name="purok_id" id="purokSelect" class="form-select" required>
                        <option value="" disabled selected>Select Purok</option>
                        @foreach($puroks as $purok)
                            <option value="{{ $purok->id }}" {{ old('purok_id') == $purok->id ? 'selected' : '' }}>
                                {{ $purok->name }} ({{ $purok->barangay }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Event Details
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label required-label">Date of Death</label>
                    <input type="date" name="death_date" class="form-control" value="{{ old('death_date') }}" max="{{ date('Y-m-d') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Time of Death (Optional)</label>
                    <input type="time" name="death_time" class="form-control" value="{{ old('death_time') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Place of Death</label>
                    <select name="place_of_death" class="form-select" required>
                        <option value="" disabled selected>Select Place</option>
                        @foreach(\App\Models\MaternalDeath::placeLabels() as $value => $label)
                            <option value="{{ $value }}" {{ old('place_of_death') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label required-label">Timing of Death</label>
                    <select name="death_timing" class="form-select" required>
                        <option value="" disabled selected>Select Timing</option>
                        @foreach(\App\Models\MaternalDeath::timingLabels() as $value => $label)
                            <option value="{{ $value }}" {{ old('death_timing') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required-label">Primary Cause Category</label>
                    <select name="cause_category" class="form-select" required>
                        <option value="" disabled selected>Select Cause Category</option>
                        @foreach(\App\Models\MaternalDeath::causeLabels() as $value => $label)
                            <option value="{{ $value }}" {{ old('cause_category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label required-label">Clinical Cause of Death Details</label>
                <textarea name="cause_of_death" rows="3" class="form-control" placeholder="Describe clinical symptoms, complications, and medical diagnosis leading to death." required>{{ old('cause_of_death') }}</textarea>
            </div>

            <div class="mb-5">
                <label class="form-label">Additional Surveillance Notes (Optional)</label>
                <textarea name="notes" rows="3" class="form-control" placeholder="Any additional notes on prenatal history, delay in care, or community audit.">{{ old('notes') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-1"></i> Log Case
                </button>
                <a href="{{ route('rhu.maternal-deaths.index') }}" class="btn btn-outline-secondary px-4">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const patientTypeSelect = document.getElementById('patientTypeSelect');
    const registeredCol = document.getElementById('registeredPatientCol');
    const walkInCol = document.getElementById('walkInPatientCol');
    const userSelect = document.getElementById('userSelect');
    const walkInSelect = document.getElementById('walkInSelect');
    const purokSelect = document.getElementById('purokSelect');
    const ageInput = document.getElementById('ageInput');

    patientTypeSelect.addEventListener('change', function() {
        const registered = this.value === 'registered';
        registeredCol.classList.toggle('d-none', !registered);
        walkInCol.classList.toggle('d-none', registered);
        userSelect.required = registered;
        walkInSelect.required = !registered;
        if (registered) { walkInSelect.value = ''; } else { userSelect.value = ''; }
    });

    // A patient already carries their address, so the purok and age follow the pick.
    [userSelect, walkInSelect].forEach(function (select) {
        select.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.dataset.purok) { purokSelect.value = option.dataset.purok; }
            const dob = option.dataset.dob;
            if (dob) {
                const birth = new Date(dob);
                const today = new Date();
                let age = today.getFullYear() - birth.getFullYear();
                const months = today.getMonth() - birth.getMonth();
                if (months < 0 || (months === 0 && today.getDate() < birth.getDate())) { age--; }
                ageInput.value = age > 0 ? age : '';
            } else if (option.dataset.age) {
                ageInput.value = option.dataset.age;
            }
        });
    });

    patientTypeSelect.dispatchEvent(new Event('change'));
});
</script>
@endpush

@endsection
