@extends('midwife.layout')

@section('title', 'Target Client List - Midwife Portal | ReproCare')

@push('styles')
<style>
    .mctl-shell { display:grid; gap:1.25rem; }
    .mctl-header, .mctl-card {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:22px;
        box-shadow:var(--shadow-sm);
    }
    .mctl-header { padding:1.4rem 1.6rem; }
    .mctl-title { font-family:'Plus Jakarta Sans', sans-serif; font-size:1.45rem; font-weight:800; color:var(--text); margin:0; }
    .mctl-subtitle { color:var(--text-muted); margin:0.35rem 0 0; }
    .mctl-stats { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:1rem; }
    .mctl-stat {
        padding:1rem 1.1rem;
        border-radius:18px;
        border:1px solid var(--border);
        background:linear-gradient(135deg, var(--primary-subtle), color-mix(in srgb, var(--color-info) 6%, transparent));
    }
    .mctl-stat-label { font-size:0.78rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em; }
    .mctl-stat-value { font-size:1.55rem; font-weight:800; color:var(--text); }
    .mctl-card { padding:1.25rem; }
    .mctl-toolbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
    .mctl-tabs { display:flex; gap:0.6rem; flex-wrap:wrap; }
    .mctl-tab {
        padding:0.65rem 1rem;
        border-radius:999px;
        border:1px solid var(--border);
        color:var(--text-muted);
        text-decoration:none;
        font-weight:700;
        font-size:0.85rem;
        background:var(--bg-card2);
    }
    .mctl-tab.active { background:var(--primary-subtle); color:var(--primary-light); border-color:var(--border-glass); }
    .mctl-page-title {
        text-align:center;
        font-family:Georgia, 'Times New Roman', serif;
        font-size:1.05rem;
        font-weight:700;
        letter-spacing:0.02em;
        color:var(--text);
        margin-bottom:0.85rem;
        text-transform:uppercase;
    }
    .mctl-table-wrap { width:100%; max-width:100%; overflow-x:auto; border-radius:18px; border:1px solid var(--border); }
    .mctl-table { width:max-content; min-width:100%; border-collapse:collapse; background:var(--bg-card); }
    .mctl-table th, .mctl-table td {
        border:1px solid var(--border);
        padding:0.45rem 0.5rem;
        vertical-align:middle;
        color:var(--text);
        font-size:0.74rem;
        line-height:1.25;
    }
    .mctl-table thead th { background:var(--bg-card2); font-weight:800; text-align:center; }
    .mctl-table tbody td { color:var(--text-muted); }
    .mctl-table tbody tr:nth-child(even) { background:color-mix(in srgb, var(--color-surface) 2%, transparent); }
    .mctl-name { min-width:200px; color:var(--text) !important; font-weight:700; }
    .mctl-actions { 
        white-space:nowrap; 
        text-align:center;
        min-width:70px;
    }
    .mctl-table th:first-child,
    .mctl-table td:first-child {
        position:sticky;
        left:0;
        background:var(--bg-card);
        z-index:1;
        box-shadow:2px 0 5px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent);
    }
    .mctl-table th:first-child {
        z-index:2;
        background:var(--bg-card2);
    }
    .mctl-empty {
        padding:2rem;
        text-align:center;
        color:var(--text-muted);
        border:1px dashed var(--border);
        border-radius:18px;
    }
    .mctl-empty-row td {
        padding:1.25rem 1rem;
        text-align:center;
        color:var(--text-muted);
    }
    .mctl-empty-actions {
        display:flex;
        justify-content:center;
        gap:0.75rem;
        margin-top:1rem;
        flex-wrap:wrap;
    }
    .mctl-pill {
        display:inline-flex;
        padding:0.2rem 0.55rem;
        border-radius:999px;
        background:color-mix(in srgb, var(--color-info) 12%, transparent);
        color:var(--info);
        font-weight:700;
        font-size:0.72rem;
    }

    /* Compact table to fit screen without scrolling */
    .mctl-table-wrap {
        overflow-x:visible;
        width:100%;
    }
    /* On phones the zoomed table would be clipped with no way to scroll —
       restore full size + horizontal scroll instead. */
    @media (max-width: 768px) {
        .mctl-table-wrap {
            overflow-x:auto !important;
            zoom:1 !important;
            -moz-transform:none !important;
            transform:none !important;
            width:100% !important;
        }
    }
    .mctl-card:has(.mctl-table-wrap) {
        overflow:hidden;
    }
    /* Scale table to fit viewport */
    .mctl-table-wrap {
        zoom:0.75;
        -moz-transform:scale(0.75);
        -moz-transform-origin:top left;
    }
    @supports not (zoom: 0.75) {
        .mctl-table-wrap {
            transform:scale(0.75);
            transform-origin:top left;
            width:133.33%;
        }
    }
    .mctl-table {
        width:100%;
        font-size:0.7rem;
    }
    .mctl-table th, .mctl-table td {
        padding:0.3rem 0.35rem;
        white-space:normal;
        word-wrap:break-word;
        max-width:120px;
    }
    .mctl-table th {
        font-size:0.62rem;
        line-height:1.2;
    }
    .mctl-table td {
        font-size:0.65rem;
        line-height:1.3;
    }
    .mctl-name {
        min-width:100px;
        max-width:150px;
    }
    .mctl-actions {
        min-width:50px;
        padding:0.2rem !important;
    }
    .mctl-actions .btn {
        font-size:0.6rem;
        padding:0.2rem 0.4rem;
    }
    /* Scale down the entire table container */
    .mctl-card {
        transform-origin:top left;
    }
    @media (max-width: 1400px) {
        .mctl-table th, .mctl-table td {
            padding:0.25rem 0.3rem;
            font-size:0.6rem;
        }
        .mctl-table th {
            font-size:0.58rem;
        }
    }
    @media (max-width: 768px) {
        .mctl-stats { grid-template-columns:1fr; }
    }
