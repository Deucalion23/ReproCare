@extends('midwife.layout')

@section('title', 'Edit Pregnancy - ReproCare')

@push('styles')
<style>
    .preg-grid { display:grid; gap:1.25rem; }
    .preg-card { border:1px solid var(--border); border-radius:18px; background:var(--bg-card); padding:1.25rem; }
    .preg-label { font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.8rem; }
    .preg-avatar { width:68px; height:68px; border-radius:18px; object-fit:cover; border:2px solid var(--border); }
    .bmi-chip { display:inline-flex; align-items:center; padding:0.55rem 0.8rem; border-radius:12px; background:var(--bg-card2); min-height:42px; font-weight:600; }
</style>
@endpush

@section('midwife-content')
@php
    $profile = $pregnancy->maternalCareTargetClient;
    $isWalkIn = (bool) $pregnancy->walk_in_patient_id;
    $selectedPatient = $isWalkIn ? $pregnancy->walkInPatient : $pregnancy->woman;
    $selectedName = $selectedPatient ? ($isWalkIn ? $selectedPatient->full_name : $selectedPatient->name) : 'Unknown';
    $selectedSubtitle = $selectedPatient ? ($isWalkIn ? ($selectedPatient->contact_number ?? 'No contact number') : ($selectedPatient->email ?? 'No email')) : '';
    $selectedImage = $isWalkIn ? '/images/avatars/avatar-female.svg' : ($selectedPatient->profile_image_url ?? '/images/avatars/avatar-female.svg');
    [$storedSystolic, $storedDiastolic] = array_pad(explode('/', (string) ($pregnancy->healthRecords->first()->bp ?? '')), 2, '');
@endphp

