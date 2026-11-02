@extends('midwife.layout')

@section('title', 'Women - Midwife Portal | ReproCare')

@push('styles')
@include('includes.women-directory-styles')
@endpush

@section('midwife-content')
<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Women</div>
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
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 fade-in-card">
        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $scope = request('filter', 'all');
    $scopeKeep = request()->except(['page', 'filter']);
    $scopeUrl = fn (string $value) => route('midwife.patients', array_merge($scopeKeep, ['filter' => $value]));
    $clearUrl = route('midwife.patients', ['filter' => $scope]);

    $secActiveCount = 0;
    if (!empty($riskLevel) && $riskLevel !== 'all') $secActiveCount++;
    if (!empty($ageRange) && $ageRange !== 'all') $secActiveCount++;
    if (!empty($trimester) && $trimester !== 'all') $secActiveCount++;
    if (!empty($pregnancyStatus) && $pregnancyStatus !== 'all') $secActiveCount++;
    $filtersActive = $secActiveCount > 0 || request('search') || request('barangay');
    $bList = $barangays;
@endphp

{{-- Summary cards --}}
<div class="row g-3 mb-4 wd-mini">
    <div class="col-6 col-md-4 col-lg">
        <a href="{{ $scopeUrl('all') }}" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $scope === 'all' ? 'is-active' : '' }}">
                <i class="bi bi-people-fill text-primary mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">All Women</div>
                <h4 class="fw-800 text-primary mb-0 mt-1">{{ $patients->total() }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <a href="{{ $scopeUrl('registered') }}" class="text-decoration-none" title="Women with a portal account">
            <div class="card fade-in-card p-3 text-center h-100 {{ $scope === 'registered' ? 'is-active' : '' }}">
                <i class="bi bi-person-check-fill text-success mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Enrolled</div>
                <h4 class="fw-800 text-success mb-0 mt-1">{{ $registeredCount }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <a href="{{ $scopeUrl('unregistered') }}" class="text-decoration-none" title="Field records with no portal account">
            <div class="card fade-in-card p-3 text-center h-100 {{ $scope === 'unregistered' ? 'is-active' : '' }}">
                <i class="bi bi-person-badge-fill text-warning mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Unlinked</div>
                <h4 class="fw-800 text-warning mb-0 mt-1">{{ $unregisteredCount }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="card fade-in-card p-3 text-center h-100 is-static">
            <i class="bi bi-calendar-check-fill text-info mb-1" style="font-size:1.4rem;"></i>
            <div class="text-muted" style="font-size:0.75rem;">Scheduled Checkups</div>
            <h4 class="fw-800 text-info mb-0 mt-1">{{ $scheduledCheckups }}</h4>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="card fade-in-card p-3 text-center h-100 is-static">
            <i class="bi bi-exclamation-circle-fill text-danger mb-1" style="font-size:1.4rem;"></i>
            <div class="text-muted" style="font-size:0.75rem;">Missed</div>
            <h4 class="fw-800 text-danger mb-0 mt-1">{{ $missedCheckups }}</h4>
        </div>
    </div>
</div>

<div class="card fade-in-card wd-wrap">
    <div class="card-header bg-transparent p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <ul class="wd-tabs" role="tablist">
                <li>
                    <a class="wd-tab {{ $scope === 'all' ? 'active' : '' }}" href="{{ $scopeUrl('all') }}">
                        <i class="bi bi-people-fill"></i> All Women ({{ $patients->total() }})
                    </a>
                </li>
                <li>
                    <a class="wd-tab {{ $scope === 'registered' ? 'active' : '' }}" href="{{ $scopeUrl('registered') }}">
                        <i class="bi bi-person-check-fill"></i> Enrolled ({{ $registeredCount }})
                    </a>
                </li>
                <li>
                    <a class="wd-tab {{ $scope === 'unregistered' ? 'active' : '' }}" href="{{ $scopeUrl('unregistered') }}">
                        <i class="bi bi-person-badge-fill"></i> Unlinked ({{ $unregisteredCount }})
                    </a>
                </li>
            </ul>

            <form method="GET" action="{{ route('midwife.patients') }}" id="womenFilterForm" class="wd-filters">
                <input type="hidden" name="filter" value="{{ $scope }}">

                <div class="wd-field wd-search-field">
                    <label for="womenSearchInput">Search</label>
                    <div class="wd-search">
                        <input type="search" id="womenSearchInput" name="search" class="form-control"
                               value="{{ request('search') }}" placeholder="Name, ID, contact..." autocomplete="off">
                        <button class="btn" type="submit" title="Search"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <div class="wd-field wd-select-field">
                    <label for="filterBarangay">Barangay</label>
                    <select id="filterBarangay" name="barangay" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Barangays</option>
                        @foreach ($bList as $b)
                            <option value="{{ $b }}" {{ request('barangay') === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="wd-field wd-select-field">
                    <label for="sec_risk_level">Risk Tier</label>
                    <select id="sec_risk_level" name="risk_level" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ ($riskLevel ?? 'all') === 'all' ? 'selected' : '' }}>All risk tiers</option>
                        <option value="high_risk_only" {{ ($riskLevel ?? '') === 'high_risk_only' ? 'selected' : '' }}>High risk only</option>
                        <option value="critical" {{ ($riskLevel ?? '') === 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="high" {{ ($riskLevel ?? '') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ ($riskLevel ?? '') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ ($riskLevel ?? '') === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>

                <div class="wd-field wd-select-field">
                    <label for="sec_age_range">Age Tier</label>
                    <select id="sec_age_range" name="age_range" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ ($ageRange ?? 'all') === 'all' ? 'selected' : '' }}>All ages</option>
                        <option value="teen" {{ ($ageRange ?? '') === 'teen' || ($ageRange ?? '') === 'under_20' ? 'selected' : '' }}>Adolescent (&lt;19)</option>
                        <option value="20_34" {{ ($ageRange ?? '') === '20_34' ? 'selected' : '' }}>20 to 34 yrs</option>
                        <option value="35_plus" {{ ($ageRange ?? '') === '35_plus' ? 'selected' : '' }}>35+ yrs</option>
                    </select>
                </div>

                <div class="wd-field wd-select-field">
                    <label for="sec_trimester">Trimester</label>
                    <select id="sec_trimester" name="trimester" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ ($trimester ?? 'all') === 'all' ? 'selected' : '' }}>All trimesters</option>
                        <option value="1" {{ ($trimester ?? '') === '1' ? 'selected' : '' }}>1st (Weeks 1–13)</option>
                        <option value="2" {{ ($trimester ?? '') === '2' ? 'selected' : '' }}>2nd (Weeks 14–26)</option>
                        <option value="3" {{ ($trimester ?? '') === '3' ? 'selected' : '' }}>3rd (Weeks 27+)</option>
                    </select>
                </div>

                <div class="wd-field wd-select-field">
                    <label for="sec_pregnancy_status">Pregnancy</label>
                    <select id="sec_pregnancy_status" name="pregnancy_status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ ($pregnancyStatus ?? 'all') === 'all' ? 'selected' : '' }}>All status</option>
                        <option value="pregnant" {{ ($pregnancyStatus ?? '') === 'pregnant' ? 'selected' : '' }}>Pregnant</option>
                        <option value="not_pregnant" {{ ($pregnancyStatus ?? '') === 'not_pregnant' ? 'selected' : '' }}>Not pregnant</option>
                    </select>
                </div>

                <div class="wd-actions d-flex gap-2">
                    <button type="submit" class="btn btn-sm wd-apply-btn">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if ($filtersActive)
                        <a href="{{ $clearUrl }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                            <i class="bi bi-x-lg me-1"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 border-bottom">
            <span class="wd-count">
                Showing <strong>{{ $patients->count() }}</strong> of <strong>{{ $patients->total() }}</strong>
                {{ Str::plural('woman', $patients->total()) }}
                @if ($scope === 'registered')
                    <span class="text-muted">· enrolled only</span>
                @elseif ($scope === 'unregistered')
                    <span class="text-muted">· unlinked only</span>
                @endif
                @if (request('search'))
                    <span class="text-muted">· results for "{{ request('search') }}"</span>
                @endif
                @if ($secActiveCount > 0)
                    <span class="text-muted">· {{ $secActiveCount }} {{ Str::plural('filter', $secActiveCount) }} active</span>
                @endif
            </span>
            @if ($filtersActive)
                <a href="{{ $clearUrl }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset filters
                </a>
            @endif
        </div>

        @if($patients->count() > 0)
            <div class="table-responsive" style="overflow:visible;">
                <table class="table table-hover align-middle table-sticky-head mb-0" id="womenTable">
                    <thead>
                        <tr>
                            <th class="px-3 py-3">Woman</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Contact Number</th>
                            <th class="py-3">Barangay</th>
                            <th class="py-3">Pregnancy &amp; Risk Level</th>
                            <th class="text-center px-3 py-3">Clinical Actions</th>
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
                            <tr>
                                <td class="px-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <x-patient-avatar :patient="$patient" :name="$name" :size="34" />
                                        <div style="min-width:0;">
                                            <div class="wd-name">
                                                {{ $name }}
                                                @if($isTeen)
                                                    <span class="wd-pill" style="background:var(--badge-critical-bg); color:var(--badge-critical-text);">Teen &lt;19</span>
                                                @endif
                                            </div>
                                            <div class="wd-sub">Age: {{ $patient->age ? $patient->age . ' yrs' : 'N/A' }} · ID: #{{ $patient->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="wd-pill {{ $isWalkIn ? 'wd-pill-unregistered' : 'wd-pill-registered' }}">
                                        <i class="bi {{ $isWalkIn ? 'bi-person-walking' : 'bi-person-check' }}"></i>
                                        {{ $isWalkIn ? 'Unlinked' : 'Enrolled' }}
                                    </span>
                                </td>
                                <td style="white-space:nowrap;">{{ $contact ?: '—' }}</td>
                                <td class="wd-cell-wrap">
                                    <div class="text-truncate" style="max-width:170px;" title="{{ $patient->barangay ? 'Brgy. ' . $patient->barangay : 'San Carlos City' }}">
                                        {{ $patient->barangay ? 'Brgy. ' . $patient->barangay : 'San Carlos City' }}
                                    </div>
                                    @if($patient->purok?->name)
                                        <div class="wd-sub text-truncate" style="max-width:170px;" title="{{ $patient->purok->name }}">{{ $patient->purok->name }}</div>
                                    @endif
                                </td>
                                <td class="wd-cell-wrap">
                                    @if($activePreg)
                                        <span class="pill-badge {{ $badgeClass }}">{{ ucfirst($risk) }} Risk</span>
                                        <div class="wd-sub mt-1">
                                            <i class="bi bi-heart-pulse me-1"></i>{{ $activePreg->aog_weeks ?? 0 }} wks AOG (EDD: {{ $activePreg->expected_delivery_date ? \Carbon\Carbon::parse($activePreg->expected_delivery_date)->format('M d') : 'N/A' }})
                                        </div>
                                    @else
                                        <span class="wd-sub">Non-pregnant / Postpartum</span>
                                    @endif
                                </td>
                                <td class="text-center px-3">
                                    <a href="{{ $isWalkIn ? route('midwife.walk-in-patients.show', $patient->id) : route('midwife.patient-details', $patient->id) }}"
                                       class="btn btn-sm btn-outline-primary wd-action-btn" title="Review Patient Record">
                                        <i class="bi bi-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3">{{ $patients->links('vendor.pagination.arrows-only') }}</div>
        @else
            <div class="empty-state p-5 text-center">
                <i class="bi bi-people empty-state-icon" style="font-size:2.5rem;"></i>
                <h6 class="mt-2 fw-bold">No Women Found</h6>
                <p class="text-muted">
                    @if(request('search'))
                        No women match "{{ request('search') }}".
                        <a href="{{ $clearUrl }}">Clear search</a>
                    @else
                        No {{ $scope === 'registered' ? 'enrolled' : ($scope === 'unregistered' ? 'unlinked' : '') }} women found.
                    @endif
                    @if($scope !== 'all')
                        <a href="{{ $scopeUrl('all') }}">View all women</a>
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('womenSearchInput');
    var filterForm = document.getElementById('womenFilterForm');
    var womenTable = document.getElementById('womenTable');
    var searchDebounceTimer = null;

    if (!searchInput || !filterForm || !womenTable) return;

    // Live client-side instant filtering on current visible rows
    searchInput.addEventListener('input', function() {
        var query = this.value.trim().toLowerCase();
        var rows = womenTable.querySelectorAll('tbody tr:not(#noLiveMatchRow)');
        var visibleCount = 0;

        rows.forEach(function(row) {
            if (!query || row.textContent.toLowerCase().indexOf(query) !== -1) {
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
            filterForm.submit();
        }, 500);
    });

    // Submit immediately on Enter
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchDebounceTimer);
            filterForm.submit();
        }
    });
});
</script>
@endpush