</style>
@if(!empty($printMode))
<style>
    @page { size:landscape; margin:8mm; }
    html, body {
        background:var(--color-surface) !important;
        padding-top:0 !important;
        margin:0 !important;
    }
    .navbar, .sidebar, .topbar, .mctl-header, .mctl-toolbar, .pagination { display:none !important; }
    .layout-wrapper, .main-content, .mctl-shell, .mctl-card {
        display:block !important;
        margin:0 !important;
        padding:0 !important;
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;
    }
    .mctl-card {
        box-shadow:none !important;
        border:none !important;
        border-radius:0 !important;
        overflow:visible !important;
        background:var(--color-surface) !important;
    }
    .mctl-print-page {
        width:100%;
        break-after:page;
        page-break-after:always;
    }
    .mctl-print-page:last-of-type {
        break-after:auto;
        page-break-after:auto;
    }
    .mctl-table-wrap {
        zoom:1 !important;
        transform:none !important;
        width:100% !important;
        max-width:100% !important;
        overflow:visible !important;
        border:none !important;
        border-radius:0 !important;
    }
    .mctl-table {
        width:100% !important;
        min-width:0 !important;
        border-collapse:collapse;
        table-layout:auto;
    }
    .mctl-table th, .mctl-table td { 
        font-size:7.5px !important; 
        padding:3px 2px !important; 
        border:1px solid var(--color-border) !important;
        vertical-align:middle;
        color:var(--color-text-muted) !important;
        word-break:break-word;
        white-space:normal;
        max-width:none !important;
    }
    .mctl-table th { 
        background:var(--color-surface-soft) !important; 
        font-weight:bold;
        text-align:center;
    }
    .mctl-table tbody tr:nth-child(even) { background:var(--color-surface-soft) !important; }
    .mctl-table th:first-child, .mctl-table td:first-child {
        position:static !important;
        box-shadow:none !important;
    }
    .mctl-name {
        min-width:0 !important;
        max-width:none !important;
    }
    .mctl-actions {
        display:none !important;
    }
    .mctl-page-title {
        font-size:12px !important;
        margin:0 0 8px !important;
        color:var(--color-text-muted) !important;
    }
    tr { page-break-inside:avoid; }
    thead { display:table-header-group; }
    tbody { display:table-row-group; }
</style>
@endif
@endpush

