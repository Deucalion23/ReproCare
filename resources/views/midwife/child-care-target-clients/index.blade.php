@extends('midwife.layout')

@section('title', 'Target Client List For Child Care - Midwife Portal | ReproCare')

@push('styles')
<style>
    .cctl-shell { display:grid; gap:1.25rem; }
    .cctl-header, .cctl-card { background:var(--bg-card); border:1px solid var(--border); border-radius:22px; box-shadow:var(--shadow-sm); }
    .cctl-header { padding:1.4rem 1.6rem; }
    .cctl-title { font-family:'Plus Jakarta Sans', sans-serif; font-size:1.45rem; font-weight:800; color:var(--text); margin:0; }
    .cctl-subtitle { color:var(--text-muted); margin:0.35rem 0 0; }
    .cctl-stats { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:1rem; }
    .cctl-stat { padding:1rem 1.1rem; border-radius:18px; border:1px solid var(--border); background:linear-gradient(135deg, color-mix(in srgb, var(--color-info) 8%, transparent), color-mix(in srgb, var(--color-primary) 7%, transparent)); }
    .cctl-stat-label { font-size:0.78rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em; }
    .cctl-stat-value { font-size:1.55rem; font-weight:800; color:var(--text); }
    .cctl-card { padding:1.25rem; }
    .cctl-toolbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
    .cctl-tabs { display:flex; gap:0.6rem; flex-wrap:wrap; }
    .cctl-tab { padding:0.65rem 1rem; border-radius:999px; border:1px solid var(--border); color:var(--text-muted); text-decoration:none; font-weight:700; font-size:0.85rem; background:var(--bg-card2); }
    .cctl-tab.active { background:var(--primary-subtle); color:var(--primary-light); border-color:var(--border-glass); }
    .cctl-page-title {
        text-align:center;
        font-family:Georgia, 'Times New Roman', serif;
        font-size:1.05rem;
        font-weight:700;
        letter-spacing:0.02em;
        color:var(--text);
        margin-bottom:0.85rem;
        text-transform:uppercase;
    }
    .cctl-table-wrap { width:100%; max-width:100%; overflow-x:visible; border-radius:18px; border:1px solid var(--border); zoom:0.75; -moz-transform:scale(0.75); -moz-transform-origin:top left; }
    @supports not (zoom: 0.75) { .cctl-table-wrap { transform:scale(0.75); transform-origin:top left; width:133.33%; } }
    /* On phones the zoomed table would be clipped with no way to scroll —
       restore full size + horizontal scroll instead. */
    @media (max-width: 768px) {
        .cctl-table-wrap { overflow-x:auto; zoom:1; -moz-transform:none; transform:none; width:100%; }
    }
    .cctl-card:has(.cctl-table-wrap) { overflow:hidden; }
    .cctl-table { width:100%; border-collapse:collapse; background:var(--bg-card); font-size:0.7rem; }
    .cctl-table th, .cctl-table td { border:1px solid var(--border); padding:0.3rem 0.35rem; vertical-align:middle; color:var(--text); font-size:0.65rem; line-height:1.3; white-space:normal; word-wrap:break-word; max-width:120px; }
    .cctl-table thead th { background:var(--bg-card2); font-weight:800; text-align:center; }
    .cctl-table tbody td { color:var(--text-muted); }
    .cctl-name { min-width:100px; max-width:150px; color:var(--text) !important; font-weight:700; }
    .cctl-empty { padding:2rem; text-align:center; color:var(--text-muted); border:1px dashed var(--border); border-radius:18px; }
    .cctl-empty-row td { padding:1.25rem 1rem; text-align:center; color:var(--text-muted); }
    .cctl-table th:first-child, .cctl-table td:first-child { min-width:50px; text-align:center; }
    .cctl-table .btn-sm { font-size:0.6rem; padding:0.2rem 0.4rem; }
    @media (max-width: 768px) { .cctl-stats { grid-template-columns:1fr; } }
