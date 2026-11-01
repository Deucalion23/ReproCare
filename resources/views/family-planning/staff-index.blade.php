@extends(auth()->user()->role === 'midwife' ? 'midwife.layout' : 'bhw.layout')
@section(auth()->user()->role === 'midwife' ? 'midwife-content' : 'bhw-content')

@php
    $isMidwife = auth()->user()->role === 'midwife';
    $routeBase = $isMidwife ? 'midwife.family-planning' : 'bhw.family-planning';
    $indexRoute = route($routeBase.'.index');
    $filtersActive = $filters['search'] !== '' || $filters['method'] !== 'all'
        || $filters['status'] !== 'all' || $filters['follow_up'] !== 'all';
@endphp

@push('styles')
    {{-- Reuses the directory filter bar (.wd-filters / .wd-search) without .wd-wrap,
         which would also squeeze this table to directory density. --}}
    @include('includes.women-directory-styles')
    <style>
        .fp-card { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
        .fp-card > .card-header { border-bottom:1px solid var(--color-border) !important; }
        .fp-table th { font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-muted) !important; }
        .fp-table td { vertical-align:middle; }
        .fp-client { font-weight:700; color:var(--color-text); }
        .fp-barangay { font-size:0.75rem; color:var(--color-text-muted); }
        body .main-content a.rc-indicator { text-decoration:none; transition:transform .15s ease, box-shadow .15s ease; }
        body .main-content a.rc-indicator:hover { transform:translateY(-1px); box-shadow:0 6px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 12%, transparent); }
        @media (max-width: 767.98px) {
            .fp-table { font-size:0.8rem; }
            .fp-table th, .fp-table td { padding:0.55rem 0.6rem !important; }
        }
    </style>
@endpush

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Family Planning</h1>
        <p class="text-muted mb-0">Private counseling, resupply, and follow-up tracking.</p>
    </div>
    <a class="btn btn-primary" href="{{ route($routeBase.'.create') }}"><i class="bi bi-plus-lg me-1"></i> Add service record</a>
</div>

@if(session('success'))
    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
@endif

@if($pendingCount || $dueCount)
    <div class="rc-indicator-row">
        @if($pendingCount)
            <a class="rc-indicator ind-info" href="{{ route($routeBase.'.index', ['status' => 'requested']) }}" title="Show only counseling requests">
                <i class="bi bi-chat-heart"></i>{{ $pendingCount }} counseling request(s) to review
            </a>
        @endif
        @if($dueCount)
            <a class="rc-indicator ind-warning" href="{{ route($routeBase.'.index', ['follow_up' => 'due']) }}" title="Show only follow-ups due or overdue">
                <i class="bi bi-calendar-x"></i>{{ $dueCount }} follow-up(s) due or overdue
            </a>
        @endif
    </div>
@endif

<div class="card fp-card">
    <div class="card-header bg-transparent p-3">
        <form method="GET" action="{{ $indexRoute }}" class="wd-filters">
            <div class="wd-field wd-search-field">
                <label for="fp-search">Search</label>
                <div class="wd-search">
                    <input type="search" id="fp-search" name="search" class="form-control" value="{{ $filters['search'] }}"
                           placeholder="Client name, contact, purok, barangay...">
                    <button class="btn" type="submit" title="Search">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>

            <div class="wd-field wd-select-field">
                <label for="fp-method">Preference / method</label>
                <select id="fp-method" name="method" class="form-select form-select-sm">
                    <option value="all" @selected($filters['method'] === 'all')>All methods</option>
                    <option value="pending" @selected($filters['method'] === 'pending')>Pending counseling</option>
                    @foreach ($methodOptions as $method)
                        <option value="{{ $method }}" @selected($filters['method'] === $method)>{{ \App\Models\FamilyPlanningRecord::methodLabel($method) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="wd-field wd-select-field">
                <label for="fp-status">Status</label>
                <select id="fp-status" name="status" class="form-select form-select-sm">
                    <option value="all" @selected($filters['status'] === 'all')>All statuses</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="wd-field wd-select-field">
                <label for="fp-follow-up">Follow-up</label>
                <select id="fp-follow-up" name="follow_up" class="form-select form-select-sm">
                    <option value="all" @selected($filters['follow_up'] === 'all')>Any follow-up</option>
                    <option value="due" @selected($filters['follow_up'] === 'due')>Due or overdue</option>
                    <option value="upcoming" @selected($filters['follow_up'] === 'upcoming')>Upcoming</option>
                    <option value="none" @selected($filters['follow_up'] === 'none')>None set</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-sm wd-apply-btn">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if ($filtersActive)
                    <a href="{{ $indexRoute }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                        <i class="bi bi-x-lg me-1"></i> Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 border-bottom">
        <span class="wd-count">
            Showing <strong>{{ $records->total() }}</strong> {{ Str::plural('record', $records->total()) }}
            @if ($filtersActive)
                <span class="text-muted">matching these filters</span>
            @else
                <span class="text-muted">in your service area</span>
            @endif
        </span>
        @if ($filtersActive)
            <a href="{{ $indexRoute }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset filters
            </a>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover fp-table mb-0">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Preference / method</th>
                    <th>Status</th>
                    <th>Follow-up</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr class="{{ $record->status === 'requested' ? 'table-warning' : '' }}">
                        <td>
                            <div class="fp-client">{{ $record->client->name }}</div>
                            <div class="fp-barangay">{{ $record->client->barangay }}</div>
                        </td>
                        <td>
                            @if(! $record->method_given && $record->status === 'requested')
                                <span class="badge text-bg-warning">Pending counseling</span>
                            @else
                                {{ \App\Models\FamilyPlanningRecord::methodLabel($record->method_given ?: $record->preferred_method) }}
                            @endif
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $record->status === 'active' ? 'success' : ($record->status === 'requested' ? 'warning' : ($record->status === 'referred' ? 'info' : 'secondary')) }}">
                                {{ ucfirst($record->status) }}
                            </span>
                        </td>
                        <td>{{ $record->next_follow_up?->format('M j, Y') ?? '—' }}</td>
                        <td class="text-center">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route($routeBase.'.edit', $record) }}">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            @if ($filtersActive)
                                <div class="text-muted mb-2">No records match these filters.</div>
                                <a href="{{ $indexRoute }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset filters
                                </a>
                            @else
                                <div class="text-muted">No records in your service area.</div>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $records->links() }}
@endsection
