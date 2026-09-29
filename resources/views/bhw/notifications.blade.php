@extends('bhw.layout')

@section('title', 'My Notifications - ReproCare')

@push('styles')
<style>
    .notif-wrap { max-width:720px; margin:0 auto; }

    .notif-card {
        background:var(--color-surface);
        background-color:var(--color-surface);
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

    .notif-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
    .notif-icon-danger { background:color-mix(in srgb, var(--color-danger) 12%, transparent); color:var(--color-danger-text); }
    .notif-icon-warning { background:color-mix(in srgb, var(--color-warning) 12%, transparent); color:var(--color-warning-text); }
    .notif-icon-success { background:color-mix(in srgb, var(--color-success) 12%, transparent); color:var(--color-success-text); }
    .notif-icon-info { background:color-mix(in srgb, var(--color-info) 12%, transparent); color:var(--color-info-text); }

    .notif-body { flex:1; min-width:0; align-self:center; }
    .notif-side { align-self:center; flex-shrink:0; }
    .notif-title { font-family:'Plus Jakarta Sans', sans-serif; font-size:0.875rem; font-weight:700; color:var(--text); margin-bottom:0.2rem; }
    .notif-message { font-size:0.85rem; color:var(--text-muted); line-height:1.5; margin:0 0 0.3rem; }
    .notif-time { font-size:0.73rem; color:var(--text-muted); display:flex; align-items:center; gap:0.3rem; }
    .notif-badge-new { background:var(--primary); color:var(--color-on-solid); font-size:0.62rem; font-weight:700; padding:0.12em 0.6em; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px; flex-shrink:0; box-shadow:0 2px 8px var(--primary-glow); margin-top:2px; }
    .type-badge { font-size:0.68rem; font-weight:700; padding:0.15em 0.6em; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px; }
    .type-badge-danger { background:color-mix(in srgb, var(--color-danger) 15%, transparent); color:var(--color-danger-text); }
    .type-badge-warning { background:color-mix(in srgb, var(--color-warning) 15%, transparent); color:var(--color-warning-text); }
    .type-badge-success { background:color-mix(in srgb, var(--color-success) 15%, transparent); color:var(--color-success-text); }
    .type-badge-info { background:color-mix(in srgb, var(--color-info) 15%, transparent); color:var(--color-info-text); }

    /* ── Mobile: patient-notification sizes and stacking ── */
    @media (max-width: 600px) {
        .notif-card { padding:0.8rem 0.9rem; gap:0.6rem; border-radius:14px; flex-wrap:wrap; align-items:flex-start; }
        .notif-icon { width:32px; height:32px; font-size:0.85rem; border-radius:9px; }
        .notif-title { font-size:0.75rem; }
        .notif-message { font-size:0.7rem; }
        .notif-time { font-size:0.62rem; flex-wrap:wrap; }
        .notif-badge-new { font-size:0.58rem; }
        .type-badge { font-size:0.56rem; }
        .notif-side { flex-direction:row !important; align-items:center !important; width:100%; justify-content:flex-end; }
    }
</style>
@endpush

@section('bhw-content')
<div class="notif-wrap fade-in-card">
<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Notifications</div>
            <p class="page-hero-subtitle">Check task assignments and referral updates.</p>
        </div>
        @if($notifications->count() > 0)
            <span style="background:var(--primary-subtle);border:1px solid var(--border-glass);color:var(--primary-light);font-size:0.8rem;font-weight:700;padding:0.35em 1em;border-radius:20px;">
                {{ $notifications->total() }} total
            </span>
        @endif
    </div>
</div>

    @if($notifications->count() > 0)
        @foreach($notifications as $notification)
            @php
                $type = $notification->type ?? 'info';
                $iconMap = ['danger' => 'bi-exclamation-circle-fill', 'warning' => 'bi-alarm-fill', 'success' => 'bi-check-circle-fill', 'info' => 'bi-bell-fill'];
                $iconClass = 'notif-icon-' . $type;
                $iconName = $iconMap[$type] ?? 'bi-bell-fill';
                $hasAction = !empty($notification->action_url);
            @endphp
            <div class="notif-card type-{{ $type }} {{ !$notification->is_read ? 'unread' : '' }} fade-in-card">
                <div class="notif-icon {{ $iconClass }}">
                    <i class="bi {{ $iconName }}"></i>
                </div>
                <div class="notif-body">
                    @if($notification->title)
                        <div class="notif-title">{{ $notification->title }}</div>
                    @endif
                    <p class="notif-message">{{ $notification->message }}</p>
                    @include('includes.patient-alert-receipt')
                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
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
                <div class="notif-side d-flex flex-column align-items-end gap-1">
                    @if(!$notification->is_read)
                        <span class="notif-badge-new">New</span>
                    @endif
                    @if($hasAction)
                        <a href="{{ $notification->action_url }}" class="btn btn-sm" style="display:inline-flex;align-items:center;justify-content:center;text-align:center;font-size:0.72rem;padding:0.25rem 0.7rem;border:1px solid var(--border);border-radius:8px;color:var(--primary-light);background:var(--primary-subtle);">
                            View <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="card">
            <div class="empty-state">
                <i class="bi bi-bell-slash empty-state-icon"></i>
                <h6>All Caught Up!</h6>
                <p>No notifications right now. New alerts and reminders will appear here.</p>
            </div>
        </div>
    @endif
</div>
@endsection