@php
    $formatDate = function ($value) {
        return $value ? \Illuminate\Support\Carbon::parse($value)->format('m/d/Y') : '-';
    };

    $ageGroup = function ($age, $min, $max) {
        return is_numeric($age) && $age >= $min && $age <= $max ? $age : '';
    };

    $positiveNegative = function ($value) {
        return match ($value) {
            'positive' => '+',
            'negative' => '-',
            default => '-',
        };
    };

    $printAllPages = !empty($printMode);

    $formatVisitWithCount = function ($date, $count, $suffix = '') use ($formatDate) {
        if (!$date && !$count) {
            return '-';
        }

        $parts = [];
        if ($date) {
            $parts[] = $formatDate($date);
        }
        if ($count) {
            $parts[] = $count . ($suffix !== '' ? ' ' . $suffix : '');
        }

        return implode('<br>', $parts);
    };
@endphp

@section('midwife-content')
<div class="mctl-shell">
    <div class="mctl-header">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h1 class="mctl-title">Target Client List For Maternal Care And Services</h1>
                <p class="mctl-subtitle">Table headers aligned to the reference DOH form. Use the per-client input page to encode the records.</p>
            </div>
            <form method="GET" action="{{ route('midwife.maternal-care-target-clients.index') }}" class="d-flex gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search client, barangay">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
            </form>
        </div>
        <div class="mctl-stats mt-3">
            <div class="mctl-stat">
                <div class="mctl-stat-label">Maternal Clients</div>
                <div class="mctl-stat-value">{{ number_format($stats['totalClients']) }}</div>
            </div>
            <div class="mctl-stat">
                <div class="mctl-stat-label">Active Pregnancies</div>
                <div class="mctl-stat-value">{{ number_format($stats['activePregnancies']) }}</div>
            </div>
            <div class="mctl-stat">
                <div class="mctl-stat-label">Completed Maternal Profiles</div>
                <div class="mctl-stat-value">{{ number_format($stats['completedProfiles']) }}</div>
            </div>
        </div>
    </div>

    <div class="mctl-card">
        <div class="mctl-toolbar">
            <div class="mctl-tabs">
                @foreach (['page-1' => '1/4', 'page-2' => '2/4', 'page-3' => '3/4', 'page-4' => '4/4'] as $key => $label)
                    <a href="{{ route('midwife.maternal-care-target-clients.index', ['tab' => $key, 'search' => $search]) }}" class="mctl-tab {{ $tab === $key ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <a href="{{ route('midwife.maternal-care-target-clients.print', ['tab' => 'all', 'search' => $search]) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                    <i class="bi bi-printer me-1"></i>Print
                </a>
                <a href="{{ route('midwife.maternal-care-target-clients.create') }}" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Add New Record
                </a>
                <span class="text-muted small">Showing {{ $clients->count() }} of {{ $clients->total() }} clients</span>
            </div>
        </div>

        @if($clients->count() === 0)
            <div class="mctl-empty mb-3">
                <div>No maternal clients matched the current filter.</div>
                <div class="small mt-2">The selected target client table remains visible below so you can verify the form layout.</div>
            </div>
        @endif

        @if($printAllPages || $tab === 'page-1')
                <section class="mctl-print-page">
                <div class="mctl-page-title">Target Client List For Maternal Care And Services 1/4</div>
                <div class="mctl-table-wrap">
                    <table class="mctl-table">
                        <thead>
                            <tr>
                                @unless($printAllPages)
                                <th rowspan="3">Input</th>
                                @endunless
                                <th rowspan="3">No.</th>
                                <th rowspan="3">Date of Registration<br>(mm/dd/yy)<br>(1)</th>
                                <th rowspan="3">Family Serial<br>No.<br>(2)</th>
                                <th rowspan="3">Name<br>(FN, MI, LN)<br>(3)</th>
                                <th rowspan="3">Barangay<br>(4)</th>
                                @unless($printAllPages)
                                <th rowspan="3">Recorded By</th>
                                @endunless
                                <th colspan="3">Age<br>(Write under the proper category)<br>(5)</th>
                                <th rowspan="2">LMP<br>(mm/dd/yy)<br>(G-P)<br>(7)</th>
                                <th rowspan="2">EDC<br>(mm/dd/yy)<br>(8)</th>
                                <th colspan="8">Dates of Pre-natal Check-ups<br>(9)</th>
                            </tr>
                            <tr>
                                <th>10-14<br>y/o</th>
                                <th>15-19<br>y/o</th>
                                <th>20-49<br>y/o</th>
                                <th>1st Tri<br>(1)</th>
                                <th colspan="2">2nd Tri<br>(2)</th>
                                <th colspan="5">3rd Tri<br>(5)</th>
                            </tr>
                            <tr></tr>
                        </thead>
                        <tbody>
                            @forelse($clients as $client)
                                <tr>
                                    @unless($printAllPages)
                                    <td class="mctl-actions"><a href="{{ route('midwife.maternal-care-target-clients.edit', [$client['woman'], $client['pregnancy']?->id]) }}" class="btn btn-sm btn-outline-primary">Input</a></td>
                                    @endunless
                                    <td>{{ $client['number'] }}</td>
                                    <td>{{ $formatDate($client['profile']?->date_of_registration ?? $client['pregnancy']?->created_at) }}</td>
                                    <td>{{ $client['woman']->purok ? 'P' . $client['woman']->purok->id : '-' }}</td>
                                    <td class="mctl-name">
                                        {{ $client['woman']->name }}
                                    </td>
                                    <td>{{ $client['woman']->barangay ?? '-' }}</td>
                                    @unless($printAllPages)
                                    <td>{{ $client['profile']?->recordedBy?->name ?? 'Unknown' }}</td>
                                    @endunless
                                    <td>{{ $ageGroup($client['age'], 10, 14) }}</td>
                                    <td>{{ $ageGroup($client['age'], 15, 19) }}</td>
                                    <td>{{ $ageGroup($client['age'], 20, 49) }}</td>
                                    <td>
                                        {{ $formatDate($client['pregnancy']?->lmp) }}
                                        @if($client['profile']?->gravida || $client['profile']?->parity)
                                            <br>({{ $client['profile']->gravida ?? '-' }}-{{ $client['profile']->parity ?? '-' }})
                                        @endif
                                    </td>
                                    <td>{{ $formatDate($client['pregnancy']?->edd) }}</td>
                                    <td class="text-nowrap">{{ $formatDate($client['prenatal']['visits']['first'][0] ?? null) }}</td>
                                    @foreach($client['prenatal']['visits']['second'] as $visitDate)
                                        <td class="text-nowrap">{{ $formatDate($visitDate) }}</td>
                                    @endforeach
                                    @foreach($client['prenatal']['visits']['third'] as $visitDate)
                                        <td class="text-nowrap">{{ $formatDate($visitDate) }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr class="mctl-empty-row">
                                    <td colspan="{{ $printAllPages ? 19 : 20 }}" style="text-align:center;">No maternal client records available for this page.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </section>
        @endif

        @if($printAllPages || $tab === 'page-2')
                <section class="mctl-print-page">
                <div class="mctl-page-title">Target Client List For Maternal Care And Services 2/4</div>
                <div class="mctl-table-wrap">
                    <table class="mctl-table">
                        <thead>
                            <tr>
                                @unless($printAllPages)
                                <th rowspan="3">Input</th>
                                @endunless
                                <th rowspan="3">No.</th>
                                <th colspan="6">Immunization Status<br>(10)</th>
                                <th colspan="8">Micronutrient Supplementation<br>(11)</th>
                                <th colspan="3">Nutritional Assessment<br>(Write the BMI for 1st Tri)<br>(12)</th>
                                <th rowspan="3">Deworming<br>Tablet<br>(Date Given)<br>(2nd or 3rd Tri)<br>(13)</th>
                            </tr>
                            <tr>
                                <th colspan="5">Date Tetanus diphtheria (Td) or Tetanus Toxoid (TT) given</th>
                                <th rowspan="2">FIM Status<br>(v or X)</th>
                                <th colspan="4">Iron sulfate with Folic Acid<br>Date and Number of Tablets Given</th>
                                <th colspan="3">Calcium Carbonate<br>Date and Number of Tablets Given</th>
                                <th rowspan="2">Iodine Capsules<br>Date 2 capsules given<br>1st visit<br>(1st tri)</th>
                                <th rowspan="2">Low:<br>&lt; 18.5</th>
                                <th rowspan="2">Normal: 18.5<br>- 22.9</th>
                                <th rowspan="2">High:<br>&ge; 23.0</th>
                            </tr>
                            <tr>
                                <th>Td1/<br>TT1</th>
                                <th>Td2/<br>TT2</th>
                                <th>Td3/<br>TT3</th>
                                <th>Td4/<br>TT4</th>
                                <th>Td5/<br>TT5</th>
                                <th>1st visit<br>(1st tri)</th>
                                <th>2nd visit<br>(2nd tri)</th>
                                <th>3rd visit<br>(3rd tri)</th>
                                <th>4th visit<br>(3rd tri)</th>
                                <th>2nd visit<br>(2nd tri)</th>
                                <th>3rd visit<br>(3rd tri)</th>
                                <th>4th visit<br>(3rd tri)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clients as $client)
                                @php($profile = $client['profile'])
                                <tr>
                                    @unless($printAllPages)
                                    <td class="mctl-actions"><a href="{{ route('midwife.maternal-care-target-clients.edit', [$client['woman'], $client['pregnancy']?->id]) }}" class="btn btn-sm btn-outline-primary">Input</a></td>
                                    @endunless
                                    <td>{{ $client['number'] }}</td>
                                    <td>{{ $formatDate($profile?->td1_date) }}</td>
                                    <td>{{ $formatDate($profile?->td2_date) }}</td>
                                    <td>{{ $formatDate($profile?->td3_date) }}</td>
                                    <td>{{ $formatDate($profile?->td4_date) }}</td>
                                    <td>{{ $formatDate($profile?->td5_date) }}</td>
                                    <td>{{ $profile?->fim_status ? 'v' : 'X' }}</td>
                                    <td>{!! $formatVisitWithCount($profile?->iron_folic_first_visit_date, $profile?->iron_folic_first_visit_tablets, 'tabs') !!}</td>
                                    <td>{!! $formatVisitWithCount($profile?->iron_folic_second_visit_date, $profile?->iron_folic_second_visit_tablets, 'tabs') !!}</td>
                                    <td>{!! $formatVisitWithCount($profile?->iron_folic_third_visit_date, $profile?->iron_folic_third_visit_tablets, 'tabs') !!}</td>
                                    <td>{!! $formatVisitWithCount($profile?->iron_folic_fourth_visit_date, $profile?->iron_folic_fourth_visit_tablets, 'tabs') !!}</td>
                                    <td>{!! $formatVisitWithCount($profile?->calcium_second_visit_date, $profile?->calcium_second_visit_tablets, 'tabs') !!}</td>
                                    <td>{!! $formatVisitWithCount($profile?->calcium_third_visit_date, $profile?->calcium_third_visit_tablets, 'tabs') !!}</td>
                                    <td>{!! $formatVisitWithCount($profile?->calcium_fourth_visit_date, $profile?->calcium_fourth_visit_tablets, 'tabs') !!}</td>
                                    <td>{!! $formatVisitWithCount($profile?->iodine_date, $profile?->iodine_capsules_given, 'caps') !!}</td>
                                    <td>{{ $profile?->nutritional_assessment_status === 'low' ? 'v' : '' }}</td>
                                    <td>{{ $profile?->nutritional_assessment_status === 'normal' ? 'v' : '' }}</td>
                                    <td>{{ $profile?->nutritional_assessment_status === 'high' ? 'v' : '' }}</td>
                                    <td>{{ $formatDate($profile?->deworming_date) }}</td>
                                </tr>
                            @empty
                                <tr class="mctl-empty-row">
                                    <td colspan="{{ $printAllPages ? 19 : 20 }}">No maternal client records available for this page.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </section>
        @endif

        @if($printAllPages || $tab === 'page-3')
                <section class="mctl-print-page">
                <div class="mctl-page-title">Target Client List For Maternal Care And Services 3/4</div>
                <div class="mctl-table-wrap">
                    <table class="mctl-table">
                        <thead>
                            <tr>
                                @unless($printAllPages)
                                <th rowspan="3">Input</th>
                                @endunless
                                <th rowspan="3">No.</th>
                                <th colspan="5">Infectious Disease Surveillance<br>(14)</th>
                                <th colspan="6">Laboratory Screening<br>(15)</th>
                                <th colspan="3">Pregnancy Outcome<br>(Obtain data from the health facility record and LCR and reconcile to avoid double reporting)<br>PLACE OF OCCURRENCE<br>(16)</th>
                                <th rowspan="3">Type of<br>Delivery<br>(17)<br><br>CS - Caesarian Section<br>VD - Vaginal Delivery</th>
                                <th colspan="3">Birth Weight<br>(Write weight in grams)<br>(18)</th>
                            </tr>
                            <tr>
                                <th colspan="2">Syphilis Screening<br>(RPR or RDT Result)</th>
                                <th colspan="2">Hepatitis B Screening<br>(Result of HBsAg Test)</th>
                                <th rowspan="2">HIV Screening<br>Date Screened</th>
                                <th colspan="2">Gestational Diabetes</th>
                                <th colspan="4">CBC/Hgb&Hct Count</th>
                                <th rowspan="2">Date Terminated<br>(mm/dd/yy)</th>
                                <th colspan="2">Outcome</th>
                                <th rowspan="2">Low:<br>&lt; 2,500 grams</th>
                                <th rowspan="2">Normal:<br>&ge; 2,500 grams</th>
                                <th rowspan="2">Unknown<br>(Place a check if unknown)</th>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <th>+ positive<br>- negative</th>
                                <th>Date</th>
                                <th>+ positive<br>- negative</th>
                                <th>Date Screened</th>
                                <th>+ positive<br>- negative</th>
                                <th>Date Screened</th>
                                <th>+ w/ anemia</th>
                                <th>- w/o anemia</th>
                                <th>Given Iron</th>
                                <th>FT - Full Term<br>PT - Pre-term<br>FD - Fetal Death<br>AB - Abortion/Miscarriage</th>
                                <th>Sex<br>(M or F)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clients as $client)
                                @php($profile = $client['profile'])
                                <tr>
                                    @unless($printAllPages)
                                    <td class="mctl-actions"><a href="{{ route('midwife.maternal-care-target-clients.edit', [$client['woman'], $client['pregnancy']?->id]) }}" class="btn btn-sm btn-outline-primary">Input</a></td>
                                    @endunless
                                    <td>{{ $client['number'] }}</td>
                                    <td>{{ $formatDate($profile?->syphilis_screening_date) }}</td>
                                    <td>{{ $positiveNegative($profile?->syphilis_screening_result) }}</td>
                                    <td>{{ $formatDate($profile?->hepatitis_b_screening_date) }}</td>
                                    <td>{{ $positiveNegative($profile?->hepatitis_b_screening_result) }}</td>
                                    <td>{{ $formatDate($profile?->hiv_screening_date) }}</td>
                                    <td>{{ $formatDate($profile?->gestational_diabetes_screening_date) }}</td>
                                    <td>{{ $positiveNegative($profile?->gestational_diabetes_result) }}</td>
                                    <td>{{ $formatDate($profile?->cbc_screening_date) }}</td>
                                    <td>{{ $profile?->cbc_anemia_status === 'with_anemia' ? '+' : '' }}</td>
                                    <td>{{ $profile?->cbc_anemia_status === 'without_anemia' ? '-' : '' }}</td>
                                    <td>{{ $profile?->cbc_given_iron ? 'v' : '' }}</td>
                                    <td>{{ $formatDate($profile?->pregnancy_terminated_date) }}</td>
                                    <td>{{ $profile?->pregnancy_outcome ? strtoupper($profile->pregnancy_outcome) : ($client['pregnancy']?->outcome ?? '-') }}</td>
                                    <td>{{ $profile?->pregnancy_outcome_sex ?? '-' }}</td>
                                    <td>{{ $profile?->delivery_type ? strtoupper($profile->delivery_type) : '-' }}</td>
                                    <td>{{ $profile?->birth_weight_category === 'low' ? 'v' : '' }}</td>
                                    <td>{{ $profile?->birth_weight_category === 'normal' ? 'v' : '' }}</td>
                                    <td>{{ $profile?->birth_weight_category === 'unknown' ? 'v' : '' }}</td>
                                </tr>
                            @empty
                                <tr class="mctl-empty-row">
                                    <td colspan="{{ $printAllPages ? 19 : 20 }}">No maternal client records available for this page.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </section>
        @endif

        @if($printAllPages || $tab === 'page-4')
                <section class="mctl-print-page">
                <div class="mctl-page-title">Target Client List For Maternal Care And Services 4/4</div>
                <div class="mctl-table-wrap">
                    <table class="mctl-table">
                        <thead>
                            <tr>
                                @unless($printAllPages)
                                <th rowspan="3">Input</th>
                                @endunless
                                <th rowspan="3">No.</th>
                                <th colspan="4">Place of Delivery<br>(19)</th>
                                <th rowspan="3">Birth Attendant<br>(20)<br><br>MD - Doctor<br>RN - Nurse<br>MW - Midwife<br>H - Hilot/TBA<br>O - Others</th>
                                <th rowspan="3">Remarks<br>(21)</th>
                                <th colspan="2">Date and Time of Delivery<br>(22)</th>
                                <th colspan="2">Date of Mothers with their Newborns' Post-Partum Check-ups<br>(23)</th>
                                <th colspan="4">Micronutrient Supplementation<br>(24)</th>
                                <th rowspan="3">Remarks<br>(25)</th>
                            </tr>
                            <tr>
                                <th colspan="3">Health Facility</th>
                                <th rowspan="2">Non-Health Facility<br>1 - Home<br>2 - Others (including emergency transport)</th>
                                <th rowspan="2">Date<br>(mm/dd/yy)</th>
                                <th rowspan="2">Time</th>
                                <th rowspan="2">Within 24 hours<br>after delivery</th>
                                <th rowspan="2">Within 7 days after<br>delivery</th>
                                <th colspan="3">Iron with Folic Acid<br>(No. Tablets & Date Given)</th>
                                <th rowspan="2">Vit. A<br>(Date Given)</th>
                            </tr>
                            <tr>
                                <th>Type<br>BHS / RHU/MHC / Lying-in / Hospital / Birthing Homes / DOH-Licensed Ambulance</th>
                                <th>BEmONC/CEmONC capable<br>(Place a v)</th>
                                <th>Ownership<br>- Public<br>- Private</th>
                                <th>1st month postpartum</th>
                                <th>2nd month postpartum</th>
                                <th>3rd month postpartum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clients as $client)
                                @php($profile = $client['profile'])
                                <tr>
                                    @unless($printAllPages)
                                    <td class="mctl-actions"><a href="{{ route('midwife.maternal-care-target-clients.edit', [$client['woman'], $client['pregnancy']?->id]) }}" class="btn btn-sm btn-outline-primary">Input</a></td>
                                    @endunless
                                    <td>{{ $client['number'] }}</td>
                                    <td>{{ $profile?->facility_delivery_type ?? '-' }}</td>
                                    <td>{{ $profile?->facility_bemonc_capable ?? '-' }}</td>
                                    <td>{{ $profile?->facility_ownership ? ucfirst($profile->facility_ownership) : '-' }}</td>
                                    <td>{{ $profile?->non_health_facility_code ?? '-' }}</td>
                                    <td>{{ $profile?->birth_attendant_code ?? '-' }}</td>
                                    <td>{{ $profile?->delivery_remarks ?? '-' }}</td>
                                    <td>{{ $formatDate($profile?->delivery_date) }}</td>
                                    <td>{{ $profile?->delivery_time ?? '-' }}</td>
                                    <td>{{ $formatDate($profile?->postpartum_within_24_hours_date) }}</td>
                                    <td>{{ $formatDate($profile?->postpartum_within_7_days_date) }}</td>
                                    <td>{{ $profile?->postpartum_iron_first_month ?? '-' }}</td>
                                    <td>{{ $profile?->postpartum_iron_second_month ?? '-' }}</td>
                                    <td>{{ $profile?->postpartum_iron_third_month ?? '-' }}</td>
                                    <td>{{ $formatDate($profile?->vitamin_a_date) }}</td>
                                    <td>{{ $profile?->postpartum_remarks ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr class="mctl-empty-row">
                                    <td colspan="{{ $printAllPages ? 16 : 17 }}">No maternal client records available for this page.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </section>
        @endif

        <div class="mt-3 {{ $printAllPages ? 'd-none' : '' }}">
            {{ $clients->appends(['tab' => $tab, 'search' => $search])->links() }}
        </div>
    </div>
</div>
@if(!empty($printMode))
<script>
window.addEventListener('load', function () {
    window.print();
});
</script>
@endif
@endsection
