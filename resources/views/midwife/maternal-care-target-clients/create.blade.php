@extends('midwife.layout')

@section('title', 'Add New Maternal Care Record - Midwife Portal | ReproCare')

@push('styles')
<style>
    .mctl-create-shell { max-width:1200px; margin:0 auto; }
    .mctl-create-header { background:var(--bg-card); border:1px solid var(--border); border-radius:22px; padding:1.4rem 1.6rem; margin-bottom:1.25rem; }
    .mctl-create-title { font-family:'Plus Jakarta Sans', sans-serif; font-size:1.45rem; font-weight:800; color:var(--text); margin:0; }
    .mctl-create-subtitle { color:var(--text-muted); margin:0.35rem 0 0; }
    .mctl-create-card { background:var(--bg-card); border:1px solid var(--border); border-radius:22px; padding:1.5rem; }
    .mctl-section-title { font-weight:700; color:var(--primary-light); margin-bottom:1rem; font-size:1.1rem; border-bottom:1px solid var(--border); padding-bottom:0.5rem; }
    .mctl-form-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem; }
    .mctl-form-group { display:flex; flex-direction:column; gap:0.35rem; }
    .mctl-form-group label { font-size:0.85rem; font-weight:600; color:var(--text); }
    .mctl-form-group input, .mctl-form-group select { padding:0.65rem 0.9rem; border-radius:12px; border:1px solid var(--border); background:var(--bg-card2); color:var(--text); }
    .mctl-form-group input:focus, .mctl-form-group select:focus { outline:none; border-color:var(--primary); }
    .mctl-actions { display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.5rem; padding-top:1rem; border-top:1px solid var(--border); }
</style>
@endpush

