@extends('midwife.layout')

@section('title', 'Notifications - ReproCare')

@push('styles')
<style>
    /* ── Notifications table: clean white, borderless pastels ── */
    .ntf-pill { display:inline-flex; align-items:center; gap:.3rem; font-size:.72rem; font-weight:800; padding:.3rem .8rem; border-radius:999px; border:none; white-space:nowrap; }
    .ntf-pill-info    { background:var(--color-primary-soft); background-color:var(--color-primary-soft); color:var(--color-primary-text); }
    .ntf-pill-warning { background:var(--color-warning-soft); background-color:var(--color-warning-soft); color:var(--color-warning-text); }
    .ntf-pill-success { background:var(--color-success-soft); background-color:var(--color-success-soft); color:var(--color-success-text); }
    .ntf-pill-danger  { background:var(--color-danger-soft); background-color:var(--color-danger-soft); color:var(--color-danger-text); }
    .ntf-pill-target  { background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text); }
    .ntf-pill-unread  { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:var(--color-secondary-text); }
    .ntf-pill-read    { background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text-muted); }

    .ntf-table thead th { font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:var(--color-text-muted); padding:.9rem 1.1rem; border:none; white-space:nowrap; vertical-align:middle; }
    .ntf-table tbody td { padding:.9rem 1.1rem; vertical-align:middle; border:none; font-size:.88rem; }
    .ntf-table tbody tr:last-child td { border:none; }
    .ntf-table tbody tr.ntf-unread td { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); }
    .ntf-table tbody tr:first-child.ntf-unread td:first-child { border-radius:14px 0 0 14px; }
    .ntf-table tbody tr:first-child.ntf-unread td:last-child { border-radius:0 14px 14px 0; }
    .ntf-table tbody tr:hover td { background:var(--color-bg); background-color:var(--color-bg); }
    .ntf-table tbody tr.ntf-unread:hover td { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); }
    .ntf-title { font-weight:800; color:var(--color-text); font-size:.9rem; }
    .ntf-msg { color:var(--color-text-muted); font-size:.85rem; }
    .ntf-date { color:var(--color-text-muted); font-size:.8rem; white-space:nowrap; }

    /* Spaced, fully-rounded action buttons (never fused) — borderless */
    .ntf-actions { display:flex; align-items:center; gap:.5rem; justify-content:flex-end; }
    .ntf-actions form { margin:0; }
    .ntf-btn { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:50%; border:none; background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text); font-size:.85rem; cursor:pointer; transition:all .18s ease; text-decoration:none; flex-shrink:0; }
    .ntf-btn:hover { border:none; color:var(--color-text); background:var(--color-border); background-color:var(--color-border); transform:translateY(-1px); }
    .ntf-btn-green:hover { border:none; color:var(--color-success-text); background:var(--color-success-soft); background-color:var(--color-success-soft); }
    .ntf-btn-red:hover { border:none; color:var(--color-danger-text); background:var(--color-danger-soft); background-color:var(--color-danger-soft); }

    /* Compact pager — borderless */
    .ntf-pager { padding:1rem; border:none; }
    .ntf-pager nav { display:flex; justify-content:center; }
    .ntf-pager .pagination { margin:0; display:flex; justify-content:center; align-items:center; gap:.3rem; flex-wrap:nowrap; }
    .ntf-pager .page-item .page-link { border:none; background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text); font-size:.78rem; font-weight:800; border-radius:10px; padding:.35rem .7rem; min-width:34px; text-align:center; box-shadow:none; }
    .ntf-pager .page-item .page-link:hover { border:none; color:var(--color-on-solid); background:var(--color-surface-strong); background-color:var(--color-surface-strong); }
    .ntf-pager .page-item.active .page-link { background:var(--color-surface-strong); background-color:var(--color-surface-strong); border:none; color:var(--color-on-solid); box-shadow:0 3px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent); }
    .ntf-pager .page-item.disabled .page-link { color:var(--color-text); background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:none; }
