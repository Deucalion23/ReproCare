@extends('user.layout')

@section('title', 'My Maternal Health Dashboard - ReproCare')

@push('styles')
<style>
    /* ================================================================
       WOMEN / PATIENT MODERN DASHBOARD STYLES
       ================================================================ */

    .patient-hero {
        position:relative;
        overflow:hidden;
        border-radius:24px;
        padding:2.25rem 2.5rem;
        margin-bottom:2rem;
        background:var(--color-surface);
        border:none;
        box-shadow:0 18px 42px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 12%, transparent), 0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent);
        color:var(--color-secondary-text);
    }

    .patient-hero-orb-1 {
        position:absolute;
        top:-40px;
        right:-30px;
        width:260px;
        height:260px;
        border-radius:50%;
        background:radial-gradient(circle, color-mix(in srgb, var(--color-surface) 45%, transparent) 0%, transparent 70%);
        pointer-events:none;
    }

    .patient-hero-orb-2 {
        position:absolute;
        bottom:-50px;
        left:20%;
        width:180px;
        height:180px;
        border-radius:50%;
        background:radial-gradient(circle, color-mix(in srgb, var(--color-secondary-soft) 55%, transparent) 0%, transparent 70%);
        pointer-events:none;
    }

    .patient-hero-content {
        position:relative;
        z-index:2;
    }

    .patient-hero-title {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:2.1rem;
        font-weight:800;
        letter-spacing:-0.03em;
        line-height:1.2;
        margin-bottom:0.4rem;
        color:var(--color-text);
        text-shadow:0 1px 0 color-mix(in srgb, rgb(var(--color-shadow-rgb)) 55%, transparent);
    }

    .patient-hero-subtitle {
        font-size:0.95rem;
        color:var(--color-secondary-text);
        margin-bottom:1.5rem;
        display:flex;
        align-items:center;
        flex-wrap:wrap;
        gap:8px;
    }

    .btn-hero-action {
        display:inline-flex;
        align-items:center;
        gap:8px;
        background:var(--color-secondary-soft);
        color:var(--color-secondary-text);
        border:none;
        padding:0.65rem 1.45rem;
        border-radius:9999px;
        font-size:0.9rem;
        font-weight:700;
        text-decoration:none;
        box-shadow:0 8px 22px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent);
        transition:all 0.2s ease;
    }

    .btn-hero-action:hover {
        background:var(--color-secondary-soft);
        color:var(--color-secondary-text);
        transform:translateY(-2px);
        box-shadow:0 12px 28px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 45%, transparent);
    }

    .patient-chips-row {
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:1rem;
    }

    .patient-summary-chip {
        display:inline-flex;
        align-items:center;
        gap:7px;
        background:var(--color-surface-soft);
        backdrop-filter:blur(8px);
        -webkit-backdrop-filter:blur(8px);
        border:1px solid color-mix(in srgb, var(--color-border) 70%, transparent);
        padding:0.4rem 0.9rem;
        border-radius:9999px;
        font-size:0.82rem;
        font-weight:600;
        color:var(--color-secondary-text);
    }

    /* ── STAT CARDS ── */
    .women-stat-card {
        background:var(--color-surface);
        border:none;
        border-radius:20px;
        padding:1.4rem;
        box-shadow:var(--wp-shadow-sm);
        height:100%;
        display:flex;
        flex-direction:column;
        justify-content:space-between;
        position:relative;
        overflow:hidden;
        transition:all 0.2s ease;
    }

    .women-stat-card:hover {
        transform:translateY(-3px);
        box-shadow:var(--wp-shadow-md);
    }

    .stat-top-row {
        display:flex;
        align-items:center;
        justify-content:space-between;
        margin-bottom:0.35rem;
    }

    .stat-dark-icon {
        width:40px;
        height:40px;
        border-radius:50%;
        background:var(--color-surface-strong);
        color:var(--color-on-solid);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.1rem;
        margin-bottom:0.7rem;
        flex-shrink:0;
    }

    .stat-badge-icon {
        width:44px;
        height:44px;
        border-radius:14px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.25rem;
    }

    .stat-label-text {
        font-size:0.78rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:0.05em;
        color:var(--wp-text-soft);
    }

    .stat-big-value {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:2.2rem;
        font-weight:800;
        color:var(--color-text);
        line-height:1.1;
        letter-spacing:-0.02em;
        margin-bottom:0.35rem;
    }

    .stat-footnote {
        font-size:0.82rem;
        font-weight:600;
        color:var(--wp-text-soft);
        display:flex;
        align-items:center;
        gap:5px;
    }

    /* ── QUICK ACTION PILLS ── */
    .action-pill {
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:0.55rem 1.1rem 0.55rem 0.6rem;
        border-radius:9999px;
        text-decoration:none;
        font-size:0.85rem;
        font-weight:700;
        color:var(--color-text);
        box-shadow:var(--wp-shadow-sm);
        transition:all 0.2s ease;
        white-space:nowrap;
    }
    .action-pill:hover {
        transform:translateY(-2px);
        box-shadow:var(--wp-shadow-md);
        color:var(--color-text);
    }
    .action-pill-icon {
        width:28px;
        height:28px;
        border-radius:50%;
        color:var(--color-on-solid);
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:0.85rem;
        flex-shrink:0;
    }
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1.4rem 1rem;
        background: var(--color-surface);
        border: none;
        border-radius: 18px;
        text-decoration: none;
        color: var(--color-text);
        box-shadow: var(--wp-shadow-sm);
        transition: all 0.22s ease;
        height: 100%;
    }

    .women-action-tile:hover {
        transform:translateY(-3px);
        box-shadow:var(--wp-shadow-md);
        background:var(--color-bg);
        color:var(--color-text);
    }

    .action-tile-icon-wrap {
        width:54px;
        height:54px;
        border-radius:16px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.5rem;
        margin-bottom:0.75rem;
        transition:transform 0.2s ease;
    }

    .women-action-tile:hover .action-tile-icon-wrap {
        transform:scale(1.08);
    }

    .action-tile-name {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:700;
        font-size:0.92rem;
        margin-bottom:0.2rem;
    }

    .action-tile-sub {
        font-size:0.75rem;
        color:var(--wp-text-soft);
    }

    /* ── PREGNANCY JOURNEY CARD ── */
    .pregnancy-journey-card {
        background:var(--color-surface);
        border:none;
        border-radius:22px;
        box-shadow:var(--wp-shadow-sm);
        overflow:hidden;
        margin-bottom:1.75rem;
    }

    .pregnancy-stat-pill {
        background:var(--wp-cream);
        border:none;
        border-radius:16px;
        padding:1.1rem;
        text-align:center;
    }
    .pregnancy-stat-pill.pill-pink { background:color-mix(in srgb, var(--color-surface-strong) 7%, transparent); }
    .pregnancy-stat-pill.pill-peach { background:color-mix(in srgb, var(--color-surface-strong) 7%, transparent); }
    .pregnancy-stat-pill.pill-mint { background:color-mix(in srgb, var(--color-surface-strong) 7%, transparent); }

    .pregnancy-stat-val {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:1.55rem;
        font-weight:800;
        color:var(--color-text);
        line-height:1.2;
    }

    .pregnancy-stat-lbl {
        font-size:0.78rem;
        font-weight:600;
        color:var(--wp-text-soft);
        margin-top:0.25rem;
    }

    /* ── TIMELINE ── */
    .maternal-timeline {
        list-style:none;
        padding:0;
        margin:0;
        position:relative;
    }

    .maternal-timeline::before {
        content:'';
        position:absolute;
        top:15px;
        bottom:15px;
        left:20px;
        width:2px;
        background:var(--wp-border);
    }

    .timeline-row {
        position:relative;
        padding-left:50px;
        margin-bottom:1.25rem;
    }

    .timeline-dot-icon {
        position:absolute;
        left:6px;
        top:0;
        width:30px;
        height:30px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:0.85rem;
        border:2px solid var(--color-border);
        box-shadow:var(--wp-shadow-sm);
    }

    /* ── HEALTH STATION CARD ── */
    .health-station-banner {
        background:var(--color-surface);
        border:1px solid var(--color-border);
        border-left:none;
        border-radius:18px;
        padding:1.1rem 1.25rem;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        margin-bottom:2rem;
        box-shadow:0 10px 28px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 7%, transparent), 0 2px 6px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent);
    }

    .hs-icon {
        width:44px;
        height:44px;
        border-radius:12px;
        background:var(--color-surface-soft);
        background-color:var(--color-surface-soft);
        border:1px solid var(--color-border);
        color:var(--color-text);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.25rem;
        flex-shrink:0;
        box-shadow:none;
    }

    .hs-title {
        font-size:0.98rem;
        font-weight:800;
        color:var(--color-text);
        font-family:'Plus Jakarta Sans', sans-serif;
        line-height:1.35;
        display:flex;
        align-items:center;
        flex-wrap:wrap;
        gap:0.5rem;
    }

    .hs-badge {
        display:inline-flex;
        align-items:center;
        gap:5px;
        font-size:0.68rem;
        font-weight:700;
        letter-spacing:0.02em;
        background:var(--color-success-soft);
        color:var(--color-success-text);
        border:1px solid var(--color-success-soft);
        padding:0.18rem 0.6rem;
        border-radius:9999px;
        white-space:nowrap;
    }

    .hs-badge .dot {
        width:7px;
        height:7px;
        border-radius:50%;
        background:var(--color-success);
        box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent);
    }

    .hs-subtitle {
        font-size:0.82rem;
        color:var(--wp-text-soft);
        margin-top:2px;
    }

    .btn-hs-dark {
        display:inline-flex;
        align-items:center;
        gap:7px;
        background:var(--color-surface-strong);
        border:1px solid var(--color-text);
        color:var(--color-on-solid);
        font-weight:700;
        font-size:0.85rem;
        padding:0.62rem 1.25rem;
        border-radius:9999px;
        text-decoration:none;
        white-space:nowrap;
        box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);
        transition:all 0.2s ease;
    }

    .btn-hs-dark:hover {
        background:var(--color-surface-strong);
        border-color:var(--color-text);
        color:var(--color-on-solid);
        transform:translateY(-1px);
        box-shadow:0 12px 26px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 32%, transparent);
    }

    @media (max-width: 768px) {
        .patient-hero {
            padding:1.75rem 1.25rem;
        }
        .patient-hero-title {
            font-size:1.6rem;
        }
        .health-station-banner {
            flex-direction:column;
            align-items:flex-start;
        }
    }