</style>
@if(!empty($printMode))
<style>
    @page { size:landscape; margin:8mm; }
    html, body {
        background:var(--color-surface) !important;
        padding-top:0 !important;
        margin:0 !important;
    }
    .navbar, .sidebar, .topbar, .cctl-header, .cctl-toolbar, .pagination { display:none !important; }
    .layout-wrapper, .main-content, .cctl-shell, .cctl-card {
        display:block !important;
        margin:0 !important;
        padding:0 !important;
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;
    }
    .cctl-card {
        box-shadow:none !important;
        border:none !important;
        border-radius:0 !important;
        overflow:visible !important;
        background:var(--color-surface) !important;
    }
    .cctl-print-page {
        width:100%;
        break-after:page;
        page-break-after:always;
    }
    .cctl-print-page:last-of-type {
        break-after:auto;
        page-break-after:auto;
    }
    .cctl-table-wrap {
        zoom:1 !important;
        transform:none !important;
        width:100% !important;
        max-width:100% !important;
        overflow:visible !important;
        border:none !important;
        border-radius:0 !important;
    }
    .cctl-table {
        width:100% !important;
        min-width:0 !important;
        table-layout:auto;
    }
    .cctl-table th, .cctl-table td {
        font-size:7.5px !important;
        padding:3px 2px !important;
        color:var(--color-text-muted) !important;
        border:1px solid var(--color-border) !important;
        word-break:break-word;
        white-space:normal;
        max-width:none !important;
    }
    .cctl-table thead th {
        background:var(--color-surface-soft) !important;
    }
    .cctl-name {
        min-width:0 !important;
        max-width:none !important;
    }
    .cctl-page-title {
        font-size:12px !important;
        margin:0 0 8px !important;
        color:var(--color-text-muted) !important;
    }
    .cctl-actions {
        display:none !important;
    }
    tr { page-break-inside:avoid; }
    thead { display:table-header-group; }
    tbody { display:table-row-group; }
</style>
@endif
@endpush

@php
    $formatDate = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('m/d/Y') : '-';
    $flag = fn ($value) => $value ? 'v' : '';
    $printAllPages = !empty($printMode);
@endphp

