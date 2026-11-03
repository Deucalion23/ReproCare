@extends('rhu.layout')

@section('title', 'BHW Management - RHU Portal | ReproCare')

@push('styles')
@include('includes.women-directory-styles')
@include('includes.report-queue-styles')
@endpush

@section('rhu-content')

@php
    $filtersActive = filled($search) || filled($status) || filled($barangay);
    // Soft-token tones, not Bootstrap bg-*: the solid badges stay light-on-dark
    // in dark mode.
    $statusMeta = [
        'approved' => ['Approved', 'success'],
        'inactive' => ['Inactive', 'warning'],
        'suspended' => ['Suspended', 'danger'],
        'archived' => ['Archived', 'info'],
    ];
@endphp

@include('includes.report-queue-head', [
    'title' => 'Barangay Health Workers',
    'subtitle' => 'Manage all BHWs under RHU supervision.',
    'icon' => 'bi-people',
    'bar_class' => 'mb-2',
    'chips' => [
        ['label' => ' on roster', 'value' => $statusCounts['total'], 'tone' => 'primary'],
        ['label' => ' approved', 'value' => $statusCounts['approved'], 'tone' => 'success'],
        ['label' => ' inactive', 'value' => $statusCounts['inactive'], 'tone' => 'warning'],
    ],
    'actions' => [
        [
            'href' => route('rhu.staff-transitions.index', ['type' => 'bhw_transfer']),
            'label' => 'Transfer Roster',
            'icon' => 'bi-arrow-left-right',
        ],
    ],
    'action_href' => route('rhu.bhws.create'),
    'action_label' => 'Register BHW',
    'action_icon' => 'bi-plus-lg',
])

<div class="card rq-wrap fade-in-card">
    <div class="card-header bg-transparent">
        {{-- Every filter lives in the card header, so the roster starts one strip
             below the head bar instead of under a separate filter card. --}}
        <form method="GET" action="{{ route('rhu.bhws.index') }}" class="wd-filters">
            <div class="wd-field wd-search-field">
                <label for="bhw-search">Search</label>
                <div class="wd-search">
                    <input type="search" id="bhw-search" name="search" class="form-control" autocomplete="off"
                           value="{{ $search }}" placeholder="Name or email...">
                    <button class="btn" type="submit" title="Search" aria-label="Search"><i class="bi bi-search"></i></button>
                </div>
            </div>

            <div class="wd-field wd-select-field">
                <label for="bhw-status">Status</label>
                <select id="bhw-status" name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach (array_keys($statusMeta) as $key)
                        <option value="{{ $key }}" @selected($status === $key)>{{ ucfirst($key) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="wd-field wd-select-field">
                <label for="bhw-barangay">Barangay</label>
                <select id="bhw-barangay" name="barangay" class="form-select form-select-sm">
                    <option value="">All barangays</option>
                    @foreach($barangays as $brgy)
                        <option value="{{ $brgy->name }}" @selected($barangay === $brgy->name)>Barangay {{ $brgy->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="wd-actions d-flex gap-2">
                <button type="submit" class="btn btn-sm wd-apply-btn"><i class="bi bi-funnel me-1"></i>Filter</button>
                @if($filtersActive)
                    <a href="{{ route('rhu.bhws.index') }}" class="btn btn-sm wd-clear-btn">
                        <i class="bi bi-x-lg me-1"></i>Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($bhws->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>BHW</th>
                            <th>Barangay / Purok</th>
                            <th>Status</th>
                            <th>Output</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bhws as $bhw)
                            @php
                                $statusKey = $bhw->status ?? 'approved';
                                [$statusLabel, $statusTone] = $statusMeta[$statusKey] ?? [ucfirst($statusKey) ?: 'Unknown', 'primary'];
                                $purok = $bhw->purok?->name ?? $bhw->activeBhwAssignment?->purok?->name;
                            @endphp
                            <tr>
                                <td>
                                    <div class="rq-truncate rq-title" title="{{ $bhw->name }}">{{ $bhw->name }}</div>
                                    <div class="rq-soft">{{ $bhw->email ?: '—' }}</div>
                                </td>
                                <td>
                                    <div class="rq-truncate" style="max-width:150px;" title="{{ $bhw->barangay }}">{{ $bhw->barangay ?: '—' }}</div>
                                    {{-- Registry purok names already read "Purok 1", so prefixing one gave "Purok Purok 1". --}}
                                    <div class="rq-soft">{{ $purok ?: 'No purok' }}</div>
                                </td>
                                <td><span class="summary-chip chip-{{ $statusTone }} rq-status">{{ $statusLabel }}</span></td>
                                <td class="rq-num">
                                    <span class="fw-700">{{ $bhw->records_count }}</span><span class="rq-soft"> records · {{ $bhw->checkups_count }} checkups</span>
                                </td>
                                <td class="text-end">
                                    <div class="rq-actions">
                                        <a href="{{ route('rhu.bhws.edit', $bhw->id) }}" class="btn btn-sm btn-outline-primary"
                                           title="Edit BHW" aria-label="Edit {{ $bhw->name }}">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </a>
                                        @if($statusKey === 'approved')
                                            <form method="POST" action="{{ route('rhu.bhws.archive', $bhw->id) }}" onsubmit="return ppPromptArchiveReason(this);">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning"
                                                        title="Archive BHW" aria-label="Archive {{ $bhw->name }}">
                                                    <i class="bi bi-archive" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('rhu.bhws.activate', $bhw->id) }}" onsubmit="return confirm('Re-activate this BHW account?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                        title="Re-activate BHW" aria-label="Re-activate {{ $bhw->name }}">
                                                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($bhws->hasPages())
                <div class="rq-foot">{{ $bhws->links('vendor.pagination.arrows-only') }}</div>
            @endif
        @else
            <div class="rq-empty">
                <i class="bi bi-people text-muted" aria-hidden="true"></i>
                <h6 class="mt-2 mb-1 fw-700">No BHWs found</h6>
                <p class="rq-soft mb-0">
                    @if($filtersActive)
                        No BHW matches the current filters.
                    @else
                        Register a BHW to add them to the supervision roster.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Archive guardrail: require a reason so the audit trail records who / when / why.
    function ppPromptArchiveReason(form) {
        const reason = prompt('Reason for archiving this BHW? (e.g. resigned, reassigned)');
        if (reason === null) return false;
        if (reason.trim() === '') { alert('A reason for archiving is required.'); return false; }
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'reason'; input.value = reason.trim();
        form.appendChild(input);
        return confirm('Archive this BHW account? Sessions will be revoked immediately.');
    }
</script>
@endpush
@endsection