</style>
@endpush

@section('user-content')

@php
    $hour = now()->hour;
    $timeOfDay = $hour < 12 ? 'Morning' : ($hour < 17 ? 'Afternoon' : 'Evening');

    $tips = [
        ['icon' => 'droplet-fill',     'color' => 'var(--color-info-text)', 'bg' => 'var(--color-info-soft)', 'title' => 'Daily Hydration',    'text' => 'Drink at least 8 to 10 glasses of clean water daily to support healthy amniotic fluid and maternal circulation.'],
        ['icon' => 'egg-fried',        'color' => 'var(--color-peach-text)', 'bg' => 'var(--color-peach-soft)', 'title' => 'Nutritious Diet',     'text' => 'Include fresh green leafy vegetables, eggs, iron-rich legumes, and colorful fruits with every meal.'],
        ['icon' => 'moon-stars-fill',   'color' => 'var(--color-purple-text)', 'bg' => 'var(--color-primary-soft)', 'title' => 'Restful Sleep',      'text' => 'Rest on your left side when sleeping or resting to optimize blood and oxygen flow to your growing baby.'],
        ['icon' => 'capsule',           'color' => 'var(--color-secondary-text)', 'bg' => 'var(--color-secondary-soft)', 'title' => 'Prenatal Vitamins',  'text' => 'Remember your daily Folic Acid and Iron supplements as prescribed by your RHU midwife or BHW.'],
        ['icon' => 'person-walking',    'color' => 'var(--color-success-text)', 'bg' => 'var(--color-success-soft)', 'title' => 'Gentle Movement',    'text' => 'Daily 20-minute light walking helps alleviate back discomfort, boosts stamina, and enhances mood.'],
        ['icon' => 'heart-pulse-fill',  'color' => 'var(--color-danger-text)', 'bg' => 'var(--color-danger-soft)', 'title' => 'Listen to Your Body','text' => 'Track fetal kicks after meals and report any unusual swelling or dizziness to your health worker promptly.'],
    ];
    $todayTip = $tips[now()->dayOfWeek % count($tips)];
