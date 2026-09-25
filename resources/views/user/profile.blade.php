@extends('user.layout')

@section('title', 'My Profile - ReproCare')

@section('user-content')
<style>
    .user-profile-card {
        background:color-mix(in srgb, var(--color-surface-soft) 96%, transparent);
        border:1px solid color-mix(in srgb, var(--color-primary) 18%, transparent);
        border-radius:1rem;
        overflow:hidden;
        box-shadow:0 1rem 2.5rem color-mix(in srgb, rgb(var(--color-shadow-rgb)) 18%, transparent);
    }

    .user-profile-header {
        background:linear-gradient(135deg, var(--color-info), var(--color-info-text));
        color:var(--color-on-solid);
    }

    .user-profile-avatar {
        width:104px;
        height:104px;
        object-fit:cover;
        border-radius:50%;
        border:4px solid color-mix(in srgb, var(--color-border) 18%, transparent);
    }

    .user-profile-side {
        background:color-mix(in srgb, var(--color-primary-text) 35%, transparent);
        border:1px solid color-mix(in srgb, var(--color-primary) 14%, transparent);
        border-radius:1rem;
        padding:1.25rem;
        height:100%;
    }
</style>

<div class="py-4">
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="user-profile-side text-center">
                <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" class="user-profile-avatar mb-3">
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                <div class="d-flex justify-content-center flex-wrap gap-2">
                    <span class="badge bg-info px-3 py-2">{{ ucfirst($user->role) }}</span>
                    <span class="badge text-bg-light border px-3 py-2">{{ $user->barangay ?? 'No barangay' }}</span>
                </div>
                <div class="d-grid mt-4">
                    <a href="{{ url('/profile/edit') }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Full Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card shadow user-profile-card">
                <div class="card-header user-profile-header">
                    <h5 class="mb-0">My Profile</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" readonly>
                                </div>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>

                            <div class="col-md-6">
                                <label for="barangay" class="form-label">Barangay</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" class="form-control" id="barangay" name="barangay" value="{{ $user->barangay }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label">Account Type</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                    <input type="text" class="form-control" id="role" name="role" value="{{ ucfirst($user->role) }}" readonly>
                                </div>
                                <small class="text-muted">Role cannot be changed</small>
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-house"></i></span>
                                    <textarea class="form-control" id="address" name="address" rows="4">{{ $user->address }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
