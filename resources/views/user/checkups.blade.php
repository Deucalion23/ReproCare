@extends('user.layout')

@section('title', 'My Checkups - ReproCare')

@push('styles')
<style>
    .checkups-page .page-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.6rem; font-weight:800; color:var(--color-text); letter-spacing:-0.02em; margin:0; }
    .checkups-count { background:var(--color-primary-soft); background-color:var(--color-primary-soft); color:var(--color-primary-text); border:none; font-size:0.78rem; font-weight:800; padding:0.4em 1em; border-radius:999px; }
    .checkups-chip { display:inline-flex; align-items:center; gap:6px; border:none; border-radius:999px; font-size:0.78rem; font-weight:800; padding:0.42rem 0.95rem; }
    .checkups-chip-sched { background:var(--color-peach-soft); background-color:var(--color-peach-soft); color:var(--color-warning-text); }
    .checkups-chip-done { background:var(--color-success-soft); background-color:var(--color-success-soft); color:var(--color-success-text); }
    .checkups-chip-miss { background:var(--color-danger-soft); background-color:var(--color-danger-soft); color:var(--color-danger-text); }

    .checkup-card {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        border-radius:20px;
        overflow:hidden;
        height:100%;
        box-shadow:var(--wp-shadow-sm);
        transition:transform 0.22s ease, box-shadow 0.22s ease;
    }
    .checkup-card:hover {
        transform:translateY(-3px);
        box-shadow:var(--wp-shadow-md);
        border:none;
    }
    .checkup-card-header {
        padding:0.85rem 1.25rem;
        border:none;
        display:flex;
        align-items:center;
        justify-content:space-between;
    }
    .checkup-card-header.is-scheduled { background:var(--color-peach-soft); background-color:var(--color-peach-soft); }
    .checkup-card-header.is-completed { background:var(--color-success-soft); background-color:var(--color-success-soft); }
    .checkup-card-header.is-missed { background:var(--color-danger-soft); background-color:var(--color-danger-soft); }
    .checkup-status {
        border:none; border-radius:999px; padding:0.28rem 0.85rem; font-size:0.72rem; font-weight:800;
        display:inline-flex; align-items:center; gap:5px; text-transform:lowercase; letter-spacing:0.2px;
        background:var(--color-surface); background-color:var(--color-surface);
    }
    .checkup-status.st-scheduled { color:var(--color-warning-text); }
    .checkup-status.st-completed { color:var(--color-success-text); }
    .checkup-status.st-missed { color:var(--color-danger-text); }
    .checkup-card-body { padding:1.25rem 1.3rem 1.1rem; }
    .checkup-purpose {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:1rem;
        font-weight:800;
        color:var(--color-text);
        margin-bottom:1rem;
        line-height:1.4;
        letter-spacing:-0.01em;
    }
    .checkup-info-row {
        display:flex;
        align-items:flex-start;
        gap:0.7rem;
        margin-bottom:0.75rem;
        font-size:0.875rem;
        color:var(--color-text-muted);
    }
    .checkup-info-icon {
        width:32px; height:32px; border-radius:10px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center; font-size:0.9rem;
        background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text); margin-top:1px;
    }
    .checkup-info-row strong { color:var(--color-text); font-weight:700; font-size:0.82rem; }
    .checkup-card-footer {
        padding:0.8rem 1.3rem 1.1rem;
        border:none;
        font-size:0.78rem;
        color:var(--color-text-muted);
        display:flex;
        align-items:center;
        gap:0.4rem;
    }
</style>
@endpush

@section('user-content')
<div class="py-4 checkups-page">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 fade-in-card">
        <div>
            <h1 class="page-title">My Checkups
            </h1>
        </div>
    @if(($totals['all'] ?? $checkups->total()) > 0)
        <span class="checkups-count">
            {{ $totals['all'] ?? $checkups->total() }} {{ Str::plural('Record', $totals['all'] ?? $checkups->total()) }}
        </span>
    @endif
