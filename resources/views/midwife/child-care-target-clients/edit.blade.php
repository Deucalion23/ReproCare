@extends('midwife.layout')

@section('title', 'Edit Child Care Target Client | ReproCare')

@push('styles')
<style>
    .cctl-edit-shell { display:grid; gap:1.25rem; }
    .cctl-panel { background:var(--bg-card); border:1px solid var(--border); border-radius:22px; box-shadow:var(--shadow-sm); padding:1.4rem; }
    .cctl-grid { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:1rem; }
    .cctl-summary { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:1rem; }
    .cctl-summary-item { background:var(--bg-card2); border:1px solid var(--border); border-radius:16px; padding:1rem; }
    .cctl-summary-label { font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em; }
    .cctl-summary-value { margin-top:0.2rem; color:var(--text); font-weight:700; }
    .cctl-section-title { font-family:'Plus Jakarta Sans', sans-serif; font-size:1.05rem; font-weight:800; color:var(--text); margin-bottom:1rem; }
    @media (max-width: 992px) { .cctl-grid, .cctl-summary { grid-template-columns:1fr 1fr; } }
    @media (max-width: 640px) { .cctl-grid, .cctl-summary { grid-template-columns:1fr; } }
</style>
@endpush

@section('midwife-content')
<div class="cctl-edit-shell">
    <div class="cctl-panel">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h1 class="cctl-section-title mb-2">Child Care Target Client Record</h1>
                <p class="text-muted mb-0">Update the 0-12 month child care service tracking fields for {{ $child->full_name }}.</p>
            </div>
            <a href="{{ route('midwife.child-care-target-clients.index') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
        </div>
        <div class="cctl-summary mt-4">
            <div class="cctl-summary-item"><div class="cctl-summary-label">Child</div><div class="cctl-summary-value">{{ $child->full_name }}</div></div>
            <div class="cctl-summary-item"><div class="cctl-summary-label">Mother</div><div class="cctl-summary-value">{{ $child->mother?->name ?? '—' }}</div></div>
            <div class="cctl-summary-item"><div class="cctl-summary-label">Date of Birth</div><div class="cctl-summary-value">{{ optional($child->date_of_birth)->format('m/d/Y') ?? '—' }}</div></div>
            <div class="cctl-summary-item"><div class="cctl-summary-label">Barangay / Purok</div><div class="cctl-summary-value">{{ $child->barangay ?? '—' }}{{ $child->purok ? ' / ' . $child->purok->name : '' }}</div></div>
        </div>
    </div>

    <form method="POST" action="{{ route('midwife.child-care-target-clients.update', $child) }}">
        @csrf
        @method('PUT')

        <div class="cctl-panel">
            <div class="cctl-section-title">Page 1/4: Registration And CPAB</div>
            <div class="cctl-grid">
                <div><label class="form-label">Date of Registration</label><input type="date" name="date_of_registration" value="{{ old('date_of_registration', optional($profile?->date_of_registration)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">Family Serial Number</label><input type="text" name="family_serial_number" value="{{ old('family_serial_number', $profile?->family_serial_number) }}" class="form-control"></div>
                <div class="d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="cpab_tt2_tt4" name="cpab_tt2_tt4" value="1" {{ old('cpab_tt2_tt4', $profile?->cpab_tt2_tt4) ? 'checked' : '' }}><label class="form-check-label" for="cpab_tt2_tt4">TT2/Td2 to TT4/Td4</label></div></div>
                <div class="d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="cpab_tt3_tt5" name="cpab_tt3_tt5" value="1" {{ old('cpab_tt3_tt5', $profile?->cpab_tt3_tt5) ? 'checked' : '' }}><label class="form-check-label" for="cpab_tt3_tt5">TT3/Td3 to TT5/Td5</label></div></div>
            </div>
        </div>

        <div class="cctl-panel">
            <div class="cctl-section-title">Page 2/4: Newborn And 1-3 Months</div>
            <div class="cctl-grid">
                <div>
                    <label class="form-label">Newborn Status</label>
                    <select name="newborn_status" class="form-select">
                        <option value="">Select...</option>
                        <option value="low" {{ old('newborn_status', $profile?->newborn_status) === 'low' ? 'selected' : '' }}>Low birth weight</option>
                        <option value="normal" {{ old('newborn_status', $profile?->newborn_status) === 'normal' ? 'selected' : '' }}>Normal birth weight</option>
                        <option value="unknown" {{ old('newborn_status', $profile?->newborn_status) === 'unknown' ? 'selected' : '' }}>Unknown</option>
                    </select>
                </div>
                <div><label class="form-label">Initiated breastfeeding within 90 minutes</label><input type="date" name="breastfeeding_initiated_date" value="{{ old('breastfeeding_initiated_date', optional($profile?->breastfeeding_initiated_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">BCG Date</label><input type="date" name="bcg_date" value="{{ old('bcg_date', optional($profile?->bcg_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">Hepa B-BD Date</label><input type="date" name="hepa_b_bd_date" value="{{ old('hepa_b_bd_date', optional($profile?->hepa_b_bd_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">1-3 Months Age Label</label><input type="text" name="assessment_1_3_age_months" value="{{ old('assessment_1_3_age_months', $profile?->assessment_1_3_age_months) }}" class="form-control" placeholder="e.g. 1 1/2 mos."></div>
                <div><label class="form-label">1-3 Length (cm)</label><input type="number" step="0.01" min="0" name="assessment_1_3_length_cm" value="{{ old('assessment_1_3_length_cm', $profile?->assessment_1_3_length_cm) }}" class="form-control"></div>
                <div><label class="form-label">1-3 Length Date</label><input type="date" name="assessment_1_3_length_date" value="{{ old('assessment_1_3_length_date', optional($profile?->assessment_1_3_length_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">1-3 Weight (kg)</label><input type="number" step="0.01" min="0" name="assessment_1_3_weight_kg" value="{{ old('assessment_1_3_weight_kg', $profile?->assessment_1_3_weight_kg) }}" class="form-control"></div>
                <div><label class="form-label">1-3 Weight Date</label><input type="date" name="assessment_1_3_weight_date" value="{{ old('assessment_1_3_weight_date', optional($profile?->assessment_1_3_weight_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div>
                    <label class="form-label">1-3 Status</label>
                    <select name="assessment_1_3_status" class="form-select">
                        <option value="">Select...</option>
                        @foreach(['S', 'W-MAM', 'W-SAM', 'O', 'N'] as $status)
                            <option value="{{ $status }}" {{ old('assessment_1_3_status', $profile?->assessment_1_3_status) === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="form-label">Low Birth Weight Iron - 1 Month</label><input type="date" name="low_birth_weight_iron_1_month_date" value="{{ old('low_birth_weight_iron_1_month_date', optional($profile?->low_birth_weight_iron_1_month_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">Low Birth Weight Iron - 2 Months</label><input type="date" name="low_birth_weight_iron_2_month_date" value="{{ old('low_birth_weight_iron_2_month_date', optional($profile?->low_birth_weight_iron_2_month_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">Low Birth Weight Iron - 3 Months</label><input type="date" name="low_birth_weight_iron_3_month_date" value="{{ old('low_birth_weight_iron_3_month_date', optional($profile?->low_birth_weight_iron_3_month_date)->format('Y-m-d')) }}" class="form-control"></div>
            </div>
        </div>

        <div class="cctl-panel">
            <div class="cctl-section-title">Page 3/4: Immunization And Exclusive Breastfeeding</div>
            <div class="cctl-grid">
                @foreach([
                    'dpt_hepb_hib_1_date' => 'DPT-Hib-HepB 1',
                    'dpt_hepb_hib_2_date' => 'DPT-Hib-HepB 2',
                    'dpt_hepb_hib_3_date' => 'DPT-Hib-HepB 3',
                    'opv_1_date' => 'OPV 1',
                    'opv_2_date' => 'OPV 2',
                    'opv_3_date' => 'OPV 3',
                    'pcv_1_date' => 'PCV 1',
                    'pcv_2_date' => 'PCV 2',
                    'pcv_3_date' => 'PCV 3',
                    'ipv_1_date' => 'IPV 1',
                ] as $field => $label)
                    <div><label class="form-label">{{ $label }}</label><input type="date" name="{{ $field }}" value="{{ old($field, optional(data_get($profile, $field))->format('Y-m-d')) }}" class="form-control"></div>
                @endforeach
                @foreach([
                    'exclusive_breastfeeding_1_5_months' => 'Exclusive breastfeeding at 1 1/2 mos.',
                    'exclusive_breastfeeding_2_5_months' => 'Exclusive breastfeeding at 2 1/2 mos.',
                    'exclusive_breastfeeding_3_5_months' => 'Exclusive breastfeeding at 3 1/2 mos.',
                    'exclusive_breastfeeding_4_5_months' => 'Exclusive breastfeeding at 4 1/2 mos.',
                    'exclusive_breastfeeding_5_9_months' => 'Exclusive breastfeeding at 5 mos. & 29 days',
                ] as $field => $label)
                    <div class="d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1" {{ old($field, data_get($profile, $field)) ? 'checked' : '' }}><label class="form-check-label" for="{{ $field }}">{{ $label }}</label></div></div>
                @endforeach
                <div><label class="form-label">6-11 Months Age Label</label><input type="text" name="assessment_6_11_age_months" value="{{ old('assessment_6_11_age_months', $profile?->assessment_6_11_age_months) }}" class="form-control"></div>
                <div><label class="form-label">6-11 Length (cm)</label><input type="number" step="0.01" min="0" name="assessment_6_11_length_cm" value="{{ old('assessment_6_11_length_cm', $profile?->assessment_6_11_length_cm) }}" class="form-control"></div>
                <div><label class="form-label">6-11 Length Date</label><input type="date" name="assessment_6_11_length_date" value="{{ old('assessment_6_11_length_date', optional($profile?->assessment_6_11_length_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">6-11 Weight (kg)</label><input type="number" step="0.01" min="0" name="assessment_6_11_weight_kg" value="{{ old('assessment_6_11_weight_kg', $profile?->assessment_6_11_weight_kg) }}" class="form-control"></div>
                <div><label class="form-label">6-11 Weight Date</label><input type="date" name="assessment_6_11_weight_date" value="{{ old('assessment_6_11_weight_date', optional($profile?->assessment_6_11_weight_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div>
                    <label class="form-label">6-11 Status</label>
                    <select name="assessment_6_11_status" class="form-select">
                        <option value="">Select...</option>
                        @foreach(['S', 'W-MAM', 'W-SAM', 'O', 'N'] as $status)
                            <option value="{{ $status }}" {{ old('assessment_6_11_status', $profile?->assessment_6_11_status) === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="cctl-panel">
            <div class="cctl-section-title">Page 4/4: 6-12 Months And Nutrition Outcomes</div>
            <div class="cctl-grid">
                <div class="d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="exclusive_breastfed_up_to_6_months" name="exclusive_breastfed_up_to_6_months" value="1" {{ old('exclusive_breastfed_up_to_6_months', $profile?->exclusive_breastfed_up_to_6_months) ? 'checked' : '' }}><label class="form-check-label" for="exclusive_breastfed_up_to_6_months">Exclusively breastfed up to 6 months</label></div></div>
                <div class="d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="complementary_feeding_introduced" name="complementary_feeding_introduced" value="1" {{ old('complementary_feeding_introduced', $profile?->complementary_feeding_introduced) ? 'checked' : '' }}><label class="form-check-label" for="complementary_feeding_introduced">Complementary feeding introduced at 6 months</label></div></div>
                <div><label class="form-label">Vitamin A Date</label><input type="date" name="vitamin_a_date" value="{{ old('vitamin_a_date', optional($profile?->vitamin_a_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">MNP Date</label><input type="date" name="mnp_date" value="{{ old('mnp_date', optional($profile?->mnp_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">MNP Sachets Given</label><input type="number" min="0" name="mnp_sachets_given" value="{{ old('mnp_sachets_given', $profile?->mnp_sachets_given) }}" class="form-control"></div>
                <div><label class="form-label">MMR 1 Date</label><input type="date" name="mmr_1_date" value="{{ old('mmr_1_date', optional($profile?->mmr_1_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">IPV 2 Date</label><input type="date" name="ipv_2_date" value="{{ old('ipv_2_date', optional($profile?->ipv_2_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">12 Months Age Label</label><input type="text" name="assessment_12_age_months" value="{{ old('assessment_12_age_months', $profile?->assessment_12_age_months) }}" class="form-control"></div>
                <div><label class="form-label">12 Months Length (cm)</label><input type="number" step="0.01" min="0" name="assessment_12_length_cm" value="{{ old('assessment_12_length_cm', $profile?->assessment_12_length_cm) }}" class="form-control"></div>
                <div><label class="form-label">12 Months Length Date</label><input type="date" name="assessment_12_length_date" value="{{ old('assessment_12_length_date', optional($profile?->assessment_12_length_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">12 Months Weight (kg)</label><input type="number" step="0.01" min="0" name="assessment_12_weight_kg" value="{{ old('assessment_12_weight_kg', $profile?->assessment_12_weight_kg) }}" class="form-control"></div>
                <div><label class="form-label">12 Months Weight Date</label><input type="date" name="assessment_12_weight_date" value="{{ old('assessment_12_weight_date', optional($profile?->assessment_12_weight_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div>
                    <label class="form-label">12 Months Status</label>
                    <select name="assessment_12_status" class="form-select">
                        <option value="">Select...</option>
                        @foreach(['S', 'W-MAM', 'W-SAM', 'O', 'N'] as $status)
                            <option value="{{ $status }}" {{ old('assessment_12_status', $profile?->assessment_12_status) === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="form-label">MMR 2 Date</label><input type="date" name="mmr_2_date" value="{{ old('mmr_2_date', optional($profile?->mmr_2_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">FIC Date</label><input type="date" name="fic_date" value="{{ old('fic_date', optional($profile?->fic_date)->format('Y-m-d')) }}" class="form-control"></div>
                <div><label class="form-label">CIC Date</label><input type="date" name="cic_date" value="{{ old('cic_date', optional($profile?->cic_date)->format('Y-m-d')) }}" class="form-control"></div>
                @foreach([
                    'man_admitted_sfp' => 'MAN admitted in SFP',
                    'man_cured' => 'MAN cured',
                    'man_defaulted' => 'MAN defaulted',
                    'man_died' => 'MAN died',
                    'sam_admitted_otc' => 'SAM admitted in OTC',
                    'sam_cured' => 'SAM cured',
                    'sam_defaulted' => 'SAM defaulted',
                    'sam_died' => 'SAM died',
                ] as $field => $label)
                    <div class="d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1" {{ old($field, data_get($profile, $field)) ? 'checked' : '' }}><label class="form-check-label" for="{{ $field }}">{{ $label }}</label></div></div>
                @endforeach
                <div style="grid-column:1 / -1;"><label class="form-label">Remarks</label><textarea name="remarks" rows="3" class="form-control">{{ old('remarks', $profile?->remarks) }}</textarea></div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Childcare Record</button>
            <a href="{{ route('midwife.child-care-target-clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
