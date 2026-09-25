@extends('user.layout')

@section('title', 'Cycle Calendar - ReproCare')

@push('styles')
<style>
    /* Back button override */
    .back-nav-btn {
        display:inline-flex;
        align-items:center;
        gap:0.4rem;
        font-size:0.83rem;
        font-weight:600;
        color:var(--color-text-muted);
        text-decoration:none;
        padding:0.45rem 0.9rem;
        border:1px solid var(--color-gray-200);
        border-radius:var(--rounded-pill);
        background:var(--color-surface);
        transition:all 0.2s;
        margin-bottom:1rem;
        width:fit-content;
    }
    .back-nav-btn:hover {
        color:var(--color-primary-text);
        border-color:var(--color-primary);
        background:var(--color-primary-subtle);
        transform:translateX(-2px);
    }
    /* ── Calendar Wrapper ── */
    .cal-page-wrap {
        max-width:900px;
        margin:0 auto;
    }

    /* ── Cycle Stat Pills ── */
    .cycle-stat-grid {
        display:grid;
        grid-template-columns:repeat(4, 1fr);
        gap:0.75rem;
        margin-bottom:1.5rem;
    }
    .cycle-stat-pill {
        background:var(--bg-card);
        border:none;
        border-radius:16px;
        padding:1rem;
        text-align:center;
        transition:all 0.25s ease;
    }
    .cycle-stat-pill:hover {
        border-color:var(--primary);
        transform:translateY(-2px);
        box-shadow:var(--shadow-sm);
    }
    .cycle-stat-pill .stat-val {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:1.6rem;
        font-weight:800;
        line-height:1;
        margin-bottom:0.3rem;
    }
    .cycle-stat-pill .stat-lbl {
        font-size:0.72rem;
        color:var(--text-muted);
        font-weight:600;
        text-transform:uppercase;
        letter-spacing:0.6px;
    }

    /* ── Calendar Card ── */
    .calendar-card {
        background:var(--bg-card);
        border:none;
        border-radius:20px;
        overflow:hidden;
        box-shadow:var(--shadow-sm);
        margin-bottom:1.5rem;
        transition:background 0.4s ease;
    }

    /* ── Calendar Header ── */
    .cal-header {
        display:flex;
        align-items:center;
        justify-content:space-between;
        padding:1.1rem 1.4rem;
        border-bottom:1px solid var(--border);
    }
    .cal-month-title {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:1.25rem;
        font-weight:800;
        color:var(--text);
        letter-spacing:-0.5px;
    }
    .calendar-nav-btn {
        width:40px; height:40px;
        display:flex; align-items:center; justify-content:center;
        background:var(--bg-card2);
        border:1px solid var(--border);
        border-radius:10px;
        color:var(--text-muted);
        text-decoration:none;
        font-size:0.875rem;
        flex-shrink:0;
        transition:all 0.2s ease;
    }
    .calendar-nav-btn:hover {
        background:var(--primary-subtle);
        border-color:var(--primary);
        color:var(--primary-light);
        transform:scale(1.05);
    }

    /* View toggle: Month | Week */
    .view-toggle {
        display:flex;
        background:var(--bg-card2);
        border:1px solid var(--border);
        border-radius:10px;
        overflow:hidden;
        font-size:0.78rem;
        font-weight:600;
    }
    .view-toggle button {
        border:none;
        background:transparent;
        color:var(--text-muted);
        padding:0.35rem 0.8rem;
        cursor:pointer;
        transition:all 0.2s ease;
    }
    .view-toggle button.active {
        background:var(--primary);
        color:var(--color-on-solid);
        box-shadow:0 2px 8px var(--primary-glow);
    }

    /* ── Calendar Grid ── */
    .calendar-body { padding:1.1rem 1.4rem 1.4rem; }

    .calendar-grid {
        display:grid;
        grid-template-columns:repeat(7, 1fr);
        gap:6px;
    }

    .cal-day-header {
        text-align:center;
        font-size:0.68rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:1.2px;
        color:var(--text-muted);
        padding:0.5rem 0;
    }

    /* ── Day Cells ── */
    .cal-day {
        aspect-ratio:1;
        border-radius:12px;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        cursor:pointer;
        transition:all 0.15s ease;
        position:relative;
        font-size:0.9rem;
        font-weight:500;
        color:var(--text);
        background:transparent;
        border:1px solid transparent;
        gap:1px;
    }
    .cal-day:hover:not(.other-month) {
        background:var(--primary-subtle);
        border-color:var(--border);
        transform:scale(1.08);
    }

    /* Other month */
    .cal-day.other-month {
        color:var(--text-muted);
        opacity:0.35;
        cursor:default;
    }
    .cal-day.other-month:hover { background:transparent; transform:none; }

    /* Today */
    .cal-day.today {
        background:var(--primary);
        color:var(--color-on-solid);
        font-weight:800;
        box-shadow:0 4px 20px var(--primary-glow), 0 0 0 2px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent);
        border-color:transparent;
    }
    .cal-day.today:hover { background:var(--primary-light); }

    /* Period day (actual) */
    .cal-day.period-day {
        background:linear-gradient(135deg, var(--color-danger-text), var(--color-danger-text));
        color:var(--color-on-solid);
        font-weight:700;
        border-color:transparent;
        box-shadow:0 3px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 45%, transparent);
    }

    /* Predicted period */
    .cal-day.predicted-period {
        background:color-mix(in srgb, var(--color-danger) 14%, transparent);
        border:2px dashed color-mix(in srgb, var(--color-danger) 50%, transparent);
        color:var(--color-danger-text);
    }

    /* Ovulation day */
    .cal-day.ovulation-day {
        background:linear-gradient(135deg, var(--secondary), var(--color-secondary-text));
        color:var(--color-on-solid);
        font-weight:700;
        border-color:transparent;
        box-shadow:0 3px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 45%, transparent);
    }

    /* Fertile window */
    .cal-day.fertile-day {
        background:color-mix(in srgb, var(--color-success) 18%, transparent);
        border:1px solid color-mix(in srgb, var(--color-success) 40%, transparent);
        color:var(--color-success-text);
    }

    /* Event Dots (below the date number) */
    .day-dots {
        display:flex;
        gap:2px;
        position:absolute;
        bottom:3px;
        left:50%;
        transform:translateX(-50%);
    }
    .day-dot-pin {
        width:4px; height:4px;
        border-radius:50%;
        flex-shrink:0;
    }

    /* ── Month slide animation ── */
    @keyframes slideInRight {
        from { opacity:0; transform:translateX(24px); }
        to   { opacity:1; transform:translateX(0); }
    }
    @keyframes slideInLeft {
        from { opacity:0; transform:translateX(-24px); }
        to   { opacity:1; transform:translateX(0); }
    }
    .cal-slide-in  { animation:slideInRight 0.3s ease both; }
    .cal-slide-out { animation:slideInLeft  0.3s ease both; }

    /* ── Legend ── */
    .legend-bar {
        display:flex;
        flex-wrap:wrap;
        gap:0.5rem 1rem;
        padding:0.85rem 1.4rem;
        border-top:1px solid var(--border);
    }
    .legend-pill {
        display:inline-flex;
        align-items:center;
        gap:0.4rem;
        font-size:0.78rem;
        color:var(--text-muted);
        font-weight:500;
    }
    .legend-dot {
        width:10px; height:10px;
        border-radius:50%;
        flex-shrink:0;
    }

    /* ── Prediction Cards ── */
    .pred-card {
        background:var(--bg-card2);
        border:none;
        border-radius:14px;
        padding:1rem;
        transition:all 0.2s ease;
        height:100%;
    }
    .pred-card:hover {
        border-color:var(--primary);
        transform:translateY(-2px);
        box-shadow:var(--shadow-sm);
    }

    /* ── Fertility Status Card ── */
    .fertility-card {
        background:linear-gradient(135deg, color-mix(in srgb, var(--color-secondary) 8%, transparent), color-mix(in srgb, var(--color-primary) 6%, transparent));
        border:1px solid color-mix(in srgb, var(--color-secondary) 25%, transparent);
        border-radius:18px;
        padding:1.25rem;
        margin-bottom:1.5rem;
        transition:background 0.4s ease;
    }

    /* ── Phase Guide Strip ── */
    .phase-strip {
        border-radius:12px;
        padding:1rem;
        height:100%;
    }
    .phase-icon {
        width:34px; height:34px;
        border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        font-size:0.9rem;
        flex-shrink:0;
        margin-bottom:0.75rem;
    }

    @media (max-width: 768px) {
        .cycle-stat-grid { grid-template-columns:1fr 1fr; }
    }
    @media (max-width: 480px) {
        .cal-day { font-size:0.8rem; border-radius:8px; }
        .calendar-body { padding:0.75rem; }
        .cal-header { padding:0.85rem 1rem; }
    }
