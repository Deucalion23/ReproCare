@extends(request()->routeIs('midwife.messages.*') ? 'midwife.layout' : (request()->routeIs('bhw.messages.*') ? 'bhw.layout' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president.layout' : 'user.layout')))

@section('title', 'Messages - ReproCare')

@push('styles')
<style>
    /* === Care Messaging — one slim bar, two content-sized panels === */
    .rc-msg-app { --chat-ink:var(--color-surface-strong); --chat-muted:var(--color-text-muted); max-width:1180px; margin:0 auto; padding-bottom:1rem; }

    .rc-msg-hero { display:flex; align-items:center; gap:.85rem; flex-wrap:wrap; background:var(--color-surface); background-color:var(--color-surface); border:1px solid var(--color-border); border-radius:14px; padding:.65rem .9rem; margin-bottom:.8rem; box-shadow:0 1px 2px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent); }
    .rc-msg-hero h1 { font-family:'Plus Jakarta Sans',sans-serif; font-size:.95rem; font-weight:800; color:var(--chat-ink); margin:0; display:flex; align-items:center; gap:.5rem; letter-spacing:-0.01em; white-space:nowrap; }
    .rc-msg-hero h1 i { color:var(--color-secondary-text); font-size:1.05rem; }
    .rc-msg-hero p { margin:0; font-size:.74rem; color:var(--chat-muted); }
    .rc-msg-hero-actions { display:flex; align-items:center; gap:.45rem; margin-left:auto; flex-wrap:wrap; }
    .rc-unread-pill { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:var(--color-secondary-text); border:none; padding:.3rem .7rem; border-radius:999px; font-weight:800; font-size:.72rem; white-space:nowrap; }
    .rc-btn-pink { display:inline-flex; align-items:center; gap:.35rem; background:var(--color-surface-strong); background-color:var(--color-surface-strong); border:none; color:var(--color-on-solid); font-weight:800; font-size:.78rem; padding:.4rem .9rem; border-radius:999px; text-decoration:none; transition:all .18s; white-space:nowrap; }
    .rc-btn-pink:hover { background:var(--color-surface-strong); background-color:var(--color-surface-strong); border:none; color:var(--color-on-solid); opacity:.9; }
    .rc-btn-ghost { display:inline-flex; align-items:center; gap:.35rem; background:var(--color-surface-soft); border:none; color:var(--color-text); font-weight:800; font-size:.78rem; padding:.4rem .85rem; border-radius:999px; text-decoration:none; transition:all .18s; white-space:nowrap; }
    .rc-btn-ghost:hover { border:none; color:var(--color-text); background:var(--color-border); }

    /* Panels size to their own content now — the old 640px floor left a wall of
       empty card under a short chat list. */
    .rc-msg-shell { display:grid; grid-template-columns:340px 1fr; gap:.8rem; align-items:start; }
    @media (max-width:991px){ .rc-msg-shell{ grid-template-columns:1fr; } }
    .rc-panel { background:var(--color-surface); background-color:var(--color-surface); border:1px solid var(--color-border); border-radius:16px; box-shadow:var(--wp-shadow-sm); display:flex; flex-direction:column; overflow:hidden; min-height:0; }
    .rc-panel-head { padding:.7rem .85rem; border-bottom:1px solid var(--color-border); }
    .rc-panel-head-top { display:flex; justify-content:space-between; align-items:center; gap:.6rem; }
    .rc-panel-head-top h3 { font-family:'Plus Jakarta Sans',sans-serif; font-size:.88rem; font-weight:800; color:var(--chat-ink); margin:0; display:flex; align-items:center; gap:.45rem; letter-spacing:-0.01em; }
    .rc-panel-head-top h3 i { color:var(--color-secondary-text); font-size:.95rem; }
    .rc-panel-sub { font-size:.72rem; color:var(--chat-muted); margin:.3rem 0 0; }
    .rc-count { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); border:none; color:var(--color-secondary-text); border-radius:999px; font-size:.68rem; font-weight:800; padding:.16rem .55rem; white-space:nowrap; }
    .rc-search { position:relative; margin-top:.55rem; }
    .rc-search i { position:absolute; left:.7rem; top:50%; transform:translateY(-50%); color:var(--color-text-muted); font-size:.8rem; }
    .rc-search input { width:100%; padding:.4rem .8rem .4rem 2rem; border-radius:999px; border:1px solid var(--color-border); background:var(--color-surface-soft); background-color:var(--color-surface-soft); font-size:.8rem; color:var(--color-text); outline:none; transition:all .18s; }
    .rc-search input:focus { border-color:var(--color-primary); background:var(--color-surface); background-color:var(--color-surface); box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent); }

    .rc-chat-list { list-style:none; margin:0; padding:.35rem; overflow-y:auto; flex:1; max-height:520px; display:flex; flex-direction:column; gap:.15rem; }
    .rc-chat-item { display:flex; align-items:center; gap:.6rem; padding:.5rem .55rem; border-radius:11px; text-decoration:none; color:inherit; border:none; transition:background .16s; }
    .rc-chat-item:hover { background:var(--color-bg); background-color:var(--color-bg); border:none; color:inherit; }
    .rc-chat-item.unread { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); border:none; }
    .rc-avatar { width:36px; height:36px; border-radius:50%; object-fit:cover; border:none; background:var(--color-surface-soft); flex-shrink:0; }
    .rc-chat-meta { flex:1; min-width:0; }
    .rc-chat-top { display:flex; align-items:baseline; gap:.4rem; min-width:0; }
    .rc-chat-name { font-weight:800; font-size:.83rem; color:var(--color-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin:0; min-width:0; }
    .rc-chat-time { font-size:.68rem; color:var(--color-text-muted); white-space:nowrap; margin-left:auto; flex-shrink:0; }
    .rc-chat-bottom { display:flex; align-items:center; gap:.4rem; margin-top:.1rem; min-width:0; }
    .rc-chat-preview { font-size:.76rem; color:var(--color-text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin:0; flex:1; min-width:0; }
    .rc-new-badge { background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid); font-size:.6rem; font-weight:800; min-width:19px; height:17px; padding:0 5px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; letter-spacing:.4px; }
    /* One line for who the person is, instead of a pill on a row of its own. */
    .rc-role-tag { font-size:.66rem; font-weight:800; color:var(--color-text-muted); white-space:nowrap; flex-shrink:0; text-transform:uppercase; letter-spacing:.3px; }

    /* Directory rows: two columns of short lines rather than three columns of
       tall cards, so a long catchment reads as a list you scan, not a wall. */
    .rc-contacts-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:.3rem; padding:.45rem; overflow-y:auto; max-height:520px; align-content:start; }
    .rc-contact { display:flex; align-items:center; gap:.6rem; background:transparent; border:none; border-radius:11px; padding:.4rem .5rem; text-decoration:none; color:inherit; transition:background .16s; min-width:0; }
    .rc-contact:hover { border:none; background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:inherit; }
    .rc-contact img { width:32px; height:32px; border-radius:50%; object-fit:cover; border:none; flex-shrink:0; background:var(--color-border); }
    .rc-contact-txt { flex:1; min-width:0; }
    .rc-contact h6 { font-size:.82rem; font-weight:800; color:var(--color-text); margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .rc-contact small { font-size:.7rem; color:var(--color-text-muted); font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block; }
    .rc-contact.is-hidden { display:none; }
    .rc-no-match { grid-column:1 / -1; text-align:center; padding:1.2rem; font-size:.78rem; color:var(--color-text-muted); }
    .rc-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:1.8rem 1.2rem; color:var(--color-text-muted); }
    .rc-empty i { font-size:1.9rem; color:var(--color-border); margin-bottom:.5rem; }
    .rc-empty h6 { font-weight:800; color:var(--color-text); font-size:.86rem; }
    .rc-empty p { font-size:.76rem; margin:0; }

    @media (max-width:600px) {
        .rc-msg-hero { padding:.6rem .75rem; gap:.5rem; }
        .rc-msg-hero h1 { font-size:.88rem; width:100%; }
        .rc-msg-hero p { font-size:.68rem; }
        .rc-msg-hero-actions { margin-left:0; width:100%; }
        .rc-msg-hero-actions .rc-btn-pink, .rc-msg-hero-actions .rc-btn-ghost { flex:1; justify-content:center; }
        .rc-msg-shell { gap:.65rem; }
        .rc-chat-list { max-height:300px; }
        .rc-contacts-grid { grid-template-columns:1fr; max-height:360px; }
        .rc-avatar { width:32px; height:32px; }
        .rc-chat-name { font-size:.78rem; }
        .rc-chat-preview { font-size:.72rem; }
    }
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
        <h1><i class="bi bi-chat-heart-fill"></i> Care Messaging</h1>
        <p>Secure maternal health consultation with your care team.</p>
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
                    <h3><i class="bi bi-threads"></i> Chats</h3>
                    <span class="rc-count">{{ $messages->count() }}</span>
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
                        $unreadForCurrentUser = ($msg->conversation_unread_count ?? 0) > 0 || (!$msg->is_read && $msg->receiver_id === $currentUserId);
                        $otherParty = $sentByCurrentUser ? $msg->receiver : $msg->sender;
                        $lastActivity = $msg->replies_max_created_at ? \Carbon\Carbon::parse($msg->replies_max_created_at) : $msg->created_at;
                        $previewPrefix = $msg->conversation_preview_prefix ?? ($sentByCurrentUser ? 'You: ' : '');
                        $otherPhoto = ($otherParty ? $otherParty->profile_image_url : null) ?: '/images/avatars/avatar-female.svg';
                        $otherRole = $otherParty?->staff_title ?? 'Member';
                    @endphp
                    <a href="{{ route($messagesRouteBase . '.thread', $msg->id) }}" class="rc-chat-item {{ $unreadForCurrentUser ? 'unread' : '' }}">
                        <img src="{{ $otherPhoto }}" alt="{{ optional($otherParty)->name ?? 'User' }}" class="rc-avatar" onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                        <div class="rc-chat-meta">
                            <div class="rc-chat-top">
                                <h5 class="rc-chat-name">{{ optional($otherParty)->name ?? 'Healthcare Provider' }}</h5>
                                <span class="rc-role-tag">{{ $otherRole }}</span>
                                <span class="rc-chat-time">{{ $lastActivity->diffForHumans(null, true, true) }}</span>
                            </div>
                            <div class="rc-chat-bottom">
                                <p class="rc-chat-preview">{{ $previewPrefix }}{{ \Illuminate\Support\Str::limit($msg->conversation_preview ?? $msg->body, 55) }}</p>
                                @if($unreadForCurrentUser)<span class="rc-new-badge">NEW</span>@endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="rc-empty">
                        <i class="bi bi-chat-square-dots"></i>
                        <h6>No conversations yet</h6>
                        <p>Pick a contact on the right to start a consultation.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rc-panel">
            <div class="rc-panel-head">
                <div class="rc-panel-head-top">
                    <h3><i class="bi bi-people-fill"></i> Available Contacts</h3>
                    <span class="rc-count" id="rcContactCount">{{ $contacts->count() }}</span>
                </div>
                <p class="rc-panel-sub">@if($currentUserRole === 'user') BHW staff assigned to your barangay. @elseif($currentUserRole === 'midwife') BHW Presidents in your designated barangays. @elseif($currentUserRole === 'bhw') Patients and BHW Presidents in your barangay. @elseif($currentUserRole === 'bhw_president') Registered women living in your barangay. @else Staff &amp; patient directory. @endif</p>
                @if($contacts->isNotEmpty())
                    <div class="rc-search">
                        <i class="bi bi-search"></i>
                        <input type="search" id="rcContactFilter" placeholder="Filter by name or barangay..." autocomplete="off" aria-label="Filter contacts">
                    </div>
                @endif
            </div>
            @if($contacts->isEmpty())
                <div class="rc-empty">
                    <i class="bi bi-people"></i>
                    <h6>No contacts found</h6>
                    <p>@if($currentUserRole === 'user') No BHW is assigned to your barangay right now. @elseif($currentUserRole === 'midwife') No BHW President is assigned to your designated barangays yet. @elseif($currentUserRole === 'bhw') No patients or BHW Presidents are in your barangay yet. @elseif($currentUserRole === 'bhw_president') No registered women live in your barangay yet. @else No available contacts under your health center right now. @endif</p>
                </div>
            @else
                <div class="rc-contacts-grid" id="rcContactGrid">
                    @foreach($contacts as $contact)
                        @php $contactPhoto = $contact->photo ?: '/images/avatars/avatar-female.svg'; @endphp
                        <a href="{{ route($messagesRouteBase . '.create', ['to' => $contact->id, 'role' => $contact->role]) }}" class="rc-contact"
                           data-search="{{ strtolower($contact->name.' '.$contact->label.' '.$contact->role) }}">
                            <img src="{{ $contactPhoto }}" alt="{{ $contact->name }}" onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                            <span class="rc-contact-txt">
                                <h6>{{ $contact->name }}</h6>
                                <small>{{ $contact->label }}</small>
                            </span>
                        </a>
                    @endforeach
                    <div class="rc-no-match" id="rcContactNoMatch" hidden>No contact matches that filter.</div>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // The directory is already on the page, so filtering it should not cost a
    // round trip — and it must not touch the conversation search above it.
    (function () {
        const input = document.getElementById('rcContactFilter');
        const grid = document.getElementById('rcContactGrid');
        if (!input || !grid) return;

        const rows = Array.from(grid.querySelectorAll('.rc-contact'));
        const count = document.getElementById('rcContactCount');
        const noMatch = document.getElementById('rcContactNoMatch');

        input.addEventListener('input', function () {
            const needle = this.value.trim().toLowerCase();
            let shown = 0;
            rows.forEach(function (row) {
                const hit = needle === '' || (row.dataset.search || '').includes(needle);
                row.classList.toggle('is-hidden', !hit);
                if (hit) shown++;
            });
            count.textContent = needle === '' ? rows.length : shown + '/' + rows.length;
            noMatch.hidden = shown !== 0;
        });
    })();
</script>
@endpush
