@extends('layouts.app')

@section('title', 'BHW Portal - ReproCare')

@include('includes.portal-theme')

@push('styles')
<style>
    /* ── BHW portal mobile foundation (women-portal-like compactness) ── */
    @media (max-width: 600px) {
        .main-content { padding:0.85rem 0.85rem 3.5rem !important; }
        .page-hero { padding:1.1rem 1rem !important; border-radius:18px !important; margin-bottom:1rem !important; }
        .page-hero-title, .page-title, h1.page-title { font-size:1.02rem !important; line-height:1.3 !important; }
        .page-hero-subtitle, .page-subtitle { font-size:0.74rem !important; line-height:1.5 !important; }
        .btn-hero-primary { font-size:0.78rem !important; }
        .main-content .stat-card { padding:0.9rem !important; border-radius:16px !important; }
        .main-content .stat-number, .main-content .metric-card-value { font-size:1.45rem !important; }
        .main-content .stat-label { font-size:0.66rem !important; }
        .summary-chip { font-size:0.64rem !important; }
        .main-content .stat-trend { font-size:0.7rem !important; }
        .main-content .card-header h5 { font-size:0.9rem !important; }
        .main-content .table { font-size:0.76rem !important; }
        .main-content .btn-sm { font-size:0.7rem !important; }
        /* Dashboard stat cards: compact 2-col grid like the women portal. */
        .bhw-stat-row { display:grid !important; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:0.65rem; }
        .bhw-stat-row > [class*="col-"] { width:auto !important; max-width:none !important; padding-left:0 !important; padding-right:0 !important; margin-top:0 !important; }
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
