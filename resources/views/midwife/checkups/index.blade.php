@extends('midwife.layout')

@section('title', 'Checkups - Midwife Portal | ReproCare')

@push('styles')
<style>
    .patient-avatar {
        width:34px; height:34px; border-radius:50%;
        background:linear-gradient(135deg, var(--primary), var(--accent-violet));
        display:flex; align-items:center; justify-content:center;
        font-size:0.78rem; font-weight:800; color:var(--color-on-solid); flex-shrink:0;
    }
    .search-bar {
        display:flex; gap:0.5rem; align-items:center;
        background:var(--bg-card2); border:1px solid var(--border);
        border-radius:12px; padding:0.4rem 0.6rem; max-width:300px;
        transition:border-color 0.2s ease;
    }
    .search-bar:focus-within { border-color:var(--primary); box-shadow:0 0 0 3px var(--focus-ring); }
    .search-bar input {
        border:none; background:transparent; color:var(--text);
        font-size:0.875rem; outline:none; flex:1; min-width:0;
    }
    .search-bar input::placeholder { color:var(--placeholder); }
    .search-bar button {
        border:none; background:transparent; color:var(--text-muted);
        cursor:pointer; padding:0.2rem 0.35rem; border-radius:8px;
        transition:background 0.15s ease;
    }
    .search-bar button:hover { background:var(--primary-subtle); color:var(--primary-light); }
    .btn-action {
        width:30px; height:30px; border-radius:8px;
        display:inline-flex; align-items:center; justify-content:center;
        font-size:0.78rem; border:1px solid var(--border); background:var(--bg-card2);
        color:var(--text-muted); transition:all 0.15s ease; cursor:pointer;
    }
    .btn-action:hover { transform:scale(1.12); }
    .ba-view:hover    { background:var(--primary-subtle);border-color:var(--primary);color:var(--primary-light); }
    .ba-edit:hover    { background:color-mix(in srgb, var(--color-warning) 12%, transparent);border-color:var(--warning);color:var(--warning); }
    .ba-done:hover    { background:color-mix(in srgb, var(--color-success) 12%, transparent);border-color:var(--success);color:var(--success); }
    .ba-miss:hover    { background:color-mix(in srgb, var(--color-danger) 12%, transparent);border-color:var(--danger);color:var(--danger); }
    .ba-cancel:hover  { background:var(--bg-card2);border-color:var(--text-muted);color:var(--text-muted); }
    .ba-resched:hover { background:color-mix(in srgb, var(--color-warning) 12%, transparent);border-color:var(--warning);color:var(--warning); }
    .purpose-pill {
        background:color-mix(in srgb, var(--color-info) 14%, transparent); color:var(--color-info-text);
        border-radius:10px; padding:0.2em 0.65em;
        font-size:0.78rem; font-weight:600; max-width:160px;
        overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block;
    }
    .checkup-alert {
        border:1px solid var(--color-border); border-left:4px solid var(--color-warning);
        border-radius:16px; background:var(--color-surface); overflow:hidden; margin-bottom:1rem;
    }
    .checkup-alert.is-danger { border-left-color:var(--color-danger); }
    .checkup-alert-head {
        display:flex; align-items:center; gap:0.85rem;
        padding:0.85rem 1.1rem; cursor:pointer; user-select:none;
        background:color-mix(in srgb, var(--color-warning) 9%, var(--color-surface));
        transition:background 0.15s ease;
    }
    .checkup-alert.is-danger .checkup-alert-head {
        background:color-mix(in srgb, var(--color-danger) 9%, var(--color-surface));
    }
    .checkup-alert-head:hover { background:color-mix(in srgb, var(--color-warning) 15%, var(--color-surface)); }
    .checkup-alert.is-danger .checkup-alert-head:hover { background:color-mix(in srgb, var(--color-danger) 15%, var(--color-surface)); }
    .checkup-alert-icon {
        width:36px; height:36px; border-radius:11px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center; font-size:1rem;
        background:var(--color-warning-soft); color:var(--color-warning-text);
    }
    .checkup-alert.is-danger .checkup-alert-icon { background:var(--color-danger-soft); color:var(--color-danger-text); }
    .checkup-alert-title { font-size:0.88rem; font-weight:800; color:var(--color-text); line-height:1.3; }
    .checkup-alert-sub { font-size:0.75rem; color:var(--color-text-muted); }
    .checkup-alert-count {
        font-size:0.72rem; font-weight:800; padding:0.18em 0.6em; border-radius:999px; flex-shrink:0;
        background:var(--color-warning-soft); color:var(--color-warning-text); border:1px solid var(--color-warning);
    }
    .checkup-alert.is-danger .checkup-alert-count {
        background:var(--color-danger-soft); color:var(--color-danger-text); border-color:var(--color-danger);
    }
    .checkup-alert-chevron { color:var(--color-text-muted); font-size:0.8rem; flex-shrink:0; transition:transform 0.2s ease; }
    .checkup-alert-head[aria-expanded="true"] .checkup-alert-chevron { transform:rotate(180deg); }
    .checkup-alert-body { padding:0.15rem 1.1rem 0.35rem; max-height:320px; overflow-y:auto; }
    .checkup-alert-row {
        display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;
        padding:0.55rem 0.15rem; border-bottom:1px solid var(--color-border);
    }
    .checkup-alert-row:last-of-type { border-bottom:none; }
    .checkup-alert-patient { display:flex; align-items:center; gap:0.6rem; flex:1 1 180px; min-width:0; }
    .checkup-alert-name { font-weight:700; font-size:0.83rem; color:var(--color-text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .checkup-alert-meta { font-size:0.72rem; color:var(--color-text-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .checkup-alert-date { font-size:0.8rem; font-weight:700; white-space:nowrap; margin-left:auto; text-align:right; }
    .checkup-alert-date.is-warning { color:var(--color-warning-text); }
    .checkup-alert-date.is-danger { color:var(--color-danger-text); }
    .checkup-alert-action { flex-shrink:0; }
    .checkup-alert-foot { padding:0.5rem 0.15rem 0.35rem; }
    .checkup-alert-foot a { font-size:0.77rem; font-weight:700; text-decoration:none; }
</style>
@endpush

@section('midwife-content')

{{-- ── Header ── --}}
<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Checkups</div>
            <p class="page-hero-subtitle">Manage prenatal and postnatal visits across patients.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('midwife.checkups.create') }}" class="btn-hero-primary">
                <i class="bi bi-calendar-plus-fill"></i> + Schedule Checkup
            </a>
        </div>
    </div>
</div>

{{-- ── Single-Row Filters ── --}}
<div class="card fade-in-card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('midwife.checkups.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <div class="position-relative flex-grow-1" style="min-width:240px; max-width:360px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="search" name="search" placeholder="Search patient, purpose..." value="{{ request('search') }}" class="form-control ps-5" style="height:42px; border-radius:12px;">
            </div>
            <select name="status" class="form-select" style="width:160px; height:42px; border-radius:12px;" onchange="this.form.submit()">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="missed" {{ $status === 'missed' ? 'selected' : '' }}>Missed</option>
                <option value="rescheduled" {{ $status === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
            </select>
            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" style="width:160px; height:42px; border-radius:12px;" onchange="this.form.submit()">
            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" style="width:160px; height:42px; border-radius:12px;" onchange="this.form.submit()">
            @if(request()->hasAny(['search', 'status', 'start_date', 'end_date']))
                <a href="{{ route('midwife.checkups.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none fw-semibold">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

{{-- ── Flash ── --}}
@if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 fade-in-card">
        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
        {{ session('success') }}
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── Summary stat cards ── --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(155,100,185,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div style="width:44px;height:44px;border-radius:14px;background:var(--color-primary-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-primary-text);">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <span style="background:var(--color-primary-soft);color:var(--color-primary-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Total</span>
            </div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Total Visits</div>
            <div class="stat-number" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $stats['total'] }}</div>
            <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                <i class="bi bi-calendar3"></i> All time registry
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(217,119,6,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div style="width:44px;height:44px;border-radius:14px;background:var(--color-warning-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-warning-text);">
                    <i class="bi bi-clock"></i>
                </div>
                <span style="background:var(--color-warning-soft);color:var(--color-warning-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Pending</span>
            </div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Scheduled</div>
            <div class="stat-number" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $stats['scheduled'] }}</div>
            <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                <i class="bi bi-calendar-check"></i> Confirmed upcoming
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(4,120,87,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div style="width:44px;height:44px;border-radius:14px;background:var(--color-success-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-success-text);">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <span style="background:var(--color-success-soft);color:var(--color-success-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Done</span>
            </div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Completed</div>
            <div class="stat-number" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $stats['completed'] }}</div>
            <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                <i class="bi bi-shield-check"></i> Finished visits
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(220,38,38,0.10)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div style="width:44px;height:44px;border-radius:14px;background:var(--color-danger-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-danger-text);">
                    <i class="bi bi-x-circle"></i>
                </div>
                <span style="background:var(--color-danger-soft);color:var(--color-danger-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Action</span>
            </div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Missed / Rescheduled</div>
            <div class="stat-number" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $stats['missed'] + $stats['rescheduled'] }}</div>
            <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                <i class="bi bi-arrow-repeat"></i> Requires follow-up
            </div>
        </div>
    </div>
