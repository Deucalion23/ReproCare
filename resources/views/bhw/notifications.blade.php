@extends('bhw.layout')

@section('title', 'My Notifications - ReproCare')

@push('styles')
<style>
    .notif-wrap { max-width:720px; margin:0 auto; }

    .notif-card {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:16px;
        padding:1rem 1.25rem;
        margin-bottom:0.75rem;
        display:flex;
        align-items:flex-start;
        gap:1rem;
        transition:all 0.2s ease;
        position:relative;
        overflow:hidden;
        text-decoration:none;
        color:inherit;
    }

    .notif-card:hover {
        border-color:var(--primary);
        transform:translateX(4px);
        box-shadow:var(--shadow-sm);
        color:inherit;
    }

    .notif-card.unread { border-left:3px solid var(--primary); background:linear-gradient(90deg, var(--primary-subtle), var(--bg-card)); }
    .notif-card.type-danger { border-left:3px solid var(--color-danger); background:linear-gradient(90deg, color-mix(in srgb, var(--color-danger) 5%, transparent), var(--bg-card)); }
    .notif-card.type-warning { border-left:3px solid var(--color-warning); background:linear-gradient(90deg, color-mix(in srgb, var(--color-warning) 5%, transparent), var(--bg-card)); }
    .notif-card.type-success { border-left:3px solid var(--color-success); background:linear-gradient(90deg, color-mix(in srgb, var(--color-success) 5%, transparent), var(--bg-card)); }
    .notif-card.type-info { border-left:3px solid var(--color-info); background:linear-gradient(90deg, color-mix(in srgb, var(--color-info) 5%, transparent), var(--bg-card)); }

    .notif-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
    .notif-icon-danger { background:color-mix(in srgb, var(--color-danger) 12%, transparent); color:var(--color-danger-text); }
    .notif-icon-warning { background:color-mix(in srgb, var(--color-warning) 12%, transparent); color:var(--color-warning-text); }
    .notif-icon-success { background:color-mix(in srgb, var(--color-success) 12%, transparent); color:var(--color-success-text); }
    .notif-icon-info { background:color-mix(in srgb, var(--color-info) 12%, transparent); color:var(--color-info-text); }

    .notif-body { flex:1; min-width:0; }
    .notif-title { font-family:'Plus Jakarta Sans', sans-serif; font-size:0.875rem; font-weight:700; color:var(--text); margin-bottom:0.2rem; }
    .notif-message { font-size:0.85rem; color:var(--text-muted); line-height:1.5; margin:0 0 0.3rem; }
    .notif-time { font-size:0.73rem; color:var(--text-muted); display:flex; align-items:center; gap:0.3rem; }
    .notif-badge-new { background:var(--primary); color:var(--color-on-solid); font-size:0.62rem; font-weight:700; padding:0.12em 0.6em; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px; flex-shrink:0; box-shadow:0 2px 8px var(--primary-glow); margin-top:2px; }
    .type-badge { font-size:0.68rem; font-weight:700; padding:0.15em 0.6em; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px; }
    .type-badge-danger { background:color-mix(in srgb, var(--color-danger) 15%, transparent); color:var(--color-danger-text); }
    .type-badge-warning { background:color-mix(in srgb, var(--color-warning) 15%, transparent); color:var(--color-warning-text); }
    .type-badge-success { background:color-mix(in srgb, var(--color-success) 15%, transparent); color:var(--color-success-text); }
    .type-badge-info { background:color-mix(in srgb, var(--color-info) 15%, transparent); color:var(--color-info-text); }
</style>
@endpush

@section('bhw-content')
<div class="notif-wrap fade-in-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Notifications
            </h1>
            <p class="page-subtitle">Your alerts, reminders, and health updates</p>
        </div>
        @if($notifications->count() > 0)
            <span style="background:var(--primary-subtle);border:1px solid var(--border-glass);color:var(--primary-light);font-size:0.8rem;font-weight:700;padding:0.35em 1em;border-radius:20px;">
                {{ $notifications->total() }} total
            </span>
        @endif
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
                    @if(!$notification->is_read)
                        <span class="notif-badge-new">New</span>
                    @endif
                    @if($hasAction)
                        <a href="{{ $notification->action_url }}" class="btn btn-sm" style="font-size:0.72rem;padding:0.2rem 0.6rem;border:1px solid var(--border);border-radius:8px;color:var(--primary-light);background:var(--primary-subtle);">
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
