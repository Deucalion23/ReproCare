@php
    $currentUser = auth()->user();
    $unreadNotifications = $currentUser?->notifications()->unread()->count() ?? 0;
    $unreadMessages = \App\Models\Message::where('receiver_id', $currentUser?->id)
        ->where('is_read', false)
        ->count();
    $isCareRecordsActive = request()->routeIs('user.checkups*') || request()->routeIs('user.health-records*');
@endphp

<!-- ================================================================
     REPROCARE WOMEN & PATIENT PORTAL NAVIGATION HEADER
     Senior Frontend Engineer Overhaul:
     - 3-Part Structural Flexbox Layout (Left: Logo, Center: Tabs, Right: Actions)
     - flex-shrink-0 and ml-auto on Action Container to eliminate collisions
     - Strict single-line alignment (white-space: nowrap)
     - Symmetrical 36px height parity, 13-14px font, and 16-17px icon sizing
     - Responsive action spacing before tablet drawer mode
     ================================================================ -->
<style>
    /* ── Layout & Variables (rem-based so browser zoom / root font scales everything) ── */
    :root {
        --nav-bar-h:auto;
        --nav-bar-min-h:3.75rem; /* 60px @ 16px root — was 72px, slimmed to match smaller brand */
        --nav-btn-h:2.25rem; /* 36px pill — brand is intentionally smaller */
        --nav-brand-sz:1.5rem; /* 24px logo — smaller than the 36px action pill */
        --nav-brand-title-sz:clamp(0.9rem, 0.85rem + 0.4vw, 1rem);
        --nav-font-sz:13.5px;
        --nav-icon-sz:16px;
        --nav-btn-r:9999px; /* Pill Shape */


        --nav-border:var(--color-border);
        --nav-slate-900:var(--color-surface-strong);  /* Obsidian Charcoal */
        --nav-slate-600:var(--color-text-muted);
        --nav-slate-500:var(--color-text-muted);
        --nav-slate-100:var(--color-secondary-soft);
        --nav-slate-800:var(--color-secondary-text);

        --nav-primary:var(--color-secondary-text);
        --nav-primary-dk:var(--color-secondary-text);
        --nav-rose:var(--color-secondary);
        --nav-rose-dark:var(--color-secondary-text);
        --nav-teal:var(--color-info-text);
        --nav-teal-dark:var(--color-info-text);
        --nav-login-lavender:#9B6CB8;
        --nav-login-lavender-hover:#8958A8;
        --nav-hover-bg:#DCC9EE;
        --nav-hover-text:#74499E;
        --nav-hover-border:#C6A9E2;
    }

    /* ── 1. Structural Parent Container ── */
    .women-navbar {
        width:100% !important;
        min-height:var(--nav-bar-min-h) !important;
        background:#F1F3F5 !important;
        background-color:#F1F3F5 !important;
        border-bottom:1px solid var(--nav-border) !important;
        position:fixed;
        top:0;
        left:0;
        right:0;
        z-index:1025;
        display:flex;
        align-items:center;
        box-shadow:0 1px 2px 0 color-mix(in srgb, rgb(var(--color-shadow-rgb)) 3%, transparent);
    }

    /* Preserve the portal's dark-mode surface while the light theme uses a
       soft charcoal-gray navigation background. */
    :root[data-theme="light"][data-bs-theme] body .women-navbar,
    body .women-navbar {
        background:#F1F3F5 !important;
        background-color:#F1F3F5 !important;
    }

    [data-theme="dark"] .women-navbar {
        background:var(--color-surface) !important;
        background-color:var(--color-surface) !important;
    }

    .women-nav-container {
        width:100% !important;
        max-width:1600px;
        min-height:var(--nav-bar-min-h);
        margin-left:auto;
        margin-right:auto;
        padding:0.7rem 1.5rem;
        display:flex !important;
        align-items:center !important;
        justify-content:space-between !important;
        gap:1.25rem;
    }

    /* ── 2. SECTION 1: Left Brand ── */
    .women-nav-left {
        display:flex !important;
        align-items:center !important;
        gap:0.5rem;
        flex-shrink:0 !important;
        margin-right:0.75rem;
        user-select:none;
    }

    .women-brand-link {
        display:flex;
        align-items:center;
        gap:0.125rem;
        text-decoration:none;
        line-height:1;
        min-width:0;
    }

    .women-brand-mark {
        width:var(--nav-brand-sz);
        height:var(--nav-brand-sz);
        display:flex;
        align-items:center;
        justify-content:center;
        overflow:visible;
        flex:0 0 var(--nav-brand-sz);
        transform:translateY(-1.5px);
    }

    .women-brand-mark img {
        width:100% !important;
        height:100% !important;
        max-width:100%;
        object-fit:contain;
        display:block;
        transform:none !important;
        margin:0;
    }

    .women-brand-icon {
        width:36px;
        height:36px;
        border-radius:var(--nav-btn-r);
        background:linear-gradient(135deg, var(--nav-primary) 0%, var(--nav-primary-dk) 100%);
        display:flex;
        align-items:center;
        justify-content:center;
        color:var(--color-on-solid);
        box-shadow:0 8px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 18%, transparent);
        flex-shrink:0;
    }

    .women-brand-title {
        display:inline-flex;
        align-items:center;
        min-height:var(--nav-btn-h);
        font-family:'Plus Jakarta Sans',sans-serif;
        font-weight:800;
        font-size:var(--nav-brand-title-sz);
        letter-spacing:-0.025em;
        color:var(--nav-slate-900);
        line-height:1;
        white-space:nowrap;
        padding-bottom:1px;
        margin-left:-0.25rem;
        transform:translateX(0);
        max-width:100%;
        overflow:hidden;
        text-overflow:ellipsis;
    }

    .women-brand-title span {
        color:#B7A8D3;
    }

    /* ── 3. SECTION 2: Center Primary Links ── */
    .women-nav-center {
        display:flex !important;
        align-items:center !important;
        justify-content:safe center !important; /* stays centered, but overflows right instead of covering the brand */
        flex:1 1 auto;
        min-width:0;
        padding-left:0.25rem;
        padding-right:0.25rem;
        overflow:visible; /* allow dropdown to display */
    }

    .women-nav-tabs {
          display:flex !important;
          align-items:center !important;
          gap:6px;
          list-style:none;
          margin:0;
          padding:0;
          overflow:visible; /* ensure dropdown not clipped */
      }

    .women-nav-item {
        position:relative;
        flex-shrink:0;
    }

    /* Tab Link Geometry & Typography */
    .women-tab-link {
          display:inline-flex !important;
          align-items:center !important;
          gap:5px;
          height:var(--nav-btn-h) !important;
          padding:0 11px !important;
          border-radius:var(--nav-btn-r) !important;
          font-size:13px !important;
          font-weight:600 !important;
          color:var(--nav-slate-600) !important;
          text-decoration:none !important;
          white-space:nowrap !important;
          background:transparent;
          border:1px solid transparent;
          cursor:pointer;
          transition:all 0.18s ease-in-out;
          user-select:none;
          line-height:1;
      }

    .women-tab-link i.nav-icon {
        font-size:var(--nav-icon-sz) !important;
        line-height:1;
        color:var(--nav-slate-500);
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:var(--nav-icon-sz);
        height:var(--nav-icon-sz);
        transition:color 0.18s ease-in-out;
    }

    /* Hover State (Inactive) */
    .women-tab-link:not(.active):hover {
        background:var(--nav-slate-100) !important;
        border-color:transparent !important;
        color:var(--nav-primary) !important;
    }

    .women-tab-link:not(.active):hover i.nav-icon {
        color:var(--nav-primary) !important;
    }

    /* A restrained charcoal hover is visible against the soft-gray header. */
    html:not([data-theme="dark"]) .women-tab-link:not(.active):hover {
        background:#D1D5DB !important;
        border-color:transparent !important;
        color:#1F2937 !important;
    }

    html:not([data-theme="dark"]) .women-tab-link:not(.active):hover i.nav-icon {
        color:#334155 !important;
    }

    /* Active Pill State */
    .women-tab-link.active {
        background:var(--color-surface-strong) !important;
        border-color:var(--color-surface-strong) !important;
        color:var(--color-on-solid) !important;
        font-weight:700 !important;
        box-shadow:0 2px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);
    }

    .women-tab-link.active i.nav-icon {
        color:var(--color-on-solid) !important;
    }

    /* Keep the selected tab stable when hovered. */
    .women-tab-link.active:hover {
        background:var(--color-surface-strong) !important;
        border-color:var(--color-surface-strong) !important;
        color:var(--color-on-solid) !important;
    }

    .women-tab-link.active:hover i.nav-icon {
        color:var(--color-on-solid) !important;
    }

    /* Unread Tab Pill */
    .women-tab-badge {
        font-size:0.65rem;
        font-weight:800;
        background:var(--color-danger-text);
        color:var(--color-on-solid);
        padding:1px 5px;
        border-radius:9999px;
        margin-left:2px;
        line-height:1.1;
    }

    /* Dropdown Care Records */
    .dropdown-chevron {
        font-size:10px;
        margin-left:2px;
        opacity:0.65;
        transition:transform 0.2s ease;
    }

    .dropdown.show .dropdown-chevron {
        transform:rotate(180deg);
    }

    .women-nav-dropdown-menu {
          border-radius:12px;
          border:1px solid var(--nav-border);
          box-shadow:0 10px 25px -5px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent), 0 8px 10px -6px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent);
          padding:6px;
          min-width:190px;
          margin-top:6px !important;
          background:var(--color-surface);
          z-index:1055; /* above other elements */
      }

    .women-dropdown-item {
        display:flex;
        align-items:center;
        gap:9px;
        padding:8px 12px;
        border-radius:6px;
        font-size:13.5px;
        font-weight:500;
        color:var(--nav-slate-600);
        text-decoration:none;
        white-space:nowrap;
        transition:all 0.15s ease;
    }

    .women-dropdown-item i {
        font-size:15px;
        color:var(--nav-slate-500);
    }

