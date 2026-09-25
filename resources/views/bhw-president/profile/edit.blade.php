@extends('bhw-president.layout')

@section('title', 'Edit Profile - BHW President Portal | ReproCare')

@section('bhw-president-content')
<style>
    .profile-edit-card {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:1rem;
        overflow:hidden;
        box-shadow:var(--shadow-md);
    }
    .profile-edit-header {
        background:linear-gradient(135deg, var(--color-primary), var(--color-primary));
    }
    .profile-image-panel {
        background:var(--bg-card2);
        border:1px solid var(--border);
        border-radius:1rem;
        padding:1.5rem;
    }
    .profile-preview-image {
        width:160px;
        height:160px;
        object-fit:cover;
        border:4px solid var(--bg-card);
        background:var(--bg-card2);
        box-shadow:var(--shadow-md);
    }
    .section-title {
        font-size:1rem;
        font-weight:700;
        color:var(--text);
        margin-bottom:1rem;
    }
    .profile-edit-card .card-body {
        background:transparent;
        color:var(--text);
    }
    .profile-edit-card .form-control,
    .profile-edit-card .form-select {
        background:var(--bg-input);
        color:var(--text);
        border-color:var(--border);
    }
    .profile-edit-card .form-control::file-selector-button {
        background:var(--primary-subtle);
        color:var(--primary-light);
        border:0;
        margin-right:1rem;
        padding:0.55rem 0.85rem;
    }
    .profile-edit-card .badge.text-bg-light {
        background:var(--bg-card2) !important;
        color:var(--text) !important;
        border-color:var(--border) !important;
    }
</style>

<div class="py-4">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <div class="mb-3">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('bhw-president.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

            <div class="card shadow-sm profile-edit-card">
                <div class="card-header text-white profile-edit-header">
                    <h4 class="mb-0">Edit Profile</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error:</strong>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="redirect_to" value="{{ route('profile.show') }}">

                        <div class="profile-image-panel mb-4">
                            <div class="row align-items-center g-4">
                                <div class="col-md-4 text-center">
                                    <img id="profile-preview"
                                         src="{{ $user->profile_image_url }}"
                                         alt="Profile Picture"
                                         onerror="this.onerror=null;this.src='{{ $user->gender === 'male' ? asset('images/avatars/avatar-male.png') : asset('images/avatars/avatar-female.png') }}';"
                                         class="rounded-circle profile-preview-image">
                                </div>
                                <div class="col-md-8">
                                    <div class="section-title mb-2">Profile Picture</div>
                                    <p class="text-muted mb-3">Upload a clear photo to personalize your BHW President account.</p>
                                    <label for="profile_image" class="form-label">Choose Image</label>
                                    <input type="file"
                                           class="form-control @error('profile_image') is-invalid @enderror"
                                           id="profile_image"
                                           name="profile_image"
                                           accept="image/jpeg,image/jpg,image/png"
                                           onchange="previewImage(this)">
                                    <div class="form-text">Accepted formats: JPG, PNG. Max size: 5MB.</div>
                                    @error('profile_image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5 class="section-title">Personal Information</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label for="middle_initial" class="form-label">MI</label>
                                <input type="text" class="form-control @error('middle_initial') is-invalid @enderror" id="middle_initial" name="middle_initial" value="{{ old('middle_initial', $user->middle_initial) }}" maxlength="2">
                                @error('middle_initial')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth) }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="text" class="form-control @error('contact_number') is-invalid @enderror" id="contact_number" name="contact_number" value="{{ old('contact_number', $user->contact_number ?? $user->phone) }}">
                            @error('contact_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="purok" class="form-label">Sitio / Street / Purok</label>
                            <input type="text" class="form-control @error('purok') is-invalid @enderror" id="purok" name="purok"
                                   value="{{ old('purok', $user->purok?->name ?? '') }}" placeholder="e.g. Sitio Malinis, Purok 3">
                            @error('purok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <h5 class="section-title">Change Password</h5>
                        <p class="text-muted small">Leave blank to keep current password.</p>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" autocomplete="new-password" placeholder="Min 8 characters">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password" placeholder="Re-enter new password">
                        </div>

                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                            <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <a href="{{ route('bhw-president.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-house me-1"></i> Dashboard
                            </a>
                            @if($user->hasProfileImage())
                                <form action="{{ route('profile.remove-image') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="{{ route('profile.show') }}">
                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to remove your profile picture?')">
                                        <i class="bi bi-trash me-1"></i> Remove Picture
                                    </button>
                                </form>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
