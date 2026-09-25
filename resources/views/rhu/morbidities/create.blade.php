@extends('rhu.layout')

@section('title', 'Record Morbidity Near-Miss Event - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Record Morbidity Near-Miss Event
            </div>
            <p class="page-hero-subtitle">
                Enter maternal near-miss surveillance data for clinical quality audits.
            </p>
        </div>
        <a href="{{ route('rhu.morbidities.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to List
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
        <form method="POST" action="{{ route('rhu.morbidities.store') }}">
            @csrf

            <!-- Patient Selection -->
            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Patient Identification
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
                            <option value="{{ $user->id }}" data-purok="{{ $user->purok_id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-8 d-none" id="walkInPatientCol">
                    <label class="form-label required-label">Select Unlinked Patient</label>
                    <select name="walk_in_patient_id" id="walkInSelect" class="form-select">
                        <option value="" disabled selected>-- Select Unlinked Patient --</option>
                        @foreach($walkIns as $wi)
                            <option value="{{ $wi->id }}" data-purok="{{ $wi->purok_id }}" {{ old('walk_in_patient_id') == $wi->id ? 'selected' : '' }}>
                                {{ $wi->name }} (Age: {{ $wi->age }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Link Active Pregnancy (Optional)</label>
                    <select name="pregnancy_id" class="form-select">
                        <option value="" selected>None / Unknown</option>
                        @foreach($pregnancies as $preg)
                            <option value="{{ $preg->id }}" {{ old('pregnancy_id') == $preg->id ? 'selected' : '' }}>
                                Patient: {{ $preg->patient_name }} (LMP: {{ $preg->lmp ? $preg->lmp->format('M j, Y') : 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required-label">Purok / Barangay</label>
                    <select name="purok_id" id="purokSelect" class="form-select" required>
                        <option value="" disabled selected>-- Select Purok --</option>
                        @foreach($puroks as $purok)
                            <option value="{{ $purok->id }}" {{ old('purok_id') == $purok->id ? 'selected' : '' }}>
                                {{ $purok->name }} ({{ $purok->barangay }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Complication details -->
            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Clinical Complication Details
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label required-label">Complication Type</label>
                    <select name="complication_type" class="form-select" required>
                        <option value="" disabled selected>-- Select Complication --</option>
                        <option value="severe_hemorrhage" {{ old('complication_type') === 'severe_hemorrhage' ? 'selected' : '' }}>Severe Obstetric Hemorrhage</option>
                        <option value="eclampsia" {{ old('complication_type') === 'eclampsia' ? 'selected' : '' }}>Eclampsia</option>
                        <option value="severe_preeclampsia" {{ old('complication_type') === 'severe_preeclampsia' ? 'selected' : '' }}>Severe Pre-eclampsia</option>
                        <option value="sepsis" {{ old('complication_type') === 'sepsis' ? 'selected' : '' }}>Sepsis / Severe Systemic Infection</option>
                        <option value="ruptured_uterus" {{ old('complication_type') === 'ruptured_uterus' ? 'selected' : '' }}>Ruptured Uterus</option>
                        <option value="severe_anemia" {{ old('complication_type') === 'severe_anemia' ? 'selected' : '' }}>Severe Anemia (Hb < 7 g/dL)</option>
                        <option value="obstructed_labor" {{ old('complication_type') === 'obstructed_labor' ? 'selected' : '' }}>Obstructed / Prolonged Labor</option>
                        <option value="placenta_previa" {{ old('complication_type') === 'placenta_previa' ? 'selected' : '' }}>Placenta Previa</option>
                        <option value="placental_abruption" {{ old('complication_type') === 'placental_abruption' ? 'selected' : '' }}>Placental Abruption</option>
                        <option value="other" {{ old('complication_type') === 'other' ? 'selected' : '' }}>Other Severe Complication</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Date of Event</label>
                    <input type="date" name="event_date" class="form-control" value="{{ old('event_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Time of Event (Optional)</label>
                    <input type="time" name="event_time" class="form-control" value="{{ old('event_time') }}">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label required-label">Place of Event</label>
                    <select name="place_of_event" class="form-select" required>
                        <option value="" disabled selected>-- Select Place --</option>
                        <option value="home" {{ old('place_of_event') === 'home' ? 'selected' : '' }}>Home</option>
                        <option value="barangay_health_station" {{ old('place_of_event') === 'barangay_health_station' ? 'selected' : '' }}>Barangay Health Station (BHS)</option>
                        <option value="rhu" {{ old('place_of_event') === 'rhu' ? 'selected' : '' }}>Rural Health Unit (RHU)</option>
                        <option value="city_hospital" {{ old('place_of_event') === 'city_hospital' ? 'selected' : '' }}>City Hospital</option>
                        <option value="provincial_hospital" {{ old('place_of_event') === 'provincial_hospital' ? 'selected' : '' }}>Provincial Hospital</option>
                        <option value="private_hospital" {{ old('place_of_event') === 'private_hospital' ? 'selected' : '' }}>Private Hospital</option>
                        <option value="in_transit" {{ old('place_of_event') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="other" {{ old('place_of_event') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required-label">Case Outcome</label>
                    <select name="outcome" id="outcomeSelect" class="form-select" required>
                        <option value="" disabled selected>-- Select Outcome --</option>
                        <option value="survived_no_intervention" {{ old('outcome') === 'survived_no_intervention' ? 'selected' : '' }}>Survived (No Major Surgical Intervention)</option>
                        <option value="survived_with_intervention" {{ old('outcome') === 'survived_with_intervention' ? 'selected' : '' }}>Survived (With Major Surgical/Clinical Intervention)</option>
                        <option value="transferred_to_higher_facility" {{ old('outcome') === 'transferred_to_higher_facility' ? 'selected' : '' }}>Transferred to Higher Level Facility</option>
                        <option value="died" {{ old('outcome') === 'died' ? 'selected' : '' }}>Died (Maternal Death)</option>
                    </select>
                </div>
            </div>

            <div class="mb-4 d-none" id="maternalDeathCol">
                <label class="form-label required-label">Link Enrolled Maternal Death Case</label>
                <select name="maternal_death_id" id="maternalDeathSelect" class="form-select">
                    <option value="" selected>-- Select Death Record --</option>
                    @foreach($deaths as $death)
                        <option value="{{ $death->id }}" {{ old('maternal_death_id') == $death->id ? 'selected' : '' }}>
                            {{ $death->patient_name }} (Died: {{ $death->death_date->format('M j, Y') }})
                        </option>
                    @endforeach
                </select>
                <div class="form-text text-danger text-xs mt-1">
                    *Note: Since outcome is "Died", please link the official maternal death surveillance case file.
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Clinical Presentation Description</label>
                <textarea name="description" rows="3" class="form-control" placeholder="Describe symptoms, vital signs, physical examination, and diagnostic details.">{{ old('description') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label">Emergency Interventions Performed</label>
                <textarea name="interventions_done" rows="3" class="form-control" placeholder="Describe clinical actions taken (e.g. blood transfusion, manual removal of placenta, balloon tamponade, uterine massage, drug administration, surgery).">{{ old('interventions_done') }}</textarea>
            </div>

            <div class="mb-5">
                <label class="form-label">Surveillance Review Notes (Optional)</label>
                <textarea name="notes" rows="3" class="form-control" placeholder="Any additional notes on patient risk factors, delay in access, or care coordination details.">{{ old('notes') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-1"></i> Log Near-Miss Case
                </button>
                <a href="{{ route('rhu.morbidities.index') }}" class="btn btn-outline-secondary px-4">
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
    
    const outcomeSelect = document.getElementById('outcomeSelect');
    const maternalDeathCol = document.getElementById('maternalDeathCol');
    const maternalDeathSelect = document.getElementById('maternalDeathSelect');

    // Toggle Patient type column
    patientTypeSelect.addEventListener('change', function() {
        if (this.value === 'registered') {
            registeredCol.classList.remove('d-none');
            walkInCol.classList.add('d-none');
            userSelect.required = true;
            walkInSelect.required = false;
            walkInSelect.value = '';
        } else {
            registeredCol.classList.add('d-none');
            walkInCol.classList.remove('d-none');
            userSelect.required = false;
            walkInSelect.required = true;
            userSelect.value = '';
        }
    });

    // Auto-select Purok if user/walk-in is selected
    userSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.dataset.purok) {
            purokSelect.value = option.dataset.purok;
        }
    });

    walkInSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.dataset.purok) {
            purokSelect.value = option.dataset.purok;
        }
    });

    // Toggle Maternal death column
    outcomeSelect.addEventListener('change', function() {
        if (this.value === 'died') {
            maternalDeathCol.classList.remove('d-none');
            maternalDeathSelect.required = true;
        } else {
            maternalDeathCol.classList.add('d-none');
            maternalDeathSelect.required = false;
            maternalDeathSelect.value = '';
        }
    });

    // Run trigger on load to handle validation redirects
    patientTypeSelect.dispatchEvent(new Event('change'));
    outcomeSelect.dispatchEvent(new Event('change'));
});
</script>
@endpush

@endsection
