@extends('user.layout')

@section('title', 'My Health & Care Records - ReproCare')

@push('styles')
<style>
/* === HEALTH & CARE RECORDS PAGE === */
.health-page {
    padding:0;
}

/* ── Hero Banner ── */
.care-hero {
    background:var(--color-surface); background-color:var(--color-surface);
    border:none;
    border-radius:24px;
    padding:2.25rem 2.5rem;
    position:relative;
    overflow:hidden;
    margin-bottom:1.5rem;
    color:var(--color-text);
    box-shadow:var(--wp-shadow-sm);
}
[data-theme="light"] .care-hero {
    background:var(--color-surface); background-color:var(--color-surface);
    color:var(--color-text);
}
.care-hero::before {
    content:'';
    position:absolute;
    width:340px;
    height:340px;
    border-radius:50%;
    background:radial-gradient(circle, color-mix(in srgb, var(--color-secondary-soft) 90%, transparent) 0%, transparent 70%);
    top:-120px;
    right:-80px;
    pointer-events:none;
}
.care-hero::after {
    content:'';
    position:absolute;
    width:260px;
    height:260px;
    border-radius:50%;
    background:radial-gradient(circle, color-mix(in srgb, var(--color-peach-soft) 70%, transparent) 0%, transparent 70%);
    bottom:-120px;
    left:30%;
    pointer-events:none;
}
.care-hero-grid {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:2rem;
    flex-wrap:wrap;
    position:relative;
    z-index:1;
}
.care-hero-left {
    flex:1;
    min-width:280px;
}
.care-hero-title {
    font-family:'Plus Jakarta Sans', sans-serif;
    font-size:1.85rem;
    font-weight:800;
    line-height:1.2;
    margin-bottom:0.5rem;
    color:var(--color-text);
    letter-spacing:-0.02em;
}
[data-theme="light"] .care-hero-title {
    color:var(--color-text);
}
.care-hero-sub {
    font-size:0.9rem;
    color:var(--color-text-muted);
    margin-bottom:1.25rem;
    line-height:1.5;
}
.care-pill-row {
    display:flex;
    flex-wrap:wrap;
    gap:0.5rem;
}
.care-pill {
    display:inline-flex;
    align-items:center;
    gap:0.4rem;
    padding:0.38rem 0.9rem;
    border-radius:999px;
    font-size:0.76rem;
    font-weight:800;
    border:none;
}
[data-theme="light"] .care-pill {
    border:none;
}
.care-pill-mint { background:var(--color-success-soft); background-color:var(--color-success-soft); color:var(--color-success-text); }
.care-pill-lav { background:var(--color-primary-soft); background-color:var(--color-primary-soft); color:var(--color-primary-text); }
.care-pill-peach { background:var(--color-peach-soft); background-color:var(--color-peach-soft); color:var(--color-warning-text); }

.care-hero-actions {
    display:flex;
    align-items:center;
    gap:0.75rem;
    flex-wrap:wrap;
}
.btn-hero-action {
    display:inline-flex;
    align-items:center;
    gap:0.5rem;
    padding:0.62rem 1.3rem;
    border-radius:999px;
    font-size:0.85rem;
    font-weight:800;
    text-decoration:none;
    transition:all 0.2s ease;
    border:none;
    cursor:pointer;
    white-space:nowrap;
}
.btn-hero-action.btn-log {
    background:var(--color-surface-strong);
    color:var(--color-on-solid);
    box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent);
}
.btn-hero-action.btn-log:hover {
    background:var(--color-surface-strong);
    color:var(--color-on-solid);
    transform:translateY(-1px);
}
.btn-hero-action.btn-support {
    background:var(--color-surface-soft);
    color:var(--color-text);
    border:none;
}
[data-theme="light"] .btn-hero-action.btn-support {
    background:var(--color-surface-soft);
    color:var(--color-text);
    border:none;
}
.btn-hero-action.btn-support:hover {
    background:var(--color-border);
    color:var(--color-text);
}
[data-theme="light"] .btn-hero-action.btn-support:hover {
    border:none;
    color:var(--color-text);
}