</div>

{{-- ── Rescheduled Checkups Alert Section ── --}}
@php
    $rescheduledTotal = $stats['rescheduled'];
    $rescheduledCheckups = \App\Models\Checkup::with(['woman', 'walkInPatient'])
        ->where('status', 'Rescheduled')
        ->orderBy('scheduled_date', 'desc')
        ->take(20)
        ->get();
    $rescheduledExpanded = $rescheduledCheckups->count() <= 3;
@endphp
@if($rescheduledTotal > 0)
<div class="checkup-alert fade-in-card">
    <div class="checkup-alert-head" role="button" data-bs-toggle="collapse" data-bs-target="#rescheduledCheckupsPanel"
         aria-expanded="{{ $rescheduledExpanded ? 'true' : 'false' }}" aria-controls="rescheduledCheckupsPanel">
        <div class="checkup-alert-icon"><i class="bi bi-arrow-repeat"></i></div>
        <div style="flex:1 1 auto; min-width:0;">
            <div class="checkup-alert-title">{{ $rescheduledTotal }} Rescheduled Checkup{{ $rescheduledTotal === 1 ? '' : 's' }} Pending</div>
            <div class="checkup-alert-sub">Confirm the new schedule with {{ $rescheduledTotal === 1 ? 'this patient' : 'these patients' }}.</div>
        </div>
        <span class="checkup-alert-count">{{ $rescheduledTotal }}</span>
        <i class="bi bi-chevron-down checkup-alert-chevron"></i>
    </div>
    <div class="collapse {{ $rescheduledExpanded ? 'show' : '' }}" id="rescheduledCheckupsPanel">
        <div class="checkup-alert-body">
            @foreach($rescheduledCheckups as $rescheduled)
            @php
                $dateDisplay = $rescheduled->scheduled_date
                    ? (\is_string($rescheduled->scheduled_date)
                        ? \Carbon\Carbon::parse($rescheduled->scheduled_date)
                        : $rescheduled->scheduled_date)->format('M j, Y')
                    : '—';
            @endphp
            <div class="checkup-alert-row">
                <div class="checkup-alert-patient">
                    <div class="patient-avatar">{{ strtoupper(substr($rescheduled->patient_name, 0, 1)) }}</div>
                    <div style="min-width:0;">
                        <div class="checkup-alert-name">{{ $rescheduled->patient_name }}</div>
                        <div class="checkup-alert-meta">{{ Str::limit($rescheduled->purpose ?? 'No purpose', 34) }}</div>
                    </div>
                </div>
                <div class="checkup-alert-date is-warning">
                    {{ $dateDisplay }}
                </div>
                <div class="checkup-alert-action">
                    <a href="{{ route('midwife.checkups.show', $rescheduled->id) }}" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-eye"></i> View
                    </a>
                </div>
            </div>
            @endforeach
            <div class="checkup-alert-foot">
                <a href="{{ route('midwife.checkups.index') }}?status=rescheduled">
                    View all {{ $rescheduledTotal }} rescheduled checkup{{ $rescheduledTotal === 1 ? '' : 's' }} <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Missed Checkups Alert Section ── --}}
