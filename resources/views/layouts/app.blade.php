<!DOCTYPE html>
<html lang="en">
<head>
    @include('includes.appearance-head')
    <script>
        (function() {
            const mode = window.currentRcTheme();
            document.documentElement.setAttribute('data-theme', mode);
            // Keep Tailwind `dark:` variants in sync (class strategy).
            document.documentElement.classList.toggle('dark', mode === 'dark');
            // Adaptive enforcement pre-paint: Layout A (desktop ≥1024px) vs Layout B (mobile).
            // Stored on <html> so first paint already matches; mirrored to <body> on DOM ready.
            document.documentElement.setAttribute('data-layout',
                window.matchMedia('(min-width: 1024px)').matches ? 'desktop' : 'mobile');
        })();

        // Reload pages restored from browser history so edited list data does not stay stale.
        window.addEventListener('pageshow', function (event) {
            const navigationEntry = performance.getEntriesByType('navigation')[0];
            const isHistoryRestore = event.persisted || navigationEntry?.type === 'back_forward';

            if (isHistoryRestore) {
                window.location.reload();
            }
        });
    </script>
    <meta charset="UTF-8">
    {{-- Bounded pinch-zoom: fit any phone screen, zoom-in capped at 5x, zoom-out floors at 1x so layout never breaks --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ReproCare - Maternal Health System')</title>
    <meta name="description" content="ReproCare - Comprehensive Maternal & Reproductive Health Management System">
    <link rel="icon" type="image/png" href="{{ asset('images/brand/reprocare-logo.png?v=5') }}">
    <link rel="shortcut icon" href="{{ asset('images/brand/reprocare-logo.png?v=5') }}">
    <!-- PWA: installable field app with offline-first support -->
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#F273AC">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ReproCare">
    <link rel="apple-touch-icon" href="{{ asset('images/brand/apple-touch-icon.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans + Inter + Baloo 2 (logo wordmark) -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=Baloo+2:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ReproCare compiled assets: design tokens + Tailwind utilities (dark: variants included) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @stack('styles')

    <style>
        :root { --sidebar-w:256px; --sidebar-collapsed-w:80px; }
        /* ============================================================
           REPROCARE DESIGN SYSTEM v3 — Modern Healthcare Purple (#6C5CE7)
           Supports [data-theme="light"] (default) and [data-theme="dark"]
           ============================================================ */

        /* ── 1. CSS VARIABLES ── */
        

        /* ── LIGHT MODE ── */
        

        /* ── DARK MODE ── */

        /* ── 2. GLOBAL RESET & BASE ── */
        *, *::before, *::after { box-sizing:border-box; }

        html {
            transition:background-color 0s;
        }

        body {
            font-family:'Inter', sans-serif;
            font-size:15px;
            background-color:var(--bg-main);
            color:var(--text);
            min-height:100vh;
            display:flex;
            flex-direction:column;
            padding-top:60px;
            transition:background-color 0s, color var(--transition-base);
        }

        /* ── 3. SCROLLBAR ── */
        ::-webkit-scrollbar { width:5px; height:5px; }
        ::-webkit-scrollbar-track { background:var(--bg-main); }
        ::-webkit-scrollbar-thumb {
            background:var(--color-text-muted);
            border-radius:10px;
            opacity:0.6;
        }
        ::-webkit-scrollbar-thumb:hover { background:var(--color-text-muted); }

        /* ── 4. NAVBAR ── */
        .navbar {
            background:var(--nav-bg) !important;
            border-bottom:1px solid var(--border-glass);
            backdrop-filter:blur(24px) saturate(180%);
            -webkit-backdrop-filter:blur(24px) saturate(180%);
            padding:0.55rem 1.5rem;
            position:fixed;
            top:0; left:0; right:0;
            z-index:1050;
            box-shadow:var(--shadow-sm);
            transition:background var(--transition-slow), border-color var(--transition-base);
        }

        .navbar-brand {
            font-family:'Plus Jakarta Sans', sans-serif;
            font-weight:800;
            font-size:1.25rem;
            color:var(--text) !important;
            display:flex;
            align-items:center;
            gap:0.55rem;
            letter-spacing:-0.5px;
            text-decoration:none;
        }

        .navbar-brand .brand-icon {
            width:40px; height:40px;
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:color-mix(in srgb, var(--color-surface) 96%, transparent);
            border:1px solid color-mix(in srgb, var(--color-border) 60%, transparent);
            box-shadow:0 10px 24px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 18%, transparent);
            flex-shrink:0;
            transition:box-shadow var(--transition-base), transform var(--transition-base);
        }
        .navbar-brand .brand-logo-image {
            width:26px;
            height:26px;
            object-fit:contain;
        }

        .navbar-brand:hover .brand-icon {
            box-shadow:0 14px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 24%, transparent);
            transform:scale(1.05);
        }

        .navbar .nav-link {
            color:var(--text-muted) !important;
            font-size:0.875rem;
            font-weight:500;
            padding:0.45rem 0.7rem !important;
            border-radius:8px;
            transition:all var(--transition-fast);
        }
        .navbar .nav-link:hover {
            color:var(--text) !important;
            background:var(--primary-subtle);
        }

        /* Role Badges in Navbar */
        .role-chip {
            font-size:0.7rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:0.8px;
            padding:0.28em 0.9em;
            border-radius:20px;
        }
        .role-chip-midwife {
            background:linear-gradient(135deg, var(--primary), var(--accent-violet));
            color:var(--color-on-solid);
            box-shadow:0 2px 10px var(--primary-glow);
        }
        .role-chip-bhw {
            background:linear-gradient(135deg, var(--color-info), var(--color-info));
            color:var(--color-on-solid);
            box-shadow:0 2px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
        }
        .role-chip-bhw-president {
            background:linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color:var(--color-on-solid);
            box-shadow:0 2px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
        }
        .role-chip-user {
            background:linear-gradient(135deg, var(--secondary), var(--primary));
            color:var(--color-on-solid);
            box-shadow:0 2px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
        }

        /* ── HIGH-CONTRAST ROLE BADGES (Light & Dark Mode) ── */
        .badge-role-cho {
            background:color-mix(in srgb, var(--color-primary) 15%, transparent) !important;
            color:var(--color-primary-text) !important;
            border:1px solid color-mix(in srgb, var(--color-primary) 35%, transparent) !important;
            font-weight:700 !important;
        }
        .badge-role-rhu {
            background:color-mix(in srgb, var(--color-info) 15%, transparent) !important;
            color:var(--color-info-text) !important;
            border:1px solid color-mix(in srgb, var(--color-info) 35%, transparent) !important;
            font-weight:700 !important;
        }
        .badge-role-midwife {
            background:color-mix(in srgb, var(--color-success) 15%, transparent) !important;
            color:var(--color-success-text) !important;
            border:1px solid color-mix(in srgb, var(--color-success) 35%, transparent) !important;
            font-weight:700 !important;
        }
        .badge-role-bhw-president {
            background:color-mix(in srgb, var(--color-primary) 15%, transparent) !important;
            color:var(--color-primary-text) !important;
            border:1px solid color-mix(in srgb, var(--color-primary) 35%, transparent) !important;
            font-weight:700 !important;
        }
        .badge-role-bhw {
            background:color-mix(in srgb, var(--color-info) 15%, transparent) !important;
            color:var(--color-info-text) !important;
            border:1px solid color-mix(in srgb, var(--color-info) 35%, transparent) !important;
            font-weight:700 !important;
        }
        .badge-role-user {
            background:color-mix(in srgb, var(--color-secondary) 15%, transparent) !important;
            color:var(--color-secondary-text) !important;
            border:1px solid color-mix(in srgb, var(--color-secondary) 35%, transparent) !important;
            font-weight:700 !important;
        }

        /* Notification Bell */
        .notif-bell-wrap {
            position:relative;
            display:flex;
            align-items:center;
        }
        .notif-bell-wrap .notif-dot {
            position:absolute;
            top:4px; right:4px;
            width:8px; height:8px;
            background:var(--danger);
            border-radius:50%;
            border:2px solid var(--bg-main);
            animation:pulse-dot 1.8s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { transform:scale(1); opacity:1; }
            50%       { transform:scale(1.4); opacity:0.7; }
        }

        /* User Avatar */
        .nav-avatar {
            width:34px; height:34px;
            border-radius:50%;
            object-fit:cover;
            border:2px solid var(--primary);
            box-shadow:0 0 0 3px var(--primary-subtle);
            transition:box-shadow var(--transition-base), transform var(--transition-base);
        }
        .nav-avatar:hover {
            box-shadow:0 0 0 4px var(--primary-glow);
            transform:scale(1.05);
        }

        /* Profile-photo lockdown (ALL portals, ALL breakpoints): avatars must
           always render as undistorted 1:1 squares/circles. Each rule below
           keeps its own declared width while aspect-ratio re-squares the
           height — defeating any fluid-media or flex rule that would stretch
           photos into ovals on narrow screens. */
        .rc-avatar, .rc-contact img, .rc-peer-avatar img, .nav-avatar,
        img.composer-user-avatar, img.author-avatar, img.comment-avatar,
        img.create-author-avatar, img.preg-avatar, img.profile-avatar,
        img.profile-show-avatar, img.profile-view-avatar, img.user-profile-avatar,
        img.women-profile-avatar, img.avatar-upload-preview,
        .sidebar-footer-avatar, .bhw-avatar, .clinician-avatar {
            aspect-ratio:1 / 1;
            object-fit:cover;
            flex-shrink:0;
        }
        .rc-avatar, .rc-contact img, .rc-peer-avatar img, .nav-avatar,
        img.composer-user-avatar, img.author-avatar, img.comment-avatar,
        img.create-author-avatar, img.preg-avatar, img.profile-avatar,
        img.profile-show-avatar, img.profile-view-avatar, img.user-profile-avatar,
        img.women-profile-avatar, img.avatar-upload-preview,
        .sidebar-footer-avatar {
            height:auto;
        }

        /* Dropdown */
        .navbar .dropdown-menu {
            background:var(--bg-card2);
            border:1px solid var(--border-glass);
            border-radius:16px;
            box-shadow:var(--shadow-md);
            backdrop-filter:blur(20px);
            padding:0.5rem;
            min-width:210px;
            transition:background var(--transition-slow);
        }
        .navbar .dropdown-item {
            color:var(--text-muted);
            border-radius:10px;
            padding:0.55rem 0.85rem;
            font-size:0.875rem;
            transition:all var(--transition-fast);
        }
        .navbar .dropdown-item:hover {
            background:var(--primary-subtle);
            color:var(--text);
        }
        .navbar .dropdown-divider { border-color:var(--border); margin:0.3rem 0; }
        .navbar .dropdown-item.text-danger:hover { background:color-mix(in srgb, var(--color-danger) 10%, transparent); color:var(--color-danger-text) !important; }

        /* Mobile toggler */
        .navbar-toggler {
            border:1px solid var(--border) !important;
            color:var(--text-muted);
            border-radius:8px;
            padding:0.35rem 0.5rem;
            transition:all var(--transition-fast);
        }
        .navbar-toggler:hover { background:var(--primary-subtle); }

        /* ── 5. SIDEBAR ── */
        .sidebar {
            position:fixed;
            top:60px; left:0; bottom:0;
            width:var(--sidebar-w);
            background:var(--sidebar-bg);
            border-right:1px solid var(--color-primary-soft);
            z-index:1020;
            overflow-y:auto;
            overflow-x:hidden;
            transition:all 300ms ease-in-out;
            padding:1rem 0 4rem;
            display:flex;
            flex-direction:column;
        }
        .sidebar::-webkit-scrollbar { width:2px; }
        body.sidebar-collapsed .sidebar {
            width:var(--sidebar-collapsed-w);
        }
        body.sidebar-collapsed .sidebar-portal-label,
        body.sidebar-collapsed .sidebar-section-label,
        body.sidebar-collapsed .sidebar .nav-link span,
        body.sidebar-collapsed .sidebar-footer-info {
            display:none;
        }
        body.sidebar-collapsed .sidebar .nav-link {
            justify-content:center;
            padding-left:0;
            padding-right:0;
            margin-left:0.7rem;
            margin-right:0.7rem;
        }
        body.sidebar-collapsed .sidebar .nav-link:hover {
            padding-left:0;
            padding-right:0;
        }
        body.sidebar-collapsed .sidebar .sub-menu {
            padding-left:0;
        }
        body.sidebar-collapsed .sidebar-footer {
            justify-content:center;
        }

        /* Sidebar portal label */
        .sidebar-portal-label {
            padding:0.6rem 1.2rem 0.8rem;
            font-family:'Plus Jakarta Sans', sans-serif;
            font-weight:800;
            font-size:0.78rem;
            text-transform:uppercase;
            letter-spacing:1.8px;
            background:linear-gradient(135deg, var(--primary), var(--accent-violet));
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            background-clip:text;
        }

        /* Sidebar section labels */
        .sidebar-section-label {
            padding:1rem 1.3rem 0.35rem;
            font-size:0.68rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:1.5px;
            color:var(--text-muted);
            opacity:0.6;
        }

        /* Sidebar nav links */
        .sidebar .nav-link {
            color:var(--text-muted);
            padding:0.65rem 1.25rem;
            margin:0.2rem 0.85rem;
            border-radius:9999px;
            font-size:0.88rem;
            font-weight:600;
            display:flex;
            align-items:center;
            gap:0.75rem;
            transition:all var(--transition-fast);
            position:relative;
            text-decoration:none;
        }
        .sidebar .nav-link i {
            font-size:1.05rem;
            width:1.25rem;
            text-align:center;
            flex-shrink:0;
            transition:color var(--transition-fast);
        }
        .sidebar .nav-link:hover {
            background:var(--color-secondary-soft);
            color:var(--color-secondary-text);
            padding-left:calc(1.25rem + 4px);
        }
        .sidebar .nav-link:hover i {
            color:var(--color-secondary-text);
        }
        .sidebar .nav-link.active {
            background:#171B22 !important;
            background:color-mix(in srgb, var(--color-surface-strong) 55%, black) !important;
            background-color:color-mix(in srgb, var(--color-surface-strong) 55%, black) !important;
            color:var(--color-on-solid) !important;
            font-weight:700;
            box-shadow:0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);
        }
        .sidebar .nav-link.active i {
            color:var(--color-on-solid) !important;
        }
        /* Selected page darkens a touch on hover instead of flashing lighter. */
        .sidebar .nav-link.active:hover {
            background:#10131A !important;
            background-color:color-mix(in srgb, var(--color-surface-strong) 42%, black) !important;
            color:var(--color-on-solid) !important;
            padding-left:calc(1.25rem + 4px);
        }
        .sidebar .nav-link.active:hover i {
            color:var(--color-on-solid) !important;
        }

        /* Submenu */
        .sidebar .sub-menu { padding:0.2rem 0 0.2rem 2rem; }
        .sidebar .sub-menu .nav-link {
            padding:0.45rem 0.75rem;
            margin:0.04rem 0.4rem;
            font-size:0.82rem;
            border-radius:10px;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            margin-top:auto;
            padding:0.75rem 1rem;
            border-top:1px solid var(--border);
            display:flex;
            align-items:center;
            gap:0.65rem;
        }
        .sidebar-footer-avatar {
            width:36px; height:36px;
            border-radius:50%;
            object-fit:cover;
            border:2px solid var(--primary);
            box-shadow:0 0 8px var(--primary-glow);
            flex-shrink:0;
        }
        .sidebar-footer-info { flex:1; min-width:0; }
        .sidebar-footer-name {
            font-size:0.82rem;
            font-weight:600;
            color:var(--text);
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }
        .sidebar-footer-role {
            font-size:0.7rem;
            color:var(--primary-light);
            text-transform:capitalize;
            font-weight:500;
        }

        /* ── Sidebar — scrollable inner + floating overlap badge toggle ── */
        .sidebar {
            padding:0 !important;
            overflow:visible !important;
            transition:all 300ms ease-in-out !important;
        }
        .sidebar-inner {
            flex:1;
            min-height:0;
            overflow-y:auto;
            overflow-x:hidden;
            padding:1rem 0 1rem;
            display:flex;
            flex-direction:column;
            width:100%;
            transition:all 300ms ease-in-out;
        }
        .sidebar-inner::-webkit-scrollbar { width:2px; }
        /* Floating Overlap Badge — 32px circular, anchored to right border (absolute -right-4 top-6 z-30) */
        .sidebar-edge-toggle {
            position:absolute;
            top:24px; /* top-6 */
            right:-16px; /* -right-4 */
            width:32px;
            height:32px;
            border-radius:9999px; /* rounded-full */
            background:var(--color-surface); /* bg-white */
            border:1px solid var(--color-border); /* border-[#EAE5F4] */
            color:var(--color-text-muted); /* text-[#756A88] */
            display:flex; /* flex */
            align-items:center;
            justify-content:center; /* items-center justify-center */
            font-size:0.8rem;
            cursor:pointer; /* cursor-pointer */
            box-shadow:0 4px 6px -1px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent), 0 2px 4px -2px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent); /* shadow-md */
            z-index:30; /* z-30 */
            transition:transform 200ms ease, color 200ms ease, border-color 200ms ease, box-shadow 200ms ease, background 200ms ease;
            padding:0;
            flex-shrink:0;
            overflow:hidden;
        }
        .sidebar-edge-toggle:hover {
            color:var(--color-primary-text); /* hover:text-[#7C3AED] */
            border-color:var(--color-border);
            background:var(--color-surface);
            box-shadow:0 10px 15px -3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 12%, transparent), 0 4px 6px -4px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent);
            transform:scale(1.05); /* hover:scale-105 */
        }
        .sidebar-edge-toggle:active {
            transform:scale(0.97);
        }
        .sidebar-edge-toggle svg {
            width:14px;
            height:14px;
            flex-shrink:0;
            transition:transform 200ms ease;
        }
        .sidebar-edge-toggle.is-collapsed svg {
            transform:rotate(180deg); /* rotate-180 when collapsed */
        }
        @media (max-width: 1023.98px) {
            .sidebar-edge-toggle { display:none !important; }
        }

        /* ── 6. LAYOUT WRAPPER & MAIN CONTENT ── */
        .layout-wrapper {
            display:flex;
            min-height:calc(100vh - 60px);
            width:100%;
        }
        .main-content {
            margin-left:var(--sidebar-w);
            padding:1.75rem 2rem 4.5rem;
            flex:1;
            min-height:calc(100vh - 60px);
            width:calc(100% - var(--sidebar-w));
            max-width:calc(100% - var(--sidebar-w));
            box-sizing:border-box;
            overflow-x:hidden;
            transition:all 300ms ease-in-out;
        }
        body.sidebar-collapsed .main-content {
            margin-left:var(--sidebar-collapsed-w);
            width:calc(100% - var(--sidebar-collapsed-w));
            max-width:calc(100% - var(--sidebar-collapsed-w));
        }

        .main-content > * {
            max-width:100%;
            overflow-x:auto;
        }

        /* ── 7. CARDS ── */
        .card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:24px;
            box-shadow:var(--shadow-sm);
            transition:background var(--transition-slow), border-color var(--transition-base), box-shadow var(--transition-base), transform var(--transition-base);
        }
        .card:hover {
            box-shadow:var(--shadow-md);
            transform:translateY(-2px);
            border-color:var(--lavender);
        }
        .card-header {
            background:transparent;
            border-bottom:1px solid var(--border);
            padding:1.1rem 1.4rem;
            color:var(--text);
            transition:border-color var(--transition-base);
        }
        .card-body { color:var(--text); padding:1.4rem; }
        .card-footer {
            background:transparent;
            border-top:1px solid var(--border);
            padding:1rem 1.4rem;
            transition:border-color var(--transition-base);
        }

        /* Glass Card */
        .glass-card {
            background:var(--bg-glass);
            backdrop-filter:blur(16px) saturate(180%);
            -webkit-backdrop-filter:blur(16px) saturate(180%);
            border:1px solid var(--border-glass);
            border-radius:24px;
            box-shadow:var(--shadow-sm);
            transition:background var(--transition-slow), box-shadow var(--transition-base);
        }

        /* Stat Card */
        .stat-card {
            border:1px solid var(--border) !important;
            border-radius:24px;
            padding:1.4rem;
            position:relative;
            overflow:hidden;
            cursor:default;
            background:var(--bg-card);
            box-shadow:var(--shadow-sm);
            transition:transform var(--transition-base), box-shadow var(--transition-base);
        }
        .stat-card:hover {
            transform:translateY(-4px);
            box-shadow:var(--shadow-md);
            border-color:var(--lavender) !important;
        }

        /* ── PASTEL STAT CARDS (Direct from Ai Aether Reference) ── */
        .stat-card-peach {
            background:var(--color-peach-soft) !important;
            border:1px solid var(--color-peach-soft) !important;
            border-radius:24px !important;
            padding:1.4rem !important;
            position:relative;
            transition:all var(--transition-base);
            color:var(--color-text);
        }
        .stat-card-peach:hover {
            transform:translateY(-3px);
            box-shadow:var(--shadow-md);
        }

        .stat-card-blue {
            background:var(--color-info-soft) !important;
            border:1px solid var(--color-info-soft) !important;
            border-radius:24px !important;
            padding:1.4rem !important;
            position:relative;
            transition:all var(--transition-base);
            color:var(--color-text);
        }
        .stat-card-blue:hover {
            transform:translateY(-3px);
            box-shadow:var(--shadow-md);
        }

        .stat-card-lavender {
            background:var(--color-primary-soft) !important;
            border:1px solid var(--color-border) !important;
            border-radius:24px !important;
            padding:1.4rem !important;
            position:relative;
            transition:all var(--transition-base);
            color:var(--color-text);
        }
        .stat-card-lavender:hover {
            transform:translateY(-3px);
            box-shadow:var(--shadow-md);
        }

        .stat-card-mint {
            background:var(--color-success-soft) !important;
            border:1px solid var(--color-success-soft) !important;
            border-radius:24px !important;
            padding:1.4rem !important;
            position:relative;
            transition:all var(--transition-base);
            color:var(--color-text);
        }
        .stat-card-mint:hover {
            transform:translateY(-3px);
            box-shadow:var(--shadow-md);
        }

        /* Circular Action Button with Arrow (from Aether stat cards) */
        .btn-circle-action {
            width:36px;
            height:36px;
            border-radius:50%;
            background:var(--dark-contrast);
            color:var(--color-on-solid);
            display:inline-flex;
            align-items:center;
            justify-content:center;
            font-size:0.85rem;
            border:none;
            cursor:pointer;
            transition:all var(--transition-fast);
            text-decoration:none;
            box-shadow:0 3px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent);
            flex-shrink:0;
        }
        .btn-circle-action:hover {
            transform:scale(1.08) translate(1px, -1px);
            background:var(--primary);
            color:var(--color-on-solid);
        }

        /* ── CAPSULE BAR ANALYTICS ── */
        .capsule-chart-wrap {
            display:flex;
            align-items:flex-end;
            gap:12px;
            height:150px;
            padding:1rem 0;
        }
        .capsule-bar {
            flex:1;
            background:var(--lavender);
            border-radius:9999px !important;
            transition:all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position:relative;
            min-height:16px;
        }
        .capsule-bar.active {
            background:var(--primary) !important;
            box-shadow:0 4px 14px var(--primary-glow);
        }
        .capsule-bar.striped {
            background:repeating-linear-gradient(
                45deg,
                var(--primary),
                var(--primary) 6px,
                var(--primary-light) 6px,
                var(--primary-light) 12px
            ) !important;
        }
        .capsule-bar:hover {
            transform:scaleY(1.05);
            background:var(--primary-light);
        }

        /* Stat Card Elements */
        .stat-card .stat-icon {
            width:48px; height:48px;
            background:var(--primary-subtle);
            border-radius:14px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:1.4rem;
            color:var(--primary);
            margin-bottom:0.9rem;
        }
        .stat-card .stat-label {
            font-size:0.78rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:0.05em;
            color:var(--text-muted);
            margin-bottom:0.35rem;
        }
        .stat-card .stat-number {
            font-family:'Plus Jakarta Sans', sans-serif;
            font-size:2.3rem;
            font-weight:800;
            color:var(--text);
            line-height:1;
            margin-bottom:0.4rem;
            letter-spacing:-0.02em;
        }
        .stat-card .stat-trend {
            font-size:0.78rem;
            font-weight:600;
            color:var(--text-muted);
            display:flex;
            align-items:center;
            gap:0.3rem;
        }
        .stat-card .stat-trend.up { color:var(--success); }
        .stat-card .stat-trend.danger { color:var(--danger); }

        /* Stat Gradients */
        .stat-purple  { background:linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-text) 100%); color:var(--color-on-solid) !important; }
        .stat-purple .stat-label, .stat-purple .stat-number, .stat-purple .stat-trend { color:var(--color-on-solid) !important; }
        .stat-rose    { background:linear-gradient(135deg, var(--color-secondary) 0%, var(--color-primary) 100%); color:var(--color-on-solid) !important; }
        .stat-rose .stat-label, .stat-rose .stat-number, .stat-rose .stat-trend { color:var(--color-on-solid) !important; }
        .stat-cyan    { background:linear-gradient(135deg, var(--color-info) 0%, var(--color-info) 100%); color:var(--color-on-solid) !important; }
        .stat-amber   { background:linear-gradient(135deg, var(--color-warning) 0%, var(--color-danger) 100%); color:var(--color-on-solid) !important; }
        .stat-green   { background:linear-gradient(135deg, var(--color-success) 0%, var(--color-success-text) 100%); color:var(--color-on-solid) !important; }
        .stat-lime    { background:linear-gradient(135deg, var(--color-success) 0%, var(--color-success-text) 100%); color:var(--color-on-solid) !important; }
        .stat-violet  { background:linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary) 100%); color:var(--color-on-solid) !important; }

        /* ── 8. PAGE HERO ── */
        .page-hero {
            background:linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-text) 50%, var(--color-primary) 100%);
            border-radius:24px;
            padding:1.85rem 2.25rem;
            margin-bottom:1.75rem;
            position:relative;
            overflow:hidden;
            box-shadow:0 12px 36px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent);
        }
        .page-hero::before {
            content:'';
            position:absolute;
            width:320px; height:320px;
            border-radius:50%;
            background:radial-gradient(circle, color-mix(in srgb, var(--color-surface) 18%, transparent) 0%, transparent 70%);
            top:-100px; right:-80px;
            pointer-events:none;
        }
        .page-hero-title {
            font-family:'Plus Jakarta Sans', sans-serif;
            font-size:1.6rem;
            font-weight:800;
            color:var(--color-on-solid);
            margin-bottom:0.3rem;
            position:relative;
        }
        .page-hero-subtitle {
            font-size:0.9rem;
            color:color-mix(in srgb, var(--color-on-solid) 85%, transparent);
            margin:0;
            position:relative;
        }

        /* ── 9. BUTTONS (Modern Pill Across the Board) ── */
        .btn {
            border-radius:9999px;
            font-family:'Plus Jakarta Sans', sans-serif;
            font-weight:700;
            transition:transform var(--transition-fast), box-shadow var(--transition-fast), background var(--transition-fast);
            position:relative;
            overflow:hidden;
        }
        .btn::after {
            content:'';
            position:absolute;
            inset:0;
            background:linear-gradient(90deg, transparent 0%, color-mix(in srgb, var(--color-surface) 18%, transparent) 50%, transparent 100%);
            transform:translateX(-100%);
            transition:transform 0.5s ease;
        }
        .btn:hover::after { transform:translateX(100%); }
        .btn:active { transform:scale(0.97) !important; }

        .btn-primary {
            background:var(--color-surface-strong);
            background-color:var(--color-surface-strong);
            border:1px solid var(--color-text);
            border-radius:9999px;
            font-weight:700;
            font-size:0.875rem;
            padding:0.6rem 1.45rem;
            color:var(--color-on-solid);
            box-shadow:0 4px 16px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);
        }
        .btn-primary:hover {
            transform:translateY(-2px);
            box-shadow:0 8px 24px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
            background:var(--color-surface-strong);
            background-color:var(--color-surface-strong);
            border-color:var(--color-text);
            color:var(--color-on-solid);
        }
        .btn-primary i { color:var(--color-on-solid); }
        /* Semantic buttons - appropriate colors, not all pink */
        .btn-view {
            background:var(--color-info-text) !important;
            background-color:var(--color-info-text) !important;
            border:1px solid var(--color-info-text) !important;
            color:var(--color-on-solid) !important;
            box-shadow:0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent);
        }
        .btn-view:hover {
            background:var(--color-info-text) !important;
            border-color:var(--color-info-text) !important;
            color:var(--color-on-solid) !important;
            transform:translateY(-2px);
        }
        .btn-view i { color:var(--color-on-solid) !important; }
        .btn-filter {
            background:var(--color-surface-strong) !important;
            background-color:var(--color-surface-strong) !important;
            border:1px solid var(--color-text) !important;
            color:var(--color-on-solid) !important;
            box-shadow:0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);
        }
        .btn-filter:hover {
            background:var(--color-surface-strong) !important;
            border-color:var(--color-text) !important;
            color:var(--color-on-solid) !important;
            transform:translateY(-2px);
        }
        .btn-filter i { color:var(--color-on-solid) !important; }

        /* Pitch-Black Pill Button (from OneBank & Aether) */
        .btn-dark, .btn-black-pill {
            background:var(--dark-contrast) !important;
            color:var(--color-on-solid) !important;
            border:none;
            border-radius:9999px;
            font-weight:700;
            font-size:0.875rem;
            padding:0.6rem 1.45rem;
            box-shadow:0 4px 16px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent);
            transition:all var(--transition-base);
        }
        .btn-dark:hover, .btn-black-pill:hover {
            background:var(--color-surface-strong) !important;
            transform:translateY(-2px);
            box-shadow:0 8px 22px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 32%, transparent);
            color:var(--color-on-solid) !important;
        }

        /* Soft Lavender Pill Button */
        .btn-soft-purple, .btn-lavender-pill {
            background:var(--primary-subtle) !important;
            color:var(--primary) !important;
            border:1px solid var(--lavender) !important;
            border-radius:9999px;
            font-weight:700;
            font-size:0.875rem;
            padding:0.55rem 1.35rem;
            transition:all var(--transition-fast);
        }
        .btn-soft-purple:hover, .btn-lavender-pill:hover {
            background:var(--lavender) !important;
            color:var(--primary-dark) !important;
            transform:translateY(-1px);
        }

        .btn-outline-primary {
            border:1.5px solid var(--primary);
            color:var(--primary);
            background:transparent;
            border-radius:9999px;
            font-weight:700;
            font-size:0.875rem;
            padding:0.55rem 1.35rem;
            transition:all var(--transition-base);
        }
        .btn-outline-primary:hover {
            background:var(--primary-subtle);
            border-color:var(--primary);
            color:var(--primary);
        }

        .btn-outline-secondary {
            border:1.5px solid var(--border);
            color:var(--text-muted);
            background:transparent;
            border-radius:9999px;
            font-size:0.875rem;
            padding:0.55rem 1.25rem;
            transition:all var(--transition-base);
        }
        .btn-outline-secondary:hover {
            background:var(--bg-card2);
            border-color:var(--primary);
            color:var(--text);
        }

        .btn-success {
            background:linear-gradient(135deg, var(--color-success), var(--color-success-text));
            border:none;
            border-radius:9999px;
            font-weight:700;
            color:var(--color-on-solid);
            box-shadow:0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
        }
        .btn-success:hover { color:var(--color-on-solid); }

        .btn-danger {
            background:linear-gradient(135deg, var(--color-danger), var(--color-danger));
            border:none;
            border-radius:9999px;
            font-weight:700;
            color:var(--color-on-solid);
            box-shadow:0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
        }
        .btn-danger:hover { color:var(--color-on-solid); }

        .btn-info {
            background:linear-gradient(135deg, var(--color-info), var(--color-info));
            border:none;
            border-radius:9999px;
            font-weight:700;
            color:var(--color-on-solid);
            box-shadow:0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
        }
        .btn-info:hover { color:var(--color-on-solid); }

        .btn-sm { padding:0.35rem 0.95rem; font-size:0.8rem; border-radius:9999px; }

        /* Outline light */
        .btn-outline-light {
            border:1.5px solid color-mix(in srgb, var(--color-border) 45%, transparent);
            color:color-mix(in srgb, var(--color-on-solid) 95%, transparent);
            border-radius:9999px;
            font-size:0.8rem;
            background:transparent;
        }
        .btn-outline-light:hover { background:color-mix(in srgb, var(--color-surface) 18%, transparent); color:var(--color-on-solid); border-color:var(--color-border); }

        /* Hero button */
        .btn-hero-primary {
            display:inline-flex;
            align-items:center;
            gap:0.5rem;
            padding:0.65rem 1.45rem;
            background:color-mix(in srgb, var(--color-surface) 22%, transparent);
            border:1.5px solid color-mix(in srgb, var(--color-border) 45%, transparent);
            border-radius:9999px;
            color:var(--color-on-solid);
            font-weight:700;
            font-size:0.9rem;
            text-decoration:none;
            transition:all var(--transition-base);
            backdrop-filter:blur(8px);
        }
        .btn-hero-primary:hover {
            background:color-mix(in srgb, var(--color-surface) 35%, transparent);
            color:var(--color-on-solid);
            transform:translateY(-2px);
        }

        /* ── 10. FORMS ── */
        /* ── FORMS & INPUTS — polished visibility ── */
        .form-control, .form-select {
            background:var(--bg-input);
            border:1.5px solid var(--input-border, var(--border));
            color:var(--text);
            border-radius:12px;
            font-size:0.9rem;
            padding:0.6rem 0.95rem;
            transition:background var(--transition-slow), border-color var(--transition-base),
                        box-shadow var(--transition-base), color var(--transition-base);
        }
        .form-control:focus, .form-select:focus {
            background:var(--bg-input);
            border-color:var(--primary);
            color:var(--text);
            box-shadow:0 0 0 3px var(--focus-ring, var(--primary-glow));
            outline:none;
        }
        /* ↑ more visible placeholder — meets WCAG AA */
        .form-control::placeholder { color:var(--placeholder, var(--text-muted)); opacity:1; }
        textarea.form-control::placeholder { color:var(--placeholder, var(--text-muted)); opacity:1; }
        .form-label {
            font-weight:600;
            font-size:0.875rem;
            color:var(--text);            /* always full-brightness label */
            margin-bottom:0.45rem;
            letter-spacing:0.01em;
        }
        .form-text  { color:var(--text-muted); font-size:0.8rem; }
        .form-select option { background:var(--bg-card2); color:var(--text); }

        .input-group-text {
            background:var(--bg-input);
            border:1.5px solid var(--input-border, var(--border));
            color:var(--text-muted);
            transition:background var(--transition-slow), border-color var(--transition-base);
        }
        .form-check-input {
            background-color:var(--bg-input);
            border-color:var(--border);
            width:1.1em; height:1.1em;
            transition:background var(--transition-base), border-color var(--transition-base);
        }
        .form-check-input:checked {
            background-color:var(--primary);
            border-color:var(--primary);
            box-shadow:0 0 0 2px var(--focus-ring);
        }
        .form-check-label { color:var(--text); font-size:0.875rem; }

        /* Custom RC Switch (for settings) */
        .rc-switch { display:inline-flex; align-items:center; gap:0.6rem; cursor:pointer; }
        .rc-switch input { display:none; }
        .rc-track {
            width:52px; height:28px;
            background:var(--bg-card2);
            border:1.5px solid var(--border);
            border-radius:100px;
            position:relative;
            transition:background 0.3s ease, border-color 0.3s ease;
        }
        .rc-track .rc-thumb {
            position:absolute;
            width:20px; height:20px;
            background:var(--text-muted);
            border-radius:50%;
            top:3px; left:3px;
            transition:transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.25s ease;
            box-shadow:0 1px 4px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent);
        }
        .rc-switch input:checked ~ .rc-track { background:var(--primary); border-color:var(--primary); }
        .rc-switch input:checked ~ .rc-track .rc-thumb {
            transform:translateX(24px);
            background:var(--color-surface);
            box-shadow:0 2px 8px var(--primary-glow);
        }

        /* ── 11. ALERTS ── */
        .alert {
            position:relative;
            overflow:hidden;
            border-radius:18px;
            border:1px solid;
            padding:1rem 1.1rem;
            font-size:0.92rem;
            line-height:1.55;
            box-shadow:0 16px 36px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);
            transition:background var(--transition-slow), border-color var(--transition-base), transform var(--transition-base);
        }
        .alert::before {
            content:'';
            position:absolute;
            inset:0 auto 0 0;
            width:4px;
            border-radius:18px 0 0 18px;
            background:currentColor;
            opacity:0.7;
        }
        .alert-success { background:linear-gradient(135deg, color-mix(in srgb, var(--color-success) 16%, transparent), color-mix(in srgb, var(--color-success) 8%, transparent)); border-color:color-mix(in srgb, var(--color-success) 28%, transparent); color:var(--color-success-text); }
        .alert-danger  { background:linear-gradient(135deg, color-mix(in srgb, var(--color-danger) 16%, transparent), color-mix(in srgb, var(--color-danger) 8%, transparent)); border-color:color-mix(in srgb, var(--color-danger) 28%, transparent); color:var(--color-danger-text); }
        .alert-info    { background:linear-gradient(135deg, color-mix(in srgb, var(--color-info) 16%, transparent), color-mix(in srgb, var(--color-info) 8%, transparent)); border-color:color-mix(in srgb, var(--color-info) 28%, transparent); color:var(--color-info-text); }
        .alert-warning { background:linear-gradient(135deg, color-mix(in srgb, var(--color-warning) 16%, transparent), color-mix(in srgb, var(--color-warning) 8%, transparent)); border-color:color-mix(in srgb, var(--color-warning) 28%, transparent); color:var(--color-warning-text); }
        .alert-primary { background:linear-gradient(135deg, color-mix(in srgb, var(--color-primary) 16%, transparent), color-mix(in srgb, var(--color-primary) 8%, transparent)); border-color:color-mix(in srgb, var(--color-primary) 24%, transparent); color:var(--color-primary-text); }
        [data-theme="dark"] .alert-success { color:var(--color-success-text); }
        [data-theme="dark"] .alert-danger { color:var(--color-danger-text); }
        [data-theme="dark"] .alert-info { color:var(--color-info-text); }
        [data-theme="dark"] .alert-warning { color:var(--color-warning-text); }
        [data-theme="dark"] .alert-primary { color:var(--color-secondary-text); }
        .alert .btn-close { filter:invert(0.45); }
        .app-confirm-modal .modal-content {
            border:1px solid var(--border);
            border-radius:22px;
            background:var(--bg-card);
            box-shadow:var(--shadow-md);
        }
        .app-confirm-modal .modal-header,
        .app-confirm-modal .modal-footer {
            border-color:var(--border);
        }
        .app-confirm-icon {
            width:54px;
            height:54px;
            border-radius:16px;
            display:grid;
            place-items:center;
            margin-bottom:1rem;
            background:linear-gradient(135deg, color-mix(in srgb, var(--color-primary) 16%, transparent), color-mix(in srgb, var(--color-secondary) 16%, transparent));
            color:var(--primary);
            font-size:1.35rem;
        }

        /* ── Dedicated LOGOUT dialog (all roles) ── */
        .logout-modal .modal-dialog { max-width:400px; }
        .logout-modal .modal-content {
            border:1px solid var(--border);
            border-radius:26px;
            background:var(--bg-card);
            box-shadow:var(--shadow-md);
            overflow:hidden;
        }
        .logout-modal .modal-body {
            padding:2rem 1.75rem 1.6rem;
            text-align:center;
        }
        .logout-modal-icon {
            width:68px;
            height:68px;
            border-radius:50%;
            margin:0 auto 1.1rem;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:1.7rem;
            color:var(--color-on-solid);
            background:linear-gradient(135deg, var(--color-secondary), var(--color-secondary-text));
            box-shadow:0 10px 26px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 40%, transparent);
        }
        .logout-modal-title {
            font-family:'Plus Jakarta Sans', sans-serif;
            font-weight:800;
            font-size:1.3rem;
            letter-spacing:-0.02em;
            color:var(--text);
            margin-bottom:0.4rem;
        }
        .logout-modal-text {
            font-size:0.9rem;
            color:var(--text-muted);
            line-height:1.6;
            margin-bottom:0;
        }
        .logout-modal-text strong { color:var(--text); }
        .logout-modal-actions {
            display:flex;
            gap:0.65rem;
            margin-top:1.5rem;
        }
        .logout-modal-actions .btn {
            flex:1;
            min-height:46px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:0.45rem;
        }
        .btn-logout-stay {
            background:var(--bg-card2);
            border:1.5px solid var(--border);
            color:var(--text);
            border-radius:9999px;
            font-weight:700;
            font-size:0.88rem;
        }
        .btn-logout-stay:hover { border-color:var(--primary); color:var(--text); }
        .btn-logout-go {
            background:linear-gradient(135deg, var(--color-secondary), var(--color-secondary-text));
            border:none;
            color:var(--color-on-solid);
            border-radius:9999px;
            font-weight:700;
            font-size:0.88rem;
            box-shadow:0 6px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
        }
        .btn-logout-go:hover { color:var(--color-on-solid); transform:translateY(-1px); }
        @media (max-width: 480px) {
            .logout-modal .modal-dialog {
                max-width:calc(100vw - 2rem);
                margin-left:auto;
                margin-right:auto;
            }
            .logout-modal-actions { flex-direction:column-reverse; }
        }

        /* ── 12. BADGES ── */
        .badge {
            font-weight:700;
            font-size:0.72rem;
            border-radius:20px;           /* pill style */
            padding:0.32em 0.75em;
            letter-spacing:0.3px;
        }
        /* Semantic badge overrides */
        .badge.bg-primary  { background:var(--primary) !important; }
        .badge.bg-secondary{ background:var(--bg-card2) !important; color:var(--text) !important; border:1px solid var(--border); }
        .badge.bg-success  { background:color-mix(in srgb, var(--color-success) 22%, transparent) !important; color:var(--color-success-text) !important; }
        .badge.bg-danger   { background:color-mix(in srgb, var(--color-danger) 22%, transparent) !important;  color:var(--color-danger-text) !important; }
        .badge.bg-warning  { background:color-mix(in srgb, var(--color-warning) 22%, transparent) !important; color:var(--color-warning-text) !important; }
        .badge.bg-info     { background:color-mix(in srgb, var(--color-info) 22%, transparent) !important;  color:var(--color-info-text) !important; }

        /* Status pill badges — readable in both modes */
        .status-scheduled { background:color-mix(in srgb, var(--color-info) 18%, transparent);   color:var(--color-info-text); border-radius:20px; padding:0.25rem 0.8rem; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:0.3rem; }
        .status-completed { background:color-mix(in srgb, var(--color-success) 18%, transparent);  color:var(--color-success-text); border-radius:20px; padding:0.25rem 0.8rem; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:0.3rem; }
        .status-missed    { background:color-mix(in srgb, var(--color-danger) 18%, transparent);   color:var(--color-danger-text); border-radius:20px; padding:0.25rem 0.8rem; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:0.3rem; }
        .status-pending   { background:color-mix(in srgb, var(--color-warning) 18%, transparent);  color:var(--color-warning-text); border-radius:20px; padding:0.25rem 0.8rem; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:0.3rem; }
        .status-active    { background:color-mix(in srgb, var(--color-success) 18%, transparent);  color:var(--color-success-text); border-radius:20px; padding:0.25rem 0.8rem; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:0.3rem; }
        .status-cancelled { background:color-mix(in srgb, var(--color-primary) 18%, transparent);  color:var(--color-primary-text); border-radius:20px; padding:0.25rem 0.8rem; font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:0.3rem; }

        /* ── 13. TABLES — card-style, modern ── */
        .table {
            color:var(--text);
            --bs-table-bg:transparent;
            --bs-table-striped-bg:var(--row-alt);
            --bs-table-hover-bg:var(--row-hover);
            border-collapse:separate;
            border-spacing:0;
        }
        .table-bordered { border-color:var(--border); }
        .table td, .table th {
            border-color:var(--border);
            padding:0.9rem 1.1rem;
            vertical-align:middle;
            transition:background var(--transition-fast), color var(--transition-base);
            font-size:0.875rem;
        }
        .table tbody td { color:var(--text); }   /* ← explicit: never invisible */
        .table-hover tbody tr { transition:background var(--transition-fast); cursor:default; }
        .table-hover tbody tr:hover td { background:var(--row-hover) !important; }
        .table-striped tbody tr:nth-of-type(odd) td { background:var(--row-alt); }
        .table thead th {
            background:var(--bg-card2);
            color:var(--text-muted);
            font-weight:700;
            font-size:0.72rem;
            text-transform:uppercase;
            letter-spacing:0.8px;
            border-bottom:2px solid var(--border);
            white-space:nowrap;
        }
        /* ── Card-style table wrapper — wrap any table in .table-card ── */
        .table-card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:16px;
            overflow:hidden;
            box-shadow:var(--shadow-sm);
            transition:background var(--transition-slow);
        }
        .table-card .table { margin:0; }
        .table-card .table thead th:first-child { border-radius:0; }
        .table-card .table tbody tr:last-child td { border-bottom:none; }
        /* Sticky header (opt-in via .table-sticky-head) */
        .table-sticky-head thead th { position:sticky; top:0; z-index:2; }

        /* ── 14. LIST GROUPS ── */
        .list-group-item {
            background:transparent;
            border-color:var(--border);
            color:var(--text);            /* ↑ explicit to prevent invisible text */
            font-size:0.875rem;
            transition:background var(--transition-fast), color var(--transition-base);
        }
        .list-group-item-action:hover {
            background:var(--row-hover);
            color:var(--text);
            border-color:var(--primary);
        }
        .list-group-flush .list-group-item:first-child { border-top:none; }
        /* Flush list inside a card — removes outer border */
        .list-group-flush .list-group-item { border-left:none; border-right:none; }

        /* ── 15. MODALS ── */
        .modal-content {
            background:var(--bg-card);
            border:1px solid var(--border-glass);
            border-radius:20px;
            box-shadow:var(--shadow-md);
            color:var(--text);
            transition:background var(--transition-slow);
        }
        .modal-header {
            background:linear-gradient(135deg, var(--primary-dark), var(--primary));
            border-bottom:none;
            border-radius:19px 19px 0 0;
            padding:1.1rem 1.4rem;
            color:var(--color-on-solid);
        }
        .modal-header .btn-close { filter:invert(1) brightness(2); opacity:0.8; }
        .modal-body { padding:1.4rem; }
        .modal-footer {
            background:var(--bg-card2);
            border-top:1px solid var(--border);
            border-radius:0 0 19px 19px;
            padding:0.85rem 1.4rem;
        }
        .modal-title { font-family:'Plus Jakarta Sans', sans-serif; font-weight:700; font-size:1.05rem; }

        /* ── 16. SKELETON LOADERS ── */
        .skeleton {
            background:linear-gradient(90deg, var(--skeleton-from) 25%, var(--skeleton-to) 50%, var(--skeleton-from) 75%);
            background-size:200% 100%;
            animation:skeleton-shimmer 1.6s infinite;
            border-radius:10px;
        }
        @keyframes skeleton-shimmer {
            0%   { background-position:200% 0; }
            100% { background-position:-200% 0; }
        }

        /* ── 17. EMPTY STATES ── */
        .empty-state {
            text-align:center;
            padding:3rem 1.5rem;
        }
        .empty-state-icon {
            font-size:3.5rem;
            color:var(--text-muted);
            opacity:0.4;
            margin-bottom:1rem;
            display:block;
        }
        .empty-state h6 {
            color:var(--text);
            font-weight:600;
            margin-bottom:0.4rem;
        }
        .empty-state p {
            color:var(--text-muted);
            font-size:0.875rem;
            margin-bottom:1.2rem;
        }

        /* ── 18. ACTIVITY TIMELINE ── */
        .activity-timeline { list-style:none; padding:0; margin:0; }
        .timeline-item {
            display:flex;
            gap:0.9rem;
            padding-bottom:1.1rem;
            position:relative;
        }
        .timeline-item:not(:last-child)::before {
            content:'';
            position:absolute;
            left:15px; top:28px;
            width:1px;
            bottom:0;
            background:var(--border);
        }
        .timeline-dot {
            width:30px; height:30px;
            border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:0.8rem;
            color:var(--color-on-solid);
            flex-shrink:0;
            margin-top:2px;
        }
        .dot-primary { background:linear-gradient(135deg, var(--primary), var(--accent-violet)); box-shadow:0 2px 8px var(--primary-glow); }
        .dot-success { background:linear-gradient(135deg, var(--color-success), var(--color-success-text)); box-shadow:0 2px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent); }
        .dot-warning { background:linear-gradient(135deg, var(--color-warning), var(--color-warning)); box-shadow:0 2px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent); }
        .dot-danger  { background:linear-gradient(135deg, var(--color-danger), var(--color-danger)); box-shadow:0 2px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent); }
        .dot-info    { background:linear-gradient(135deg, var(--color-info), var(--color-info)); box-shadow:0 2px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent); }

        .timeline-content {
            flex:1;
            background:var(--bg-card2);
            border:1px solid var(--border);
            border-radius:12px;
            padding:0.65rem 0.9rem;
            font-size:0.85rem;
            transition:background var(--transition-slow);
        }
        .timeline-time {
            font-size:0.75rem;
            color:var(--text-muted);
            display:block;
            margin-top:0.2rem;
        }

        /* ── 19. FADE-IN ANIMATION ── */
        .fade-in-card {
            opacity:0;
            transform:translateY(18px);
            transition:opacity 0.5s ease, transform 0.5s ease;
        }
        .fade-in-card.visible { opacity:1; transform:translateY(0); }
        .fade-in-card:nth-child(1) { transition-delay:0.04s; }
        .fade-in-card:nth-child(2) { transition-delay:0.08s; }
        .fade-in-card:nth-child(3) { transition-delay:0.12s; }
        .fade-in-card:nth-child(4) { transition-delay:0.16s; }
        .fade-in-card:nth-child(5) { transition-delay:0.20s; }
        .fade-in-card:nth-child(6) { transition-delay:0.24s; }

        /* ── 20. PAGE TITLE ── */
        .page-title {
            font-family:'Plus Jakarta Sans', sans-serif;
            font-size:1.5rem;
            font-weight:800;
            color:var(--text);
            letter-spacing:-0.5px;
            margin-bottom:0.15rem;
        }
        .page-subtitle { font-size:0.875rem; color:var(--text-muted); }

        /* Quick action tiles — borderless dashboard pastels */
        .quick-action-tile {
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            padding:1.25rem 0.75rem;
            background:var(--color-surface-soft);
            border:none;
            border-radius:18px;
            text-decoration:none;
            color:var(--color-text);
            font-size:0.82rem;
            font-weight:700;
            gap:0.6rem;
            text-align:center;
            transition:all var(--transition-base);
            box-shadow:0 2px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent);
        }
        .quick-action-tile i {
            font-size:1.5rem;
            color:var(--color-text);
        }
        .quick-action-tile:hover {
            background:var(--color-border);
            border:none;
            color:var(--color-text);
            transform:translateY(-3px);
            box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);
        }
        /* Soft tinted variants — borderless, deep icons */
        .quick-action-tile.qa-pink { background:var(--color-secondary-soft); border:none; }
        .quick-action-tile.qa-pink i { color:var(--color-secondary-text); }
        .quick-action-tile.qa-teal { background:var(--color-success-soft); border:none; }
        .quick-action-tile.qa-teal i { color:var(--color-success-text); }
        .quick-action-tile.qa-blue { background:var(--color-primary-soft); border:none; }
        .quick-action-tile.qa-blue i { color:var(--color-primary-text); }
        .quick-action-tile.qa-amber { background:var(--color-peach-soft); border:none; }
        .quick-action-tile.qa-amber i { color:var(--color-warning-text); }
        .quick-action-tile.qa-red { background:var(--color-danger-soft); border:none; }
        .quick-action-tile.qa-red i { color:var(--color-danger-text); }
        .quick-action-tile.qa-violet { background:var(--color-primary-soft); border:none; }
        .quick-action-tile.qa-violet i { color:var(--color-primary-text); }
        .quick-action-tile.qa-green { background:var(--color-success-soft); border:none; }
        .quick-action-tile.qa-green i { color:var(--color-success-text); }
        .quick-action-tile.qa-slate { background:var(--color-surface-soft); border:none; }
        .quick-action-tile.qa-slate i { color:var(--color-text); }

        /* ── 21. MISC ── */
        .text-muted    { color:var(--text-muted) !important; }  /* ↑ uses brighter variable now */
        .text-dim      { color:var(--text-dim, var(--text-muted)) !important; }
        .text-primary  { color:var(--primary-light) !important; }
        .text-success  { color:var(--success) !important; }
        .text-danger   { color:var(--danger) !important; }
        .text-warning  { color:var(--warning) !important; }
        .text-info     { color:var(--info) !important; }
        .bg-light      { background:var(--bg-card2) !important; }
        .bg-white      { background:var(--bg-card) !important; color:var(--text) !important; }
        .border-bottom { border-color:var(--border) !important; }
        .border-top    { border-color:var(--border) !important; }
        .border        { border-color:var(--border) !important; }
        hr             { border-color:var(--border); opacity:1; margin:1.25rem 0; }
        small          { color:var(--text-muted); }
        strong         { color:var(--text); font-weight:700; }

        /* ── Dropdown theming ── */
        .dropdown-menu {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:14px;
            box-shadow:var(--shadow-md);
            padding:0.5rem;
            min-width:180px;
        }
        .dropdown-item {
            color:var(--text);
            border-radius:10px;
            font-size:0.875rem;
            padding:0.5rem 0.85rem;
            transition:background var(--transition-fast), color var(--transition-fast);
        }
        .dropdown-item:hover, .dropdown-item:focus {
            background:var(--row-hover);
            color:var(--text);
        }
        .dropdown-item.text-danger { color:var(--danger) !important; }
        .dropdown-item.text-danger:hover { background:color-mix(in srgb, var(--color-danger) 10%, transparent); color:var(--danger) !important; }
        .dropdown-divider { border-color:var(--border); margin:0.35rem 0; }

        /* Buttons */
        .btn {
            border-radius:12px;
            font-weight:600;
            transition:transform var(--transition-fast), box-shadow var(--transition-fast), background var(--transition-fast), border-color var(--transition-fast), color var(--transition-fast);
        }
        .btn:hover {
            transform:translateY(-1px);
        }
        .btn-primary {
            box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 18%, transparent);
        }
        .btn-outline-primary,
        .btn-outline-secondary,
        .btn-outline-info,
        .btn-outline-warning,
        .btn-outline-danger {
            border-width:1.5px;
        }
        .table .btn-group .btn,
        .btn-group.table-actions .btn {
            min-width:2.35rem;
            display:inline-flex;
            align-items:center;
            justify-content:center;
        }

        /* ── Pagination ── */
        .pagination {
            gap:0.25rem;
            align-items:center;
        }
        .pagination .page-link {
            background:var(--bg-card2);
            border-color:var(--border);
            color:var(--text-muted);
            border-radius:10px !important;
            margin:0;
            min-width:2.5rem;
            text-align:center;
            font-size:0.85rem;
            font-weight:600;
            transition:all var(--transition-fast);
        }
        .pagination .page-link:hover {
            background:var(--primary-subtle);
            border-color:var(--primary);
            color:var(--primary-light);
        }
        .pagination .page-item.active .page-link {
            background:var(--primary);
            border-color:var(--primary);
            color:var(--color-on-solid);
            box-shadow:0 2px 10px var(--primary-glow);
        }
        .pagination .page-item.disabled .page-link {
            background:transparent;
            color:var(--text-dim, var(--text-muted));
            opacity:0.5;
        }

        /* Summary chips */
        .summary-chip {
            display:inline-flex;
            align-items:center;
            gap:0.4rem;
            padding:0.35rem 0.85rem;
            border-radius:20px;
            font-size:0.8rem;
            font-weight:600;
            border:1px solid;
        }
        .chip-primary { background:var(--primary-subtle); border-color:var(--border-glass); color:var(--primary-light); }
        .chip-warning { background:color-mix(in srgb, var(--color-warning) 12%, transparent); border-color:color-mix(in srgb, var(--color-warning) 30%, transparent); color:var(--color-warning-text); }
        .chip-danger  { background:color-mix(in srgb, var(--color-danger) 12%, transparent);  border-color:color-mix(in srgb, var(--color-danger) 30%, transparent);  color:var(--color-danger-text); }
        .chip-success { background:color-mix(in srgb, var(--color-success) 12%, transparent); border-color:color-mix(in srgb, var(--color-success) 30%, transparent);  color:var(--color-success-text); }
        .chip-info    { background:color-mix(in srgb, var(--color-info) 12%, transparent);  border-color:color-mix(in srgb, var(--color-info) 30%, transparent);   color:var(--color-info-text); }

        /* ── 22. FOOTER ── */
        .footer {
            background:var(--bg-card);
            border-top:1px solid var(--border);
            color:var(--text-muted);
            padding:0.85rem 0;
            text-align:center;
            font-size:0.8rem;
            margin-top:auto;
            margin-left:var(--sidebar-w);
            position:relative;
            z-index:1;
            transition:all 300ms ease-in-out;
        }
        body:not(.has-sidebar) .footer { margin-left:0; }
        body.sidebar-collapsed .footer { margin-left:var(--sidebar-collapsed-w); }

        /* ── 23. RESPONSIVE (adaptive breakpoint: 1024px, see §28) ── */
        @media (max-width: 1023.98px) {
            .main-content {
                margin-left:0;
                width:100%;
                max-width:100%;
                padding:1.25rem 1.25rem 4rem;
            }
            .footer { margin-left:0; }
            /* Layout B: sidebar becomes an off-canvas hamburger drawer */
            .sidebar {
                transform:translateX(-100%);
                top:60px;
                z-index:1040;
                box-shadow:4px 0 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 40%, transparent);
            }
            .sidebar.show { transform:translateX(0); }
        }

        @media (max-width: 768px) {
            .main-content { padding:1rem 1rem 3.5rem; }
            .page-hero { padding:1.25rem; }
            .page-hero-title { font-size:1.2rem; }
        }

        /* Sidebar overlay on mobile */
        .sidebar-overlay {
            display:none;
            position:fixed;
            inset:0;
            background:color-mix(in srgb, var(--color-surface-strong) 50%, transparent);
            z-index:1030;
            backdrop-filter:blur(3px);
        }
        .sidebar-overlay.show { display:block; }

        /* ═══════════════════════════════════════════════════════════
           27. GLOBAL MOBILE / ANY-SCREEN HARDENING
           - Fluid layout on any screen size (320px phones → 4K)
           - Nothing overflows horizontally; tables/cards scroll inside
           - Pinch-zoom is bounded by viewport (min 1x / max 5x)
        ═══════════════════════════════════════════════════════════ */
        html {
            -webkit-text-size-adjust:100%;
            text-size-adjust:100%;
            scroll-padding-top:76px;
        }
        html, body {
            max-width:100%;
            overflow-x:clip;
        }
        body {
            padding-bottom:env(safe-area-inset-bottom, 0px);
        }
        img, video, canvas, svg, iframe, .hero-img, .hero-img-container {
            max-width:100%;
            height:auto;
        }
        iframe { border:0; }
        pre, code { white-space:pre-wrap; word-break:break-word; }
        /* Never let dropdowns / popovers escape small viewports */
        .dropdown-menu {
            max-width:calc(100vw - 2rem);
            overflow-wrap:anywhere;
        }
        /* Tables & wide panels scroll INSIDE their card — page layout never breaks */
        .table-responsive, .table-card, .modern-table-wrap,
        .mctl-table-wrap, .cctl-table-wrap {
            overflow-x:auto !important;
            -webkit-overflow-scrolling:touch;
            max-width:100%;
        }
        .table-responsive table, .table-card table { white-space:normal; }
        .modern-table-wrap .modern-table { max-width:none; }
        /* Grids / toolbars wrap instead of overflowing */
        .main-content .d-flex { min-width:0; }
        .workspace-toolbar, .workspace-toolbar-actions, .info-strip,
        .section-chip-row, .table-actions {
            flex-wrap:wrap;
        }
        /* Radio-button groups (btn-check): selected option MUST visibly highlight.
           Portal themes paint .btn-outline-* white, which used to erase the
           checked state — these rules restore it everywhere, every portal. */
        body .main-content .btn-check:checked + .btn-outline-primary,
        body .btn-check:checked + .btn-outline-primary {
            background:linear-gradient(135deg, var(--color-secondary), var(--color-secondary-text)) !important;
            background-color:var(--color-secondary) !important;
            border-color:var(--color-secondary-text) !important;
            color:var(--color-on-solid) !important;
            box-shadow:0 6px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent) !important;
        }
        body .main-content .btn-check:checked + .btn-outline-secondary,
        body .btn-check:checked + .btn-outline-secondary {
            background:var(--dark-contrast, var(--color-surface-strong)) !important;
            border-color:var(--dark-contrast, var(--color-text)) !important;
            color:var(--color-on-solid) !important;
        }
        body .btn-check:focus-visible + .btn {
            outline:3px solid var(--focus-ring, color-mix(in srgb, var(--color-primary) 35%, transparent));
            outline-offset:2px;
        }

        /* ── Phones (≤576px): stack everything, keep items visible ── */
        @media (max-width: 576px) {
            body { font-size:14px; }
            .navbar { padding:0.5rem 0.75rem; }
            .navbar-brand { font-size:1.05rem; }
            .main-content { padding:0.9rem 0.75rem 5rem; }
            .page-hero { padding:1.1rem 1rem; border-radius:18px; }
            .page-hero-title { font-size:1.15rem; line-height:1.3; }
            .page-hero-subtitle { font-size:0.82rem; }
            .page-hero .d-flex.justify-content-between,
            .workspace-toolbar {
                flex-direction:column !important;
                align-items:stretch !important;
            }
            .page-hero .d-flex.justify-content-between .btn,
            .workspace-toolbar .btn {
                width:100%;
                justify-content:center;
            }
            .card-body { padding:1rem; }
            .stat-card .stat-number, .stat-number,
            .metric-card-value { font-size:1.7rem !important; }
            .metric-grid { grid-template-columns:1fr !important; }
            .split-panels { grid-template-columns:1fr !important; }
            .info-strip { gap:0.5rem; }
            .info-pill-card { flex:1 1 calc(50% - 0.5rem); min-width:0; }
            .btn, .btn-primary, .btn-dark, .btn-black-pill {
                min-height:44px;
            }
            .modal-dialog { margin:0.5rem; max-width:calc(100vw - 1rem); }
            body.patient-portal-body .footer { margin-bottom:0 !important; padding-bottom:calc(1.5rem + env(safe-area-inset-bottom, 0px)); }
        }
        /* ── Small tablets (≤768px): 2-col stats, wrapped filters ── */
        @media (max-width: 768px) {
            .metric-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); }
            .workspace-filter-grid > div { grid-column:span 12 !important; }
            .table td, .table th { padding:0.65rem 0.7rem; font-size:0.82rem; }
            .card-header { flex-direction:column; align-items:flex-start !important; }
            .capsule-chart-wrap { gap:8px; }
        }

        /* ═══════════════════════════════════════════════════════════
           28. RESPONSIVE ADAPTIVE ENFORCEMENT — @media (min-width: 1024px)?
           TRUE  (≥1024px, Layout A / Desktop): expanded sidebar nav +
                 multi-column grids & form fields (BP · Hgb · Weight · GA).
           FALSE (<1024px, Layout B / Mobile): sidebar collapses into the
                 hamburger drawer + every adaptive field stacks vertically.
           The <body data-layout> flag is set by matchMedia in the global
           JS below and mirrors the same 1024px check for scripting.
        ═══════════════════════════════════════════════════════════ */
        /* Layout A — Desktop (TRUE path): explicit multi-column enforcement */
        @media (min-width: 1024px) {
            [data-layout="desktop"] .sidebar {
                transform:none;
            }
            .rc-adaptive-2 { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:1rem; }
            .rc-adaptive-3 { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:1rem; }
            .rc-adaptive-4 { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:1rem; }
            .rc-adaptive-2 > *, .rc-adaptive-3 > *, .rc-adaptive-4 > * { min-width:0; }
        }
        /* Layout B — Mobile (FALSE path): hamburger nav + strict vertical stack */
        @media (max-width: 1023.98px) {
            [data-layout="mobile"] .main-content {
                margin-left:0 !important;
                width:100% !important;
                max-width:100% !important;
            }
            /* Every adaptive grid collapses to ONE column, in source order */
            .rc-adaptive-2, .rc-adaptive-3, .rc-adaptive-4 {
                display:flex !important;
                flex-direction:column !important;
                gap:0 !important;
            }
            .rc-adaptive-2 > *, .rc-adaptive-3 > *, .rc-adaptive-4 > * {
                width:100% !important;
                max-width:100% !important;
                margin-bottom:1rem;
            }
            .rc-adaptive-2 > *:last-child, .rc-adaptive-3 > *:last-child,
            .rc-adaptive-4 > *:last-child { margin-bottom:0; }
            /* Adaptive forms: Bootstrap col-md-*/col-lg-* fields (which would
               normally float into columns at ≥768px) stay strictly stacked */
            .rc-adaptive-form .row > [class*="col-md-"],
            .rc-adaptive-form .row > [class*="col-lg-"] {
                flex:0 0 100% !important;
                width:100% !important;
                max-width:100% !important;
            }
            /* Paired half-fields (e.g. Systolic/Diastolic) stack too */
            .rc-adaptive-form .row.g-2 > .col-6,
            .rc-adaptive-form .row.g-2 > [class*="col-"] {
                flex:0 0 100% !important;
                width:100% !important;
                max-width:100% !important;
            }
            /* Full-width action buttons for thumbs */
            .rc-adaptive-form .rc-form-actions {
                flex-direction:column !important;
                align-items:stretch !important;
            }
            .rc-adaptive-form .rc-form-actions .btn {
                width:100%;
                justify-content:center;
            }
        }

        /* ═══════════════════════════════════════════════════════════
           24. STAT CARDS  — used by all 3 dashboards
        ═══════════════════════════════════════════════════════════ */
        .stat-card {
            position:relative;
            overflow:hidden;
            border-radius:20px;
            padding:1.4rem 1.3rem;
            color:var(--color-on-solid);
            box-shadow:0 6px 28px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
            transition:transform 0.25s ease, box-shadow 0.25s ease;
            min-height:140px;
            display:flex;
            flex-direction:column;
            justify-content:flex-end;
        }
        .stat-card:hover {
            transform:translateY(-5px);
            box-shadow:0 14px 45px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 42%, transparent);
        }
        /* Colour variants */
        .stat-purple { background:linear-gradient(135deg, var(--color-primary), var(--primary)); }
        .stat-rose   { background:linear-gradient(135deg, var(--secondary), var(--color-secondary-text)); }
        .stat-cyan   { background:linear-gradient(135deg, var(--color-info), var(--color-info)); }
        .stat-amber  { background:linear-gradient(135deg, var(--color-warning), var(--color-warning-text)); }
        .stat-green  { background:linear-gradient(135deg, var(--color-success), var(--color-success-text)); }
        .stat-lime   { background:linear-gradient(135deg, var(--color-success), var(--color-success-text)); }
        .stat-violet { background:linear-gradient(135deg, var(--accent-violet), var(--color-primary)); }
        .stat-pink   { background:linear-gradient(135deg, var(--color-secondary), var(--accent-pink)); }
        .stat-indigo { background:linear-gradient(135deg, var(--color-primary), var(--color-primary)); }

        /* Decorative glare */
        .stat-card::before {
            content:'';
            position:absolute;
            top:-40px; right:-40px;
            width:130px; height:130px;
            border-radius:50%;
            background:color-mix(in srgb, var(--color-surface) 8%, transparent);
            pointer-events:none;
        }
        .stat-card::after {
            content:'';
            position:absolute;
            bottom:-30px; left:-20px;
            width:90px; height:90px;
            border-radius:50%;
            background:color-mix(in srgb, var(--color-surface) 5%, transparent);
            pointer-events:none;
        }

        /* Icon */
        .stat-icon {
            position:absolute;
            top:1.1rem; right:1.1rem;
            font-size:1.6rem;
            opacity:0.3;
        }

        /* Text elements */
        .stat-label {
            font-size:0.75rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:0.8px;
            opacity:0.85;
            margin-bottom:0.2rem;
        }
        .stat-number {
            font-family:'Plus Jakarta Sans', sans-serif;
            font-size:2.4rem;
            font-weight:800;
            line-height:1;
            margin-bottom:0.45rem;
            letter-spacing:-1px;
        }
        .stat-trend {
            font-size:0.78rem;
            opacity:0.85;
            display:flex;
            align-items:center;
            gap:0.25rem;
        }
        .stat-trend.up     { color:var(--color-success-text); }
        .stat-trend.danger { color:var(--color-danger-text); }

        /* ═══════════════════════════════════════════════════════════
           25. PAGE HERO  — masthead banner at top of dashboard pages
        ═══════════════════════════════════════════════════════════ */
        .page-hero {
            position:relative;
            overflow:hidden;
            border-radius:20px;
            padding:1.75rem 2rem;
            margin-bottom:1.75rem;
            background:linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 55%, var(--accent-pink) 100%);
            box-shadow:0 8px 40px var(--primary-glow), 0 2px 0 color-mix(in srgb, rgb(var(--color-shadow-rgb)) 7%, transparent) inset;
            transition:box-shadow 0.3s ease;
        }
        /* Orb decorations */
        .page-hero::before {
            content:'';
            position:absolute;
            top:-60px; right:-60px;
            width:220px; height:220px;
            background:color-mix(in srgb, var(--color-surface) 6%, transparent);
            border-radius:50%;
            pointer-events:none;
        }
        .page-hero::after {
            content:'';
            position:absolute;
            bottom:-50px; left:35%;
            width:160px; height:160px;
            background:color-mix(in srgb, var(--color-surface) 4%, transparent);
            border-radius:50%;
            pointer-events:none;
        }
        .page-hero-title {
            font-family:'Plus Jakarta Sans', sans-serif;
            font-size:1.4rem;
            font-weight:800;
            color:var(--color-on-solid);
            letter-spacing:-0.5px;
            text-shadow:0 1px 8px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent);
            margin:0;
        }
        .page-hero-subtitle {
            font-size:0.875rem;
            color:color-mix(in srgb, var(--color-on-solid) 75%, transparent);
            margin:0.3rem 0 0;
        }

        /* CTA button inside hero */
        .btn-hero-primary {
            display:inline-flex;
            align-items:center;
            gap:0.5rem;
            background:color-mix(in srgb, var(--color-surface) 18%, transparent);
            border:1.5px solid color-mix(in srgb, var(--color-border) 35%, transparent);
            color:var(--color-on-solid);
            font-weight:700;
            font-size:0.875rem;
            padding:0.55rem 1.2rem;
            border-radius:12px;
            text-decoration:none;
            backdrop-filter:blur(6px);
            transition:all 0.2s ease;
            white-space:nowrap;
        }
        .btn-hero-primary:hover {
            background:color-mix(in srgb, var(--color-surface) 30%, transparent);
            border-color:color-mix(in srgb, var(--color-border) 60%, transparent);
            color:var(--color-on-solid);
            transform:translateY(-2px);
            box-shadow:0 6px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent);
        }

        /* ═══════════════════════════════════════════════════════════
           26. COUNT-UP ANIMATION  — triggered by IntersectionObserver
        ═══════════════════════════════════════════════════════════ */
        .btn-hero-secondary {
            display:inline-flex;
            align-items:center;
            gap:0.5rem;
            background:color-mix(in srgb, var(--color-surface-strong) 16%, transparent);
            border:1.5px solid color-mix(in srgb, var(--color-border) 22%, transparent);
            color:color-mix(in srgb, var(--color-on-solid) 90%, transparent);
            font-weight:700;
            font-size:0.875rem;
            padding:0.55rem 1.2rem;
            border-radius:12px;
            text-decoration:none;
            transition:all 0.2s ease;
        }
        .btn-hero-secondary:hover {
            color:var(--color-on-solid);
            border-color:color-mix(in srgb, var(--color-border) 45%, transparent);
            background:color-mix(in srgb, var(--color-surface) 12%, transparent);
            transform:translateY(-2px);
        }
        .workspace-stack {
            display:flex;
            flex-direction:column;
            gap:1.5rem;
        }
        .workspace-toolbar {
            display:flex;
            align-items:center;
            justify-content:space-between;
            flex-wrap:wrap;
            gap:1rem;
        }
        .workspace-toolbar-actions {
            display:flex;
            align-items:center;
            flex-wrap:wrap;
            gap:0.75rem;
        }
        .workspace-panel {
            background:linear-gradient(180deg, color-mix(in srgb, var(--color-surface) 2%, transparent) 0%, color-mix(in srgb, var(--color-surface) 0%, transparent) 100%), var(--bg-card);
            border:1px solid var(--border);
            border-radius:22px;
            box-shadow:var(--shadow-sm);
            overflow:hidden;
        }
        .workspace-panel-header {
            padding:1.15rem 1.35rem 0;
        }
        .workspace-panel-title {
            display:flex;
            align-items:center;
            gap:0.65rem;
            margin:0;
            font-family:'Plus Jakarta Sans', sans-serif;
            font-size:1rem;
            font-weight:800;
            color:var(--text);
        }
        .workspace-panel-title i { color:var(--primary-light); }
        .workspace-panel-subtitle {
            margin:0.35rem 0 0;
            color:var(--text-muted);
            font-size:0.87rem;
        }
        .workspace-panel-body {
            padding:1.35rem;
        }
        .workspace-filter-grid {
            display:grid;
            grid-template-columns:repeat(12, minmax(0, 1fr));
            gap:1rem;
        }
        .workspace-filter-grid > div { grid-column:span 3; }
        .workspace-filter-grid .span-2 { grid-column:span 2; }
        .workspace-filter-grid .span-4 { grid-column:span 4; }
        .workspace-filter-grid .span-5 { grid-column:span 5; }
        .workspace-filter-grid .span-6 { grid-column:span 6; }
        .workspace-filter-grid .span-8 { grid-column:span 8; }
        .workspace-filter-actions {
            display:flex;
            align-items:end;
            flex-wrap:wrap;
            gap:0.75rem;
        }
        .metric-grid {
            display:grid;
            grid-template-columns:repeat(4, minmax(0, 1fr));
            gap:1rem;
        }
        .metric-card {
            position:relative;
            overflow:hidden;
            min-height:148px;
            border-radius:22px;
            padding:1.2rem 1.15rem;
            color:var(--color-on-solid);
            box-shadow:0 20px 40px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent);
        }
        .metric-card::before {
            content:'';
            position:absolute;
            top:-52px;
            right:-28px;
            width:128px;
            height:128px;
            border-radius:50%;
            background:color-mix(in srgb, var(--color-surface) 10%, transparent);
        }
        .metric-card::after {
            content:'';
            position:absolute;
            left:-18px;
            bottom:-42px;
            width:96px;
            height:96px;
            border-radius:50%;
            background:color-mix(in srgb, var(--color-surface) 8%, transparent);
        }
        .metric-card-icon {
            position:absolute;
            top:1rem;
            right:1rem;
            font-size:1.55rem;
            opacity:0.35;
        }
        .metric-card-label {
            position:relative;
            z-index:1;
            font-size:0.76rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:0.08em;
            opacity:0.88;
        }
        .metric-card-value {
            position:relative;
            z-index:1;
            margin-top:1.25rem;
            font-family:'Plus Jakarta Sans', sans-serif;
            font-size:2rem;
            font-weight:800;
            line-height:1;
            letter-spacing:-0.04em;
        }
        .metric-card-note {
            position:relative;
            z-index:1;
            margin-top:0.6rem;
            font-size:0.82rem;
            opacity:0.86;
        }
        .metric-card-primary { background:linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary) 100%); }
        .metric-card-cyan { background:linear-gradient(135deg, var(--color-info) 0%, var(--color-info) 100%); }
        .metric-card-green { background:linear-gradient(135deg, var(--color-success) 0%, var(--color-success-text) 100%); }
        .metric-card-amber { background:linear-gradient(135deg, var(--color-warning) 0%, var(--color-peach) 100%); }
        .metric-card-rose { background:linear-gradient(135deg, var(--color-secondary) 0%, var(--color-secondary) 100%); }
        .metric-card-indigo { background:linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary) 100%); }
        .info-strip {
            display:flex;
            flex-wrap:wrap;
            gap:0.75rem;
        }
        .info-pill-card {
            flex:1;
            min-width:100px;
            padding:0.75rem;
            border-radius:12px;
            border:1px solid var(--border);
            background:var(--bg-card2);
            text-align:center;
        }
        .info-pill-label {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:0.35rem;
            font-size:0.7rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:0.05em;
            color:var(--text-muted);
        }
        .info-pill-value {
            margin-top:0.4rem;
            font-size:0.9rem;
            font-weight:700;
            color:var(--text);
            line-height:1.2;
        }
        .split-panels {
            display:grid;
            grid-template-columns:repeat(2, minmax(0, 1fr));
            gap:1.5rem;
        }
        .modern-table-wrap {
            overflow-x:auto;
        }
        .modern-table {
            width:100%;
            min-width:720px;
            border-collapse:separate;
            border-spacing:0;
        }
        .modern-table thead th {
            position:sticky;
            top:0;
            background:var(--bg-card2);
            color:var(--text-muted);
            font-size:0.76rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:0.08em;
            border-bottom:1px solid var(--border);
            padding:0.95rem 1rem;
            z-index:1;
        }
        .modern-table tbody td {
            padding:1rem;
            border-bottom:1px solid var(--border);
            vertical-align:top;
        }
        .modern-table tbody tr:nth-child(even) { background:var(--row-alt); }
        .modern-table tbody tr:hover { background:var(--row-hover); }
        .modern-table tbody tr:last-child td { border-bottom:none; }
        .table-title {
            font-weight:700;
            color:var(--text);
        }
        .table-subtitle {
            margin-top:0.2rem;
            color:var(--text-muted);
            font-size:0.82rem;
        }
        .table-meta-stack {
            display:flex;
            flex-direction:column;
            gap:0.25rem;
        }
        .table-actions {
            display:inline-flex;
            gap:0.45rem;
            flex-wrap:wrap;
            justify-content:flex-end;
        }
        .section-chip-row {
            display:flex;
            flex-wrap:wrap;
            gap:0.55rem;
        }
        .empty-state-panel {
            padding:3rem 1.5rem;
            text-align:center;
        }
        .empty-state-panel i {
            font-size:3rem;
            color:var(--text-dim);
        }
        .empty-state-panel h3,
        .empty-state-panel h5 {
            margin-top:1rem;
            color:var(--text);
        }
        .empty-state-panel p {
            max-width:520px;
            margin:0.65rem auto 0;
            color:var(--text-muted);
        }
        .selection-box {
            max-height:240px;
            overflow-y:auto;
            padding:0.35rem;
            border-radius:16px;
            border:1px solid var(--border);
            background:var(--bg-card2);
        }
        .selection-option {
            display:flex;
            gap:0.8rem;
            align-items:start;
            padding:0.85rem 0.9rem;
            border-radius:14px;
            transition:background var(--transition-fast);
        }
        .selection-option:hover { background:var(--row-hover); }
        .selection-option .form-check-input { margin-top:0.2rem; }
        .selection-option-title {
            font-weight:600;
            color:var(--text);
        }
        .selection-option-note {
            margin-top:0.2rem;
            font-size:0.82rem;
            color:var(--text-muted);
        }
        .insight-card {
            border-radius:20px;
            border:1px solid var(--border);
            background:var(--bg-card2);
            padding:1.15rem;
        }
        .insight-card h6 {
            margin-bottom:0.75rem;
            font-weight:800;
            color:var(--text);
        }
        .insight-list {
            display:flex;
            flex-direction:column;
            gap:0.8rem;
        }
        .insight-list-item {
            display:flex;
            gap:0.75rem;
            align-items:start;
            color:var(--text-muted);
            font-size:0.86rem;
            line-height:1.55;
        }
        .insight-list-item i {
            color:var(--primary-light);
            margin-top:0.1rem;
        }
        @media (max-width: 1200px) {
            .metric-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); }
            .workspace-filter-grid > div,
            .workspace-filter-grid .span-2,
            .workspace-filter-grid .span-4,
            .workspace-filter-grid .span-5,
            .workspace-filter-grid .span-6,
            .workspace-filter-grid .span-8 {
                grid-column:span 6;
            }
        }
        @media (max-width: 1023.98px) {
            .split-panels,
            .metric-grid {
                grid-template-columns:1fr;
            }
            .info-pill-card {
                min-width:80px;
                padding:0.5rem;
            }
        }
        @media (max-width: 768px) {
            .workspace-filter-grid > div,
            .workspace-filter-grid .span-2,
            .workspace-filter-grid .span-4,
            .workspace-filter-grid .span-5,
            .workspace-filter-grid .span-6,
            .workspace-filter-grid .span-8 {
                grid-column:span 12;
            }
            .workspace-panel-body,
            .workspace-panel-header {
                padding-left:1rem;
                padding-right:1rem;
            }
        }

        @keyframes countPulse {
            0%   { transform:scale(1); }
            50%  { transform:scale(1.08); }
            100% { transform:scale(1); }
        }
        .stat-number.counted { animation:countPulse 0.35s ease; }

        /* Patient Portal Overrides (No admin sidebar, full-width consumer experience) */
        body.patient-portal-body {
            background-color:var(--color-peach-soft) !important;
            font-family:'Inter', sans-serif;
            overflow-x:hidden;
            padding-top:0 !important;
        }
        body.patient-portal-body .main-content {
            margin-left:0 !important;
            width:100% !important;
            max-width:100% !important;
            padding:0 !important;
            min-height:calc(100vh - 120px);
        }
        body.patient-portal-body .sidebar,
        body.patient-portal-body .sidebar-overlay {
            display:none !important;
        }
        body.patient-portal-body .footer {
            margin-left:0 !important;
            background:var(--color-surface);
            border-top:1px solid var(--color-peach-soft);
            color:var(--color-text-muted);
            padding:1.5rem 1rem;
            text-align:center;
            font-size:0.82rem;
        }
        @media (max-width: 768px) {
            body.patient-portal-body .footer {
                margin-bottom:74px !important; /* space for mobile bottom dock */
            }
        }

        /* ============================================================
           GLOBAL DARK MODE — Exact Palette (all portals)
           bg #18191A · surfaces #242526 · borders #3A3B3C
           text #E4E6EB · muted #B0B3B8 · hover #3A3B3C
           ============================================================ */
        html { color-scheme:light; }
        [data-theme="dark"], [data-theme="dark"] body { color-scheme:dark; }

        /* Global theme toggle icon swap */
        .rc-theme-toggle .icon-sun { display:none; }
        [data-theme="dark"] .rc-theme-toggle .icon-moon { display:none; }
        [data-theme="dark"] .rc-theme-toggle .icon-sun { display:inline-block; }
        [data-theme="dark"] .rc-theme-toggle { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }

        /* Re-point the design-system variables at the exact dark palette */
        

        /* ── Base ── */
        [data-theme="dark"] body { background-color:var(--color-bg) !important; color:var(--color-text) !important; }
        [data-theme="dark"] h1, [data-theme="dark"] h2, [data-theme="dark"] h3,
        [data-theme="dark"] h4, [data-theme="dark"] h5, [data-theme="dark"] h6 { color:var(--color-text) !important; }
        [data-theme="dark"] hr { border-color:var(--color-border) !important; }
        [data-theme="dark"] body.patient-portal-body { background-color:var(--color-bg) !important; }
        [data-theme="dark"] body.patient-portal-body .footer { background:var(--color-surface) !important; border-top-color:var(--color-border) !important; color:var(--color-text-muted) !important; }

        /* ── Bootstrap utility normalizers (kill invisible-text glitches) ── */
        [data-theme="dark"] .bg-white { background-color:var(--color-surface) !important; }
        [data-theme="dark"] .bg-light { background-color:var(--color-border) !important; }
        [data-theme="dark"] .bg-black { background-color:var(--color-surface-strong) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .text-dark, [data-theme="dark"] .text-black { color:var(--color-text) !important; }
        [data-theme="dark"] .text-muted { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .text-secondary { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .text-slate-800 { color:var(--color-text) !important; }
        [data-theme="dark"] .text-slate-400 { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .border, [data-theme="dark"] .border-top, [data-theme="dark"] .border-bottom,
        [data-theme="dark"] .border-start, [data-theme="dark"] .border-end { border-color:var(--color-border) !important; }
        [data-theme="dark"] .border-slate-200 { border-color:var(--color-border) !important; }
        [data-theme="dark"] .shadow-sm, [data-theme="dark"] .shadow { box-shadow:0 2px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 45%, transparent) !important; }

        /* ── Navbar / Sidebar ── */
        [data-theme="dark"] .navbar { background:color-mix(in srgb, var(--color-surface) 95%, transparent) !important; border-bottom-color:var(--color-border) !important; }
        [data-theme="dark"] .navbar-brand { color:var(--color-text) !important; }
        [data-theme="dark"] .sidebar { background:var(--color-surface) !important; border-right-color:var(--color-border) !important; }
        [data-theme="dark"] .sidebar .nav-link { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .sidebar .nav-link:hover { background:var(--color-border) !important; color:var(--color-secondary-text) !important; }
        [data-theme="dark"] .sidebar .nav-link:hover i { color:var(--color-secondary-text) !important; }
        [data-theme="dark"] .sidebar .nav-link.active,
        [data-theme="dark"] body .layout-wrapper nav.sidebar .nav-link.active,
        [data-theme="dark"] body nav.sidebar .nav-link.active { background:var(--color-surface) !important; background-color:var(--color-surface) !important; color:var(--color-text) !important; box-shadow:0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 45%, transparent) !important; }
        [data-theme="dark"] .sidebar .nav-link.active i,
        [data-theme="dark"] body nav.sidebar .nav-link.active i { color:var(--color-text) !important; }
        [data-theme="dark"] .sidebar .nav-link.active:hover,
        [data-theme="dark"] body nav.sidebar .nav-link.active:hover { background:color-mix(in srgb, var(--color-surface) 86%, black) !important; background-color:color-mix(in srgb, var(--color-surface) 86%, black) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .sidebar .nav-link.active:hover i,
        [data-theme="dark"] body nav.sidebar .nav-link.active:hover i { color:var(--color-text) !important; }
        [data-theme="dark"] .sidebar-section-label { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .sidebar-footer { border-top-color:var(--color-border) !important; }
        [data-theme="dark"] .sidebar-footer-name { color:var(--color-text) !important; }
        [data-theme="dark"] .sidebar-edge-toggle { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .sidebar-edge-toggle:hover { color:var(--color-text) !important; border-color:var(--color-border) !important; background:var(--color-surface) !important; }

        /* ── Layout surfaces (all role portals) ── */
        [data-theme="dark"] .main-content { background-color:var(--color-bg) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .page-hero { background:var(--color-surface) !important; background-color:var(--color-surface) !important; border:1px solid var(--color-border) !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 45%, transparent) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .page-hero-title, [data-theme="dark"] .page-title { color:var(--color-text) !important; }
        [data-theme="dark"] .page-hero-subtitle, [data-theme="dark"] .page-subtitle { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .card, [data-theme="dark"] .glass-card, [data-theme="dark"] .table-card { background:var(--color-surface) !important; background-color:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .card:hover { border-color:var(--color-border) !important; box-shadow:0 8px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 55%, transparent) !important; transform:none !important; }
        [data-theme="dark"] .card-header, [data-theme="dark"] .card-footer { border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .card-header h5, [data-theme="dark"] .card-header h6 { color:var(--color-text) !important; }
        [data-theme="dark"] .card-body { color:var(--color-text) !important; }
        [data-theme="dark"] .list-group-item { background:transparent !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .list-group-item-action:hover { background:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .timeline-content { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .timeline-time { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .empty-state h6 { color:var(--color-text) !important; }
        [data-theme="dark"] .empty-state p { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .women-shell { background-color:var(--color-bg) !important; }

        /* ── Stat cards: elevated surface + accent top border, off-white metrics ── */
        [data-theme="dark"] .stat-card, [data-theme="dark"] .metric-card,
        [data-theme="dark"] .stat-card-peach, [data-theme="dark"] .stat-card-blue,
        [data-theme="dark"] .stat-card-lavender, [data-theme="dark"] .stat-card-mint,
        [data-theme="dark"] .stat-purple, [data-theme="dark"] .stat-rose,
        [data-theme="dark"] .stat-cyan, [data-theme="dark"] .stat-green,
        [data-theme="dark"] .stat-amber, [data-theme="dark"] .stat-lime, [data-theme="dark"] .stat-violet {
            background:var(--color-surface) !important; background-color:var(--color-surface) !important;
            border:1px solid var(--color-border) !important; border-top:3px solid var(--color-primary) !important;
            color:var(--color-text) !important; box-shadow:0 2px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 45%, transparent) !important;
        }
        [data-theme="dark"] .stat-card-peach { border-top-color:var(--color-peach) !important; }
        [data-theme="dark"] .stat-card-blue, [data-theme="dark"] .stat-cyan { border-top-color:var(--color-info) !important; }
        [data-theme="dark"] .stat-card-mint, [data-theme="dark"] .stat-green, [data-theme="dark"] .stat-lime { border-top-color:var(--color-success) !important; }
        [data-theme="dark"] .stat-amber, [data-theme="dark"] .stat-rose { border-top-color:var(--color-secondary) !important; }
        [data-theme="dark"] .stat-card .stat-label, [data-theme="dark"] .stat-label, [data-theme="dark"] .metric-card-label { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .stat-card .stat-number, [data-theme="dark"] .stat-number, [data-theme="dark"] .metric-card-value { color:var(--color-text) !important; }
        [data-theme="dark"] .stat-card .stat-trend, [data-theme="dark"] .stat-trend { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .stat-card .stat-icon, [data-theme="dark"] .stat-icon, [data-theme="dark"] .metric-card-icon { background:color-mix(in srgb, var(--color-primary) 16%, transparent) !important; color:var(--color-primary-text) !important; box-shadow:none !important; }

        /* ── Tables ── */
        [data-theme="dark"] .table { color:var(--color-text) !important; --bs-table-bg:transparent; }
        [data-theme="dark"] .table thead th { background:var(--color-surface) !important; color:var(--color-text-muted) !important; border-bottom-color:var(--color-border) !important; }
        [data-theme="dark"] .table td, [data-theme="dark"] .table th { border-color:var(--color-border) !important; }
        [data-theme="dark"] .table tbody td { color:var(--color-text) !important; background:var(--color-surface) !important; }
        [data-theme="dark"] .table-hover tbody tr:hover td { background:var(--color-border) !important; }
        [data-theme="dark"] .table-striped tbody tr:nth-of-type(odd) td { background:color-mix(in srgb, var(--color-surface) 3%, transparent) !important; }

        /* ── Forms: dark inputs, crisp text + placeholders ── */
        [data-theme="dark"] .form-control, [data-theme="dark"] .form-select {
            background:var(--color-bg) !important; background-color:var(--color-bg) !important;
            border-color:var(--color-border) !important; color:var(--color-text) !important;
        }
        [data-theme="dark"] .form-control:focus, [data-theme="dark"] .form-select:focus {
            background:var(--color-bg) !important; border-color:var(--color-primary) !important; color:var(--color-text) !important;
            box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent) !important;
        }
        [data-theme="dark"] .form-control::placeholder, [data-theme="dark"] textarea.form-control::placeholder { color:var(--color-text-muted) !important; opacity:1; }
        [data-theme="dark"] .form-label { color:var(--color-text) !important; }
        [data-theme="dark"] .form-text, [data-theme="dark"] .form-check-label { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .form-select option { background:var(--color-surface) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .input-group-text { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text-muted) !important; }
        [data-theme="dark"] input[type="date"]::-webkit-calendar-picker-indicator,
        [data-theme="dark"] input[type="time"]::-webkit-calendar-picker-indicator { filter:invert(0.8); }

        /* ── Badges & status pills: translucent tints, high-contrast text ── */
        [data-theme="dark"] .badge.bg-primary { background:color-mix(in srgb, var(--color-primary) 25%, transparent) !important; color:var(--color-primary-text) !important; }
        [data-theme="dark"] .badge.bg-secondary { background:var(--color-border) !important; color:var(--color-text) !important; border:1px solid var(--color-border) !important; }
        [data-theme="dark"] .badge.bg-success { background:color-mix(in srgb, var(--color-success) 20%, transparent) !important; color:var(--color-success-text) !important; }
        [data-theme="dark"] .badge.bg-danger { background:color-mix(in srgb, var(--color-danger) 20%, transparent) !important; color:var(--color-danger-text) !important; }
        [data-theme="dark"] .badge.bg-warning { background:color-mix(in srgb, var(--color-warning) 20%, transparent) !important; color:var(--color-warning-text) !important; }
        [data-theme="dark"] .badge.bg-info { background:color-mix(in srgb, var(--color-info) 20%, transparent) !important; color:var(--color-info-text) !important; }
        [data-theme="dark"] .status-scheduled { background:color-mix(in srgb, var(--color-info) 18%, transparent) !important; color:var(--color-info-text) !important; }
        [data-theme="dark"] .status-completed, [data-theme="dark"] .status-active { background:color-mix(in srgb, var(--color-success) 18%, transparent) !important; color:var(--color-success-text) !important; }
        [data-theme="dark"] .status-missed { background:color-mix(in srgb, var(--color-danger) 18%, transparent) !important; color:var(--color-danger-text) !important; }
        [data-theme="dark"] .status-pending { background:color-mix(in srgb, var(--color-warning) 18%, transparent) !important; color:var(--color-peach-text) !important; }
        [data-theme="dark"] .status-cancelled { background:color-mix(in srgb, var(--color-primary) 20%, transparent) !important; color:var(--color-primary-text) !important; }
        [data-theme="dark"] .summary-chip.chip-primary { background:color-mix(in srgb, var(--color-primary) 18%, transparent) !important; border-color:color-mix(in srgb, var(--color-primary) 40%, transparent) !important; color:var(--color-primary-text) !important; }
        [data-theme="dark"] .summary-chip.chip-danger { background:color-mix(in srgb, var(--color-danger) 16%, transparent) !important; border-color:color-mix(in srgb, var(--color-danger) 40%, transparent) !important; color:var(--color-danger-text) !important; }
        [data-theme="dark"] .summary-chip.chip-warning { background:color-mix(in srgb, var(--color-warning) 16%, transparent) !important; border-color:color-mix(in srgb, var(--color-warning) 40%, transparent) !important; color:var(--color-warning-text) !important; }
        [data-theme="dark"] .summary-chip.chip-success { background:color-mix(in srgb, var(--color-success) 16%, transparent) !important; border-color:color-mix(in srgb, var(--color-success) 40%, transparent) !important; color:var(--color-success-text) !important; }
        [data-theme="dark"] .summary-chip.chip-info { background:color-mix(in srgb, var(--color-info) 16%, transparent) !important; border-color:color-mix(in srgb, var(--color-info) 40%, transparent) !important; color:var(--color-info-text) !important; }

        /* ── Buttons / dropdowns / modals / pagination ── */
        [data-theme="dark"] .btn-light { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .btn-outline-secondary { border-color:var(--color-border) !important; color:var(--color-text-muted) !important; }
        [data-theme="dark"] .btn-outline-secondary:hover { background:var(--color-border) !important; color:var(--color-text) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .btn-close { filter:invert(0.8) !important; }
        [data-theme="dark"] .dropdown-menu { background:var(--color-surface) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .dropdown-item { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .dropdown-item:hover { background:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .dropdown-divider { border-color:var(--color-border) !important; }
        [data-theme="dark"] .modal-content { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .modal-footer { background:var(--color-surface) !important; border-top-color:var(--color-border) !important; }
        [data-theme="dark"] .pagination .page-link { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text-muted) !important; }
        [data-theme="dark"] .pagination .page-item.active .page-link { background:var(--color-primary) !important; border-color:var(--color-primary) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .pagination .page-link:hover { background:var(--color-border) !important; color:var(--color-text) !important; }

        /* ── Women (patient) portal nav ── */
        [data-theme="dark"] .women-navbar { background:var(--color-surface) !important; border-bottom-color:var(--color-border) !important; }
        [data-theme="dark"] .women-brand-title { color:var(--color-text) !important; }
        [data-theme="dark"] .women-tab-link { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .women-tab-link:hover { background:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .women-tab-link.active { background:var(--color-primary) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .women-dropdown-item { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .women-dropdown-item:hover, [data-theme="dark"] .women-dropdown-item.active { background:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .women-profile-name { color:var(--color-text) !important; }
        [data-theme="dark"] .women-bottom-dock { background:color-mix(in srgb, var(--color-surface) 94%, transparent) !important; border-top-color:var(--color-border) !important; }
        [data-theme="dark"] .women-dock-link { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .women-dock-link.active { color:var(--color-text) !important; background:var(--color-primary) !important; }
        [data-theme="dark"] .btn-care-support { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .women-bell-btn { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }

        /* ── Chat UIs (messages + SMS, all portals) ── */
        [data-theme="dark"] .rc-msg-app, [data-theme="dark"] .rc-thread, [data-theme="dark"] .rc-compose, [data-theme="dark"] .rc-sms { color:var(--color-text) !important; }
        [data-theme="dark"] .rc-msg-hero, [data-theme="dark"] .rc-compose-hero, [data-theme="dark"] .rc-sms-hero { background:var(--color-surface) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .rc-msg-hero h1, [data-theme="dark"] .rc-compose-hero h2, [data-theme="dark"] .rc-sms-hero h1,
        [data-theme="dark"] .rc-panel-head h3, [data-theme="dark"] .rc-chat-name, [data-theme="dark"] .rc-peer-name,
        [data-theme="dark"] .rc-thread-peer h4, [data-theme="dark"] .rc-conv-name, [data-theme="dark"] .rc-contact h6 { color:var(--color-text) !important; }
        [data-theme="dark"] .rc-msg-hero p, [data-theme="dark"] .rc-chat-preview, [data-theme="dark"] .rc-chat-time,
        [data-theme="dark"] .rc-conv-phone, [data-theme="dark"] .rc-conv-preview, [data-theme="dark"] .rc-conv-time { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .rc-panel, [data-theme="dark"] .rc-chat-card { background:var(--color-surface) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .rc-panel-head, [data-theme="dark"] .rc-chat-topbar, [data-theme="dark"] .rc-thread-head,
        [data-theme="dark"] .rc-composer, [data-theme="dark"] .rc-chat-composer-wrap { background:var(--color-surface) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .rc-chat-item:hover, [data-theme="dark"] .rc-chat-item.unread, [data-theme="dark"] .rc-conv:hover, [data-theme="dark"] .rc-conv.active,
        [data-theme="dark"] .rc-contact:hover, [data-theme="dark"] .rc-contact-opt:hover, [data-theme="dark"] .rc-contact-opt.active { background:var(--color-border) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .rc-stream, [data-theme="dark"] .rc-chat-stream-container, [data-theme="dark"] .rc-thread-body { background:var(--color-bg) !important; }
        [data-theme="dark"] .rc-row.theirs .rc-bubble, [data-theme="dark"] .chat-row.theirs .chat-bubble { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .rc-row.theirs .rc-meta, [data-theme="dark"] .chat-row.theirs .bubble-meta { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .rc-day, [data-theme="dark"] .date-separator { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text-muted) !important; }
        [data-theme="dark"] .rc-search input, [data-theme="dark"] .rc-contact-search, [data-theme="dark"] .msg-search-input,
        [data-theme="dark"] .rc-composer-bar, [data-theme="dark"] .composer-pill-bar { background:var(--color-bg) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .rc-composer-bar textarea, [data-theme="dark"] .composer-input-field,
        [data-theme="dark"] .rc-input, [data-theme="dark"] .rc-textarea { color:var(--color-text) !important; }
        [data-theme="dark"] .rc-filter-row select, [data-theme="dark"] .msg-search-input { background:var(--color-bg) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .rc-empty h6 { color:var(--color-text) !important; }
        [data-theme="dark"] .rc-empty p { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .rc-card, [data-theme="dark"] .compose-panel { background:var(--color-surface) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .rc-contact-opt .rc-nm { color:var(--color-text) !important; }

        /* ── Quick actions / misc portal components ── */
        [data-theme="dark"] .quick-action-tile { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .quick-action-tile:hover { background:var(--color-border) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .capsule-bar { background:var(--color-border) !important; }
        [data-theme="dark"] .capsule-bar.active { background:var(--color-primary) !important; }
        [data-theme="dark"] .skeleton { border-radius:10px; }
        [data-theme="dark"] .activity-timeline .timeline-content { color:var(--color-text) !important; }

        /* ── Card headers/footers: explicit dark surface (component stylesheets paint these white) ── */
        [data-theme="dark"] .card-header, [data-theme="dark"] .card-footer {
            background:var(--color-surface) !important; background-color:var(--color-surface) !important;
            border-color:var(--color-border) !important; color:var(--color-text) !important;
        }

        /* ── Notifications page components ── */
        [data-theme="dark"] .ntf-btn { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text-muted) !important; }
        [data-theme="dark"] .ntf-btn:hover { border-color:var(--color-secondary-soft) !important; color:var(--color-secondary-text) !important; background:var(--color-border) !important; }
        [data-theme="dark"] .ntf-btn-green:hover { border-color:var(--color-success) !important; color:var(--color-success-text) !important; }
        [data-theme="dark"] .ntf-btn-red:hover { border-color:var(--color-danger-soft) !important; color:var(--color-danger-text) !important; }
        [data-theme="dark"] .ntf-title { color:var(--color-text) !important; }
        [data-theme="dark"] .ntf-msg, [data-theme="dark"] .ntf-date { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .ntf-pill-read { background:color-mix(in srgb, var(--color-surface) 8%, transparent) !important; color:var(--color-text-muted) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .notif-row:hover { background:var(--color-border) !important; }
        [data-theme="dark"] .notif-row.unread { background:color-mix(in srgb, var(--color-primary) 12%, transparent) !important; }
        [data-theme="dark"] .notif-status-read { background:color-mix(in srgb, var(--color-surface) 8%, transparent) !important; border-color:var(--color-border) !important; color:var(--color-text-muted) !important; }
        [data-theme="dark"] .notif-status-unread { background:color-mix(in srgb, var(--color-primary) 22%, transparent) !important; border-color:color-mix(in srgb, var(--color-primary) 45%, transparent) !important; color:var(--color-primary-text) !important; }
        [data-theme="dark"] .notif-filter-row, [data-theme="dark"] .notif-stats-bar { border-color:var(--color-border) !important; }
        [data-theme="dark"] .notif-stats-bar { background:var(--color-surface) !important; }
        [data-theme="dark"] .notif-stat-item { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .notif-stat-item strong { color:var(--color-text) !important; }
        [data-theme="dark"] .notif-pill { border-color:var(--color-border) !important; color:var(--color-text-muted) !important; }
        [data-theme="dark"] .notif-icon-info { background:color-mix(in srgb, var(--color-info-text) 20%, transparent) !important; color:var(--color-info-text) !important; }
        [data-theme="dark"] .notif-icon-warning { background:color-mix(in srgb, var(--color-warning) 22%, transparent) !important; color:var(--color-warning-text) !important; }
        [data-theme="dark"] .notif-icon-success { background:color-mix(in srgb, var(--color-success-text) 22%, transparent) !important; color:var(--color-success-text) !important; }
        [data-theme="dark"] .notif-icon-danger { background:color-mix(in srgb, var(--color-danger) 22%, transparent) !important; color:var(--color-danger-text) !important; }
        [data-theme="dark"] .notif-body .notif-title { color:var(--color-text) !important; }
        [data-theme="dark"] .notif-body .notif-preview, [data-theme="dark"] .notif-meta { color:var(--color-text-muted) !important; }

        /* ── Chat contact cards + SMS pager ── */
        [data-theme="dark"] .rc-contact { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .rc-contact h6 { color:var(--color-text) !important; }
        [data-theme="dark"] .rc-contact small { color:var(--color-secondary-text) !important; }
        [data-theme="dark"] .rc-pager { background:var(--color-surface) !important; border-top-color:var(--color-border) !important; }
        [data-theme="dark"] .rc-pager .page-item .page-link { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text-muted) !important; }
        [data-theme="dark"] .rc-pager .page-item.active .page-link { background:var(--color-secondary) !important; border-color:var(--color-secondary) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .rc-pager p.small, [data-theme="dark"] .rc-pager .small { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .rc-stat-pill { background:var(--color-surface) !important; border-color:var(--color-border) !important; color:var(--color-text-muted) !important; }
        [data-theme="dark"] .rc-stat-pill b { color:var(--color-secondary-text) !important; }

        /* ── Midwife settings panels ── */
        [data-theme="dark"] .settings-nav { background:var(--color-surface) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .settings-nav-item { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .settings-nav-item:hover { background:var(--color-border) !important; color:var(--color-text) !important; }
        [data-theme="dark"] .settings-nav-item.active { background:color-mix(in srgb, var(--color-secondary) 16%, transparent) !important; color:var(--color-secondary-text) !important; border-color:color-mix(in srgb, var(--color-secondary) 40%, transparent) !important; }
        [data-theme="dark"] .pref-card { background:var(--color-surface) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .pref-card-header { border-color:var(--color-border) !important; }
        [data-theme="dark"] .pref-card-header h6 { color:var(--color-text) !important; }
        [data-theme="dark"] .pref-card-header p { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .pref-card-header-icon { background:color-mix(in srgb, var(--color-secondary) 16%, transparent) !important; border-color:var(--color-border) !important; color:var(--color-secondary-text) !important; }
        [data-theme="dark"] .pref-row { border-color:var(--color-border) !important; }
        [data-theme="dark"] .pref-row-label h6 { color:var(--color-text) !important; }
        [data-theme="dark"] .pref-row-label p { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .settings-content .form-label, [data-theme="dark"] .pref-card-body .form-label { color:var(--color-text) !important; }
        [data-theme="dark"] .theme-option { background:var(--color-surface) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .theme-option.active { background:color-mix(in srgb, var(--color-secondary) 12%, transparent) !important; border-color:var(--color-secondary) !important; }
        [data-theme="dark"] .theme-option-label { color:var(--color-text) !important; }
        [data-theme="dark"] .theme-toggle-row { background:var(--color-surface) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .theme-toggle-row span { color:var(--color-text-muted) !important; }
        [data-theme="dark"] .clinical-input[readonly] { background:var(--color-bg) !important; color:var(--color-text) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .oversight-table thead th { color:var(--color-text-muted) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .oversight-table tbody td { color:var(--color-text) !important; border-color:var(--color-border) !important; }
        [data-theme="dark"] .verified-badge.ok { background:color-mix(in srgb, var(--color-success) 16%, transparent) !important; color:var(--color-success-text) !important; border-color:color-mix(in srgb, var(--color-success) 40%, transparent) !important; }
        [data-theme="dark"] .verified-badge.warn { background:color-mix(in srgb, var(--color-warning) 16%, transparent) !important; color:var(--color-peach-text) !important; border-color:color-mix(in srgb, var(--color-warning) 40%, transparent) !important; }
    </style>
    @vite('resources/css/theme.css')
    <script src="{{ asset('js/chart-palette.js') }}?v={{ filemtime(public_path('js/chart-palette.js')) }}"></script>
</head>
<body class="{{ auth()->check() && auth()->user()->role !== 'user' ? 'has-sidebar' : 'patient-portal-body' }}">
    @include('includes.navigation')

    @yield('content')

    <footer class="footer">
        <span>© {{ date('Y') }} ReproCare — Maternal &amp; Reproductive Health Management System</span>
    </footer>

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- Dedicated logout confirmation (every role posts here via .js-logout-form) -->
    <div class="modal fade logout-modal" id="logoutConfirmModal" tabindex="-1" aria-hidden="true" aria-labelledby="logoutConfirmTitle">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="logout-modal-icon">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <h5 class="logout-modal-title" id="logoutConfirmTitle">Log out of ReproCare?</h5>
                    <p class="logout-modal-text">
                        <strong id="logoutUserName">You</strong> will be signed out on this device and need to log in again to continue.
                    </p>
                    <div class="logout-modal-actions">
                        <button type="button" class="btn btn-logout-stay" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-left"></i> Stay
                        </button>
                        <button type="button" class="btn btn-logout-go" id="logoutConfirmAccept">
                            <i class="bi bi-box-arrow-right"></i> Log Out
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade app-confirm-modal" id="appConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="app-confirm-icon">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                    <p class="mb-0" id="appConfirmMessage">Are you sure you want to continue?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="appConfirmAccept">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        /* ============================================================
           REPROCARE GLOBAL JS — Theme + Animations + Utilities
           ============================================================ */

        const RC_THEME_KEY = 'rc_theme';

        /* -- Theme System -- */
        function setTheme(mode) {
            window.setRcTheme(mode);
            // Sync all toggle switches on page
            document.querySelectorAll('.theme-switch-input').forEach(el => {
                el.checked = (mode === 'dark');
            });
            // Update theme option cards if present
            updateThemeOptionCards(mode);
        }

        function updateThemeOptionCards(mode) {
            document.querySelectorAll('.theme-option').forEach(el => {
                el.classList.toggle('active', el.dataset.theme === mode);
            });
            const checkLight = document.getElementById('check-light');
            const checkDark  = document.getElementById('check-dark');
            if (checkLight) checkLight.style.display = mode === 'light' ? '' : 'none';
            if (checkDark)  checkDark.style.display  = mode === 'dark'  ? '' : 'none';
        }

        /* -- Count-Up Animation -- */
        function animateCountUp(el, target, duration) {
            duration = duration || 1200;
            const start = 0;
            const step  = target / (duration / 16);
            let current = start;
            const timer = setInterval(function() {
                current = Math.min(current + step, target);
                el.textContent = Math.floor(current).toLocaleString();
                if (current >= target) clearInterval(timer);
            }, 16);
        }

        /* -- Adaptive breakpoint: single source of truth (mirrors §28 CSS) -- */
        const RC_DESKTOP_QUERY = '(min-width: 1024px)';
        function rcIsDesktop() {
            return window.matchMedia(RC_DESKTOP_QUERY).matches;
        }

        /* -- Adaptive layout flag: TRUE (≥1024px) → Layout A / desktop,
              FALSE (<1024px) → Layout B / mobile (hamburger + stacked) -- */
        function rcApplyLayout() {
            const layout = rcIsDesktop() ? 'desktop' : 'mobile';
            document.body.dataset.layout = layout;
            document.documentElement.dataset.layout = layout;
            if (!rcIsDesktop()) {
                document.body.classList.remove('sidebar-collapsed');
            } else {
                closeSidebar();
            }
        }

        /* -- Sidebar Toggle -- */
        function toggleSidebar() {
            const sidebar  = document.getElementById('sidebar');
            const overlay  = document.getElementById('sidebarOverlay');
            if (!sidebar) return;
            if (!rcIsDesktop()) {
                sidebar.classList.toggle('show');
                if (overlay) overlay.classList.toggle('show');
                return;
            }

            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem(
                'rc_sidebar_collapsed',
                document.body.classList.contains('sidebar-collapsed') ? '1' : '0'
            );
        }

        function closeSidebar() {
            const sidebar  = document.getElementById('sidebar');
            const overlay  = document.getElementById('sidebarOverlay');
            if (sidebar)  sidebar.classList.remove('show');
            if (overlay)  overlay.classList.remove('show');
        }

        function extractConfirmMessage(attributeValue) {
            if (!attributeValue || !attributeValue.includes('confirm(')) {
                return null;
            }

            const match = attributeValue.match(/confirm\((['"`])([\s\S]*?)\1\)/);
            return match ? match[2] : null;
        }

        function showAppConfirm(message, onConfirm) {
            const modalEl = document.getElementById('appConfirmModal');
            const messageEl = document.getElementById('appConfirmMessage');
            const acceptButton = document.getElementById('appConfirmAccept');

            if (!modalEl || !messageEl || !acceptButton || typeof bootstrap === 'undefined') {
                return window.confirm(message);
            }

            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            messageEl.textContent = message || 'Are you sure you want to continue?';

            const handleConfirm = function() {
                acceptButton.removeEventListener('click', handleConfirm);
                modal.hide();
                onConfirm();
            };

            acceptButton.addEventListener('click', handleConfirm, { once: true });
            modal.show();
            return false;
        }

        function enhanceLegacyConfirms() {
            document.querySelectorAll('form[onsubmit*="confirm("]').forEach(function(form) {
                const message = extractConfirmMessage(form.getAttribute('onsubmit'));
                if (!message || form.dataset.confirmEnhanced === '1') {
                    return;
                }

                form.dataset.confirmEnhanced = '1';
                form.removeAttribute('onsubmit');

                form.addEventListener('submit', function(event) {
                    if (form.dataset.confirmBypass === '1') {
                        form.dataset.confirmBypass = '0';
                        return;
                    }

                    event.preventDefault();
                    showAppConfirm(message, function() {
                        form.dataset.confirmBypass = '1';
                        form.requestSubmit();
                    });
                });
            });

            document.querySelectorAll('button[onclick*="confirm("], a[onclick*="confirm("]').forEach(function(trigger) {
                const message = extractConfirmMessage(trigger.getAttribute('onclick'));
                if (!message || trigger.dataset.confirmEnhanced === '1') {
                    return;
                }

                trigger.dataset.confirmEnhanced = '1';
                trigger.removeAttribute('onclick');

                trigger.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    showAppConfirm(message, function() {
                        const form = trigger.closest('form');
                        if (form) {
                            form.dataset.confirmBypass = '1';
                            if (typeof form.requestSubmit === 'function') {
                                form.requestSubmit(trigger);
                            } else {
                                form.submit();
                            }
                            return;
                        }

                        if (trigger.tagName === 'A' && trigger.href) {
                            window.location.href = trigger.href;
                        }
                    });
                });
            });
        }

        /* -- Dedicated logout dialog (all roles) --
           Every logout <form class="js-logout-form"> is intercepted and routed
           here instead of the generic confirm / native alert. The pending form
           is submitted only when "Log Out" is tapped. */
        let pendingLogoutForm = null;

        function showLogoutDialog(form) {
            pendingLogoutForm = form;
            const nameEl = document.getElementById('logoutUserName');
            const rawName = form && form.dataset ? (form.dataset.userName || '') : '';
            if (nameEl) nameEl.textContent = rawName.trim() !== '' ? rawName.trim() : 'You';

            const modalEl = document.getElementById('logoutConfirmModal');
            if (!modalEl || typeof bootstrap === 'undefined') {
                if (window.confirm('Are you sure you want to log out?')) {
                    pendingLogoutForm = null;
                    form.submit();
                } else {
                    pendingLogoutForm = null;
                }
                return;
            }
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        function wireLogoutForms() {
            const acceptBtn = document.getElementById('logoutConfirmAccept');
            if (acceptBtn && !acceptBtn.dataset.wired) {
                acceptBtn.dataset.wired = '1';
                acceptBtn.addEventListener('click', function () {
                    const form = pendingLogoutForm;
                    pendingLogoutForm = null;
                    const modalEl = document.getElementById('logoutConfirmModal');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    }
                    if (form) {
                        form.dataset.logoutBypass = '1';
                        if (typeof form.requestSubmit === 'function') form.requestSubmit();
                        else form.submit();
                    }
                });
            }

            document.querySelectorAll('form.js-logout-form').forEach(function (form) {
                if (form.dataset.logoutWired === '1') return;
                form.dataset.logoutWired = '1';
                form.addEventListener('submit', function (event) {
                    if (form.dataset.logoutBypass === '1') {
                        form.dataset.logoutBypass = '0';
                        return;
                    }
                    event.preventDefault();
                    showLogoutDialog(form);
                });
            });
        }

        /* -- Init on DOM ready -- */
        document.addEventListener('DOMContentLoaded', function() {

            /* Apply saved theme */
            setTheme(window.currentRcTheme());

            /* Adaptive enforcement: stamp Layout A/B, then restore collapse on desktop only */
            rcApplyLayout();
            if (typeof window.matchMedia === 'function') {
                const rcMedia = window.matchMedia(RC_DESKTOP_QUERY);
                if (typeof rcMedia.addEventListener === 'function') {
                    rcMedia.addEventListener('change', rcApplyLayout);
                } else if (typeof rcMedia.addListener === 'function') {
                    rcMedia.addListener(rcApplyLayout);
                }
            }

            const sidebarCollapsed = localStorage.getItem('rc_sidebar_collapsed') === '1';
            if (rcIsDesktop() && sidebarCollapsed) {
                document.body.classList.add('sidebar-collapsed');
            }
            syncCollapseArrow();

            /* Count-up for all [data-count] elements */
            document.querySelectorAll('[data-count]').forEach(function(el) {
                const target = parseInt(el.getAttribute('data-count'), 10);
                if (!isNaN(target)) animateCountUp(el, target);
            });

            /* Fade-in via IntersectionObserver */
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(e) {
                    if (e.isIntersecting) {
                        e.target.classList.add('visible');
                        observer.unobserve(e.target);
                    }
                });
            }, { threshold: 0.08 });

            document.querySelectorAll('.fade-in-card').forEach(function(el) {
                observer.observe(el);
            });

            /* Sync theme toggle checkboxes */
            document.querySelectorAll('.theme-switch-input').forEach(function(el) {
                el.addEventListener('change', function() {
                    setTheme(this.checked ? 'dark' : 'light');
                });
            });

            /* Theme option cards click */
            document.querySelectorAll('.theme-option').forEach(function(el) {
                el.addEventListener('click', function() {
                    setTheme(this.dataset.theme);
                });
            });

            /* Sidebar toggler — Floating Overlap Badge with rotating chevron */
            function syncCollapseArrow() {
                const icon = document.getElementById('sidebarCollapseIcon');
                const btn = document.getElementById('sidebarCollapseBtn');
                if (!btn) return;
                const collapsed = document.body.classList.contains('sidebar-collapsed');
                btn.classList.toggle('is-collapsed', collapsed);
                if (icon) {
                    // SVG rotates via CSS (.is-collapsed svg { transform: rotate(180deg) })
                    icon.style.transform = collapsed ? 'rotate(180deg)' : 'rotate(0deg)';
                }
                btn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
                btn.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            }
            const toggler = document.getElementById('sidebarToggleBtn');
            if (toggler) toggler.addEventListener('click', function() { toggleSidebar(); syncCollapseArrow(); });
            const collapseBtn = document.getElementById('sidebarCollapseBtn');
            if (collapseBtn) collapseBtn.addEventListener('click', function() { toggleSidebar(); syncCollapseArrow(); });

            enhanceLegacyConfirms();
            wireLogoutForms();

            window.addEventListener('resize', function() {
                rcApplyLayout();
                if (!rcIsDesktop()) {
                    closeSidebar();
                }
            });

            /* ── Sidebar scroll position persistence ── */
            (function() {
                const SCROLL_KEY = 'rc_sidebar_scroll';
                const sidebar = document.querySelector('#sidebar .sidebar-inner') || document.getElementById('sidebar');
                if (!sidebar) return;

                /* Restore scroll position immediately */
                const savedScroll = sessionStorage.getItem(SCROLL_KEY);
                if (savedScroll !== null) {
                    sidebar.scrollTop = parseInt(savedScroll, 10);
                }

                /* Save scroll position on every nav-link click */
                (document.getElementById('sidebar') || sidebar).querySelectorAll('a.nav-link').forEach(function(link) {
                    link.addEventListener('click', function() {
                        sessionStorage.setItem(SCROLL_KEY, sidebar.scrollTop);
                    });
                });

                /* Also save on sidebar scroll (for passive restore) */
                sidebar.addEventListener('scroll', function() {
                    sessionStorage.setItem(SCROLL_KEY, sidebar.scrollTop);
                }, { passive: true });
            })();
        });
    </script>

    @stack('scripts')

    <!-- PWA: offline-first field support (service worker + IndexedDB outbox) -->
    <script src="{{ asset('js/pwa-outbox.js') }}?v=3" defer></script>
    <!-- Field resilience: localStorage draft auto-save for long clinical forms (419-proof) -->
    <script src="{{ asset('js/form-drafts.js') }}?v=2" defer></script>
    <script>
        if ('serviceWorker' in navigator && window.location.protocol.indexOf('http') === 0) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js').catch(function () {});
            });
        }
    </script>
</body>
</html>
