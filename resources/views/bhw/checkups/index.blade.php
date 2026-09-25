@extends('bhw.layout')

@section('title', 'Checkups - ReproCare')

@push('styles')
<style>
    body .main-content .page-hero {
        background:var(--color-surface) !important; background-color:var(--color-surface) !important;
        border:1px solid var(--color-primary-soft) !important;
        border-radius:20px !important;
        box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important;
    }
    body .main-content .ck-stat {
        background:var(--color-surface) !important; background-color:var(--color-surface) !important;
        border:1px solid var(--color-primary-soft) !important;
        border-radius:20px !important;
        box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important;
        padding:1.35rem 1.4rem !important;
        display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;
        height:100%;
    }
    body .main-content .ck-stat:hover { transform:translateY(-3px); box-shadow:0 10px 26px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent) !important; }
    .ck-stat-label { font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-muted); margin-bottom:0.4rem; }
    .ck-stat-number { font-family:'Plus Jakarta Sans',sans-serif; font-size:2rem; font-weight:800; line-height:1.1; color:var(--color-text); letter-spacing:-0.02em; }
    .ck-stat-icon { width:44px; height:44px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; border:none; }
    .ck-stat-icon.peach { background:var(--color-peach-soft); background-color:var(--color-peach-soft); color:var(--color-warning-text); }
    .ck-stat-icon.rose { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:var(--color-secondary-text); }
    .ck-stat-icon.mint { background:var(--color-success-soft); background-color:var(--color-success-soft); color:var(--color-success-text); }
    .ck-archived-btn { border:none !important; border-radius:999px !important; padding:0.6rem 1.25rem !important; font-weight:800 !important; font-size:0.85rem !important; background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; color:var(--color-text) !important; box-shadow:none !important; display:inline-flex; align-items:center; gap:0.5rem; }
    .ck-archived-btn:hover { background:var(--color-border) !important; color:var(--color-text) !important; transform:translateY(-1px); }
    .ck-archived-btn i { color:var(--color-secondary-text); }
</style>
@endpush

@section('bhw-content')

{{-- ═══════════════════════════════
     PAGE HERO
════════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Scheduled Checkups
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Manage scheduled patient checkups
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleCheckupModal">
                <i class="bi bi-calendar-plus me-1"></i> Schedule Checkup
            </button>
            <a href="{{ route('bhw.checkups.archived') }}" class="btn ck-archived-btn">
                <i class="bi bi-archive-fill"></i> View Archived
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     STATS CARDS
════════════════════════════════ --}}
@php
    $allCheckups = $checkups instanceof \Illuminate\Pagination\LengthAwarePaginator ? $checkups->getCollection() : collect($checkups);
    $scheduledCount = $checkups instanceof \Illuminate\Pagination\LengthAwarePaginator ? $checkups->total() : (clone $allCheckups)->count();
    $todayCount = (clone $allCheckups)->filter(fn ($c) => optional($c->scheduled_date)->isToday())->count();
    $upcomingCount = (clone $allCheckups)->filter(fn ($c) => optional($c->scheduled_date)->isFuture())->count();
@endphp
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-cyan ck-stat fade-in-card">
            <div>
                <div class="ck-stat-label">Scheduled</div>
                <div class="ck-stat-number">{{ $scheduledCount }}</div>
            </div>
            <span class="ck-stat-icon peach"><i class="bi bi-clock"></i></span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-green ck-stat fade-in-card">
            <div>
                <div class="ck-stat-label">Due Today</div>
                <div class="ck-stat-number">{{ $todayCount }}</div>
            </div>
            <span class="ck-stat-icon rose"><i class="bi bi-calendar-day-fill"></i></span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-success ck-stat fade-in-card">
            <div>
                <div class="ck-stat-label">Upcoming</div>
                <div class="ck-stat-number">{{ $upcomingCount }}</div>
            </div>
            <span class="ck-stat-icon mint"><i class="bi bi-calendar-week-fill"></i></span>
        </div>
    </div>
</div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ═══════════════════════════════
         CHECKUPS TABLE
    ════════════════════════════════ --}}
    <div class="card fade-in-card" style="border:none; background:var(--bg-card);">
        <div class="card-header" style="background:transparent; border-bottom:1px solid var(--border-color); padding:1rem 1.5rem;">
            <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">Scheduled Checkups
            </h5>
        </div>
        <div class="card-body p-4">
            @if($checkups->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Purpose</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checkups as $checkup)
                                <tr>
                                    <td>{{ $checkup->patient_name }}</td>
                                    <td>{{ optional($checkup->scheduled_date)->format('M j, Y') ?? '—' }}</td>
                                    <td>{{ $checkup->purpose ?? '—' }}</td>
                                    <td>
                                        @if($checkup->status === 'Scheduled')
                                            <span class="badge bg-primary">Scheduled</span>
                                        @elseif($checkup->status === 'Completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($checkup->status === 'Missed')
                                            <span class="badge bg-danger">Missed</span>
                                        @elseif($checkup->status === 'Rescheduled')
                                            <span class="badge bg-warning text-dark">Rescheduled</span>
                                        @elseif($checkup->status === 'Archived')
                                            <span class="badge bg-secondary">Archived</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $checkup->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $checkups->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-calendar-check empty-state-icon"></i>
                    <h6>No Checkups Scheduled</h6>
                    <p>There are no scheduled checkups yet.</p>
                </div>
            @endif
        </div>
    </div>

{{-- Schedule Checkup: pick an enrolled woman, then fill the schedule form --}}
<div class="modal fade" id="scheduleCheckupModal" tabindex="-1" aria-labelledby="scheduleCheckupLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleCheckupLabel">Schedule Checkup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-semibold" for="scheduleWomanSelect">Enrolled woman</label>
                <select id="scheduleWomanSelect" class="form-select">
                    <option value="">Select a woman...</option>
                    @foreach(($women ?? collect()) as $woman)
                        <option value="{{ $woman->id }}">{{ $woman->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Unlinked profiles can't take checkups — convert one to an enrolled account from Women first.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="scheduleGoBtn" disabled>Continue</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    (function () {
        var select = document.getElementById('scheduleWomanSelect');
        var go = document.getElementById('scheduleGoBtn');
        if (!select || !go) return;
        select.addEventListener('change', function () { go.disabled = !select.value; });
        go.addEventListener('click', function () {
            if (select.value) window.location.href = "{{ url('bhw/checkups/create') }}/" + select.value;
        });
    })();
</script>
@endpush
@endsection
