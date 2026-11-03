@extends($archiveLayout ?? 'cho.layout')

@section('title', $archiveTitle ?? 'Archived Records Hub - CHO Admin | ReproCare')

@push('styles')
@include('includes.women-directory-styles')
@include('includes.report-queue-styles')
<style>
    /* The archive hub is one queue with six groups, so the group switcher and
       the date filter both live in the single card header. Six metric cards
       above it used to repeat exactly what the tabs already say. */
    .arch-head { display:grid; gap:0.55rem; }
    .arch-head .wd-tab { padding:0.34rem 0.8rem !important; font-size:0.74rem !important; }
    body .main-content .arch-store .table :is(thead th, tbody td) { white-space:normal; }
    .arch-bulk { display:flex; flex-wrap:wrap; align-items:center; gap:0.45rem 0.7rem; padding:0.4rem 0.8rem; border-bottom:1px solid var(--color-border); font-size:0.74rem; }
    .arch-bulk .arch-count { font-weight:800; color:var(--color-text); white-space:nowrap; }
    .arch-bulk .btn { border-radius:999px; font-weight:800; font-size:0.72rem; }
    .arch-restore { display:inline-flex; align-items:center; justify-content:center; width:30px; height:29px; padding:0 !important; border-radius:9px; background:var(--color-success-soft); color:var(--color-success-text); border:1px solid transparent; }
    .arch-restore:hover { background:var(--color-success); color:var(--color-on-solid); }
    .arch-check { width:34px; }
    .arch-note { font-size:0.7rem; color:var(--color-text-muted); line-height:1.4; }
    /* The shield is a state, not a count, so it keeps the chip shape without
       the number the shared chip markup expects. */
    .arch-shield { display:inline-flex; align-items:center; gap:0.32rem; padding:0.2rem 0.62rem; border-radius:999px; background:var(--color-success-soft); color:var(--color-success-text); font-size:0.72rem; font-weight:700; white-space:nowrap; }
</style>
@endpush

@section($archiveSection ?? 'cho-content')

@php
    $filtersActive = filled($search) || filled($dateFrom) || filled($dateTo);
    // An unknown ?tab= still lists patients, so the highlighted tab, the bulk
    // form's type and the Clear link can never disagree with the rows.
    $activeTab = match ($activeTab) {
        'staff', 'health_records', 'materials', 'pregnancies', 'supplies' => $activeTab,
        default => 'patients',
    };
    $isChoArchive = str_starts_with($archiveRestoreRoute ?? '', 'cho.');
    $bulkType = match($activeTab) {
        'patients' => 'patient',
        'staff' => 'staff',
        'health_records' => 'health-record',
        'materials' => 'learning-material',
        'pregnancies' => 'pregnancy',
        'supplies' => 'supply-request',
        default => 'patient',
    };
    // Group switcher keeps the active search and dates, the filter form keeps
    // the active group — neither control can silently reset the other.
    $keepQuery = array_filter(['search' => $search, 'date_from' => $dateFrom, 'date_to' => $dateTo], fn ($value) => filled($value));
    $tabUrl = fn (string $tab) => '?' . http_build_query($keepQuery + ['tab' => $tab]);
    $groups = [
        'patients' => ['label' => 'Patients', 'icon' => 'bi-people', 'count' => $stats['patients']],
        'staff' => ['label' => 'Staff', 'icon' => 'bi-person-badge', 'count' => $stats['staff']],
        'health_records' => ['label' => 'Medical records', 'icon' => 'bi-file-earmark-medical', 'count' => $stats['health_records']],
        'materials' => ['label' => 'Videos & learning', 'icon' => 'bi-play-circle', 'count' => $stats['learning_materials']],
        'pregnancies' => ['label' => 'Pregnancies', 'icon' => 'bi-heart-pulse', 'count' => $stats['pregnancies']],
        'supplies' => ['label' => 'Supplies', 'icon' => 'bi-box-seam', 'count' => $stats['supply_requests']],
    ];
    $totalArchived = array_sum(array_column($groups, 'count'));
    // Only the file-backed groups add anything the tabs do not already count.
    $fileGroups = array_values(array_filter($storageStats, fn ($row) => ($row['bytes'] ?? null) !== null));
    $rowOnlyGroups = array_values(array_filter($storageStats, fn ($row) => ($row['bytes'] ?? null) === null));
    $rows = match($activeTab) {
        'staff' => $staff,
        'health_records' => $healthRecords,
        'materials' => $learningMaterials,
        'pregnancies' => $pregnancies,
        'supplies' => $supplyRequests,
        default => $patients,
    };
    $emptyCopy = match($activeTab) {
        'staff' => ['bi-person-x', 'No archived staff accounts', 'Archived midwives, BHW presidents and BHWs appear here.'],
        'health_records' => ['bi-file-earmark-medical', 'No archived health records', 'Records deleted from a patient file appear here.'],
        'materials' => ['bi-camera-video-off', 'No archived videos or materials', 'Retired learning materials appear here.'],
        'pregnancies' => ['bi-heartbreak', 'No archived pregnancies', 'Closed or withdrawn pregnancy records appear here.'],
        'supplies' => ['bi-box', 'No archived supply requests', 'Deleted supply requests appear here.'],
        default => ['bi-archive', 'No archived patients', 'Soft-deleted patient accounts appear here.'],
    };
    $hint = $filtersActive ? 'Nothing in this group matches the current search or date range.' : $emptyCopy[2];
