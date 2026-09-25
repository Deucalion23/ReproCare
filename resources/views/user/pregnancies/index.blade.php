@extends('user.layout')

@section('title', 'My Pregnancy Journey - ReproCare')

@push('styles')
<style>
/* === PREGNANCY JOURNEY PAGE === */
.pregnancy-page { padding:0; }
.women-shell { background-color:var(--color-surface) !important; }

.pregnancy-page .page-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.6rem; font-weight:800; color:var(--color-text); letter-spacing:-0.02em; margin-bottom:0.15rem; }
.pregnancy-page .page-subtitle { font-size:0.9rem; color:var(--color-text-muted); margin:0; }
.preg-top-card { background:var(--color-surface); border:1px solid var(--color-border); border-radius:20px; padding:1.25rem 1.5rem; box-shadow:var(--wp-shadow-sm); margin-bottom:1.5rem; }
.preg-active-badge { display:inline-flex; align-items:center; gap:6px; background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:var(--color-secondary-text); border:none; font-size:0.82rem; padding:0.5em 1.05em; border-radius:999px; font-weight:800; }
.preg-active-badge i { color:var(--color-danger); }

.preg-hero {
    background:var(--color-surface); background-color:var(--color-surface);
    border:none; border-radius:24px; padding:2.25rem 2.5rem; position:relative; overflow:hidden; margin-bottom:1.5rem;
    box-shadow:var(--wp-shadow-sm);
}
.preg-hero::before { content:''; position:absolute; width:340px; height:340px; border-radius:50%; background:radial-gradient(circle, color-mix(in srgb, var(--color-secondary-soft) 90%, transparent) 0%, transparent 70%); top:-120px; right:-80px; pointer-events:none; }
.preg-hero::after { content:''; position:absolute; width:260px; height:260px; border-radius:50%; background:radial-gradient(circle, color-mix(in srgb, var(--color-peach-soft) 70%, transparent) 0%, transparent 70%); bottom:-120px; left:30%; pointer-events:none; }
[data-theme="light"] .preg-hero { background:var(--color-surface); background-color:var(--color-surface); }
.week-ring-wrap { position:relative; width:170px; height:170px; flex-shrink:0; z-index:1; }
.week-ring-svg { width:170px; height:170px; transform:rotate(-90deg); }
.week-ring-svg .ring-bg { fill:none; stroke:var(--color-secondary-soft); stroke-width:12; }
[data-theme="light"] .week-ring-svg .ring-bg { stroke:var(--color-secondary-soft); }
.week-ring-svg .ring-fill { fill:none; stroke:var(--color-secondary); stroke-width:12; stroke-linecap:round; }
.week-ring-inner { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.week-num { font-family:'Plus Jakarta Sans',sans-serif; font-size:2.6rem; font-weight:900; line-height:1; color:var(--color-text); letter-spacing:-0.03em; }
[data-theme="light"] .week-num { color:var(--color-text); }
.week-label { font-size:0.68rem; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:var(--color-text-muted); margin-top:4px; }
[data-theme="light"] .week-label { color:var(--color-text-muted); }

.preg-hero-body { position:relative; z-index:1; }
.preg-hero-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.9rem; font-weight:800; color:var(--color-text); line-height:1.15; margin-bottom:0.4rem; letter-spacing:-0.02em; }
[data-theme="light"] .preg-hero-title { color:var(--color-text); }
.preg-hero-sub { font-size:0.92rem; color:var(--color-text-muted); margin-bottom:1.15rem; }
.preg-hero-sub strong { color:var(--color-secondary-text); }

