@extends('midwife.layout')

@section('title', 'Add New Child Care Record - Midwife Portal | ReproCare')

@push('styles')
<style>
    .cctl-create-shell { max-width:1200px; margin:0 auto; }
    .cctl-create-header { background:var(--bg-card); border:1px solid var(--border); border-radius:22px; padding:1.4rem 1.6rem; margin-bottom:1.25rem; }
    .cctl-create-title { font-family:'Plus Jakarta Sans', sans-serif; font-size:1.45rem; font-weight:800; color:var(--text); margin:0; }
    .cctl-create-subtitle { color:var(--text-muted); margin:0.35rem 0 0; }
    .cctl-create-card { background:var(--bg-card); border:1px solid var(--border); border-radius:22px; padding:1.5rem; margin-bottom:1rem; }
    .cctl-section-title { font-weight:700; color:var(--primary-light); margin-bottom:1rem; font-size:1.1rem; border-bottom:1px solid var(--border); padding-bottom:0.5rem; }
    .cctl-form-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem; }
    .cctl-form-group { display:flex; flex-direction:column; gap:0.35rem; }
    .cctl-form-group label { font-size:0.85rem; font-weight:600; color:var(--text); }
    .cctl-form-group input, .cctl-form-group select { padding:0.65rem 0.9rem; border-radius:12px; border:1px solid var(--border); background:var(--bg-card2); color:var(--text); }
    .cctl-form-group input:focus, .cctl-form-group select:focus { outline:none; border-color:var(--primary); }
    .cctl-actions { display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.5rem; padding-top:1rem; border-top:1px solid var(--border); }
</style>
@endpush

