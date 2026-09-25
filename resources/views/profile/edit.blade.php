@extends('layouts.app')

@section('title', 'Edit Profile - ReproCare')

@push('styles')
<style>
    /* ── Layout ── */
    .edit-profile-wrap { max-width:780px; margin:0 auto; }

    /* ── Section header divider ── */
    .section-divider {
        display:flex; align-items:center; gap:0.75rem;
        margin:1.75rem 0 1.25rem;
    }
    .section-divider-icon {
        width:32px; height:32px; border-radius:9px;
        display:flex; align-items:center; justify-content:center;
        font-size:0.85rem; flex-shrink:0;
    }
    .section-divider h6 {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:800; font-size:0.92rem;
        color:var(--text); margin:0;
        text-transform:uppercase; letter-spacing:0.7px;
    }
    .section-divider-line {
        flex:1; height:1px; background:var(--border);
    }

    /* ── Avatar upload panel ── */
    .avatar-upload-panel {
        background:var(--bg-card2);
        border:1px solid var(--border);
        border-radius:18px;
        padding:1.5rem;
        display:flex;
        align-items:center;
        gap:1.5rem;
        transition:background 0.4s ease;
        flex-wrap:wrap;
    }
    .avatar-upload-preview {
        width:90px; height:90px;
        border-radius:50%;
        object-fit:cover;
        border:3px solid var(--bg-card);
        box-shadow:0 0 0 3px var(--primary), 0 4px 18px var(--primary-glow);
        flex-shrink:0;
        background:linear-gradient(135deg, var(--primary), var(--accent-violet));
        display:block;
    }
    .avatar-upload-init {
        width:90px; height:90px;
        border-radius:50%;
        background:linear-gradient(135deg, var(--primary), var(--accent-violet));
        display:flex; align-items:center; justify-content:center;
        font-size:2rem; font-weight:800; color:var(--color-on-solid);
        flex-shrink:0;
        border:3px solid var(--bg-card);
        box-shadow:0 0 0 3px var(--primary), 0 4px 18px var(--primary-glow);
    }
    .avatar-upload-info h6 {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:0.95rem; font-weight:700; color:var(--text); margin-bottom:0.3rem;
    }
    .avatar-upload-info p { font-size:0.8rem; color:var(--text-muted); margin:0 0 0.85rem; }
    .avatar-file-input {
        width:100%; background:var(--bg-input); border:1.5px solid var(--border);
        border-radius:10px; color:var(--text); font-size:0.875rem;
        padding:0.45rem 0.75rem; outline:none; cursor:pointer;
        transition:border-color 0.2s ease, background 0.3s ease;
    }
    .avatar-file-input:focus { border-color:var(--primary); }
    .avatar-file-input::file-selector-button {
        background:var(--primary-subtle); color:var(--primary-light);
        border:1px solid var(--border-glass); border-radius:7px;
        padding:0.3rem 0.75rem; font-size:0.8rem; font-weight:600;
        cursor:pointer; margin-right:0.75rem;
        transition:background 0.2s ease;
    }
    .avatar-file-input::file-selector-button:hover { background:var(--primary); color:var(--color-on-solid); }

    /* ── Edit card header ── */
    .edit-profile-header {
        background:linear-gradient(135deg, var(--primary-dark), var(--primary), var(--accent-violet));
        padding:1.25rem 1.75rem;
        border-radius:20px 20px 0 0;
        color:var(--color-on-solid);
    }
    .edit-profile-header h4 {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:800; font-size:1.2rem; margin:0;
    }
    .edit-profile-header p { font-size:0.8rem; opacity:0.75; margin:0.2rem 0 0; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="edit-profile-wrap fade-in-card">

        {{-- Back --}}
        <div class="mb-3">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('profile.show') }}"
               style="color:var(--text-muted);font-size:0.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:0.4rem;transition:color 0.2s ease;">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="card" style="border-radius:20px;overflow:hidden;">

            {{-- Header --}}
            <div class="edit-profile-header">
                <h4>Edit Profile</h4>
                <p>Update your personal information and account settings</p>
            </div>

            <div class="card-body p-4">

                {{-- Flash messages --}}
                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger d-flex align-items-start gap-2 mb-4">
                        <i class="bi bi-exclamation-circle-fill flex-shrink-0 mt-1"></i>
                        <div>
                            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                        </div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect_to" value="{{ url()->previous() }}">

                    {{-- ── Section: Profile Picture ── --}}
                    <div class="section-divider">
                        <div class="section-divider-icon" style="background:var(--primary-subtle);color:var(--primary-light);">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <h6>Profile Picture</h6>
                        <div class="section-divider-line"></div>
                    </div>

                    <div class="avatar-upload-panel mb-2">
                        {{-- Avatar preview --}}
                        @if($user->hasProfileImage())
                            <img id="profilePreview"
                                 src="{{ $user->profile_image_url }}"
                                 alt="Avatar"
                                 class="avatar-upload-preview"
                                 onerror="this.onerror=null;this.style.display='none';document.getElementById('avatarInit').style.display='flex';">
                            <div id="avatarInit"
                                 class="avatar-upload-init"
                                 style="display:none;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @else
                            <img id="profilePreview"
                                 src=""
                                 alt="Avatar"
                                 class="avatar-upload-preview"
                                 style="display:none;">
                            <div id="avatarInit" class="avatar-upload-init">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif

                        <div class="avatar-upload-info flex-grow-1">
                            <h6>Upload a new photo</h6>
                            <p>JPG or PNG, max 5MB. Shown on your profile and forum posts.</p>
                            <input type="file"
                                   id="profile_image"
                                   name="profile_image"
                                   class="avatar-file-input @error('profile_image') is-invalid @enderror"
                                   accept="image/jpeg,image/jpg,image/png"
                                   onchange="previewProfileImage(this)">
                            @error('profile_image')
                                <div style="font-size:0.82rem;color:var(--danger);margin-top:0.35rem;">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    {{-- ── Section: Personal Info ── --}}
                    <div class="section-divider">
                        <div class="section-divider-icon" style="background:color-mix(in srgb, var(--color-info) 12%, transparent);color:var(--info);">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <h6>Personal Information</h6>
                        <div class="section-divider-line"></div>
                    </div>

                    <div class="row g-3 mb-1">
                        <div class="col-md-5">
                            <label for="first_name" class="form-label">First Name <span style="color:var(--danger);">*</span></label>
                            <input type="text"
                                   id="first_name" name="first_name"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name', $user->first_name) }}" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <label for="middle_initial" class="form-label">M.I.</label>
                            <input type="text"
                                   id="middle_initial" name="middle_initial"
                                   class="form-control @error('middle_initial') is-invalid @enderror"
                                   value="{{ old('middle_initial', $user->middle_initial) }}" maxlength="2">
                            @error('middle_initial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-5">
                            <label for="last_name" class="form-label">Last Name <span style="color:var(--danger);">*</span></label>
                            <input type="text"
                                   id="last_name" name="last_name"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name', $user->last_name) }}" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address <span style="color:var(--danger);">*</span></label>
                            <input type="email"
                                   id="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select id="gender" name="gender"
                                    class="form-select @error('gender') is-invalid @enderror">
                                <option value="">-- Select Gender --</option>
                                @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('gender', $user->gender) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date"
                                   id="date_of_birth" name="date_of_birth"
                                   class="form-control @error('date_of_birth') is-invalid @enderror"
                                   value="{{ old('date_of_birth', $user->date_of_birth) }}"
                                   readonly>
                            @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="text"
                                   id="contact_number" name="contact_number"
                                   class="form-control @error('contact_number') is-invalid @enderror"
                                   value="{{ old('contact_number', $user->contact_number) }}"
                                   placeholder="e.g., 09XX XXXX XXXX">
                            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- ── Section: Address ── --}}
                    <div class="section-divider">
                        <div class="section-divider-icon" style="background:color-mix(in srgb, var(--color-success) 12%, transparent);color:var(--success);">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h6>Address Information</h6>
                        <div class="section-divider-line"></div>
                    </div>

                    <div class="row g-3 mb-1">
                        <div class="col-md-12">
                            <label for="barangay" class="form-label">Barangay</label>
                            <input type="text"
                                   id="barangay" name="barangay"
                                   class="form-control @error('barangay') is-invalid @enderror"
                                   value="{{ old('barangay', $user->barangay) }}"
                                   placeholder="Barangay name">
                            @error('barangay')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- ── Section: Change Password ── --}}
                    <div class="section-divider">
                        <div class="section-divider-icon" style="background:color-mix(in srgb, var(--color-warning) 12%, transparent);color:var(--warning);">
                            <i class="bi bi-key-fill"></i>
                        </div>
                        <h6>Change Password</h6>
                        <div class="section-divider-line"></div>
                    </div>

                    <p style="font-size:0.82rem;color:var(--text-muted);margin:-0.25rem 0 1rem;">
                        Leave blank to keep your current password.
                    </p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password"
                                   id="password" name="password"
                                   autocomplete="new-password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimum 8 characters">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password"
                                   id="password_confirmation" name="password_confirmation"
                                   autocomplete="new-password"
                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                   placeholder="Re-enter new password">
                            @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- ── Action Buttons ── --}}
                    <div style="padding-top:1.25rem;border-top:1px solid var(--border);display:flex;gap:0.75rem;flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save-fill me-1"></i> Save Changes
                        </button>
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                    </div>

                </form>
                @if($user->hasProfileImage())
                    <form id="remove-picture-form" action="{{ route('profile.remove-image') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ url()->previous() }}">
                    </form>
                @endif
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function previewProfileImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById('profilePreview');
            const initDiv = document.getElementById('avatarInit');
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (initDiv) initDiv.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush

@endsection
