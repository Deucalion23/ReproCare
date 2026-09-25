@extends('bhw-president.layout')

@section('title', 'BHW Management - BHW President Portal | ReproCare')

@push('styles')
<style>
    .bhw-search-bar {
        display:flex;
        gap:0.65rem;
        align-items:center;
        flex-wrap:wrap;
    }
    .bhw-search-field,
    .bhw-filter-field {
        display:flex;
        align-items:center;
        gap:0.55rem;
        background:var(--bg-card2);
        border:1px solid var(--border);
        border-radius:14px;
        padding:0.75rem 0.95rem;
        min-height:48px;
    }
    .bhw-search-field {
        flex:1 1 280px;
        min-width:240px;
    }
    .bhw-filter-field {
        flex:0 0 190px;
    }
    .bhw-search-field input,
    .bhw-filter-field select {
        border:none;
        background:transparent;
        color:var(--text);
        outline:none;
        width:100%;
        font-size:0.9rem;
    }
    .bhw-search-field input::placeholder {
        color:var(--placeholder);
    }
    .bhw-search-actions {
        display:flex;
        gap:0.65rem;
        flex-wrap:wrap;
    }
    .bhw-avatar {
        width:44px;
        height:44px;
        border-radius:14px;
        background:linear-gradient(135deg, var(--color-info), var(--color-info));
        color:var(--color-on-solid);
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:0.92rem;
        font-weight:800;
        flex-shrink:0;
        box-shadow:0 10px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent);
        overflow:hidden;
    }
    .bhw-avatar img {
        width:100%;
        height:100%;
        object-fit:cover;
    }
    .metric-chip {
        display:inline-flex;
        align-items:center;
        gap:0.35rem;
        padding:0.34rem 0.72rem;
        border-radius:999px;
        border:1px solid transparent;
        font-size:0.78rem;
        font-weight:700;
    }
    .metric-chip-records {
        background:color-mix(in srgb, var(--color-info) 12%, transparent);
        color:var(--color-info-text);
        border-color:color-mix(in srgb, var(--color-info) 25%, transparent);
    }
    .metric-chip-checkups {
        background:color-mix(in srgb, var(--color-success) 12%, transparent);
        color:var(--color-success-text);
        border-color:color-mix(in srgb, var(--color-success) 25%, transparent);
    }
    .metric-chip-reports {
        background:color-mix(in srgb, var(--color-warning) 12%, transparent);
        color:var(--color-warning-text);
        border-color:color-mix(in srgb, var(--color-warning) 25%, transparent);
    }
    .status-badge-custom {
        display:inline-flex;
        align-items:center;
        gap:0.35rem;
        padding:0.35rem 0.75rem;
        border-radius:999px;
        font-size:0.76rem;
        font-weight:700;
        border:1px solid transparent;
        text-transform:capitalize;
    }
    .status-approved {
        background:color-mix(in srgb, var(--color-success) 14%, transparent);
        color:var(--color-success-text);
        border-color:color-mix(in srgb, var(--color-success) 22%, transparent);
    }
    .status-pending {
        background:color-mix(in srgb, var(--color-warning) 14%, transparent);
        color:var(--color-warning-text);
        border-color:color-mix(in srgb, var(--color-warning) 22%, transparent);
    }
    .status-inactive {
        background:color-mix(in srgb, var(--color-info) 16%, transparent);
        color:var(--color-info-text);
        border-color:color-mix(in srgb, var(--color-info) 22%, transparent);
    }
    .status-archived {
        background:color-mix(in srgb, var(--color-info) 14%, transparent);
        color:var(--color-info-text);
        border-color:color-mix(in srgb, var(--color-info) 22%, transparent);
    }
    .status-suspended {
        background:color-mix(in srgb, var(--color-danger) 14%, transparent);
        color:var(--color-danger-text);
        border-color:color-mix(in srgb, var(--color-danger) 22%, transparent);
    }
    .bhw-row:hover {
        background:var(--row-hover);
    }
    .bhw-actions {
        display:flex;
        align-items:center;
        justify-content:center;
        gap:0.45rem;
        flex-wrap:nowrap;
    }
    .bhw-actions form {
        display:inline-flex;
        margin:0;
        padding:0;
        line-height:0;
    }
    .bhw-action-btn,
    .bhw-action-btn button {
        width:38px;
        height:38px;
        border-radius:10px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border:1px solid var(--border);
        background:var(--bg-card2);
        color:var(--text-muted);
        text-decoration:none;
        transition:all 0.18s ease;
        padding:0;
        margin:0;
        line-height:1;
        vertical-align:middle;
        cursor:pointer;
    }
    .bhw-action-btn:hover {
        transform:translateY(-1px) scale(1.05);
    }
    .bhw-action-btn.view:hover {
        color:var(--color-info-text);
        border-color:color-mix(in srgb, var(--color-info) 35%, transparent);
        background:color-mix(in srgb, var(--color-info) 10%, transparent);
    }
    .bhw-action-btn.inactive:hover,
    .bhw-action-btn.archive:hover {
        color:var(--color-warning-text);
        border-color:color-mix(in srgb, var(--color-warning) 35%, transparent);
        background:color-mix(in srgb, var(--color-warning) 10%, transparent);
    }
    .bhw-action-btn.activate:hover {
        color:var(--color-success-text);
        border-color:color-mix(in srgb, var(--color-success) 35%, transparent);
        background:color-mix(in srgb, var(--color-success) 10%, transparent);
    }
    .table-toolbar {
        padding:1.25rem 1.5rem;
        border-bottom:1px solid var(--border);
    }
    .bhw-actions-col {
        width:140px;
        min-width:140px;
        text-align:center !important;
    }
    .bhw-actions-col th,
    .bhw-actions-col td {
        text-align:center !important;
        vertical-align:middle;
    }
    @media (max-width: 768px) {
        .bhw-search-actions {
            width:100%;
        }
        .bhw-search-actions .btn {
            flex:1 1 auto;
        }
        .bhw-filter-field {
            flex:1 1 100%;
        }
        .bhw-actions {
            justify-content:center;
        }
        .bhw-actions-col {
            min-width:140px;
        }
    }