.women-dropdown-item:hover, .women-dropdown-item.active {
        background:var(--nav-slate-100) !important;
        color:var(--nav-slate-900) !important;
        font-weight:600;
    }

    html:not([data-theme="dark"]) .women-dropdown-item:hover {
        background:#D1D5DB !important;
        color:#1F2937 !important;
    }

    html:not([data-theme="dark"]) .women-dropdown-item:hover i {
        color:#334155 !important;
    }

    /* Keep both Care Records menu entries in the same dark slate used by the navigation. */
    .women-nav-dropdown-menu .women-dropdown-item,
    .women-nav-dropdown-menu .women-dropdown-item.active,
    .women-nav-dropdown-menu .women-dropdown-item i,
    .women-nav-dropdown-menu .women-dropdown-item.active i {
        color:var(--nav-slate-600) !important;
    }

    .women-nav-dropdown-menu .women-dropdown-item:hover,
    .women-nav-dropdown-menu .women-dropdown-item:hover i {
        background:var(--nav-slate-100) !important;
        color:var(--nav-primary) !important;
    }

    .women-dropdown-item:hover i, .women-dropdown-item.active i {
        color:var(--nav-teal) !important;
    }

    /* Dark mode needs its own navigation states: the shared `surface-strong`
       token is the same shade as the dark navbar, which made the selected tab
       disappear and left hovered dropdown labels too muted to read. */
    [data-theme="dark"] .women-tab-link:not(.active):hover {
        background:var(--color-surface-soft) !important;
        border-color:var(--color-border) !important;
        color:var(--color-text) !important;
    }

    [data-theme="dark"] .women-tab-link:not(.active):hover i.nav-icon {
        color:var(--color-text) !important;
    }

    [data-theme="dark"] .women-tab-link.active,
    [data-theme="dark"] .women-tab-link.active:hover {
        background:#0F172A !important;
        border-color:#0F172A !important;
        color:#FFFFFF !important;
    }

    [data-theme="dark"] .women-tab-link.active i.nav-icon,
    [data-theme="dark"] .women-tab-link.active:hover i.nav-icon {
        color:#FFFFFF !important;
    }

    [data-theme="dark"] .women-dropdown-item:hover,
    [data-theme="dark"] .women-dropdown-item.active {
        background:var(--color-surface-soft) !important;
        color:var(--color-text) !important;
    }

    [data-theme="dark"] .women-dropdown-item:hover i,
    [data-theme="dark"] .women-dropdown-item.active i {
        color:var(--color-text) !important;
    }

    /* ── 4. SECTION 3: Right Actions Container ── */
    .women-nav-right {
        display:flex !important;
        align-items:center !important;
        justify-content:flex-end !important;
        gap:0.8rem;
        flex-shrink:0 !important;
        margin-left:auto !important;
    }

    /* Care Support Button */
    .btn-care-support {
        display:inline-flex !important;
        align-items:center !important;
        gap:6px;
        height:var(--nav-btn-h) !important;
        padding:0 12px !important;
        background:#F1F5F9 !important;
        border:none !important;
        color:#743AFF !important;
        border-radius:var(--nav-btn-r) !important;
        font-size:var(--nav-font-sz) !important;
        font-weight:600 !important;
        text-decoration:none !important;
        white-space:nowrap !important;
        cursor:pointer;
        flex-shrink:0 !important;
        transition:all 0.15s ease;
        line-height:1;
    }

    .btn-care-support i {
        font-size:14px;
        color:#743AFF;
    }

    .btn-care-support:hover {
        background:#E8EEF5 !important;
        border-color:transparent !important;
        color:#6431DE !important;
        box-shadow:0 8px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent);
    }

    .btn-care-support:hover i {
        color:#6431DE !important;
    }

    /* Bell Button */
    .women-bell-btn {
        width:var(--nav-btn-h) !important;
        height:var(--nav-btn-h) !important;
        border-radius:var(--nav-btn-r) !important;
        background:var(--color-surface) !important;
        border:1px solid var(--nav-border) !important;
        display:flex !important;
        align-items:center !important;
        justify-content:center !important;
        color:var(--nav-slate-600) !important;
        text-decoration:none !important;
        position:relative;
        flex-shrink:0 !important;
        transition:all 0.15s ease;
    }

    .women-bell-btn i {
        font-size:15px;
    }

    .women-bell-btn:hover {
        background:var(--nav-slate-100) !important;
        color:var(--nav-slate-900) !important;
        border-color:var(--color-info-soft);
    }

    .women-bell-dot {
        position:absolute;
        top:-3px;
        right:-3px;
        min-width:16px;
        height:16px;
        padding:0 3px;
        border-radius:9999px;
        background:var(--color-danger-text);
        color:var(--color-on-solid);
        font-size:0.62rem;
        font-weight:800;
        display:flex;
        align-items:center;
        justify-content:center;
        border:2px solid var(--color-border);
    }

    /* Profile Pill */
    .women-profile-pill {
        display:inline-flex !important;
        align-items:center !important;
        gap:7px;
        height:var(--nav-btn-h) !important;
        padding:0 8px 0 3px !important;
        background:var(--color-surface) !important;
        border:1px solid var(--nav-border) !important;
        border-radius:9999px !important;
        text-decoration:none !important;
        cursor:pointer;
        flex-shrink:0 !important;
        transition:all 0.15s ease;
        user-select:none;
    }

    .women-profile-pill:hover {
        background:var(--nav-slate-100) !important;
        border-color:var(--color-info-soft);
    }

    .women-profile-avatar {
        width:28px;
        height:28px;
        border-radius:50%;
        object-fit:cover;
        border:1.5px solid var(--nav-rose);
        flex-shrink:0;
    }

    .women-profile-name {
        font-size:var(--nav-font-sz) !important;
        font-weight:600;
        color:var(--nav-slate-900);
        white-space:nowrap;
    }

    /* Mobile Drawer Toggle */
    .women-mobile-toggle {
        display:none;
        width:var(--nav-btn-h);
        height:var(--nav-btn-h);
        border-radius:var(--nav-btn-r);
        background:var(--color-surface);
        border:1px solid var(--nav-border);
        align-items:center;
        justify-content:center;
        color:var(--nav-slate-600);
        font-size:1.25rem;
        cursor:pointer;
        flex-shrink:0 !important;
    }

    html:not([data-theme="dark"]) .women-mobile-toggle {
        background:#E5E7EB !important;
        color:#334155 !important;
        border-color:transparent !important;
    }

    html:not([data-theme="dark"]) #womenMobileDrawer {
        background:#F1F3F5 !important;
    }

    /* ── Responsive Adaptations ── */
    @media (max-width: 1320px) {
        .women-tab-link {
            padding:0 8px !important;
            font-size:13px !important;
        }
        .btn-care-support span {
            display:none;
        }
        .btn-care-support {
            width:var(--nav-btn-h);
            padding:0 !important;
            justify-content:center;
        }
    }

    @media (max-width: 1260px) {
        .women-nav-center {
            display:none !important;
        }
        .women-mobile-toggle {
            display:flex !important;
        }
    }

    @media (max-width: 640px) {
        .women-navbar { --nav-bar-min-h:3.25rem; --nav-brand-sz:1.375rem; --nav-brand-title-sz:0.92rem; }
        .women-nav-container {
            padding-left:0.85rem;
            padding-right:0.85rem;
            padding-top:0.5rem;
            padding-bottom:0.5rem;
            gap:0.5rem;
        }
        .women-nav-left { margin-right:0; gap:0.35rem; min-width:0; }
        .women-brand-link { gap:0.125rem; }
        .women-brand-mark { flex:0 0 var(--nav-brand-sz); width:var(--nav-brand-sz); height:var(--nav-brand-sz); transform:translateY(-1.5px) !important; }
        .women-brand-mark img { width:100% !important; height:100% !important; }
        .women-brand-title {
            font-size:var(--nav-brand-title-sz);
            min-height:2rem;
            margin-left:-0.2rem;
        }
        .women-nav-right { gap:0.45rem; margin-left:auto !important; }
        /* Hide the round Care Support button on phones — it lives in the drawer. */
        .women-nav-right .btn-care-support {
            display:none !important;
        }
        .women-bell-btn {
            width:32px !important;
            height:32px !important;
        }
        .women-bell-btn i { font-size:13px; }
        .women-profile-pill {
            height:32px !important;
            padding:0 6px 0 2px !important;
            gap:4px;
        }
        .women-profile-avatar { width:24px; height:24px; }
        .women-profile-pill .bi-chevron-down { display:none; }
        .women-mobile-toggle { width:32px; height:32px; font-size:1.05rem; }
        .btn-care-support span {
            display:none;
        }
        .btn-care-support {
            width:var(--nav-btn-h);
            padding:0 !important;
            justify-content:center;
        }
    }

    @media (max-width: 380px) {
        .women-navbar { --nav-brand-sz:1.25rem; --nav-brand-title-sz:0.85rem; }
        .women-brand-title { font-size:var(--nav-brand-title-sz); }
        .women-nav-right { gap:0.35rem; }
        .women-bell-btn { width:30px !important; height:30px !important; }
        .women-profile-pill { height:30px !important; }
        .women-mobile-toggle { width:30px; height:30px; }
    }
