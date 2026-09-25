@extends('midwife.layout')

@section('title', 'Notifications - ReproCare')

@push('styles')
<style>
    .notif-feed { display:flex; flex-direction:column; gap:0; }

    .notif-row {
        display:flex; align-items:center; gap:1rem;
        padding:1.1rem 1.4rem;
        border-bottom:1px solid var(--mw-border);
        transition:background 0.15s ease;
        position:relative;
    }
    .notif-row:last-child { border-bottom:none; }
    .notif-row:hover { background:color-mix(in srgb, var(--color-primary) 3%, transparent); }
    .notif-row.unread { background:color-mix(in srgb, var(--color-primary) 4%, transparent); }
    .notif-row.unread::before {
        content:'';
        position:absolute; left:0; top:50%; transform:translateY(-50%);
        width:3px; height:60%; border-radius:0 2px 2px 0;
        background:var(--mw-primary);
    }

    /* Type icon badge */
    .notif-icon-badge {
        width:42px; height:42px; border-radius:14px;
        display:flex; align-items:center; justify-content:center;
        font-size:1.1rem; flex-shrink:0;
    }
    .notif-icon-info    { background:var(--color-info-soft); color:var(--color-info-text); }
    .notif-icon-warning { background:var(--color-warning-soft); color:var(--color-warning-text); }
    .notif-icon-success { background:var(--color-success-soft); color:var(--color-success-text); }
    .notif-icon-error,
    .notif-icon-danger  { background:var(--color-danger-soft); color:var(--color-danger-text); }

    /* Content */
    .notif-body { flex:1; min-width:0; }
    .notif-title {
        font-weight:700; font-size:0.9rem;
        color:var(--mw-heading); white-space:nowrap;
        overflow:hidden; text-overflow:ellipsis;
        max-width:100%;
    }
    .notif-preview {
        font-size:0.8rem; color:var(--mw-body);
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
        max-width:420px;
    }
    .notif-meta {
        font-size:0.75rem; color:var(--mw-body);
        margin-top:0.2rem; display:flex; align-items:center; gap:0.5rem;
    }

    /* Status badge */
    .notif-status-badge {
        padding:0.2em 0.7em; border-radius:20px;
        font-size:0.72rem; font-weight:700; white-space:nowrap;
    }
    .notif-status-unread {
        background:var(--color-primary-soft); border:1px solid var(--color-primary-soft); color:var(--color-primary-text);
    }
    .notif-status-read {
        background:var(--color-bg); border:1px solid var(--color-border); color:var(--color-text-muted);
    }

    /* Ghost action buttons */
    .notif-actions { display:flex; gap:0.4rem; flex-shrink:0; }
    .notif-btn {
        width:32px; height:32px; border-radius:8px; border:1px solid transparent;
        display:flex; align-items:center; justify-content:center;
        background:transparent; color:var(--color-text-muted);
        font-size:0.88rem; cursor:pointer; transition:all 0.15s ease;
        text-decoration:none;
    }
    .notif-btn:hover { background:var(--color-bg); border-color:var(--mw-border); color:var(--mw-heading); }
    .notif-btn.danger:hover { background:var(--color-danger-soft); border-color:var(--color-danger-soft); color:var(--color-danger-text); }
    .notif-btn.success:hover { background:var(--color-success-soft); border-color:var(--color-success-soft); color:var(--color-success-text); }

    /* Filter pill tabs */
    .notif-filter-row { display:flex; align-items:center; gap:0.5rem; padding:1rem 1.4rem; border-bottom:1px solid var(--mw-border); flex-wrap:wrap; }
    .notif-pill {
        padding:0.35em 1em; border-radius:20px; font-size:0.82rem; font-weight:700;
        border:1px solid var(--mw-border); background:transparent;
        color:var(--mw-body); cursor:pointer; text-decoration:none;
        transition:all 0.15s ease;
    }
    .notif-pill:hover, .notif-pill.active {
        background:var(--mw-primary); border-color:var(--mw-primary);
        color:var(--color-on-solid);
    }
    .notif-pill.active-secondary {
        background:var(--color-primary-soft); border-color:var(--color-border); color:var(--mw-primary);
    }

    /* Stats row */
    .notif-stats-bar {
        display:flex; gap:1.5rem; padding:0.9rem 1.4rem;
        border-bottom:1px solid var(--mw-border);
        background:var(--color-primary-soft);
    }
    .notif-stat-item { font-size:0.82rem; color:var(--mw-body); }
    .notif-stat-item strong { color:var(--mw-heading); font-weight:700; }
</style>
@endpush

@section('midwife-content')

