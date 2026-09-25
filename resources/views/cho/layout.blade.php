@extends('layouts.app')

@section('title', 'CHO Portal - ReproCare')

@include('includes.portal-theme')

@push('styles')
<style>
    /* CHO dark hero - flowing contour lines on black (matches reference art) */
    body .main-content .page-hero {
        background-color:var(--color-surface-strong) !important;
        background-image:linear-gradient(135deg, color-mix(in srgb, var(--color-surface-strong) 55%, transparent) 0%, color-mix(in srgb, var(--color-surface-strong) 15%, transparent) 55%, color-mix(in srgb, var(--color-surface-strong) 45%, transparent) 100%) !important;
        border:1px solid color-mix(in srgb, var(--color-border) 8%, transparent) !important;
        border-radius:18px !important;
        color:var(--color-on-solid) !important;
        box-shadow:0 10px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 50%, transparent) !important;
        position:relative !important;
        overflow:hidden !important;
        isolation:isolate !important;
    }
    body .main-content .page-hero::before {
        display:block !important;
        content:"" !important;
        position:absolute !important;
        inset:0 !important;
        top:auto !important;
        right:auto !important;
        width:100% !important;
        height:100% !important;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='800' height='400' viewBox='0 0 800 400'%3E%3Cg fill='none' stroke='white' stroke-width='1'%3E%3Cpath d='M-20,60 C120,20 220,110 360,70 C500,30 620,90 820,40' stroke-opacity='0.55'/%3E%3Cpath d='M-20,80 C120,40 220,130 360,90 C500,50 620,110 820,60' stroke-opacity='0.35'/%3E%3Cpath d='M-20,100 C120,60 220,150 360,110 C500,70 620,130 820,80' stroke-opacity='0.28'/%3E%3Cpath d='M-20,130 C80,150 140,220 260,200 C380,180 420,260 560,230 C680,205 740,280 820,250' stroke-opacity='0.4'/%3E%3Cpath d='M-20,150 C80,170 140,240 260,220 C380,200 420,280 560,250 C680,225 740,300 820,270' stroke-opacity='0.3'/%3E%3Cpath d='M-20,170 C80,190 140,260 260,240 C380,220 420,300 560,270 C680,245 740,320 820,290' stroke-opacity='0.22'/%3E%3Cpath d='M200,400 C260,300 340,280 380,220 C420,160 500,150 560,100 C620,50 700,60 820,20' stroke-opacity='0.5'/%3E%3Cpath d='M240,400 C300,310 370,295 410,235 C450,175 520,165 580,115 C640,65 710,75 820,35' stroke-opacity='0.32'/%3E%3Cpath d='M280,400 C330,320 395,310 435,250 C475,190 540,180 600,130 C660,80 720,90 820,50' stroke-opacity='0.24'/%3E%3Cpath d='M480,400 C520,340 600,330 630,280 C660,230 720,220 760,180 C790,150 800,120 820,100' stroke-opacity='0.35'/%3E%3Cpath d='M520,400 C555,350 625,340 655,295 C685,250 735,240 770,200 C795,172 805,140 820,120' stroke-opacity='0.25'/%3E%3Cpath d='M-20,240 C100,260 180,340 320,320 C460,300 520,380 680,350 C740,338 780,360 820,350' stroke-opacity='0.28'/%3E%3Cpath d='M-20,260 C100,280 180,360 320,340 C460,320 520,400 680,370' stroke-opacity='0.2'/%3E%3C/g%3E%3C/svg%3E") !important;
        background-size:cover !important;
        background-position:center !important;
        background-repeat:no-repeat !important;
        opacity:0.9 !important;
        pointer-events:none !important;
        z-index:0 !important;
    }
    body .main-content .page-hero::after {
        display:block !important;
        content:"" !important;
        position:absolute !important;
        inset:0 !important;
        bottom:auto !important;
        left:auto !important;
        width:100% !important;
        height:100% !important;
        background:linear-gradient(90deg, color-mix(in srgb, var(--color-surface-strong) 55%, transparent) 0%, color-mix(in srgb, var(--color-surface-strong) 10%, transparent) 45%, color-mix(in srgb, var(--color-surface-strong) 5%, transparent) 100%) !important;
        pointer-events:none !important;
        z-index:0 !important;
    }
    body .main-content .page-hero > * {
        position:relative !important;
        z-index:1 !important;
    }
    body .main-content .page-hero .page-hero-title,
    body .main-content .page-hero h1.page-title {
        color:var(--color-on-solid) !important;
    }
    body .main-content .page-hero .page-hero-subtitle,
    body .main-content .page-hero .page-subtitle,
    body .main-content .page-hero p {
        color:var(--color-border) !important;
    }

    /* CHO sidebar portal label: black */
    .sidebar-portal-label {
        background:none !important;
        color:var(--color-text) !important;
        -webkit-text-fill-color:var(--color-text) !important;
    }

    /* Consistent top-card + table action buttons */
    body .main-content .page-hero .btn {
        font-size:.8rem !important; font-weight:700 !important;
        padding:.55rem 1.2rem !important; border-radius:999px !important;
    }
    body .main-content .card .table .btn {
        font-size:.75rem !important; font-weight:700 !important;
        padding:.35rem .8rem !important; border-radius:8px !important;
    }
</style>
@endpush

@section('content')
<div class="layout-wrapper">
    @include('includes.sidebar')
    <main class="main-content">
        <div class="mw-container">
            @yield('cho-content')
        </div>
    </main>
</div>
@endsection
