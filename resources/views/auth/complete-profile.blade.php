@extends('layouts.public')

@section('title', 'Complete your profile — ReproCare')
@section('page-class', 'login-page')

@push('styles')
<style>
    .complete-wrap { display:flex; align-items:flex-start; justify-content:center; padding:56px 22px 64px; background:var(--color-bg); min-height:calc(100vh - 166px); }
    .complete-card { width:100%; max-width:640px; background:var(--color-surface); border:1px solid var(--color-border); border-radius:22px; padding:34px 32px; box-shadow:0 18px 48px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 14%, transparent); }
    .complete-card h2 { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.5rem; letter-spacing:-.04em; margin:0 0 8px; text-align:center; }
    .complete-card p.lead { text-align:center; color:var(--color-text-muted); font-size:.9rem; margin:0 0 24px; line-height:1.6; }
    .complete-section { border-top:1px solid var(--color-border); padding-top:20px; margin-top:22px; }
    .complete-section h3 { font-size:.95rem; font-weight:800; margin:0 0 4px; }
    .complete-section p.desc { font-size:.78rem; color:var(--color-text-muted); margin:0 0 16px; }
    .complete-grid { display:grid; grid-template-columns:1fr 1fr; gap:0 14px; }
    .complete-grid .full { grid-column:1 / -1; }
    .complete-field { margin-bottom:14px; }
    .complete-field label { display:block; font-size:.8rem; font-weight:700; margin-bottom:6px; }
    .complete-field label .req { color:var(--color-danger-text, #b42318); }
    .complete-field input, .complete-field select { width:100%; min-height:50px; border-radius:12px; border:1.5px solid var(--color-border); background:var(--color-surface); padding:10px 14px; font-size:.88rem; color:var(--color-text); outline:none; }
    .complete-field input:focus, .complete-field select:focus { border-color:var(--color-primary-text); box-shadow:0 0 0 4px color-mix(in srgb, var(--color-primary) 13%, transparent); }
    .complete-field input[disabled] { opacity:.65; }
    .complete-field .hint { font-size:.74rem; color:var(--color-text-muted); margin-top:5px; }
    .complete-error { font-size:.76rem; color:var(--color-danger-text, #b42318); margin-top:5px; }
    .id-preview { display:none; max-width:100%; max-height:160px; border-radius:12px; margin-top:8px; border:1px solid var(--color-border); }
    @media (max-width:600px) { .complete-grid { grid-template-columns:1fr; } .complete-card { padding:26px 20px; } }
</style>
@endpush

@section('content')
<div class="complete-wrap">
    <div class="complete-card" aria-labelledby="complete-title">
        <h2 id="complete-title">One last step</h2>
        <p class="lead">Welcome, {{ $user->first_name }}! You signed in with Google, so there's <strong>no email or password to fill in</strong> — just complete the same registration details below so RHU can verify your account.</p>

        @if ($errors->any())
            <div class="public-alert" role="alert">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('profile.complete.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- 0. Google identity (read-only) --}}
            <div class="complete-field">
                <label>Google email</label>
                <input type="email" value="{{ $user->email }}" disabled>
                <div class="hint">Verified by Google — this becomes your ReproCare account email.</div>
            </div>

            {{-- 1. Personal details --}}
            <div class="complete-section">
                <h3>1. Personal details</h3>
                <p class="desc">Name arrived from Google — correct it if needed.</p>
                <div class="complete-grid">
                    <div class="complete-field">
                        <label for="first_name">First name <span class="req">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                        @error('first_name')<div class="complete-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="complete-field">
                        <label for="middle_initial">Middle initial</label>
                        <input type="text" id="middle_initial" name="middle_initial" value="{{ old('middle_initial', $user->middle_initial) }}" maxlength="10">
                        @error('middle_initial')<div class="complete-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="complete-field">
                        <label for="last_name">Last name <span class="req">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                        @error('last_name')<div class="complete-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="complete-field">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">Select…</option>
                            <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                        </select>
                        @error('gender')<div class="complete-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="complete-field full">
                        <label for="date_of_birth">Date of birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}">
                        @error('date_of_birth')<div class="complete-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- 2. Contact & address --}}
            <div class="complete-section">
                <h3>2. Contact &amp; address</h3>
                <p class="desc">So your BHW and midwife can reach you for checkups and RHU updates.</p>
                <div class="complete-grid">
                    <div class="complete-field">
                        <label for="contact_number">Phone number <span class="req">*</span></label>
                        <input type="tel" id="contact_number" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" placeholder="e.g. 09171234567" autocomplete="tel" required>
                        @error('contact_number')<div class="complete-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="complete-field">
                        <label for="barangay">Barangay (San Carlos City) <span class="req">*</span></label>
                        <select id="barangay" name="barangay" required>
                            <option value="" disabled {{ old('barangay', $user->barangay) ? '' : 'selected' }}>Select your barangay</option>
                            @foreach ($barangays as $barangay)
                                <option value="{{ $barangay }}" {{ old('barangay', $user->barangay) === $barangay ? 'selected' : '' }}>{{ $barangay }}</option>
                            @endforeach
                        </select>
                        @error('barangay')<div class="complete-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="complete-field">
                        <label for="house_number">House / unit no.</label>
                        <input type="text" id="house_number" name="house_number" value="{{ old('house_number') }}">
                    </div>
                    <div class="complete-field">
                        <label for="purok">Purok</label>
                        <input type="text" id="purok" name="purok" value="{{ old('purok') }}">
                    </div>
                    <div class="complete-field">
                        <label for="sitio">Sitio / street</label>
                        <input type="text" id="sitio" name="sitio" value="{{ old('sitio') }}">
                    </div>
                    <div class="complete-field">
                        <label for="address_label">Address landmark</label>
                        <input type="text" id="address_label" name="address_label" value="{{ old('address_label') }}" placeholder="e.g. Near the chapel">
                    </div>
                </div>
            </div>

            {{-- 3. Valid ID --}}
            <div class="complete-section">
                <h3>3. Valid ID</h3>
                <p class="desc">RHU needs a photo of your ID (front and back) to verify your account. JPG or PNG, max 5&nbsp;MB each.</p>
                <div class="complete-grid">
                    <div class="complete-field">
                        <label for="id_image_front">ID front <span class="req">*</span></label>
                        <input type="file" id="id_image_front" name="id_image_front" accept="image/jpeg,image/png,image/jpg" data-preview="preview_front" required>
                        <img id="preview_front" class="id-preview" alt="ID front preview">
                        @error('id_image_front')<div class="complete-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="complete-field">
                        <label for="id_image_back">ID back <span class="req">*</span></label>
                        <input type="file" id="id_image_back" name="id_image_back" accept="image/jpeg,image/png,image/jpg" data-preview="preview_back" required>
                        <img id="preview_back" class="id-preview" alt="ID back preview">
                        @error('id_image_back')<div class="complete-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- 4. Partner (optional) --}}
            <div class="complete-section">
                <h3>4. Partner <span style="font-weight:400;color:var(--color-text-muted);">(optional)</span></h3>
                <div class="complete-grid">
                    <div class="complete-field">
                        <label for="partner_name">Partner name</label>
                        <input type="text" id="partner_name" name="partner_name" value="{{ old('partner_name') }}">
                    </div>
                    <div class="complete-field">
                        <label for="partner_contact">Partner contact</label>
                        <input type="text" id="partner_contact" name="partner_contact" value="{{ old('partner_contact') }}">
                    </div>
                </div>
            </div>

            {{-- 5. Emergency contacts --}}
            <div class="complete-section">
                <h3>5. Emergency contacts</h3>
                <p class="desc">At least one contact is required.</p>
                <div class="complete-grid">
                    <div class="complete-field">
                        <label for="emergency_name_1">Primary contact name <span class="req">*</span></label>
                        <input type="text" id="emergency_name_1" name="emergency_name_1" value="{{ old('emergency_name_1') }}" required>
                        @error('emergency_name_1')<div class="complete-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="complete-field">
                        <label for="emergency_relationship_1">Relationship <span class="req">*</span></label>
                        <input type="text" id="emergency_relationship_1" name="emergency_relationship_1" value="{{ old('emergency_relationship_1') }}" placeholder="e.g. Mother, Spouse" required>
                        @error('emergency_relationship_1')<div class="complete-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="complete-field">
                        <label for="emergency_contact_number_1">Contact number <span class="req">*</span></label>
                        <input type="text" id="emergency_contact_number_1" name="emergency_contact_number_1" value="{{ old('emergency_contact_number_1') }}" required>
                        @error('emergency_contact_number_1')<div class="complete-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="complete-field">
                        <label for="emergency_address_1">Address</label>
                        <input type="text" id="emergency_address_1" name="emergency_address_1" value="{{ old('emergency_address_1') }}">
                    </div>
                    <div class="complete-field">
                        <label for="emergency_name_2">Secondary contact name</label>
                        <input type="text" id="emergency_name_2" name="emergency_name_2" value="{{ old('emergency_name_2') }}">
                    </div>
                    <div class="complete-field">
                        <label for="emergency_contact_number_2">Secondary contact number</label>
                        <input type="text" id="emergency_contact_number_2" name="emergency_contact_number_2" value="{{ old('emergency_contact_number_2') }}">
                        <div class="hint">If you name a secondary contact, relationship + number are required too.</div>
                    </div>
                    <div class="complete-field full" id="emergency_relationship_2_wrap" style="display:none;">
                        <label for="emergency_relationship_2">Secondary relationship</label>
                        <input type="text" id="emergency_relationship_2" name="emergency_relationship_2" value="{{ old('emergency_relationship_2') }}">
                    </div>
                </div>
            </div>

            <button type="submit" style="width:100%; height:54px; margin-top:24px; border-radius:16px; border:none; background:var(--color-primary); color:var(--color-primary-on); font-weight:700; font-size:1rem; cursor:pointer;">Complete profile</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="margin-top:14px; text-align:center;">
            @csrf
            <button type="submit" style="background:none; border:none; color:var(--color-text-muted); font-size:.82rem; cursor:pointer; text-decoration:underline;">Sign out</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ID photo previews.
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            var img = document.getElementById(input.dataset.preview);
            if (!img || !input.files || !input.files[0]) return;
            img.src = URL.createObjectURL(input.files[0]);
            img.style.display = 'block';
        });
    });
    // Reveal the secondary relationship field only when a secondary contact is named.
    (function () {
        var name2 = document.getElementById('emergency_name_2');
        var wrap = document.getElementById('emergency_relationship_2_wrap');
        if (!name2 || !wrap) return;
        var sync = function () { wrap.style.display = name2.value.trim() !== '' ? '' : 'none'; };
        name2.addEventListener('input', sync);
        sync();
    })();
</script>
@endpush
