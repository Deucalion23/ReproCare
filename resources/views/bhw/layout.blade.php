@extends('layouts.app')

@section('title', 'BHW Portal - ReproCare')

@include('includes.portal-theme')

@push('styles')
<style>
    /* ── BHW portal mobile: shared foundation lives in portal-theme;
          dashboard stat cards use a compact 2-col grid like the women portal. ── */
    @media (max-width: 600px) {
        .bhw-stat-row { display:grid !important; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:0.65rem; }
        .bhw-stat-row > [class*="col-"] { width:auto !important; max-width:none !important; padding-left:0 !important; padding-right:0 !important; margin-top:0 !important; }
        /* Lower dashboard sections: tiles, lists, states, alerts. */
        .quick-action-tile { font-size:0.76rem !important; padding:0.8rem 0.5rem !important; }
        .quick-action-tile i { font-size:1.1rem !important; }
        .main-content .list-group-item span { font-size:0.78rem !important; }
        .main-content .alert { font-size:0.78rem !important; }
        .main-content .empty-state h6 { font-size:0.85rem !important; }
        .main-content .empty-state p { font-size:0.76rem !important; }
        .main-content .modal-title { font-size:1rem !important; }
    }
</style>
@endpush

@section('content')
<div class="layout-wrapper">
    @include('includes.sidebar')
    <main class="main-content">
        <div class="mw-container">
            @yield('bhw-content')
        </div>
    </main>
</div>
@endsection