<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <h1 class="page-hero-title">Edit Pregnancy</h1>
                <p class="page-hero-subtitle">Update the current pregnancy record for {{ $selectedName }}.</p>
            </div>
            <a href="{{ route('midwife.pregnancies.show', $pregnancy->id) }}" class="btn-hero-primary">
                <i class="bi bi-arrow-left"></i> Back to Pregnancy
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger fade-in-card">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('midwife.pregnancies.update', $pregnancy->id) }}" method="POST" class="preg-grid">
        @csrf
        @method('PUT')
        <input type="hidden" name="patient_type" value="{{ $isWalkIn ? 'walk_in' : 'registered' }}">

        <div class="preg-card fade-in-card">
            <div class="preg-label"><i class="bi bi-person-vcard me-1"></i>Patient Information</div>
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Patient Type</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <input type="radio" class="btn-check" name="patient_type_switch" id="patient_type_registered" value="registered" {{ !$isWalkIn ? 'checked' : '' }}>
                        <label class="btn btn-outline-primary" for="patient_type_registered">Enrolled</label>
                        <input type="radio" class="btn-check" name="patient_type_switch" id="patient_type_walk_in" value="walk_in" {{ $isWalkIn ? 'checked' : '' }}>
                        <label class="btn btn-outline-primary" for="patient_type_walk_in">Unlinked</label>
                    </div>
                </div>
                <div class="col-md-8" id="registeredSearchWrap">
                    <label for="patientSearch" class="form-label">Search Enrolled Woman</label>
                    <input type="text" class="form-control" id="patientSearch" placeholder="Search by name or email">
                </div>
                <div class="col-md-8" id="registered_patient_section">
                    <label for="user_id" class="form-label">Enrolled Woman</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="">Select woman</option>
                        @foreach($women as $woman)
                            <option value="{{ $woman->id }}"
                                data-name="{{ $woman->name }}"
                                data-subtitle="{{ $woman->email }}"
                                data-image="{{ $woman->profile_image_url }}"
                                data-age="{{ $woman->age }}"
                                data-active="{{ $woman->pregnancies->where('id', '!=', $pregnancy->id)->isNotEmpty() ? '1' : '0' }}"
                                data-search="{{ strtolower($woman->name . ' ' . $woman->email) }}"
                                {{ (int) old('user_id', $pregnancy->user_id) === (int) $woman->id ? 'selected' : '' }}>
                                {{ $woman->name }} - {{ $woman->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4" id="registered_patient_meta">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:var(--bg-card2);">
                        <img src="{{ $selectedImage }}" id="patient_preview_image" alt="Preview" class="preg-avatar">
                        <div>
                            <div id="patient_preview_name" class="fw-bold">{{ $selectedName }}</div>
                            <div id="patient_preview_subtitle" class="text-muted small">{{ $selectedSubtitle }}</div>
                            <div id="patient_preview_age" class="text-muted small">{{ $selectedPatient?->age ? 'Age: ' . $selectedPatient->age . ' years old' : '' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 d-none" id="walk_in_patient_section">
                    <label for="walk_in_patient_id" class="form-label">Unlinked Woman</label>
                    <select class="form-select" id="walk_in_patient_id" name="walk_in_patient_id">
                        <option value="">Select unlinked woman</option>
                        @foreach($walkInPatients as $walkInPatient)
                            <option value="{{ $walkInPatient->id }}"
                                data-name="{{ $walkInPatient->full_name }}"
                                data-subtitle="{{ $walkInPatient->contact_number ?? 'No contact number' }}"
                                data-age="{{ $walkInPatient->age }}"
                                data-active="{{ $walkInPatient->pregnancies->where('id', '!=', $pregnancy->id)->isNotEmpty() ? '1' : '0' }}"
                                {{ (int) old('walk_in_patient_id', $pregnancy->walk_in_patient_id) === (int) $walkInPatient->id ? 'selected' : '' }}>
                                {{ $walkInPatient->full_name }} - {{ $walkInPatient->contact_number ?? 'No contact number' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-none" id="walk_in_patient_meta">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:var(--bg-card2);">
                        <img src="/images/avatars/avatar-female.svg" id="walkin_preview_image" alt="Preview" class="preg-avatar">
                        <div>
                            <div id="walkin_preview_name" class="fw-bold">{{ $selectedName }}</div>
                            <div id="walkin_preview_subtitle" class="text-muted small">{{ $selectedSubtitle }}</div>
                            <div id="walkin_preview_age" class="text-muted small">{{ $selectedPatient?->age ? 'Age: ' . $selectedPatient->age . ' years old' : '' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="preg-card fade-in-card h-100">
                    <div class="preg-label"><i class="bi bi-calendar-heart me-1"></i>Pregnancy Timeline</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="lmp" class="form-label">LMP</label>
                            <input type="date" class="form-control" id="lmp" name="lmp" value="{{ old('lmp', optional($pregnancy->lmp)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="edd" class="form-label">EDD</label>
                            <input type="date" class="form-control" id="edd" value="{{ optional($pregnancy->edd)->format('Y-m-d') }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="aog_display" class="form-label">AOG</label>
                            <input type="text" class="form-control" id="aog_display" value="{{ $pregnancy->formatted_aog }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="gravida" class="form-label">Gravida</label>
                            <input type="number" class="form-control" id="gravida" name="gravida" value="{{ old('gravida', $profile?->gravida) }}" min="1">
                        </div>
                        <div class="col-md-3">
                            <label for="para" class="form-label">Para</label>
                            <input type="number" class="form-control" id="para" name="para" value="{{ old('para', $profile?->parity) }}" min="0">
                        </div>
                        <div class="col-md-3">
                            <label for="gtpal_term" class="form-label">Term</label>
                            <input type="number" class="form-control" id="gtpal_term" name="gtpal_term" value="{{ old('gtpal_term', $profile?->gtpal_term ?? 0) }}" min="0">
                        </div>
                        <div class="col-md-3">
                            <label for="gtpal_preterm" class="form-label">Preterm</label>
                            <input type="number" class="form-control" id="gtpal_preterm" name="gtpal_preterm" value="{{ old('gtpal_preterm', $profile?->gtpal_preterm ?? 0) }}" min="0">
                        </div>
                        <div class="col-md-6" id="outcome_wrap">
                            <label for="gtpal_abortions" class="form-label">Abortions / Miscarriages</label>
                            <input type="number" class="form-control" id="gtpal_abortions" name="gtpal_abortions" value="{{ old('gtpal_abortions', $profile?->gtpal_abortions ?? 0) }}" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="gtpal_living_children" class="form-label">Living Children</label>
                            <input type="number" class="form-control" id="gtpal_living_children" name="gtpal_living_children" value="{{ old('gtpal_living_children', $profile?->gtpal_living_children ?? 0) }}" min="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="preg-card fade-in-card h-100">
                    <div class="preg-label"><i class="bi bi-heart-pulse me-1"></i>Status, Outcome And Assessment</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Pregnancy Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" {{ old('status', $pregnancy->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ old('status', $pregnancy->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="outcome" class="form-label">Outcome</label>
                            <select class="form-select" id="outcome" name="outcome">
                                <option value="">Select outcome</option>
                                @foreach(['delivered' => 'Delivered', 'miscarriage' => 'Miscarriage', 'stillbirth' => 'Stillbirth', 'terminated' => 'Terminated', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('outcome', $profile?->pregnancy_outcome) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12" id="outcome_details_wrap">
                            <label for="outcome_details" class="form-label">Outcome Details</label>
                            <textarea class="form-control" id="outcome_details" name="outcome_details" rows="2">{{ old('outcome_details', $profile?->outcome_details) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="risk_assessment_mode" class="form-label">Risk Assessment</label>
                            <select class="form-select" id="risk_assessment_mode" name="risk_assessment_mode">
                                <option value="automatic" {{ old('risk_assessment_mode', $pregnancy->risk_assessment_mode) === 'automatic' ? 'selected' : '' }}>Automatic</option>
                                <option value="manual" {{ old('risk_assessment_mode', $pregnancy->risk_assessment_mode) === 'manual' ? 'selected' : '' }}>Manual</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="risk_level_wrap">
                            <label for="risk_level" class="form-label">Manual Risk</label>
                            <select class="form-select" id="risk_level" name="risk_level">
                                <option value="Low" {{ old('risk_level', $pregnancy->risk_level) === 'Low' ? 'selected' : '' }}>Low</option>
                                <option value="Medium" {{ old('risk_level', $pregnancy->risk_level) === 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option value="High" {{ old('risk_level', $pregnancy->risk_level) === 'High' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Blood Pressure</label>
                            <div class="row g-2">
                                <div class="col-6"><input type="number" class="form-control" id="bp_systolic" name="bp_systolic" value="{{ old('bp_systolic', $storedSystolic) }}" placeholder="Systolic"></div>
                                <div class="col-6"><input type="number" class="form-control" id="bp_diastolic" name="bp_diastolic" value="{{ old('bp_diastolic', $storedDiastolic) }}" placeholder="Diastolic"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="bp_preview" class="form-label">BP Preview</label>
                            <input type="text" class="form-control" id="bp_preview" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="weight" class="form-label">Weight (kg)</label>
                            <input type="number" step="0.1" class="form-control" id="weight" name="weight" value="{{ old('weight', $pregnancy->healthRecords->first()->weight ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="height" class="form-label">Height (cm)</label>
                            <input type="number" step="0.1" class="form-control" id="height" name="height" value="{{ old('height', $pregnancy->healthRecords->first()->height ?? '') }}">
                            <div class="form-text">Below 122 cm (4 ft) flags short-stature risk.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="bmi_display" class="form-label">BMI</label>
                            <input type="text" class="form-control" id="bmi_display" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">BMI Status</label>
                            <div class="bmi-chip" id="bmi_category">Waiting for weight and height</div>
                        </div>
                        <div class="col-12">
                            <label for="risk_notes" class="form-label">Risk Notes</label>
                            <textarea class="form-control" id="risk_notes" name="risk_notes" rows="2">{{ old('risk_notes', $pregnancy->risk_notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="preg-card fade-in-card">
            <div class="preg-label"><i class="bi bi-clipboard2-pulse me-1"></i>Health Conditions</div>
            <div class="row g-3">
                @foreach($healthConditionOptions as $value => $label)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="health_condition_{{ $value }}" name="health_conditions[]" value="{{ $value }}" {{ in_array($value, old('health_conditions', $profile?->health_conditions ?? []), true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="health_condition_{{ $value }}">{{ $label }}</label>
                        </div>
                    </div>
                @endforeach
                <div class="col-12">
                    <label for="health_condition_other" class="form-label">Other Health Conditions</label>
                    <input type="text" class="form-control" id="health_condition_other" name="health_condition_other" value="{{ old('health_condition_other', $profile?->health_condition_other) }}">
                </div>
            </div>
        </div>

        <div class="preg-card fade-in-card">
            <div class="preg-label"><i class="bi bi-check2-square me-1"></i>Lifestyle And Notes</div>
            <div class="row g-3">
                <div class="col-md-4"><div class="form-check mt-2"><input class="form-check-input" type="checkbox" id="lifestyle_smoking" name="lifestyle_smoking" value="1" {{ old('lifestyle_smoking', in_array(old('smoking_status', $pregnancy->healthRecords->first()->smoking_status ?? $pregnancy->smoking_status ?? 'none'), ['former', 'current'], true) ? 1 : 0) ? 'checked' : '' }}><label class="form-check-label" for="lifestyle_smoking">Smoking</label></div></div>
                <div class="col-md-4"><div class="form-check mt-2"><input class="form-check-input" type="checkbox" id="lifestyle_alcohol" name="lifestyle_alcohol" value="1" {{ old('lifestyle_alcohol', in_array(old('alcohol_status', $pregnancy->healthRecords->first()->alcohol_use ?? 'none'), ['former', 'current'], true) ? 1 : 0) ? 'checked' : '' }}><label class="form-check-label" for="lifestyle_alcohol">Alcohol</label></div></div>
                <div class="col-md-4"><div class="form-check mt-2"><input class="form-check-input" type="checkbox" id="lifestyle_drugs" name="lifestyle_drugs" value="1" {{ old('lifestyle_drugs', in_array(old('drug_use_status', $pregnancy->healthRecords->first()->drug_use ?? 'none'), ['former', 'current'], true) ? 1 : 0) ? 'checked' : '' }}><label class="form-check-label" for="lifestyle_drugs">Drugs</label></div></div>
                <div class="col-md-6">
                    <label for="lifestyle_other" class="form-label">Other Lifestyle Factors</label>
                    <textarea class="form-control" id="lifestyle_other" name="lifestyle_other" rows="3">{{ old('lifestyle_other', $pregnancy->healthRecords->first()->lifestyle_notes ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $pregnancy->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('midwife.pregnancies.show', $pregnancy->id) }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Pregnancy</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const patientTypeRegistered = document.getElementById('patient_type_registered');
    const patientTypeWalkIn = document.getElementById('patient_type_walk_in');
    const womanSelect = document.getElementById('user_id');
    const walkInPatientSelect = document.getElementById('walk_in_patient_id');
    const patientSearch = document.getElementById('patientSearch');
    const registeredSearchWrap = document.getElementById('registeredSearchWrap');
    const registeredSection = document.getElementById('registered_patient_section');
    const registeredMeta = document.getElementById('registered_patient_meta');
    const walkInSection = document.getElementById('walk_in_patient_section');
    const walkInMeta = document.getElementById('walk_in_patient_meta');
    const lmpInput = document.getElementById('lmp');
    const eddInput = document.getElementById('edd');
    const aogDisplay = document.getElementById('aog_display');
    const weightInput = document.getElementById('weight');
    const heightInput = document.getElementById('height');
    const bmiDisplay = document.getElementById('bmi_display');
    const bmiCategory = document.getElementById('bmi_category');
    const paraInput = document.getElementById('para');
    const gtpalTermInput = document.getElementById('gtpal_term');
    const gtpalPretermInput = document.getElementById('gtpal_preterm');
    const bpSystolicInput = document.getElementById('bp_systolic');
    const bpDiastolicInput = document.getElementById('bp_diastolic');
    const bpPreview = document.getElementById('bp_preview');
    const riskModeInput = document.getElementById('risk_assessment_mode');
    const riskLevelWrap = document.getElementById('risk_level_wrap');
    const statusInput = document.getElementById('status');
    const outcomeInput = document.getElementById('outcome');
    const outcomeDetails = document.getElementById('outcome_details');
    const outcomeWrap = document.getElementById('outcome_wrap');
    const outcomeDetailsWrap = document.getElementById('outcome_details_wrap');
    const hiddenPatientType = document.querySelector('input[name="patient_type"]');

    function togglePatientType() {
        const isRegistered = patientTypeRegistered.checked;
        if (hiddenPatientType) hiddenPatientType.value = isRegistered ? 'registered' : 'walk_in';
        registeredSearchWrap?.classList.toggle('d-none', !isRegistered);
        registeredSection?.classList.toggle('d-none', !isRegistered);
        registeredMeta?.classList.toggle('d-none', !isRegistered);
        walkInSection?.classList.toggle('d-none', isRegistered);
        walkInMeta?.classList.toggle('d-none', isRegistered);
        if (womanSelect) womanSelect.required = isRegistered;
        if (walkInPatientSelect) walkInPatientSelect.required = !isRegistered;
    }

    function updateRegisteredPreview() {
        if (!womanSelect || !womanSelect.selectedOptions.length) return;
        const selected = womanSelect.selectedOptions[0];
        document.getElementById('patient_preview_image').src = selected.dataset.image || '/images/avatars/avatar-female.svg';
        document.getElementById('patient_preview_name').textContent = selected.dataset.name || 'No woman selected';
        document.getElementById('patient_preview_subtitle').textContent = selected.dataset.subtitle || '';
        document.getElementById('patient_preview_age').textContent = selected.dataset.age ? `Age: ${selected.dataset.age} years old` : '';
        womanSelect.setCustomValidity(selected.dataset.active === '1' ? 'This woman already has another active pregnancy record.' : '');
    }

    function updateWalkInPreview() {
        if (!walkInPatientSelect || !walkInPatientSelect.selectedOptions.length) return;
        const selected = walkInPatientSelect.selectedOptions[0];
        document.getElementById('walkin_preview_name').textContent = selected.dataset.name || 'No walk-in selected';
        document.getElementById('walkin_preview_subtitle').textContent = selected.dataset.subtitle || '';
        document.getElementById('walkin_preview_age').textContent = selected.dataset.age ? `Age: ${selected.dataset.age} years old` : '';
        walkInPatientSelect.setCustomValidity(selected.dataset.active === '1' ? 'This unlinked woman already has another active pregnancy record.' : '');
    }

    function updateDates() {
        if (!lmpInput || !lmpInput.value) return;
        const lmpDate = new Date(lmpInput.value);
        const now = new Date();
        const eddDate = new Date(lmpDate);
        eddDate.setDate(eddDate.getDate() + 280);
        eddInput.value = eddDate.toISOString().split('T')[0];
        const diffDays = Math.max(0, Math.floor((now - lmpDate) / 86400000));
        const weeks = Math.floor(diffDays / 7);
        const days = diffDays % 7;
        aogDisplay.value = `${weeks} weeks ${days} days`;
    }

    function updateBmi() {
        const weight = parseFloat(weightInput?.value || '');
        const height = parseFloat(heightInput?.value || '');
        if (!weight || !height) {
            bmiDisplay.value = '';
            bmiCategory.textContent = 'Waiting for weight and height';
            return;
        }
        const bmi = weight / Math.pow(height / 100, 2);
        bmiDisplay.value = bmi.toFixed(2);
        let label = 'Normal';
        if (bmi < 18.5) label = 'Malnourished';
        else if (bmi >= 30) label = 'Obese';
        bmiCategory.textContent = `${bmi.toFixed(2)} BMI - ${label}`;
    }

    function syncParaFromGtpal() {
        const term = parseInt(gtpalTermInput?.value || '', 10);
        const preterm = parseInt(gtpalPretermInput?.value || '', 10);
        if (Number.isNaN(term) && Number.isNaN(preterm)) return;
        paraInput.value = (Number.isNaN(term) ? 0 : term) + (Number.isNaN(preterm) ? 0 : preterm);
    }

    function updateBloodPressure() {
        const systolic = parseInt(bpSystolicInput?.value || '', 10);
        const diastolic = parseInt(bpDiastolicInput?.value || '', 10);
        bpPreview.value = (!Number.isNaN(systolic) && !Number.isNaN(diastolic)) ? `${systolic}/${diastolic}` : '';
    }

    function toggleRiskMode() {
        if (!riskModeInput || !riskLevelWrap) return;
        riskLevelWrap.style.display = riskModeInput.value === 'manual' ? '' : 'none';
    }

    function toggleOutcome() {
        const isCompleted = statusInput?.value === 'completed';
        outcomeWrap?.classList.toggle('d-none', !isCompleted);
        outcomeDetailsWrap?.classList.toggle('d-none', !isCompleted);
        if (outcomeInput) outcomeInput.disabled = !isCompleted;
        if (outcomeDetails) outcomeDetails.disabled = !isCompleted;
        if (!isCompleted) {
            if (outcomeInput) outcomeInput.value = '';
            if (outcomeDetails) outcomeDetails.value = '';
        }
    }

    if (patientSearch && womanSelect) {
        patientSearch.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            womanSelect.querySelectorAll('option').forEach(function (option) {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }
                option.hidden = !(option.dataset.search || '').includes(searchTerm);
            });
        });
    }

    patientTypeRegistered?.addEventListener('change', togglePatientType);
    patientTypeWalkIn?.addEventListener('change', togglePatientType);
    womanSelect?.addEventListener('change', updateRegisteredPreview);
    walkInPatientSelect?.addEventListener('change', updateWalkInPreview);
    lmpInput?.addEventListener('change', updateDates);
    weightInput?.addEventListener('input', updateBmi);
    heightInput?.addEventListener('input', updateBmi);
    gtpalTermInput?.addEventListener('input', syncParaFromGtpal);
    gtpalPretermInput?.addEventListener('input', syncParaFromGtpal);
    bpSystolicInput?.addEventListener('input', updateBloodPressure);
    bpDiastolicInput?.addEventListener('input', updateBloodPressure);
    riskModeInput?.addEventListener('change', toggleRiskMode);
    statusInput?.addEventListener('change', toggleOutcome);

    togglePatientType();
    updateRegisteredPreview();
    updateWalkInPreview();
    updateDates();
    updateBmi();
    updateBloodPressure();
    toggleRiskMode();
    toggleOutcome();
</script>
@endpush
@endsection