/* ── Latest Vitals Summary Grid ── */
.vitals-summary-grid {
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:1rem;
    margin-bottom:1.75rem;
}
@media (min-width: 768px) {
    .vitals-summary-grid {
        grid-template-columns:repeat(4, 1fr);
    }
}
.vital-stat-card {
    border:none;
    border-radius:20px;
    padding:1.4rem 1.1rem;
    display:flex;
    flex-direction:column;
    align-items:center;
    text-align:center;
    transition:all 0.22s ease;
    box-shadow:var(--wp-shadow-sm);
    position:relative;
    overflow:hidden;
    height:100%;
}
.vital-stat-card.rose { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); }
.vital-stat-card.peach { background:var(--color-peach-soft); background-color:var(--color-peach-soft); }
.vital-stat-card.mint { background:var(--color-success-soft); background-color:var(--color-success-soft); }
.vital-stat-card.lavender { background:var(--color-primary-soft); background-color:var(--color-primary-soft); }
.vital-stat-card:hover {
    border:none;
    transform:translateY(-3px);
    box-shadow:var(--wp-shadow-md);
}
.vital-icon-box {
    width:40px;
    height:40px;
    border-radius:50%;
    background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1.1rem;
    margin-bottom:0.75rem;
}

.vital-val {
    font-family:'Plus Jakarta Sans', sans-serif;
    font-size:1.35rem;
    font-weight:800;
    color:var(--color-text);
    line-height:1.2;
    margin-bottom:0.2rem;
    letter-spacing:-0.01em;
}
.vital-unit {
    font-size:0.75rem;
    font-weight:500;
    color:var(--color-text-muted);
}
.vital-lbl {
    font-size:0.68rem;
    color:var(--color-text-muted);
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:0.6px;
    margin-top:0.15rem;
}
.vital-badge-status {
    margin-top:0.55rem;
    font-size:0.64rem;
    font-weight:800;
    padding:0.22rem 0.65rem;
    border-radius:999px;
    text-transform:uppercase;
    letter-spacing:0.4px;
    border:none;
}
.status-normal { background:var(--color-surface); color:var(--color-success-text); }
.status-monitor { background:var(--color-surface); color:var(--color-warning-text); }
.status-elevated { background:var(--color-surface); color:var(--color-danger-text); }

/* ── Section Header & Filter Tabs ── */
.records-head-row {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:1rem;
    margin-bottom:1.25rem;
    flex-wrap:wrap;
}
.records-title {
    font-family:'Plus Jakarta Sans', sans-serif;
    font-size:1.25rem;
    font-weight:800;
    color:var(--color-text);
    margin:0;
    display:flex;
    align-items:center;
    gap:0.5rem;
}
.records-count { background:var(--color-primary-soft); background-color:var(--color-primary-soft); color:var(--color-primary-text); border:none; font-size:0.78rem; padding:0.4rem 0.9rem; border-radius:999px; font-weight:800; }
.filter-tabs {
    display:flex;
    gap:0.4rem;
    background:var(--color-surface);
    padding:0.25rem;
    border-radius:12px;
    border:none;
}
.filter-tab {
    padding:0.35rem 0.85rem;
    font-size:0.78rem;
    font-weight:600;
    border-radius:8px;
    color:var(--color-text-muted);
    text-decoration:none;
    border:none;
    background:transparent;
    cursor:pointer;
    transition:all 0.2s;
}
.filter-tab.active, .filter-tab:hover {
    background:var(--color-primary-subtle);
    color:var(--color-primary-text);
}

/* ── Record Detail Cards ── */
.record-card {
    background:var(--color-surface); background-color:var(--color-surface);
    border:none;
    border-radius:22px;
    padding:1.5rem;
    margin-bottom:1.25rem;
    transition:all 0.25s ease;
    box-shadow:var(--wp-shadow-sm);
    position:relative;
}
.record-card:hover {
    border:none;
    box-shadow:var(--wp-shadow-md);
    transform:translateY(-2px);
}
.record-card.risk-low    { border:none; }
.record-card.risk-medium { border:none; }
.record-card.risk-high   { border:none; }

.record-top {
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:1rem;
    margin-bottom:1.25rem;
    flex-wrap:wrap;
}
.record-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.1rem; font-weight:800; color:var(--color-text); margin:0; letter-spacing:-0.01em; }
.record-date-badge {
    display:flex;
    align-items:center;
    gap:0.5rem;
    font-size:0.82rem;
    color:var(--color-text-muted);
    font-weight:600;
}
.record-date-badge i {
    color:var(--color-secondary-text);
}