</style>

<nav class="women-navbar w-full bg-white border-b border-slate-200 sticky top-0 z-50">
    <div class="women-nav-container flex items-center justify-between w-full mx-auto">

        <!-- ============================================================
             SECTION 1 (LEFT): Brand Logo & Title
             ============================================================ -->
        <div class="women-nav-left flex items-center gap-2.5 flex-shrink-0">
            <a href="{{ route('user.dashboard') }}" class="women-brand-link flex items-center" title="ReproCare Home">
                <span class="women-brand-mark" aria-hidden="true"><img src="{{ asset('images/brand/reprocare-logo.png?v=5') }}" alt=""></span>
                <span class="women-brand-title font-extrabold text-slate-800">Repro<span>Care</span></span>
            </a>
        </div>

        <!-- ============================================================
             SECTION 2 (CENTER): Primary Navigation Tabs (Single-line, Symmetric)
             ============================================================ -->
        <div class="women-nav-center flex items-center justify-center flex-1 min-w-0">
            <ul class="women-nav-tabs flex items-center gap-1.5 m-0 p-0 list-none">

                {{-- Dashboard --}}
                <li class="women-nav-item">
                    <a href="{{ route('user.dashboard') }}"
                       class="women-tab-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-fill nav-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- My Pregnancy --}}
                <li class="women-nav-item">
                    <a href="{{ route('user.pregnancies.index') }}"
                       class="women-tab-link {{ request()->routeIs('user.pregnancies.*') ? 'active' : '' }}">
                        <i class="bi bi-heart-pulse-fill nav-icon"></i>
                        <span>My Pregnancy</span>
                    </a>
                </li>

                {{-- Cycle & Period --}}
                <li class="women-nav-item">
                    <a href="{{ route('user.menstruation.index') }}"
                       class="women-tab-link {{ request()->routeIs('user.menstruation.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar2-heart-fill nav-icon"></i>
                        <span>Cycle &amp; Period</span>
                    </a>
                </li>

                {{-- Care Records Dropdown Group (Checkups + Health Records) --}}
                <li class="women-nav-item dropdown">
                    <button class="women-tab-link dropdown-toggle border-0 {{ $isCareRecordsActive ? 'active' : '' }}"
                            type="button"
                            id="careRecordsDropdown"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <i class="bi bi-folder2-open nav-icon"></i>
                        <span>Care Records</span>
                        <i class="bi bi-chevron-down dropdown-chevron"></i>
                    </button>
                    <ul class="dropdown-menu women-nav-dropdown-menu" aria-labelledby="careRecordsDropdown">
                        <li>
                            <a class="dropdown-item women-dropdown-item {{ request()->routeIs('user.checkups*') ? 'active' : '' }}"
                               href="{{ route('user.checkups') }}">
                                <i class="bi bi-clipboard2-pulse-fill"></i>
                                <span>Clinic Checkups</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item women-dropdown-item {{ request()->routeIs('user.health-records*') ? 'active' : '' }}"
                               href="{{ route('user.health-records') }}">
                                <i class="bi bi-file-earmark-medical-fill"></i>
                                <span>Health Records</span>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Messages with Care Team --}}
                <li class="women-nav-item">
                    <a href="{{ route('user.messages.index') }}"
                       class="women-tab-link {{ request()->routeIs('user.messages.*') ? 'active' : '' }}">
                        <i class="bi bi-chat-heart-fill nav-icon"></i>
                        <span>Messages</span>
                        @if($unreadMessages > 0)
                            <span class="women-tab-badge">{{ $unreadMessages }}</span>
                        @endif
                    </a>
                </li>

                {{-- Learning --}}
                <li class="women-nav-item">
                    <a href="{{ route('learning.index') }}"
                       class="women-tab-link {{ request()->routeIs('learning.*') ? 'active' : '' }}">
                        <i class="bi bi-mortarboard-fill nav-icon"></i>
                        <span>Learning</span>
                    </a>
                </li>

                {{-- Community Forum --}}
                <li class="women-nav-item">
                    <a href="{{ route('forum.index') }}"
                       class="women-tab-link {{ request()->routeIs('forum.*') ? 'active' : '' }}">
                        <i class="bi bi-chat-quote-fill nav-icon"></i>
                        <span>Community</span>
                    </a>
                </li>

            </ul>
        </div>

        <!-- ============================================================
             SECTION 3 (RIGHT): Action Buttons & Profile (flex-shrink-0, ml-auto)
             ============================================================ -->
        <div class="women-nav-right flex items-center gap-2.5 flex-shrink-0 ml-auto">

            {{-- Offline / sync status (PWA field support) --}}
            <span id="pwa-sync-pill" style="display:none;align-items:center;gap:.4rem;background:var(--color-secondary-soft);border:1px solid var(--color-secondary-soft);color:var(--color-secondary-text);font-size:.74rem;font-weight:700;padding:.4rem .8rem;border-radius:999px;white-space:nowrap;"></span>

            {{-- Care Support Action Button --}}
            <button type="button"
                    class="btn-care-support flex items-center gap-1.5 flex-shrink-0"
                    data-bs-toggle="modal"
                    data-bs-target="#careEmergencyModal"
                    title="Health Center & Emergency Support">
                <i class="bi bi-telephone-plus-fill"></i>
                <span>Care Support</span>
            </button>

            {{-- Global Dark Mode Toggle --}}
            <button type="button"
                    class="women-bell-btn rc-theme-toggle flex items-center justify-center flex-shrink-0"
                    title="Toggle dark mode"
                    onclick="setTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark')">
                <i class="bi bi-moon-fill icon-moon"></i>
                <i class="bi bi-sun-fill icon-sun"></i>
            </button>

            {{-- Notification Bell --}}
            <a href="{{ route('user.notifications') }}"
               class="women-bell-btn flex items-center justify-center flex-shrink-0"
               title="My Notifications">
                <i class="bi bi-bell-fill"></i>
                @if($unreadNotifications > 0)
                    <span class="women-bell-dot">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                @endif
            </a>

            {{-- User Profile Menu --}}
            <div class="dropdown flex-shrink-0">
                <div class="women-profile-pill flex items-center gap-1.5" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                    <img src="{{ $currentUser->profile_image_url }}"
                         alt="{{ $currentUser->name }}"
                         class="women-profile-avatar"
                         onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                    <span class="women-profile-name d-none d-sm-inline font-medium text-slate-800">{{ $currentUser->first_name }}</span>
                    <i class="bi bi-chevron-down text-slate-400" style="font-size:10px;"></i>
                </div>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2" style="border-radius:12px; min-width:210px; font-size:0.88rem; padding:6px;">
                    <li class="px-3 py-2 border-bottom mb-1">
                        <div class="fw-700 text-dark">{{ $currentUser->name }}</div>
                        <div class="text-muted text-xs">{{ $currentUser->email }}</div>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center gap-2 rounded-2 {{ request()->routeIs('user.settings') ? 'active bg-light' : '' }}" href="{{ route('user.settings') }}">
                            <i class="bi bi-gear-fill" style="color:var(--nav-rose-dark);"></i> Settings &amp; Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="js-logout-form" data-user-name="{{ $currentUser->first_name ?? $currentUser->name }}">
                            @csrf
                            <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger rounded-2">
                                <i class="bi bi-box-arrow-right"></i> Log Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            {{-- Mobile Drawer Trigger --}}
            <button class="women-mobile-toggle flex items-center justify-center flex-shrink-0"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#womenMobileDrawer"
                    aria-controls="womenMobileDrawer"
                    aria-label="Toggle navigation menu">
                <i class="bi bi-list"></i>
            </button>

        </div>

    </div>
