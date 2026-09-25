@extends('midwife.layout')

@section('title', 'My Profile - ReproCare')

@section('midwife-content')
<style>
    .mw-profile-card {
        background:var(--color-surface);
        border:1px solid var(--color-border);
        border-radius:20px;
        box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent);
        overflow:hidden;
    }
    .mw-profile-header {
        padding:1.25rem 1.5rem;
        background:var(--color-surface);
        border-bottom:1px solid var(--color-border);
    }
    .mw-profile-title {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:700;
        font-size:1.05rem;
        color:var(--color-text);
        margin:0;
    }
    .mw-stat-tile {
        background:var(--color-surface);
        border:1px solid var(--color-border);
        border-radius:16px;
        padding:1.25rem 1rem;
        text-align:center;
        transition:transform 0.2s ease, box-shadow 0.2s ease;
    }
    .mw-stat-tile:hover {
        transform:translateY(-2px);
        box-shadow:0 8px 24px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);
    }
    .mw-stat-icon {
        width:44px;
        height:44px;
        border-radius:12px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.25rem;
        margin:0 auto 0.75rem;
    }
    .mw-stat-val {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:800;
        font-size:1.45rem;
        color:var(--color-text);
        line-height:1.2;
    }
    .mw-stat-lbl {
        font-size:0.78rem;
        color:var(--color-text-muted);
        font-weight:600;
        text-transform:uppercase;
        letter-spacing:0.5px;
        margin-top:0.25rem;
        margin-bottom:0;
    }
    .mw-timeline-item {
        position:relative;
        padding-left:1.5rem;
        padding-bottom:1.25rem;
        border-left:2px solid var(--color-border);
    }
    .mw-timeline-item:last-child {
        border-left-color:transparent;
        padding-bottom:0;
    }
    .mw-timeline-dot {
        position:absolute;
        left:-7px;
        top:2px;
        width:12px;
        height:12px;
        border-radius:50%;
        background:var(--color-primary);
        border:2px solid var(--color-border);
        box-shadow:0 0 0 2px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent);
    }
