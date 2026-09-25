@extends(request()->routeIs('midwife.messages.*') ? 'midwife.layout' : (request()->routeIs('bhw.messages.*') ? 'bhw.layout' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president.layout' : 'user.layout')))

@section('title', 'Messages - ReproCare')

@push('styles')
<style>
    /* === Care Messaging — clean, borderless, dashboard-matched === */
    .rc-msg-app { --chat-primary:var(--color-secondary-text); --chat-primary-dk:var(--color-secondary-text); --chat-ink:var(--color-surface-strong); --chat-muted:var(--color-text-muted); --chat-faint:var(--color-text-muted); --chat-soft:var(--color-bg); max-width:1200px; margin:0 auto; padding-bottom:2rem; }
    .rc-msg-hero { background:var(--color-surface); background-color:var(--color-surface); border:none; border-radius:20px; padding:1.35rem 1.6rem; margin-bottom:1.25rem; box-shadow:var(--wp-shadow-sm); display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; }
    .rc-msg-hero h1 { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.35rem; font-weight:800; color:var(--chat-ink); margin:0; display:flex; align-items:center; gap:.6rem; letter-spacing:-0.01em; }
    .rc-msg-hero h1 .rc-msg-ico { width:42px; height:42px; border-radius:12px; background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:1px solid var(--color-border); color:var(--color-text); display:inline-flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
    .rc-msg-hero p { margin:.25rem 0 0; font-size:.85rem; color:var(--chat-muted); }
    .rc-msg-hero-actions { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
    .rc-unread-pill { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:var(--color-secondary-text); border:none; padding:.5rem .95rem; border-radius:999px; font-weight:800; font-size:.78rem; white-space:nowrap; }
    .rc-btn-pink { display:inline-flex; align-items:center; justify-content:center; gap:.45rem; background:var(--color-surface-strong); background-color:var(--color-surface-strong); border:none; color:var(--color-on-solid); font-weight:800; font-size:.86rem; padding:.62rem 1.3rem; border-radius:999px; text-decoration:none; box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent); transition:all .2s; white-space:nowrap; }
    .rc-btn-pink:hover { background:var(--color-surface-strong); background-color:var(--color-surface-strong); border:none; color:var(--color-on-solid); transform:translateY(-1px); }
    .rc-btn-ghost { display:inline-flex; align-items:center; gap:.45rem; background:var(--color-surface-soft); border:none; color:var(--color-text); font-weight:800; font-size:.86rem; padding:.6rem 1.1rem; border-radius:999px; text-decoration:none; transition:all .2s; }
    .rc-btn-ghost:hover { border:none; color:var(--color-text); background:var(--color-border); }

    .rc-msg-shell { display:grid; grid-template-columns:370px 1fr; gap:1.25rem; min-height:640px; align-items:start; }
    @media (max-width:991px){ .rc-msg-shell{ grid-template-columns:1fr; } }
    .rc-panel { background:var(--color-surface); background-color:var(--color-surface); border:none; border-radius:20px; box-shadow:var(--wp-shadow-sm); display:flex; flex-direction:column; overflow:hidden; min-height:640px; }
    .rc-panel-head { padding:1.15rem 1.3rem; border:none; }
    .rc-panel-head-top { display:flex; justify-content:space-between; align-items:center; gap:.75rem; margin-bottom:.8rem; }
    .rc-panel-head-top h3 { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.02rem; font-weight:800; color:var(--chat-ink); margin:0; display:flex; align-items:center; gap:.5rem; letter-spacing:-0.01em; }
    .rc-panel-head-top h3 i { color:var(--color-secondary-text); }
    .rc-panel-sub { font-size:.8rem; color:var(--chat-muted); margin:.3rem 0 0; }
    .rc-count { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); border:none; color:var(--color-secondary-text); border-radius:999px; font-size:.72rem; font-weight:800; padding:.25rem .75rem; }
    .rc-search { position:relative; }
    .rc-search i { position:absolute; left:.9rem; top:50%; transform:translateY(-50%); color:var(--color-text-muted); font-size:.88rem; }
    .rc-search input { width:100%; padding:.65rem 1rem .65rem 2.5rem; border-radius:999px; border:none; background:var(--color-surface-soft); background-color:var(--color-surface-soft); font-size:.86rem; color:var(--color-text); outline:none; transition:all .2s; }
    .rc-search input:focus { border:none; background:var(--color-surface); background-color:var(--color-surface); box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent); }

    .rc-chat-list { list-style:none; margin:0; padding:.7rem; overflow-y:auto; flex:1; max-height:620px; display:flex; flex-direction:column; gap:.3rem; }
    .rc-chat-item { display:flex; align-items:center; gap:.8rem; padding:.8rem; border-radius:14px; text-decoration:none; color:inherit; border:none; transition:all .18s; }
    .rc-chat-item:hover { background:var(--color-bg); background-color:var(--color-bg); border:none; color:inherit; }
    .rc-chat-item.unread { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); border:none; }
    .rc-avatar-wrap { position:relative; flex-shrink:0; }
    .rc-avatar { width:48px; height:48px; border-radius:50%; object-fit:cover; border:none; background:var(--color-surface-soft); }
    .rc-online { position:absolute; bottom:1px; right:1px; width:12px; height:12px; background:var(--color-success); border:2px solid var(--color-border); border-radius:50%; }
    .rc-chat-meta { flex:1; min-width:0; }
    .rc-chat-top { display:flex; justify-content:space-between; align-items:baseline; gap:.5rem; }
    .rc-chat-name { font-weight:800; font-size:.9rem; color:var(--color-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin:0; }
    .rc-chat-time { font-size:.72rem; color:var(--color-text-muted); white-space:nowrap; flex-shrink:0; }
    .rc-chat-bottom { display:flex; justify-content:space-between; align-items:center; gap:.5rem; margin-top:.15rem; }
    .rc-chat-preview { font-size:.82rem; color:var(--color-text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin:0; flex:1; }
    .rc-new-badge { background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid); font-size:.64rem; font-weight:800; min-width:22px; height:20px; padding:0 6px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; letter-spacing:.4px; }
    .rc-role-tag { display:inline-block; margin-top:.35rem; font-size:.66rem; font-weight:800; padding:.18rem .6rem; border-radius:999px; border:none; text-transform:uppercase; letter-spacing:.4px; }
    .rc-role-midwife { background:var(--color-success-soft); background-color:var(--color-success-soft); color:var(--color-success-text); }
    .rc-role-bhw { background:var(--color-peach-soft); background-color:var(--color-peach-soft); color:var(--color-warning-text); }
    .rc-role-president { background:var(--color-primary-soft); background-color:var(--color-primary-soft); color:var(--color-primary-text); }
    .rc-role-patient { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:var(--color-secondary-text); }
    .rc-role-member { background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text); }

    .rc-contacts-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:.8rem; padding:1.25rem; overflow-y:auto; }
    .rc-contact { display:flex; align-items:center; gap:.85rem; background:var(--color-bg); background-color:var(--color-bg); border:none; border-radius:16px; padding:.95rem 1rem; text-decoration:none; color:inherit; transition:all .2s; }
    .rc-contact:hover { border:none; background:var(--color-surface); background-color:var(--color-surface); transform:translateY(-2px); box-shadow:var(--wp-shadow-sm); color:inherit; }
    .rc-contact img { width:44px; height:44px; border-radius:50%; object-fit:cover; border:none; flex-shrink:0; background:var(--color-border); }
    .rc-contact h6 { font-size:.88rem; font-weight:800; color:var(--color-text); margin:0 0 .2rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .rc-contact small { font-size:.72rem; color:var(--color-text-muted); font-weight:700; display:flex; align-items:center; gap:.3rem; }
    .rc-contact small i { color:var(--color-secondary-text); }
    .rc-contact-go { width:32px; height:32px; border-radius:50%; background:var(--color-surface); background-color:var(--color-surface); color:var(--color-text); display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
    .rc-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:3.5rem 1.5rem; color:var(--color-text-muted); }
    .rc-empty i { font-size:2.8rem; color:var(--color-border); margin-bottom:.8rem; }
    .rc-empty h6 { font-weight:800; color:var(--color-text); }
    .rc-empty p { font-size:.84rem; }
</style>
@endpush

@php
    $contentSection = request()->routeIs('midwife.messages.*') ? 'midwife-content' : (request()->routeIs('bhw.messages.*') ? 'bhw-content' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president-content' : 'user-content'));
    $messagesRouteBase = request()->routeIs('midwife.messages.*') ? 'midwife.messages' : (request()->routeIs('bhw.messages.*') ? 'bhw.messages' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president.messages' : 'user.messages'));
    $currentUser = auth()->user();
    $currentUserId = $currentUser?->id;
    $currentUserRole = $currentUser?->role;
@endphp

@section($contentSection)
<div class="rc-msg-app">
    <div class="rc-msg-hero">
        <div class="d-flex align-items-center gap-3">
            <div>
                <h1>Care Messaging</h1>
                <p>Secure maternal health consultation with your care team.</p>
            </div>
        </div>
        <div class="rc-msg-hero-actions">
            @if($unreadCount > 0)
                <span class="rc-unread-pill"><i class="bi bi-envelope-fill me-1"></i>{{ $unreadCount }} Unread</span>
            @endif
            <a href="{{ route($messagesRouteBase . '.trash') }}" class="rc-btn-ghost"><i class="bi bi-trash"></i> Trash</a>
            <a href="{{ route($messagesRouteBase . '.create') }}" class="rc-btn-pink"><i class="bi bi-pencil-square"></i> Compose</a>
        </div>
    </div>

    <div class="rc-msg-shell">
        <section class="rc-panel">
            <div class="rc-panel-head">
                <div class="rc-panel-head-top">
                    <h3>Chats</h3>
                    <span class="rc-count">{{ $messages->count() }} active</span>
                </div>
                <form method="GET" class="rc-search">
                    <i class="bi bi-search"></i>
                    <input type="search" name="search" placeholder="Search conversations..." value="{{ $search ?? '' }}" autocomplete="off">
                </form>
            </div>
            <div class="rc-chat-list">
                @forelse($messages as $msg)
                    @php
                        $sentByCurrentUser = $msg->sender_id === $currentUserId;
                        $unreadForCurrentUser = !$msg->is_read && $msg->receiver_id === $currentUserId;
                        $otherParty = $sentByCurrentUser ? $msg->receiver : $msg->sender;
                        $lastActivity = $msg->replies_max_created_at ? \Carbon\Carbon::parse($msg->replies_max_created_at) : $msg->created_at;
                        $previewPrefix = $sentByCurrentUser ? 'You: ' : '';
                        $otherPhoto = $otherParty ? $otherParty->profile_image_url : '/images/avatars/avatar-female.svg';
                        $otherRole = match ($otherParty?->role) { 'midwife' => 'Midwife', 'bhw' => 'BHW', 'bhw_president' => 'BHW President', 'user' => 'Patient', default => 'Member', };
                        $roleCls = match ($otherParty?->role) { 'midwife' => 'rc-role-midwife', 'bhw' => 'rc-role-bhw', 'bhw_president' => 'rc-role-president', 'user' => 'rc-role-patient', default => 'rc-role-member', };
                    @endphp
                    <a href="{{ route($messagesRouteBase . '.thread', $msg->id) }}" class="rc-chat-item {{ $unreadForCurrentUser ? 'unread' : '' }}">
                        <div class="rc-avatar-wrap">
                            <img src="{{ $otherPhoto }}" alt="{{ optional($otherParty)->name ?? 'User' }}" class="rc-avatar" onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                            <span class="rc-online"></span>
                        </div>
                        <div class="rc-chat-meta">
                            <div class="rc-chat-top">
                                <h5 class="rc-chat-name">{{ optional($otherParty)->name ?? 'Healthcare Provider' }}</h5>
                                <span class="rc-chat-time">{{ $lastActivity->diffForHumans(null, true, true) }}</span>
                            </div>
                            <div class="rc-chat-bottom">
                                <p class="rc-chat-preview">{{ $previewPrefix }}{{ \Illuminate\Support\Str::limit($msg->body, 55) }}</p>
                                @if($unreadForCurrentUser)<span class="rc-new-badge">NEW</span>@endif
                            </div>
                            <span class="rc-role-tag {{ $roleCls }}">{{ $otherRole }}</span>
                        </div>
                    </a>
                @empty
                    <div class="rc-empty">
                        <i class="bi bi-chat-square-dots"></i>
                        <h6>No conversations yet</h6>
                        <p>Pick a provider on the right to start a consultation.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rc-panel">
            <div class="rc-panel-head">
                <div class="rc-panel-head-top" style="margin-bottom:0;">
                    <h3>Available Contacts</h3>
                </div>
                <p class="rc-panel-sub">@if($currentUserRole === 'user') Tap a provider to start a new conversation. @else Staff &amp; patient directory. @endif</p>
            </div>
            @if($contacts->isEmpty())
                <div class="rc-empty">
                    <i class="bi bi-people"></i>
                    <h6>No contacts found</h6>
                    <p>No available contacts under your health center right now.</p>
                </div>
            @else
                <div class="rc-contacts-grid">
                    @foreach($contacts as $contact)
                        @php $contactUser = \App\Models\User::find($contact->id); $contactPhoto = $contactUser ? $contactUser->profile_image_url : '/images/avatars/avatar-female.svg'; @endphp
                        <a href="{{ route($messagesRouteBase . '.create', ['to' => $contact->id, 'role' => $contact->role]) }}" class="rc-contact">
                            <img src="{{ $contactPhoto }}" alt="{{ $contact->name }}" onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                            <div style="flex:1;min-width:0;">
                                <h6>{{ $contact->name }}</h6>
                                <small><i class="bi bi-shield-check"></i> {{ $contact->label }}</small>
                            </div>
                            <span class="rc-contact-go"><i class="bi bi-chat-dots"></i></span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
