@extends('layouts.app')

@section('title', 'My Profile - ReproCare')

@push('styles')
<style>
    /* ── Profile Hero ── */
    .profile-hero {
        position:relative;
        border-radius:20px 20px 0 0;
        overflow:hidden;
        min-height:160px;
        background:linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--accent-pink) 100%);
    }
    .profile-hero-orb {
        position:absolute;
        border-radius:50%;
        background:color-mix(in srgb, var(--color-surface) 6%, transparent);
        pointer-events:none;
    }
    .profile-hero-inner {
        position:relative;
        z-index:1;
        padding:1.75rem 2rem 5rem;
    }
    .profile-hero-title {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:1.3rem;
        font-weight:800;
        color:var(--color-on-solid);
        margin:0;
    }
    .profile-hero-sub {
        font-size:0.82rem;
        color:color-mix(in srgb, var(--color-on-solid) 70%, transparent);
        margin:0.2rem 0 0;
    }

    /* ── Avatar wrap ── */
    .profile-avatar-wrap {
        position:relative;
        display:inline-block;
        margin-top:-60px;
        margin-left:2rem;
        z-index:10;
    }
    .profile-avatar {
        width:120px; height:120px;
        object-fit:cover;
        border-radius:50%;
        border:4px solid var(--bg-card);
        box-shadow:0 0 0 4px var(--primary), 0 8px 32px var(--primary-glow);
        display:block;
        background:linear-gradient(135deg, var(--primary), var(--accent-violet));
    }
    .profile-avatar-init {
        width:120px; height:120px;
        border-radius:50%;
        background:linear-gradient(135deg, var(--primary), var(--accent-violet));
        display:flex; align-items:center; justify-content:center;
        font-size:2.5rem; font-weight:800; color:var(--color-on-solid);
        border:4px solid var(--bg-card);
        box-shadow:0 0 0 4px var(--primary), 0 8px 32px var(--primary-glow);
    }
    .avatar-online-ring {
        position:absolute;
        bottom:6px; right:6px;
        width:18px; height:18px;
        background:var(--success);
        border-radius:50%;
        border:3px solid var(--bg-card);
    }

    /* ── Profile card ── */
    .profile-card {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:20px;
        overflow:hidden;
        box-shadow:var(--shadow-sm);
        transition:background 0.4s ease;
    }
    .profile-meta-row {
        padding:1rem 2rem 1.5rem;
        border-bottom:1px solid var(--border);
    }
    .profile-name {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:1.4rem;
        font-weight:800;
        color:var(--text);
        margin-bottom:0.25rem;
    }
    .profile-email { font-size:0.875rem; color:var(--text-muted); }

    /* Role badge */
    .profile-role-badge {
        display:inline-flex; align-items:center; gap:0.4rem;
        padding:0.35em 0.85em;
        border-radius:20px;
        font-size:0.78rem; font-weight:700;
        margin-top:0.75rem;
    }
    .badge-midwife { background:linear-gradient(135deg, var(--primary), var(--accent-violet)); color:var(--color-on-solid); box-shadow:0 2px 10px var(--primary-glow); }
    .badge-bhw     { background:linear-gradient(135deg, var(--color-info), var(--color-info)); color:var(--color-on-solid); box-shadow:0 2px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent); }
    .badge-user    { background:linear-gradient(135deg, var(--secondary), var(--color-secondary-text)); color:var(--color-on-solid); box-shadow:0 2px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent); }
    .badge-staff   { background:linear-gradient(135deg, var(--color-surface-strong), var(--color-surface-strong)); color:var(--color-on-solid); box-shadow:0 2px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent); }

    /* Info grid */
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:0.85rem; padding:1.5rem 2rem; }
    .info-item {
        background:var(--bg-card2);
        border:1px solid var(--border);
        border-radius:14px;
        padding:1rem;
        transition:all 0.2s ease;
    }
    .info-item:hover { border-color:var(--primary); transform:translateY(-1px); }
    .info-label {
        font-size:0.7rem; text-transform:uppercase; letter-spacing:1px;
        color:var(--text-muted); font-weight:700; margin-bottom:0.3rem;
        display:flex; align-items:center; gap:0.4rem;
    }
    .info-label i { color:var(--primary-light); }
    .info-value { font-size:0.95rem; font-weight:600; color:var(--text); }

    /* Action bar */
    .profile-actions { padding:1.25rem 2rem; border-top:1px solid var(--border); display:flex; gap:0.75rem; flex-wrap:wrap; }

    @media (max-width: 600px) {
        .info-grid { grid-template-columns:1fr; }
        .profile-avatar-wrap { margin-left:1.25rem; }
        .profile-meta-row, .info-grid, .profile-actions { padding-left:1.25rem; padding-right:1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-9">

            <div class="profile-card fade-in-card">

                {{-- Hero Banner --}}
                <div class="profile-hero">
                    <div class="profile-hero-orb" style="width:220px;height:220px;top:-60px;right:-60px;"></div>
                    <div class="profile-hero-orb" style="width:140px;height:140px;bottom:-40px;left:30%;"></div>
                    <div class="profile-hero-inner">
                        <p class="profile-hero-title">My Profile</p>
                        <p class="profile-hero-sub">
                            <i class="bi bi-patch-check-fill me-1"></i>
                            ReproCare Member since {{ $user->created_at->format('F Y') }}
                        </p>
                    </div>
                </div>

                {{-- Avatar + Name Row --}}
                <div class="profile-avatar-wrap">
                    @if($user->profile_image_url && $user->hasProfileImage())
                        <img src="{{ $user->profile_image_url }}"
                             alt="{{ $user->name }}"
                             class="profile-avatar"
                             onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="profile-avatar-init" style="display:none;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @else
                        <div class="profile-avatar-init">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="avatar-online-ring"></div>
                </div>

                <div class="profile-meta-row" style="padding-top:0.75rem;">
                    <h2 class="profile-name">{{ $user->name }}</h2>
                    <div class="profile-email">
                        <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                        @php
                            $roleLabels = [
                                'midwife' => 'Midwife',
                                'bhw' => 'Barangay Health Worker',
                                'bhw_president' => 'BHW President',
                                'cho' => 'CHO Administrator',
                                'rhu' => 'RHU Staff',
                                'user' => 'Woman',
                            ];
                            $roleBadgeClasses = [
                                'midwife' => 'badge-midwife',
                                'bhw' => 'badge-bhw',
                                'bhw_president' => 'badge-bhw',
                                'cho' => 'badge-staff',
                                'rhu' => 'badge-staff',
                                'user' => 'badge-user',
                            ];
                            $roleIcons = [
                                'midwife' => 'bi-heart-pulse-fill',
                                'bhw' => 'bi-person-badge-fill',
                                'bhw_president' => 'bi-person-badge-fill',
                                'cho' => 'bi-building-fill',
                                'rhu' => 'bi-hospital-fill',
                                'user' => 'bi-person-heart-fill',
                            ];
                            $r = $user->role ?? 'user';
                        @endphp
                        <span class="profile-role-badge {{ $roleBadgeClasses[$r] ?? 'badge-staff' }}">
                            <i class="bi {{ $roleIcons[$r] ?? 'bi-person-fill' }}"></i>
                            {{ $roleLabels[$r] ?? ucfirst(str_replace('_', ' ', $r)) }}
                        </span>
                        @if($user->age)
                        <span style="background:var(--bg-card2);border:1px solid var(--border);border-radius:12px;padding:0.25em 0.75em;font-size:0.78rem;color:var(--text-muted);font-weight:600;">
                            <i class="bi bi-person me-1"></i>{{ $user->age }} years old
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Info Grid --}}
                <div class="info-grid">
                    @php
                        $fields = [
                            ['icon'=>'person-fill',      'label'=>'Full Name',       'val'=> $user->name],
                            ['icon'=>'gender-ambiguous', 'label'=>'Gender',          'val'=> $user->gender ? ucfirst($user->gender) : null],
                            ['icon'=>'telephone-fill',   'label'=>'Contact Number',  'val'=> $user->contact_number],
                            ['icon'=>'calendar-date',    'label'=>'Date of Birth',   'val'=> $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('F d, Y') : null],
                            ['icon'=>'house-fill',       'label'=>'Barangay',        'val'=> $user->barangay],
                        ];
                    @endphp
                    @foreach($fields as $f)
                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-{{ $f['icon'] }}"></i>
                            {{ $f['label'] }}
                        </div>
                        <div class="info-value">
                            {{ $f['val'] ?? '—' }}
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Action Bar --}}
                <div class="profile-actions">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="bi bi-pencil-fill me-1"></i> Edit Profile
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-house me-1"></i> Dashboard
                    </a>
                    @php
                        $settingsRoute = match(auth()->user()?->role) {
                            'cho' => route('cho.settings'),
                            'midwife' => route('midwife.settings'),
                            'rhu' => route('rhu.settings'),
                            'bhw' => route('bhw.settings'),
                            'bhw_president' => route('bhw-president.settings'),
                            default => route('user.settings'),
                        };
                    @endphp
                    <a href="{{ $settingsRoute }}" class="btn btn-outline-secondary">
                        <i class="bi bi-gear me-1"></i> Settings
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
