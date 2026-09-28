@php
    $currentUser = auth()->user();
    $currentRole = $currentUser?->role ?? null;
    $unreadNotifications = $currentUser?->notifications()->unread()->count() ?? 0;
    $dashboardRoute = match ($currentRole) {
        'cho' => route('cho.dashboard'),
        'rhu' => route('rhu.dashboard'),
        'user' => route('user.dashboard'),
        'midwife' => route('midwife.dashboard'),
        'bhw_president' => route('bhw-president.dashboard'),
        default => route('bhw.dashboard'),
    };
    // Profile lives inside Settings (My Profile section) — no standalone profile page.
    $settingsRoute = match ($currentRole) {
        'cho' => route('cho.settings'),
        'rhu' => route('rhu.settings'),
        'user' => route('user.settings'),
        'midwife' => route('midwife.settings'),
        'bhw_president' => route('bhw-president.settings'),
        default => route('bhw.settings'),
    };
    $roleDisplay = match ($currentRole) {
        'cho' => 'CHO Administrator',
        'rhu' => 'RHU Administrator',
        'midwife' => 'Midwife',
        'bhw_president' => 'BHW President',
        'bhw' => 'Barangay Health Worker',
        default => 'Patient / Client',
    };
    $roleBadgeBg = match ($currentRole) {
        'cho' => 'rgba(242, 115, 172, 0.12)',
        'rhu' => 'rgba(244, 114, 182, 0.12)',
        'midwife' => 'rgba(242, 115, 172, 0.12)',
        default => 'rgba(100, 116, 139, 0.12)',
    };
    $roleBadgeText = match ($currentRole) {
        'cho' => 'var(--color-primary-text)',
        'rhu' => 'var(--color-primary)',
        'midwife' => 'var(--color-primary-text)',
        default => 'var(--color-text-muted)',
    };
@endphp

@if($currentUser)
    @if($currentRole === 'user')
        @include('includes.women-navigation')
    @else
