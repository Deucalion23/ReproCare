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
                <div class="col-md-6">
                    <label class="form-label">Link Enrolled Patient (Optional)</label>
                    <select name="user_id" id="patientSelect" class="form-select">
                        <option value="" selected>None (Unlinked / External Patient)</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}"
                                    data-name="{{ $patient->name }}"
                                    data-dob="{{ $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '' }}"
                                    data-barangay="{{ $patient->barangay }}">
                                {{ $patient->name }} ({{ $patient->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required-label">Patient Name</label>
                    <input type="text" name="patient_name" id="patientNameInput" class="form-control" value="{{ old('patient_name') }}" placeholder="Full Name" required>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label required-label">Age at Death</label>
                    <input type="number" name="age_at_death" id="ageInput" class="form-control" value="{{ old('age_at_death') }}" min="1" max="120" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Barangay</label>
                    <select name="barangay" id="barangaySelect" class="form-select" required>
                        <option value="" disabled selected>Select Barangay</option>
                        @forelse(($barangays ?? collect()) as $brgy)
                            <option value="{{ $brgy->name }}" {{ old('barangay') === $brgy->name ? 'selected' : '' }}>Barangay {{ $brgy->name }}</option>
                        @empty
                            <option value="Burgos" {{ old('barangay') === 'Burgos' ? 'selected' : '' }}>Barangay Burgos</option>
                            <option value="Padlan" {{ old('barangay') === 'Padlan' ? 'selected' : '' }}>Barangay Padlan</option>
                        @endforelse
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Purok</label>
                    <select name="purok_id" class="form-select" required>
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
                        <option value="hospital" {{ old('place_of_death') === 'hospital' ? 'selected' : '' }}>Hospital</option>
                        <option value="rhu_clinic" {{ old('place_of_death') === 'rhu_clinic' ? 'selected' : '' }}>RHU Clinic / Lying-in</option>
                        <option value="home" {{ old('place_of_death') === 'home' ? 'selected' : '' }}>Home</option>
                        <option value="transit" {{ old('place_of_death') === 'transit' ? 'selected' : '' }}>In Transit (On the way to hospital)</option>
                        <option value="others" {{ old('place_of_death') === 'others' ? 'selected' : '' }}>Others</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label required-label">Timing of Death</label>
                    <select name="death_timing" class="form-select" required>
                        <option value="" disabled selected>Select Timing</option>
                        <option value="pregnancy" {{ old('death_timing') === 'pregnancy' ? 'selected' : '' }}>During Pregnancy</option>
                        <option value="delivery" {{ old('death_timing') === 'delivery' ? 'selected' : '' }}>During Delivery</option>
                        <option value="postpartum_24h" {{ old('death_timing') === 'postpartum_24h' ? 'selected' : '' }}>Within 24 Hours Postpartum</option>
                        <option value="postpartum_42d" {{ old('death_timing') === 'postpartum_42d' ? 'selected' : '' }}>Within 42 Days Postpartum</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required-label">Primary Cause Category</label>
                    <select name="cause_category" class="form-select" required>
                        <option value="" disabled selected>Select Cause Category</option>
                        <option value="hemorrhage" {{ old('cause_category') === 'hemorrhage' ? 'selected' : '' }}>Obstetric Hemorrhage</option>
                        <option value="hypertension_pre_eclampsia" {{ old('cause_category') === 'hypertension_pre_eclampsia' ? 'selected' : '' }}>Hypertension / Eclampsia</option>
                        <option value="sepsis_infection" {{ old('cause_category') === 'sepsis_infection' ? 'selected' : '' }}>Pregnancy Sepsis / Infection</option>
                        <option value="obstructed_labor" {{ old('cause_category') === 'obstructed_labor' ? 'selected' : '' }}>Obstructed / Prolonged Labor</option>
                        <option value="abortion_complications" {{ old('cause_category') === 'abortion_complications' ? 'selected' : '' }}>Complications of Abortion</option>
                        <option value="indirect_causes_heart_diabetes" {{ old('cause_category') === 'indirect_causes_heart_diabetes' ? 'selected' : '' }}>Indirect Causes (Cardiac, Diabetes, etc.)</option>
                        <option value="others" {{ old('cause_category') === 'others' ? 'selected' : '' }}>Others</option>
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
    const patientSelect = document.getElementById('patientSelect');
    const nameInput = document.getElementById('patientNameInput');
    const dobInput = document.getElementById('ageInput');
    const barangaySelect = document.getElementById('barangaySelect');

    patientSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            nameInput.value = option.dataset.name;
            barangaySelect.value = option.dataset.barangay;
            
            // Calculate age from DOB if available
            const dob = option.dataset.dob;
            if (dob) {
                const birthDate = new Date(dob);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                dobInput.value = age > 0 ? age : '';
            } else {
                dobInput.value = '';
            }
        } else {
            nameInput.value = '';
            dobInput.value = '';
            barangaySelect.value = '';
        }
    });
});
</script>
@endpush

@endsection
