@extends('user.layout')

@section('title', 'My Notifications - ReproCare')

@push('styles')
<style>
    .notif-wrap { max-width:720px; margin:0 auto; }
    .notif-page-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.6rem; font-weight:800; color:var(--color-text); letter-spacing:-0.02em; margin:0; }
    .notif-page-sub { color:var(--color-text-muted); font-size:0.9rem; margin:0.15rem 0 0; }
    .notif-count-pill { background:var(--color-primary-soft); background-color:var(--color-primary-soft); color:var(--color-primary-text); border:none; font-size:0.78rem; font-weight:800; padding:0.4em 1em; border-radius:999px; }

    .notif-card {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        border-radius:18px;
        padding:1.1rem 1.3rem;
        margin-bottom:0.75rem;
        display:flex;
        align-items:flex-start;
        gap:1rem;
        transition:all 0.22s ease;
        position:relative;
        overflow:hidden;
        cursor:pointer;
        text-decoration:none;
        color:inherit;
        box-shadow:var(--wp-shadow-sm);
    }
    .notif-card:hover {
        border:none;
        transform:translateY(-2px);
        box-shadow:var(--wp-shadow-md);
        color:inherit;
    }
    .notif-card.unread { border:none; background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); }
    .notif-card.unread.type-danger  { border:none; background:var(--color-danger-soft); background-color:var(--color-danger-soft); }
    .notif-card.unread.type-warning { border:none; background:var(--color-warning-soft); background-color:var(--color-warning-soft); }
    .notif-card.unread.type-success { border:none; background:var(--color-success-soft); background-color:var(--color-success-soft); }
    .notif-card.unread.type-info    { border:none; background:var(--color-primary-soft); background-color:var(--color-primary-soft); }

    /* Read notifications: clean white, no borders */
    .notif-card:not(.unread),
    .notif-card.read-done {
        border:none !important;
        background:var(--color-surface) !important; background-color:var(--color-surface) !important;
        box-shadow:var(--wp-shadow-sm) !important;
        opacity:1;
    }
    .notif-card:not(.unread):hover,
    .notif-card.read-done:hover {
        opacity:1;
        border:none !important;
        box-shadow:var(--wp-shadow-md) !important;
        transform:translateY(-2px);
    }

    .notif-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; border:none; }
    .notif-icon-danger  { background:var(--color-danger-soft); background-color:var(--color-danger-soft); color:var(--color-danger-text); }
    .notif-icon-warning { background:var(--color-warning-soft); background-color:var(--color-warning-soft); color:var(--color-warning-text); }
    .notif-icon-success { background:var(--color-success-soft); background-color:var(--color-success-soft); color:var(--color-success-text); }
    .notif-icon-info    { background:var(--color-primary-soft); background-color:var(--color-primary-soft); color:var(--color-primary-text); }
    .notif-icon-primary { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:var(--color-secondary-text); }

    .notif-body { flex:1; min-width:0; }
    .notif-title { font-family:'Plus Jakarta Sans', sans-serif; font-size:0.9rem; font-weight:800; color:var(--color-text); margin-bottom:0.25rem; letter-spacing:-0.01em; }
    .notif-message { font-size:0.86rem; color:var(--color-text); line-height:1.55; margin:0 0 0.4rem; }
    .notif-time { font-size:0.73rem; color:var(--color-text-muted); display:flex; align-items:center; gap:0.35rem; font-weight:600; }
    .notif-badge-new { background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid); font-size:0.62rem; font-weight:800; padding:0.2em 0.7em; border-radius:999px; text-transform:uppercase; letter-spacing:0.5px; flex-shrink:0; box-shadow:none; margin-top:2px; border:none; }
    .type-badge { font-size:0.64rem; font-weight:800; padding:0.22em 0.7em; border-radius:999px; text-transform:uppercase; letter-spacing:0.5px; border:none; }
    .type-badge-danger  { background:var(--color-danger-soft); background-color:var(--color-danger-soft); color:var(--color-danger-text); }
    .type-badge-warning { background:var(--color-warning-soft); background-color:var(--color-warning-soft); color:var(--color-warning-text); }
    .type-badge-success { background:var(--color-success-soft); background-color:var(--color-success-soft); color:var(--color-success-text); }
    .type-badge-info    { background:var(--color-primary-soft); background-color:var(--color-primary-soft); color:var(--color-primary-text); }
    .notif-view-pill { font-size:0.72rem; font-weight:800; padding:0.35rem 0.85rem; border:none; border-radius:999px; color:var(--color-text); background:var(--color-surface-soft); background-color:var(--color-surface-soft); white-space:nowrap; }

    .mark-all-btn {
        background:var(--color-secondary-soft); background-color:var(--color-secondary-soft);
        border:none;
        color:var(--color-secondary-text);
        font-size:0.78rem;
        font-weight:800;
        padding:0.45em 1.1em;
        border-radius:999px;
        cursor:pointer;
        transition:all 0.2s ease;
        display:inline-flex;
        align-items:center;
        gap:0.4rem;
    }
    .mark-all-btn:hover { background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid); }
    .mark-all-btn:disabled { opacity:0.4; cursor:not-allowed; }
</style>
@endpush

@section('user-content')

