@extends('midwife.layout')

@section('title', 'Edit Maternal Care Target Client | ReproCare')

@push('styles')
<style>
    .mctl-edit-shell { display:grid; gap:1.25rem; }
    .mctl-panel {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:22px;
        box-shadow:var(--shadow-sm);
        padding:1.4rem;
    }
    .mctl-grid { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:1rem; }
    .mctl-grid-3 { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:1rem; }
    .mctl-section-title { font-family:'Plus Jakarta Sans', sans-serif; font-size:1.05rem; font-weight:800; color:var(--text); margin-bottom:1rem; }
    .mctl-summary { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:1rem; }
    .mctl-summary-item { background:var(--bg-card2); border:1px solid var(--border); border-radius:16px; padding:1rem; }
    .mctl-summary-label { font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em; }
    .mctl-summary-value { margin-top:0.2rem; color:var(--text); font-weight:700; }
    @media (max-width: 992px) {
        .mctl-grid, .mctl-grid-3, .mctl-summary { grid-template-columns:1fr 1fr; }
    }
    @media (max-width: 640px) {
        .mctl-grid, .mctl-grid-3, .mctl-summary { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('midwife-content')
@php
    $profile = $profile ?? null;
@endphp
<div class="mctl-edit-shell">
    <div class="mctl-panel">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h1 class="mctl-section-title mb-2">Target Client List Record</h1>
                <p class="text-muted mb-0">Update the maternal care tracking fields for {{ $woman->name }}.</p>
            </div>
            <a href="{{ route('midwife.maternal-care-target-clients.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <div class="mctl-summary mt-4">
            <div class="mctl-summary-item">
                <div class="mctl-summary-label">Client</div>
                <div class="mctl-summary-value">{{ $woman->name }}</div>
            </div>
            <div class="mctl-summary-item">
                <div class="mctl-summary-label">Address</div>
                <div class="mctl-summary-value">{{ $woman->address ?? '—' }}</div>
            </div>
            <div class="mctl-summary-item">
                <div class="mctl-summary-label">Barangay / Purok</div>
                <div class="mctl-summary-value">{{ $woman->barangay ?? '—' }}{{ $woman->purok ? ' / ' . $woman->purok->name : '' }}</div>
            </div>
            <div class="mctl-summary-item">
                <div class="mctl-summary-label">LMP / EDC</div>
                <div class="mctl-summary-value">
                    {{ optional($pregnancy?->lmp)->format('m/d/Y') ?? '—' }}
                    /
                    {{ optional($pregnancy?->edd)->format('m/d/Y') ?? '—' }}
                </div>
            </div>
            <div class="mctl-summary-item">
                <div class="mctl-summary-label">Gravida / Parity</div>
                <div class="mctl-summary-value">
                    {{ $profile?->gravida ?? '—' }}
                    /
                    {{ $profile?->parity ?? '—' }}
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('midwife.maternal-care-target-clients.update', [$woman, $pregnancy?->id]) }}">
        @csrf
        @method('PUT')

        <div class="mctl-panel">
            <div class="mctl-section-title">Registration Information</div>
            <div class="mctl-grid">
                <div>
                    <label class="form-label">Date of Registration</label>
                    <input type="date" name="date_of_registration" value="{{ old('date_of_registration', optional($profile?->date_of_registration)->format('Y-m-d')) }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Family Serial No.</label>
                    <input type="text" name="family_serial_no" value="{{ old('family_serial_no', $profile?->family_serial_no) }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Gravida (G)</label>
                    <input type="number" name="gravida" value="{{ old('gravida', $profile?->gravida) }}" class="form-control" min="0">
                </div>
                <div>
                    <label class="form-label">Parity (P)</label>
                    <input type="number" name="parity" value="{{ old('parity', $profile?->parity) }}" class="form-control" min="0">
                </div>
            </div>
        </div>

        <div class="mctl-panel">
            <div class="mctl-section-title">GTPAL Information</div>
            <div class="mctl-grid">
                <div>
                    <label class="form-label">Term</label>
                    <input type="number" name="gtpal_term" value="{{ old('gtpal_term', $profile?->gtpal_term) }}" class="form-control" min="0">
                </div>
                <div>
                    <label class="form-label">Preterm</label>
                    <input type="number" name="gtpal_preterm" value="{{ old('gtpal_preterm', $profile?->gtpal_preterm) }}" class="form-control" min="0">
                </div>
                <div>
                    <label class="form-label">Abortions</label>
                    <input type="number" name="gtpal_abortions" value="{{ old('gtpal_abortions', $profile?->gtpal_abortions) }}" class="form-control" min="0">
                </div>
                <div>
                    <label class="form-label">Living Children</label>
                    <input type="number" name="gtpal_living_children" value="{{ old('gtpal_living_children', $profile?->gtpal_living_children) }}" class="form-control" min="0">
                </div>
            </div>
        </div>

        <div class="mctl-panel">
            <div class="mctl-section-title">Health Assessment</div>
            <div class="mctl-grid">
                <div>
                    <label class="form-label">Nutritional Assessment</label>
                    <select name="nutritional_assessment_status" class="form-select">
                        <option value="">Select...</option>
                        <option value="low" {{ old('nutritional_assessment_status', $profile?->nutritional_assessment_status) === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="normal" {{ old('nutritional_assessment_status', $profile?->nutritional_assessment_status) === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ old('nutritional_assessment_status', $profile?->nutritional_assessment_status) === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Deworming Date</label>
                    <input type="date" name="deworming_date" value="{{ old('deworming_date', optional($profile?->deworming_date)->format('Y-m-d')) }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Health Conditions</label>
                    <input type="text" name="health_conditions" value="{{ old('health_conditions', is_array($profile?->health_conditions) ? json_encode($profile->health_conditions) : $profile?->health_conditions) }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Other Health Condition</label>
                    <input type="text" name="health_condition_other" value="{{ old('health_condition_other', $profile?->health_condition_other) }}" class="form-control">
                </div>
                <div class="d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="fim_status" name="fim_status" value="1" {{ old('fim_status', $profile?->fim_status) ? 'checked' : '' }}>
                        <label class="form-check-label" for="fim_status">FIM Status Complete</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mctl-panel">
            <div class="mctl-section-title">Pregnancy Outcome</div>
            <div class="mctl-grid">
                <div>
                    <label class="form-label">Pregnancy Terminated Date</label>
                    <input type="date" name="pregnancy_terminated_date" value="{{ old('pregnancy_terminated_date', optional($profile?->pregnancy_terminated_date)->format('Y-m-d')) }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Pregnancy Outcome</label>
                    <select name="pregnancy_outcome" class="form-select">
                        <option value="">Select...</option>
                        <option value="ft" {{ old('pregnancy_outcome', $profile?->pregnancy_outcome) === 'ft' ? 'selected' : '' }}>FT - Full Term</option>
                        <option value="pt" {{ old('pregnancy_outcome', $profile?->pregnancy_outcome) === 'pt' ? 'selected' : '' }}>PT - Pre-term</option>
                        <option value="fd" {{ old('pregnancy_outcome', $profile?->pregnancy_outcome) === 'fd' ? 'selected' : '' }}>FD - Fetal Death</option>
                        <option value="ab" {{ old('pregnancy_outcome', $profile?->pregnancy_outcome) === 'ab' ? 'selected' : '' }}>AB - Abortion/Miscarriage</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Outcome Details</label>
                    <input type="text" name="outcome_details" value="{{ old('outcome_details', $profile?->outcome_details) }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Pregnancy Outcome Sex</label>
                    <select name="pregnancy_outcome_sex" class="form-select">
                        <option value="">Select...</option>
                        <option value="M" {{ old('pregnancy_outcome_sex', $profile?->pregnancy_outcome_sex) === 'M' ? 'selected' : '' }}>Male</option>
                        <option value="F" {{ old('pregnancy_outcome_sex', $profile?->pregnancy_outcome_sex) === 'F' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mctl-panel">
            <div class="mctl-section-title">Delivery Information</div>
            <div class="mctl-grid">
                <div>
                    <label class="form-label">Delivery Type</label>
                    <select name="delivery_type" class="form-select">
                        <option value="">Select...</option>
                        <option value="cs" {{ old('delivery_type', $profile?->delivery_type) === 'cs' ? 'selected' : '' }}>CS - Cesarean Section</option>
                        <option value="vd" {{ old('delivery_type', $profile?->delivery_type) === 'vd' ? 'selected' : '' }}>VD - Vaginal Delivery</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Birth Weight Category</label>
                    <select name="birth_weight_category" class="form-select">
                        <option value="">Select...</option>
                        <option value="low" {{ old('birth_weight_category', $profile?->birth_weight_category) === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="normal" {{ old('birth_weight_category', $profile?->birth_weight_category) === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="unknown" {{ old('birth_weight_category', $profile?->birth_weight_category) === 'unknown' ? 'selected' : '' }}>Unknown</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Delivery Date</label>
                    <input type="date" name="delivery_date" value="{{ old('delivery_date', optional($profile?->delivery_date)->format('Y-m-d')) }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Delivery Time</label>
                    <input type="time" name="delivery_time" value="{{ old('delivery_time', $profile?->delivery_time) }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="mctl-panel">
            <div class="mctl-section-title">Facility Information</div>
            <div class="mctl-grid">
                <div>
                    <label class="form-label">Facility Delivery Type</label>
                    <input type="text" name="facility_delivery_type" value="{{ old('facility_delivery_type', $profile?->facility_delivery_type) }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Facility BEMONC Capable</label>
                    <input type="text" name="facility_bemonc_capable" value="{{ old('facility_bemonc_capable', $profile?->facility_bemonc_capable) }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Facility Ownership</label>
                    <select name="facility_ownership" class="form-select">
                        <option value="">Select...</option>
                        <option value="public" {{ old('facility_ownership', $profile?->facility_ownership) === 'public' ? 'selected' : '' }}>Public</option>
                        <option value="private" {{ old('facility_ownership', $profile?->facility_ownership) === 'private' ? 'selected' : '' }}>Private</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Non-Health Facility Code</label>
                    <input type="text" name="non_health_facility_code" value="{{ old('non_health_facility_code', $profile?->non_health_facility_code) }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Birth Attendant Code</label>
                    <input type="text" name="birth_attendant_code" value="{{ old('birth_attendant_code', $profile?->birth_attendant_code) }}" class="form-control">
                </div>
                <div>
                    <label class="form-label">Delivery Remarks</label>
                    <textarea name="delivery_remarks" class="form-control" rows="2">{{ old('delivery_remarks', $profile?->delivery_remarks) }}</textarea>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Save Maternal Record
            </button>
            <a href="{{ route('midwife.maternal-care-target-clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