@endphp

{{-- ═══════════════════════════════════════════════
     HERO MASTHEAD — WARM, EMPOWERING PATIENT WELCOME
   ═══════════════════════════════════════════════ --}}
<div class="patient-hero">
    <div class="patient-hero-orb-1"></div>
    <div class="patient-hero-orb-2"></div>

    <div class="patient-hero-content">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="patient-hero-title">
                    Good {{ $timeOfDay }}, {{ auth()->user()->first_name }}!
                </h1>
                <div class="patient-hero-subtitle">
                    <span><i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}</span>
                    <span>&bull;</span>
                    <span>Barangay {{ auth()->user()->barangay ?? 'San Carlos City' }}</span>
                </div>
            </div>

            @if(!$activePregnancy)
                <a href="{{ route('user.pregnancies.create') }}" class="btn-hero-action">
                    <i class="bi bi-plus-circle-fill" style="color:var(--color-secondary-text);"></i>
                    <span>Start Pregnancy Tracking</span>
                </a>
            @else
                <a href="{{ route('user.pregnancies.index') }}" class="btn-hero-action">
                    <i class="bi bi-heart-pulse-fill" style="color:var(--color-secondary-text);"></i>
                    <span>View Pregnancy Journey</span>
                </a>
            @endif
        </div>

        {{-- Summary Pills Row --}}
        <div class="patient-chips-row">
            @if($activePregnancy)
                <div class="patient-summary-chip">
                    <i class="bi bi-heart-pulse-fill" style="color:var(--color-secondary-text);"></i>
                    <span>Week {{ $activePregnancy->aog_weeks }} &bull; {{ $activePregnancy->aog_weeks <= 12 ? '1st' : ($activePregnancy->aog_weeks <= 27 ? '2nd' : '3rd') }} Trimester</span>
                </div>
            @endif

            @if($nextPeriod)
                <div class="patient-summary-chip">
                    <i class="bi bi-calendar2-heart-fill" style="color:var(--color-peach-text);"></i>
                    <span>Predicted Period in {{ round(abs($nextPeriod->diffInDays(now()))) }} days</span>
                </div>
            @endif

            <div class="patient-summary-chip">
                    <i class="bi bi-calendar-check-fill" style="color:var(--color-success-text);"></i>
                <span>{{ $upcomingCheckups }} Scheduled Checkup(s)</span>
            </div>

            @if($unreadNotifications > 0)
                <div class="patient-summary-chip" style="background:color-mix(in srgb, var(--color-danger) 28%, transparent); border-color:color-mix(in srgb, var(--color-danger) 45%, transparent);">
                    <i class="bi bi-bell-fill" style="color:var(--color-danger-text);"></i>
                    <span>{{ $unreadNotifications }} New Notification(s)</span>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     LOCAL HEALTH STATION & BHW CARE TEAM BANNER
   ═══════════════════════════════════════════════ --}}