</style>

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h1 class="page-hero-title">My Profile
            </h1>
            <p class="page-hero-subtitle">
                Manage your clinical identification, personal information, and professional credentials.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('midwife.settings') }}" class="btn-hero-secondary">
                <i class="bi bi-gear-fill me-1"></i> Account Settings
            </a>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Left Profile Summary Card --}}
    <div class="col-xl-4">
        <div class="mw-profile-card h-100 fade-in-card">
            <div class="mw-profile-header">
                <h5 class="mw-profile-title">Profile Overview</h5>
            </div>
            <div class="p-4 text-center">
                <div class="position-relative d-inline-block mb-3">
                    @if($midwife->profile_image_url)
                        <img src="{{ $midwife->profile_image_url }}" alt="{{ $midwife->name }}" 
                             style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid var(--color-primary-soft);box-shadow:0 6px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent);">
                    @else
                        <div style="width:120px;height:120px;border-radius:50%;background:linear-gradient(135deg, var(--color-primary), var(--color-primary-text));display:flex;align-items:center;justify-content:center;color:var(--color-on-solid);font-size:2.5rem;font-weight:800;margin:0 auto;box-shadow:0 6px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);">
                            {{ strtoupper(substr($midwife->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <span class="badge rounded-pill px-3 py-1 fw-bold mb-2" style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text); font-size:0.8rem;">
                    <i class="bi bi-shield-check me-1"></i> Certified Midwife
                </span>

                <h4 class="fw-bold mb-1" style="color:var(--color-text); font-family:'Plus Jakarta Sans', sans-serif;">{{ $midwife->name }}</h4>
                <p class="text-muted mb-3" style="font-size:0.9rem;">{{ $midwife->email }}</p>

                <div class="p-3 rounded-3 text-start border" style="background:var(--color-bg); border-color:var(--color-border) !important; font-size:0.88rem;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">PRC License:</span>
                        <span class="fw-bold" style="color:var(--color-text);">{{ $midwife->license_number ?? '0084921-MW' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Station:</span>
                        <span class="fw-bold" style="color:var(--color-text);">San Carlos City CHO</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Experience:</span>
                        <span class="fw-bold" style="color:var(--color-text);">{{ $midwife->years_experience ?? '8' }} Years</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Personal Information Card --}}
    <div class="col-xl-8">
        <div class="mw-profile-card h-100 fade-in-card">
            <div class="mw-profile-header d-flex justify-content-between align-items-center">
                <h5 class="mw-profile-title">Personal Information</h5>
                <a href="{{ url('/profile/edit') }}" class="btn btn-sm btn-primary" style="border-radius:10px; font-weight:700;">
                    <i class="bi bi-pencil-square me-1"></i> Edit Profile
                </a>
            </div>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:0.75rem; letter-spacing:0.5px;">Full Name</label>
                        <input type="text" class="form-control" value="{{ $midwife->name }}" readonly style="border-radius:12px; background:var(--color-bg); border:1.5px solid var(--color-border); font-weight:600; color:var(--color-text);">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:0.75rem; letter-spacing:0.5px;">Email Address</label>
                        <input type="email" class="form-control" value="{{ $midwife->email }}" readonly style="border-radius:12px; background:var(--color-bg); border:1.5px solid var(--color-border); font-weight:600; color:var(--color-text);">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:0.75rem; letter-spacing:0.5px;">Contact Number</label>
                        <input type="tel" class="form-control" value="{{ $midwife->contact_number ?? '0917-849-2101' }}" readonly style="border-radius:12px; background:var(--color-bg); border:1.5px solid var(--color-border); font-weight:600; color:var(--color-text);">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:0.75rem; letter-spacing:0.5px;">Date of Birth</label>
                        <input type="text" class="form-control" value="{{ $midwife->date_of_birth ? \Carbon\Carbon::parse($midwife->date_of_birth)->format('F d, Y') : 'June 18, 1988' }}" readonly style="border-radius:12px; background:var(--color-bg); border:1.5px solid var(--color-border); font-weight:600; color:var(--color-text);">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:0.75rem; letter-spacing:0.5px;">Jurisdiction / Assigned Address</label>
                        <input type="text" class="form-control" value="{{ $midwife->address ?? 'San Carlos City Health Office, Rural Health Unit I, Pangasinan' }}" readonly style="border-radius:12px; background:var(--color-bg); border:1.5px solid var(--color-border); font-weight:600; color:var(--color-text);">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:0.75rem; letter-spacing:0.5px;">PRC License Number</label>
                        <input type="text" class="form-control" value="{{ $midwife->license_number ?? '0084921-MW' }}" readonly style="border-radius:12px; background:var(--color-bg); border:1.5px solid var(--color-border); font-weight:600; color:var(--color-text);">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size:0.75rem; letter-spacing:0.5px;">Clinical Experience</label>
                        <input type="text" class="form-control" value="{{ $midwife->years_experience ?? '8' }} Years Active Midwifery" readonly style="border-radius:12px; background:var(--color-bg); border:1.5px solid var(--color-border); font-weight:600; color:var(--color-text);">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Statistics Tiles --}}
    <div class="col-xl-8">
        <div class="mw-profile-card">
            <div class="mw-profile-header">
                <h5 class="mw-profile-title">Clinical Practice Statistics</h5>
            </div>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-sm-6 col-md-3">
                        <div class="mw-stat-tile">
                            <div class="mw-stat-icon" style="background:var(--color-primary-soft); color:var(--color-primary-text);">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="mw-stat-val">{{ App\Models\User::where('role', 'user')->count() }}</div>
                            <div class="mw-stat-lbl">Enrolled Women</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="mw-stat-tile">
                            <div class="mw-stat-icon" style="background:var(--color-success-soft); color:var(--color-success-text);">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                            <div class="mw-stat-val">{{ App\Models\Checkup::count() }}</div>
                            <div class="mw-stat-lbl">Consultations</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="mw-stat-tile">
                            <div class="mw-stat-icon" style="background:var(--color-info-soft); color:var(--color-info-text);">
                                <i class="bi bi-person-workspace"></i>
                            </div>
                            <div class="mw-stat-val">{{ App\Models\User::where('role', 'bhw')->count() }}</div>
                            <div class="mw-stat-lbl">BHW Network</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="mw-stat-tile">
                            <div class="mw-stat-icon" style="background:var(--color-peach-soft); color:var(--color-peach-text);">
                                <i class="bi bi-heart-pulse-fill"></i>
                            </div>
                            <div class="mw-stat-val">{{ App\Models\Pregnancy::count() }}</div>
                            <div class="mw-stat-lbl">Pregnancies</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Clinical Activity Timeline --}}
    <div class="col-xl-4">
        <div class="mw-profile-card h-100">
            <div class="mw-profile-header">
                <h5 class="mw-profile-title">Recent Activity</h5>
            </div>
            <div class="p-4">
                <div class="d-flex flex-column">
                    <div class="mw-timeline-item">
                        <div class="mw-timeline-dot"></div>
                        <h6 class="mb-1 fw-bold" style="color:var(--color-text); font-size:0.9rem;">Authenticated into Midwife Portal</h6>
                        <small class="text-muted d-block">{{ now()->format('M j, Y · h:i A') }}</small>
                    </div>
                    <div class="mw-timeline-item">
                        <div class="mw-timeline-dot" style="background:var(--color-success-text);"></div>
                        <h6 class="mb-1 fw-bold" style="color:var(--color-text); font-size:0.9rem;">Updated Maternal Health Records</h6>
                        <small class="text-muted d-block">San Carlos City RHU I</small>
                    </div>
                    <div class="mw-timeline-item">
                        <div class="mw-timeline-dot" style="background:var(--color-info-text);"></div>
                        <h6 class="mb-1 fw-bold" style="color:var(--color-text); font-size:0.9rem;">Reviewed Barangay Referrals</h6>
                        <small class="text-muted d-block">Routine prenatal triage verified</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
