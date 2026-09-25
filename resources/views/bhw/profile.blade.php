@extends('bhw.layout')

@section('title', 'Profile - ReproCare')

@section('bhw-content')
<style>
    .profile-shell {
        display:grid;
        gap:1.5rem;
    }

    .profile-panel {
        background:color-mix(in srgb, var(--color-surface-soft) 96%, transparent);
        border:1px solid color-mix(in srgb, var(--color-primary) 18%, transparent);
        border-radius:1rem;
        overflow:hidden;
        box-shadow:0 1rem 2.5rem color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent);
    }

    .profile-panel-header {
        background:linear-gradient(135deg, var(--color-info), var(--color-info-text));
        color:var(--color-on-solid);
        padding:1rem 1.25rem;
    }

    .profile-panel-body {
        padding:1.25rem;
    }

    .profile-avatar {
        width:132px;
        height:132px;
        object-fit:cover;
        border-radius:50%;
        border:4px solid color-mix(in srgb, var(--color-border) 20%, transparent);
        box-shadow:0 0.75rem 2rem color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);
    }

    .profile-kicker {
        color:var(--color-primary-text);
        font-size:0.8rem;
        text-transform:uppercase;
        letter-spacing:0.08em;
    }

    .stat-card {
        text-align:center;
        background:color-mix(in srgb, var(--color-primary-text) 45%, transparent);
        border:1px solid color-mix(in srgb, var(--color-primary) 14%, transparent);
        border-radius:0.9rem;
        padding:1.25rem 1rem;
        height:100%;
    }

    .timeline {
        position:relative;
        padding-left:1.25rem;
    }

    .timeline-item {
        position:relative;
        margin-bottom:1rem;
    }

    .timeline-marker {
        position:absolute;
        left:-1.2rem;
        top:0.45rem;
        width:10px;
        height:10px;
        border-radius:50%;
    }

    .timeline-content {
        background:color-mix(in srgb, var(--color-primary-text) 35%, transparent);
        border:1px solid color-mix(in srgb, var(--color-primary) 12%, transparent);
        padding:1rem;
        border-radius:0.9rem;
    }
</style>

<div class="page-header mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">My Profile</h1>
            <p class="page-subtitle mb-0">Manage your personal information and account settings</p>
        </div>
        <a href="{{ route('bhw.settings') }}" class="btn btn-outline-primary">
            <i class="bi bi-gear"></i> Settings
        </a>
    </div>
</div>

<div class="profile-shell">
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="profile-panel h-100">
                <div class="profile-panel-header">
                    <h5 class="mb-0">Profile Overview</h5>
                </div>
                <div class="profile-panel-body text-center">
                    <img src="{{ $bhw->profile_image_url }}" alt="{{ $bhw->name }}" class="profile-avatar mb-3">
                    <div class="profile-kicker mb-2">BHW Portal</div>
                    <h4 class="mb-1">{{ $bhw->name }}</h4>
                    <p class="text-muted mb-3">{{ $bhw->email }}</p>
                    <span class="badge bg-success px-3 py-2">Barangay Health Worker</span>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="profile-panel h-100">
                <div class="profile-panel-header">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="profile-panel-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="{{ $bhw->name }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="{{ $bhw->email }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" value="{{ $bhw->contact_number ?? 'Not provided' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="text" class="form-control" value="{{ $bhw->date_of_birth ? \Carbon\Carbon::parse($bhw->date_of_birth)->format('F d, Y') : 'Not provided' }}" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" rows="3" readonly>{{ $bhw->address ?? 'Not provided' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Barangay Assignment</label>
                            <input type="text" class="form-control" value="{{ $bhw->barangay ?? 'Not assigned' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Training Certification</label>
                            <input type="text" class="form-control" value="{{ $bhw->certification ?? 'Not provided' }}" readonly>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a href="{{ url('/profile/edit') }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </a>
                        <a href="{{ route('bhw.settings') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-gear"></i> Account Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="profile-panel">
                <div class="profile-panel-header">
                    <h5 class="mb-0">Work Statistics</h5>
                </div>
                <div class="profile-panel-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <div class="stat-card">
                                <div class="mb-2 text-primary"><i class="bi bi-people fs-3"></i></div>
                                <h4 class="mb-1">{{ App\Models\User::where('role', 'user')->count() }}</h4>
                                <p class="text-muted small mb-0">Patients Served</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-card">
                                <div class="mb-2 text-success"><i class="bi bi-calendar-check fs-3"></i></div>
                                <h4 class="mb-1">{{ App\Models\Checkup::where('midwife_id', auth()->id())->count() }}</h4>
                                <p class="text-muted small mb-0">Checkups Conducted</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-card">
                                <div class="mb-2 text-info"><i class="bi bi-clipboard-pulse fs-3"></i></div>
                                <h4 class="mb-1">{{ App\Models\HealthRecord::where('recorded_by_id', auth()->id())->count() }}</h4>
                                <p class="text-muted small mb-0">Health Records</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-card">
                                <div class="mb-2 text-warning"><i class="bi bi-geo-alt fs-3"></i></div>
                                <h4 class="mb-1">{{ $bhw->barangay ?? 'N/A' }}</h4>
                                <p class="text-muted small mb-0">Service Area</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="profile-panel h-100">
                <div class="profile-panel-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="profile-panel-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Logged in to system</h6>
                                <p class="text-muted small mb-0">{{ now()->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Added health record</h6>
                                <p class="text-muted small mb-0">2 hours ago</p>
                            </div>
                        </div>
                        <div class="timeline-item mb-0">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Conducted checkup</h6>
                                <p class="text-muted small mb-0">Yesterday</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
