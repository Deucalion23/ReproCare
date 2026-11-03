@extends('layouts.app')

@section('title', 'BHW President Portal - ReproCare')

@include('includes.portal-theme')

@push('styles')
<style>
    /* Use the available workspace and keep page chrome compact across the portal. */
    body .main-content.bhw-president-main {
        padding:1.15rem 1.35rem 2.5rem !important;
    }
    body .main-content.bhw-president-main > .mw-container {
        width:100%;
        max-width:none !important;
        margin-left:0;
        margin-right:0;
    }
    body .main-content.bhw-president-main .page-hero {
        padding:1.25rem 1.5rem !important;
        margin-bottom:1rem !important;
        border-radius:16px !important;
    }
    body .main-content.bhw-president-main .page-hero-title {
        font-size:1.35rem !important;
        margin-bottom:0.2rem;
    }
    body .main-content.bhw-president-main .page-hero-subtitle {
        font-size:0.8rem !important;
    }
    body .main-content.bhw-president-main > .mw-container > .card:not(.wd-wrap) {
        border-radius:18px !important;
    }
    body .main-content.bhw-president-main > .mw-container > .card:not(.wd-wrap) > .card-header {
        padding:0.8rem 1rem !important;
    }
    body .main-content.bhw-president-main > .mw-container > .card:not(.wd-wrap) > .card-body:not(.p-0) {
        padding:1rem !important;
    }

    /* ── President portal mobile: shared foundation lives in portal-theme;
          dashboard stat rows use a compact 2-col grid like the BHW portal. ── */
    @media (max-width: 600px) {
        body .main-content.bhw-president-main { padding:0.85rem 0.75rem 2rem !important; }
        body .main-content.bhw-president-main .page-hero { padding:1rem !important; margin-bottom:0.75rem !important; }
        body .main-content.bhw-president-main > .mw-container > .card:not(.wd-wrap) > .card-header { padding:0.7rem 0.8rem !important; }
        body .main-content.bhw-president-main > .mw-container > .card:not(.wd-wrap) > .card-body:not(.p-0) { padding:0.8rem !important; }
        .pres-stat-row { display:grid !important; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:0.65rem; }
        .pres-stat-row > [class*="col-"] { width:auto !important; max-width:none !important; padding-left:0 !important; padding-right:0 !important; margin-top:0 !important; }
        .main-content .list-group-item span { font-size:0.72rem !important; }
        .main-content .alert { font-size:0.72rem !important; }
        .main-content .empty-state h6 { font-size:0.8rem !important; }
        .main-content .empty-state p { font-size:0.7rem !important; }
        .main-content .modal-title { font-size:0.9rem !important; }
    }
</style>
@endpush

@section('content')
<div class="layout-wrapper">
    @include('includes.sidebar')
    <main class="main-content bhw-president-main">
        <div class="mw-container">
            @yield('bhw-president-content')
        </div>
    </main>
</div>
@endsection