.preg-pill-row { display:flex; flex-wrap:wrap; gap:0.55rem; }
.preg-pill { display:inline-flex; align-items:center; gap:0.4rem; padding:0.38rem 0.9rem; border-radius:999px; font-size:0.78rem; font-weight:700; border:none; }
[data-theme="light"] .preg-pill { border:none; }
.preg-pill-trim { background:var(--color-secondary-soft); color:var(--color-secondary-text); }
.preg-pill-grav { background:var(--color-primary-soft); color:var(--color-primary-text); }
.preg-pill-para { background:var(--color-success-soft); color:var(--color-success-text); }
.preg-pill-risk-low { background:var(--color-success-soft); color:var(--color-success-text); }
.preg-pill-risk-medium { background:var(--color-warning-soft); color:var(--color-warning-text); }
.preg-pill-risk-high { background:var(--color-danger-soft); color:var(--color-danger-text); }

.trimester-track { display:flex; gap:0.85rem; margin-bottom:1.25rem; position:relative; }
.trimester-card { flex:1; background:var(--color-surface); border:none; border-radius:18px; padding:1.35rem 0.9rem 1.15rem; text-align:center; transition:all 0.25s ease; position:relative; z-index:1; box-shadow:var(--wp-shadow-sm); overflow:hidden; }
.trimester-card.active { background:var(--color-surface); box-shadow:0 14px 32px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 18%, transparent), var(--wp-shadow-sm); transform:translateY(-2px); }
.trimester-card.completed { background:var(--color-surface); }
.trimester-card.upcoming { background:var(--color-surface); box-shadow:var(--wp-shadow-sm); }
.trimester-num { font-family:'Plus Jakarta Sans',sans-serif; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:1.1px; margin-bottom:0.35rem; }
.trimester-card.active .trimester-num { color:var(--color-secondary-text); }
.trimester-card.completed .trimester-num { color:var(--color-success-text); }
.trimester-card.upcoming .trimester-num { color:var(--color-text-muted); }
.trimester-weeks { font-size:0.82rem; font-weight:600; color:var(--color-text-muted); margin-bottom:0.55rem; }
.trimester-card.active .trimester-weeks { color:var(--color-secondary-text); font-weight:700; }
.trimester-card.completed .trimester-weeks { color:var(--color-text); }
.trimester-status { display:inline-flex; align-items:center; gap:4px; font-size:0.62rem; font-weight:800; text-transform:uppercase; letter-spacing:0.6px; padding:0.22rem 0.65rem; border-radius:9999px; }
.trimester-card.completed .trimester-status { background:var(--color-success-soft); color:var(--color-success-text); }
.trimester-card.active .trimester-status { background:var(--color-secondary-soft); color:var(--color-secondary-text); }
.trimester-card.upcoming .trimester-status { background:var(--color-surface-soft); color:var(--color-text-muted); }
.trimester-badge { position:absolute; top:0.65rem; right:0.65rem; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.65rem; font-weight:800; }
.trimester-card.active .trimester-badge { background:var(--color-secondary-text); color:var(--color-on-solid); box-shadow:0 0 0 4px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent); }
.trimester-card.completed .trimester-badge { background:var(--color-success-text); color:var(--color-on-solid); box-shadow:0 0 0 4px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent); }
.trimester-card.upcoming .trimester-badge { background:var(--color-surface); color:var(--color-text-muted); border:1px solid var(--color-border); }
@media (max-width:576px) { .trimester-track { flex-direction:column; } }

.preg-info-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:1rem; margin-bottom:1.25rem; }
@media (min-width:768px) { .preg-info-grid { grid-template-columns:repeat(4,1fr); } }
.preg-info-card { border:none; border-radius:20px; padding:1.4rem 1rem; text-align:center; transition:all 0.22s ease; box-shadow:var(--wp-shadow-sm); height:100%; }
.preg-info-card:hover { transform:translateY(-3px); box-shadow:var(--wp-shadow-md); }
.preg-info-card.rose { background:var(--color-secondary-soft); }
.preg-info-card.peach { background:var(--color-peach-soft); }
.preg-info-card.mint { background:var(--color-success-soft); }
.preg-info-card.lavender { background:var(--color-primary-soft); }
.preg-info-icon { width:40px; height:40px; border-radius:50%; background:var(--color-surface-strong); color:var(--color-on-solid); display:flex; align-items:center; justify-content:center; font-size:1.1rem; margin:0 auto 0.7rem; }
.preg-info-val { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.08rem; font-weight:800; color:var(--color-text); margin-bottom:0.2rem; line-height:1.2; letter-spacing:-0.01em; }
.preg-info-lbl { font-size:0.68rem; color:var(--color-text-muted); font-weight:700; text-transform:uppercase; letter-spacing:0.6px; }