</div>

{{-- Summary chips --}}
@if(($totals['all'] ?? $checkups->total()) > 0)
<div class="d-flex flex-wrap gap-2 mb-4 fade-in-card">
    <span class="checkups-chip checkups-chip-sched">
        <i class="bi bi-calendar-check"></i>
        Scheduled: {{ $totals['scheduled'] ?? $checkups->where('status', 'Scheduled')->count() }}
    </span>
    <span class="checkups-chip checkups-chip-done">
        <i class="bi bi-check2-circle"></i>
        Completed: {{ $totals['completed'] ?? $checkups->where('status', 'Completed')->count() }}
    </span>
    <span class="checkups-chip checkups-chip-miss">
        <i class="bi bi-x-circle"></i>
        Missed: {{ $totals['missed'] ?? $checkups->where('status', 'Missed')->count() }}
    </span>
</div>
@endif

{{-- Cards --}}
@if($checkups->count() > 0)
    <div class="row g-3">
        @foreach($checkups as $checkup)
        <div class="col-lg-6 col-md-6 fade-in-card">
            <div class="checkup-card">
                {{-- Card Header --}}
                <div class="checkup-card-header @if($checkup->status === 'Scheduled') is-scheduled @elseif($checkup->status === 'Completed') is-completed @elseif($checkup->status === 'Missed') is-missed @endif">
                    <span class="checkup-status
                        @if($checkup->status === 'Scheduled') st-scheduled
                        @elseif($checkup->status === 'Completed') st-completed
                        @elseif($checkup->status === 'Missed') st-missed
                        @else st-scheduled
                        @endif
                    ">
                        @if($checkup->status === 'Scheduled')
                            <i class="bi bi-calendar-event"></i>
                        @elseif($checkup->status === 'Completed')
                            <i class="bi bi-check2-circle"></i>
                        @elseif($checkup->status === 'Missed')
                            <i class="bi bi-x-circle"></i>
                        @endif
                        {{ strtolower($checkup->status) }}
                    </span>
                </div>

                {{-- Card Body --}}
                <div class="checkup-card-body">
                    <h6 class="checkup-purpose">{{ $checkup->purpose }}</h6>

                    <div class="checkup-info-row">
                        <span class="checkup-info-icon"><i class="bi bi-calendar-date"></i></span>
                        <div>
                            <strong>Scheduled</strong><br>
                            {{ $checkup->scheduled_date->format('F j, Y') }}
                        </div>
                    </div>

                    @if($checkup->actual_date)
                    <div class="checkup-info-row">
                        <span class="checkup-info-icon"><i class="bi bi-calendar-check"></i></span>
                        <div>
                            <strong>Actual Visit</strong><br>
                            {{ $checkup->actual_date->format('F j, Y') }}
                        </div>
                    </div>
                    @endif

                    @if($checkup->midwife)
                    <div class="checkup-info-row">
                        <span class="checkup-info-icon"><i class="bi bi-person-badge-fill"></i></span>
                        <div>
                            <strong>Midwife</strong><br>
                            {{ $checkup->midwife->name }}
                        </div>
                    </div>
                    @endif

                    @if($checkup->notes)
                    <div class="checkup-info-row">
                        <span class="checkup-info-icon"><i class="bi bi-journal-text"></i></span>
                        <div>
                            <strong>Notes</strong><br>
                            {{ $checkup->notes }}
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Card Footer --}}
                <div class="checkup-card-footer">
                    <i class="bi bi-clock-history"></i>
                    Recorded on {{ $checkup->created_at->format('M j, Y') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

@else
    {{-- Empty State --}}
    <div class="card fade-in-card">
        <div class="empty-state">
            <i class="bi bi-clipboard2-x empty-state-icon"></i>
            <h6>No Checkup Records Yet</h6>
            <p>Your prenatal checkup schedule will appear here once it's been set up by your assigned midwife.</p>
        </div>
    </div>
@endif

@endsection