<style>
    /* Staff navbar stays a single fixed top row at any zoom or width. */
    .navbar { flex-wrap:nowrap !important; }
    .navbar .container-fluid { flex-wrap:nowrap !important; gap:0.5rem; }
    .navbar .navbar-brand { white-space:nowrap !important; flex-shrink:1; min-width:0; font-size:clamp(1rem, 2.5vw + 0.6rem, 1.25rem); }
    .navbar .navbar-brand > span:last-child { overflow:hidden; text-overflow:ellipsis; }
    .navbar .d-flex.align-items-center.gap-2.ms-auto { flex-shrink:0; margin-left:auto; }
    /* Circles stay circles: exact squares that clip any inner content. */
    .navbar .staff-action-btn, .navbar #sidebarToggleBtn, .navbar .staff-profile-pill { flex-shrink:0 !important; align-self:center !important; }
    .navbar .staff-action-btn {
        width:30px !important; height:30px !important;
        min-width:30px !important; max-width:30px !important;
        min-height:30px !important; max-height:30px !important;
        aspect-ratio:1 / 1 !important;
        padding:0 !important; overflow:hidden !important;
        border-radius:50% !important;
        line-height:1 !important;
        display:inline-flex !important; align-items:center !important; justify-content:center !important;
    }
    .navbar .staff-action-btn i { flex-shrink:0; font-size:13px !important; line-height:1 !important; }
    /* Keep the unread dot visible: it sits half-outside the bell button. */
    .navbar a.staff-action-btn.position-relative { overflow:visible !important; }
    /* Brand accent: lavender Care everywhere this navbar renders. */
    .navbar .navbar-brand .brand-care { color:#9B64B9 !important; }
    /* Bootstrap adds its own caret to .dropdown-toggle — the pill has its own chevron. */
    .navbar .staff-profile-pill.dropdown-toggle::after { display:none !important; }
    /* Staff actions match the patient portal's circular bordered buttons. */
    :root:not([data-theme="dark"]) .navbar .rc-theme-toggle .icon-sun { display:none !important; }
    [data-theme="dark"] .navbar .rc-theme-toggle .icon-moon { display:none !important; }
    @media (max-width: 576px) {
        .navbar { height:58px !important; }
        .navbar .container-fluid { gap:0.375rem !important; padding-left:0.6rem !important; padding-right:0.6rem !important; }
        .navbar .navbar-brand { gap:2px !important; }
        .navbar .navbar-brand > span:first-child { width:30px !important; height:30px !important; flex-basis:30px !important; }
        .navbar .navbar-brand > span:first-child img { width:30px !important; height:30px !important; }
        .navbar .navbar-brand > span:last-child { font-size:0.95rem !important; letter-spacing:-0.2px !important; }
        .navbar #sidebarToggleBtn { width:32px !important; height:32px !important; }
        .navbar .staff-action-btn {
            width:28px !important; height:28px !important;
            min-width:28px !important; max-width:28px !important;
            min-height:28px !important; max-height:28px !important;
        }
        .navbar .staff-action-btn i { font-size:12px !important; }
        .navbar .staff-profile-pill { padding:2px !important; gap:0 !important; }
        .navbar .staff-profile-pill img { width:30px !important; height:30px !important; }
        .navbar .staff-profile-pill.dropdown-toggle::after { display:none !important; }
        .navbar .staff-profile-pill .dropdown-chevron { display:none !important; }
    }
</style>
<nav class="navbar navbar-expand-lg border-bottom" style="background:var(--color-surface); border-color:var(--color-border) !important; height:64px;">
    <div class="container-fluid px-3">
        {{-- Brand & Sidebar Toggle --}}
        <div class="d-flex align-items-center gap-2">
            <button id="sidebarToggleBtn"
                    class="btn btn-sm btn-light border d-flex d-lg-none align-items-center justify-content-center"
                    style="width:32px; height:32px; border-radius:9px; color:var(--color-text-muted); background:var(--color-surface-soft);"
                    aria-label="Toggle sidebar">
                <i class="bi bi-list fs-5"></i>
            </button>
            <a class="navbar-brand d-flex align-items-center m-0 p-0 text-decoration-none" href="{{ $dashboardRoute }}" style="gap:4px; line-height:1;">
                <span aria-hidden="true" style="width:30px; height:30px; display:flex; align-items:center; justify-content:center; overflow:visible; flex:0 0 30px;">
                    <img src="{{ asset('images/brand/reprocare-logo.png?v=5') }}" alt=""
                         style="width:30px; height:30px; max-width:none; object-fit:contain; display:block;">
                </span>
                <span class="fw-800 d-inline-flex align-items-center" style="min-height:30px; font-family:'Plus Jakarta Sans',sans-serif; color:var(--text); font-size:0.95rem; letter-spacing:-0.4px; line-height:1; padding-bottom:1px;">
                    Repro<span class="brand-care">Care</span>
                </span>
            </a>
        </div>

        {{-- Right Side Actions --}}
        <div class="d-flex align-items-center gap-2 ms-auto">
            {{-- Offline / sync status (PWA field support) --}}
            <span id="pwa-sync-pill" style="display:none;align-items:center;gap:.4rem;background:var(--color-secondary-soft);border:1px solid var(--color-secondary-soft);color:var(--color-secondary-text);font-size:.74rem;font-weight:700;padding:.4rem .8rem;border-radius:999px;white-space:nowrap;"></span>

            {{-- Global Dark Mode Toggle (all portals) --}}
            <button type="button" class="btn btn-sm btn-light border d-flex align-items-center justify-content-center rc-theme-toggle staff-action-btn"
               style="width:30px;height:30px;min-width:30px;max-width:30px;min-height:30px;max-height:30px;aspect-ratio:1/1;padding:0;overflow:hidden;line-height:1;border-radius:50%;color:var(--color-text-muted);background:var(--color-surface);border:1px solid var(--color-border) !important;"
               title="Toggle dark mode"
               onclick="setTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark')">
                <i class="bi bi-moon-fill icon-moon fs-6"></i>
                <i class="bi bi-sun-fill icon-sun fs-6"></i>
            </button>

            {{-- System Alerts / Notification Bell --}}
            @php
                $notifRoute = match($currentRole) {
                    'cho' => route('cho.dashboard'),
                    'rhu' => route('rhu.dashboard'),
                    'midwife' => route('midwife.notifications.index'),
                    'bhw_president' => route('bhw-president.dashboard'),
                    'bhw' => route('bhw.notifications.index'),
                    default => route('user.notifications'),
                };
            @endphp
            <a href="{{ $notifRoute }}" class="btn btn-sm btn-light border position-relative d-flex align-items-center justify-content-center staff-action-btn"
               style="width:30px;height:30px;min-width:30px;max-width:30px;min-height:30px;max-height:30px;aspect-ratio:1/1;padding:0;overflow:hidden;line-height:1;border-radius:50%;color:var(--color-text-muted);background:var(--color-surface);border:1px solid var(--color-border) !important;"
               title="System Alerts & Notifications">
                <i class="bi bi-bell fs-6"></i>
                @if($unreadNotifications > 0)
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                        <span class="visually-hidden">New alerts</span>
                    </span>
                @endif
            </a>

            {{-- User Avatar Dropdown --}}
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-1 text-decoration-none staff-profile-pill"
                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                   style="border:1px solid var(--color-border); border-radius:9999px; padding:2px 10px 2px 2px !important;">
                    <img src="{{ $currentUser->profile_image_url }}"
                         alt="{{ $currentUser->name }}"
                         class="rounded-circle border"
                         style="width:28px; height:28px; object-fit:cover; border-color:var(--color-border);"
                         onerror="this.onerror=null;this.src='{{ $currentUser->gender === 'male' ? '/images/avatars/avatar-male.svg' : '/images/avatars/avatar-female.svg' }}';">
                    <div class="d-none d-lg-block text-start lh-1">
                        <div class="fw-700 text-truncate" style="max-width:110px; font-size:0.84rem; color:var(--text);">
                            {{ $currentUser->first_name }}
                        </div>
                        <small class="text-muted" style="font-size:0.7rem;">{{ ucfirst($currentRole) }}</small>
                    </div>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2" style="border-radius:14px; min-width:210px; font-size:0.85rem;">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-700 text-dark">{{ $currentUser->name }}</div>
                        <div class="text-muted text-xs">{{ $currentUser->email }}</div>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ $settingsRoute }}">
                            <i class="bi bi-person-circle text-primary"></i> My Profile
                        </a>
                    </li>
                    @if($currentRole === 'cho')
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('cho.settings') }}">
                                <i class="bi bi-gear text-primary"></i> System Settings
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('cho.archived.index') }}">
                                <i class="bi bi-archive text-secondary"></i> Archived Records
                            </a>
                        </li>
                    @endif
                    <li class="border-top">
                        <form action="{{ route('logout') }}" method="POST" class="js-logout-form" data-user-name="{{ $currentUser->first_name ?? $currentUser->name }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger py-2 d-flex align-items-center gap-2">
                                <i class="bi bi-box-arrow-right"></i> Log Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
    @endif
@endif