<div class="health-station-banner">
    <div class="d-flex align-items-center gap-3">
        <div class="hs-icon">
            <i class="bi bi-hospital"></i>
        </div>
        <div>
            <div class="hs-title">
                Your Health Station: RHU 1 Main Health Center
                <span class="hs-badge"><span class="dot"></span> Available for Consultations</span>
            </div>
            <div class="hs-subtitle">
                San Carlos City Health Network &bull; Assigned Barangay Health Worker
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('user.messages.create') }}" class="btn-hs-dark">
            <i class="bi bi-chat-heart-fill"></i> Message Health Worker
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     4 STAT CARDS — METRICS AT A GLANCE
   ═══════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Pregnancy Card --}}
    <div class="col-6 col-lg-3">
        <div class="women-stat-card" style="background:var(--color-secondary-soft);">
            <div>
                <div class="stat-dark-icon">
                    <i class="bi bi-heart-pulse-fill"></i>
                </div>
                <div class="stat-top-row">
                    <span class="stat-label-text">Pregnancy</span>
                </div>
                <div class="stat-big-value">
                    {{ $activePregnancy ? 'Wk ' . $activePregnancy->aog_weeks : '--' }}
                </div>
            </div>
            <div class="stat-footnote">
                @if($activePregnancy)
                    <i class="bi bi-calendar3" style="color:var(--color-text);"></i> EDD: {{ $activePregnancy->edd->format('M d, Y') }}
                @else
                    <a href="{{ route('user.pregnancies.create') }}" class="text-decoration-none fw-700" style="color:var(--color-text);">+ Track pregnancy</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Next Period / Cycle Card --}}
    <div class="col-6 col-lg-3">
        <div class="women-stat-card" style="background:var(--color-peach-soft);">
            <div>
                <div class="stat-dark-icon">
                    <i class="bi bi-calendar2-heart-fill"></i>
                </div>
                <div class="stat-top-row">
                    <span class="stat-label-text">Next Period</span>
                </div>
                <div class="stat-big-value">
                    {{ $nextPeriod ? round(abs($nextPeriod->diffInDays(now()))) . 'd' : '--' }}
                </div>
            </div>
            <div class="stat-footnote">
                @if($nextPeriod)
                    <i class="bi bi-clock-history" style="color:var(--color-text);"></i> Est. {{ $nextPeriod->format('M d') }}
                @else
                    <a href="{{ route('user.menstruation.create') }}" class="text-decoration-none fw-700" style="color:var(--color-text);">+ Log period</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Upcoming Checkups Card --}}
    <div class="col-6 col-lg-3">
        <div class="women-stat-card" style="background:var(--color-success-soft);">
            <div>
                <div class="stat-dark-icon">
                    <i class="bi bi-clipboard2-pulse-fill"></i>
                </div>
                <div class="stat-top-row">
                    <span class="stat-label-text">Checkups</span>
                </div>
                <div class="stat-big-value" data-count="{{ $upcomingCheckups }}">
                    {{ $upcomingCheckups }}
                </div>
            </div>
            <div class="stat-footnote">
                <i class="bi bi-check2-circle" style="color:var(--color-text);"></i>
                <span>{{ $upcomingCheckups > 0 ? 'Visit scheduled' : 'All up to date' }}</span>
            </div>
        </div>
    </div>

    {{-- Care Messages Card --}}
    <div class="col-6 col-lg-3">
        @php
            $unreadMessages = \App\Models\Message::where('receiver_id', auth()->id())
                ->where('is_read', false)
                ->count();
        @endphp
        <div class="women-stat-card" style="background:var(--color-primary-soft);">
            <div>
                <div class="stat-dark-icon">
                    <i class="bi bi-chat-heart-fill"></i>
                </div>
                <div class="stat-top-row">
                    <span class="stat-label-text">Messages</span>
                </div>
                <div class="stat-big-value" data-count="{{ $unreadMessages }}">
                    {{ $unreadMessages }}
                </div>
            </div>
            <div class="stat-footnote">
                <a href="{{ route('user.messages.index') }}" class="text-decoration-none fw-700" style="color:var(--color-text);">
                    {{ $unreadMessages > 0 ? 'Unread care notes' : 'Chat with BHW' }}
                </a>
            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════
     QUICK ACTION PILLS (4 Core Patient Tasks)
    ═══════════════════════════════════════════════ --}}
