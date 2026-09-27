@extends('layouts.app')

@section('title', 'Women & Maternal Health Portal - ReproCare')

@push('styles')
<style>
    /* ================================================================
       REPROCARE WOMEN & PATIENT PORTAL DESIGN SYSTEM
       Theme: Warm, empowering, human-centered maternal healthcare
       Palette: Deep Slate Teal (#1C3F46), Warm Ivory Cream (#FDFBF8),
                Soft Blush (#EAA89F), Rose (#D48E85), Sage (#6CA57A)
       ================================================================ */

    :root { --wp-radius:24px; }

    /* Match patient primary actions to the lavender used on the public landing page. */
    .women-shell { --women-action:#9B6CB8; --women-action-hover:#8958A8; --women-action-soft:#F1EBFA; }

    /* Outer Shell - white canvas with the soft circular accents from the portal art. */
    body { background-color:var(--color-surface) !important; }
    .women-shell {
        background:var(--color-surface);
        min-height:calc(100vh - 3.75rem);
        width:100%;
        margin-top:3.75rem;
        padding-bottom:3.5rem;
        position:relative;
        isolation:isolate;
        overflow:hidden;
    }

    html:not([data-theme="dark"]) .women-shell {
        background-color:#FFFFFF;
        background-image:
            radial-gradient(circle at 8% 13%, rgb(232 218 247 / .45) 0 52px, transparent 53px),
            radial-gradient(circle at 24% 66%, rgb(242 231 252 / .6) 0 34px, transparent 35px),
            radial-gradient(circle at 78% 19%, rgb(235 220 249 / .48) 0 26px, transparent 27px),
            radial-gradient(circle at 91% 62%, rgb(243 232 252 / .65) 0 48px, transparent 49px),
            radial-gradient(circle at 61% 89%, rgb(236 224 249 / .42) 0 30px, transparent 31px);
        background-repeat:no-repeat;
    }

    .women-shell::before,
    .women-shell::after {
        content:'';
        position:absolute;
        z-index:0;
        pointer-events:none;
        border-radius:50%;
    }

    /* Large, partially cropped lavender ring in the upper-right corner. */
    .women-shell::before {
        width:540px;
        height:540px;
        top:-330px;
        right:-150px;
        border:64px solid rgb(182 150 218 / .17);
    }

    /* A second quiet circular accent keeps long pages from feeling flat. */
    .women-shell::after {
        width:360px;
        height:360px;
        bottom:7rem;
        left:-245px;
        background:rgb(222 207 239 / .25);
        box-shadow:0 0 0 42px rgb(222 207 239 / .12);
    }

    .women-content-wrap {
        max-width:1280px;
        margin:0 auto;
        padding:2rem 1.5rem;
        position:relative;
        z-index:1;
    }

    /* Borderless card surfaces across the patient portal */
    .women-content-wrap .card { border:none !important; }

    @media (max-width: 768px) {
        .women-shell::before {
            width:380px;
            height:380px;
            top:-255px;
            right:-180px;
            border-width:44px;
        }

        .women-shell::after {
            width:260px;
            height:260px;
            left:-195px;
            box-shadow:0 0 0 30px rgb(222 207 239 / .12);
        }

        .women-content-wrap {
            padding:1.25rem 1rem 6rem; /* Extra padding on bottom for mobile dock */
        }
    }

    /* ═══════════════════════════════════════════════
       MOBILE FLOATING BOTTOM NAVIGATION DOCK
       (App-like experience for phones & small tablets)
    ═══════════════════════════════════════════════ */
    .women-bottom-dock {
        display:none;
        position:fixed;
        bottom:0;
        left:0;
        right:0;
        z-index:1040;
        background:color-mix(in srgb, var(--color-surface) 94%, transparent);
        backdrop-filter:blur(20px);
        -webkit-backdrop-filter:blur(20px);
        border-top:1px solid var(--wp-border);
        box-shadow:0 -4px 25px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 9%, transparent);
        padding:0.5rem 0.5rem calc(0.5rem + env(safe-area-inset-bottom));
    }

    :root[data-theme="light"][data-bs-theme] body .women-bottom-dock,
    :root:not([data-theme="dark"]) body .women-bottom-dock {
        background:#F1F3F5 !important;
        background-color:#F1F3F5 !important;
        border-top-color:#D1D5DB !important;
    }

    .women-dock-items {
        display:flex;
        align-items:stretch;
        justify-content:flex-start;
        list-style:none;
        margin:0;
        padding:2px 4px;
        overflow-x:auto;
        overflow-y:hidden;
        -webkit-overflow-scrolling:touch;
        scrollbar-width:none;
        gap:2px;
    }

    .women-dock-items::-webkit-scrollbar { display:none; }

    .women-dock-items > li {
        flex:1 0 auto;
        min-width:62px;
        max-width:96px;
    }

    .women-dock-link {
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        gap:3px;
        width:100%;
        min-height:52px;
        padding:6px 6px;
        border-radius:14px;
        text-decoration:none;
        color:var(--wp-text-soft);
        font-size:0.66rem;
        font-weight:600;
        line-height:1.1;
        text-align:center;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        transition:all 0.18s ease;
        position:relative;
    }

    .women-dock-link i {
        font-size:1.25rem;
        line-height:1;
        transition:transform 0.18s ease;
    }

    .women-dock-link:hover {
        color:var(--wp-primary);
    }

    html:not([data-theme="dark"]) .women-dock-link:hover {
        background:#D1D5DB;
        color:#1F2937;
    }

    /* Match the desktop tab hover colors exactly, including the icon shade. */
    :root[data-theme="light"][data-bs-theme] body .women-bottom-dock .women-dock-link:not(.active):hover,
    :root:not([data-theme="dark"]) body .women-bottom-dock .women-dock-link:not(.active):hover {
        background:#D1D5DB !important;
        color:#1F2937 !important;
    }

    :root[data-theme="light"][data-bs-theme] body .women-bottom-dock .women-dock-link:not(.active):hover i,
    :root:not([data-theme="dark"]) body .women-bottom-dock .women-dock-link:not(.active):hover i {
        color:#334155 !important;
    }

    /* Selected dock tab = same dark pill as desktop, no left bar */
    .women-dock-link.active {
        color:#FFFFFF;
        font-weight:800;
        background:var(--color-surface-strong);
        background-color:var(--color-surface-strong);
        border-color:transparent;
        box-shadow:none;
    }

    .women-dock-link.active i {
        color:#FFFFFF;
        transform:scale(1.12);
    }

    :root[data-theme][data-bs-theme] body .women-bottom-dock .women-dock-link.active.active,
    :root[data-theme="light"][data-bs-theme] body .women-bottom-dock .women-dock-link.active.active,
    :root:not([data-theme="dark"]) body .women-bottom-dock .women-dock-link.active.active {
        background:var(--color-surface-strong) !important;
        background-color:var(--color-surface-strong) !important;
        border-color:transparent !important;
        box-shadow:none !important;
        color:#FFFFFF !important;
    }

    :root[data-theme][data-bs-theme] body .women-bottom-dock .women-dock-link.active.active i,
    :root[data-theme="light"][data-bs-theme] body .women-bottom-dock .women-dock-link.active.active i,
    :root:not([data-theme="dark"]) body .women-bottom-dock .women-dock-link.active.active i {
        color:#FFFFFF !important;
    }

    :root[data-theme][data-bs-theme] body .women-bottom-dock .women-dock-link.active.active:hover,
    :root[data-theme="light"][data-bs-theme] body .women-bottom-dock .women-dock-link.active.active:hover,
    :root:not([data-theme="dark"]) body .women-bottom-dock .women-dock-link.active.active:hover {
        background:var(--color-surface-strong) !important;
        background-color:var(--color-surface-strong) !important;
        border-color:transparent !important;
        box-shadow:none !important;
        color:#FFFFFF !important;
    }

    :root[data-theme][data-bs-theme] body .women-bottom-dock .women-dock-link.active.active:hover i,
    :root:not([data-theme="dark"]) body .women-bottom-dock .women-dock-link.active.active:hover i {
        color:#FFFFFF !important;
    }

    .women-dock-unread {
        position:absolute;
        top:2px;
        right:12px;
        width:8px;
        height:8px;
        border-radius:50%;
        background:var(--wp-primary);
    }

    @media (max-width: 1140px) {
        .women-bottom-dock {
            display:block;
        }
    }

    @media (max-width: 600px) {
        .women-bottom-dock {
            padding:0.4rem 0.35rem calc(0.4rem + env(safe-area-inset-bottom));
        }
        .women-dock-items { gap:0; }
        .women-dock-items > li { min-width:58px; }
        .women-dock-link {
            font-size:0.62rem;
            min-height:50px;
            padding:5px 4px;
            border-radius:12px;
        }
        .women-dock-link i { font-size:1.1rem; }
        .women-content-wrap {
            padding-bottom:6.5rem;
        }
    }

    /* ═══════════════════════════════════════════════
       MODERN CARD ENHANCEMENTS FOR WOMEN PORTAL
    ═══════════════════════════════════════════════ */
    .card {
        border-radius:var(--wp-radius) !important;
        border:1px solid var(--wp-border) !important;
        box-shadow:var(--wp-shadow-sm) !important;
        background:var(--wp-var(--color-surface)) !important;
        overflow:hidden;
        transition:transform 0.22s ease, box-shadow 0.22s ease;
    }

    .card:hover {
        box-shadow:var(--wp-shadow-md) !important;
        transform:translateY(-2px);
    }

    .card-header {
        background:transparent !important;
        border-bottom:1px solid color-mix(in srgb, var(--color-peach-soft) 60%, transparent) !important;
        padding:1.15rem 1.4rem !important;
    }

    .card-body {
        padding:1.4rem !important;
    }

    /* Women portal button system: navy for committed actions, soft lavender for
       navigation and secondary actions.  All variants use the same pill shape. */
    .btn-primary, .btn-success {
        background:#1E293B !important;
        border-color:#1E293B !important;
        color:#FFFFFF !important;
        border-radius:9999px !important;
        font-weight:700 !important;
        padding:0.55rem 1.35rem !important;
        box-shadow:0 8px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 18%, transparent) !important;
        transition:all 0.2s ease !important;
    }

    .btn-primary:hover, .btn-success:hover {
        background:#0F172A !important;
        border-color:#0F172A !important;
        transform:translateY(-1px) !important;
        box-shadow:0 10px 24px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 24%, transparent) !important;
        color:var(--color-on-solid) !important;
    }

    .btn-outline-primary {
        color:#743AFF !important;
        border:1px solid transparent !important;
        background:#F1F5F9 !important;
        border-radius:9999px !important;
        font-weight:700 !important;
        padding:0.52rem 1.3rem !important;
        transition:all 0.2s ease !important;
    }

    .btn-outline-primary:hover {
        background:#E8EEF5 !important;
        color:#6431DE !important;
        border-color:transparent !important;
        transform:translateY(-1px) !important;
    }

    .btn-black-pill, .btn-dark {
        background:#1E293B !important;
        color:#FFFFFF !important;
        border:1px solid #1E293B !important;
        border-radius:9999px !important;
        font-weight:700 !important;
        padding:0.55rem 1.35rem !important;
        transition:all 0.2s ease !important;
    }

    .btn-black-pill:hover, .btn-dark:hover {
        background:#0F172A !important;
        color:#FFFFFF !important;
        transform:translateY(-1px) !important;
    }

    /* High-specificity variants keep theme.css from recoloring the portal. */
    :root[data-theme][data-bs-theme] body .women-shell :is(.btn-primary, .btn-success, .btn-dark, .btn-black-pill),
    body .women-shell :is(.btn-primary, .btn-success, .btn-dark, .btn-black-pill) {
        background:#1E293B !important;
        background-color:#1E293B !important;
        border-color:#1E293B !important;
        color:#FFFFFF !important;
    }
    :root[data-theme][data-bs-theme] body .women-shell :is(.btn-primary, .btn-success, .btn-dark, .btn-black-pill) :is(span, i, svg),
    body .women-shell :is(.btn-primary, .btn-success, .btn-dark, .btn-black-pill) :is(span, i, svg) {
        color:#FFFFFF !important;
    }
    :root[data-theme][data-bs-theme] body .women-shell :is(.btn-primary, .btn-success, .btn-dark, .btn-black-pill):is(:hover, :active, :focus-visible),
    body .women-shell :is(.btn-primary, .btn-success, .btn-dark, .btn-black-pill):is(:hover, :active, :focus-visible) {
        background:#0F172A !important;
        background-color:#0F172A !important;
        border-color:#0F172A !important;
        color:#FFFFFF !important;
    }
    :root[data-theme][data-bs-theme] body .women-shell :is(.btn-primary, .btn-success, .btn-dark, .btn-black-pill):is(:hover, :active, :focus-visible) :is(span, i, svg),
    body .women-shell :is(.btn-primary, .btn-success, .btn-dark, .btn-black-pill):is(:hover, :active, :focus-visible) :is(span, i, svg) {
        color:#FFFFFF !important;
    }

    :root[data-theme][data-bs-theme] body .women-shell :is(.btn-outline-primary, .btn-outline-secondary, .btn-secondary, .btn-light, .btn-outline-dark),
    body .women-shell :is(.btn-outline-primary, .btn-outline-secondary, .btn-secondary, .btn-light, .btn-outline-dark) {
        background:#F1F5F9 !important;
        background-color:#F1F5F9 !important;
        border-color:transparent !important;
        color:#475569 !important;
        border-radius:9999px !important;
        font-weight:700 !important;
        box-shadow:0 8px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 12%, transparent) !important;
    }
    :root[data-theme][data-bs-theme] body .women-shell .btn-outline-primary,
    body .women-shell .btn-outline-primary {
        color:#743AFF !important;
    }
    :root[data-theme][data-bs-theme] body .women-shell :is(.btn-outline-primary, .btn-outline-secondary, .btn-secondary, .btn-light, .btn-outline-dark):is(:hover, :active, :focus-visible),
    body .women-shell :is(.btn-outline-primary, .btn-outline-secondary, .btn-secondary, .btn-light, .btn-outline-dark):is(:hover, :active, :focus-visible) {
        background:#E8EEF5 !important;
        background-color:#E8EEF5 !important;
        border-color:transparent !important;
        color:#6431DE !important;
        transform:translateY(-1px);
    }
    :root[data-theme][data-bs-theme] body .women-shell :is(.btn-outline-primary, .btn-outline-secondary, .btn-secondary, .btn-light, .btn-outline-dark) :is(span, i, svg),
    body .women-shell :is(.btn-outline-primary, .btn-outline-secondary, .btn-secondary, .btn-light, .btn-outline-dark) :is(span, i, svg) {
        color:currentColor !important;
    }

    /* Care Emergency Modal */
    .emergency-hotline-card {
        border:1px solid var(--wp-border);
        border-radius:20px;
        padding:1rem 1.25rem;
        display:flex;
        align-items:center;
        justify-content:space-between;
        margin-bottom:0.75rem;
        background:var(--color-surface);
        transition:all 0.2s ease;
    }

    .emergency-hotline-card:hover {
        border-color:var(--wp-primary);
        box-shadow:0 8px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent);
        transform:translateY(-1px);
    }

    .emergency-hotline-num {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:800;
        font-size:1.1rem;
        color:var(--wp-primary);
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        gap:6px;
    }