{{-- Page Hero --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Notifications
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; System alerts and announcements
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('midwife.notifications.create') }}" class="btn-hero-primary">
                <i class="bi bi-plus-circle-fill"></i> Create Notification
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 fade-in-card" style="border-radius:14px; background:color-mix(in srgb, var(--color-success) 10%, transparent); border:1px solid color-mix(in srgb, var(--color-success) 30%, transparent); color:var(--color-success-text);">
        <i class="bi bi-check-circle-fill flex-shrink-0"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card fade-in-card">
    {{-- Stats Bar --}}
    <div class="notif-stats-bar">
        <div class="notif-stat-item">
            <strong>{{ $notifications->total() }}</strong> Total
        </div>
        <div class="notif-stat-item">
            <strong style="color:var(--color-primary-text);">{{ $notifications->where('is_read', false)->count() }}</strong> Unread
        </div>
        <div class="notif-stat-item">
            <strong style="color:var(--color-success-text);">{{ $notifications->where('is_read', true)->count() }}</strong> Read
        </div>
    </div>

    {{-- Filter Pills --}}
    <div class="notif-filter-row">
        <a href="{{ route('midwife.notifications.index') }}" class="notif-pill {{ !request('filter') ? 'active' : '' }}">
            <i class="bi bi-grid me-1"></i> All
        </a>
        <a href="{{ route('midwife.notifications.index', ['filter' => 'unread']) }}" class="notif-pill {{ request('filter') === 'unread' ? 'active' : '' }}">
            <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;vertical-align:middle;"></i> Unread
        </a>
        <a href="{{ route('midwife.notifications.index', ['filter' => 'read']) }}" class="notif-pill {{ request('filter') === 'read' ? 'active' : '' }}">
            <i class="bi bi-check-circle me-1"></i> Read
        </a>
    </div>

    {{-- Notification Feed --}}
    @if($notifications->count() > 0)
        <div class="notif-feed">
            @foreach($notifications as $notification)
                @php
                    $iconClass = match($notification->type) {
                        'info'    => 'notif-icon-info',
                        'warning' => 'notif-icon-warning',
                        'success' => 'notif-icon-success',
                        'error', 'danger' => 'notif-icon-danger',
                        default   => 'notif-icon-info',
                    };
                    $iconName = match($notification->type) {
                        'info'    => 'bi-info-circle-fill',
                        'warning' => 'bi-exclamation-triangle-fill',
                        'success' => 'bi-check-circle-fill',
                        'error', 'danger' => 'bi-x-circle-fill',
                        default   => 'bi-bell-fill',
                    };
                    // Safely decode title to handle encoding glitches
                    $safeTitle = htmlspecialchars_decode(strip_tags($notification->title ?? 'Notification'));
                @endphp
                <div class="notif-row {{ !$notification->is_read ? 'unread' : '' }}">
                    {{-- Icon --}}
                    <div class="notif-icon-badge {{ $iconClass }}">
                        <i class="bi {{ $iconName }}"></i>
                    </div>

                    {{-- Body --}}
                    <div class="notif-body">
                        <div class="notif-title">{{ $safeTitle }}</div>
                        <div class="notif-preview">{{ Str::limit(strip_tags($notification->message ?? ''), 90) }}</div>
                        <div class="notif-meta">
                            <i class="bi bi-clock"></i>
                            {{ $notification->created_at->format('M j, Y g:i A') }}
                            @if($notification->target_role)
                                <span style="background:color-mix(in srgb, var(--color-primary) 12%, transparent);color:var(--mw-primary);padding:0.1em 0.55em;border-radius:8px;font-size:0.7rem;font-weight:700;">
                                    {{ ucfirst($notification->target_role) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <span class="notif-status-badge {{ $notification->is_read ? 'notif-status-read' : 'notif-status-unread' }}">
                        {{ $notification->is_read ? 'Read' : 'Unread' }}
                    </span>

                    {{-- Actions --}}
                    <div class="notif-actions">
                        <a href="{{ route('midwife.notifications.show', $notification->id) }}"
                           class="notif-btn" title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if(!$notification->is_read)
                            <form method="POST" action="{{ route('midwife.notifications.mark-read', $notification->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="notif-btn success" title="Mark as Read">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('midwife.notifications.delete', $notification->id) }}" class="d-inline"
                              onsubmit="return confirm('Delete this notification?')">
                            @csrf
                            <button type="submit" class="notif-btn danger" title="Delete">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center p-4 border-top" style="border-color:var(--mw-border)!important;">
            {{ $notifications->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="text-center py-5">
            <div style="width:60px;height:60px;border-radius:50%;background:var(--mw-primary-subtle);color:var(--mw-primary);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.5rem;">
                <i class="bi bi-bell-slash"></i>
            </div>
            <h6 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;color:var(--mw-heading);margin-bottom:0.35rem;">No Notifications Found</h6>
            <p style="color:var(--mw-body);font-size:0.875rem;max-width:300px;margin:0 auto 1.25rem;">
                @if(request('filter') === 'unread')
                    No unread notifications — you're all caught up!
                @elseif(request('filter') === 'read')
                    No read notifications yet.
                @else
                    You haven't created any notifications yet.
                @endif
            </p>
            <a href="{{ route('midwife.notifications.create') }}" class="btn-hero-primary d-inline-flex">
                <i class="bi bi-plus-circle-fill me-1"></i> Create First Notification
            </a>
        </div>
    @endif
</div>

@endsection
