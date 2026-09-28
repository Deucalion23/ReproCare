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
        .bhw-quick-card .card-body { padding:0.6rem !important; }
        .bhw-quick-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:0.4rem; }
        @media (min-width: 992px) {
            .bhw-quick-grid { grid-template-columns:repeat(4, minmax(0, 1fr)); }
        }
        .quick-action-tile { font-size:0.6rem !important; padding:0.45rem 0.3rem !important; gap:0.2rem !important; border-radius:12px !important; }
        .quick-action-tile i { font-size:0.75rem !important; }
        .main-content .list-group-item span { font-size:0.72rem !important; }
        .main-content .list-group-item { padding:0.6rem 0.85rem !important; }
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
    <main class="main-content">
        <div class="mw-container">
            @yield('bhw-content')
        </div>
    </main>
</div>
@endsection
