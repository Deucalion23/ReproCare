@extends('midwife.layout')

@section('title', 'Women - Midwife Portal | ReproCare')

@push('styles')
<style>
    .women-toolbar { display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .women-search {
        display:flex; align-items:center; gap:0.55rem;
        min-width:min(100%, 320px); padding:0.55rem 0.8rem;
        border-radius:14px; border:1px solid var(--border); background:var(--bg-card2);
    }
    .women-search:focus-within { border-color:var(--primary); box-shadow:0 0 0 3px var(--focus-ring); }
    .women-search input { flex:1; border:0; outline:0; background:transparent; color:var(--text); }
    .women-filter-grid {
        display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:1rem;
    }
    .women-filter-card {
        display:block; text-decoration:none; padding:1.1rem 1.15rem;
        border-radius:18px; border:1px solid var(--border); background:var(--bg-card);
        color:var(--text); transition:transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
    }
    .women-filter-card:hover { transform:translateY(-2px); border-color:var(--primary); box-shadow:var(--shadow-sm); }
    .women-filter-card.active { border-color:var(--primary); box-shadow:0 0 0 3px var(--focus-ring); background:linear-gradient(135deg, var(--primary-subtle), color-mix(in srgb, var(--color-surface) 2%, transparent)); }
    .women-filter-top { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:0.7rem; }
    .women-filter-label { font-weight:700; }
    .women-filter-count { font-family:'Plus Jakarta Sans', sans-serif; font-size:1.8rem; font-weight:800; line-height:1; }
    .women-filter-note { color:var(--text-muted); font-size:0.84rem; }
    .women-meta { display:flex; gap:0.6rem; flex-wrap:wrap; }
    .women-table-name { display:flex; align-items:center; gap:0.8rem; }
    .women-avatar {
        width:42px; height:42px; border-radius:50%; flex-shrink:0;
        display:inline-flex; align-items:center; justify-content:center;
        color:var(--color-on-solid); font-weight:800; background:var(--color-surface-strong); background-color:var(--color-surface-strong);
    }
    .women-name { font-weight:800; color:var(--color-text); }
    .women-sub { color:var(--color-text-muted); font-size:0.8rem; }
    .women-type-badge {
        display:inline-flex; align-items:center; gap:0.35rem; padding:0.32rem 0.75rem;
        border-radius:999px; font-size:0.72rem; font-weight:800; border:none;
    }
    .women-type-badge.registered { background:var(--color-success-soft); background-color:var(--color-success-soft); color:var(--color-success-text); }
    .women-type-badge.walk-in { background:var(--color-peach-soft); background-color:var(--color-peach-soft); color:var(--color-warning-text); }
    .women-action-btn {
        display:inline-flex; align-items:center; justify-content:center;
        width:34px; height:34px; border-radius:10px; border:1px solid var(--border);
        background:var(--bg-card2); color:var(--primary-light); text-decoration:none;
    }
    .women-action-btn:hover { background:var(--primary-subtle); border-color:var(--primary); }
    /* CRITICAL FIX FOR FILTER DROPDOWN CLIPPING & STACKING */
    .main-content > .filter-toolbar-card,
    .filter-toolbar-card,
    .filter-toolbar-card .card-body,
    .filter-toolbar-card form {
        overflow:visible !important;
        overflow-x:visible !important;
        overflow-y:visible !important;
    }
    .filter-toolbar-card {
        position:relative !important;
        z-index:20 !important;
    }
    .directory-card-wrapper {
        position:relative !important;
        z-index:10 !important;
    }
    .filter-popover-panel {
        position:absolute !important;
        right:0 !important;
        top:100% !important;
        margin-top:0.5rem !important;
        min-width:min(320px, calc(100vw - 32px)) !important;
        width:340px !important;
        max-width:calc(100vw - 32px) !important;
        border-radius:1rem !important;
        border:none !important;
        background:var(--color-surface) !important;
        box-shadow:0 20px 25px -5px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent), 0 8px 10px -6px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent), 0 20px 45px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 18%, transparent) !important;
        z-index:50 !important;
    }
