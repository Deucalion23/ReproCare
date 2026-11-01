@extends('midwife.layout')

@section('title', 'Risk Alerts - Midwife Portal | ReproCare')

@push('styles')
{{-- Same shell as the women directory and the family planning list: one card, tight
     rows, pill tabs and a fixed-width row action. --}}
@include('includes.women-directory-styles')
<style>
    .main-content .ra-icon {
        width:26px; height:26px; border-radius:8px; flex:0 0 auto;
        display:inline-flex; align-items:center; justify-content:center; font-size:0.8rem;
    }
    .ra-icon-danger { background:var(--color-danger-soft); color:var(--color-danger-text); }
    .ra-icon-warning { background:var(--color-warning-soft); color:var(--color-warning-text); }
    .ra-icon-primary { background:var(--color-primary-soft); color:var(--color-primary-text); }
    .ra-icon-muted { background:var(--color-surface-soft); color:var(--color-text-muted); }
    .main-content .ra-title { display:flex; align-items:center; gap:0.5rem; margin:0; min-width:0; font-size:0.84rem !important; font-weight:800; }
    .main-content .ra-title .ra-label { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    /* The framework stretches every .btn-outline-primary to full width on phones
       (portal-theme, max-width:768px), which would push these labels off the header. */
    body .main-content .wd-wrap .ra-open-link { width:auto !important; flex:0 0 auto !important; padding:0.3rem 0.7rem !important; font-size:0.74rem !important; }
    body .main-content .wd-wrap .ra-footer { padding:0.3rem 0.6rem !important; border-top:1px solid var(--color-border) !important; background:transparent !important; }
    .main-content .wd-wrap .ra-empty { padding:0.85rem 0.6rem !important; font-size:0.78rem; }
    @media (max-width: 767.98px) {
        /* app.blade.php turns every .main-content card-header into a column at ≤768px.
           A queue header only ever holds the label and one link, which still fit
           side by side here, so the second line is pure dead space. */
        body .main-content .wd-wrap .card-header { flex-direction:row !important; align-items:center !important; }
    }
    @media (max-width: 575.98px) {
        /* Five pills wrap to three rows and a wrapped header costs two lines, which
           together pushed the first row ~150px down the phone screen. The strip
           scrolls sideways instead; counts stay on the label because they are the
           whole point of the tab. */
        .main-content .wd-tabs { flex-wrap:nowrap; overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:none; padding-bottom:0.15rem; }
        .main-content .wd-tabs::-webkit-scrollbar { display:none; }
        .main-content .wd-tabs > li { flex:0 0 auto; }
        /* The header has 288px to work with on a 390px phone and the label + link
           need 291, so the pair drops to two lines. Trimming the icon and the label
           font buys the 16px back and keeps the queue header on one row. */
        body .main-content .wd-wrap .card-header { gap:0.35rem !important; }
        .main-content .wd-wrap .ra-title { font-size:0.72rem !important; gap:0.35rem; }
        .main-content .wd-wrap .ra-icon { width:20px; height:20px; font-size:0.65rem; }
    }
</style>
@endpush

@section('midwife-content')
@php
    // One source for label, icon and count so the tabs and the card headers
    // can never drift apart.
    $queues = [
        'all' => ['label' => 'All queues', 'icon' => 'bi-inboxes-fill', 'count' => array_sum($counts)],
        'pregnancies' => ['label' => 'High-Risk Pregnancies', 'icon' => 'bi-heart-pulse-fill', 'count' => $counts['pregnancies']],
        'referrals' => ['label' => 'Urgent Referrals', 'icon' => 'bi-exclamation-octagon-fill', 'count' => $counts['referrals']],
        'missed' => ['label' => 'Missed Checkups', 'icon' => 'bi-calendar-x-fill', 'count' => $counts['missed']],
        'records' => ['label' => 'Awaiting Validation', 'icon' => 'bi-clipboard2-check-fill', 'count' => $counts['records']],
    ];
    $queueUrl = fn (string $key) => route('midwife.risk-alerts', array_filter([
        'queue' => $key === 'all' ? null : $key,
        'search' => $search !== '' ? $search : null,
    ]));
@endphp

<div class="py-2">
    <div class="page-hero fade-in-card mb-3">
        <div style="position:relative;z-index:1;">
            <div class="page-hero-title">Risk Alerts</div>
            <p class="page-hero-subtitle mb-0">Clinical review queue — pick a queue to list only that one.</p>
        </div>
    </div>

    {{-- Queue filter --}}
    <ul class="wd-tabs mb-3 fade-in-card" role="tablist" aria-label="Risk alert queues">
        @foreach ($queues as $key => $meta)
            <li>
                <a class="wd-tab {{ $queue === $key ? 'active' : '' }}" href="{{ $queueUrl($key) }}"
                   @if ($queue === $key) aria-current="true" @endif>
                    <i class="bi {{ $meta['icon'] }}"></i> {{ $meta['label'] }} ({{ $meta['count'] }})
                </a>
            </li>
        @endforeach
    </ul>

    @include('includes.list-toolbar', [
        'action' => route('midwife.risk-alerts'),
        'tabs' => [],
        'active' => $queue,
        'search' => $search,
        'placeholder' => 'Patient name, email or contact...',
        'label' => 'Search risk alerts',
        'preserve' => ['queue' => $queue === 'all' ? null : $queue],
    ])

    @if ($highRiskPregnancies)
        <div class="card fade-in-card wd-wrap mb-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="ra-title">
                    <span class="ra-icon ra-icon-danger"><i class="bi {{ $queues['pregnancies']['icon'] }}"></i></span>
                    <span class="ra-label">{{ $queues['pregnancies']['label'] }} ({{ $counts['pregnancies'] }})</span>
                </h2>
                <a href="{{ route('midwife.pregnancies.index', ['status' => 'high-risk']) }}" class="btn btn-sm btn-outline-primary ra-open-link">
                    <i class="bi bi-list-ul me-1"></i> Open list
                </a>
            </div>
            <div class="card-body">
                @if ($highRiskPregnancies->count())
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>LMP</th>
                                    <th>EDD</th>
                                    <th>Risk</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($highRiskPregnancies as $pregnancy)
                                    <tr>
                                        <td><span class="wd-name">{{ $pregnancy->patient_name ?? optional($pregnancy->woman)->name ?? '—' }}</span></td>
                                        <td>{{ $pregnancy->lmp?->format('M d, Y') ?? '—' }}</td>
                                        <td>{{ $pregnancy->edd?->format('M d, Y') ?? '—' }}</td>
                                        <td><span class="wd-pill wd-pill-pregnant">High risk</span></td>
                                        <td class="text-center">
                                            <a href="{{ route('midwife.pregnancy-history', $pregnancy->id) }}"
                                               class="btn btn-sm btn-outline-primary wd-action-btn">
                                                <i class="bi bi-eye-fill"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($highRiskPregnancies->hasPages())
                        <div class="card-footer ra-footer">
                            {{ $highRiskPregnancies->appends(request()->except('pregnancies_page'))->links() }}
                        </div>
                    @endif
                @else
                    <p class="text-muted ra-empty text-center mb-0">No high-risk pregnancies right now.</p>
                @endif
            </div>
        </div>
    @endif

    @if ($urgentReferrals)
        <div class="card fade-in-card wd-wrap mb-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="ra-title">
                    <span class="ra-icon ra-icon-warning"><i class="bi {{ $queues['referrals']['icon'] }}"></i></span>
                    <span class="ra-label">{{ $queues['referrals']['label'] }} ({{ $counts['referrals'] }})</span>
                </h2>
                <a href="{{ route('midwife.referrals.index') }}" class="btn btn-sm btn-outline-primary ra-open-link">
                    <i class="bi bi-list-ul me-1"></i> Open referrals
                </a>
            </div>
            <div class="card-body">
                @if ($urgentReferrals->count())
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Urgency</th>
                                    <th>Status</th>
                                    <th>From BHW</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($urgentReferrals as $referral)
                                    <tr>
                                        <td><span class="wd-name">{{ $referral->patient_name ?? '—' }}</span></td>
                                        <td>
                                            <span class="wd-pill {{ $referral->urgency === 'emergency' ? 'wd-pill-pregnant' : 'wd-pill-unregistered' }}">
                                                {{ ucfirst($referral->urgency) }}
                                            </span>
                                        </td>
                                        <td><span class="wd-pill wd-pill-muted">{{ ucfirst($referral->status) }}</span></td>
                                        <td>{{ $referral->referredByBhw?->name ?? '—' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('midwife.referrals.show', $referral->id) }}"
                                               class="btn btn-sm btn-outline-primary wd-action-btn">
                                                <i class="bi bi-eye-fill"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($urgentReferrals->hasPages())
                        <div class="card-footer ra-footer">
                            {{ $urgentReferrals->appends(request()->except('referrals_page'))->links() }}
                        </div>
                    @endif
                @else
                    <p class="text-muted ra-empty text-center mb-0">No urgent referrals pending.</p>
                @endif
            </div>
        </div>
    @endif

    @if ($missedCheckups)
        <div class="card fade-in-card wd-wrap mb-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="ra-title">
                    <span class="ra-icon ra-icon-muted"><i class="bi {{ $queues['missed']['icon'] }}"></i></span>
                    <span class="ra-label">{{ $queues['missed']['label'] }} ({{ $counts['missed'] }})</span>
                </h2>
                <a href="{{ route('midwife.checkups.index', ['status' => 'missed']) }}" class="btn btn-sm btn-outline-primary ra-open-link">
                    <i class="bi bi-list-ul me-1"></i> Open checkups
                </a>
            </div>
            <div class="card-body">
                @if ($missedCheckups->count())
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Scheduled</th>
                                    <th>Purpose</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($missedCheckups as $checkup)
                                    <tr>
                                        <td><span class="wd-name">{{ $checkup->patient_name ?? '—' }}</span></td>
                                        <td>{{ optional($checkup->scheduled_date)->format('M d, Y') ?? '—' }}</td>
                                        <td><span class="wd-sub">{{ $checkup->purpose ?? '—' }}</span></td>
                                        <td class="text-center">
                                            <a href="{{ route('midwife.checkups.edit', $checkup->id) }}"
                                               class="btn btn-sm btn-outline-primary wd-action-btn">
                                                <i class="bi bi-calendar-plus"></i> Reschedule
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($missedCheckups->hasPages())
                        <div class="card-footer ra-footer">
                            {{ $missedCheckups->appends(request()->except('missed_page'))->links() }}
                        </div>
                    @endif
                @else
                    <p class="text-muted ra-empty text-center mb-0">No missed checkups.</p>
                @endif
            </div>
        </div>
    @endif

    @if ($pendingRecords)
        <div class="card fade-in-card wd-wrap mb-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="ra-title">
                    <span class="ra-icon ra-icon-primary"><i class="bi {{ $queues['records']['icon'] }}"></i></span>
                    <span class="ra-label">{{ $queues['records']['label'] }} ({{ $counts['records'] }})</span>
                </h2>
                <a href="{{ route('midwife.health-records.index') }}" class="btn btn-sm btn-outline-primary ra-open-link">
                    <i class="bi bi-list-ul me-1"></i> Open records
                </a>
            </div>
            <div class="card-body">
                @if ($pendingRecords->count())
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Recorded By</th>
                                    <th>Stage</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingRecords as $record)
                                    <tr>
                                        <td><span class="wd-name">{{ $record->patient_name ?? '—' }}</span></td>
                                        <td>{{ $record->recordedBy?->name ?? '—' }}</td>
                                        <td><span class="wd-pill wd-pill-muted">{{ str_replace('_', ' ', $record->workflow_status) }}</span></td>
                                        <td class="text-center">
                                            <a href="{{ route('midwife.health-records.show', $record->id) }}"
                                               class="btn btn-sm btn-outline-primary wd-action-btn">
                                                <i class="bi bi-check2-circle"></i> Validate
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($pendingRecords->hasPages())
                        <div class="card-footer ra-footer">
                            {{ $pendingRecords->appends(request()->except('records_page'))->links() }}
                        </div>
                    @endif
                @else
                    <p class="text-muted ra-empty text-center mb-0">Validation queue is clear.</p>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