</style>
@endpush

@section('content')
<main class="women-shell">
    <div class="women-content-wrap">
        @yield('user-content')
    </div>
</main>

{{-- ═══════════════════════════════════════════════
     MOBILE FLOATING BOTTOM DOCK
   ═══════════════════════════════════════════════ --}}
@php
    $unreadMessages = \App\Models\Message::where('receiver_id', auth()->id())
        ->where('is_read', false)
        ->count();
@endphp
<div class="women-bottom-dock">
    <ul class="women-dock-items">
        <li>
            <a href="{{ route('user.dashboard') }}"
               class="women-dock-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i>
                <span>Home</span>
            </a>
        </li>
        <li>
            <a href="{{ route('user.pregnancies.index') }}"
               class="women-dock-link {{ request()->routeIs('user.pregnancies.*') ? 'active' : '' }}">
                <i class="bi bi-heart-pulse-fill"></i>
                <span>Pregnancy</span>
            </a>
        </li>
        <li>
            <a href="{{ route('user.menstruation.index') }}"
               class="women-dock-link {{ request()->routeIs('user.menstruation.*') ? 'active' : '' }}">
                <i class="bi bi-calendar2-heart-fill"></i>
                <span>Cycle</span>
            </a>
        </li>
        <li>
            <a href="{{ route('user.checkups') }}"
               class="women-dock-link {{ request()->routeIs('user.checkups*') ? 'active' : '' }}">
                <i class="bi bi-clipboard2-pulse-fill"></i>
                <span>Checkups</span>
            </a>
        </li>
        <li>
            <a href="{{ route('user.messages.index') }}"
               class="women-dock-link {{ request()->routeIs('user.messages.*') ? 'active' : '' }}">
                <i class="bi bi-chat-heart-fill"></i>
                <span>Care Chat</span>
                @if($unreadMessages > 0)
                    <span class="women-dock-unread"></span>
                @endif
            </a>
        </li>
        <li>
            <a href="{{ route('user.health-records') }}"
               class="women-dock-link {{ request()->routeIs('user.health-records*') ? 'active' : '' }}">
                <i class="bi bi-clipboard2-data-fill"></i>
                <span>Records</span>
            </a>
        </li>
        <li>
            <a href="{{ route('learning.index') }}"
               class="women-dock-link {{ request()->routeIs('learning.*') ? 'active' : '' }}">
                <i class="bi bi-mortarboard-fill"></i>
                <span>Learning</span>
            </a>
        </li>
    </ul>
