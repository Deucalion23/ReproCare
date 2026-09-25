@extends('user.layout')

@section('title', 'Menstrual Cycle - ReproCare')

@push('styles')
<style>
/* === CYCLE OVERVIEW — clean, borderless, dashboard-matched === */
.women-shell { background-color:var(--color-surface) !important; }
.menstruation-page .page-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.6rem; font-weight:800; color:var(--color-text); letter-spacing:-0.02em; margin-bottom:0.15rem; }
.menstruation-page .page-subtitle { font-size:0.9rem; color:var(--color-text-muted); margin:0; }

.cycle-hero {
    background:var(--color-surface); background-color:var(--color-surface);
    border:1px solid var(--color-border);
    border-radius:24px;
    padding:2rem 2.25rem;
    position:relative;
    overflow:hidden;
    margin-bottom:1.5rem;
    box-shadow:var(--wp-shadow-sm);
}

 [data-theme="light"] .cycle-hero {
     background:var(--color-surface); background-color:var(--color-surface);
 }

.cycle-hero::before {
    content:'';
    position:absolute;
    width:360px; height:360px;
    border-radius:50%;
    background:radial-gradient(circle, color-mix(in srgb, var(--color-danger-soft) 95%, transparent) 0%, transparent 70%);
    top:-140px; right:-90px; pointer-events:none;
}

.cycle-hero::after {
    content:'';
    position:absolute;
    width:280px; height:280px;
    border-radius:50%;
    background:radial-gradient(circle, color-mix(in srgb, var(--color-danger-soft) 70%, transparent) 0%, transparent 70%);
    bottom:-140px; left:-60px; pointer-events:none;
}

.cycle-hero-content { position:relative; z-index:1; }
.cycle-hero-layout { display:flex; align-items:flex-start; gap:2.5rem; }
.cycle-left { flex-shrink:0; display:flex; flex-direction:column; align-items:center; text-align:center; min-width:220px; }
@media (max-width:768px) {
    .cycle-hero { padding:1.5rem 1.25rem; }
    .cycle-hero-layout { flex-direction:column; align-items:center; text-align:center; gap:1.5rem; }
    .cycle-right { text-align:center; width:100%; }
    .cycle-right .cycle-hero-sub { margin-left:auto; margin-right:auto; }
}

.cycle-phase-badge {
    display:inline-flex;
    align-items:center;
    gap:0.5rem;
    font-size:0.72rem;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:1px;
    padding:0.38rem 0.9rem;
    border-radius:999px;
    border:none;
    margin-bottom:0.75rem;
}

.cycle-ring {
    width:180px; height:180px;
    border-radius:50%;
    background:conic-gradient(var(--color-danger) var(--progress, 30%), var(--color-danger-soft) 0%);
    display:flex; align-items:center; justify-content:center;
    position:relative;
    box-shadow:none;
    flex-shrink:0;
}

.cycle-ring::before {
    content:'';
    position:absolute;
    width:146px; height:146px;
    background:var(--color-surface);
    border-radius:50%;
    box-shadow:inset 0 2px 8px rgb(var(--color-shadow-rgb) / .06);
}

.cycle-ring-inner {
    position:relative; z-index:1; text-align:center;
    display:flex; flex-direction:column; align-items:center;
}

.cycle-ring-inner .day-num {
    font-family:'Plus Jakarta Sans',sans-serif;
    font-size:2.6rem;
    font-weight:900;
    line-height:1;
    letter-spacing:-0.02em;
    color:#000;
}

.cycle-ring-inner .day-label {
    font-size:0.68rem;
    color:#000;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:1px;
    margin-top:0.15rem;
}

.cycle-overdue-pill {
    display:inline-flex; align-items:center; gap:4px;
    background:var(--color-danger); color:#fff;
    font-size:0.62rem; font-weight:800; text-transform:uppercase; letter-spacing:0.6px;
    padding:0.22rem 0.65rem; border-radius:999px; margin-top:0.4rem;
    box-shadow:none;
}

