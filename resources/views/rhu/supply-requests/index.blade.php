@extends('rhu.layout')

@section('title', 'Supply Requests - RHU Portal | ReproCare')

@push('styles')
@include('includes.women-directory-styles')
@include('includes.report-queue-styles')
@endpush

@section('rhu-content')

@php
    $filtersActive = filled($search ?? '') || ($status ?? 'all') !== 'all' || $scope !== 'all'
        || filled($dateFrom ?? '') || filled($dateTo ?? '');
    $statusMeta = [
        'draft' => ['Draft', 'primary'],
        'submitted' => ['Submitted', 'warning'],
        'under_review' => ['Under review', 'warning'],
        'approved' => ['Approved', 'success'],
        'declined' => ['Declined', 'danger'],
        'delivered' => ['Delivered', 'info'],
    ];
    $urgencyMeta = [
        'routine' => ['Routine', 'info'],
        'urgent' => ['Urgent', 'warning'],
        'emergency' => ['Emergency', 'danger'],
    ];
    $awaitingCount = ($statusCounts['submitted'] ?? 0) + ($statusCounts['under_review'] ?? 0);
@endphp

@include('includes.report-queue-head', [
    'title' => 'Supply Requests',
    'subtitle' => 'Requests this station sends to the City Health Office (CHO) for clinic supplies.',
    'icon' => 'bi-box-seam',
    'bar_class' => 'mb-2',
    'chips' => [
        ['label' => 'awaiting CHO', 'value' => $awaitingCount, 'tone' => 'warning'],
        ['label' => 'approved', 'value' => $statusCounts['approved'] ?? 0, 'tone' => 'success'],
        ['label' => 'delivered', 'value' => $statusCounts['delivered'] ?? 0, 'tone' => 'info'],
        ['label' => 'declined', 'value' => $statusCounts['declined'] ?? 0, 'tone' => 'danger'],
    ],
    'action_href' => route('rhu.supply-requests.create'),
    'action_label' => 'New Request',
    'action_icon' => 'bi-plus-lg',
])

<div class="card rq-wrap fade-in-card">
    <div class="card-header bg-transparent">
        {{-- One card header carries every filter, so the queue list starts one
             strip below the head bar instead of under a second full-size card. --}}
        <form method="GET" action="{{ route('rhu.supply-requests.index') }}" class="wd-filters">
            <div class="wd-field wd-search-field">
                <label for="sr-search">Search</label>
                <div class="wd-search">
                    <input type="search" id="sr-search" name="search" class="form-control" autocomplete="off"
                           value="{{ $search ?? '' }}" placeholder="ID, item, date, status...">
                    <button class="btn" type="submit" title="Search"><i class="bi bi-search"></i></button>
                </div>
            </div>

            <div class="wd-field wd-select-field">
                <label for="sr-scope">Scope</label>
                <select id="sr-scope" name="scope" class="form-select form-select-sm">
                    <option value="all" @selected($scope === 'all')>All requests</option>
                    <option value="mine" @selected($scope === 'mine')>My requests</option>
                </select>
            </div>

            <div class="wd-field wd-select-field">
                <label for="sr-status">Status</label>
                <select id="sr-status" name="status" class="form-select form-select-sm">
                    <option value="all" @selected(($status ?? 'all') === 'all')>All statuses</option>
                    @foreach ($statusMeta as $key => [$label])
                        <option value="{{ $key }}" @selected(($status ?? 'all') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="wd-field wd-date-field">
                <label for="sr-date-from">Submitted from</label>
                <input type="date" id="sr-date-from" name="date_from" class="form-control form-control-sm"
                       value="{{ $dateFrom ?? '' }}" max="{{ date('Y-m-d') }}">
            </div>

            <div class="wd-field wd-date-field">
                <label for="sr-date-to">to</label>
                <input type="date" id="sr-date-to" name="date_to" class="form-control form-control-sm"
                       value="{{ $dateTo ?? '' }}" max="{{ date('Y-m-d') }}">
            </div>

            <div class="wd-actions d-flex gap-2">
                <button type="submit" class="btn btn-sm wd-apply-btn"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('rhu.supply-requests.index') }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                    <i class="bi bi-x-lg me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($requests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-end">Qty</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <th>Expected Delivery</th>
                            <th>Submitted</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            @php
                                [$statusLabel, $statusTone] = $statusMeta[$req->status] ?? [ucfirst($req->status), 'primary'];
                                [$urgencyLabel, $urgencyTone] = $urgencyMeta[$req->urgency] ?? [ucfirst($req->urgency), 'primary'];
                            @endphp
                            <tr>
                                <td>
                                    <div class="rq-truncate rq-title" title="{{ $req->supply_name }}">{{ $req->supply_name }}</div>
                                    <div class="rq-soft">{{ str_replace('_', ' ', ucfirst($req->supply_category)) }}</div>
                                </td>
                                <td class="text-end rq-num">
                                    <span class="fw-700">{{ number_format($req->quantity_requested) }}</span>
                                    <span class="rq-soft">{{ $req->unit ?? 'pcs' }}</span>
                                </td>
                                <td><span class="summary-chip chip-{{ $urgencyTone }} rq-status">{{ $urgencyLabel }}</span></td>
                                <td><span class="summary-chip chip-{{ $statusTone }} rq-status">{{ $statusLabel }}</span></td>
                                <td>
                                    @if($req->expected_delivery_date)
                                        {{ $req->expected_delivery_date->format('M j, Y') }}
                                    @else
                                        <span class="rq-soft">Pending review</span>
                                    @endif
                                </td>
                                <td>
                                    @if($req->submitted_at)
                                        <div title="{{ $req->submitted_at->format('M j, Y \a\t g:i A') }}">{{ $req->submitted_at->format('M j, Y') }}</div>
                                        <div class="rq-soft">{{ $req->submitted_at->format('g:i A') }}</div>
                                    @else
                                        <span class="rq-soft">Not submitted</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="rq-actions">
                                        <a href="{{ route('rhu.supply-requests.show', $req->id) }}" class="btn btn-sm btn-outline-primary"
                                           title="Open this request" aria-label="Open this request"><i class="bi bi-eye"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="rq-foot">{{ $requests->links('vendor.pagination.arrows-only') }}</div>
            @endif
        @else
            <div class="rq-empty">
                <i class="bi bi-box-seam text-muted"></i>
                <h6 class="mt-2 mb-1 fw-700">No supply requests found</h6>
                <p class="rq-soft mb-0">
                    @if($filtersActive)
                        No request matches the current filters.
                    @else
                        Submit a replenishment request to keep clinic stock covered.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@endsection