.baby-week-card { background:var(--color-surface); background-color:var(--color-surface); border:none; border-radius:22px; padding:1.5rem 1.6rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:1.4rem; flex-wrap:wrap; box-shadow:var(--wp-shadow-sm); }
.baby-emoji-wrap { width:68px; height:68px; border-radius:20px; background:var(--color-surface); border:1px solid var(--color-border); display:flex; align-items:center; justify-content:center; font-size:2.4rem; line-height:1; flex-shrink:0; }
.baby-details { flex:1; min-width:200px; }
.baby-eyebrow { font-size:0.68rem; font-weight:800; text-transform:uppercase; letter-spacing:0.8px; color:var(--color-secondary-text); margin-bottom:0.25rem; }
.baby-details h4 { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.15rem; font-weight:800; color:var(--color-text); margin-bottom:0.3rem; letter-spacing:-0.01em; }
.baby-details p { font-size:0.88rem; color:var(--color-text-muted); margin:0; line-height:1.6; }
.baby-progress-block { flex-shrink:0; padding-left:1.4rem; border-left:1px solid var(--color-border); text-align:center; }
.baby-progress-lbl { font-size:0.68rem; font-weight:800; text-transform:uppercase; letter-spacing:0.6px; color:var(--color-text-muted); margin-bottom:0.35rem; }
.baby-progress-val { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:800; color:var(--color-text); }