.cycle-hero-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.75rem; font-weight:800; color:var(--color-text); letter-spacing:-0.02em; margin-bottom:0.25rem; }
.cycle-hero-sub { color:var(--color-text-muted); font-size:0.9rem; margin-bottom:1rem; }
.cycle-hero-sub strong { color:var(--color-secondary-text); }
.cycle-hero-meta { color:var(--color-text-muted); font-size:0.82rem; }

.cycle-tabs { display:inline-flex; align-items:center; gap:4px; background:var(--color-surface-soft); border:1px solid var(--color-border); border-radius:999px; padding:4px; }
.cycle-nav-btn { display:inline-flex; align-items:center; justify-content:center; gap:6px; border:none; border-radius:999px; font-size:0.82rem; font-weight:800; padding:0.55rem 1.35rem; text-decoration:none; transition:all 0.2s ease; white-space:nowrap; }
.cycle-nav-dark { background:var(--color-danger); color:#fff; box-shadow:none; }
.cycle-nav-dark:hover { background:var(--color-danger-hover); color:#fff; transform:translateY(-1px); }
.cycle-nav-soft { background:transparent; color:var(--color-danger-text); }
.cycle-nav-soft:hover { background:var(--color-danger-soft); color:var(--color-danger-text); transform:translateY(-1px); }

.history-card { background:var(--color-surface); border:1px solid var(--color-border); border-radius:20px; box-shadow:var(--wp-shadow-sm); overflow:hidden; }
.history-count { background:var(--color-secondary-soft); color:var(--color-secondary-text); border:none; font-size:0.72rem; font-weight:800; padding:0.35em 0.9em; border-radius:999px; }

.period-record-card {
    background:var(--color-bg);
    border:none;
    border-radius:16px;
    padding:1rem 1.25rem;
    transition:all 0.2s ease;
}

.period-record-card:hover {
    border:none;
    transform:translateY(-2px);
    box-shadow:var(--wp-shadow-sm);
    background:var(--color-surface);
}

.period-dot {
    width:10px; height:10px;
    border-radius:50%;
    background:var(--color-secondary);
    flex-shrink:0;
    box-shadow:none;
}
.period-date-num { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.5rem; font-weight:800; color:var(--color-secondary-text); line-height:1; }
.period-date-mon { font-size:0.68rem; color:var(--color-text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:800; }
.period-range { font-size:0.92rem; color:var(--color-text); font-weight:700; }
.period-days-badge { background:var(--color-secondary-soft); color:var(--color-secondary-text); border:none; border-radius:999px; padding:0.3em 0.85em; font-size:0.72rem; font-weight:800; }
.period-del-btn { border:none; background:var(--color-surface-soft); color:var(--color-text-muted); border-radius:50%; width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; font-size:0.8rem; transition:all 0.2s ease; }
.period-del-btn:hover { background:var(--color-danger-soft); color:var(--color-danger-text); }
</style>
@endpush

@section('user-content')
<div class="py-2 menstruation-page">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Menstrual Cycle</h1>
            <p class="page-subtitle">Track, predict, and understand your cycle</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Hero Cycle Card -->
    <div class="cycle-hero mb-4">
        <div class="cycle-hero-content cycle-hero-layout">
            <!-- Cycle Ring -->
            @php
                $cycleDays = 28; // Default
                $currentDay = 1;
                $isOverdue = false;
                if ($records->count() > 0) {
                    $lastPeriod = $records->first();
                    if ($averageCycle) $cycleDays = round($averageCycle);
                    
                    // Calculate current day of cycle
                    $currentDay = (int) $lastPeriod->period_start_date->diffInDays(today(), false) + 1;
                    
                    // Check if cycle is overdue
                    if ($currentDay > $cycleDays) {
                        $currentDay = $cycleDays;
                        $isOverdue = $predictionDetail['next_latest'] && today()->gt($predictionDetail['next_latest']);
                    }
                    
                    $currentDay = max(1, min($currentDay, $cycleDays));
                }
                $progress = min($currentDay / $cycleDays, 1) * 100;

                // Determine phase
                $phase = 'Follicular';
                $phaseColor = 'var(--color-purple-text)';
                $phaseBg = 'rgba(139,92,246,0.15)';
                $phaseIcon = 'bi-stars';

                if ($records->count() > 0) {
                    if ($predictionDetail['regularity'] !== 'regular') {
                        $phase = 'Uncertain';
                    } elseif ($currentDay <= ($records->first()->period_length ?? 5)) {
                        $phase = 'Menstrual'; $phaseColor = 'var(--color-danger-text)'; $phaseBg = 'var(--color-danger-soft)'; $phaseIcon = 'bi-droplet-fill';
                    } elseif ($currentDay < $cycleDays - 14) {
                        $phase = 'Follicular'; $phaseColor = 'var(--color-primary-text)'; $phaseBg = 'var(--color-primary-soft)'; $phaseIcon = 'bi-stars';
                    } elseif ($currentDay == $cycleDays - 14) {
                        $phase = 'Ovulation'; $phaseColor = 'var(--color-secondary-text)'; $phaseBg = 'var(--color-secondary-soft)'; $phaseIcon = 'bi-egg-fill';
                    } else {
                        $phase = 'Luteal'; $phaseColor = 'var(--color-warning-text)'; $phaseBg = 'var(--color-peach-soft)'; $phaseIcon = 'bi-moon-stars';
                    }
                }
            @endphp

            @if($records->count() > 0)
            <div class="cycle-left">
                <div class="cycle-ring" style="--progress:{{ $progress }}%;">
                    <div class="cycle-ring-inner">
                        <div class="day-num">{{ $currentDay }}</div>
                        <div class="day-label">of {{ $cycleDays }} days</div>
                        @if($isOverdue)
                        <div class="cycle-overdue-pill">
                            <i class="bi bi-exclamation-circle"></i> Overdue
                        </div>
                        @endif
                    </div>
                </div>
                <div class="cycle-tabs mt-3">
                    <a href="{{ route('user.menstruation.calendar') }}" class="cycle-nav-btn cycle-nav-dark">
                        <i class="bi bi-calendar3"></i> Calendar
                    </a>
                    <a href="{{ route('user.menstruation.statistics') }}" class="cycle-nav-btn cycle-nav-soft">
                        <i class="bi bi-bar-chart-line"></i> Statistics
                    </a>
                </div>
            </div>
            @endif

            <div class="flex-grow-1 cycle-right">
                @if($records->count() > 0)
                    <div class="cycle-phase-badge" style="background:{{ $phaseBg }}; color:{{ $phaseColor }};">
                        <i class="bi {{ $phaseIcon }}"></i> {{ $phase }} phase (estimate)
                    </div>
                @endif

                @if($nextPeriod)
                    <h2 class="cycle-hero-title">
                        {{ $predictionDetail['next_earliest']->format('M j') }} – {{ $predictionDetail['next_latest']->format('M j') }}
                    </h2>
                    <p class="cycle-hero-sub">
                        Estimated next-period start window · {{ ucfirst($predictionDetail['confidence']) }} confidence.
                        @if(today()->gt($predictionDetail['next_latest']))
                            This estimate has passed. Log any unrecorded period; discuss persistent changes with your health worker.
                        @endif
                    </p>
                    @if($averageCycle)
                        <small class="cycle-hero-meta">
                            <i class="bi bi-arrow-repeat me-1"></i> Average cycle: {{ $averageCycle }} days
                        </small>
                    @endif
                    @php
                        $regularity = null;
                        try { $regularity = \App\Models\Cycle::getCycleRegularity(auth()->id()); } catch (\Throwable $e) {}
                    @endphp
                    @if($regularity === 'irregular')
                        @php
                            $detail = null;
                            try { $detail = (new \App\Services\CyclePredictionService())->getPredictionDetail(auth()->id()); } catch (\Throwable $e) {}
                        @endphp
                        <div class="mt-2" style="background:var(--color-peach-soft); border-radius:12px; padding:0.6rem 0.9rem; font-size:0.8rem; color:var(--color-warning-text); font-weight:600;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Irregular cycle detected
                            @if($detail && ($detail['next_earliest'] ?? null) && ($detail['next_latest'] ?? null))
                                — next period likely between
                                <strong>{{ $detail['next_earliest']->format('M j') }} and {{ $detail['next_latest']->format('M j') }}</strong>
                                (not an exact day). Ovulation estimates are hidden for irregular cycles.
                            @else
                                — predictions are shown as a range. Keep logging and consult your midwife if variation persists.
                            @endif
                        </div>
                    @elseif($regularity === 'somewhat_irregular')
                        <div class="mt-2" style="background:var(--color-primary-soft); border-radius:12px; padding:0.6rem 0.9rem; font-size:0.8rem; color:var(--color-primary-text); font-weight:600;">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            Slightly irregular cycle — prediction may shift by a few days.
                        </div>
                    @endif
                @else
                    <h3 class="fw-700 mb-1" style="color:var(--text);">Start Tracking</h3>
                    <p style="color:var(--text-muted); font-size:0.875rem;">
                        Log your first period to get predictions and insights
                    </p>
                    <a href="{{ route('user.menstruation.create') }}" class="btn btn-primary btn-sm mt-2">
                        <i class="bi bi-plus-lg me-1"></i> Log Period
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Period History -->
    <div class="card history-card">
        <div class="card-header d-flex justify-content-between align-items-center" style="border:none;">
            <h5 class="mb-0 fw-800" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--color-text);">Period History</h5>
            <span class="badge history-count">
                {{ $records->count() }} records
            </span>
        </div>
        <div class="card-body p-0">
            @if($records->count() > 0)
                <div class="p-3 d-flex flex-column gap-3">
                    @foreach($records as $record)
                        <div class="period-record-card d-flex align-items-center gap-3">
                            <div class="text-center" style="min-width:48px;">
                                <div class="period-date-num">
                                    {{ $record->period_start_date->format('d') }}
                                </div>
                                <div class="period-date-mon">
                                    {{ $record->period_start_date->format('M') }}
                                </div>
                            </div>

                            <div class="d-flex flex-column" style="height:48px; align-items:center; justify-content:center;">
                                <div style="width:2px; flex:1; background:linear-gradient(to bottom, var(--color-secondary-soft), var(--color-secondary));"></div>
                                <div class="period-dot"></div>
                                <div style="width:2px; flex:1; background:linear-gradient(to bottom, var(--color-secondary), transparent);"></div>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="period-range">
                                            {{ $record->period_start_date->format('M d') }}
                                            @if($record->period_end_date)
                                                – {{ $record->period_end_date->format('M d, Y') }}
                                            @else
                                                – <span style="color:var(--color-warning-text);">Ongoing</span>
                                            @endif
                                        </strong>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @php
                                            $bleedDays = $record->period_length
                                                ?? (($record->period_start_date && $record->period_end_date)
                                                    ? $record->period_start_date->diffInDays($record->period_end_date, false) + 1
                                                    : null);
                                        @endphp
                                        <span class="badge period-days-badge">
                                            {{ $bleedDays ? $bleedDays . ' day' . ($bleedDays != 1 ? 's' : '') : 'Ongoing' }}
                                        </span>
                                        <form action="{{ route('user.menstruation.destroy', $record->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="period-del-btn" onclick="return confirm('Archive this record? It will be kept in your history archive.')" title="Archive record">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center">
                    <div style="width:80px; height:80px; border-radius:50%; background:var(--primary-subtle); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                        <i class="bi bi-calendar-heart" style="font-size:2rem; color:var(--primary-light);"></i>
                    </div>
                    <h5 style="color:var(--text);">No periods logged yet</h5>
                    <p style="color:var(--text-muted); font-size:0.875rem;">Start tracking to get personalized insights & predictions</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
