@extends('midwife.layout')

@section('title', 'Notification Details - ReproCare')

@section('midwife-content')

@php
    $iconClass = match($notification->type) {
        'info'    => ['bg' => 'var(--color-info-soft)', 'color' => 'var(--color-info-text)', 'icon' => 'bi-info-circle-fill'],
        'warning' => ['bg' => 'var(--color-warning-soft)', 'color' => 'var(--color-warning-text)', 'icon' => 'bi-exclamation-triangle-fill'],
        'success' => ['bg' => 'var(--color-success-soft)', 'color' => 'var(--color-success-text)', 'icon' => 'bi-check-circle-fill'],
        default   => ['bg' => 'var(--color-danger-soft)', 'color' => 'var(--color-danger-text)', 'icon' => 'bi-x-circle-fill'],
    };
    $safeTitle = htmlspecialchars_decode(strip_tags($notification->title ?? 'Notification'));
@endphp

{{-- Page Hero --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Notification Details
            </div>
            <p class="page-hero-subtitle">Review the full notification content and destination.</p>
        </div>
        <a href="{{ route('midwife.notifications.index') }}" class="btn-hero-secondary">
            <i class="bi bi-arrow-left"></i> Back to Notifications
        </a>
    </div>
</div>

<div class="d-flex justify-content-center">
    <div style="width:100%; max-width:680px;">
        <div class="card fade-in-card">
            <div class="card-body p-5">
                {{-- Type Icon --}}
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div style="width:52px;height:52px;border-radius:16px;background:{{ $iconClass['bg'] }};color:{{ $iconClass['color'] }};display:flex;align-items:center;justify-content:center;font-size:1.35rem;flex-shrink:0;">
                        <i class="bi {{ $iconClass['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge rounded-pill fw-bold" style="background:{{ $iconClass['bg'] }};color:{{ $iconClass['color'] }};border:1px solid currentColor;font-size:0.75rem;padding:0.35em 0.85em;">
                                {{ ucfirst($notification->type) }}
                            </span>
                            <span class="badge rounded-pill fw-bold" style="{{ $notification->is_read ? 'background:var(--color-bg);color:var(--color-text-muted);border:1px solid var(--color-border);' : 'background:var(--color-primary-soft);color:var(--color-primary-text);border:1px solid var(--color-primary-soft);' }}font-size:0.75rem;padding:0.35em 0.85em;">
                                {{ $notification->is_read ? 'Read' : 'Unread' }}
                            </span>
                            @if($notification->target_role)
                                <span class="badge rounded-pill fw-bold" style="background:var(--color-primary-soft);color:var(--color-primary-text);border:1px solid var(--color-border);font-size:0.75rem;padding:0.35em 0.85em;">
                                    {{ ucfirst($notification->target_role) }}
                                </span>
                            @endif
                        </div>
                        <div style="font-size:0.78rem;color:var(--mw-body);margin-top:0.3rem;">
                            <i class="bi bi-clock me-1"></i>{{ $notification->created_at->format('F j, Y \a\t g:i A') }}
                        </div>
                    </div>
                </div>

                {{-- Title --}}
                <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:1.35rem;color:var(--mw-heading);margin-bottom:1rem;">
                    {{ $safeTitle }}
                </h2>

                {{-- Message --}}
                <div style="background:var(--color-primary-soft);border:1px solid var(--color-border);border-radius:14px;padding:1.35rem 1.5rem;white-space:pre-line;font-size:0.93rem;color:var(--mw-heading);line-height:1.7;margin-bottom:1.75rem;">
                    {{ strip_tags($notification->message) }}
                </div>

                {{-- Actions --}}
                @include('includes.patient-alert-receipt')
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    @if($notification->action_url)
                        <a href="{{ $notification->action_url }}" class="btn-hero-primary">
                            <i class="bi bi-box-arrow-up-right"></i> Open Linked Page
                        </a>
                    @endif
                    <a href="{{ route('midwife.notifications.index') }}" class="btn-hero-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    @if(!$notification->is_read)
                        <form method="POST" action="{{ route('midwife.notifications.mark-read', $notification->id) }}" class="d-inline ms-auto">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background:var(--color-success-soft);border:1px solid var(--color-success-soft);color:var(--color-success-text);border-radius:10px;font-weight:700;padding:0.45rem 1rem;">
                                <i class="bi bi-check-lg me-1"></i> Mark as Read
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
