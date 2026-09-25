@extends(request()->routeIs('midwife.messages.*') ? 'midwife.layout' : (request()->routeIs('bhw.messages.*') ? 'bhw.layout' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president.layout' : 'user.layout')))

@section('title', 'Conversation - ReproCare')

@push('styles')
<style>
    .rc-thread { --chat-primary:var(--color-secondary); --chat-dark:var(--color-secondary-text); --chat-soft:var(--color-secondary-soft); --chat-border:var(--color-secondary-soft); max-width:960px; margin:0 auto; padding-bottom:2rem; }
    .rc-chat-card { background:var(--color-surface); border:1px solid var(--color-border); border-radius:20px; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent); overflow:hidden; display:flex; flex-direction:column; height:78vh; min-height:560px; }
    .rc-chat-topbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.85rem 1.25rem; border-bottom:1px solid var(--color-border); background:var(--color-surface); }
    .rc-chat-peer { display:flex; align-items:center; gap:.85rem; min-width:0; }
    .rc-back { width:38px; height:38px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--color-border); background:var(--color-surface); color:var(--color-text); text-decoration:none; transition:all .2s; flex-shrink:0; }
    .rc-back:hover { background:var(--color-secondary-soft); border-color:var(--color-secondary-soft); color:var(--color-secondary-text); }
    .rc-peer-avatar { position:relative; flex-shrink:0; }
    .rc-peer-avatar img { width:46px; height:46px; border-radius:50%; object-fit:cover; border:2px solid var(--color-secondary-soft); background:var(--color-secondary-soft); }
    .rc-peer-avatar span { position:absolute; bottom:1px; right:1px; width:12px; height:12px; background:var(--color-success); border:2px solid var(--color-border); border-radius:50%; }
    .rc-peer-name { font-family:'Plus Jakarta Sans',sans-serif; font-size:1rem; font-weight:800; color:var(--color-text); margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .rc-peer-sub { display:flex; align-items:center; gap:.45rem; font-size:.75rem; color:var(--color-text-muted); margin-top:.15rem; }
    .rc-role-pill { font-size:.66rem; font-weight:800; padding:.15rem .6rem; border-radius:999px; background:var(--color-secondary-soft); color:var(--color-secondary-text); border:1px solid var(--color-secondary-soft); text-transform:uppercase; letter-spacing:.4px; }
    .rc-top-actions { display:flex; align-items:center; gap:.5rem; }
    .rc-icon-btn { width:38px; height:38px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; border:1px solid var(--color-border); background:var(--color-surface); color:var(--color-text); transition:all .2s; cursor:pointer; text-decoration:none; }
    .rc-icon-btn:hover { background:var(--color-secondary-soft); border-color:var(--color-secondary-soft); color:var(--color-secondary-text); }

    .rc-stream { flex:1; overflow-y:auto; padding:1.4rem; background:var(--color-surface-soft); display:flex; flex-direction:column; gap:.8rem; }
    .rc-day { align-self:center; background:var(--color-surface); border:1px solid var(--color-border); color:var(--color-text-muted); font-size:.7rem; font-weight:700; padding:.25rem .85rem; border-radius:999px; text-transform:uppercase; letter-spacing:.5px; box-shadow:0 1px 4px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent); }
    .rc-row { display:flex; align-items:flex-end; gap:.6rem; max-width:78%; }
    .rc-row.theirs { align-self:flex-start; }
    .rc-row.mine { align-self:flex-end; flex-direction:row-reverse; }
    .rc-row img.rc-mini { width:30px; height:30px; border-radius:50%; object-fit:cover; border:1px solid var(--color-border); flex-shrink:0; background:var(--color-surface); }
    .rc-bubble { padding:.8rem 1.05rem; border-radius:18px; font-size:.9rem; line-height:1.55; word-break:break-word; box-shadow:0 1px 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent); max-width:100%; }
    .rc-row.theirs .rc-bubble { background:var(--color-surface); border:1px solid var(--color-border); color:var(--color-text); border-bottom-left-radius:6px; }
    .rc-row.mine .rc-bubble { background:var(--color-secondary); border:1px solid var(--color-secondary); color:var(--color-on-solid); border-bottom-right-radius:6px; box-shadow:0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent); }
    .rc-subject { font-weight:700; font-size:.78rem; margin-bottom:.25rem; opacity:.9; display:flex; align-items:center; gap:.35rem; }
    .rc-meta { display:flex; align-items:center; justify-content:flex-end; gap:.35rem; font-size:.68rem; margin-top:.3rem; opacity:.85; }
    .rc-row.mine .rc-meta { color:color-mix(in srgb, var(--color-on-solid) 92%, transparent); }
    .rc-row.theirs .rc-meta { color:var(--color-text-muted); }

    .rc-composer { padding:.85rem 1.1rem; background:var(--color-surface); border-top:1px solid var(--color-border); }
    .rc-composer-bar { display:flex; align-items:center; gap:.7rem; background:var(--color-bg); border:1px solid var(--color-border); border-radius:999px; padding:.35rem .4rem .35rem 1.2rem; transition:all .2s; }
    .rc-composer-bar:focus-within { border-color:var(--color-secondary); background:var(--color-surface); box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 16%, transparent); }
    .rc-composer-bar input { flex:1; border:none; outline:none; background:transparent; font-size:.9rem; color:var(--color-text); padding:.4rem 0; min-width:0; }
    .rc-send { width:44px; height:44px; border-radius:50%; background:var(--color-secondary); border:1px solid var(--color-secondary); color:var(--color-on-solid); display:inline-flex; align-items:center; justify-content:center; font-size:1.05rem; cursor:pointer; transition:all .2s; box-shadow:0 3px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 35%, transparent); flex-shrink:0; }
    .rc-send:hover { background:var(--color-secondary); transform:scale(1.05); }
    .rc-send:active { transform:scale(.95); }
    @media (max-width:640px){ .rc-row{ max-width:88%; } .rc-chat-card{ height:82vh; } }