<div class="notif-wrap fade-in-card">

    {{-- Header --}}
    @php $unreadCount = $notifications->where('is_read', false)->count(); @endphp
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="notif-page-title">Notifications
            </h1>
            <p class="notif-page-sub">Open an alert to acknowledge it and stop its reminders.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($unreadCount > 0)
                <button id="markAllReadBtn" class="mark-all-btn" onclick="markAllRead()">
                    <i class="bi bi-check2-all"></i> Mark All Read
                </button>
            @endif
            @if($notifications->count() > 0)
                <span class="notif-count-pill">
                    <span id="unread-count-label">{{ $unreadCount }}</span> unread
                </span>
            @endif
        </div>
    </div>

    {{-- List --}}
    @if($notifications->count() > 0)
    @foreach($notifications as $notification)
        @php
            $type      = $notification->type ?? 'info';
            $iconMap   = ['danger' => 'bi-exclamation-circle-fill', 'warning' => 'bi-alarm-fill', 'success' => 'bi-check-circle-fill', 'info' => 'bi-bell-fill'];
            $iconClass = 'notif-icon-' . $type;
            $iconName  = $iconMap[$type] ?? 'bi-bell-fill';
            $isUnread  = !$notification->is_read;
        @endphp
        <div class="notif-card type-{{ $type }} {{ $isUnread ? 'unread' : '' }} fade-in-card"
             id="notif-{{ $notification->id }}"
             data-id="{{ $notification->id }}"
             data-unread="{{ $isUnread ? 'true' : 'false' }}"
             data-action="{{ $notification->action_url ?? '' }}"
             onclick="handleNotifClick(this)">
            <div class="notif-icon {{ $iconClass }}">
                <i class="bi {{ $iconName }}"></i>
            </div>
            <div class="notif-body">
                @if($notification->title)
                    <div class="notif-title">{{ $notification->title }}</div>
                @endif
                <p class="notif-message">{{ $notification->message }}</p>
                @if($notification->resolved_at)
                    <p class="small text-muted">Resolved or superseded — reminders stopped.</p>
                @elseif($notification->category === 'risk' && $notification->last_reminded_at)
                    <p class="small text-muted">Last reminder: {{ $notification->last_reminded_at->format('M j, Y g:i A') }}</p>
                @endif
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="type-badge type-badge-{{ $type }}">{{ ucfirst($type) }}</span>
                    <span class="notif-time">
                        <i class="bi bi-clock"></i>
                        {{ $notification->created_at->diffForHumans() }}
                        @if($notification->read_at)
                            · Read {{ $notification->read_at->diffForHumans() }}
                        @endif
                    </span>
                </div>
            </div>
            <div class="d-flex flex-column align-items-end gap-1">
                @if($isUnread)
                    <span class="notif-badge-new" id="notif-new-badge-{{ $notification->id }}">New</span>
                @endif
                @if(!empty($notification->action_url))
                    <span class="notif-view-pill">
                        View <i class="bi bi-arrow-right ms-1"></i>
                    </span>
                @endif
            </div>
        </div>
    @endforeach
    {{ $notifications->links() }}

    @else
        {{-- Empty State --}}
        <div class="card">
            <div class="empty-state">
                <i class="bi bi-bell-slash empty-state-icon"></i>
                <h6>All Caught Up!</h6>
                <p>No notifications right now. New alerts and reminders will appear here.</p>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
    const _csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    let _unread = {{ $unreadCount }};

    function _syncBadge() {
        const label = document.getElementById('unread-count-label');
        if (label) label.textContent = _unread;

        // Update the nav bell dot
        const bellDot = document.querySelector('.women-bell-dot');
        if (bellDot) {
            if (_unread <= 0) {
                bellDot.style.display = 'none';
            } else {
                bellDot.style.display = '';
                bellDot.textContent = _unread > 9 ? '9+' : _unread;
            }
        }

        // Hide Mark All button when nothing left
        if (_unread <= 0) {
            const btn = document.getElementById('markAllReadBtn');
            if (btn) btn.style.display = 'none';
        }
    }

    async function handleNotifClick(el) {
        const id       = el.dataset.id;
        const isUnread = el.dataset.unread === 'true';
        const action   = el.dataset.action;

        if (isUnread) {
            try {
                const response = await fetch('/api/notifications/' + id + '/read', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }
                });
                if (!response.ok || !(await response.json()).success) throw new Error('Not acknowledged');
            } catch (error) {
                alert('Could not acknowledge this alert. Please check your connection and try again.');
                return;
            }
            el.dataset.unread = 'false';
            el.classList.remove('unread');
            el.classList.add('read-done');
            const badge = document.getElementById('notif-new-badge-' + id);
            if (badge) badge.remove();
            _unread = Math.max(0, _unread - 1);
            _syncBadge();

            if (action) window.location.href = action;
        } else {
            if (action) window.location.href = action;
        }
    }

    function markAllRead() {
        const btn = document.getElementById('markAllReadBtn');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Marking…'; }

        fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }
        })
        .then(r => { if (!r.ok) throw new Error('Not acknowledged'); return r.json(); })
        .then(data => {
            if (!data.success) throw new Error('Not acknowledged');
            document.querySelectorAll('.notif-card[data-unread="true"]').forEach(card => {
                card.dataset.unread = 'false';
                card.classList.remove('unread');
                card.classList.add('read-done');
                const b = card.querySelector('.notif-badge-new');
                if (b) b.remove();
            });
            _unread = 0;
            _syncBadge();
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check2-all"></i> Mark All Read'; }
        });
    }

    document.addEventListener('DOMContentLoaded', _syncBadge);
</script>
@endpush

@endsection
