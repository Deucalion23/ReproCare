@extends('layouts.app')

@section('title', 'BHW President Portal - ReproCare')

@include('includes.portal-theme')

@push('styles')
<style>
    /* ── President portal mobile: shared foundation lives in portal-theme;
          dashboard stat rows use a compact 2-col grid like the BHW portal. ── */
    @media (max-width: 600px) {
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
    <main class="main-content">
        <div class="mw-container">
            @yield('bhw-president-content')
        </div>
    </main>
</div>
@endsection