@php
    $missedTotal = $stats['missed'];
    $missedCheckups = \App\Models\Checkup::with(['woman', 'walkInPatient'])
        ->where('status', 'Missed')
        ->orderBy('scheduled_date', 'desc')
        ->take(20)
        ->get();
    $missedExpanded = $missedCheckups->count() <= 3;
@endphp
@if($missedTotal > 0)
<div class="checkup-alert is-danger fade-in-card">
    <div class="checkup-alert-head" role="button" data-bs-toggle="collapse" data-bs-target="#missedCheckupsPanel"
         aria-expanded="{{ $missedExpanded ? 'true' : 'false' }}" aria-controls="missedCheckupsPanel">
        <div class="checkup-alert-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div style="flex:1 1 auto; min-width:0;">
            <div class="checkup-alert-title">{{ $missedTotal }} Missed Checkup{{ $missedTotal === 1 ? '' : 's' }} Require Attention</div>
            <div class="checkup-alert-sub">Reschedule or follow up on {{ $missedTotal === 1 ? 'this visit' : 'these visits' }}.</div>
        </div>
        <span class="checkup-alert-count">{{ $missedTotal }}</span>
        <i class="bi bi-chevron-down checkup-alert-chevron"></i>
    </div>
    <div class="collapse {{ $missedExpanded ? 'show' : '' }}" id="missedCheckupsPanel">
        <div class="checkup-alert-body">
            @foreach($missedCheckups as $missed)
            @php
                $dateDisplay = $missed->scheduled_date
                    ? (\is_string($missed->scheduled_date)
                        ? \Carbon\Carbon::parse($missed->scheduled_date)
                        : $missed->scheduled_date)->format('M j, Y')
                    : '—';
            @endphp
            <div class="checkup-alert-row">
                <div class="checkup-alert-patient">
                    <div class="patient-avatar">{{ strtoupper(substr($missed->patient_name, 0, 1)) }}</div>
                    <div style="min-width:0;">
                        <div class="checkup-alert-name">{{ $missed->patient_name }}</div>
                        <div class="checkup-alert-meta">{{ Str::limit($missed->purpose ?? 'No purpose', 34) }}</div>
                    </div>
                </div>
                <div class="checkup-alert-date is-danger">
                    {{ $dateDisplay }}
                </div>
                <div class="checkup-alert-action">
                    <form action="{{ route('midwife.checkups.schedule', $missed->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-warning" title="Reschedule">
                            <i class="bi bi-calendar-event me-1"></i> Reschedule
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
            <div class="checkup-alert-foot">
                <a href="{{ route('midwife.checkups.index') }}?status=missed">
                    View all {{ $missedTotal }} missed checkup{{ $missedTotal === 1 ? '' : 's' }} <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Checkups Table ── --}}