</style>
@endpush

@section('user-content')

<div class="cal-page-wrap">

    {{-- Back to Menstrual Cycle --}}
    <a href="{{ route('user.menstruation.index') }}"
       class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 mb-3"
       style="border-radius:10px;font-weight:600;padding:0.45rem 1rem;">
        <i class="bi bi-arrow-left"></i> Back to Menstrual Cycle
    </a>

    {{-- ── Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4 fade-in-card">
        <div>
            <h1 class="page-title">Cycle Calendar
            </h1>
            <p class="page-subtitle">Visualize your cycle phases, predictions, and fertile window</p>
        </div>
        <a href="{{ route('user.menstruation.create') }}" class="btn btn-primary">
            <i class="bi bi-droplet-fill me-1"></i> Log Period
        </a>
    </div>

    {{-- ── Cycle Stats ── --}}
    @if($averageCycle && $averagePeriod)
    <div class="cycle-stat-grid fade-in-card">
        <div class="cycle-stat-pill">
            <div class="stat-val" style="color:var(--primary-light);">{{ $averageCycle }}</div>
            <div class="stat-lbl">Avg Cycle (days)</div>
        </div>
        <div class="cycle-stat-pill">
            <div class="stat-val" style="color:var(--color-danger-text);">{{ $averagePeriod }}</div>
            <div class="stat-lbl">Avg Period (days)</div>
        </div>
        <div class="cycle-stat-pill">
            <div class="stat-val" style="color:var(--color-success-text);">{{ $predictionDetail['spread_days'] }}</div>
            <div class="stat-lbl">Cycle variation (days)</div>
        </div>
        <div class="cycle-stat-pill">
            <div class="stat-val" style="color:var(--color-secondary-text);">{{ ucfirst($predictionDetail['confidence']) }}</div>
            <div class="stat-lbl">Prediction confidence</div>
        </div>
    </div>
    @endif

    {{-- ── Calendar Card ── --}}
    <div class="calendar-card fade-in-card">

        {{-- Calendar Header --}}
        <div class="cal-header">
            <a href="{{ route('user.menstruation.calendar', ['date' => $currentDate->copy()->subMonth()->format('Y-m-d')]) }}"
               class="calendar-nav-btn" aria-label="Previous month">
                <i class="bi bi-chevron-left"></i>
            </a>

            <div class="d-flex align-items-center gap-3">
                <span class="cal-month-title">{{ $currentDate->format('F Y') }}</span>
                <div class="view-toggle" id="viewToggle">
                    <button class="active" onclick="switchView('month', this)">Month</button>
                    <button onclick="switchView('week', this)">Week</button>
                </div>
            </div>

            <a href="{{ route('user.menstruation.calendar', ['date' => $currentDate->copy()->addMonth()->format('Y-m-d')]) }}"
               class="calendar-nav-btn" aria-label="Next month">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        {{-- Month View --}}
        <div class="calendar-body" id="monthView">
            {{-- Day Headers --}}
            <div class="calendar-grid mb-2">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                    <div class="cal-day-header">{{ $d }}</div>
                @endforeach
            </div>

            {{-- Days Grid --}}
            <div class="calendar-grid cal-slide-in" id="calGrid">
                @php $weekDays = array_chunk($calendarDays, 7); @endphp
                @foreach($weekDays as $week)
                    @foreach($week as $day)
                        @php
                            $cls = [];
                            if (!$day['is_current_month']) $cls[] = 'other-month';
                            if ($day['is_today'])         $cls[] = 'today';
                            if ($day['period'])           $cls[] = 'period-day';
                            elseif (isset($day['predicted_period']) && $day['predicted_period']) $cls[] = 'predicted-period';
                            elseif ($day['ovulation'])    $cls[] = 'ovulation-day';
                            elseif ($day['fertile'])      $cls[] = 'fertile-day';
                        @endphp
                        <div class="cal-day {{ implode(' ', $cls) }}"
                             title="{{ $day['date']->format('F j, Y') }}">
                            {{ $day['date']->format('j') }}
                            {{-- Event dots --}}
                            <div class="day-dots">
                                @if($day['period'])
                                    <div class="day-dot-pin" style="background:color-mix(in srgb, var(--color-surface) 80%, transparent);"></div>
                                @elseif(isset($day['predicted_period']) && $day['predicted_period'])
                                    <div class="day-dot-pin" style="background:var(--color-danger-soft);"></div>
                                @elseif($day['ovulation'])
                                    <div class="day-dot-pin" style="background:color-mix(in srgb, var(--color-surface) 80%, transparent);"></div>
                                @elseif($day['fertile'])
                                    <div class="day-dot-pin" style="background:var(--color-success);"></div>
                                @elseif($day['is_today'])
                                    <div class="day-dot-pin" style="background:color-mix(in srgb, var(--color-surface) 60%, transparent);"></div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- Week View (cosmetic toggle) --}}
        <div class="calendar-body" id="weekView" style="display:none;">
            <div class="calendar-grid mb-2">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                    <div class="cal-day-header">{{ $d }}</div>
                @endforeach
            </div>
            <div class="calendar-grid" id="weekGrid">
                {{-- Populated by JS --}}
            </div>
        </div>

        {{-- Legend --}}
        <div class="legend-bar">
            <span class="legend-pill">
                <span class="legend-dot" style="background:var(--color-danger-text);"></span> Period
            </span>
            <span class="legend-pill">
                <span class="legend-dot" style="background:color-mix(in srgb, var(--color-danger) 40%, transparent);border:1px dashed var(--color-danger);"></span> Predicted
            </span>
            <span class="legend-pill">
                <span class="legend-dot" style="background:var(--color-success);"></span> Fertile Window
            </span>
            <span class="legend-pill">
                <span class="legend-dot" style="background:var(--secondary);"></span> Ovulation
            </span>
            <span class="legend-pill">
                <span class="legend-dot" style="background:var(--primary);"></span> Today
            </span>
        </div>
    </div>

    {{-- ── No-data alert ── --}}
    @if(count($predictions) === 0)
    <div class="alert alert-info fade-in-card d-flex align-items-center gap-2">
        <i class="bi bi-info-circle-fill flex-shrink-0" style="font-size:1.1rem;"></i>
        <span>
            <strong>Getting Started —</strong>
            Log your periods to build a history. Predictions have low confidence until at least three cycle intervals are available.
        </span>
        <a href="{{ route('user.menstruation.create') }}" class="btn btn-sm btn-primary ms-auto">Log Now</a>
    </div>
    @endif

        </div>
    </div>

    {{-- ── Upcoming Predictions ── --}}
    @if(count($predictions) > 0)
    <div class="card fade-in-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">Upcoming Predictions
            </h5>
            <span style="font-size:0.78rem;color:var(--text-muted);">Next {{ count($predictions) }} cycle(s)</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($predictions as $index => $prediction)
                <div class="col-md-4">
                    <div class="pred-card">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span style="background:var(--primary-subtle);color:var(--primary-light);border:1px solid var(--border-glass);border-radius:8px;padding:0.22em 0.7em;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;">
                                Cycle {{ $index + 1 }}
                            </span>
                        </div>
                        <div class="mb-2">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:0.25rem;">Estimated period start window</div>
                            <div style="font-size:0.875rem;color:var(--color-danger-text);font-weight:600;">
                                <i class="bi bi-droplet-fill me-1"></i>
                                {{ $prediction['period_start_earliest']->format('M d') }} – {{ $prediction['period_start_latest']->format('M d, Y') }}
                            </div>
                        </div>
                        @if(isset($prediction['ovulation']))
                        <div class="mb-2">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:0.25rem;">Ovulation</div>
                            <div style="font-size:0.875rem;color:var(--color-secondary-text);font-weight:600;">
                                <i class="bi bi-egg-fill me-1"></i>{{ $prediction['ovulation']->format('M d, Y') }}
                            </div>
                        </div>
                        @endif
                        <p class="small text-muted mt-2 mb-0">{{ ucfirst($prediction['confidence']) }} confidence. {{ $prediction['note'] }}</p>
                        @if(isset($prediction['fertile_start']) && isset($prediction['fertile_end']))
                        <div>
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--text-muted);margin-bottom:0.25rem;">Fertile Window</div>
                            <div style="font-size:0.875rem;color:var(--color-success-text);font-weight:600;">
                                <i class="bi bi-heart me-1"></i>
                                {{ $prediction['fertile_start']->format('M d') }} – {{ $prediction['fertile_end']->format('M d, Y') }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ── Cycle Phase Guide ── --}}
    <p class="small text-muted">Calendar dates are estimates, not confirmed ovulation or a reliable way to prevent pregnancy. Shaded predictions show possible period days, not continuous bleeding.</p>
    <div class="card fade-in-card">
        <div class="card-header">
            <h5 class="mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">Understanding Your Cycle
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="phase-strip" style="background:color-mix(in srgb, var(--color-danger) 7%, transparent);border:1px solid color-mix(in srgb, var(--color-danger) 18%, transparent);">
                        <div class="phase-icon" style="background:color-mix(in srgb, var(--color-danger) 16%, transparent);">
                            <i class="bi bi-droplet-fill" style="color:var(--color-danger-text);"></i>
                        </div>
                        <strong style="color:var(--color-danger-text);display:block;margin-bottom:0.3rem;">Menstrual</strong>
                        <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.4rem;">Days 1–5</div>
                        <p style="font-size:0.8rem;color:var(--text-muted);margin:0;line-height:1.5;">Uterine lining sheds. Rest, warmth &amp; iron-rich foods help.</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="phase-strip" style="background:color-mix(in srgb, var(--color-primary) 7%, transparent);border:1px solid color-mix(in srgb, var(--color-primary) 18%, transparent);">
                        <div class="phase-icon" style="background:color-mix(in srgb, var(--color-primary) 16%, transparent);">
                            <i class="bi bi-stars" style="color:var(--color-primary-text);"></i>
                        </div>
                        <strong style="color:var(--color-primary-text);display:block;margin-bottom:0.3rem;">Follicular</strong>
                        <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.4rem;">Days 6–13</div>
                        <p style="font-size:0.8rem;color:var(--text-muted);margin:0;line-height:1.5;">Estrogen rises. Energy increases. Great for new projects!</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="phase-strip" style="background:color-mix(in srgb, var(--color-secondary) 7%, transparent);border:1px solid color-mix(in srgb, var(--color-secondary) 18%, transparent);">
                        <div class="phase-icon" style="background:color-mix(in srgb, var(--color-secondary) 16%, transparent);">
                            <i class="bi bi-egg-fill" style="color:var(--color-secondary-text);"></i>
                        </div>
                        <strong style="color:var(--color-secondary-text);display:block;margin-bottom:0.3rem;">Ovulation</strong>
                        <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.4rem;">Day 14</div>
                        <p style="font-size:0.8rem;color:var(--text-muted);margin:0;line-height:1.5;">Egg released. Peak fertility. LH surge detectable via test.</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="phase-strip" style="background:color-mix(in srgb, var(--color-warning) 7%, transparent);border:1px solid color-mix(in srgb, var(--color-warning) 18%, transparent);">
                        <div class="phase-icon" style="background:color-mix(in srgb, var(--color-warning) 16%, transparent);">
                            <i class="bi bi-moon-stars" style="color:var(--color-peach-text);"></i>
                        </div>
                        <strong style="color:var(--color-peach-text);display:block;margin-bottom:0.3rem;">Luteal</strong>
                        <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:0.4rem;">Days 15–28</div>
                        <p style="font-size:0.8rem;color:var(--text-muted);margin:0;line-height:1.5;">Progesterone rises. PMS may occur. Body prepares for next period.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /.cal-page-wrap --}}