.preg-tip-card { background:var(--color-surface); background-color:var(--color-surface); border:none; border-radius:16px; padding:1.15rem 1.3rem; margin-bottom:1.5rem; display:flex; align-items:flex-start; gap:0.9rem; box-shadow:var(--wp-shadow-sm); }
.preg-tip-icon { width:38px; height:38px; border-radius:12px; background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:1px solid var(--color-border); color:var(--color-text); display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
.preg-tip-title { font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.6px; color:var(--color-text-muted); margin-bottom:0.25rem; }
.preg-tip-text { font-size:0.9rem; color:var(--color-text); margin:0; line-height:1.6; font-weight:500; }
.preg-history-card { background:var(--color-surface); border:none; border-radius:20px; padding:1.25rem; margin-bottom:0.75rem; transition:all 0.2s; }
.preg-history-card:hover { border-color:var(--color-secondary-soft); box-shadow:var(--shadow-sm); }

.preg-empty { background:linear-gradient(135deg, var(--color-secondary-soft) 0%, var(--color-secondary-soft) 100%); border:2px dashed var(--color-secondary-soft); border-radius:24px; padding:3.5rem 2rem; text-align:center; }
</style>
@endpush

@section('user-content')
<div class="pregnancy-page py-2">

    @php
        $activePreg = $pregnancies->firstWhere('is_active', true);
        $pastPregs = $pregnancies->filter(fn($p) => !$p->is_active);
        $weekData = [
            1=>['🌱','Tiny Seed','Your pregnancy begins. The fertilized egg is implanting.'],
            4=>['🫘','Poppy Seed','The embryo is the size of a poppy seed. Heart begins forming.'],
            6=>['🫛','Sweet Pea',"Baby's heartbeat may be visible on ultrasound."],
            8=>['🍇','Grape','All major organs are forming. Fingers and toes are developing.'],
            10=>['🍓','Strawberry','Baby can make small movements and has tiny fingernails.'],
            12=>['🍋','Lime','End of first trimester! Baby\'s reflexes are developing.'],
            14=>['🍑','Peach','Second trimester starts! You may start showing a baby bump.'],
            16=>['🥑','Avocado','Baby can hear your voice and make facial expressions.'],
            18=>['🍠','Sweet Potato','You may start feeling baby\'s movements (quickening).'],
            20=>['🍌','Banana','Halfway there! Baby is about 10 inches long.'],
            22=>['🌽','Corn','Baby\'s sense of touch is developing rapidly.'],
            24=>['🌽','Ear of Corn','Baby can respond to sounds from outside the womb.'],
            26=>['🥦','Head of Lettuce','Baby opens eyes for the first time and can see light.'],
            28=>['🍆','Chinese Cabbage','Third trimester! Baby\'s brain is developing rapidly.'],
            30=>['🥥','Coconut','Baby is gaining fat and will put on weight quickly now.'],
            32=>['🎃','Squash','Baby\'s bones are hardening, except for skull bones.'],
            34=>['🍈','Cantaloupe',"Baby's lungs are nearly fully developed."],
            36=>['🥬','Romaine Lettuce','Baby is considered early term. Starting to drop lower.'],
            38=>['🫐','Mini Watermelon','Baby is ready! Full-term pregnancy reached.'],
            40=>['🍉','Watermelon','Baby is due any day now. Get ready to meet your little one!'],
        ];
        $currentWeekNum = 0; $weekEmoji = '🌱'; $weekSizeName = 'Tiny Seed'; $weekDescription = 'Your pregnancy journey begins.';
        $progressPct = 0; $daysUntilEDD = null; $trimester = 1;
        if ($activePreg && $activePreg->lmp) {
            $currentWeekNum = min(40, (int) floor($activePreg->lmp->diffInDays(now()) / 7));
            $progressPct = min(100, ($currentWeekNum / 40) * 100);
            $trimester = $currentWeekNum <= 13 ? 1 : ($currentWeekNum <= 26 ? 2 : 3);
            $closestWeek = 1;
            foreach (array_keys($weekData) as $w) { if ($currentWeekNum >= $w) $closestWeek = $w; }
            [$weekEmoji, $weekSizeName, $weekDescription] = $weekData[$closestWeek];
            if ($activePreg->edd) $daysUntilEDD = max(0, (int) now()->diffInDays($activePreg->edd, false));
        }
        $tips = [
            1 => ["Take folic acid daily to support baby's neural development.", "Stay hydrated — aim for 8-10 glasses of water daily.", "Avoid raw/undercooked meat, unpasteurized dairy, and high-mercury fish."],
            2 => ["Start light prenatal exercises like walking or swimming.", "Schedule your anatomy scan ultrasound (18-22 weeks).", "Sleep on your left side to improve blood flow to baby."],
            3 => ["Pack your hospital bag — include essentials for you and baby.", "Practice breathing exercises and relaxation techniques.", "Monitor baby movements — you should feel 10 kicks in 2 hours."],
        ];
        $currentTip = $tips[$trimester][array_rand($tips[$trimester])];
        $trimesterWeeks = ['1'=>'Weeks 1–13','2'=>'Weeks 14–26','3'=>'Weeks 27–40'];
    @endphp

    <div class="preg-top-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="page-title">My Pregnancy Journey</h1>
                <p class="page-subtitle">Track your pregnancy week by week</p>
            </div>
            @if(!$activePreg)
                <a href="{{ route('user.pregnancies.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Start Pregnancy Record
                </a>
            @else
                <span class="preg-active-badge">
                    <i class="bi bi-heart-fill"></i> Active Pregnancy
                </span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($activePreg)
    <div class="preg-hero mb-4">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <div class="week-ring-wrap">
                <svg class="week-ring-svg" viewBox="0 0 180 180">
                    <circle class="ring-bg" cx="90" cy="90" r="80"/>
                    <circle class="ring-fill" cx="90" cy="90" r="80" id="weekRingFill"
                        stroke-dasharray="{{ 2 * 3.14159 * 80 }}"
                        stroke-dashoffset="{{ (2 * 3.14159 * 80) * (1 - $progressPct/100) }}"/>
                </svg>
                <div class="week-ring-inner">
                    <div class="week-num">{{ $currentWeekNum }}</div>
                    <div class="week-label">of 40 wks</div>
                </div>
            </div>
            <div class="flex-grow-1 preg-hero-body">
                <div class="preg-hero-title">
                    @if($daysUntilEDD !== null && $daysUntilEDD > 0) {{ $daysUntilEDD }} days to go!
                    @elseif($daysUntilEDD === 0) Your due date is today! 🎉
                    @else Week {{ $currentWeekNum }} of your journey @endif
                </div>
                <div class="preg-hero-sub">
                    @if($activePreg->edd) Estimated due date: <strong>{{ $activePreg->edd->format('F j, Y') }}</strong> @endif
                </div>
                <div class="preg-pill-row">
                    <span class="preg-pill preg-pill-trim"><i class="bi bi-calendar3"></i> Trimester {{ $trimester }}</span>
                    @if($activePreg->gravida) <span class="preg-pill preg-pill-grav"><i class="bi bi-person-heart"></i> Gravida {{ $activePreg->gravida }}</span> @endif
                    @if($activePreg->para !== null) <span class="preg-pill preg-pill-para"><i class="bi bi-star"></i> Para {{ $activePreg->para }}</span> @endif
                    @if($activePreg->risk_level)
                        @php $riskCls = strtolower($activePreg->risk_level) === 'high' ? 'preg-pill-risk-high' : (strtolower($activePreg->risk_level) === 'low' ? 'preg-pill-risk-low' : 'preg-pill-risk-medium'); @endphp
                        <span class="preg-pill {{ $riskCls }}"><i class="bi bi-shield-check"></i> {{ $activePreg->risk_level }} Risk</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="trimester-track mb-4">
        @for($t = 1; $t <= 3; $t++)
        @php
            $state = $trimester > $t ? 'completed' : ($trimester === $t ? 'active' : 'upcoming');
            $statusLabel = $state === 'completed' ? 'Completed' : ($state === 'active' ? 'Current' : 'Upcoming');
        @endphp
        <div class="trimester-card {{ $state }}">
            @if($state === 'completed') <div class="trimester-badge"><i class="bi bi-check-lg"></i></div>
            @elseif($state === 'active') <div class="trimester-badge"><i class="bi bi-circle-fill" style="font-size:0.4rem;"></i></div>
            @else <div class="trimester-badge upcoming">{{ $t }}</div>
            @endif
            <div class="trimester-num">Trimester {{ $t }}</div>
            <div class="trimester-weeks">{{ $trimesterWeeks[(string)$t] }}</div>
            <span class="trimester-status">{{ $statusLabel }}</span>
        </div>
        @endfor
    </div>

    <div class="preg-info-grid mb-4">
        <div class="preg-info-card rose">
            <div class="preg-info-icon"><i class="bi bi-calendar-heart"></i></div>
            <div class="preg-info-val">{{ $activePreg->lmp ? $activePreg->lmp->format('M j, Y') : 'N/A' }}</div>
            <div class="preg-info-lbl">Last Menstrual Period</div>
        </div>
        <div class="preg-info-card peach">
            <div class="preg-info-icon"><i class="bi bi-calendar-check"></i></div>
            <div class="preg-info-val">{{ $activePreg->edd ? $activePreg->edd->format('M j, Y') : 'N/A' }}</div>
            <div class="preg-info-lbl">Due Date (EDD)</div>
        </div>
        <div class="preg-info-card mint">
            <div class="preg-info-icon"><i class="bi bi-clock"></i></div>
            <div class="preg-info-val">{{ $currentWeekNum }}w {{ (int)(($currentWeekNum * 7) % 7) }}d</div>
            <div class="preg-info-lbl">Age of Gestation</div>
        </div>
        <div class="preg-info-card lavender">
            <div class="preg-info-icon"><i class="bi bi-heart-pulse"></i></div>
            <div class="preg-info-val">{{ 40 - $currentWeekNum }} Weeks</div>
            <div class="preg-info-lbl">Until Due Date</div>
        </div>
    </div>

    <div class="baby-week-card">
        <div class="baby-emoji-wrap">{{ $weekEmoji }}</div>
        <div class="baby-details">
            <div class="baby-eyebrow">Week {{ $currentWeekNum }} &bull; Baby Development</div>
            <h4>About the size of a {{ $weekSizeName }}</h4>
            <p>{{ $weekDescription }}</p>
        </div>
        <div class="baby-progress-block">
            <div class="baby-progress-lbl">Progress</div>
            <div style="position:relative;width:60px;height:60px;margin:0 auto;">
                <svg viewBox="0 0 60 60" style="width:60px;height:60px;transform:rotate(-90deg);">
                    <circle cx="30" cy="30" r="24" fill="none" stroke="var(--color-secondary-soft)" stroke-width="6"/>
                    <circle cx="30" cy="30" r="24" fill="none" stroke="var(--color-secondary)" stroke-width="6" stroke-linecap="round"
                        stroke-dasharray="150.8" stroke-dashoffset="{{ 150.8 - (150.8 * $progressPct / 100) }}"/>
                </svg>
                <div class="baby-progress-val">{{ round($progressPct) }}%</div>
            </div>
        </div>
    </div>

    <div class="preg-tip-card">
        <div class="preg-tip-icon"><i class="bi bi-lightbulb-fill"></i></div>
        <div>
            <div class="preg-tip-title">Tip for Trimester {{ $trimester }}</div>
            <p class="preg-tip-text">{{ $currentTip }}</p>
        </div>
    </div>

    @else
    <div class="preg-empty mb-4">
        <div style="font-size:4rem;margin-bottom:1rem;">🤱</div>
        <h3 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;color:var(--color-text);margin-bottom:0.5rem;">No Active Pregnancy</h3>
        <p style="color:var(--color-text-muted);margin-bottom:1.5rem;max-width:400px;margin-left:auto;margin-right:auto;">Track your pregnancy journey week by week. Get personalized insights, baby development updates, and health tips.</p>
        <a href="{{ route('user.pregnancies.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Start Tracking Your Pregnancy</a>
    </div>
    @endif

    @if($pastPregs->count() > 0)
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-700">Pregnancy History</h5>
            <span class="badge" style="background:var(--color-primary-subtle);color:var(--color-primary-text);border:1px solid var(--color-lavender);font-size:0.75rem;padding:0.35em 0.85em;border-radius:999px;">{{ $pastPregs->count() }} records</span>
        </div>
        <div class="card-body p-3">
            @foreach($pastPregs as $p)
            <div class="preg-history-card">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <div style="font-weight:700;color:var(--color-text);margin-bottom:0.25rem;"><i class="bi bi-calendar me-1" style="color:var(--color-primary-light);"></i>LMP: {{ $p->lmp ? $p->lmp->format('M j, Y') : 'N/A' }}</div>
                        <div style="font-size:0.83rem;color:var(--color-text-muted);">EDD: {{ $p->edd ? $p->edd->format('M j, Y') : 'N/A' }} · Gravida {{ $p->gravida ?? '—' }}, Para {{ $p->para ?? '—' }}
                            @if($p->risk_level) · <span style="color:{{ $p->risk_level === 'High' ? 'var(--color-danger)' : ($p->risk_level === 'Medium' ? 'var(--color-warning)' : 'var(--color-success)') }};">{{ $p->risk_level }} Risk</span> @endif
                        </div>
                    </div>
                    <span class="badge" style="background:var(--color-gray-100);color:var(--color-text-muted);font-size:0.72rem;border-radius:999px;padding:0.3em 0.8em;">Completed</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
