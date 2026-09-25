@extends(request()->routeIs('midwife.messages.*') ? 'midwife.layout' : (request()->routeIs('bhw.messages.*') ? 'bhw.layout' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president.layout' : 'user.layout')))

@section('title', 'New Chat - ReproCare')

@push('styles')
<style>
    .rc-compose { max-width:1100px; margin:0 auto; padding-bottom:2rem; }
    .rc-compose-hero { background:var(--color-surface); border:1px solid var(--color-border); border-radius:18px; padding:1.25rem 1.5rem; margin-bottom:1.25rem; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent); display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; }
    .rc-compose-hero h2 { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.25rem; font-weight:800; color:var(--color-text); margin:0; display:flex; align-items:center; gap:.6rem; }
    .rc-compose-hero h2 .rc-ico { width:40px; height:40px; border-radius:12px; background:var(--color-secondary-soft); border:1px solid var(--color-secondary-soft); color:var(--color-secondary-text); display:inline-flex; align-items:center; justify-content:center; }
    .rc-compose-hero p { margin:.2rem 0 0; font-size:.85rem; color:var(--color-text-muted); }
    .rc-compose-grid { display:grid; grid-template-columns:minmax(300px,360px) minmax(0,1fr); gap:1.25rem; align-items:start; }
    @media (max-width:991px){ .rc-compose-grid{ grid-template-columns:1fr; } }
    .rc-card { background:var(--color-surface); border:1px solid var(--color-border); border-radius:18px; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent); overflow:hidden; }
    .rc-card-head { padding:1.1rem 1.25rem; border-bottom:1px solid var(--color-border); }
    .rc-card-head h3 { font-size:1rem; font-weight:800; color:var(--color-text); margin:0; font-family:'Plus Jakarta Sans',sans-serif; }
    .rc-card-head p { font-size:.8rem; color:var(--color-text-muted); margin:.25rem 0 0; }
    .rc-contact-search { width:100%; margin-top:.85rem; border:1px solid var(--color-border); background:var(--color-bg); border-radius:12px; padding:.7rem 1rem; font-size:.86rem; color:var(--color-text); outline:none; }
    .rc-contact-search:focus { border-color:var(--color-secondary); background:var(--color-surface); box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 16%, transparent); }
    .rc-contact-list { padding:.8rem; max-height:62vh; overflow-y:auto; display:flex; flex-direction:column; gap:.45rem; }
    .rc-contact-opt { display:flex; align-items:center; gap:.8rem; width:100%; border:1px solid transparent; background:transparent; border-radius:14px; text-align:left; padding:.8rem; transition:.18s; cursor:pointer; }
    .rc-contact-opt:hover, .rc-contact-opt.active { background:var(--color-secondary-soft); border-color:var(--color-secondary-soft); }
    .rc-contact-opt .rc-av { width:42px; height:42px; border-radius:50%; background:var(--color-secondary-soft); border:1px solid var(--color-secondary-soft); color:var(--color-secondary-text); display:inline-flex; align-items:center; justify-content:center; font-weight:800; flex-shrink:0; overflow:hidden; }
    .rc-contact-opt .rc-av img { width:100%; height:100%; object-fit:cover; }
    .rc-contact-opt .rc-nm { font-weight:700; font-size:.88rem; color:var(--color-text); }
    .rc-contact-opt .rc-rl { display:inline-block; margin-top:.15rem; font-size:.7rem; font-weight:700; padding:.12rem .55rem; border-radius:999px; background:var(--color-surface-soft); color:var(--color-text-muted); }
    .rc-contact-opt.active .rc-rl { background:var(--color-secondary-soft); color:var(--color-secondary-text); }
    .rc-form { padding:1.25rem; }
    .rc-selected { display:flex; align-items:center; gap:.85rem; padding:.9rem 1rem; border-radius:14px; border:1px solid var(--color-secondary-soft); background:var(--color-secondary-soft); margin-bottom:1rem; }
    .rc-selected .rc-av { width:44px; height:44px; border-radius:50%; background:var(--color-secondary); color:var(--color-on-solid); display:inline-flex; align-items:center; justify-content:center; font-weight:800; flex-shrink:0; overflow:hidden; }
    .rc-selected .rc-av img { width:100%; height:100%; object-fit:cover; }
    .rc-label { font-weight:700; font-size:.76rem; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted); margin-bottom:.4rem; display:block; }
    .rc-input, .rc-textarea { width:100%; background:var(--color-surface); border:1px solid var(--color-border); border-radius:12px; color:var(--color-text); padding:.8rem 1rem; font-size:.88rem; outline:none; transition:.18s; }
    .rc-input:focus, .rc-textarea:focus { border-color:var(--color-secondary); box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 16%, transparent); }
    .rc-textarea { min-height:220px; resize:vertical; line-height:1.6; }
    .rc-actions { display:flex; justify-content:flex-end; gap:.6rem; margin-top:1rem; }
    .rc-btn-pink { display:inline-flex; align-items:center; gap:.45rem; background:var(--color-secondary); border:1px solid var(--color-secondary); color:var(--color-on-solid); font-weight:700; font-size:.86rem; padding:.65rem 1.35rem; border-radius:12px; box-shadow:0 6px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent); transition:all .2s; }
    .rc-btn-pink:hover { background:var(--color-secondary); color:var(--color-on-solid); transform:translateY(-1px); }
    .rc-btn-ghost { display:inline-flex; align-items:center; gap:.45rem; background:var(--color-surface); border:1px solid var(--color-border); color:var(--color-text); font-weight:700; font-size:.86rem; padding:.65rem 1.2rem; border-radius:12px; text-decoration:none; }
    .rc-btn-ghost:hover { border-color:var(--color-secondary-soft); color:var(--color-secondary-text); background:var(--color-secondary-soft); }
    .hidden { display:none !important; }
