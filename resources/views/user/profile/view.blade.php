@extends('user.layout')

@section('title', $user->name . ' - Profile')

@section('user-content')
<style>
    .profile-view-card {
        border:0;
        border-radius:1rem;
        overflow:hidden;
    }

    .profile-view-header {
        background:linear-gradient(135deg, var(--color-info), var(--color-info));
    }

    .profile-view-avatar {
        width:160px;
        height:160px;
        object-fit:cover;
        border:5px solid var(--color-border);
        box-shadow:0 0.75rem 2rem color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent);
    }

    .profile-info-item {
        border:none;
        border-radius:0.85rem;
        padding:1rem;
        height:100%;
        background:var(--color-surface);
    }

    .profile-info-label {
        font-size:0.8rem;
        text-transform:uppercase;
        letter-spacing:0.04em;
        color:var(--color-text-muted);
        margin-bottom:0.35rem;
    }

    .profile-info-value {
        font-size:1rem;
        font-weight:600;
        color:var(--color-text-muted);
    }
</style>
<div class="py-4">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <div class="mb-3">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('user.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
            <div class="card shadow-sm profile-view-card">
                <div class="card-header text-white profile-view-header">
                    <h4 class="mb-0">{{ $user->name }}'s Profile</h4>
                </div>
                <div class="card-body">
                    <div class="row align-items-center g-4 mb-4">
                        <div class="col-md-4 text-center">
                            <img src="{{ $user->profile_image_url }}"
                                 alt="Profile Picture"
                                 class="rounded-circle profile-view-avatar">
                        </div>
                        <div class="col-md-8">
                            <h3 class="mb-1">{{ $user->name }}</h3>
                            <p class="text-muted mb-3">{{ $user->email }}</p>
                            <div class="d-flex flex-wrap gap-2">
                                @if($user->role === 'midwife')
                                    <span class="badge bg-primary px-3 py-2">Midwife</span>
                                @elseif($user->role === 'bhw')
                                    <span class="badge bg-success px-3 py-2">Barangay Health Worker</span>
                                @else
                                    <span class="badge bg-info px-3 py-2">Woman</span>
                                @endif
                                <span class="badge text-bg-light border px-3 py-2">Age: {{ $user->age }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="profile-info-item">
                                <div class="profile-info-label">Full Name</div>
                                <div class="profile-info-value">{{ $user->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-info-item">
                                <div class="profile-info-label">Date of Birth</div>
                                <div class="profile-info-value">{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('F d, Y') : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-info-item">
                                <div class="profile-info-label">Address</div>
                                <div class="profile-info-value">{{ $user->address ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-info-item">
                                <div class="profile-info-label">Barangay</div>
                                <div class="profile-info-value">{{ $user->barangay ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-info-item">
                                <div class="profile-info-label">Partner/Spouse Name</div>
                                <div class="profile-info-value">{{ $user->partner_name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-info-item">
                                <div class="profile-info-label">Partner Phone Number</div>
                                <div class="profile-info-value">{{ $user->partner_contact ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex flex-wrap gap-2">
                        @if(Auth::id() === $user->id)
                            <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                        @endif
                        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('user.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