.risk-pill {
    display:inline-flex;
    align-items:center;
    gap:0.35rem;
    padding:0.32rem 0.85rem;
    border-radius:999px;
    font-size:0.7rem;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:0.5px;
    border:none;
}
.risk-pill.low    { background:var(--color-success-soft); color:var(--color-success-text); }
.risk-pill.medium { background:var(--color-warning-soft); color:var(--color-warning-text); }
.risk-pill.high   { background:var(--color-danger-soft); color:var(--color-danger-text); }

/* Vitals in Record Card */
.record-vitals-strip {
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    gap:0.85rem;
    background:var(--color-bg); background-color:var(--color-bg);
    border:none;
    border-radius:16px;
    padding:1rem 1.25rem;
    margin-bottom:1.25rem;
}
@media (min-width: 640px) {
    .record-vitals-strip {
        grid-template-columns:repeat(4, 1fr);
    }
}
.strip-item {
    display:flex;
    flex-direction:column;
}
.strip-lbl {
    font-size:0.68rem;
    font-weight:800;
    color:var(--color-text-muted);
    text-transform:uppercase;
    letter-spacing:0.6px;
    margin-bottom:0.3rem;
    display:flex;
    align-items:center;
    gap:0.4rem;
}
.strip-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.strip-dot.rose { background:var(--color-secondary-text); }
.strip-dot.peach { background:var(--color-peach); }
.strip-dot.mint { background:var(--color-success-text); }
.strip-dot.lav { background:var(--color-primary); }
.strip-val {
    font-family:'Plus Jakarta Sans', sans-serif;
    font-size:1.15rem;
    font-weight:800;
    color:var(--color-text);
    letter-spacing:-0.01em;
}
.strip-val small {
    font-size:0.72rem;
    font-weight:500;
    color:var(--color-text-muted);
}
.strip-val.na { color:var(--color-text); font-weight:700; }

/* Provider & Clinical Notes */
.clinician-strip {
    display:flex;
    align-items:center;
    gap:0.85rem;
    padding:0.9rem 1.1rem;
    background:var(--color-bg); background-color:var(--color-bg);
    border:none;
    border-radius:14px;
    margin-bottom:1rem;
}
.clinician-avatar {
    width:40px;
    height:40px;
    border-radius:50%;
    background:var(--color-surface-strong); background-color:var(--color-surface-strong);
    color:var(--color-on-solid);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1rem;
    flex-shrink:0;
}
.clinician-info h6 {
    margin:0;
    font-size:0.88rem;
    font-weight:800;
    color:var(--color-text);
}
.clinician-info span {
    font-size:0.76rem;
    color:var(--color-text-muted);
}

.notes-box {
    background:var(--color-bg); background-color:var(--color-bg);
    border:none;
    border-radius:14px;
    padding:0.95rem 1.15rem;
    margin-top:0.75rem;
}
.notes-box.notes-rec { background:var(--color-primary-soft); background-color:var(--color-primary-soft); }
.notes-box p {
    margin:0;
    font-size:0.88rem;
    line-height:1.6;
    color:var(--color-text);
    font-weight:500;
}
.notes-label {
    font-size:0.68rem;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:0.6px;
    color:var(--color-text-muted);
    margin-bottom:0.3rem;
    display:flex;
    align-items:center;
    gap:0.35rem;
}
.notes-box.notes-rec .notes-label { color:var(--color-primary-text); }

/* Empty State */
.care-empty {
    background:linear-gradient(135deg, var(--color-primary-subtle) 0%, color-mix(in srgb, var(--color-info-text) 6%, transparent) 100%);
    border:2px dashed var(--color-lavender);
    border-radius:24px;
    padding:3.5rem 2rem;
    text-align:center;
}
.care-empty-icon {
    font-size:3.5rem;
    color:var(--color-primary-text);
    opacity:0.6;
    margin-bottom:1rem;
}
</style>
@endpush