@push('scripts')
<script>
// Month slide animation on load
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.getElementById('calGrid');
    if (grid) {
        grid.classList.add('cal-slide-in');
        grid.addEventListener('animationend', () => grid.classList.remove('cal-slide-in'));
    }
});

// Week/Month view toggle (cosmetic)
function switchView(view, btn) {
    document.querySelectorAll('.view-toggle button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const monthView = document.getElementById('monthView');
    const weekView  = document.getElementById('weekView');
    if (view === 'week') {
        monthView.style.display = 'none';
        weekView.style.display  = 'block';
        renderWeekView();
    } else {
        weekView.style.display  = 'none';
        monthView.style.display = 'block';
    }
}

function renderWeekView() {
    // Show days of the current week only
    const today    = new Date();
    const dayOfWk  = today.getDay();
    const sunday   = new Date(today);
    sunday.setDate(today.getDate() - dayOfWk);
    const grid = document.getElementById('weekGrid');
    grid.innerHTML = '';
    for (let i = 0; i < 7; i++) {
        const d = new Date(sunday);
        d.setDate(sunday.getDate() + i);
        const isToday = d.toDateString() === today.toDateString();
        const cel = document.createElement('div');
        cel.className = 'cal-day' + (isToday ? ' today' : '');
        cel.style.aspectRatio = '1';
        cel.textContent = d.getDate();
        grid.appendChild(cel);
    }
}
</script>
@endpush

@endsection