</style>
@endpush

@section('midwife-content')
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div>
                <div class="page-hero-title">Notifications</div>
                <p class="page-hero-subtitle mb-0">
                    <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                    &nbsp;·&nbsp; System alerts and announcements
                </p>
            </div>
        </div>
        <a href="{{ route('midwife.notifications.create') }}" class="btn-hero-primary flex-shrink-0">
            <i class="bi bi-plus-circle-fill"></i> Create Notification
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:14px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        @if($notifications->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 ntf-table">
                    <thead>
                        <tr>
                            <th class="text-start">Type</th>
                            <th class="text-start">Title</th>
                            <th class="text-start">Message</th>
                            <th class="text-start">Target</th>
                            <th class="text-start">Status</th>
                            <th class="text-start">Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                            @php
                                $pillClass = match($notification->type) {
                                    'info'    => 'ntf-pill-info',
                                    'warning' => 'ntf-pill-warning',
                                    'success' => 'ntf-pill-success',
                                    'error', 'danger' => 'ntf-pill-danger',
                                    default   => 'ntf-pill-info',
                                };
                                $pillIcon = match($notification->type) {
                                    'info'    => 'bi-info-circle-fill',
                                    'warning' => 'bi-exclamation-triangle-fill',
                                    'success' => 'bi-check-circle-fill',
                                    'error', 'danger' => 'bi-x-circle-fill',
                                    default   => 'bi-bell-fill',
                                };
                            @endphp
                            <tr class="{{ !$notification->is_read ? 'ntf-unread' : '' }}">
                                <td>
                                    <span class="ntf-pill {{ $pillClass }}">
                                        <i class="bi {{ $pillIcon }}"></i>{{ ucfirst($notification->type === 'error' ? 'danger' : $notification->type) }}
                                    </span>
                                </td>
                                <td><span class="ntf-title">{{ $notification->title }}</span></td>
                                <td><span class="ntf-msg">{{ Str::limit(strip_tags($notification->message), 80) }}</span>
                                    @include('includes.patient-alert-receipt')
                                </td>
                                <td>
                                    @if($notification->target_role)
                                        <span class="ntf-pill ntf-pill-target">{{ ucfirst($notification->target_role) }}</span>
                                    @else
                                        <span class="text-muted">All</span>
                                    @endif
                                </td>
                                <td>
                                    @if($notification->is_read)
                                        <span class="ntf-pill ntf-pill-read">Read</span>
                                    @else
                                        <span class="ntf-pill ntf-pill-unread">Unread</span>
                                    @endif
                                </td>
                                <td><span class="ntf-date">{{ $notification->created_at->format('M j, Y g:i A') }}</span></td>
                                <td>
                                    <div class="ntf-actions">
                                        <a href="{{ route('midwife.notifications.show', $notification->id) }}" class="ntf-btn" title="View details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(!$notification->is_read)
                                            <form method="POST" action="{{ route('midwife.notifications.mark-read', $notification->id) }}">
                                                @csrf
                                                <button type="submit" class="ntf-btn ntf-btn-green" title="Mark as read">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('midwife.notifications.delete', $notification->id) }}" onsubmit="return confirm('Delete this notification?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ntf-btn ntf-btn-red" title="Delete">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($notifications->hasPages())
                <div class="ntf-pager">{{ $notifications->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
            @endif
        @else
            <div class="empty-state text-center py-5">
                <div class="d-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px;height:60px;border-radius:50%;background:var(--color-secondary-soft);color:var(--color-secondary-text);font-size:1.5rem;">
                    <i class="bi bi-bell-slash"></i>
                </div>
                <h6 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;color:var(--color-text);">No Notifications Found</h6>
                <p style="color:var(--color-text-muted);font-size:0.875rem;">You haven't created any notifications yet.</p>
                <a href="{{ route('midwife.notifications.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Create First Notification
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