</div>

{{-- ═══════════════════════════════════════════════
     CARE SUPPORT & HEALTH CENTER EMERGENCY MODAL
   ═══════════════════════════════════════════════ --}}
<div class="modal fade" id="careEmergencyModal" tabindex="-1" aria-labelledby="careEmergencyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px; border:1px solid var(--wp-border); overflow:hidden;">
            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg, var(--color-danger-soft) 0%, var(--color-danger-soft) 100%); padding:1.5rem 1.5rem 0.5rem;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px; height:44px; border-radius:14px; background:var(--color-danger-text); color:var(--color-on-solid); display:flex; align-items:center; justify-content:center; font-size:1.25rem;">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 text-dark mb-0" id="careEmergencyModalLabel">Health Center &amp; Emergency Support</h5>
                        <small class="text-muted" style="font-size:0.78rem;">Direct contacts for San Carlos City maternal health care</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" style="background:var(--wp-cream);">
                <p style="font-size:0.88rem; color:var(--wp-text-soft); margin-bottom:1.25rem;">
                    If you are experiencing severe cramping, unusual bleeding, blurred vision, or need urgent care, please reach out to the contacts below immediately:
                </p>

                <div class="emergency-hotline-card">
                    <div>
                        <div class="fw-700 text-dark" style="font-size:0.9rem;">National Emergency Hotline</div>
                        <small class="text-muted">24/7 Philippines Emergency Response</small>
                    </div>
                    <a href="tel:911" class="emergency-hotline-num" style="color:var(--color-danger-text); font-size:1.3rem;">
                        <i class="bi bi-shield-fill-plus text-danger"></i> 911
                    </a>
                </div>

                <div class="mt-3 p-3 rounded-3" style="background:color-mix(in srgb, var(--color-surface-soft) 5%, transparent); border:1px solid var(--wp-border);">
                    <div class="d-flex align-items-center gap-2 mb-1 fw-700" style="color:var(--wp-teal); font-size:0.85rem;">
                        <i class="bi bi-info-circle-fill"></i> Routine Consultations
                    </div>
                    <div style="font-size:0.8rem; color:var(--wp-text-soft);">
                        For non-urgent inquiries, prenatal scheduling, or vitamin refills, send a message to your assigned Barangay Health Worker in <a href="{{ route('user.messages.index') }}" class="fw-700 text-decoration-none" style="color:var(--wp-teal);">Messages</a>.
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 px-4 pb-4" style="background:var(--wp-cream);">
                <button type="button" class="btn btn-outline-secondary w-100 py-2.5 rounded-3 fw-600" data-bs-dismiss="modal">Close Window</button>
            </div>
        </div>
    </div>
</div>
@endsection