<div class="mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-800 text-dark mb-0" style="font-family:'Plus Jakarta Sans', sans-serif;">
            Quick Health Actions
        </h5>
        <span class="text-muted" style="font-size:0.82rem;">1-tap essential maternal tools</span>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('user.menstruation.calendar') }}" class="action-pill" style="background:var(--color-surface);">
            <span class="action-pill-icon" style="background:var(--color-secondary);"><i class="bi bi-calendar-heart-fill"></i></span>
            <span>Cycle Calendar</span>
        </a>
        <a href="{{ route('user.pregnancies.index') }}" class="action-pill" style="background:var(--color-surface);">
            <span class="action-pill-icon" style="background:var(--color-peach);"><i class="bi bi-heart-pulse-fill"></i></span>
            <span>Pregnancy Record</span>
        </a>
        <a href="{{ route('user.checkups') }}" class="action-pill" style="background:var(--color-surface);">
            <span class="action-pill-icon" style="background:var(--color-success-text);"><i class="bi bi-clipboard2-check-fill"></i></span>
            <span>Prenatal Checkups</span>
        </a>
        <a href="{{ route('learning.index') }}" class="action-pill" style="background:var(--color-surface);">
            <span class="action-pill-icon" style="background:var(--color-info);"><i class="bi bi-mortarboard-fill"></i></span>
            <span>Mother Guides</span>
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     MAIN SPLIT COLUMNS: Pregnancy Card & Activity
   ═══════════════════════════════════════════════ --}}