</nav>

{{-- ═══════════════════════════════════════════════
     MOBILE OFFCANVAS DRAWER (Preserved for small screens)
   ═══════════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="womenMobileDrawer" aria-labelledby="womenMobileDrawerLabel" style="border-radius:0 20px 20px 0; background:var(--color-peach-soft);">
    <div class="offcanvas-header border-bottom px-4 py-3">
        <div class="d-flex align-items-center" style="gap:0.125rem;">
            <span aria-hidden="true" style="width:var(--nav-brand-sz); height:var(--nav-brand-sz); display:flex; align-items:center; justify-content:center; overflow:visible; flex:0 0 var(--nav-brand-sz); transform:translateY(-1.5px);"><img src="{{ asset('images/brand/reprocare-logo.png?v=5') }}" alt="" style="width:100%; height:100%; max-width:100%; object-fit:contain; display:block;"></span>
            <div style="margin-left:-0.25rem; min-width:0;">
                <h6 class="offcanvas-title fw-800 text-dark mb-0 d-flex align-items-center" style="min-height:var(--nav-btn-h); line-height:1; padding-bottom:1px; font-size:var(--nav-brand-title-sz);" id="womenMobileDrawerLabel">Repro<span style="color:#B7A8D3;">Care</span></h6>
                <small class="text-muted" style="font-size:0.72rem;">Mother &amp; Patient Portal</small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-4">
        {{-- Mother Info Card --}}
        <div class="d-flex align-items-center gap-3 p-3 rounded-4 mb-4" style="background:var(--color-surface); border:1px solid var(--color-border);">
            <img src="{{ $currentUser->profile_image_url }}"
                 alt="{{ $currentUser->name }}"
                 class="rounded-circle"
                 style="width:44px; height:44px; object-fit:cover; border:2px solid var(--nav-rose);"
                 onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
            <div class="flex-grow-1">
                <div class="fw-700 text-dark" style="font-size:0.95rem;">{{ $currentUser->name }}</div>
                <div class="text-muted" style="font-size:0.76rem;">{{ $currentUser->email }}</div>
            </div>
        </div>

        {{-- Mobile Nav Links List --}}
        <div class="d-flex flex-column gap-1 mb-4">
            <a href="{{ route('user.dashboard') }}" class="d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none text-dark fw-600 {{ request()->routeIs('user.dashboard') ? 'bg-light text-primary' : '' }}">
                <i class="bi bi-grid-fill text-primary"></i> Dashboard
            </a>
            <a href="{{ route('user.pregnancies.index') }}" class="d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none text-dark fw-600 {{ request()->routeIs('user.pregnancies.*') ? 'bg-light text-primary' : '' }}">
                <i class="bi bi-heart-pulse-fill text-danger"></i> My Pregnancy Journey
            </a>
            <a href="{{ route('user.menstruation.index') }}" class="d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none text-dark fw-600 {{ request()->routeIs('user.menstruation.*') ? 'bg-light text-primary' : '' }}">
                <i class="bi bi-calendar2-heart-fill text-danger"></i> Cycle &amp; Menstruation
            </a>
            <a href="{{ route('user.checkups') }}" class="d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none text-dark fw-600 {{ request()->routeIs('user.checkups*') ? 'bg-light text-primary' : '' }}">
                <i class="bi bi-clipboard2-pulse-fill text-info"></i> Clinic Checkups
            </a>
            <a href="{{ route('user.health-records') }}" class="d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none text-dark fw-600 {{ request()->routeIs('user.health-records*') ? 'bg-light text-primary' : '' }}">
                <i class="bi bi-file-earmark-medical-fill text-warning"></i> Health Records
            </a>
            <a href="{{ route('user.messages.index') }}" class="d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none text-dark fw-600 {{ request()->routeIs('user.messages.*') ? 'bg-light text-primary' : '' }}">
                <i class="bi bi-chat-heart-fill text-primary"></i> Messages &amp; Care Team
                @if($unreadMessages > 0)
                    <span class="badge bg-danger rounded-pill ms-auto">{{ $unreadMessages }}</span>
                @endif
            </a>
            <a href="{{ route('learning.index') }}" class="d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none text-dark fw-600 {{ request()->routeIs('learning.*') ? 'bg-light text-primary' : '' }}">
                <i class="bi bi-mortarboard-fill text-success"></i> Learning &amp; Guides
            </a>
            <a href="{{ route('forum.index') }}" class="d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none text-dark fw-600 {{ request()->routeIs('forum.*') ? 'bg-light text-primary' : '' }}">
                <i class="bi bi-chat-quote-fill text-secondary"></i> Mothers Community
            </a>
            <a href="{{ route('user.settings') }}" class="d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none text-dark fw-600 {{ request()->routeIs('user.settings') ? 'bg-light text-primary' : '' }}">
                <i class="bi bi-gear-fill text-primary"></i> Settings &amp; Profile
            </a>
        </div>

        {{-- Emergency Support Button in Drawer --}}
        <div class="mt-auto pt-3 border-top">
            <button type="button"
                    class="btn btn-outline-danger w-100 py-2.5 rounded-3 fw-700 d-flex align-items-center justify-content-center gap-2 mb-3"
                    data-bs-toggle="modal"
                    data-bs-target="#careEmergencyModal">
                <i class="bi bi-telephone-plus-fill"></i> Health Center Emergency Contacts
            </button>
            <form method="POST" action="{{ route('logout') }}" class="js-logout-form" data-user-name="{{ $currentUser->first_name ?? $currentUser->name }}">
                @csrf
                <button type="submit" class="btn btn-light border text-danger w-100 py-2 rounded-3 fw-600">
                    <i class="bi bi-box-arrow-right me-2"></i> Log Out
                </button>
            </form>
        </div>
    </div>
</div>