@section('midwife-content')
<div class="cctl-shell">
    <div class="cctl-header">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h1 class="cctl-title">Target Client List For Child Care And Services (0-12 Mos)</h1>
                <p class="cctl-subtitle">Table headers aligned to the reference 0-12 month child care form.</p>
            </div>
            <form method="GET" action="{{ route('midwife.child-care-target-clients.index') }}" class="d-flex gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search child, mother, barangay">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
            </form>
        </div>
        <div class="cctl-stats mt-3">
            <div class="cctl-stat"><div class="cctl-stat-label">Children</div><div class="cctl-stat-value">{{ number_format($stats['totalChildren']) }}</div></div>
            <div class="cctl-stat"><div class="cctl-stat-label">Active Child Records</div><div class="cctl-stat-value">{{ number_format($stats['activeChildren']) }}</div></div>
            <div class="cctl-stat"><div class="cctl-stat-label">Completed Childcare Profiles</div><div class="cctl-stat-value">{{ number_format($stats['completedProfiles']) }}</div></div>
        </div>
    </div>

    <div class="cctl-card">
        <div class="cctl-toolbar">
            <div class="cctl-tabs">
                @foreach(['page-1' => 'Page 1/4', 'page-2' => 'Page 2/4', 'page-3' => 'Page 3/4', 'page-4' => 'Page 4/4'] as $key => $label)
                    <a href="{{ route('midwife.child-care-target-clients.index', ['tab' => $key, 'search' => $search]) }}" class="cctl-tab {{ $tab === $key ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <a href="{{ route('midwife.child-care-target-clients.print', ['tab' => 'all', 'search' => $search]) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                    <i class="bi bi-printer me-1"></i>Print
                </a>
                <a href="{{ route('midwife.child-care-target-clients.create') }}" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Add New Record
                </a>
                <span class="text-muted small">Showing {{ $clients->count() }} of {{ $clients->total() }} children</span>
            </div>
        </div>

        @if($clients->count() === 0)
            <div class="cctl-empty mb-3">No records</div>
        @endif

        @if($printAllPages || $tab === 'page-1')
                <section class="cctl-print-page">
                <div class="cctl-page-title">Target Client List For Child Care And Services (0-12 Mos) 1/4</div>
                <div class="cctl-table-wrap"><table class="cctl-table">
                    <thead>
                        <tr>
                            @unless($printAllPages)
                            <th rowspan="3">Input</th>
                            @endunless
                            <th rowspan="3">No.</th>
                            <th rowspan="3">Date of Registration<br>(mm/dd/yy)<br>(1)</th>
                            <th rowspan="3">Date of Birth<br>(mm/dd/yy)<br>(2)</th>
                            <th rowspan="3">Family Serial<br>Number<br>(3)</th>
                            <th rowspan="3">Name of Child<br>(Last Name, First Name, Middle Initial)<br>(5)</th>
                            <th rowspan="3">Sex<br>(M or F)<br>(6)</th>
                            <th rowspan="3">Complete Name of Mother<br>(LN, FN, MI)<br>(7)</th>
                            <th rowspan="3">Barangay<br>(8)</th>
                            <th colspan="3">Child Protected at Birth (CPAB)<br>(9)<br>(Place a v)<br>(counts should be consistent with Maternal TCL Livebirths)</th>
                        </tr>
                        <tr>
                            <th>TT2/Td2 given to<br>the mother a<br>month prior to<br>delivery<br>(9a)</th>
                            <th>TT3/Td3 to<br>TT6/Td6 (or<br>TT1/Td1 to<br>TT5/Td5) given to<br>the mother anytime<br>prior to delivery<br>(9b)</th>
                            <th>Total<br>(9a + 9b)</th>
                        </tr>
                        <tr></tr>
                    </thead>
                    <tbody>
                            @forelse($clients as $client)
                            @php($profile = $client['profile'])
                            <tr>
                                @unless($printAllPages)
                                <td><a href="{{ route('midwife.child-care-target-clients.edit', $client['child']) }}" class="btn btn-sm btn-outline-primary">Input</a></td>
                                @endunless
                                <td>{{ $client['number'] }}</td>
                                <td>{{ $formatDate($profile?->date_of_registration ?? $client['child']->created_at) }}</td>
                                <td>{{ $formatDate($client['child']->date_of_birth) }}</td>
                                <td>{{ $profile?->family_serial_number ?? '-' }}</td>
                                <td class="cctl-name">{{ $client['child']->full_name }}</td>
                                <td>{{ strtoupper(substr($client['child']->gender ?? '', 0, 1)) ?: '-' }}</td>
                                <td>{{ $client['child']->mother?->name ?? '-' }}</td>
                                <td>{{ $client['child']->barangay ?? $client['child']->mother?->barangay ?? '-' }}</td>
                                <td>{{ $flag($profile?->cpab_tt2_tt4) }}</td>
                                <td>{{ $flag($profile?->cpab_tt3_tt5) }}</td>
                                <td>{{ ($profile?->cpab_tt2_tt4 ? 1 : 0) + ($profile?->cpab_tt3_tt5 ? 1 : 0) }}</td>
                            </tr>
                            @empty
                            <tr class="cctl-empty-row">
                                <td colspan="{{ $printAllPages ? 11 : 12 }}">No child records available for this page.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
                </section>
        @endif

        @if($printAllPages || $tab === 'page-2')
                <section class="cctl-print-page">
                <div class="cctl-page-title">Target Client List For Child Care And Services (0-12 Mos) 2/4</div>
                <div class="cctl-table-wrap"><table class="cctl-table">
                    <thead>
                        <tr>
                            @unless($printAllPages)
                            <th rowspan="4">Input</th>
                            @endunless
                            <th rowspan="4">No.</th>
                            <th colspan="6">Newborn (0-28 days old)<br>(10)</th>
                            <th colspan="11">1-3 months old<br>(Col 11)</th>
                        </tr>
                        <tr>
                            <th rowspan="3">Length<br>at Birth<br>(cm)</th>
                            <th rowspan="3">Weight<br>at Birth<br>(kg)</th>
                            <th rowspan="3">Status<br>(Birth Weight)<br>L: low: &lt;2,500gms<br>N: normal: &ge;2,500gms<br>U: unknown</th>
                            <th rowspan="3">Initiated breast<br>feeding immediately<br>after birth within 90<br>minutes<br>(date)</th>
                            <th colspan="3">Immunization</th>
                            <th colspan="5">Nutritional Status Assessment</th>
                            <th colspan="3">Low birth weight given iron<br>(Write the date)</th>
                        </tr>
                        <tr>
                            <th rowspan="2">BCG<br>(date)</th>
                            <th rowspan="2">Hepa B-BD<br>(date)</th>
                            <th rowspan="2">Age in months</th>
                            <th colspan="2">Length(cm) & date taken</th>
                            <th colspan="2">Weight (kg) & date taken</th>
                            <th rowspan="2">Status<br>S = stunted<br>W-MAM = wasted-MAM<br>W-SAM = wasted-SAM<br>O = obese/overweight<br>N = normal</th>
                            <th rowspan="2">1 mo</th>
                            <th rowspan="2">2 mos</th>
                            <th rowspan="2">3 mos</th>
                        </tr>
                        <tr>
                            <th>Length(cm)</th>
                            <th>Date Taken</th>
                            <th>Weight(kg)</th>
                            <th>Date Taken</th>
                        </tr>
                    </thead>
                    <tbody>
                            @forelse($clients as $client)
                            @php($profile = $client['profile'])
                            <tr>
                                @unless($printAllPages)
                                <td><a href="{{ route('midwife.child-care-target-clients.edit', $client['child']) }}" class="btn btn-sm btn-outline-primary">Input</a></td>
                                @endunless
                                <td>{{ $client['number'] }}</td>
                                <td>{{ $client['child']->birth_length ?? '-' }}</td>
                                <td>{{ $client['child']->birth_weight ?? '-' }}</td>
                                <td>{{ $profile?->newborn_status ? strtoupper(substr($profile->newborn_status, 0, 1)) : '-' }}</td>
                                <td>{{ $formatDate($profile?->breastfeeding_initiated_date) }}</td>
                                <td>{{ $formatDate($profile?->bcg_date) }}</td>
                                <td>{{ $formatDate($profile?->hepa_b_bd_date) }}</td>
                                <td>{{ $profile?->assessment_1_3_age_months ?? '-' }}</td>
                                <td>{{ $profile?->assessment_1_3_length_cm ?? '-' }}</td>
                                <td>{{ $formatDate($profile?->assessment_1_3_length_date) }}</td>
                                <td>{{ $profile?->assessment_1_3_weight_kg ?? '-' }}</td>
                                <td>{{ $formatDate($profile?->assessment_1_3_weight_date) }}</td>
                                <td>{{ $profile?->assessment_1_3_status ?? '-' }}</td>
                                <td>{{ $formatDate($profile?->low_birth_weight_iron_1_month_date) }}</td>
                                <td>{{ $formatDate($profile?->low_birth_weight_iron_2_month_date) }}</td>
                                <td>{{ $formatDate($profile?->low_birth_weight_iron_3_month_date) }}</td>
                            </tr>
                            @empty
                            <tr class="cctl-empty-row">
                                <td colspan="{{ $printAllPages ? 16 : 17 }}">No child records available for this page.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
                </section>
        @endif

        @if($printAllPages || $tab === 'page-3')
                <section class="cctl-print-page">
                <div class="cctl-page-title">Target Client List For Child Care And Services (0-12 Mos) 3/4</div>
                <div class="cctl-table-wrap"><table class="cctl-table">
                    <thead>
                        <tr>
                            @unless($printAllPages)
                            <th rowspan="4">Input</th>
                            @endunless
                            <th rowspan="4">No.</th>
                            <th colspan="15">1-3 months old<br>(Col 11)</th>
                            <th colspan="4">6-11 months old<br>(12)</th>
                        </tr>
                        <tr>
                            <th colspan="10">Immunization<br>(Write the date)</th>
                            <th colspan="5">Exclusive Breastfeeding*<br>Place a check (v)<br>During the following immunization visits of the child at 1 1/2, 2 1/2 and 3 1/2 months old<br>(or at 4-5 mos.), ask the mother if the child continues to be exclusively breastfed.<br>Place a check (v) on each column</th>
                            <th colspan="4">Nutritional Status Assessment</th>
                        </tr>
                        <tr>
                            <th colspan="3">DPT-HiB-HepB</th>
                            <th colspan="3">OPV</th>
                            <th colspan="3">PCV</th>
                            <th>IPV</th>
                            <th rowspan="2">1 1/2 mos.</th>
                            <th rowspan="2">2 1/2 mos.</th>
                            <th rowspan="2">3 1/2 mos.</th>
                            <th rowspan="2">4 1/2 mos.</th>
                            <th rowspan="2">5 mos. & 29 days old</th>
                            <th rowspan="2">Age in months</th>
                            <th rowspan="2">Length<br>(cm)<br>& date taken</th>
                            <th rowspan="2">Weight<br>(kg)<br>& date taken</th>
                            <th rowspan="2">Status<br>S = stunted<br>W-MAM = wasted-MAM<br>W-SAM = wasted-SAM<br>O = obese/overweight<br>N = normal</th>
                        </tr>
                        <tr>
                            <th>1st dose<br>1 1/2 mos</th>
                            <th>2nd dose<br>2 1/2 mos</th>
                            <th>3rd dose<br>3 1/2 mos</th>
                            <th>1st dose<br>1 1/2 mos</th>
                            <th>2nd dose<br>2 1/2 mos</th>
                            <th>3rd dose<br>3 1/2 mos</th>
                            <th>1st dose<br>1 1/2 mos</th>
                            <th>2nd dose<br>2 1/2 mos</th>
                            <th>3rd dose<br>3 1/2 mos</th>
                            <th>3 1/2 mos</th>
                        </tr>
                    </thead>
                    <tbody>
                            @forelse($clients as $client)
                            @php($profile = $client['profile'])
                            <tr>
                                @unless($printAllPages)
                                <td><a href="{{ route('midwife.child-care-target-clients.edit', $client['child']) }}" class="btn btn-sm btn-outline-primary">Input</a></td>
                                @endunless
                                <td>{{ $client['number'] }}</td>
                                <td>{{ $formatDate($profile?->dpt_hepb_hib_1_date) }}</td>
                                <td>{{ $formatDate($profile?->dpt_hepb_hib_2_date) }}</td>
                                <td>{{ $formatDate($profile?->dpt_hepb_hib_3_date) }}</td>
                                <td>{{ $formatDate($profile?->opv_1_date) }}</td>
                                <td>{{ $formatDate($profile?->opv_2_date) }}</td>
                                <td>{{ $formatDate($profile?->opv_3_date) }}</td>
                                <td>{{ $formatDate($profile?->pcv_1_date) }}</td>
                                <td>{{ $formatDate($profile?->pcv_2_date) }}</td>
                                <td>{{ $formatDate($profile?->pcv_3_date) }}</td>
                                <td>{{ $formatDate($profile?->ipv_1_date) }}</td>
                                <td>{{ $flag($profile?->exclusive_breastfeeding_1_5_months) }}</td>
                                <td>{{ $flag($profile?->exclusive_breastfeeding_2_5_months) }}</td>
                                <td>{{ $flag($profile?->exclusive_breastfeeding_3_5_months) }}</td>
                                <td>{{ $flag($profile?->exclusive_breastfeeding_4_5_months) }}</td>
                                <td>{{ $flag($profile?->exclusive_breastfeeding_5_9_months) }}</td>
                                <td>{{ $profile?->assessment_6_11_age_months ?? '-' }}</td>
                                <td>{!! $profile?->assessment_6_11_length_cm ? $profile->assessment_6_11_length_cm . '<br>' . $formatDate($profile->assessment_6_11_length_date) : '-' !!}</td>
                                <td>{!! $profile?->assessment_6_11_weight_kg ? $profile->assessment_6_11_weight_kg . '<br>' . $formatDate($profile->assessment_6_11_weight_date) : '-' !!}</td>
                                <td>{{ $profile?->assessment_6_11_status ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr class="cctl-empty-row">
                                <td colspan="{{ $printAllPages ? 20 : 21 }}">No child records available for this page.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
                </section>
        @endif

        @if($printAllPages || $tab === 'page-4')
                <section class="cctl-print-page">
                <div class="cctl-page-title">Target Client List For Child Care And Services (0-12 Mos) 4/4</div>
                <div class="cctl-table-wrap"><table class="cctl-table">
                    <thead>
                        <tr>
                            @unless($printAllPages)
                            <th rowspan="4">Input</th>
                            @endunless
                            <th rowspan="4">No.</th>
                            <th colspan="7">6-11 months old<br>(12)</th>
                            <th colspan="6">12 months old<br>(13)</th>
                            <th rowspan="4">CIC<br>(date)<br><br>(14)</th>
                            <th colspan="8">0-11 months old<br>(15)</th>
                            <th rowspan="4">Remarks<br><br>(16)</th>
                        </tr>
                        <tr>
                            <th rowspan="3">Exclusively<br>Breastfed* up to 6<br>months<br>(Y or N)</th>
                            <th colspan="2">Introduction of Complementary<br>Feeding** at 6 months old</th>
                            <th rowspan="3">Vitamin A<br>(date given)</th>
                            <th rowspan="3">MNP<br>(date when<br>90 sachets<br>given)</th>
                            <th rowspan="3">MMR<br>Dose 1 at 9th<br>month<br>(date given)</th>
                            <th rowspan="3">IPV Dose 2<br>at 9th month<br>(date given)</th>
                            <th colspan="4">Nutritional Status Assessment</th>
                            <th rowspan="3">MMR<br>Dose 2 at 12th<br>month<br>(date given)</th>
                            <th rowspan="3">FIC***<br>(date)</th>
                            <th colspan="4">MAN</th>
                            <th colspan="4">SAM w/o complication</th>
                        </tr>
                        <tr>
                            <th rowspan="2">Y or N</th>
                            <th rowspan="2">1 - With continuous<br>breastfeeding<br>2 - no longer<br>breastfeeding or never<br>breastfed</th>
                            <th rowspan="2">Age in<br>months</th>
                            <th rowspan="2">Length<br>(cm)<br>& date taken</th>
                            <th rowspan="2">Weight (kg)<br>& date taken</th>
                            <th rowspan="2">Status<br>S = stunted<br>W-MAM = wasted-MAM<br>W-SAM = wasted-SAM<br>O = obese/overweight<br>N = normal</th>
                            <th rowspan="2">Admitted in<br>SFP</th>
                            <th rowspan="2">Cured</th>
                            <th rowspan="2">Defaulted</th>
                            <th rowspan="2">Died</th>
                            <th rowspan="2">Admitted in<br>OTC</th>
                            <th rowspan="2">Cured</th>
                            <th rowspan="2">Defaulted</th>
                            <th rowspan="2">Died</th>
                        </tr>
                        <tr></tr>
                    </thead>
                    <tbody>
                            @forelse($clients as $client)
                            @php($profile = $client['profile'])
                            <tr>
                                @unless($printAllPages)
                                <td><a href="{{ route('midwife.child-care-target-clients.edit', $client['child']) }}" class="btn btn-sm btn-outline-primary">Input</a></td>
                                @endunless
                                <td>{{ $client['number'] }}</td>
                                <td>{{ $profile?->exclusive_breastfed_up_to_6_months ? 'Y' : 'N' }}</td>
                                <td>{{ $profile?->complementary_feeding_introduced ? 'Y' : 'N' }}</td>
                                <td>{{ $profile?->complementary_feeding_introduced ? '1' : '2' }}</td>
                                <td>{{ $formatDate($profile?->vitamin_a_date) }}</td>
                                <td>{!! $profile?->mnp_date ? $formatDate($profile->mnp_date) . '<br>' . ($profile->mnp_sachets_given ?? 0) . ' sachets' : '-' !!}</td>
                                <td>{{ $formatDate($profile?->mmr_1_date) }}</td>
                                <td>{{ $formatDate($profile?->ipv_2_date) }}</td>
                                <td>{{ $profile?->assessment_12_age_months ?? '-' }}</td>
                                <td>{!! $profile?->assessment_12_length_cm ? $profile->assessment_12_length_cm . '<br>' . $formatDate($profile->assessment_12_length_date) : '-' !!}</td>
                                <td>{!! $profile?->assessment_12_weight_kg ? $profile->assessment_12_weight_kg . '<br>' . $formatDate($profile->assessment_12_weight_date) : '-' !!}</td>
                                <td>{{ $profile?->assessment_12_status ?? '-' }}</td>
                                <td>{{ $formatDate($profile?->mmr_2_date) }}</td>
                                <td>{{ $formatDate($profile?->fic_date) }}</td>
                                <td>{{ $formatDate($profile?->cic_date) }}</td>
                                <td>{{ $flag($profile?->man_admitted_sfp) }}</td>
                                <td>{{ $flag($profile?->man_cured) }}</td>
                                <td>{{ $flag($profile?->man_defaulted) }}</td>
                                <td>{{ $flag($profile?->man_died) }}</td>
                                <td>{{ $flag($profile?->sam_admitted_otc) }}</td>
                                <td>{{ $flag($profile?->sam_cured) }}</td>
                                <td>{{ $flag($profile?->sam_defaulted) }}</td>
                                <td>{{ $flag($profile?->sam_died) }}</td>
                                <td>{{ $profile?->remarks ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr class="cctl-empty-row">
                                <td colspan="{{ $printAllPages ? 24 : 25 }}">No child records available for this page.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
                </section>
        @endif

        <div class="mt-3 {{ $printAllPages ? 'd-none' : '' }}">{{ $clients->appends(['tab' => $tab, 'search' => $search])->links() }}</div>
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