<div class="row g-4">

    {{-- Left Column (8 cols) --}}
    <div class="col-lg-8">

        {{-- Active Pregnancy Journey Widget --}}
        @if($activePregnancy)
        <div class="pregnancy-journey-card">
            <div class="p-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge" style="background:var(--color-surface-soft); color:var(--color-text); font-weight:700;">Active Pregnancy</span>
                        <span class="text-muted" style="font-size:0.8rem;">Recorded by RHU 1 Clinician</span>
                    </div>
                    <h5 class="fw-800 text-dark mb-0" style="font-family:'Plus Jakarta Sans', sans-serif;">
                        Your Baby's Journey &bull; Week {{ $activePregnancy->aog_weeks }}
                    </h5>
                </div>
                <a href="{{ route('user.pregnancies.index') }}" class="btn btn-sm btn-outline-dark">
                    Full Pregnancy Details <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="pregnancy-stat-pill pill-pink">
                            <div class="pregnancy-stat-val" style="color:var(--color-text);">
                                {{ $activePregnancy->formatted_aog }}
                            </div>
                            <div class="pregnancy-stat-lbl">Age of Gestation</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="pregnancy-stat-pill pill-peach">
                            <div class="pregnancy-stat-val" style="color:var(--color-text);">
                                {{ $activePregnancy->edd->format('M d, Y') }}
                            </div>
                            <div class="pregnancy-stat-lbl">Estimated Due Date</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="pregnancy-stat-pill pill-mint">
                            <div class="pregnancy-stat-val" style="color:var(--color-text);">
                                G{{ $activePregnancy->gravida }} / P{{ $activePregnancy->para }}
                            </div>
                            <div class="pregnancy-stat-lbl">Gravida / Para Count</div>
                        </div>
                    </div>
                </div>

                {{-- Trimester Progress Bar --}}
                @php
                    $weeks    = $activePregnancy->aog_weeks;
                    $pct      = min(round(($weeks / 40) * 100), 100);
                    $trimester = $weeks <= 12 ? '1st Trimester (Weeks 1-12)' : ($weeks <= 27 ? '2nd Trimester (Weeks 13-27)' : '3rd Trimester (Weeks 28-40)');
                    $trimColor = 'var(--color-text)';
                @endphp
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-700 text-dark" style="font-size:0.88rem;">{{ $trimester }}</span>
                        <span class="fw-800" style="color:var(--color-text); font-size:0.9rem;">Week {{ $weeks }} of 40 ({{ $pct }}%)</span>
                    </div>
                    <div style="height:12px; background:var(--color-surface-soft); border-radius:9999px; overflow:hidden;">
                        <div style="width:{{ $pct }}%; height:100%; background:linear-gradient(90deg, var(--color-surface-strong) 0%, var(--color-surface-soft) 100%); border-radius:9999px; transition:width 1s ease;"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Recent Maternal Activity & Checkups Timeline --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="fw-800 text-dark mb-0" style="font-family:'Plus Jakarta Sans', sans-serif;">Recent Health Activity
                </h5>
                <a href="{{ route('user.checkups') }}" class="btn btn-sm btn-link text-decoration-none fw-700" style="color:var(--color-secondary-text);">
                    View Checkups &rarr;
                </a>
            </div>
            <div class="card-body">
                @php
                    $recentCheckups = auth()->user()->checkups()->latest()->take(3)->get();
                    $recentRecords  = auth()->user()->cycles()->latest()->take(2)->get();
                @endphp

                @if($recentCheckups->count() > 0 || $recentRecords->count() > 0)
                    <div class="maternal-timeline">
                        @foreach($recentCheckups as $c)
                            <div class="timeline-row">
                                <div class="timeline-dot-icon" style="background:var(--color-secondary-text); color:var(--color-on-solid);">
                                    <i class="bi bi-hospital"></i>
                                </div>
                                <div>
                                    <div class="fw-700 text-dark" style="font-size:0.92rem;">
                                        Prenatal Consultation with {{ $c->midwife->name ?? 'Clinical Midwife' }}
                                    </div>
                                    <div style="font-size:0.82rem; color:var(--wp-text-soft);">
                                        {{ $c->scheduled_date->format('F j, Y') }} &bull;
                                        <span class="badge bg-light text-dark border">{{ ucfirst($c->status) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @foreach($recentRecords as $r)
                            <div class="timeline-row">
                                <div class="timeline-dot-icon" style="background:var(--color-danger-text); color:var(--color-on-solid);">
                                    <i class="bi bi-droplet-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-700 text-dark" style="font-size:0.92rem;">Period Logged</div>
                                    <div style="font-size:0.82rem; color:var(--wp-text-soft);">
                                        {{ $r->period_start_date->format('M d') }} –
                                        {{ $r->period_end_date ? $r->period_end_date->format('M d') : 'Present' }}
                                        ({{ $r->duration }} days cycle)
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <div style="width:56px; height:56px; border-radius:50%; background:var(--wp-cream); border:1px solid var(--wp-border); display:flex; align-items:center; justify-content:center; margin:0 auto 0.75rem; font-size:1.5rem; color:var(--wp-text-soft);">
                            <i class="bi bi-clipboard2-heart"></i>
                        </div>
                        <h6 class="fw-700 text-dark mb-1">No recorded activity yet</h6>
                        <p class="text-muted mb-0" style="font-size:0.85rem;">Your prenatal visits, symptoms, and cycle entries will appear here.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Right Column (4 cols) --}}
    <div class="col-lg-4">

        {{-- Daily Health Tip --}}
        <div class="card mb-4" style="background:var(--color-surface);">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:1.2rem;">💡</span>
                    <h5 class="fw-800 text-dark mb-0" style="font-family:'Plus Jakarta Sans', sans-serif;">
                        Today's Maternal Tip
                    </h5>
                </div>
            </div>
            <div class="card-body text-center py-4">
                <div style="width:64px; height:64px; border-radius:20px; background:{{ $todayTip['bg'] }}; color:{{ $todayTip['color'] }}; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; font-size:1.75rem; box-shadow:0 4px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent);">
                    <i class="bi bi-{{ $todayTip['icon'] }}"></i>
                </div>
                <h6 class="fw-800 text-dark mb-2" style="font-family:'Plus Jakarta Sans', sans-serif;">
                    {{ $todayTip['title'] }}
                </h6>
                <p style="font-size:0.88rem; color:var(--wp-text-soft); line-height:1.6; margin-bottom:0;">
                    {{ $todayTip['text'] }}
                </p>
            </div>
        </div>

        {{-- Community Mothers Forum Preview --}}
        @php
            $recentForumPosts = \App\Models\ForumPost::active()->latest()->take(3)->get();
        @endphp
        @if($recentForumPosts->count() > 0)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center gap-2">
                <h5 class="fw-800 text-dark mb-0" style="font-family:'Plus Jakarta Sans', sans-serif;">Mothers Community
                </h5>
                <a href="{{ route('forum.index') }}" class="btn btn-sm btn-link text-decoration-none fw-700 text-nowrap" style="color:var(--color-secondary-text); font-size:0.82rem; white-space:nowrap; flex-shrink:0;">
                    View All &rarr;
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($recentForumPosts as $post)
                        <div class="list-group-item p-3 border-bottom">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div style="width:26px; height:26px; border-radius:50%; background:var(--color-secondary-soft); color:var(--color-secondary-text); display:flex; align-items:center; justify-content:center; font-size:0.72rem; font-weight:800; flex-shrink:0;">
                                    {{ strtoupper(substr($post->user->name ?? 'M', 0, 1)) }}
                                </div>
                                <span class="fw-700 text-dark" style="font-size:0.84rem;">{{ $post->user->first_name ?? 'Mother' }}</span>
                                <small class="text-muted ms-auto" style="font-size:0.72rem;">{{ $post->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="text-muted mb-0" style="font-size:0.82rem; line-height:1.45;">
                                {{ \Illuminate\Support\Str::limit($post->content, 75) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>

</div>

@endsection
