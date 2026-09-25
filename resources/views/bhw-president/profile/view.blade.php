@extends('bhw-president.layout')

@section('title', $user->name . ' - Profile')

@section('bhw-president-content')
<style>
    .profile-view-card {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:1rem;
        overflow:hidden;
        box-shadow:var(--shadow-md);
    }
    .profile-view-header {
        background:linear-gradient(135deg, var(--color-primary), var(--color-primary));
    }
    .profile-view-avatar {
        width:150px;
        height:150px;
        object-fit:cover;
        border:5px solid var(--bg-card);
        box-shadow:var(--shadow-md);
    }
    .profile-info-item {
        border:1px solid var(--border);
        border-radius:0.85rem;
        padding:1rem;
        background:var(--bg-card2);
        height:100%;
    }
    .profile-info-label {
        font-size:0.8rem;
        text-transform:uppercase;
        letter-spacing:0.04em;
        color:var(--text-muted);
        margin-bottom:0.35rem;
    }
    .profile-info-value {
        font-size:1rem;
        font-weight:600;
        color:var(--text);
    }
</style>

<div class="py-4">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <div class="card shadow-sm profile-view-card">
                <div class="card-header text-white profile-view-header">
                    <h4 class="mb-0">{{ $user->name }}'s Profile</h4>
                </div>
                <div class="card-body">
                    <div class="row align-items-center g-4 mb-4">
                        <div class="col-md-4 text-center">
                            <x-patient-avatar :patient="$user" :size="150" />
                        </div>
                        <div class="col-md-8">
                            <h3 class="mb-1">{{ $user->name }}</h3>
                            <p class="text-muted mb-3">{{ $user->email }}</p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge px-3 py-2" style="background:var(--color-primary);">{{ $user->role === 'bhw_president' ? 'BHW President' : ucfirst(str_replace('_', ' ', $user->role ?? 'user')) }}</span>
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
                                <div class="profile-info-label">Gender</div>
                                <div class="profile-info-value">{{ $user->gender ? ucfirst($user->gender) : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-info-item">
                                <div class="profile-info-label">Contact Number</div>
                                <div class="profile-info-value">{{ $user->contact_number ?? $user->phone ?? 'N/A' }}</div>
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
                    </div>

                    <div class="mt-4 d-flex flex-wrap gap-2">
                        <a href="{{ route('profile.show') }}" class="btn btn-primary">
                            <i class="bi bi-person me-1"></i> My Profile
                        </a>
                        <a href="{{ route('bhw-president.dashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
