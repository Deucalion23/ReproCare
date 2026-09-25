@extends('rhu.layout')

@section('title', 'Edit Morbidity Near-Miss Event - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Edit Near-Miss Event
            </div>
            <p class="page-hero-subtitle">
                Modify maternal near-miss surveillance data.
            </p>
        </div>
        <a href="{{ route('rhu.morbidities.show', $morbidity->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Cancel & Back
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
        <form method="POST" action="{{ route('rhu.morbidities.update', $morbidity->id) }}">
            @csrf
            @method('PUT')

            <!-- Patient Identification -->
            <h5 class="fw-700 mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; border-bottom:1px solid var(--border); padding-bottom:0.5rem; color:var(--text);">Patient Details
            </h5>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-muted">Patient Name</label>
                    <input type="text" class="form-control" value="{{ $morbidity->patient_name }}" readonly style="background-color:var(--border);">
                </div>

                <div class="col-md-6">
                    <label class="form-label required-label">Purok / Barangay</label>
                    <select name="purok_id" class="form-select" required>
                        <option value="" disabled>-- Select Purok --</option>
                        @foreach($puroks as $purok)
                            <option value="{{ $purok->id }}" {{ old('purok_id', $morbidity->purok_id) == $purok->id ? 'selected' : '' }}>
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
                        <option value="" disabled>-- Select Complication --</option>
                        <option value="severe_hemorrhage" {{ old('complication_type', $morbidity->complication_type) === 'severe_hemorrhage' ? 'selected' : '' }}>Severe Obstetric Hemorrhage</option>
                        <option value="eclampsia" {{ old('complication_type', $morbidity->complication_type) === 'eclampsia' ? 'selected' : '' }}>Eclampsia</option>
                        <option value="severe_preeclampsia" {{ old('complication_type', $morbidity->complication_type) === 'severe_preeclampsia' ? 'selected' : '' }}>Severe Pre-eclampsia</option>
                        <option value="sepsis" {{ old('complication_type', $morbidity->complication_type) === 'sepsis' ? 'selected' : '' }}>Sepsis / Severe Systemic Infection</option>
                        <option value="ruptured_uterus" {{ old('complication_type', $morbidity->complication_type) === 'ruptured_uterus' ? 'selected' : '' }}>Ruptured Uterus</option>
                        <option value="severe_anemia" {{ old('complication_type', $morbidity->complication_type) === 'severe_anemia' ? 'selected' : '' }}>Severe Anemia (Hb < 7 g/dL)</option>
                        <option value="obstructed_labor" {{ old('complication_type', $morbidity->complication_type) === 'obstructed_labor' ? 'selected' : '' }}>Obstructed / Prolonged Labor</option>
                        <option value="placenta_previa" {{ old('complication_type', $morbidity->complication_type) === 'placenta_previa' ? 'selected' : '' }}>Placenta Previa</option>
                        <option value="placental_abruption" {{ old('complication_type', $morbidity->complication_type) === 'placental_abruption' ? 'selected' : '' }}>Placental Abruption</option>
                        <option value="other" {{ old('complication_type', $morbidity->complication_type) === 'other' ? 'selected' : '' }}>Other Severe Complication</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Date of Event</label>
                    <input type="date" name="event_date" class="form-control" value="{{ old('event_date', $morbidity->event_date ? $morbidity->event_date->format('Y-m-d') : '') }}" max="{{ date('Y-m-d') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Time of Event (Optional)</label>
                    <input type="time" name="event_time" class="form-control" value="{{ old('event_time', $morbidity->event_time) }}">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label required-label">Place of Event</label>
                    <select name="place_of_event" class="form-select" required>
                        <option value="" disabled>-- Select Place --</option>
                        <option value="home" {{ old('place_of_event', $morbidity->place_of_event) === 'home' ? 'selected' : '' }}>Home</option>
                        <option value="barangay_health_station" {{ old('place_of_event', $morbidity->place_of_event) === 'barangay_health_station' ? 'selected' : '' }}>Barangay Health Station (BHS)</option>
                        <option value="rhu" {{ old('place_of_event', $morbidity->place_of_event) === 'rhu' ? 'selected' : '' }}>Rural Health Unit (RHU)</option>
                        <option value="city_hospital" {{ old('place_of_event', $morbidity->place_of_event) === 'city_hospital' ? 'selected' : '' }}>City Hospital</option>
                        <option value="provincial_hospital" {{ old('place_of_event', $morbidity->place_of_event) === 'provincial_hospital' ? 'selected' : '' }}>Provincial Hospital</option>
                        <option value="private_hospital" {{ old('place_of_event', $morbidity->place_of_event) === 'private_hospital' ? 'selected' : '' }}>Private Hospital</option>
                        <option value="in_transit" {{ old('place_of_event', $morbidity->place_of_event) === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="other" {{ old('place_of_event', $morbidity->place_of_event) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required-label">Case Outcome</label>
                    <select name="outcome" id="outcomeSelect" class="form-select" required>
                        <option value="" disabled>-- Select Outcome --</option>
                        <option value="survived_no_intervention" {{ old('outcome', $morbidity->outcome) === 'survived_no_intervention' ? 'selected' : '' }}>Survived (No Major Surgical Intervention)</option>
                        <option value="survived_with_intervention" {{ old('outcome', $morbidity->outcome) === 'survived_with_intervention' ? 'selected' : '' }}>Survived (With Major Surgical/Clinical Intervention)</option>
                        <option value="transferred_to_higher_facility" {{ old('outcome', $morbidity->outcome) === 'transferred_to_higher_facility' ? 'selected' : '' }}>Transferred to Higher Level Facility</option>
                        <option value="died" {{ old('outcome', $morbidity->outcome) === 'died' ? 'selected' : '' }}>Died (Maternal Death)</option>
                    </select>
                </div>
            </div>

            <div class="mb-4 d-none" id="maternalDeathCol">
                <label class="form-label required-label">Link Enrolled Maternal Death Case</label>
                <select name="maternal_death_id" id="maternalDeathSelect" class="form-select">
                    <option value="">-- Select Death Record --</option>
                    @foreach($deaths as $death)
                        <option value="{{ $death->id }}" {{ old('maternal_death_id', $morbidity->maternal_death_id) == $death->id ? 'selected' : '' }}>
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
                <textarea name="description" rows="3" class="form-control" placeholder="Describe symptoms, vital signs, physical examination, and diagnostic details.">{{ old('description', $morbidity->description) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label">Emergency Interventions Performed</label>
                <textarea name="interventions_done" rows="3" class="form-control" placeholder="Describe clinical actions taken.">{{ old('interventions_done', $morbidity->interventions_done) }}</textarea>
            </div>

            <div class="mb-5">
                <label class="form-label">Surveillance Review Notes (Optional)</label>
                <textarea name="notes" rows="3" class="form-control" placeholder="Any additional notes.">{{ old('notes', $morbidity->notes) }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning px-4 text-dark fw-600">
                    <i class="bi bi-save me-1"></i> Update Case
                </button>
                <a href="{{ route('rhu.morbidities.show', $morbidity->id) }}" class="btn btn-outline-secondary px-4">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const outcomeSelect = document.getElementById('outcomeSelect');
    const maternalDeathCol = document.getElementById('maternalDeathCol');
    const maternalDeathSelect = document.getElementById('maternalDeathSelect');

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

    outcomeSelect.dispatchEvent(new Event('change'));
});
</script>
@endpush

@endsection