</style>
@endpush

@php
    $contentSection = request()->routeIs('midwife.messages.*') ? 'midwife-content' : (request()->routeIs('bhw.messages.*') ? 'bhw-content' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president-content' : 'user-content'));
    $messagesRouteBase = request()->routeIs('midwife.messages.*') ? 'midwife.messages' : (request()->routeIs('bhw.messages.*') ? 'bhw.messages' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president.messages' : 'user.messages'));
    $currentUser = auth()->user();
    $currentUserId = $currentUser?->id;
    $rootSentByCurrentUser = $root->sender_id === $currentUserId;
    $otherParty = $rootSentByCurrentUser ? $root->receiver : $root->sender;
    $otherPartyPhoto = $otherParty ? $otherParty->profile_image_url : '/images/avatars/avatar-female.svg';
    $otherPartyLabel = match ($otherParty?->role) { 'user' => 'Patient', 'midwife' => 'Midwife', 'bhw_president' => 'BHW President', 'bhw' => 'BHW Health Worker', default => 'Member', };
@endphp

@section($contentSection)
<div class="rc-thread">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="border-radius:14px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="rc-chat-card">
        <div class="rc-chat-topbar">
            <div class="rc-chat-peer">
                <a href="{{ route($messagesRouteBase . '.index') }}" class="rc-back" title="Back to chats"><i class="bi bi-arrow-left"></i></a>
                <div class="rc-peer-avatar">
                    <img src="{{ $otherPartyPhoto }}" alt="{{ optional($otherParty)->name ?? 'User' }}" onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                    <span></span>
                </div>
                <div style="min-width:0;">
                    <h4 class="rc-peer-name">{{ optional($otherParty)->name ?? 'Healthcare Provider' }}</h4>
                    <div class="rc-peer-sub"><span class="rc-role-pill">{{ $otherPartyLabel }}</span><span>Active care support</span></div>
                </div>
            </div>
            <div class="rc-top-actions">
                <span id="connDot" class="badge bg-success" title="Live">Live</span>
                <button type="button" class="rc-icon-btn" data-bs-toggle="modal" data-bs-target="#careEmergencyModal" title="Emergency help"><i class="bi bi-telephone-fill" style="color:var(--color-success-text);"></i></button>
                <a href="{{ route($messagesRouteBase . '.create', ['to' => $otherParty?->id, 'role' => $rootSentByCurrentUser ? $root->receiver_role : $root->sender_role]) }}" class="rc-icon-btn" title="New topic"><i class="bi bi-pencil-square" style="color:var(--color-secondary-text);"></i></a>
                <button type="button" class="rc-icon-btn" id="deleteThreadBtn" title="Move to trash"><i class="bi bi-trash" style="color:var(--color-danger-text);"></i></button>
            </div>
        </div>

        <div class="rc-stream" id="messageStream">
            <div class="rc-day">{{ $root->created_at->format('F d, Y') }}</div>
            <div class="rc-row {{ $rootSentByCurrentUser ? 'mine' : 'theirs' }}">
                @if(!$rootSentByCurrentUser)<img src="{{ $otherPartyPhoto }}" alt="" class="rc-mini" onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">@endif
                <div class="rc-bubble">
                    @if($root->subject)<div class="rc-subject"><i class="bi bi-bookmark-fill"></i> {{ $root->subject }}</div>@endif
                    <div style="white-space:pre-wrap;margin:0;">{{ $root->body }}</div>
                    <div class="rc-meta"><span>{{ $root->created_at->format('g:i A') }}</span>@if($rootSentByCurrentUser)<i class="bi bi-check2-all"></i>@endif</div>
                </div>
            </div>
            @php $lastDate = $root->created_at->format('Y-m-d'); @endphp
            @foreach($root->replies->sortBy('created_at') as $reply)
                @php $replyMine = $reply->sender_id === $currentUserId; $currentDate = $reply->created_at->format('Y-m-d'); $senderPhoto = $replyMine ? ($currentUser->profile_image_url ?? '/images/avatars/avatar-female.svg') : $otherPartyPhoto; @endphp
                @if($currentDate !== $lastDate)<div class="rc-day">{{ $reply->created_at->format('F d, Y') }}</div>@php $lastDate = $currentDate; @endphp@endif
                <div class="rc-row {{ $replyMine ? 'mine' : 'theirs' }}">
                    @if(!$replyMine)<img src="{{ $senderPhoto }}" alt="" class="rc-mini" onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">@endif
                    <div class="rc-bubble">
                        <div style="white-space:pre-wrap;margin:0;">{{ $reply->body }}</div>
                        <div class="rc-meta"><span>{{ $reply->created_at->format('g:i A') }}</span>@if($replyMine)<i class="bi bi-check2-all"></i>@endif</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rc-composer">
            <div id="sendError" class="alert alert-danger py-2 px-3 mb-2 d-none" style="border-radius:12px;font-size:.82rem;"></div>
            <div id="trashBanner" class="alert alert-warning py-2 px-3 mb-2 d-none" style="border-radius:12px;font-size:.82rem;">Moved to trash. <button type="button" id="undoDeleteBtn" class="btn btn-sm btn-success ms-2">Undo</button></div>
            <form id="chatForm" action="{{ route($messagesRouteBase . '.send') }}" method="POST">
                @csrf
                <input type="hidden" name="receiver_id" value="{{ $rootSentByCurrentUser ? $root->receiver_id : $root->sender_id }}">
                <input type="hidden" name="receiver_role" value="{{ $rootSentByCurrentUser ? $root->receiver_role : $root->sender_role }}">
                <input type="hidden" name="reply_to_id" value="{{ $root->id }}">
                <input type="hidden" name="client_uuid" id="clientUuid">
                <div class="rc-composer-bar">
                    <input type="text" name="body" id="messageInput" placeholder="Type a message to {{ optional($otherParty)->first_name ?? 'recipient' }}..." autocomplete="off" required maxlength="2000">
                    <button type="submit" class="rc-send" id="sendBtn" title="Send"><i class="bi bi-send-fill"></i></button>
                </div>
            </form>
            <div class="d-flex justify-content-between align-items-center mt-1"><small class="text-muted" id="seenLine">Messages send instantly · auto-refresh on</small><small class="text-muted">Enter to send</small></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var stream = document.getElementById('messageStream');
    var form = document.getElementById('chatForm');
    var input = document.getElementById('messageInput');
    var uuidField = document.getElementById('clientUuid');
    var sendBtn = document.getElementById('sendBtn');
    var errBox = document.getElementById('sendError');
    var trashBanner = document.getElementById('trashBanner');
    var undoBtn = document.getElementById('undoDeleteBtn');
    var delBtn = document.getElementById('deleteThreadBtn');
    var connDot = document.getElementById('connDot');
    var seenLine = document.getElementById('seenLine');
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '{{ csrf_token() }}';
    var threadId = {{ $root->id }};
    var currentUserId = {{ (int) $currentUserId }};
    var otherPhoto = @json($otherPartyPhoto);
    var updatesUrl = @json(route($messagesRouteBase.'.updates', $root->id));
    var deleteUrl = @json(route($messagesRouteBase.'.destroy', $root->id));
    var restoreUrl = @json(route($messagesRouteBase.'.restore', $root->id));
    var indexUrl = @json(route($messagesRouteBase.'.index'));
    var lastId = {{ (int) ($root->replies->max('id') ?? $root->id) }};
    function newUuid() { return (crypto.randomUUID ? crypto.randomUUID() : 'id-' + Date.now() + '-' + Math.random().toString(16).slice(2)); }
    function nearBottom() { return stream && (stream.scrollHeight - stream.scrollTop - stream.clientHeight < 140); }
    function scrollBottom(force) { if (!stream) return; if (force || nearBottom()) stream.scrollTop = stream.scrollHeight; }
    function esc(s) { return String(s ?? '').replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
    function bubble(m, mine) {
        var row = document.createElement('div');
        row.className = 'rc-row ' + (mine ? 'mine' : 'theirs');
        row.dataset.mid = m.id;
        var html = '';
        if (!mine) html += '<img src="' + otherPhoto + '" alt="" class="rc-mini" onerror="this.onerror=null;this.src=\'/images/avatars/avatar-female.svg\';">';
        html += '<div class="rc-bubble"><div style="white-space:pre-wrap;margin:0;">' + esc(m.body) + '</div><div class="rc-meta"><span>' + esc(m.time || '') + '</span>' + (mine ? '<i class="bi ' + (m.is_read ? 'bi-check2-all text-primary' : 'bi-check2-all') + '"></i>' : '') + '</div></div>';
        row.innerHTML = html;
        return row;
    }
    uuidField.value = newUuid();
    scrollBottom(true);
    if (input) input.focus();
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        errBox.classList.add('d-none');
        var body = input.value.trim();
        if (!body) return;
        sendBtn.disabled = true;
        var fd = new FormData(form);
        fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
            .then(function (res) {
                if (!res.ok || !res.j.success) throw new Error(res.j.message || 'Send failed');
                var m = res.j.message;
                stream.appendChild(bubble(m, true));
                lastId = Math.max(lastId, m.id);
                input.value = '';
                uuidField.value = newUuid();
                scrollBottom(true);
            })
            .catch(function (err) { errBox.textContent = 'Could not send. Your text is kept — check connection and retry.'; errBox.classList.remove('d-none'); })
            .finally(function () { sendBtn.disabled = false; input.focus(); });
    });
    input.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); } });
    var pollTimer = setInterval(function () {
        fetch(updatesUrl + '?after_id=' + lastId, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j.success) return;
                connDot.className = 'badge bg-success'; connDot.textContent = 'Live';
                (j.messages || []).forEach(function (m) {
                    if (stream.querySelector('[data-mid="' + m.id + '"]')) return;
                    stream.appendChild(bubble(m, m.sender_id === currentUserId));
                    lastId = Math.max(lastId, m.id);
                });
                if ((j.messages || []).length) scrollBottom(false);
                if (j.root_read) { seenLine.textContent = 'Seen by recipient'; }
            })
            .catch(function () { connDot.className = 'badge bg-warning text-dark'; connDot.textContent = 'Reconnecting…'; });
    }, 3000);
    delBtn.addEventListener('click', function () {
        if (!confirm('Move this conversation to Trash? You can restore it.')) return;
        fetch(deleteUrl, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j.success) throw new Error('delete');
                clearInterval(pollTimer);
                trashBanner.classList.remove('d-none');
            })
            .catch(function () { alert('Delete failed. Please retry.'); });
    });
    undoBtn.addEventListener('click', function () {
        fetch(restoreUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (j) { if (j.success) trashBanner.classList.add('d-none'); else window.location.href = indexUrl; });
    });
})();
</script>
@endpush
@endsection