@endphp

<div class="rq-bar fade-in-card mb-2">
    <div class="rq-bar-id">
        <div class="rq-bar-title"><i class="bi bi-archive" aria-hidden="true"></i>Archived Records &amp; Data Integrity</div>
        <p class="rq-bar-subtitle">Every soft-deleted record in one place. Restore from here; nothing is deleted automatically.</p>
    </div>
    <div class="rq-chips">
        <span class="summary-chip chip-primary rq-chip"><b>{{ number_format($totalArchived) }}</b>archived</span>
        <span class="arch-shield"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i>Zero hard deletes</span>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-2">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-2">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card rq-wrap fade-in-card">
    <div class="card-header bg-transparent arch-head">
        <ul class="wd-tabs" role="tablist" aria-label="Archived record groups">
            @foreach ($groups as $key => $group)
                <li>
                    <a class="wd-tab {{ $activeTab === $key ? 'active' : '' }}" href="{{ $tabUrl((string) $key) }}"
                       role="tab" @if ($activeTab === $key) aria-current="true" @endif>
                        <i class="bi {{ $group['icon'] }}" aria-hidden="true"></i>{{ $group['label'] }} ({{ number_format($group['count']) }})
                    </a>
                </li>
            @endforeach
        </ul>

        <form method="GET" class="wd-filters" role="search">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div class="wd-field wd-search-field">
                <label for="arch-search">Search this group</label>
                <div class="wd-search">
                    <input type="search" id="arch-search" name="search" class="form-control" autocomplete="off"
                           value="{{ $search }}" placeholder="Name, ID, barangay...">
                    <button class="btn" type="submit" title="Search"><i class="bi bi-search"></i></button>
                </div>
            </div>
            <div class="wd-field wd-date-field">
                <label for="arch-from">Archived from</label>
                <input type="date" id="arch-from" name="date_from" class="form-control form-control-sm"
                       value="{{ $dateFrom ?? '' }}" max="{{ date('Y-m-d') }}">
            </div>
            <div class="wd-field wd-date-field">
                <label for="arch-to">to</label>
                <input type="date" id="arch-to" name="date_to" class="form-control form-control-sm"
                       value="{{ $dateTo ?? '' }}" max="{{ date('Y-m-d') }}">
            </div>
            <div class="wd-actions d-flex gap-2">
                <button type="submit" class="btn btn-sm wd-apply-btn"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ '?tab=' . $activeTab }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                    <i class="bi bi-x-lg me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if ($rows->isNotEmpty())
            <form method="POST" action="{{ $isChoArchive ? route('cho.archived.bulk-restore') : route('rhu.archived.bulk-restore') }}"
                 id="archive-bulk-form" class="arch-bulk">
                @csrf
                <input type="hidden" name="type" value="{{ $bulkType }}">
                <span class="arch-count"><span id="archive-selected-count">0</span> selected</span>
                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirmBulkRestore()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Restore selected
                </button>
                @if ($isChoArchive)
                    <button type="submit" class="btn btn-sm btn-outline-danger"
                            formaction="{{ route('cho.archived.bulk-delete') }}" onclick="return confirmBulkDelete()">
                        <i class="bi bi-trash3 me-1"></i>Delete permanently
                    </button>
                @endif
                <span class="arch-note ms-auto">Permanent delete skips patient, pregnancy and health records.</span>
            </form>
        @endif

        {{-- TAB 1: ARCHIVED PATIENTS --}}
        @if ($activeTab === 'patients')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="arch-check"><input type="checkbox" class="archive-select-all" title="Select all on this page" aria-label="Select all patients on this page"></th>
                            <th>Patient</th>
                            <th>Contact</th>
                            <th>Barangay</th>
                            <th>Archived on</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patients as $patient)
                            <tr>
                                <td><input type="checkbox" class="archive-select" name="ids[]" value="{{ $patient->id }}" form="archive-bulk-form" aria-label="Select {{ $patient->name }}"></td>
                                <td>
                                    <div class="rq-truncate rq-title" title="{{ $patient->name }}">{{ $patient->name }}</div>
                                    <div class="rq-soft">#{{ $patient->id }}</div>
                                </td>
                                <td>
                                    <div>{{ $patient->contact_number ?? '—' }}</div>
                                    <div class="rq-soft">{{ $patient->email }}</div>
                                </td>
                                <td><span class="rq-truncate" title="{{ $patient->barangay }}">{{ $patient->barangay ?? '—' }}</span></td>
                                <td>
                                    <div>{{ $patient->deleted_at?->format('M j, Y') ?? '—' }}</div>
                                    <div class="rq-soft">{{ $patient->deleted_at?->format('g:i A') }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="rq-actions">
                                        <form method="POST" action="{{ route($archiveRestoreRoute ?? 'cho.archived.restore', ['type' => 'patient', 'id' => $patient->id]) }}"
                                              data-confirm="Restore patient record for {{ $patient->name }}?">
                                            @csrf
                                            <button type="submit" class="btn arch-restore" title="Restore this patient" aria-label="Restore {{ $patient->name }}">
                                                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($patients->hasPages())
                <div class="rq-foot">{{ $patients->links('vendor.pagination.arrows-only') }}</div>
            @endif
        @endif

        {{-- TAB 2: ARCHIVED STAFF --}}
        @if ($activeTab === 'staff')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="arch-check"><input type="checkbox" class="archive-select-all" title="Select all on this page" aria-label="Select all staff on this page"></th>
                            <th>Staff</th>
                            <th>Role</th>
                            <th>Email / contact</th>
                            <th>Archived on</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($staff as $member)
                            <tr>
                                <td><input type="checkbox" class="archive-select" name="ids[]" value="{{ $member->id }}" form="archive-bulk-form" aria-label="Select {{ $member->name }}"></td>
                                <td>
                                    <div class="rq-truncate rq-title" title="{{ $member->name }}">{{ $member->name }}</div>
                                    <div class="rq-soft">#{{ $member->id }}</div>
                                </td>
                                <td><span class="summary-chip chip-primary rq-status">{{ str_replace('_', ' ', ucfirst($member->role)) }}</span></td>
                                <td>
                                    <div class="rq-truncate" title="{{ $member->email }}">{{ $member->email }}</div>
                                    <div class="rq-soft">{{ $member->contact_number ?? '—' }}</div>
                                </td>
                                <td>
                                    <div>{{ $member->deleted_at?->format('M j, Y') ?? '—' }}</div>
                                    <div class="rq-soft">{{ $member->deleted_at?->format('g:i A') }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="rq-actions">
                                        <form method="POST" action="{{ route($archiveRestoreRoute ?? 'cho.archived.restore', ['type' => 'staff', 'id' => $member->id]) }}"
                                              data-confirm="Restore staff account for {{ $member->name }}?">
                                            @csrf
                                            <button type="submit" class="btn arch-restore" title="Restore this account" aria-label="Restore {{ $member->name }}">
                                                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($staff->hasPages())
                <div class="rq-foot">{{ $staff->links('vendor.pagination.arrows-only') }}</div>
            @endif
        @endif

        {{-- TAB 3: HEALTH RECORDS --}}
        @if ($activeTab === 'health_records')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="arch-check"><input type="checkbox" class="archive-select-all" title="Select all on this page" aria-label="Select all records on this page"></th>
                            <th>Record / patient</th>
                            <th>Vitals</th>
                            <th>Risk</th>
                            <th>Archived on</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($healthRecords as $record)
                            @php $riskTone = match (strtolower($record->risk_level ?? 'low')) {
                                'critical', 'high' => 'danger',
                                'medium' => 'warning',
                                default => 'success',
                            }; @endphp
                            <tr>
                                <td><input type="checkbox" class="archive-select" name="ids[]" value="{{ $record->id }}" form="archive-bulk-form" aria-label="Select record {{ $record->id }}"></td>
                                <td>
                                    <div class="rq-truncate rq-title" title="{{ $record->user?->name ?? 'Patient' }}">#{{ $record->id }} · {{ $record->user?->name ?? 'Patient' }}</div>
                                </td>
                                <td>
                                    <div>BP {{ $record->bp ?? '—' }} · Hb {{ $record->hemoglobin ?? '—' }}</div>
                                    <div class="rq-soft">{{ $record->weight ? $record->weight.' kg' : 'Weight unrecorded' }}</div>
                                </td>
                                <td><span class="summary-chip chip-{{ $riskTone }} rq-status">{{ ucfirst($record->risk_level ?? 'Low') }}</span></td>
                                <td>{{ $record->deleted_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="rq-actions">
                                        <form method="POST" action="{{ route($archiveRestoreRoute ?? 'cho.archived.restore', ['type' => 'health-record', 'id' => $record->id]) }}"
                                              data-confirm="Restore health record #{{ $record->id }}?">
                                            @csrf
                                            <button type="submit" class="btn arch-restore" title="Restore this record" aria-label="Restore health record {{ $record->id }}">
                                                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($healthRecords->hasPages())
                <div class="rq-foot">{{ $healthRecords->links('vendor.pagination.arrows-only') }}</div>
            @endif
        @endif

        {{-- TAB 4: VIDEOS / LEARNING MATERIALS --}}
        @if ($activeTab === 'materials')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="arch-check"><input type="checkbox" class="archive-select-all" title="Select all on this page" aria-label="Select all materials on this page"></th>
                            <th>Material</th>
                            <th>Type</th>
                            <th>Media source</th>
                            <th>Archived on</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($learningMaterials as $mat)
                            <tr>
                                <td><input type="checkbox" class="archive-select" name="ids[]" value="{{ $mat->id }}" form="archive-bulk-form" aria-label="Select {{ $mat->title }}"></td>
                                <td>
                                    <div class="rq-truncate rq-title" title="{{ $mat->title }}">{{ $mat->title }}</div>
                                    <div class="rq-soft">{{ Str::limit($mat->content, 60) }}</div>
                                </td>
                                <td>
                                    <span class="summary-chip chip-primary rq-status">{{ ucfirst($mat->material_type) }}</span>
                                    <span class="summary-chip chip-info rq-status">{{ ucfirst($mat->category ?? 'General') }}</span>
                                </td>
                                <td>
                                    @if ($mat->video_url)
                                        <span class="rq-truncate" title="{{ $mat->video_url }}"><i class="bi bi-camera-video me-1 text-danger" aria-hidden="true"></i>{{ $mat->video_url }}</span>
                                    @elseif ($mat->file)
                                        <span class="rq-truncate" title="{{ basename($mat->file) }}"><i class="bi bi-file-earmark-arrow-down me-1 text-primary" aria-hidden="true"></i>{{ basename($mat->file) }}</span>
                                    @else
                                        <span class="rq-soft">Article / text</span>
                                    @endif
                                </td>
                                <td>{{ $mat->deleted_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="rq-actions">
                                        <form method="POST" action="{{ route($archiveRestoreRoute ?? 'cho.archived.restore', ['type' => 'learning-material', 'id' => $mat->id]) }}"
                                              data-confirm="Restore “{{ $mat->title }}”?">
                                            @csrf
                                            <button type="submit" class="btn arch-restore" title="Restore this material" aria-label="Restore {{ $mat->title }}">
                                                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($learningMaterials->hasPages())
                <div class="rq-foot">{{ $learningMaterials->links('vendor.pagination.arrows-only') }}</div>
            @endif
        @endif

        {{-- TAB 5: PREGNANCIES --}}
        @if ($activeTab === 'pregnancies')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="arch-check"><input type="checkbox" class="archive-select-all" title="Select all on this page" aria-label="Select all pregnancies on this page"></th>
                            <th>Pregnancy / patient</th>
                            <th>EDD / AOG</th>
                            <th>Status</th>
                            <th>Archived on</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pregnancies as $preg)
                            <tr>
                                <td><input type="checkbox" class="archive-select" name="ids[]" value="{{ $preg->id }}" form="archive-bulk-form" aria-label="Select pregnancy {{ $preg->id }}"></td>
                                <td>
                                    <div class="rq-truncate rq-title" title="{{ $preg->user?->name ?? 'Patient' }}">#{{ $preg->id }} · {{ $preg->user?->name ?? 'Patient' }}</div>
                                </td>
                                <td>
                                    <div>{{ $preg->expected_delivery_date ? \Carbon\Carbon::parse($preg->expected_delivery_date)->format('M j, Y') : 'EDD unrecorded' }}</div>
                                    <div class="rq-soft">{{ $preg->aog_weeks ? $preg->aog_weeks.' weeks' : 'AOG unrecorded' }}</div>
                                </td>
                                <td><span class="summary-chip chip-warning rq-status">{{ ucfirst($preg->status ?? 'archived') }}</span></td>
                                <td>{{ $preg->deleted_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="rq-actions">
                                        <form method="POST" action="{{ route($archiveRestoreRoute ?? 'cho.archived.restore', ['type' => 'pregnancy', 'id' => $preg->id]) }}"
                                              data-confirm="Restore pregnancy record #{{ $preg->id }}?">
                                            @csrf
                                            <button type="submit" class="btn arch-restore" title="Restore this pregnancy" aria-label="Restore pregnancy {{ $preg->id }}">
                                                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($pregnancies->hasPages())
                <div class="rq-foot">{{ $pregnancies->links('vendor.pagination.arrows-only') }}</div>
            @endif
        @endif

        {{-- TAB 6: SUPPLIES --}}
        @if ($activeTab === 'supplies')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="arch-check"><input type="checkbox" class="archive-select-all" title="Select all on this page" aria-label="Select all requests on this page"></th>
                            <th>Supply item</th>
                            <th>Requested by</th>
                            <th class="text-end">Qty</th>
                            <th>Archived on</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($supplyRequests as $sup)
                            <tr>
                                <td><input type="checkbox" class="archive-select" name="ids[]" value="{{ $sup->id }}" form="archive-bulk-form" aria-label="Select {{ $sup->supply_name }}"></td>
                                <td>
                                    <div class="rq-truncate rq-title" title="{{ $sup->supply_name }}">{{ $sup->supply_name }}</div>
                                    <div class="rq-soft">{{ ucfirst($sup->supply_category ?? 'General') }}</div>
                                </td>
                                <td><span class="rq-truncate" title="{{ $sup->requestedBy?->name ?? 'Staff' }}">{{ $sup->requestedBy?->name ?? 'Staff' }}</span></td>
                                <td class="text-end rq-num fw-700">{{ number_format($sup->quantity_requested ?? 0) }} {{ $sup->unit ?? 'units' }}</td>
                                <td>{{ $sup->deleted_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="rq-actions">
                                        <form method="POST" action="{{ route($archiveRestoreRoute ?? 'cho.archived.restore', ['type' => 'supply-request', 'id' => $sup->id]) }}"
                                              data-confirm="Restore supply request “{{ $sup->supply_name }}”?">
                                            @csrf
                                            <button type="submit" class="btn arch-restore" title="Restore this request" aria-label="Restore {{ $sup->supply_name }}">
                                                <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($supplyRequests->hasPages())
                <div class="rq-foot">{{ $supplyRequests->links('vendor.pagination.arrows-only') }}</div>
            @endif
        @endif

        @if ($rows->isEmpty())
            <div class="rq-empty">
                <i class="bi {{ $emptyCopy[0] }} text-muted"></i>
                <h6 class="mt-2 mb-1 fw-700">{{ $emptyCopy[1] }}</h6>
                <p class="rq-soft mb-0">{{ $hint }}</p>
            </div>
        @endif
    </div>
</div>

{{-- File storage is the one thing the group tabs cannot show: rows are already
     counted above, so only disk usage keeps its own table. --}}
<div class="card rq-wrap fade-in-card arch-store mt-2">
    <div class="card-header bg-transparent d-flex flex-wrap align-items-center gap-2">
        <span class="rq-bar-title"><i class="bi bi-hdd" aria-hidden="true"></i>File storage</span>
        <span class="arch-note ms-auto">
            {{ count($rowOnlyGroups) }} groups hold database rows only and are never auto-deleted.
            Permanent deletion needs CHO confirmation per record.
        </span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>Group</th><th class="text-end">Archived rows</th><th class="text-end">Files size</th><th>Note</th></tr>
            </thead>
            <tbody>
                @foreach ($fileGroups as $row)
                    <tr>
                        <td class="fw-700">{{ $row['label'] }}</td>
                        <td class="text-end rq-num">{{ number_format($row['rows']) }}</td>
                        <td class="text-end rq-num">{{ $row['bytes'] }}</td>
                        <td class="rq-soft">{{ $row['note'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function selected() {
        return document.querySelectorAll('.archive-select:checked');
    }
    function updateCount() {
        var el = document.getElementById('archive-selected-count');
        if (el) el.textContent = selected().length;
    }
    document.querySelectorAll('.archive-select').forEach(function (box) {
        box.addEventListener('change', updateCount);
    });
    // One strip of checkboxes per tab: the header box mirrors them both ways.
    document.querySelectorAll('.archive-select-all').forEach(function (all) {
        all.addEventListener('change', function () {
            document.querySelectorAll('.archive-select').forEach(function (box) { box.checked = all.checked; });
            updateCount();
        });
    });
    // Confirmation text lives in an attribute so a name containing a quote can
    // never break out of an inline onsubmit handler.
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
    updateCount();
    window.confirmBulkRestore = function () {
        if (selected().length === 0) { alert('Select at least one archived record first.'); return false; }
        return confirm('Restore ' + selected().length + ' selected record(s)?');
    };
    window.confirmBulkDelete = function () {
        if (selected().length === 0) { alert('Select at least one archived record first.'); return false; }
        var reason = prompt('PERMANENT deletion cannot be undone.\nType a reason (min 10 characters) for the audit log:');
        if (reason === null || reason.trim().length < 10) { alert('A reason of at least 10 characters is required. Deletion cancelled.'); return false; }
        var form = document.getElementById('archive-bulk-form');
        var confirmInput = document.createElement('input');
        confirmInput.type = 'hidden'; confirmInput.name = 'confirm'; confirmInput.value = 'PERMANENTLY DELETE';
        form.appendChild(confirmInput);
        var reasonInput = document.createElement('input');
        reasonInput.type = 'hidden'; reasonInput.name = 'reason'; reasonInput.value = reason.trim();
        form.appendChild(reasonInput);
        return confirm('Click OK to PERMANENTLY delete ' + selected().length + ' record(s). Patient, pregnancy and health records will be skipped and retained.');
    };
})();
</script>
@endpush

@endsection