<div class="table-card fade-in-card">

    @if($checkups->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover table-sticky-head mb-0">
            <thead>
                <tr style="border-bottom:1px solid var(--color-border);">
                    <th class="ps-3 py-3">Date &amp; Time</th>
                    <th class="py-3">Patient</th>
                    <th class="py-3">Purpose</th>
                    <th class="py-3">Midwife</th>
                    <th class="py-3">Status</th>
                    <th class="pe-3 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($checkups as $checkup)
                @php
                    $dateDisplay = $timeDisplay = '';
                    if($checkup->scheduled_date) {
                        $dateDisplay = is_string($checkup->scheduled_date)
                            ? \Carbon\Carbon::parse($checkup->scheduled_date)->format('M j, Y')
                            : $checkup->scheduled_date->format('M j, Y');
                    }
                    if($checkup->scheduled_time) {
                        $timeDisplay = \Carbon\Carbon::parse($checkup->scheduled_time)->format('g:i A');
                    }
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:0.875rem;color:var(--text);">{{ $dateDisplay }}</div>
                        @if($timeDisplay)
                        <div style="font-size:0.75rem;color:var(--text-muted);">
                            <i class="bi bi-clock me-1"></i>{{ $timeDisplay }}
                        </div>
                        @endif
                    </td>
                    <td>
                        @if($checkup->patient_record)
                        <div style="display:flex;align-items:center;gap:0.6rem;">
                            <div class="patient-avatar">{{ strtoupper(substr($checkup->patient_name, 0, 1)) }}</div>
                            <div>
                                <div style="font-weight:700;font-size:0.875rem;color:var(--text);">{{ $checkup->patient_name }}</div>
                                <div style="font-size:0.75rem;color:var(--text-muted);">{{ $checkup->woman?->email ?? $checkup->walkInPatient?->contact_number ?? 'Unlinked woman' }}</div>
                            </div>
                        </div>
                        @else
                            <span style="color:var(--text-muted);font-size:0.875rem;">—</span>
                        @endif
                    </td>
                    <td><span class="purpose-pill">{{ Str::limit($checkup->purpose ?? 'No purpose', 22) }}</span></td>
                    <td>
                        @if($checkup->midwife)
                        <div style="font-weight:600;font-size:0.875rem;color:var(--text);">{{ $checkup->midwife->name }}</div>
                        <div style="font-size:0.75rem;color:var(--text-muted);">{{ $checkup->midwife->email }}</div>
                        @else
                            <span style="color:var(--text-muted);font-size:0.875rem;">Not assigned</span>
                        @endif
                    </td>
                    <td>
                        @if($timeDisplay)
                        <div style="font-weight:600;font-size:0.875rem;color:var(--text);">
                            <i class="bi bi-clock me-1" style="color:var(--primary-light);"></i>{{ $timeDisplay }}
                        </div>
                        @else
                        <span style="color:var(--text-muted);font-size:0.875rem;">—</span>
                        @endif
                    </td>
                    <td>
                        @switch($checkup->status)
                            @case('scheduled')
                            @case('Scheduled')   <span class="status-scheduled"><i class="bi bi-clock"></i> Scheduled</span>  @break
                            @case('completed')
                            @case('Completed')   <span class="status-completed"><i class="bi bi-check2-circle"></i> Completed</span> @break
                            @case('missed')
                            @case('Missed')      <span class="status-missed"><i class="bi bi-x-circle"></i> Missed</span>      @break
                            @case('cancelled')   <span class="status-cancelled"><i class="bi bi-dash-circle"></i> Cancelled</span> @break
                            @case('Rescheduled') <span class="ba-resched"><i class="bi bi-arrow-repeat"></i> Rescheduled</span> @break
                            @default             <span class="status-pending">{{ ucfirst($checkup->status ?? 'Unknown') }}</span>
                        @endswitch
                    </td>
                    <td class="text-end pe-3 py-3">
                        <div class="d-inline-flex align-items-center gap-1">
                            {{-- Primary Action: View Details --}}
                            <a href="{{ route('midwife.checkups.show', $checkup->id) }}" class="btn btn-sm btn-view px-2.5 py-1" style="border-radius:8px; font-size:0.8rem; font-weight:700;" title="View Details">
                                <i class="bi bi-eye-fill me-1"></i> View
                            </a>

                            {{-- Actions Dropdown Menu --}}
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-light border px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:8px;" title="More Actions">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius:12px; border:1px solid var(--color-border); font-size:0.85rem;">
                                    @if(in_array($checkup->status, ['scheduled', 'Rescheduled']))
                                        <li>
                                            <a class="dropdown-item py-1.5" href="{{ route('midwife.checkups.edit', $checkup->id) }}">
                                                <i class="bi bi-pencil me-2 text-primary"></i> Edit Checkup
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('midwife.checkups.complete', $checkup->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item py-1.5 text-success" onclick="return confirm('Mark as completed?')">
                                                    <i class="bi bi-check2-circle me-2"></i> Mark Completed
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('midwife.checkups.miss', $checkup->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item py-1.5 text-warning" onclick="return confirm('Mark as missed?')">
                                                    <i class="bi bi-x-circle me-2"></i> Mark Missed
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('midwife.checkups.cancel', $checkup->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item py-1.5 text-secondary" onclick="return confirm('Cancel this checkup?')">
                                                    <i class="bi bi-dash-circle me-2"></i> Cancel
                                                </button>
                                            </form>
                                        </li>
                                    @elseif(in_array($checkup->status, ['completed', 'missed', 'cancelled']))
                                        <li>
                                            <form action="{{ route('midwife.checkups.schedule', $checkup->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item py-1.5 text-primary" onclick="return confirm('Reschedule this checkup?')">
                                                    <i class="bi bi-arrow-repeat me-2"></i> Reschedule
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('midwife.checkups.destroy', $checkup->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-1.5 text-danger" onclick="return confirm('Archive this checkup?')">
                                                <i class="bi bi-archive me-2"></i> Archive
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center" style="padding:1.25rem;">
        {{ $checkups->links() }}
    </div>

    @else
    <div class="empty-state">
        <i class="bi bi-calendar-x empty-state-icon"></i>
        <h6>No Checkups Found</h6>
        <p>
            @if(request('search'))
                No checkups match "{{ request('search') }}".
                <a href="{{ route('midwife.checkups.index') }}">Clear search</a>
            @else
                No checkups have been scheduled yet.
            @endif
        </p>
        <a href="{{ route('midwife.checkups.create') }}" class="btn btn-primary">
            <i class="bi bi-calendar-plus-fill me-1"></i> Schedule First Checkup
        </a>
    </div>
    @endif

</div>

@endsection