</style>
@endpush

@php
    $contentSection = request()->routeIs('midwife.messages.*') ? 'midwife-content' : (request()->routeIs('bhw.messages.*') ? 'bhw-content' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president-content' : 'user-content'));
    $messagesRouteBase = request()->routeIs('midwife.messages.*') ? 'midwife.messages' : (request()->routeIs('bhw.messages.*') ? 'bhw.messages' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president.messages' : 'user.messages'));
    $selectedReceiverId = old('receiver_id', $receiver?->id);
    $selectedReceiverRole = old('receiver_role', $receiver?->role);
@endphp

@section($contentSection)
<div class="rc-compose">
    <div class="rc-compose-hero">
        <div class="d-flex align-items-center gap-3">
            <div class="rc-ico" style="display:inline-flex;"><i class="bi bi-pencil-square"></i></div>
            <div>
                <h2>New Chat</h2>
                <p>Choose a contact, then send a message like a modern chat app.</p>
            </div>
        </div>
        <a href="{{ route($messagesRouteBase . '.index') }}" class="rc-btn-ghost"><i class="bi bi-arrow-left"></i> Back to Messages</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-4" style="border-radius:14px;">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="rc-compose-grid">
        <section class="rc-card">
            <div class="rc-card-head">
                <h3>Contacts</h3>
                <p>@if(auth()->user()?->role === 'user') Message midwives, BHWs, and BHW presidents only. @else Staff can message anyone in the system. @endif</p>
                <input type="search" id="contactSearch" class="rc-contact-search" placeholder="Search contacts..." value="{{ $search ?? '' }}">
            </div>
            <div class="rc-contact-list" id="contactList">
                @forelse($contacts as $contact)
                    @php $isActive = (string) $selectedReceiverId === (string) $contact->id && $selectedReceiverRole === $contact->role; $cUser = \App\Models\User::find($contact->id); $cPhoto = $cUser?->profile_image_url; @endphp
                    <button type="button" class="rc-contact-opt {{ $isActive ? 'active' : '' }}" data-id="{{ $contact->id }}" data-role="{{ $contact->role }}" data-name="{{ $contact->name }}" data-label="{{ $contact->label }}" data-search="{{ strtolower($contact->name . ' ' . $contact->label . ' ' . $contact->role) }}">
                        <span class="rc-av">@if($cPhoto)<img src="{{ $cPhoto }}" alt="" onerror="this.remove();">@else{{ strtoupper(substr($contact->name, 0, 1)) }}@endif</span>
                        <span style="min-width:0;">
                            <span class="rc-nm d-block text-truncate">{{ $contact->name }}</span>
                            <span class="rc-rl">{{ $contact->label }}</span>
                        </span>
                        <i class="bi bi-chevron-right ms-auto" style="color:var(--color-secondary-text);"></i>
                    </button>
                @empty
                    <div class="p-4 text-center text-muted">No contacts available.</div>
                @endforelse
            </div>
        </section>

        <section class="rc-card">
            <div class="rc-card-head">
                <h3>Message Composer</h3>
                <p>Start a direct conversation.</p>
            </div>
            <form action="{{ route($messagesRouteBase . '.send') }}" method="POST" class="rc-form">
                @csrf
                <input type="hidden" name="receiver_role" id="receiver_role" value="{{ $selectedReceiverRole }}">
                <input type="hidden" name="receiver_id" id="receiver_id" value="{{ $selectedReceiverId }}">
                <div class="rc-selected" id="selectedContactCard">
                    <span class="rc-av" id="selectedContactAvatar">{{ $receiver ? strtoupper(substr($receiver->name, 0, 1)) : '?' }}</span>
                    <div style="min-width:0;">
                        <div class="fw-bold" id="selectedContactName" style="color:var(--color-text);font-size:.92rem;">{{ $receiver?->name ?? 'No contact selected' }}</div>
                        <small id="selectedContactLabel" style="color:var(--color-secondary-text);font-weight:600;">{{ $receiver ? ucfirst(str_replace('_', ' ', $receiver->role)) : 'Choose a contact from the left' }}</small>
                    </div>
                    <i class="bi bi-chat-heart-fill ms-auto" style="color:var(--color-secondary-text);font-size:1.2rem;"></i>
                </div>
                <div class="mb-3">
                    <label class="rc-label">Subject (optional)</label>
                    <input type="text" name="subject" class="rc-input" placeholder="Optional subject..." value="{{ old('subject') }}">
                </div>
                <div class="mb-3">
                    <label class="rc-label">Message</label>
                    <textarea name="body" class="rc-textarea" placeholder="Type your message..." required>{{ old('body') }}</textarea>
                </div>
                <div class="rc-actions">
                    <a href="{{ route($messagesRouteBase . '.index') }}" class="rc-btn-ghost">Cancel</a>
                    <button type="submit" class="rc-btn-pink"><i class="bi bi-send-fill"></i> Send Message</button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const contactButtons = Array.from(document.querySelectorAll('.rc-contact-opt'));
    const contactSearch = document.getElementById('contactSearch');
    const receiverIdInput = document.getElementById('receiver_id');
    const receiverRoleInput = document.getElementById('receiver_role');
    const avatar = document.getElementById('selectedContactAvatar');
    const nameEl = document.getElementById('selectedContactName');
    const labelEl = document.getElementById('selectedContactLabel');
    function setActive(button) {
        contactButtons.forEach(function (i) { i.classList.remove('active'); });
        if (!button) {
            receiverIdInput.value = ''; receiverRoleInput.value = '';
            avatar.textContent = '?'; nameEl.textContent = 'No contact selected'; labelEl.textContent = 'Choose a contact from the left';
            return;
        }
        button.classList.add('active');
        receiverIdInput.value = button.dataset.id; receiverRoleInput.value = button.dataset.role;
        avatar.textContent = (button.dataset.name || '?').charAt(0).toUpperCase();
        nameEl.textContent = button.dataset.name || 'Unknown User';
        labelEl.textContent = button.dataset.label || button.dataset.role || '';
    }
    contactButtons.forEach(function (b) { b.addEventListener('click', function () { setActive(b); }); });
    if (contactSearch) {
        contactSearch.addEventListener('input', function () {
            const needle = this.value.toLowerCase().trim();
            contactButtons.forEach(function (b) {
                const hay = b.dataset.search || '';
                b.classList.toggle('hidden', needle !== '' && !hay.includes(needle));
            });
        });
    }
    setActive(contactButtons.find(function (b) { return b.classList.contains('active'); }) || null);
});
</script>
@endsection