</style>
@endpush

@section('bhw-president-content')
@php
    $totalBhws = $bhws->total();
    $approvedBhws = $bhws->getCollection()->where('status', 'approved')->count();
    $inactiveBhws = $bhws->getCollection()->where('status', 'inactive')->count();
    $archivedBhws = $bhws->getCollection()->where('status', 'archived')->count();
@endphp

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">BHW Management
            </div>
            <p class="page-hero-subtitle">Review, filter, and manage all Barangay Health Workers assigned under your supervision.</p>
        </div>
        <a href="{{ route('bhw-president.bhws.create') }}" class="btn-hero-primary">
            <i class="bi bi-plus-circle-fill"></i>
            Add BHW
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 fade-in-card">
        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card stat-cyan fade-in-card">
            <i class="bi bi-people-fill stat-icon"></i>
            <div class="stat-label">Total BHWs</div>
            <div class="stat-number" data-count="{{ $totalBhws }}">{{ $totalBhws }}</div>
            <div class="stat-trend">All accounts in this directory</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card stat-green fade-in-card">
            <i class="bi bi-person-check-fill stat-icon"></i>
            <div class="stat-label">Approved</div>
            <div class="stat-number" data-count="{{ $approvedBhws }}">{{ $approvedBhws }}</div>
            <div class="stat-trend">Ready for active work</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card stat-amber fade-in-card">
            <i class="bi bi-person-dash-fill stat-icon"></i>
            <div class="stat-label">Inactive</div>
            <div class="stat-number" data-count="{{ $inactiveBhws }}">{{ $inactiveBhws }}</div>
            <div class="stat-trend">Temporarily unavailable</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card stat-purple fade-in-card">
            <i class="bi bi-archive-fill stat-icon"></i>
            <div class="stat-label">Archived</div>
            <div class="stat-number" data-count="{{ $archivedBhws }}">{{ $archivedBhws }}</div>
            <div class="stat-trend">Stored for record history</div>
        </div>
    </div>
</div>