@section('midwife-content')
<div class="cctl-create-shell">
    <div class="cctl-create-header">
        <h1 class="cctl-create-title">Add New Child Care Record (0-12 Mos)</h1>
        <p class="cctl-create-subtitle">Create a new child care target client record for children not in the system.</p>
    </div>

    <form action="{{ route('midwife.child-care-target-clients.store') }}" method="POST">
        @csrf

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">Child Information</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="child_first_name">First Name *</label>
                    <input type="text" id="child_first_name" name="child_first_name" value="{{ old('child_first_name') }}" required>
                </div>
                <div class="cctl-form-group">
                    <label for="child_middle_initial">Middle Initial</label>
                    <input type="text" id="child_middle_initial" name="child_middle_initial" value="{{ old('child_middle_initial') }}" maxlength="10">
                </div>
                <div class="cctl-form-group">
                    <label for="child_last_name">Last Name *</label>
                    <input type="text" id="child_last_name" name="child_last_name" value="{{ old('child_last_name') }}" required>
                </div>
                <div class="cctl-form-group">
                    <label for="date_of_birth">Date of Birth *</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                </div>
                <div class="cctl-form-group">
                    <label for="gender">Sex</label>
                    <select id="gender" name="gender">
                        <option value="">Select</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="birth_weight">Birth Weight (kg)</label>
                    <input type="number" id="birth_weight" name="birth_weight" value="{{ old('birth_weight') }}" step="0.01" min="0">
                </div>
                <div class="cctl-form-group">
                    <label for="birth_length">Birth Length (cm)</label>
                    <input type="number" id="birth_length" name="birth_length" value="{{ old('birth_length') }}" step="0.1" min="0">
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">Mother Information</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="mother_name">Mother's Name *</label>
                    <input type="text" id="mother_name" name="mother_name" value="{{ old('mother_name') }}" placeholder="Last, First, Middle" required>
                </div>
                <div class="cctl-form-group">
                    <label for="mother_address">Mother's Address</label>
                    <input type="text" id="mother_address" name="mother_address" value="{{ old('mother_address') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="mother_barangay">Mother's Barangay</label>
                    <input type="text" id="mother_barangay" name="mother_barangay" value="{{ old('mother_barangay') }}">
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">Registration & Location</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="date_of_registration">Date of Registration</label>
                    <input type="date" id="date_of_registration" name="date_of_registration" value="{{ old('date_of_registration') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="family_serial_number">Family Serial Number</label>
                    <input type="text" id="family_serial_number" name="family_serial_number" value="{{ old('family_serial_number') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="barangay">Barangay</label>
                    <input type="text" id="barangay" name="barangay" value="{{ old('barangay') }}">
                </div>
                <div class="cctl-form-group">
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

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">Child Protected at Birth (CPAB)</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="cpab_tt2_tt4">TT2/Td2 or TT4/Td4</label>
                    <select id="cpab_tt2_tt4" name="cpab_tt2_tt4">
                        <option value="">Select</option>
                        <option value="1" {{ old('cpab_tt2_tt4') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('cpab_tt2_tt4') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="cpab_tt3_tt5">TT3/Td3 to TT6/Td6</label>
                    <select id="cpab_tt3_tt5" name="cpab_tt3_tt5">
                        <option value="">Select</option>
                        <option value="1" {{ old('cpab_tt3_tt5') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('cpab_tt3_tt5') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">Newborn (0-28 days)</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="newborn_status">Status at Birth</label>
                    <select id="newborn_status" name="newborn_status">
                        <option value="">Select</option>
                        <option value="low" {{ old('newborn_status') == 'low' ? 'selected' : '' }}>Low (&lt;2,500g)</option>
                        <option value="normal" {{ old('newborn_status') == 'normal' ? 'selected' : '' }}>Normal (&ge;2,500g)</option>
                        <option value="unknown" {{ old('newborn_status') == 'unknown' ? 'selected' : '' }}>Unknown</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="breastfeeding_initiated_date">Breastfeeding Initiated Date</label>
                    <input type="date" id="breastfeeding_initiated_date" name="breastfeeding_initiated_date" value="{{ old('breastfeeding_initiated_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="bcg_date">BCG Date</label>
                    <input type="date" id="bcg_date" name="bcg_date" value="{{ old('bcg_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="hepa_b_bd_date">Hepa B-BD Date</label>
                    <input type="date" id="hepa_b_bd_date" name="hepa_b_bd_date" value="{{ old('hepa_b_bd_date') }}">
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">1-3 Months Assessment</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="assessment_1_3_age_months">Age (months)</label>
                    <input type="text" id="assessment_1_3_age_months" name="assessment_1_3_age_months" value="{{ old('assessment_1_3_age_months') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_1_3_length_cm">Length (cm)</label>
                    <input type="number" id="assessment_1_3_length_cm" name="assessment_1_3_length_cm" value="{{ old('assessment_1_3_length_cm') }}" step="0.1" min="0">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_1_3_length_date">Length Date</label>
                    <input type="date" id="assessment_1_3_length_date" name="assessment_1_3_length_date" value="{{ old('assessment_1_3_length_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_1_3_weight_kg">Weight (kg)</label>
                    <input type="number" id="assessment_1_3_weight_kg" name="assessment_1_3_weight_kg" value="{{ old('assessment_1_3_weight_kg') }}" step="0.01" min="0">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_1_3_weight_date">Weight Date</label>
                    <input type="date" id="assessment_1_3_weight_date" name="assessment_1_3_weight_date" value="{{ old('assessment_1_3_weight_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_1_3_status">Status</label>
                    <select id="assessment_1_3_status" name="assessment_1_3_status">
                        <option value="">Select</option>
                        <option value="S" {{ old('assessment_1_3_status') == 'S' ? 'selected' : '' }}>S - Stunted</option>
                        <option value="W-MAM" {{ old('assessment_1_3_status') == 'W-MAM' ? 'selected' : '' }}>W-MAM - Wasted-MAM</option>
                        <option value="W-SAM" {{ old('assessment_1_3_status') == 'W-SAM' ? 'selected' : '' }}>W-SAM - Wasted-SAM</option>
                        <option value="O" {{ old('assessment_1_3_status') == 'O' ? 'selected' : '' }}>O - Obese/Overweight</option>
                        <option value="N" {{ old('assessment_1_3_status') == 'N' ? 'selected' : '' }}>N - Normal</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="low_birth_weight_iron_1_month_date">Iron 1st Month</label>
                    <input type="date" id="low_birth_weight_iron_1_month_date" name="low_birth_weight_iron_1_month_date" value="{{ old('low_birth_weight_iron_1_month_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="low_birth_weight_iron_2_month_date">Iron 2nd Month</label>
                    <input type="date" id="low_birth_weight_iron_2_month_date" name="low_birth_weight_iron_2_month_date" value="{{ old('low_birth_weight_iron_2_month_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="low_birth_weight_iron_3_month_date">Iron 3rd Month</label>
                    <input type="date" id="low_birth_weight_iron_3_month_date" name="low_birth_weight_iron_3_month_date" value="{{ old('low_birth_weight_iron_3_month_date') }}">
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">Immunization (1-3 months)</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="dpt_hepb_hib_1_date">DPT-HepB-Hib 1</label>
                    <input type="date" id="dpt_hepb_hib_1_date" name="dpt_hepb_hib_1_date" value="{{ old('dpt_hepb_hib_1_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="dpt_hepb_hib_2_date">DPT-HepB-Hib 2</label>
                    <input type="date" id="dpt_hepb_hib_2_date" name="dpt_hepb_hib_2_date" value="{{ old('dpt_hepb_hib_2_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="dpt_hepb_hib_3_date">DPT-HepB-Hib 3</label>
                    <input type="date" id="dpt_hepb_hib_3_date" name="dpt_hepb_hib_3_date" value="{{ old('dpt_hepb_hib_3_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="opv_1_date">OPV 1</label>
                    <input type="date" id="opv_1_date" name="opv_1_date" value="{{ old('opv_1_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="opv_2_date">OPV 2</label>
                    <input type="date" id="opv_2_date" name="opv_2_date" value="{{ old('opv_2_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="opv_3_date">OPV 3</label>
                    <input type="date" id="opv_3_date" name="opv_3_date" value="{{ old('opv_3_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="pcv_1_date">PCV 1</label>
                    <input type="date" id="pcv_1_date" name="pcv_1_date" value="{{ old('pcv_1_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="pcv_2_date">PCV 2</label>
                    <input type="date" id="pcv_2_date" name="pcv_2_date" value="{{ old('pcv_2_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="pcv_3_date">PCV 3</label>
                    <input type="date" id="pcv_3_date" name="pcv_3_date" value="{{ old('pcv_3_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="ipv_1_date">IPV 1</label>
                    <input type="date" id="ipv_1_date" name="ipv_1_date" value="{{ old('ipv_1_date') }}">
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">Exclusive Breastfeeding</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="exclusive_breastfeeding_1_5_months">1 1/2 months</label>
                    <select id="exclusive_breastfeeding_1_5_months" name="exclusive_breastfeeding_1_5_months">
                        <option value="">Select</option>
                        <option value="1" {{ old('exclusive_breastfeeding_1_5_months') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('exclusive_breastfeeding_1_5_months') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="exclusive_breastfeeding_2_5_months">2 1/2 months</label>
                    <select id="exclusive_breastfeeding_2_5_months" name="exclusive_breastfeeding_2_5_months">
                        <option value="">Select</option>
                        <option value="1" {{ old('exclusive_breastfeeding_2_5_months') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('exclusive_breastfeeding_2_5_months') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="exclusive_breastfeeding_3_5_months">3 1/2 months</label>
                    <select id="exclusive_breastfeeding_3_5_months" name="exclusive_breastfeeding_3_5_months">
                        <option value="">Select</option>
                        <option value="1" {{ old('exclusive_breastfeeding_3_5_months') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('exclusive_breastfeeding_3_5_months') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="exclusive_breastfeeding_4_5_months">4 1/2 months</label>
                    <select id="exclusive_breastfeeding_4_5_months" name="exclusive_breastfeeding_4_5_months">
                        <option value="">Select</option>
                        <option value="1" {{ old('exclusive_breastfeeding_4_5_months') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('exclusive_breastfeeding_4_5_months') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="exclusive_breastfeeding_5_9_months">5 months & 29 days</label>
                    <select id="exclusive_breastfeeding_5_9_months" name="exclusive_breastfeeding_5_9_months">
                        <option value="">Select</option>
                        <option value="1" {{ old('exclusive_breastfeeding_5_9_months') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('exclusive_breastfeeding_5_9_months') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="exclusive_breastfed_up_to_6_months">Exclusively BF up to 6 months</label>
                    <select id="exclusive_breastfed_up_to_6_months" name="exclusive_breastfed_up_to_6_months">
                        <option value="">Select</option>
                        <option value="1" {{ old('exclusive_breastfed_up_to_6_months') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('exclusive_breastfed_up_to_6_months') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="complementary_feeding_introduced">Complementary Feeding Introduced</label>
                    <select id="complementary_feeding_introduced" name="complementary_feeding_introduced">
                        <option value="">Select</option>
                        <option value="1" {{ old('complementary_feeding_introduced') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('complementary_feeding_introduced') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">6-11 Months Assessment</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="assessment_6_11_age_months">Age (months)</label>
                    <input type="text" id="assessment_6_11_age_months" name="assessment_6_11_age_months" value="{{ old('assessment_6_11_age_months') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_6_11_length_cm">Length (cm)</label>
                    <input type="number" id="assessment_6_11_length_cm" name="assessment_6_11_length_cm" value="{{ old('assessment_6_11_length_cm') }}" step="0.1" min="0">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_6_11_length_date">Length Date</label>
                    <input type="date" id="assessment_6_11_length_date" name="assessment_6_11_length_date" value="{{ old('assessment_6_11_length_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_6_11_weight_kg">Weight (kg)</label>
                    <input type="number" id="assessment_6_11_weight_kg" name="assessment_6_11_weight_kg" value="{{ old('assessment_6_11_weight_kg') }}" step="0.01" min="0">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_6_11_weight_date">Weight Date</label>
                    <input type="date" id="assessment_6_11_weight_date" name="assessment_6_11_weight_date" value="{{ old('assessment_6_11_weight_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_6_11_status">Status</label>
                    <select id="assessment_6_11_status" name="assessment_6_11_status">
                        <option value="">Select</option>
                        <option value="S" {{ old('assessment_6_11_status') == 'S' ? 'selected' : '' }}>S - Stunted</option>
                        <option value="W-MAM" {{ old('assessment_6_11_status') == 'W-MAM' ? 'selected' : '' }}>W-MAM - Wasted-MAM</option>
                        <option value="W-SAM" {{ old('assessment_6_11_status') == 'W-SAM' ? 'selected' : '' }}>W-SAM - Wasted-SAM</option>
                        <option value="O" {{ old('assessment_6_11_status') == 'O' ? 'selected' : '' }}>O - Obese/Overweight</option>
                        <option value="N" {{ old('assessment_6_11_status') == 'N' ? 'selected' : '' }}>N - Normal</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">12 Months Assessment</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="assessment_12_age_months">Age (months)</label>
                    <input type="text" id="assessment_12_age_months" name="assessment_12_age_months" value="{{ old('assessment_12_age_months') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_12_length_cm">Length (cm)</label>
                    <input type="number" id="assessment_12_length_cm" name="assessment_12_length_cm" value="{{ old('assessment_12_length_cm') }}" step="0.1" min="0">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_12_length_date">Length Date</label>
                    <input type="date" id="assessment_12_length_date" name="assessment_12_length_date" value="{{ old('assessment_12_length_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_12_weight_kg">Weight (kg)</label>
                    <input type="number" id="assessment_12_weight_kg" name="assessment_12_weight_kg" value="{{ old('assessment_12_weight_kg') }}" step="0.01" min="0">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_12_weight_date">Weight Date</label>
                    <input type="date" id="assessment_12_weight_date" name="assessment_12_weight_date" value="{{ old('assessment_12_weight_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="assessment_12_status">Status</label>
                    <select id="assessment_12_status" name="assessment_12_status">
                        <option value="">Select</option>
                        <option value="S" {{ old('assessment_12_status') == 'S' ? 'selected' : '' }}>S - Stunted</option>
                        <option value="W-MAM" {{ old('assessment_12_status') == 'W-MAM' ? 'selected' : '' }}>W-MAM - Wasted-MAM</option>
                        <option value="W-SAM" {{ old('assessment_12_status') == 'W-SAM' ? 'selected' : '' }}>W-SAM - Wasted-SAM</option>
                        <option value="O" {{ old('assessment_12_status') == 'O' ? 'selected' : '' }}>O - Obese/Overweight</option>
                        <option value="N" {{ old('assessment_12_status') == 'N' ? 'selected' : '' }}>N - Normal</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">Micronutrient Supplementation</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="vitamin_a_date">Vitamin A Date</label>
                    <input type="date" id="vitamin_a_date" name="vitamin_a_date" value="{{ old('vitamin_a_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="mnp_date">MNP Date</label>
                    <input type="date" id="mnp_date" name="mnp_date" value="{{ old('mnp_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="mnp_sachets_given">MNP Sachets Given</label>
                    <input type="number" id="mnp_sachets_given" name="mnp_sachets_given" value="{{ old('mnp_sachets_given') }}" min="0">
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">Additional Immunization</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="mmr_1_date">MMR Dose 1 (9th month)</label>
                    <input type="date" id="mmr_1_date" name="mmr_1_date" value="{{ old('mmr_1_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="ipv_2_date">IPV Dose 2 (9th month)</label>
                    <input type="date" id="ipv_2_date" name="ipv_2_date" value="{{ old('ipv_2_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="mmr_2_date">MMR Dose 2 (12th month)</label>
                    <input type="date" id="mmr_2_date" name="mmr_2_date" value="{{ old('mmr_2_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="fic_date">FIC Date</label>
                    <input type="date" id="fic_date" name="fic_date" value="{{ old('fic_date') }}">
                </div>
                <div class="cctl-form-group">
                    <label for="cic_date">CIC Date</label>
                    <input type="date" id="cic_date" name="cic_date" value="{{ old('cic_date') }}">
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">MAN (Moderate Acute Malnutrition)</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="man_admitted_sfp">Admitted in SFP</label>
                    <select id="man_admitted_sfp" name="man_admitted_sfp">
                        <option value="">Select</option>
                        <option value="1" {{ old('man_admitted_sfp') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('man_admitted_sfp') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="man_cured">Cured</label>
                    <select id="man_cured" name="man_cured">
                        <option value="">Select</option>
                        <option value="1" {{ old('man_cured') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('man_cured') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="man_defaulted">Defaulted</label>
                    <select id="man_defaulted" name="man_defaulted">
                        <option value="">Select</option>
                        <option value="1" {{ old('man_defaulted') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('man_defaulted') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="man_died">Died</label>
                    <select id="man_died" name="man_died">
                        <option value="">Select</option>
                        <option value="1" {{ old('man_died') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('man_died') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">SAM (Severe Acute Malnutrition)</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group">
                    <label for="sam_admitted_otc">Admitted in OTC</label>
                    <select id="sam_admitted_otc" name="sam_admitted_otc">
                        <option value="">Select</option>
                        <option value="1" {{ old('sam_admitted_otc') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('sam_admitted_otc') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="sam_cured">Cured</label>
                    <select id="sam_cured" name="sam_cured">
                        <option value="">Select</option>
                        <option value="1" {{ old('sam_cured') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('sam_cured') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="sam_defaulted">Defaulted</label>
                    <select id="sam_defaulted" name="sam_defaulted">
                        <option value="">Select</option>
                        <option value="1" {{ old('sam_defaulted') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('sam_defaulted') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="cctl-form-group">
                    <label for="sam_died">Died</label>
                    <select id="sam_died" name="sam_died">
                        <option value="">Select</option>
                        <option value="1" {{ old('sam_died') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('sam_died') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="cctl-create-card">
            <h3 class="cctl-section-title">Remarks</h3>
            <div class="cctl-form-grid">
                <div class="cctl-form-group" style="grid-column:1 / -1;">
                    <label for="remarks">Remarks</label>
                    <input type="text" id="remarks" name="remarks" value="{{ old('remarks') }}" maxlength="1000">
                </div>
            </div>
        </div>

        <div class="cctl-actions">
            <a href="{{ route('midwife.child-care-target-clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Record</button>
        </div>
    </form>
</div>
@endsection
