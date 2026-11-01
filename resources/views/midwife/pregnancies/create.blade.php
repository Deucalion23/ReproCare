@extends('midwife.layout')

@section('title', 'Add Pregnancy - ReproCare')

@push('styles')
    @include('midwife.partials.pregnancy-form-styles')
@endpush

@section('midwife-content')
<div class="py-3">
    <div class="page-hero fade-in-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2" style="position:relative;z-index:1;">
            <div>
                <h1 class="page-hero-title">Add Pregnancy</h1>
                <p class="page-hero-subtitle">Create a new active pregnancy record.</p>
            </div>
            <a href="{{ route('midwife.pregnancies.index') }}" class="btn-hero-primary">
                <i class="bi bi-arrow-left"></i> Back to Pregnancies
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 fade-in-card">
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('midwife.pregnancies.store') }}" method="POST" id="pregnancyForm" class="pf pf-grid">
        @csrf

        <div class="pf-card fade-in-card">
            <div class="pf-title"><i class="bi bi-person-vcard me-1"></i>Patient Information</div>
            @if(isset($woman) && $woman)
                <input type="hidden" name="patient_type" value="registered">
                <input type="hidden" name="user_id" value="{{ $woman->id }}">
                <div class="pf-identity">
                    <img src="{{ $woman->profile_image_url }}" alt="{{ $woman->name }}" class="pf-avatar">
                    <div class="min-w-0">
                        <div class="fw-bold">{{ $woman->name }}</div>
                        <div class="text-muted small">{{ $woman->email }}</div>
                        <div class="text-muted small">Age: {{ $woman->age ? $woman->age . ' years old' : 'N/A' }}</div>
                    </div>
                </div>
            @else
                <div class="row g-2">
                    <div class="col-12 col-lg-4">
                        <div id="registered_patient_meta" class="pf-identity">
                            <img src="/images/avatars/avatar-female.svg" id="patient_preview_image" alt="Preview" class="pf-avatar">
                            <div class="min-w-0">
                                <div id="patient_preview_name" class="fw-bold">No woman selected</div>
                                <div id="patient_preview_subtitle" class="text-muted small">Choose an enrolled woman</div>
                                <div id="patient_preview_age" class="text-muted small"></div>
                            </div>
                        </div>
                        <div id="walk_in_patient_meta" class="pf-identity d-none">
                            <img src="/images/avatars/avatar-female.svg" id="walkin_preview_image" alt="Preview" class="pf-avatar">
                            <div class="min-w-0">
                                <div id="walkin_preview_name" class="fw-bold">No walk-in selected</div>
                                <div id="walkin_preview_subtitle" class="text-muted small">Choose an unlinked woman</div>
                                <div id="walkin_preview_age" class="text-muted small"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-2">
                        <label class="form-label">Patient Type</label>
                        <div class="d-flex flex-column gap-2 align-items-start">
                            <div class="d-flex gap-2">
                                <input type="radio" class="btn-check" name="patient_type" id="patient_type_registered" value="registered" {{ old('patient_type', 'registered') === 'registered' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="patient_type_registered">Enrolled</label>
                                <input type="radio" class="btn-check" name="patient_type" id="patient_type_walk_in" value="walk_in" {{ old('patient_type') === 'walk_in' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="patient_type_walk_in">Unlinked</label>
                            </div>
                            <div class="form-text">Pick the woman who owns this record.</div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        {{-- One field per branch: type to filter and the list drops
                             down under it. The select stays in the DOM as the value
                             that gets submitted, hidden once rc-combobox.js is ready. --}}
                        <div id="registeredPatientWrap" class="mb-2" data-combo>
                            <label for="patientSearch" class="form-label">Enrolled woman</label>
                            @include('includes.patient-combobox', [
                                'cbInput' => 'patientSearch',
                                'cbPlaceholder' => 'Type to search by name or email',
                                'cbRequired' => true,
                                'cbInvalid' => $errors->has('user_id'),
                            ])
                            <select class="rc-combo-store" id="user_id" name="user_id">
                                <option value="">Select woman</option>
                                @foreach($women as $listedWoman)
                                    <option value="{{ $listedWoman->id }}"
                                        data-age="{{ $listedWoman->age }}"
                                        data-dob="{{ optional($listedWoman->date_of_birth)->format('Y-m-d') }}"
                                        data-active="{{ $listedWoman->pregnancies->isNotEmpty() ? '1' : '0' }}"
                                        data-image="{{ $listedWoman->profile_image_url }}"
                                        data-subtitle="{{ $listedWoman->email }}"
                                        data-name="{{ $listedWoman->name }}"
                                        data-search="{{ strtolower($listedWoman->name . ' ' . $listedWoman->email) }}"
                                        {{ old('user_id') == $listedWoman->id ? 'selected' : '' }}>
                                        {{ $listedWoman->name }} - {{ $listedWoman->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<div class="rc-combo-error">{{ $message }}</div>@enderror
                        </div>
                        <div id="walkInPatientWrap" class="mb-2 d-none" data-combo>
                            <label for="walkInSearch" class="form-label">Unlinked woman</label>
                            {{-- No `required` in markup: this branch starts hidden and a
                               hidden required control blocks the entire submit.
                               togglePatientType() switches it on with the branch. --}}
                            @include('includes.patient-combobox', [
                                'cbInput' => 'walkInSearch',
                                'cbPlaceholder' => 'Type to search by name or contact',
                                'cbInvalid' => $errors->has('walk_in_patient_id'),
                            ])
                            <select class="rc-combo-store" id="walk_in_patient_id" name="walk_in_patient_id">
                                <option value="">Select unlinked woman</option>
                                @foreach($walkInPatients as $walkInPatient)
                                    <option value="{{ $walkInPatient->id }}"
                                        data-age="{{ $walkInPatient->age }}"
                                        data-dob="{{ optional($walkInPatient->date_of_birth)->format('Y-m-d') }}"
                                        data-active="{{ $walkInPatient->pregnancies->isNotEmpty() ? '1' : '0' }}"
                                        data-name="{{ $walkInPatient->full_name }}"
                                        data-subtitle="{{ $walkInPatient->contact_number ?? 'No contact number' }}"
                                        data-search="{{ strtolower($walkInPatient->full_name . ' ' . ($walkInPatient->contact_number ?? '') . ' ' . ($walkInPatient->barangay ?? '')) }}"
                                        {{ old('walk_in_patient_id') == $walkInPatient->id ? 'selected' : '' }}>
                                        {{ $walkInPatient->full_name }} - {{ $walkInPatient->contact_number ?? 'No contact number' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('walk_in_patient_id')<div class="rc-combo-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-xl-7">
                <div class="pf-card fade-in-card h-100">
                    <div class="pf-title"><i class="bi bi-calendar-heart me-1"></i>Pregnancy Timeline</div>
                    <div class="row g-2">
                        <div class="col-6 col-lg-4">
                            <label for="lmp" class="form-label">LMP</label>
                            <input type="date" class="form-control @error('lmp') is-invalid @enderror" id="lmp" name="lmp" value="{{ old('lmp') }}" required>
                            @error('lmp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6 col-lg-4">
                            <label for="edd" class="form-label">EDD</label>
                            <input type="date" class="form-control" id="edd" readonly>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label for="aog_display" class="form-label">AOG</label>
                            <input type="text" class="form-control" id="aog_display" readonly>
                            <input type="hidden" id="aog" name="aog" value="{{ old('aog') }}">
                        </div>
                        <div class="col-12">
                            <div class="pf-sub">Obstetric History (GTPAL)</div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label for="gravida" class="form-label">Gravida</label>
                            <input type="number" class="form-control" id="gravida" name="gravida" value="{{ old('gravida', $suggested['gravida'] ?? null) }}" min="1">
                            @if(!empty($suggested) && ($suggested['gravida'] ?? 1) > 1)
                                <small class="form-text text-muted">Carried over (G{{ $suggested['gravida'] }})</small>
                            @endif
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label for="para" class="form-label">Para</label>
                            <input type="number" class="form-control" id="para" name="para" value="{{ old('para', $suggested['para'] ?? null) }}" min="0">
                            @if(!empty($suggested) && ($suggested['para'] ?? 0) > 0)
                                <small class="form-text text-muted">Carried over (P{{ $suggested['para'] }})</small>
                            @endif
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label for="gtpal_term" class="form-label">Term</label>
                            <input type="number" class="form-control" id="gtpal_term" name="gtpal_term" value="{{ old('gtpal_term', 0) }}" min="0">
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label for="gtpal_preterm" class="form-label">Preterm</label>
                            <input type="number" class="form-control" id="gtpal_preterm" name="gtpal_preterm" value="{{ old('gtpal_preterm', 0) }}" min="0">
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label for="gtpal_abortions" class="form-label">Abortions</label>
                            <input type="number" class="form-control" id="gtpal_abortions" name="gtpal_abortions" value="{{ old('gtpal_abortions', 0) }}" min="0">
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <label for="gtpal_living_children" class="form-label">Living Kids</label>
                            <input type="number" class="form-control" id="gtpal_living_children" name="gtpal_living_children" value="{{ old('gtpal_living_children', 0) }}" min="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="pf-card fade-in-card h-100">
                    <div class="pf-title"><i class="bi bi-heart-pulse me-1"></i>Risk Assessment</div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="risk_assessment_mode" class="form-label">Mode</label>
                            <select class="form-select" id="risk_assessment_mode" name="risk_assessment_mode">
                                <option value="automatic" {{ old('risk_assessment_mode', 'automatic') === 'automatic' ? 'selected' : '' }}>Automatic</option>
                                <option value="manual" {{ old('risk_assessment_mode') === 'manual' ? 'selected' : '' }}>Manual</option>
                            </select>
                        </div>
                        <div class="col-6" id="risk_level_wrap">
                            <label for="risk_level" class="form-label">Manual Risk</label>
                            <select class="form-select" id="risk_level" name="risk_level">
                                <option value="Low" {{ old('risk_level') === 'Low' ? 'selected' : '' }}>Low</option>
                                <option value="Medium" {{ old('risk_level') === 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option value="High" {{ old('risk_level') === 'High' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="risk_notes" class="form-label">Risk Notes</label>
                            <textarea class="form-control" id="risk_notes" name="risk_notes" rows="3">{{ old('risk_notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pf-card fade-in-card">
            <div class="pf-title"><i class="bi bi-clipboard2-pulse me-1"></i>Vitals And Nutrition</div>
            <div class="row g-2">
                <div class="col-6 col-lg-2">
                    <label for="bp_systolic" class="form-label">BP Systolic</label>
                    <input type="number" class="form-control" id="bp_systolic" name="bp_systolic" value="{{ old('bp_systolic') }}" placeholder="Systolic">
                </div>
                <div class="col-6 col-lg-2">
                    <label for="bp_diastolic" class="form-label">BP Diastolic</label>
                    <input type="number" class="form-control" id="bp_diastolic" name="bp_diastolic" value="{{ old('bp_diastolic') }}" placeholder="Diastolic">
                </div>
                <div class="col-6 col-lg-2">
                    <label for="bp_preview" class="form-label">BP Preview</label>
                    <input type="text" class="form-control" id="bp_preview" readonly>
                </div>
                <div class="col-6 col-lg-2">
                    <label for="weight" class="form-label">Weight (kg)</label>
                    <input type="number" step="0.1" class="form-control" id="weight" name="weight" value="{{ old('weight') }}">
                </div>
                <div class="col-6 col-lg-2">
                    <label for="height" class="form-label">Height (cm)</label>
                    <input type="number" step="0.1" class="form-control" id="height" name="height" value="{{ old('height') }}">
                </div>
                <div class="col-6 col-lg-2">
                    <label for="bmi_display" class="form-label">BMI</label>
                    <input type="text" class="form-control" id="bmi_display" readonly>
                </div>
                <div class="col-12 col-lg-6">
                    <label class="form-label">BMI Status</label>
                    <div class="pf-chip" id="bmi_category">Waiting for weight and height</div>
                </div>
                <div class="col-12 col-lg-6 d-flex align-items-end">
                    <div class="form-text">Height below 122 cm (4 ft) flags short-stature risk. Vitals here sync to the pregnancy's paired health record.</div>
                </div>
            </div>
        </div>

        <div class="pf-card fade-in-card">
            <div class="pf-title"><i class="bi bi-clipboard2-pulse me-1"></i>Health Conditions</div>
            <div class="row g-2">
                @foreach($healthConditionOptions as $value => $label)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="health_condition_{{ $value }}" name="health_conditions[]" value="{{ $value }}" {{ in_array($value, old('health_conditions', []), true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="health_condition_{{ $value }}">{{ $label }}</label>
                        </div>
                    </div>
                @endforeach
                <div class="col-12">
                    <label for="health_condition_other" class="form-label">Other Health Conditions</label>
                    <input type="text" class="form-control" id="health_condition_other" name="health_condition_other" value="{{ old('health_condition_other') }}">
                </div>
            </div>
        </div>

        <div class="pf-card fade-in-card">
            <div class="pf-title"><i class="bi bi-check2-square me-1"></i>Lifestyle And Notes</div>
            <div class="row g-2">
                <div class="col-12">
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="lifestyle_smoking" name="lifestyle_smoking" value="1" {{ old('lifestyle_smoking') || in_array(old('smoking_status'), ['former', 'current'], true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lifestyle_smoking">Smoking</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="lifestyle_alcohol" name="lifestyle_alcohol" value="1" {{ old('lifestyle_alcohol') || in_array(old('alcohol_status'), ['former', 'current'], true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lifestyle_alcohol">Alcohol</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="lifestyle_drugs" name="lifestyle_drugs" value="1" {{ old('lifestyle_drugs') || in_array(old('drug_use_status'), ['former', 'current'], true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lifestyle_drugs">Drugs</label>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <label for="lifestyle_other" class="form-label">Other Lifestyle Factors</label>
                    <textarea class="form-control" id="lifestyle_other" name="lifestyle_other" rows="2">{{ old('lifestyle_other', old('lifestyle_notes')) }}</textarea>
                </div>
                <div class="col-12 col-lg-6">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="pf-actions">
            <a href="{{ route('midwife.pregnancies.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm">Save Pregnancy</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const womanSelect = document.getElementById('user_id');
    const walkInPatientSelect = document.getElementById('walk_in_patient_id');
    const patientSearch = document.getElementById('patientSearch');
    const walkInSearch = document.getElementById('walkInSearch');
    const patientTypeRegistered = document.getElementById('patient_type_registered');
    const patientTypeWalkIn = document.getElementById('patient_type_walk_in');
    const registeredPatientWrap = document.getElementById('registeredPatientWrap');
    const registeredMeta = document.getElementById('registered_patient_meta');
    const walkInPatientWrap = document.getElementById('walkInPatientWrap');
    const walkInMeta = document.getElementById('walk_in_patient_meta');
    const lmpInput = document.getElementById('lmp');
    const eddInput = document.getElementById('edd');
    const aogInput = document.getElementById('aog');
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

    /* Filtering and picking live in rc-combobox.js. This only swaps which branch is
       in play. `required` sits on the visible input because Chrome refuses to
       validate the hidden store select and would block the submit silently. */
    function togglePatientType() {
        if (!patientTypeRegistered || !patientTypeWalkIn) return;
        const isRegistered = patientTypeRegistered.checked;
        registeredPatientWrap?.classList.toggle('d-none', !isRegistered);
        walkInPatientWrap?.classList.toggle('d-none', isRegistered);
        registeredMeta?.classList.toggle('d-none', !isRegistered);
        walkInMeta?.classList.toggle('d-none', isRegistered);
        if (patientSearch) {
            patientSearch.disabled = !isRegistered;
            patientSearch.required = isRegistered;
        }
        if (walkInSearch) {
            walkInSearch.disabled = isRegistered;
            walkInSearch.required = !isRegistered;
        }
        // Disabled stores are left out of the POST, so only one key is sent.
        if (womanSelect) womanSelect.disabled = !isRegistered;
        if (walkInPatientSelect) walkInPatientSelect.disabled = isRegistered;
    }

    function updateRegisteredPreview() {
        if (!womanSelect || !womanSelect.selectedOptions.length) return;
        const selected = womanSelect.selectedOptions[0];
        const image = document.getElementById('patient_preview_image');
        const name = document.getElementById('patient_preview_name');
        const subtitle = document.getElementById('patient_preview_subtitle');
        const age = document.getElementById('patient_preview_age');
        if (image) image.src = selected.dataset.image || '/images/avatars/avatar-female.svg';
        if (name) name.textContent = selected.dataset.name || 'No woman selected';
        if (subtitle) subtitle.textContent = selected.dataset.subtitle || '';
        if (age) age.textContent = selected.dataset.age ? `Age: ${selected.dataset.age} years old` : '';
        // The warning has to land on the visible input; the store select is hidden.
        if (patientSearch) {
            patientSearch.setCustomValidity(selected.dataset.active === '1' ? 'This woman already has an active pregnancy record.' : '');
        }
    }

    function updateWalkInPreview() {
        if (!walkInPatientSelect || !walkInPatientSelect.selectedOptions.length) return;
        const selected = walkInPatientSelect.selectedOptions[0];
        const name = document.getElementById('walkin_preview_name');
        const subtitle = document.getElementById('walkin_preview_subtitle');
        const age = document.getElementById('walkin_preview_age');
        if (name) name.textContent = selected.dataset.name || 'No walk-in selected';
        if (subtitle) subtitle.textContent = selected.dataset.subtitle || '';
        if (age) age.textContent = selected.dataset.age ? `Age: ${selected.dataset.age} years old` : '';
        if (walkInSearch) {
            walkInSearch.setCustomValidity(selected.dataset.active === '1' ? 'This unlinked woman already has an active pregnancy record.' : '');
        }
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
        aogInput.value = weeks;
        aogDisplay.value = `${weeks} weeks ${days} days`;
    }

    function updateBmi() {
        const weight = parseFloat(weightInput?.value || '');
        const height = parseFloat(heightInput?.value || '');
        if (!weight || !height) {
            if (bmiDisplay) bmiDisplay.value = '';
            if (bmiCategory) bmiCategory.textContent = 'Waiting for weight and height';
            return;
        }

        const bmi = weight / Math.pow(height / 100, 2);
        if (bmiDisplay) bmiDisplay.value = bmi.toFixed(2);

        let label = 'Normal';
        if (bmi < 18.5) {
            label = 'Malnourished';
        } else if (bmi >= 30) {
            label = 'Obese';
        }

        if (bmiCategory) bmiCategory.textContent = `${bmi.toFixed(2)} BMI - ${label}`;
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

    togglePatientType();
    updateRegisteredPreview();
    updateWalkInPreview();
    updateDates();
    updateBmi();
    updateBloodPressure();
    toggleRiskMode();
</script>
@endpush
@endsection