@section('midwife-content')
<div class="mctl-create-shell">
    <div class="mctl-create-header">
        <h1 class="mctl-create-title">Add New Maternal Care Record</h1>
        <p class="mctl-create-subtitle">Create a new maternal care target client record for patients not in the system.</p>
    </div>

    <form action="{{ route('midwife.maternal-care-target-clients.store') }}" method="POST">
        @csrf

        <div class="mctl-create-card mb-3">
            <h3 class="mctl-section-title">Personal Information</h3>
            <div class="mctl-form-grid">
                <div class="mctl-form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                </div>
                <div class="mctl-form-group">
                    <label for="middle_initial">Middle Initial</label>
                    <input type="text" id="middle_initial" name="middle_initial" value="{{ old('middle_initial') }}" maxlength="10">
                </div>
                <div class="mctl-form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                </div>
                <div class="mctl-form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="{{ old('address') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="purok_id">Purok</label>
                    <select id="purok_id" name="purok_id">
                        <option value="">Select Purok</option>
                        @foreach(\App\Models\Purok::orderBy('name')->get() as $purok)
                            <option value="{{ $purok->id }}" {{ old('purok_id') == $purok->id ? 'selected' : '' }}>{{ $purok->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="mctl-create-card mb-3">
            <h3 class="mctl-section-title">Registration & Pregnancy</h3>
            <div class="mctl-form-grid">
                <div class="mctl-form-group">
                    <label for="date_of_registration">Date of Registration</label>
                    <input type="date" id="date_of_registration" name="date_of_registration" value="{{ old('date_of_registration') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="family_serial_no">Family Serial Number</label>
                    <input type="text" id="family_serial_no" name="family_serial_no" value="{{ old('family_serial_no') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="lmp">LMP (Last Menstrual Period)</label>
                    <input type="date" id="lmp" name="lmp" value="{{ old('lmp') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="edd">EDD (Estimated Date of Delivery)</label>
                    <input type="date" id="edd" name="edd" value="{{ old('edd') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="gravida">Gravida (G)</label>
                    <input type="number" id="gravida" name="gravida" value="{{ old('gravida') }}" min="0">
                </div>
                <div class="mctl-form-group">
                    <label for="parity">Parity (P)</label>
                    <input type="number" id="parity" name="parity" value="{{ old('parity') }}" min="0">
                </div>
            </div>
        </div>

        <div class="mctl-create-card mb-3">
            <h3 class="mctl-section-title">Pre-natal Check-ups</h3>
            <div class="mctl-form-grid">
                <div class="mctl-form-group">
                    <label for="prenatal_first_trimester_date">1st Trimester Date</label>
                    <input type="date" id="prenatal_first_trimester_date" name="prenatal_first_trimester_date" value="{{ old('prenatal_first_trimester_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="prenatal_second_trimester_date_1">2nd Trimester Date 1</label>
                    <input type="date" id="prenatal_second_trimester_date_1" name="prenatal_second_trimester_date_1" value="{{ old('prenatal_second_trimester_date_1') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="prenatal_second_trimester_date_2">2nd Trimester Date 2</label>
                    <input type="date" id="prenatal_second_trimester_date_2" name="prenatal_second_trimester_date_2" value="{{ old('prenatal_second_trimester_date_2') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="prenatal_third_trimester_date_1">3rd Trimester Date 1</label>
                    <input type="date" id="prenatal_third_trimester_date_1" name="prenatal_third_trimester_date_1" value="{{ old('prenatal_third_trimester_date_1') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="prenatal_third_trimester_date_2">3rd Trimester Date 2</label>
                    <input type="date" id="prenatal_third_trimester_date_2" name="prenatal_third_trimester_date_2" value="{{ old('prenatal_third_trimester_date_2') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="prenatal_third_trimester_date_3">3rd Trimester Date 3</label>
                    <input type="date" id="prenatal_third_trimester_date_3" name="prenatal_third_trimester_date_3" value="{{ old('prenatal_third_trimester_date_3') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="prenatal_third_trimester_date_4">3rd Trimester Date 4</label>
                    <input type="date" id="prenatal_third_trimester_date_4" name="prenatal_third_trimester_date_4" value="{{ old('prenatal_third_trimester_date_4') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="prenatal_third_trimester_date_5">3rd Trimester Date 5</label>
                    <input type="date" id="prenatal_third_trimester_date_5" name="prenatal_third_trimester_date_5" value="{{ old('prenatal_third_trimester_date_5') }}">
                </div>
            </div>
        </div>

        <div class="mctl-create-card mb-3">
            <h3 class="mctl-section-title">Immunization (Td/Tt)</h3>
            <div class="mctl-form-grid">
                <div class="mctl-form-group">
                    <label for="td1_date">Td1 Date</label>
                    <input type="date" id="td1_date" name="td1_date" value="{{ old('td1_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="td2_date">Td2 Date</label>
                    <input type="date" id="td2_date" name="td2_date" value="{{ old('td2_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="td3_date">Td3 Date</label>
                    <input type="date" id="td3_date" name="td3_date" value="{{ old('td3_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="td4_date">Td4 Date</label>
                    <input type="date" id="td4_date" name="td4_date" value="{{ old('td4_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="td5_date">Td5 Date</label>
                    <input type="date" id="td5_date" name="td5_date" value="{{ old('td5_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="fim_status">FIM Status</label>
                    <select id="fim_status" name="fim_status">
                        <option value="">Select</option>
                        <option value="1" {{ old('fim_status') == '1' ? 'selected' : '' }}>Complete</option>
                        <option value="0" {{ old('fim_status') == '0' ? 'selected' : '' }}>Incomplete</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mctl-create-card mb-3">
            <h3 class="mctl-section-title">Iron with Folic Acid</h3>
            <div class="mctl-form-grid">
                <div class="mctl-form-group">
                    <label for="iron_folic_first_visit_date">1st Visit Date</label>
                    <input type="date" id="iron_folic_first_visit_date" name="iron_folic_first_visit_date" value="{{ old('iron_folic_first_visit_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="iron_folic_first_visit_tablets">1st Visit Tablets</label>
                    <input type="number" id="iron_folic_first_visit_tablets" name="iron_folic_first_visit_tablets" value="{{ old('iron_folic_first_visit_tablets') }}" min="0">
                </div>
                <div class="mctl-form-group">
                    <label for="iron_folic_second_visit_date">2nd Visit Date</label>
                    <input type="date" id="iron_folic_second_visit_date" name="iron_folic_second_visit_date" value="{{ old('iron_folic_second_visit_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="iron_folic_second_visit_tablets">2nd Visit Tablets</label>
                    <input type="number" id="iron_folic_second_visit_tablets" name="iron_folic_second_visit_tablets" value="{{ old('iron_folic_second_visit_tablets') }}" min="0">
                </div>
                <div class="mctl-form-group">
                    <label for="iron_folic_third_visit_date">3rd Visit Date</label>
                    <input type="date" id="iron_folic_third_visit_date" name="iron_folic_third_visit_date" value="{{ old('iron_folic_third_visit_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="iron_folic_third_visit_tablets">3rd Visit Tablets</label>
                    <input type="number" id="iron_folic_third_visit_tablets" name="iron_folic_third_visit_tablets" value="{{ old('iron_folic_third_visit_tablets') }}" min="0">
                </div>
                <div class="mctl-form-group">
                    <label for="iron_folic_fourth_visit_date">4th Visit Date</label>
                    <input type="date" id="iron_folic_fourth_visit_date" name="iron_folic_fourth_visit_date" value="{{ old('iron_folic_fourth_visit_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="iron_folic_fourth_visit_tablets">4th Visit Tablets</label>
                    <input type="number" id="iron_folic_fourth_visit_tablets" name="iron_folic_fourth_visit_tablets" value="{{ old('iron_folic_fourth_visit_tablets') }}" min="0">
                </div>
            </div>
        </div>

        <div class="mctl-create-card mb-3">
            <h3 class="mctl-section-title">Calcium Carbonate</h3>
            <div class="mctl-form-grid">
                <div class="mctl-form-group">
                    <label for="calcium_second_visit_date">2nd Visit Date</label>
                    <input type="date" id="calcium_second_visit_date" name="calcium_second_visit_date" value="{{ old('calcium_second_visit_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="calcium_second_visit_tablets">2nd Visit Tablets</label>
                    <input type="number" id="calcium_second_visit_tablets" name="calcium_second_visit_tablets" value="{{ old('calcium_second_visit_tablets') }}" min="0">
                </div>
                <div class="mctl-form-group">
                    <label for="calcium_third_visit_date">3rd Visit Date</label>
                    <input type="date" id="calcium_third_visit_date" name="calcium_third_visit_date" value="{{ old('calcium_third_visit_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="calcium_third_visit_tablets">3rd Visit Tablets</label>
                    <input type="number" id="calcium_third_visit_tablets" name="calcium_third_visit_tablets" value="{{ old('calcium_third_visit_tablets') }}" min="0">
                </div>
                <div class="mctl-form-group">
                    <label for="calcium_fourth_visit_date">4th Visit Date</label>
                    <input type="date" id="calcium_fourth_visit_date" name="calcium_fourth_visit_date" value="{{ old('calcium_fourth_visit_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="calcium_fourth_visit_tablets">4th Visit Tablets</label>
                    <input type="number" id="calcium_fourth_visit_tablets" name="calcium_fourth_visit_tablets" value="{{ old('calcium_fourth_visit_tablets') }}" min="0">
                </div>
            </div>
        </div>

        <div class="mctl-create-card mb-3">
            <h3 class="mctl-section-title">Iodine Capsules</h3>
            <div class="mctl-form-grid">
                <div class="mctl-form-group">
                    <label for="iodine_date">Date Given</label>
                    <input type="date" id="iodine_date" name="iodine_date" value="{{ old('iodine_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="iodine_capsules_given">Capsules Given</label>
                    <input type="number" id="iodine_capsules_given" name="iodine_capsules_given" value="{{ old('iodine_capsules_given') }}" min="0">
                </div>
            </div>
        </div>

        <div class="mctl-create-card mb-3">
            <h3 class="mctl-section-title">Screening & Assessment</h3>
            <div class="mctl-form-grid">
                <div class="mctl-form-group">
                    <label for="nutritional_assessment_status">Nutritional Assessment</label>
                    <select id="nutritional_assessment_status" name="nutritional_assessment_status">
                        <option value="">Select</option>
                        <option value="low" {{ old('nutritional_assessment_status') == 'low' ? 'selected' : '' }}>Low (&lt; 18.5)</option>
                        <option value="normal" {{ old('nutritional_assessment_status') == 'normal' ? 'selected' : '' }}>Normal (18.5-22.9)</option>
                        <option value="high" {{ old('nutritional_assessment_status') == 'high' ? 'selected' : '' }}>High (&ge; 23.0)</option>
                    </select>
                </div>
                <div class="mctl-form-group">
                    <label for="deworming_date">Deworming Date</label>
                    <input type="date" id="deworming_date" name="deworming_date" value="{{ old('deworming_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="syphilis_screening_date">Syphilis Screening Date</label>
                    <input type="date" id="syphilis_screening_date" name="syphilis_screening_date" value="{{ old('syphilis_screening_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="syphilis_screening_result">Syphilis Result</label>
                    <select id="syphilis_screening_result" name="syphilis_screening_result">
                        <option value="">Select</option>
                        <option value="positive" {{ old('syphilis_screening_result') == 'positive' ? 'selected' : '' }}>Positive</option>
                        <option value="negative" {{ old('syphilis_screening_result') == 'negative' ? 'selected' : '' }}>Negative</option>
                    </select>
                </div>
                <div class="mctl-form-group">
                    <label for="hepatitis_b_screening_date">Hepatitis B Screening Date</label>
                    <input type="date" id="hepatitis_b_screening_date" name="hepatitis_b_screening_date" value="{{ old('hepatitis_b_screening_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="hepatitis_b_screening_result">Hepatitis B Result</label>
                    <select id="hepatitis_b_screening_result" name="hepatitis_b_screening_result">
                        <option value="">Select</option>
                        <option value="positive" {{ old('hepatitis_b_screening_result') == 'positive' ? 'selected' : '' }}>Positive</option>
                        <option value="negative" {{ old('hepatitis_b_screening_result') == 'negative' ? 'selected' : '' }}>Negative</option>
                    </select>
                </div>
                <div class="mctl-form-group">
                    <label for="hiv_screening_date">HIV Screening Date</label>
                    <input type="date" id="hiv_screening_date" name="hiv_screening_date" value="{{ old('hiv_screening_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="gestational_diabetes_screening_date">Gestational Diabetes Date</label>
                    <input type="date" id="gestational_diabetes_screening_date" name="gestational_diabetes_screening_date" value="{{ old('gestational_diabetes_screening_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="gestational_diabetes_result">Gestational Diabetes Result</label>
                    <select id="gestational_diabetes_result" name="gestational_diabetes_result">
                        <option value="">Select</option>
                        <option value="positive" {{ old('gestational_diabetes_result') == 'positive' ? 'selected' : '' }}>Positive</option>
                        <option value="negative" {{ old('gestational_diabetes_result') == 'negative' ? 'selected' : '' }}>Negative</option>
                    </select>
                </div>
                <div class="mctl-form-group">
                    <label for="cbc_screening_date">CBC Screening Date</label>
                    <input type="date" id="cbc_screening_date" name="cbc_screening_date" value="{{ old('cbc_screening_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="cbc_anemia_status">CBC Anemia Status</label>
                    <select id="cbc_anemia_status" name="cbc_anemia_status">
                        <option value="">Select</option>
                        <option value="with_anemia" {{ old('cbc_anemia_status') == 'with_anemia' ? 'selected' : '' }}>With Anemia (+)</option>
                        <option value="without_anemia" {{ old('cbc_anemia_status') == 'without_anemia' ? 'selected' : '' }}>Without Anemia (-)</option>
                    </select>
                </div>
                <div class="mctl-form-group">
                    <label for="cbc_given_iron">Given Iron?</label>
                    <select id="cbc_given_iron" name="cbc_given_iron">
                        <option value="">Select</option>
                        <option value="1" {{ old('cbc_given_iron') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('cbc_given_iron') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mctl-create-card mb-3">
            <h3 class="mctl-section-title">Pregnancy Outcome & Delivery</h3>
            <div class="mctl-form-grid">
                <div class="mctl-form-group">
                    <label for="pregnancy_terminated_date">Pregnancy Terminated Date</label>
                    <input type="date" id="pregnancy_terminated_date" name="pregnancy_terminated_date" value="{{ old('pregnancy_terminated_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="pregnancy_outcome">Pregnancy Outcome</label>
                    <select id="pregnancy_outcome" name="pregnancy_outcome">
                        <option value="">Select</option>
                        <option value="ft" {{ old('pregnancy_outcome') == 'ft' ? 'selected' : '' }}>FT - Full Term</option>
                        <option value="pt" {{ old('pregnancy_outcome') == 'pt' ? 'selected' : '' }}>PT - Pre-term</option>
                        <option value="fd" {{ old('pregnancy_outcome') == 'fd' ? 'selected' : '' }}>FD - Fetal Death</option>
                        <option value="ab" {{ old('pregnancy_outcome') == 'ab' ? 'selected' : '' }}>AB - Abortion/Miscarriage</option>
                    </select>
                </div>
                <div class="mctl-form-group">
                    <label for="pregnancy_outcome_sex">Baby Sex</label>
                    <select id="pregnancy_outcome_sex" name="pregnancy_outcome_sex">
                        <option value="">Select</option>
                        <option value="M" {{ old('pregnancy_outcome_sex') == 'M' ? 'selected' : '' }}>Male</option>
                        <option value="F" {{ old('pregnancy_outcome_sex') == 'F' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="mctl-form-group">
                    <label for="delivery_type">Type of Delivery</label>
                    <select id="delivery_type" name="delivery_type">
                        <option value="">Select</option>
                        <option value="cs" {{ old('delivery_type') == 'cs' ? 'selected' : '' }}>CS - Caesarian Section</option>
                        <option value="vd" {{ old('delivery_type') == 'vd' ? 'selected' : '' }}>VD - Vaginal Delivery</option>
                    </select>
                </div>
                <div class="mctl-form-group">
                    <label for="birth_weight_category">Birth Weight Category</label>
                    <select id="birth_weight_category" name="birth_weight_category">
                        <option value="">Select</option>
                        <option value="low" {{ old('birth_weight_category') == 'low' ? 'selected' : '' }}>Low (&lt; 2,500g)</option>
                        <option value="normal" {{ old('birth_weight_category') == 'normal' ? 'selected' : '' }}>Normal (&ge; 2,500g)</option>
                        <option value="unknown" {{ old('birth_weight_category') == 'unknown' ? 'selected' : '' }}>Unknown</option>
                    </select>
                </div>
                <div class="mctl-form-group">
                    <label for="facility_delivery_type">Facility Delivery Type</label>
                    <input type="text" id="facility_delivery_type" name="facility_delivery_type" value="{{ old('facility_delivery_type') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="facility_bemonc_capable">Facility BEmONC Capable</label>
                    <input type="text" id="facility_bemonc_capable" name="facility_bemonc_capable" value="{{ old('facility_bemonc_capable') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="facility_ownership">Facility Ownership</label>
                    <select id="facility_ownership" name="facility_ownership">
                        <option value="">Select</option>
                        <option value="public" {{ old('facility_ownership') == 'public' ? 'selected' : '' }}>Public</option>
                        <option value="private" {{ old('facility_ownership') == 'private' ? 'selected' : '' }}>Private</option>
                    </select>
                </div>
                <div class="mctl-form-group">
                    <label for="non_health_facility_code">Non-Health Facility Code</label>
                    <input type="text" id="non_health_facility_code" name="non_health_facility_code" value="{{ old('non_health_facility_code') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="birth_attendant_code">Birth Attendant Code</label>
                    <input type="text" id="birth_attendant_code" name="birth_attendant_code" value="{{ old('birth_attendant_code') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="delivery_remarks">Delivery Remarks</label>
                    <input type="text" id="delivery_remarks" name="delivery_remarks" value="{{ old('delivery_remarks') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="delivery_date">Delivery Date</label>
                    <input type="date" id="delivery_date" name="delivery_date" value="{{ old('delivery_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="delivery_time">Delivery Time</label>
                    <input type="time" id="delivery_time" name="delivery_time" value="{{ old('delivery_time') }}">
                </div>
            </div>
        </div>

        <div class="mctl-create-card mb-3">
            <h3 class="mctl-section-title">Postpartum</h3>
            <div class="mctl-form-grid">
                <div class="mctl-form-group">
                    <label for="postpartum_within_24_hours_date">Within 24 Hours Date</label>
                    <input type="date" id="postpartum_within_24_hours_date" name="postpartum_within_24_hours_date" value="{{ old('postpartum_within_24_hours_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="postpartum_within_7_days_date">Within 7 Days Date</label>
                    <input type="date" id="postpartum_within_7_days_date" name="postpartum_within_7_days_date" value="{{ old('postpartum_within_7_days_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="postpartum_iron_first_month">Iron 1st Month</label>
                    <input type="text" id="postpartum_iron_first_month" name="postpartum_iron_first_month" value="{{ old('postpartum_iron_first_month') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="postpartum_iron_second_month">Iron 2nd Month</label>
                    <input type="text" id="postpartum_iron_second_month" name="postpartum_iron_second_month" value="{{ old('postpartum_iron_second_month') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="postpartum_iron_third_month">Iron 3rd Month</label>
                    <input type="text" id="postpartum_iron_third_month" name="postpartum_iron_third_month" value="{{ old('postpartum_iron_third_month') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="vitamin_a_date">Vitamin A Date</label>
                    <input type="date" id="vitamin_a_date" name="vitamin_a_date" value="{{ old('vitamin_a_date') }}">
                </div>
                <div class="mctl-form-group">
                    <label for="postpartum_remarks">Postpartum Remarks</label>
                    <input type="text" id="postpartum_remarks" name="postpartum_remarks" value="{{ old('postpartum_remarks') }}">
                </div>
            </div>
        </div>

        <div class="mctl-actions">
            <a href="{{ route('midwife.maternal-care-target-clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Record</button>
        </div>
    </form>
</div>
@endsection