<div class="table-card fade-in-card">
    <div class="table-toolbar">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h2 class="mb-1" style="font-size:1.12rem;font-weight:800;color:var(--text);">All BHWs</h2>
                <p class="mb-0" style="font-size:0.84rem;color:var(--text-muted);">Monitor worker status, service output, and account actions without leaving the directory.</p>
            </div>
            <span class="summary-chip chip-primary">
                <i class="bi bi-person-lines-fill"></i>
                {{ $totalBhws }} total
            </span>
        </div>

        <form method="GET" action="{{ route('bhw-president.bhws.index') }}" class="mt-3">
            <div class="bhw-search-bar">
                <div class="bhw-search-field">
                    <i class="bi bi-search" style="color:var(--text-muted);"></i>
                    <input type="text" name="search" placeholder="Search by name, email, or barangay..." value="{{ request('search') }}">
                </div>
                <div class="bhw-filter-field">
                    <i class="bi bi-funnel" style="color:var(--text-muted);"></i>
                    <select name="status">
                        <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div class="bhw-search-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel-fill me-1"></i> Apply
                    </button>
                    <a href="{{ route('bhw-president.bhws.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    @if($bhws->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover table-sticky-head mb-0 align-middle">
                <thead>
                    <tr>
                        <th>BHW</th>
                        <th>Barangay</th>
                        <th>Assigned Purok</th>
                        <th>Status</th>
                        <th>Health Records</th>
                        <th>Checkups</th>
                        <th>Reports</th>
                        <th class="bhw-actions-col" style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bhws as $bhw)
                        @php($assignedPurok = $bhw->purok ?? $bhw->activeBhwAssignment?->purok)
                        <tr class="bhw-row">
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bhw-avatar">
                                        @if($bhw->profile_image)
                                            <img src="{{ asset('storage/' . $bhw->profile_image) }}" alt="{{ $bhw->name }}" onerror="this.style.display='none'; this.parentElement.innerText='{{ strtoupper(substr($bhw->name, 0, 1)) }}';">
                                        @else
                                            {{ strtoupper(substr($bhw->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-weight:800;color:var(--text);font-size:0.93rem;">{{ $bhw->name }}</div>
                                        <div style="font-size:0.8rem;color:var(--text-muted);">
                                            <i class="bi bi-envelope me-1"></i>{{ $bhw->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:700;color:var(--text);">{{ $bhw->barangay ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div style="font-weight:700;color:var(--text);">{{ $assignedPurok?->name ?? 'Not assigned' }}</div>
                                <div style="font-size:0.76rem;color:var(--text-muted);">{{ $assignedPurok?->barangay ?? 'Assign a purok from details' }}</div>
                            </td>
                            <td>
                                <span class="status-badge-custom status-{{ $bhw->status }}">
                                    <i class="bi bi-circle-fill" style="font-size:0.45rem;"></i>
                                    {{ ucfirst($bhw->status ?? 'unknown') }}
                                </span>
                            </td>
                            <td>
                                <span class="metric-chip metric-chip-records">
                                    <i class="bi bi-file-medical"></i>
                                    {{ $bhw->health_records_count }}
                                </span>
                            </td>
                            <td>
                                <span class="metric-chip metric-chip-checkups">
                                    <i class="bi bi-calendar-check"></i>
                                    {{ $bhw->checkups_scheduled }}
                                </span>
                            </td>
                            <td>
                                <span class="metric-chip metric-chip-reports">
                                    <i class="bi bi-file-earmark-bar-graph"></i>
                                    {{ $bhw->monthly_reports_count }}
                                </span>
                            </td>
                            <td class="bhw-actions-col">
                                <div class="bhw-actions">
                                    <a href="{{ route('bhw-president.bhws.details', $bhw->id) }}"
                                       class="bhw-action-btn view"
                                       title="View details"
                                       aria-label="View {{ $bhw->name }}">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @if($bhw->status === 'approved')
                                        <form action="{{ route('bhw-president.bhws.inactive', $bhw->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="bhw-action-btn inactive"
                                                    title="Mark inactive"
                                                    aria-label="Mark {{ $bhw->name }} inactive"
                                                    onclick="return confirm('Mark {{ $bhw->name }} as inactive?')">
                                                <i class="bi bi-person-x"></i>
                                            </button>
                                        </form>
                                        <x-archive-form :action="route('bhw-president.bhws.archive', $bhw->id)" method="POST" label="" title="Archive (retained for audit)" btnClass="bhw-action-btn archive" icon="bi bi-archive" :confirmText="'Archive ' . $bhw->name . '? Sessions are revoked and the account is retained for audit.'" />
                                    @elseif(in_array($bhw->status, ['inactive', 'archived']))
                                        <form action="{{ route('bhw-president.bhws.activate', $bhw->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="bhw-action-btn activate"
                                                    title="Activate"
                                                    aria-label="Activate {{ $bhw->name }}"
                                                    onclick="return confirm('Activate {{ $bhw->name }}?')">
                                                <i class="bi bi-person-check"></i>
                                            </button>
                                        </form>
                                        @if($bhw->status === 'inactive')
                                            <x-archive-form :action="route('bhw-president.bhws.archive', $bhw->id)" method="POST" label="" title="Archive (retained for audit)" btnClass="bhw-action-btn archive" icon="bi bi-archive" :confirmText="'Archive ' . $bhw->name . '? The account is retained for audit.'" />
                                        @endif
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center" style="padding:1.25rem;">
            {{ $bhws->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-people empty-state-icon"></i>
            <h6>No BHWs Found</h6>
            <p>
                @if(request('search') || request('status'))
                    No BHW matches the current filters.
                @else
                    No BHW accounts have been added yet.
                @endif
            </p>
            <a href="{{ route('bhw-president.bhws.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle-fill me-1"></i> Add First BHW
            </a>
        </div>
    @endif
</div>
@endsection
