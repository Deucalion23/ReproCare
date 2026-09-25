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

    /* Outer Shell - Full width, clean background, no administrative sidebar gap */
    body { background-color:var(--color-surface) !important; }
    .women-shell {
        background-color:var(--color-surface);
        min-height:calc(100vh - 64px);
        width:100%;
        padding-bottom:3.5rem;
    }

    .women-content-wrap {
        max-width:1280px;
        margin:0 auto;
        padding:2rem 1.5rem;
    }

    /* Borderless card surfaces across the patient portal */
    .women-content-wrap .card { border:none !important; }

    @media (max-width: 768px) {
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

    .women-dock-items {
        display:flex;
        align-items:center;
        justify-content:space-around;
        list-style:none;
        margin:0;
        padding:0;
    }

    .women-dock-link {
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        gap:3px;
        padding:6px 12px;
        border-radius:14px;
        text-decoration:none;
        color:var(--wp-text-soft);
        font-size:0.72rem;
        font-weight:600;
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

    .women-dock-link.active {
        color:var(--wp-primary);
        font-weight:800;
        background:var(--wp-lavender-subtle);
    }

    .women-dock-link.active i {
        color:var(--wp-primary);
        transform:scale(1.12);
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

    /* Buttons — Pill Style (OneBank / Ai Aether) */
    .btn-primary {
        background:linear-gradient(135deg, var(--wp-primary) 0%, var(--wp-primary-dk) 100%) !important;
        border-color:var(--wp-primary) !important;
        color:var(--color-on-solid) !important;
        border-radius:9999px !important;
        font-weight:700 !important;
        padding:0.55rem 1.35rem !important;
        box-shadow:0 8px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 18%, transparent) !important;
        transition:all 0.2s ease !important;
    }

    .btn-primary:hover {
        background:linear-gradient(135deg, var(--wp-primary-dk) 0%, var(--color-secondary-text) 100%) !important;
        transform:translateY(-1px) !important;
        box-shadow:0 10px 24px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 24%, transparent) !important;
        color:var(--color-on-solid) !important;
    }

    .btn-outline-primary {
        color:var(--wp-primary) !important;
        border:1.5px solid var(--color-secondary-soft) !important;
        background:var(--color-surface) !important;
        border-radius:9999px !important;
        font-weight:700 !important;
        padding:0.52rem 1.3rem !important;
        transition:all 0.2s ease !important;
    }

    .btn-outline-primary:hover {
        background:var(--wp-rose-light) !important;
        color:var(--wp-primary-dk) !important;
        border-color:var(--wp-primary) !important;
        transform:translateY(-1px) !important;
    }

    .btn-black-pill, .btn-dark {
        background:var(--color-surface-strong) !important;
        color:var(--color-on-solid) !important;
        border:1px solid var(--color-text) !important;
        border-radius:9999px !important;
        font-weight:700 !important;
        padding:0.55rem 1.35rem !important;
        transition:all 0.2s ease !important;
    }

    .btn-black-pill:hover, .btn-dark:hover {
        background:var(--color-surface-strong) !important;
        color:var(--color-on-solid) !important;
        transform:translateY(-1px) !important;
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
            <a href="{{ route('user.notifications') }}"
               class="women-dock-link {{ request()->routeIs('user.notifications*') ? 'active' : '' }}">
                <i class="bi bi-bell-fill"></i>
                <span>Alerts</span>
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