@section('user-content')
<div class="health-page py-2">

    @php
        $latestRecord = $healthRecords->first();
        $totalRecords = $healthRecords->total();
        
        // Compute averages/latest
        $latestBp = $latestRecord->bp ?? '—';
        $latestWeight = $latestRecord->weight ? $latestRecord->weight . ' kg' : '—';
        $latestHeartRate = $latestRecord->heart_rate ? $latestRecord->heart_rate . ' bpm' : '—';
        $latestTemp = $latestRecord->temperature ? $latestRecord->temperature . ' °C' : '—';
    @endphp

    {{-- ── Hero Section (Styled like Pregnancy Page) ── --}}
    <div class="care-hero fade-in-card">
        <div class="care-hero-grid">
            <div class="care-hero-left">
                <h1 class="care-hero-title">
                    Maternal Health & Care Records
                </h1>
                <p class="care-hero-sub">
                    Your official clinical assessments, vital signs monitoring, and healthcare provider evaluations in San Carlos City.
                </p>
                <div class="care-pill-row">
                    <span class="care-pill care-pill-mint">
                        <i class="bi bi-shield-check"></i> Health Records (validated ones show Verified)
                    </span>
                    <span class="care-pill care-pill-lav">
                        <i class="bi bi-calendar2-check"></i> {{ $totalRecords }} Total Assessment{{ $totalRecords === 1 ? '' : 's' }}
                    </span>
                    @if($latestRecord)
                        <span class="care-pill care-pill-peach">
                            <i class="bi bi-clock-history"></i> Latest: {{ $latestRecord->created_at->format('M d, Y') }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="care-hero-actions">
                <a href="{{ route('user.health-records.create') }}" class="btn-hero-action btn-log">
                    <i class="bi bi-plus-circle-fill"></i> Log Vitals Check
                </a>
                <button type="button" class="btn-hero-action btn-support" data-bs-toggle="modal" data-bs-target="#careEmergencyModal">
                    <i class="bi bi-telephone-plus-fill"></i> Health Center Help
                </button>
            </div>
        </div>
    </div>

    {{-- ── Latest Vitals Summary Strip ── --}}
    <div class="vitals-summary-grid fade-in-card">
        {{-- Blood Pressure --}}
        <div class="vital-stat-card rose">
            <div class="vital-icon-box">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <div class="vital-val">{{ $latestBp }}</div>
            <div class="vital-unit">mmHg</div>
            <div class="vital-lbl">Blood Pressure</div>
            <span class="vital-badge-status status-normal">Target: &lt;120/80</span>
        </div>

        {{-- Weight --}}
        <div class="vital-stat-card peach">
            <div class="vital-icon-box">
                <i class="bi bi-speedometer2"></i>
            </div>
            <div class="vital-val">{{ $latestWeight }}</div>
            <div class="vital-unit">kilograms</div>
            <div class="vital-lbl">Body Weight</div>
            <span class="vital-badge-status status-normal">Active Tracking</span>
        </div>

        {{-- Heart Rate --}}
        <div class="vital-stat-card mint">
            <div class="vital-icon-box">
                <i class="bi bi-activity"></i>
            </div>
            <div class="vital-val">{{ $latestHeartRate }}</div>
            <div class="vital-unit">beats / min</div>
            <div class="vital-lbl">Heart Rate</div>
            <span class="vital-badge-status status-normal">Normal 60-100</span>
        </div>

        {{-- Temperature --}}
        <div class="vital-stat-card lavender">
            <div class="vital-icon-box">
                <i class="bi bi-thermometer-half"></i>
            </div>
            <div class="vital-val">{{ $latestTemp }}</div>
            <div class="vital-unit">celsius</div>
            <div class="vital-lbl">Body Temp</div>
            <span class="vital-badge-status status-normal">Normal 36.5-37.5</span>
        </div>
    </div>

    {{-- ── Detailed Assessments List ── --}}
    <div class="records-head-row">
        <h2 class="records-title">Clinical Assessments History
        </h2>
        @if($totalRecords > 0)
            <span class="badge records-count">
                Showing {{ $healthRecords->count() }} of {{ $totalRecords }} records
            </span>
        @endif
    </div>

    @if($healthRecords->count() > 0)
        @foreach($healthRecords as $record)
            @php
                $risk = ucfirst(strtolower($record->risk_level ?? 'Low'));
                $riskClass = match($risk) {
                    'High' => 'high',
                    'Medium' => 'medium',
                    default => 'low',
                };
            @endphp
            <div class="record-card risk-{{ $riskClass }} fade-in-card">
                <div class="record-top">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="record-title">
                                Health Assessment
                            </h4>
                            <span class="risk-pill {{ $riskClass }}">
                                <i class="bi bi-shield-fill"></i> {{ $risk }} Risk
                            </span>
                            @if(in_array($record->workflow_status, ['accepted_by_midwife', 'validated', 'approved'], true))
                                <span class="risk-pill low"><i class="bi bi-patch-check-fill"></i> Verified</span>
                            @else
                                <span class="risk-pill medium"><i class="bi bi-hourglass-split"></i> Pending review</span>
                            @endif
                        </div>
                        <div class="record-date-badge">
                            <i class="bi bi-calendar3"></i>
                            Recorded on {{ $record->created_at->format('F d, Y \a\t g:i A') }}
                            <span style="opacity:0.6;">· ({{ $record->created_at->diffForHumans() }})</span>
                        </div>
                    </div>
                </div>

                {{-- Vitals Strip --}}
                <div class="record-vitals-strip">
                    <div class="strip-item">
                        <span class="strip-lbl"><span class="strip-dot rose"></span> Blood Pressure</span>
                        @if($record->bp)
                            <span class="strip-val">{{ $record->bp }} <small>mmHg</small></span>
                        @else
                            <span class="strip-val na">N/A</span>
                        @endif
                    </div>
                    <div class="strip-item">
                        <span class="strip-lbl"><span class="strip-dot peach"></span> Weight</span>
                        @if($record->weight)
                            <span class="strip-val">{{ $record->weight }} <small>kg</small></span>
                        @else
                            <span class="strip-val na">N/A</span>
                        @endif
                    </div>
                    <div class="strip-item">
                        <span class="strip-lbl"><span class="strip-dot mint"></span> Heart Rate</span>
                        @if($record->heart_rate)
                            <span class="strip-val">{{ $record->heart_rate }} <small>bpm</small></span>
                        @else
                            <span class="strip-val na">N/A</span>
                        @endif
                    </div>
                    <div class="strip-item">
                        <span class="strip-lbl"><span class="strip-dot lav"></span> Temperature</span>
                        @if($record->temperature)
                            <span class="strip-val">{{ $record->temperature }} <small>°C</small></span>
                        @else
                            <span class="strip-val na">N/A</span>
                        @endif
                    </div>
                </div>

                {{-- Clinician / Recorder info --}}
                <div class="clinician-strip">
                    <div class="clinician-avatar">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="clinician-info">
                        <h6>
                            {{ optional($record->recordedBy)->name ?? 'Healthcare Provider' }}
                        </h6>
                        <span>
                            Role: {{ ucfirst($record->recorded_by_role ?? (optional($record->recordedBy)->role ?? 'Health Worker')) }}
                            · San Carlos Health Unit
                        </span>
                    </div>
                </div>

                {{-- Clinical Notes if present --}}
                @if(!empty($record->notes))
                    <div class="notes-box">
                        <div class="notes-label">
                            <i class="bi bi-chat-left-quote-fill"></i> Provider Clinical Notes
                        </div>
                        <p>{{ $record->notes }}</p>
                    </div>
                @endif

                {{-- Recommendations if present --}}
                @if(!empty($record->recommendations))
                    <div class="notes-box notes-rec mt-2">
                        <div class="notes-label">
                            <i class="bi bi-check2-circle"></i> Provider Recommendations
                        </div>
                        <p>{{ $record->recommendations }}</p>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="mt-4">
            {{ $healthRecords->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="care-empty fade-in-card">
            <div class="care-empty-icon">
                <i class="bi bi-clipboard2-pulse"></i>
            </div>
            <h3 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;color:var(--color-text);margin-bottom:0.5rem;">
                No Clinical Health Records Yet
            </h3>
            <p style="color:var(--color-text-muted);max-width:480px;margin:0 auto 1.5rem;font-size:0.92rem;line-height:1.6;">
                Your health records will automatically appear here once recorded during your prenatal checkups with your Barangay Health Worker or Midwife.
            </p>
            <a href="{{ route('user.health-records.create') }}" class="btn-hero-action btn-log">
                <i class="bi bi-plus-circle-fill"></i> Record First Health Check
            </a>
        </div>
    @endif

</div>
@endsection