</style>
@endpush

@section('midwife-content')
<div class="page-hero fade-in-card">
    <div class="women-toolbar" style="position:relative;z-index:1;">
        <div>
            <h1 class="page-hero-title">
                Women
            </h1>
            <p class="page-hero-subtitle">Review enrolled women and unlinked profiles in one organized list.</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="{{ route('midwife.walk-in-patients.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-person-badge me-1"></i> Review Unlinked
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mt-4 fade-in-card">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $secActiveCount = 0;
    if (!empty($riskLevel) && $riskLevel !== 'all') $secActiveCount++;
    if (!empty($ageRange) && $ageRange !== 'all') $secActiveCount++;
    if (!empty($trimester) && $trimester !== 'all') $secActiveCount++;
    if (!empty($pregnancyStatus) && $pregnancyStatus !== 'all') $secActiveCount++;
@endphp

<div class="card fade-in-card filter-toolbar-card my-4" style="border:none; border-radius:24px; box-shadow:var(--wp-shadow-sm); overflow:visible !important; position:relative !important; z-index:20 !important;">
    <div class="card-body p-3 p-md-4" style="overflow:visible !important; border:none;">
        {{-- Scope Nav Pills --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-3" style="border:none;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('midwife.patients', array_merge(request()->except(['page']), ['filter' => 'all'])) }}" 
                   class="btn btn-sm rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1.5"
                   style="border:none; {{ request('filter', 'all') === 'all' ? 'background:var(--color-text) !important; color:var(--color-on-solid) !important;' : 'background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text);' }}">
                    <i class="bi bi-people-fill"></i> All Women ({{ $patients->total() }})
                </a>
                <a href="{{ route('midwife.patients', array_merge(request()->except(['page']), ['filter' => 'registered'])) }}" 
                   class="btn btn-sm rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1.5"
                   style="border:none; {{ request('filter') === 'registered' ? 'background:var(--color-text) !important; color:var(--color-on-solid) !important;' : 'background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text);' }}">
                    <i class="bi bi-person-check-fill"></i> Enrolled ({{ $registeredCount }})
                </a>
                <a href="{{ route('midwife.patients', array_merge(request()->except(['page']), ['filter' => 'unregistered'])) }}" 
                   class="btn btn-sm rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1.5"
                   style="border:none; {{ request('filter') === 'unregistered' ? 'background:var(--color-text) !important; color:var(--color-on-solid) !important;' : 'background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text);' }}">
                    <i class="bi bi-person-badge-fill"></i> Unlinked ({{ $unregisteredCount }})
                </a>
            </div>
            @if(request()->hasAny(['search', 'barangay', 'risk_level', 'age_range', 'trimester', 'pregnancy_status']))
                <a href="{{ route('midwife.patients', ['filter' => request('filter', 'all')]) }}" class="btn btn-sm btn-link text-danger text-decoration-none fw-semibold">
                    <i class="bi bi-x-circle me-1"></i> Clear All Filters
                </a>
            @endif
        </div>

        {{-- Inline Toolbar with Collapsed Popover --}}
        <form method="GET" action="{{ route('midwife.patients') }}" id="womenFilterForm" class="d-flex align-items-center gap-2 flex-wrap" style="position:relative; overflow:visible !important;">
            <input type="hidden" name="filter" value="{{ request('filter', 'all') }}">

            {{-- 1. Search Input (directly visible, triggers live dynamic filtering) --}}
            <div class="position-relative flex-grow-1" style="min-width:240px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3" style="color:var(--color-text-muted); font-size:0.95rem;"></i>
                <input type="search" name="search" id="womenSearchInput" value="{{ request('search') }}" 
                       placeholder="Search name, ID, contact..." 
                       class="form-control" 
                       autocomplete="off"
                       style="height:44px; border-radius:12px; border:none; background:var(--color-surface-soft); background-color:var(--color-surface-soft); padding-left:2.6rem; font-size:0.9rem;">
            </div>

            {{-- 2. Barangay Dropdown (directly visible) --}}
            <div style="min-width:200px; max-width:260px;" class="flex-grow-1 flex-md-grow-0">
                <select name="barangay" class="form-select" style="height:44px; border-radius:12px; border:none; background-color:var(--color-surface-soft); font-size:0.9rem;" onchange="this.form.submit()">
                    <option value="">All Barangays</option>
                    @php
                        $bList = !empty($barangays) ? $barangays : \App\Http\Controllers\AuthController::getSanCarlosBarangays();
                    @endphp
                    @foreach($bList as $b)
                        <option value="{{ $b }}" {{ request('barangay') === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 3. Collapsed "Filters" Popover Button (z-50 container) --}}
            <div class="dropdown position-relative" style="z-index:50;">
                <button type="button" 
                        id="filtersDropdownBtn"
                        class="btn d-flex align-items-center gap-2 dropdown-toggle" 
                        data-bs-toggle="dropdown" 
                        data-bs-display="static"
                        data-bs-auto-close="outside" 
                        aria-expanded="false"
                        style="height:44px; border-radius:12px; border:none; background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text); font-weight:700; padding:0 1.15rem;">
                    <i class="bi bi-sliders"></i>
                    <span>Filters{{ $secActiveCount > 0 ? ' (' . $secActiveCount . ')' : '' }}</span>
                    @if($secActiveCount > 0)
                        <span class="badge rounded-pill" style="background:var(--color-primary); color:var(--color-on-solid); font-size:0.75rem; padding:0.25em 0.55em;">{{ $secActiveCount }}</span>
                    @endif
                </button>

                {{-- Floating Dropdown/Popover Panel anchored directly beneath trigger button (right-0 top-full mt-2) --}}
                <div class="dropdown-menu dropdown-menu-end shadow-xl p-3.5 filter-popover-panel" 
                     id="secondaryFiltersPanel"
                     style="position:absolute !important; right:0 !important; top:100% !important; margin-top:0.5rem !important; min-width:min(320px, calc(100vw - 32px)) !important; width:340px !important; max-width:calc(100vw - 32px) !important; border-radius:1rem !important; border:none; background:var(--color-surface); box-shadow:0 20px 25px -5px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent), 0 8px 10px -6px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent), 0 20px 45px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 18%, transparent) !important; z-index:50 !important;">
                    
                    <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom" style="border-color:var(--color-border) !important;">
                        <h6 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans', sans-serif; color:var(--color-text); font-size:0.95rem;">Secondary Filters
                        </h6>
                        @if($secActiveCount > 0)
                            <span class="badge rounded-pill" style="background:var(--color-primary-soft); border:1px solid var(--color-border); color:var(--color-primary-text); font-size:0.75rem; font-weight:700;">
                                {{ $secActiveCount }} active
                            </span>
                        @endif
                    </div>

                    <div class="d-flex flex-column gap-2.5">
                        {{-- Risk Tier --}}
                        <div>
                            <label for="sec_risk_level" class="form-label text-muted text-uppercase fw-bold mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">
                                Risk Tier
                            </label>
                            <select name="risk_level" id="sec_risk_level" class="form-select form-select-sm" style="height:42px; border-radius:10px; border:1px solid var(--color-border); font-size:0.88rem;">
                                <option value="all" {{ ($riskLevel ?? 'all') === 'all' ? 'selected' : '' }}>All Risk Tiers</option>
                                <option value="high_risk_only" {{ ($riskLevel ?? '') === 'high_risk_only' ? 'selected' : '' }}>🚨 High Risk Only</option>
                                <option value="critical" {{ ($riskLevel ?? '') === 'critical' ? 'selected' : '' }}>Critical Risk</option>
                                <option value="high" {{ ($riskLevel ?? '') === 'high' ? 'selected' : '' }}>High Risk</option>
                                <option value="medium" {{ ($riskLevel ?? '') === 'medium' ? 'selected' : '' }}>Medium Risk</option>
                                <option value="low" {{ ($riskLevel ?? '') === 'low' ? 'selected' : '' }}>Low Risk</option>
                            </select>
                        </div>

                        {{-- Age Tier --}}
                        <div>
                            <label for="sec_age_range" class="form-label text-muted text-uppercase fw-bold mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">
                                Age Tier
                            </label>
                            <select name="age_range" id="sec_age_range" class="form-select form-select-sm" style="height:42px; border-radius:10px; border:1px solid var(--color-border); font-size:0.88rem;">
                                <option value="all" {{ ($ageRange ?? 'all') === 'all' ? 'selected' : '' }}>All Ages</option>
                                <option value="teen" {{ ($ageRange ?? '') === 'teen' || ($ageRange ?? '') === 'under_20' ? 'selected' : '' }}>⚠️ Adolescent (&lt;19)</option>
                                <option value="20_34" {{ ($ageRange ?? '') === '20_34' ? 'selected' : '' }}>20 to 34 yrs</option>
                                <option value="35_plus" {{ ($ageRange ?? '') === '35_plus' ? 'selected' : '' }}>35+ yrs (Advanced)</option>
                            </select>
                        </div>

                        {{-- Trimester --}}
                        <div>
                            <label for="sec_trimester" class="form-label text-muted text-uppercase fw-bold mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">
                                Trimester
                            </label>
                            <select name="trimester" id="sec_trimester" class="form-select form-select-sm" style="height:42px; border-radius:10px; border:1px solid var(--color-border); font-size:0.88rem;">
                                <option value="all" {{ ($trimester ?? 'all') === 'all' ? 'selected' : '' }}>All Trimesters</option>
                                <option value="1" {{ ($trimester ?? '') === '1' ? 'selected' : '' }}>1st Trimester (Weeks 1–13)</option>
                                <option value="2" {{ ($trimester ?? '') === '2' ? 'selected' : '' }}>2nd Trimester (Weeks 14–26)</option>
                                <option value="3" {{ ($trimester ?? '') === '3' ? 'selected' : '' }}>3rd Trimester (Weeks 27+)</option>
                            </select>
                        </div>

                        {{-- Pregnancy Status --}}
                        <div>
                            <label for="sec_pregnancy_status" class="form-label text-muted text-uppercase fw-bold mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">
                                Pregnancy Status
                            </label>
                            <select name="pregnancy_status" id="sec_pregnancy_status" class="form-select form-select-sm" style="height:42px; border-radius:10px; border:1px solid var(--color-border); font-size:0.88rem;">
                                <option value="all" {{ ($pregnancyStatus ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="pregnant" {{ ($pregnancyStatus ?? '') === 'pregnant' ? 'selected' : '' }}>Pregnant</option>
                                <option value="not_pregnant" {{ ($pregnancyStatus ?? '') === 'not_pregnant' ? 'selected' : '' }}>Not Pregnant</option>
                            </select>
                        </div>
                    </div>

                    {{-- Popover Action Buttons --}}
                    <div class="d-flex align-items-center justify-content-between pt-3 mt-3 border-top gap-2" style="border-color:var(--color-border) !important;">
                        <button type="button" class="btn btn-sm btn-light border px-3" onclick="resetSecondaryFilters()" style="height:38px; border-radius:10px; font-weight:600; color:var(--color-text-muted);">
                            Reset
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary px-4" style="height:38px; border-radius:10px; font-weight:700;">
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Unified Patient Directory Table Card with Summary Pills in Header --}}
<div class="card fade-in-card directory-card-wrapper mb-4" style="border:none; border-radius:24px; box-shadow:var(--wp-shadow-sm); overflow:hidden; position:relative !important; z-index:10 !important;">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-3 px-4" style="background:var(--color-surface); background-color:var(--color-surface); border:none;">
        <h5 class="mb-0 fw-bold" style="color:var(--color-text); font-family:'Plus Jakarta Sans',sans-serif; font-size:1.05rem;">
            Patient Directory
        </h5>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background:var(--color-success-soft); background-color:var(--color-success-soft); border:none; color:var(--color-success-text); font-size:0.78rem;">
                <i class="bi bi-calendar-check-fill me-1"></i> Scheduled Checkups: {{ $scheduledCheckups }}
            </span>
            <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background:var(--color-danger-soft); background-color:var(--color-danger-soft); border:none; color:var(--color-danger-text); font-size:0.78rem;">
                <i class="bi bi-exclamation-circle-fill me-1"></i> Missed: {{ $missedCheckups }}
            </span>
            @if(request('search'))
                <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background:var(--color-peach-soft); border:1px solid var(--color-peach-soft); color:var(--color-peach-text); font-size:0.8rem;">
                    <i class="bi bi-search me-1"></i> Results for "{{ request('search') }}"
                </span>
            @endif
        </div>
    </div>
    <div class="card-body p-0">
        @if($patients->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sticky-head mb-0" id="womenTable" style="border:none;">
                    <thead>
                        <tr class="text-uppercase" style="font-size:0.74rem; font-weight:800; color:var(--color-text-muted); border:none;">
                            <th class="ps-4 py-3" style="border:none;">Woman</th>
                            <th class="py-3" style="border:none;">Type</th>
                            <th class="py-3" style="border:none;">Contact Number</th>
                            <th class="py-3" style="border:none;">Barangay</th>
                            <th class="py-3" style="border:none;">Pregnancy &amp; Risk Level</th>
                            <th class="text-end pe-4 py-3" style="border:none;">Clinical Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patients as $patient)
                            @php
                                $isWalkIn = $patient->type === 'unregistered';
                                $name = $isWalkIn ? $patient->full_name : $patient->name;
                                $contact = $isWalkIn ? ($patient->contact_number ?? null) : ($patient->contact_number ?? $patient->phone ?? null);
                                $activePreg = $patient->pregnancies?->first();
                                $latestRecord = $patient->healthRecords?->first();
                                $risk = strtolower($activePreg?->risk_level ?? $latestRecord?->risk_level ?? 'low');
                                $isTeen = method_exists($patient, 'isTeenage') ? $patient->isTeenage() : ($patient->age !== null && $patient->age < 19);
                                $badgeClass = match($risk) {
                                    'critical', 'high' => 'badge-critical',
                                    'medium'           => 'badge-warning',
                                    default            => 'badge-success',
                                };
                            @endphp
                            <tr style="border:none;">
                                <td class="ps-4 py-3" style="border:none;">
                                    <div class="women-table-name">
                                        <x-patient-avatar :patient="$patient" :name="$name" :size="42" />
                                        <div>
                                            <div class="women-name">
                                                {{ $name }}
                                                @if($isTeen)
                                                    <span class="badge" style="background:var(--badge-critical-bg); color:var(--badge-critical-text); font-size:0.68rem; font-weight:700;">
                                                        Teen &lt;19
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="women-sub">Age: {{ $patient->age ? $patient->age . ' yrs' : 'N/A' }} · ID: #{{ $patient->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3" style="border:none;">
                                    <span class="women-type-badge {{ $isWalkIn ? 'walk-in' : 'registered' }}">
                                        <i class="bi {{ $isWalkIn ? 'bi-person-walking' : 'bi-person-check-fill' }}"></i>
                                        {{ $isWalkIn ? 'Unlinked' : 'Enrolled' }}
                                    </span>
                                </td>
                                <td class="py-3" style="border:none; color:var(--color-text); font-weight:600;">{{ $contact ?: '—' }}</td>
                                <td class="py-3" style="border:none;">
                                    {{-- Truncated Barangay to prevent row height breaking --}}
                                    <div class="fw-600 text-truncate" style="max-width:180px;" title="{{ $patient->barangay ? 'Brgy. ' . $patient->barangay : 'San Carlos City' }}">
                                        {{ $patient->barangay ? 'Brgy. ' . $patient->barangay : 'San Carlos City' }}
                                    </div>
                                    @if($patient->purok?->name)
                                        <div class="women-sub text-truncate" style="max-width:180px;" title="{{ $patient->purok->name }}">{{ $patient->purok->name }}</div>
                                    @endif
                                </td>
                                <td class="py-3" style="border:none;">
                                    @if($activePreg)
                                        <div>
                                            <span class="pill-badge {{ $badgeClass }}">{{ ucfirst($risk) }} Risk</span>
                                        </div>
                                        <div class="text-muted text-xs mt-1">
                                            🤰 {{ $activePreg->aog_weeks ?? 0 }} wks AOG (EDD: {{ $activePreg->expected_delivery_date ? \Carbon\Carbon::parse($activePreg->expected_delivery_date)->format('M d') : 'N/A' }})
                                        </div>
                                    @else
                                        <span class="women-sub">Non-pregnant / Postpartum</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4 py-3" style="border:none;">
                                    <div class="d-inline-flex align-items-center gap-1.5">
                                        <a href="{{ $isWalkIn ? route('midwife.walk-in-patients.show', $patient->id) : route('midwife.patient-details', $patient->id) }}"
                                           class="btn btn-sm"
                                           style="border:none; border-radius:999px; padding:0.45rem 1.1rem; font-size:0.8rem; font-weight:800; background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid);"
                                           title="Review Patient Record">
                                            <i class="bi bi-eye-fill me-1"></i> Review
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center" style="padding:1.25rem;">
                {{ $patients->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="empty-state p-5 text-center">
                <i class="bi bi-people empty-state-icon" style="font-size:2.5rem; color:var(--color-primary-text);"></i>
                <h6 class="mt-2 fw-bold" style="color:var(--color-text);">No Women Found</h6>
                <p class="text-muted">
                    @if(request('search'))
                        No women match "{{ request('search') }}".
                        <a href="{{ route('midwife.patients', ['filter' => request('filter', 'all')]) }}">Clear search</a>
                    @else
                        No {{ request('filter') === 'registered' ? 'enrolled' : (request('filter') === 'unregistered' ? 'unlinked' : '') }} women found.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetSecondaryFilters() {
    var risk = document.getElementById('sec_risk_level');
    var age = document.getElementById('sec_age_range');
    var trim = document.getElementById('sec_trimester');
    var preg = document.getElementById('sec_pregnancy_status');
    if (risk) risk.value = 'all';
    if (age) age.value = 'all';
    if (trim) trim.value = 'all';
    if (preg) preg.value = 'all';
    document.getElementById('womenFilterForm').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('womenSearchInput');
    var womenTable = document.getElementById('womenTable');
    var searchDebounceTimer = null;

    if (searchInput && womenTable) {
        // Live client-side instant filtering on current visible rows
        searchInput.addEventListener('input', function() {
            var query = this.value.trim().toLowerCase();
            var rows = womenTable.querySelectorAll('tbody tr:not(#noLiveMatchRow)');
            var visibleCount = 0;

            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                if (!query || text.indexOf(query) !== -1) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            var noMatchRow = document.getElementById('noLiveMatchRow');
            if (visibleCount === 0 && query) {
                if (!noMatchRow) {
                    noMatchRow = document.createElement('tr');
                    noMatchRow.id = 'noLiveMatchRow';
                    noMatchRow.innerHTML = '<td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-search me-1"></i> No matching women found on this page for "' + query + '"</td>';
                    womenTable.querySelector('tbody').appendChild(noMatchRow);
                }
                noMatchRow.style.display = '';
            } else if (noMatchRow) {
                noMatchRow.style.display = 'none';
            }

            // Debounced backend submission to update server pagination
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(function() {
                document.getElementById('womenFilterForm').submit();
            }, 500);
        });

        // Submit immediately on Enter
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchDebounceTimer);
                document.getElementById('womenFilterForm').submit();
            }
        });
    }
});
</script>
@endpush
