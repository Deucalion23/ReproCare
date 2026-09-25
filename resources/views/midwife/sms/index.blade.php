@extends('midwife.layout')

@section('title', 'SMS Alerts | ReproCare')

@push('styles')
<style>
    .rc-sms { max-width:1200px; margin:0 auto; padding-bottom:2rem; }
    .rc-sms-hero { background:var(--color-surface); background-color:var(--color-surface); border:none; border-radius:20px; padding:1.35rem 1.6rem; margin-bottom:1.25rem; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent); display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; }
    .rc-sms-hero h1 { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.3rem; font-weight:800; color:var(--color-text); margin:0; letter-spacing:-0.01em; }
    .rc-sms-hero p { margin:.25rem 0 0; font-size:.85rem; color:var(--color-text-muted); }
    .rc-stat-pills { display:flex; gap:.5rem; flex-wrap:wrap; }
    .rc-stat-pill { border:none; border-radius:999px; padding:.42rem .95rem; font-size:.76rem; font-weight:800; color:var(--color-text); background:var(--color-surface-soft); background-color:var(--color-surface-soft); white-space:nowrap; }
    .rc-stat-pill b { color:var(--color-text); }
    .rc-stat-pill.ok { background:var(--color-success-soft); background-color:var(--color-success-soft); color:var(--color-success-text); } .rc-stat-pill.ok b { color:var(--color-success-text); }
    .rc-stat-pill.bad { background:var(--color-danger-soft); background-color:var(--color-danger-soft); color:var(--color-danger-text); } .rc-stat-pill.bad b { color:var(--color-danger-text); }
    .rc-btn-pink { display:inline-flex; align-items:center; justify-content:center; gap:.45rem; background:var(--color-surface-strong); background-color:var(--color-surface-strong); border:none; color:var(--color-on-solid); font-weight:800; font-size:.85rem; padding:.62rem 1.3rem; border-radius:999px; text-decoration:none; box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent); transition:all .2s; white-space:nowrap; cursor:pointer; }
    .rc-btn-pink:hover { background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid); transform:translateY(-1px); }
    .rc-btn-ghost { display:inline-flex; align-items:center; gap:.4rem; background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:none; color:var(--color-text); font-weight:800; font-size:.85rem; padding:.6rem 1.1rem; border-radius:999px; text-decoration:none; cursor:pointer; }
    .rc-btn-ghost:hover { border:none; color:var(--color-text); background:var(--color-border); background-color:var(--color-border); }

    .rc-sms-shell { display:grid; grid-template-columns:360px 1fr; gap:1.25rem; align-items:start; }
    @media (max-width:991px){ .rc-sms-shell{ grid-template-columns:1fr; } }
    .rc-panel { background:var(--color-surface); background-color:var(--color-surface); border:none; border-radius:20px; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent); display:flex; flex-direction:column; overflow:hidden; min-height:620px; }
    .rc-panel-head { padding:1.1rem 1.25rem; border:none; }
    .rc-panel-head h3 { font-size:1rem; font-weight:800; color:var(--color-text); margin:0; font-family:'Plus Jakarta Sans',sans-serif; display:flex; align-items:center; gap:.5rem; }
    .rc-panel-head h3 i { color:var(--color-secondary-text); }
    .rc-search { position:relative; margin-top:.8rem; }
    .rc-search i { position:absolute; left:.9rem; top:50%; transform:translateY(-50%); color:var(--color-text-muted); }
    .rc-search input, .rc-search select { width:100%; padding:.62rem 1rem .62rem 2.5rem; border-radius:999px; border:none; background:var(--color-surface-soft); background-color:var(--color-surface-soft); font-size:.84rem; color:var(--color-text); outline:none; }
    .rc-search input:focus { border:none; background:var(--color-surface); background-color:var(--color-surface); box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent); }
    .rc-filter-row { display:flex; gap:.5rem; margin-top:.6rem; }
    .rc-filter-row select { border:none; border-radius:12px; font-size:.78rem; font-weight:600; padding:.5rem .6rem; background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text); flex:1; }
    .rc-conv-list { padding:.7rem; overflow-y:auto; flex:1; max-height:600px; display:flex; flex-direction:column; gap:.3rem; }
    .rc-conv { display:flex; align-items:center; gap:.8rem; padding:.8rem; border-radius:14px; text-decoration:none; color:inherit; border:none; transition:all .18s; }
    .rc-conv:hover { background:var(--color-bg); background-color:var(--color-bg); border:none; color:inherit; }
    .rc-conv.active { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); border:none; }
    .rc-av { width:46px; height:46px; border-radius:50%; background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); border:none; color:var(--color-secondary-text); display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:.95rem; flex-shrink:0; overflow:hidden; }
    .rc-av img { width:100%; height:100%; object-fit:cover; }
    .rc-conv-meta { flex:1; min-width:0; }
    .rc-conv-name { font-weight:800; font-size:.88rem; color:var(--color-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin:0; }
    .rc-conv-phone { font-size:.72rem; color:var(--color-text-muted); }
    .rc-conv-preview { font-size:.8rem; color:var(--color-text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin:.15rem 0 0; }
    .rc-conv-time { font-size:.7rem; color:var(--color-text-muted); white-space:nowrap; flex-shrink:0; }
    .rc-dot { width:9px; height:9px; border-radius:50%; display:inline-block; flex-shrink:0; }
    .rc-dot.sent { background:var(--color-success); } .rc-dot.failed { background:var(--color-danger); } .rc-dot.pending { background:var(--color-warning); }

    .rc-thread-head { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1rem 1.25rem; border:none; flex-wrap:wrap; }
    .rc-thread-peer { display:flex; align-items:center; gap:.85rem; min-width:0; }
    .rc-thread-peer h4 { font-size:1rem; font-weight:800; color:var(--color-text); margin:0; }
    .rc-thread-peer small { color:var(--color-text-muted); font-size:.78rem; }
    .rc-thread-body { flex:1; overflow-y:auto; padding:1.4rem; background:var(--color-bg); background-color:var(--color-bg); display:flex; flex-direction:column; gap:.75rem; min-height:380px; max-height:520px; }
    .rc-day { align-self:center; background:var(--color-surface); background-color:var(--color-surface); border:none; color:var(--color-text-muted); font-size:.66rem; font-weight:800; padding:.3rem .9rem; border-radius:999px; text-transform:uppercase; letter-spacing:.5px; }
    .rc-bubble-row { display:flex; max-width:80%; }
    .rc-bubble-row.out { align-self:flex-end; }
    .rc-bubble-row.sys { align-self:flex-start; }
    .rc-bubble { padding:.75rem 1rem; border-radius:16px; font-size:.87rem; line-height:1.55; word-break:break-word; }
    .rc-bubble-row.out .rc-bubble { background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid); border-bottom-right-radius:6px; box-shadow:0 6px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent); }
    .rc-bubble-row.sys .rc-bubble { background:var(--color-surface); background-color:var(--color-surface); border:none; color:var(--color-text); border-bottom-left-radius:6px; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent); }
    .rc-bubble-meta { display:flex; align-items:center; gap:.35rem; font-size:.68rem; margin-top:.3rem; opacity:.75; justify-content:flex-end; }
    .rc-type-tag { display:inline-block; font-size:.64rem; font-weight:800; padding:.15rem .6rem; border-radius:999px; background:color-mix(in srgb, var(--color-surface) 16%, transparent); background-color:color-mix(in srgb, var(--color-surface) 16%, transparent); border:none; margin-bottom:.35rem; text-transform:uppercase; letter-spacing:.4px; }
    .rc-bubble-row.sys .rc-type-tag { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:var(--color-secondary-text); border:none; }
    .rc-composer { padding:.9rem 1.1rem; background:var(--color-surface); background-color:var(--color-surface); border:none; }
    .rc-lang-tabs { display:flex; gap:.5rem; margin-bottom:.6rem; }
    .rc-lang-tabs button { flex:1; border:none; background:var(--color-surface-soft); background-color:var(--color-surface-soft); border-radius:999px; font-size:.76rem; font-weight:800; color:var(--color-text); padding:.5rem; cursor:pointer; }
    .rc-lang-tabs button.active { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); border:none; color:var(--color-secondary-text); }
    .rc-composer-bar { display:flex; align-items:flex-end; gap:.6rem; background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:none; border-radius:16px; padding:.5rem .5rem .5rem 1rem; transition:all .2s; }
    .rc-composer-bar:focus-within { border:none; background:var(--color-surface); background-color:var(--color-surface); box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent); }
    .rc-composer-bar textarea { flex:1; border:none; outline:none; background:transparent; font-size:.87rem; color:var(--color-text); resize:none; min-height:44px; max-height:120px; padding:.4rem 0; font-family:inherit; }
    .rc-send { width:42px; height:42px; border-radius:50%; background:var(--color-surface-strong); background-color:var(--color-surface-strong); border:none; color:var(--color-on-solid); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; box-shadow:0 3px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent); }
    .rc-send:hover { background:var(--color-surface-strong); background-color:var(--color-surface-strong); transform:scale(1.05); }
    .rc-char { font-size:.7rem; color:var(--color-text-muted); text-align:right; margin-top:.25rem; }
    .rc-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:3rem 1.5rem; color:var(--color-text-muted); }
    .rc-empty i { font-size:2.6rem; color:var(--color-border); margin-bottom:.7rem; }

    /* ── Pager: borderless soft pills ── */
    .rc-pager { padding:.8rem 1rem; border:none; background:var(--color-surface); background-color:var(--color-surface); }
    .rc-pager nav { display:flex; justify-content:center; }
    .rc-pager .pagination { margin:0; display:flex; justify-content:center; align-items:center; gap:.3rem; flex-wrap:nowrap; }
    .rc-pager .page-item .page-link { border:none; background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text); font-size:.78rem; font-weight:800; border-radius:10px; padding:.35rem .7rem; min-width:34px; text-align:center; box-shadow:none; }
    .rc-pager .page-item .page-link:hover { border:none; color:var(--color-on-solid); background:var(--color-surface-strong); background-color:var(--color-surface-strong); }
    .rc-pager .page-item.active .page-link { background:var(--color-surface-strong); background-color:var(--color-surface-strong); border:none; color:var(--color-on-solid); box-shadow:none; }
    .rc-pager .page-item.disabled .page-link { color:var(--color-text); background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:none; }
    .rc-pager .page-link:focus { box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent); }
    .rc-pager p.small, .rc-pager .small { font-size:.72rem; color:var(--color-text-muted); text-align:center; margin:.4rem 0 0; }
</style>
@endpush

@section('midwife-content')
@php
    $grouped = $logs->getCollection()->groupBy(fn($l) => $l->user_id ?? ('phone:'.$l->phone_number));
    $selectedId = request('to') ? (int) request('to') : null;
    $selectedPhone = request('phone');
    $activeKey = null; $activePatient = null; $activeLogs = collect();
    if ($selectedId) {
        $activePatient = $patients->firstWhere('id', $selectedId);
        $activeKey = $selectedId;
        // Full per-patient history from the controller (not limited to the paginated page).
        $activeLogs = isset($threadLogs) ? $threadLogs : $logs->getCollection()->where('user_id', $selectedId)->sortBy('created_at');
    } elseif ($selectedPhone) {
        $activeKey = 'phone:'.$selectedPhone;
        $activeLogs = $logs->getCollection()->where('phone_number', $selectedPhone)->sortBy('created_at');
    } else {
        $firstThread = $grouped->keys()->first();
        if ($firstThread !== null) {
            $activeKey = $firstThread;
            if (is_numeric($activeKey)) { $activePatient = $patients->firstWhere('id', (int) $activeKey); $activeLogs = $logs->getCollection()->where('user_id', (int) $activeKey)->sortBy('created_at'); }
            else { $activeLogs = $logs->getCollection()->filter(fn($l) => ('phone:'.$l->phone_number) === $activeKey)->sortBy('created_at'); }
        } elseif ($patients->isNotEmpty()) {
            $activePatient = $patients->first(); $activeKey = $activePatient->id; $activeLogs = collect();
        }
    }
    if (!$activePatient && is_numeric($activeKey)) $activePatient = $patients->firstWhere('id', (int) $activeKey);
    $activeName = $activePatient ? trim($activePatient->first_name.' '.($activePatient->middle_initial ? $activePatient->middle_initial.'. ' : '').$activePatient->last_name) : ($activeLogs->first()?->phone_number ?? 'Select a conversation');
    $activePhone = $activePatient?->contact_number ?? ($activeLogs->first()?->phone_number ?? '');
    $lastDate = null;
@endphp

<div class="rc-sms">
    <div class="rc-sms-hero">
        <div>
            <h1>SMS Alerts</h1>
            <p>Chat-style health alerts, reminders, and broadcasts to patients.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="rc-stat-pills">
                <span class="rc-stat-pill">Total <b>{{ $stats['total'] }}</b></span>
                <span class="rc-stat-pill ok">Sent <b>{{ $stats['sent'] }}</b></span>
                <span class="rc-stat-pill bad">Failed <b>{{ $stats['failed'] }}</b></span>
                <span class="rc-stat-pill">Opted-in <b>{{ $stats['enabled'] }}</b></span>
            </div>
            <button class="rc-btn-pink" data-bs-toggle="modal" data-bs-target="#broadcastModal"><i class="bi bi-megaphone-fill"></i> Broadcast</button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" style="border-radius:14px;"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" style="border-radius:14px;"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="rc-sms-shell">
        <section class="rc-panel">
            <div class="rc-panel-head">
                <h3>Conversations <span class="rc-stat-pill ms-1"><b>{{ $patients->count() }}</b></span></h3>
                <form method="GET" class="rc-search">
                    <i class="bi bi-search"></i>
                    <input type="search" name="search" placeholder="Search name or number..." value="{{ request('search') }}">
                    @if(request('to'))<input type="hidden" name="to" value="{{ request('to') }}">@endif
                </form>
                <form method="GET" class="rc-filter-row">
                    @if(request('to'))<input type="hidden" name="to" value="{{ request('to') }}">@endif
                    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All status</option>
                        <option value="sent" {{ request('status')==='sent'?'selected':'' }}>Sent</option>
                        <option value="failed" {{ request('status')==='failed'?'selected':'' }}>Failed</option>
                        <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Pending</option>
                    </select>
                    <select name="type" onchange="this.form.submit()">
                        <option value="">All types</option>
                        <option value="appointment_reminder" {{ request('type')==='appointment_reminder'?'selected':'' }}>Appointment</option>
                        <option value="high_risk_alert" {{ request('type')==='high_risk_alert'?'selected':'' }}>High risk</option>
                        <option value="missed_checkup" {{ request('type')==='missed_checkup'?'selected':'' }}>Missed</option>
                        <option value="custom" {{ request('type')==='custom'?'selected':'' }}>Custom</option>
                        <option value="broadcast" {{ request('type')==='broadcast'?'selected':'' }}>Broadcast</option>
                    </select>
                </form>
            </div>
            <div class="rc-conv-list">
                @forelse($patients as $p)
                    @php
                        // Prefer the controller's latest-per-patient map so previews stay
                        // accurate even when the log paginator is on another page.
                        $last = (isset($recentByUser) && $recentByUser->has($p->id))
                            ? $recentByUser->get($p->id)
                            : $logs->getCollection()->where('user_id', $p->id)->sortByDesc('created_at')->first();
                        $pName = trim($p->first_name.' '.($p->middle_initial ? $p->middle_initial.'. ' : '').$p->last_name);
                        $isActive = (string) $activeKey === (string) $p->id;
                        $link = route('midwife.sms.index', array_merge(request()->except('page'), ['to' => $p->id]));
                    @endphp
                    <a href="{{ $link }}" class="rc-conv {{ $isActive ? 'active' : '' }}">
                        <span class="rc-av">{{ strtoupper(substr($pName, 0, 1)) }}</span>
                        <span class="rc-conv-meta">
                            <span class="d-flex justify-content-between align-items-baseline gap-2">
                                <span class="rc-conv-name">{{ $pName }}</span>
                                <span class="rc-conv-time">{{ $last ? $last->created_at->diffForHumans(null, true, true) : 'new' }}</span>
                            </span>
                            <span class="rc-conv-phone">{{ $p->contact_number }}</span>
                            <span class="rc-conv-preview d-block">{{ $last ? \Illuminate\Support\Str::limit($last->message, 48) : 'No SMS yet — say hello.' }}</span>
                        </span>
                        <span class="rc-dot {{ $last?->status ?? 'pending' }}"></span>
                    </a>
                @empty
                    <div class="rc-empty"><i class="bi bi-chat-dots"></i><h6 class="fw-bold text-dark">No patients</h6><p style="font-size:.82rem;">No SMS-enabled patients found.</p></div>
                @endforelse
            </div>
            @if($logs->hasPages())
                <div class="rc-pager">{{ $logs->onEachSide(1)->withQueryString()->links('pagination::bootstrap-5') }}</div>
            @endif
        </section>

        <section class="rc-panel">
            <div class="rc-thread-head">
                <div class="rc-thread-peer">
                    <span class="rc-av">{{ strtoupper(substr($activeName, 0, 1)) }}</span>
                    <div style="min-width:0;">
                        <h4>{{ $activeName }}</h4>
                        <small><i class="bi bi-telephone me-1"></i>{{ $activePhone }} · <span style="color:var(--color-success-text);font-weight:700;">SMS enabled</span></small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @if($activePatient)
                    <form method="GET" class="d-flex gap-2">
                        <input type="hidden" name="to" value="{{ $activePatient->id }}">
                        <select name="type" class="form-select form-select-sm" style="border:none; border-radius:999px; background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text); font-weight:700;" onchange="this.form.submit()">
                            <option value="">All types</option>
                            <option value="custom" {{ request('type')==='custom'?'selected':'' }}>Custom</option>
                            <option value="appointment_reminder" {{ request('type')==='appointment_reminder'?'selected':'' }}>Appointment</option>
                            <option value="broadcast" {{ request('type')==='broadcast'?'selected':'' }}>Broadcast</option>
                        </select>
                    </form>
                    @endif
                </div>
            </div>

            <div class="rc-thread-body" id="smsThread">
                @forelse($activeLogs as $log)
                    @php
                        $d = $log->created_at->format('Y-m-d');
                        $showDay = $d !== $lastDate; $lastDate = $d;
                    @endphp
                    @if($showDay)<div class="rc-day">{{ $log->created_at->format('F d, Y') }}</div>@endif
                    <div class="rc-bubble-row out">
                        <div class="rc-bubble">
                            <span class="rc-type-tag">{{ $log->type_icon }} {{ str_replace('_',' ', ucfirst($log->type)) }}</span>
                            <div style="white-space:pre-wrap;">{{ $log->message }}</div>
                            <div class="rc-bubble-meta"><span>{{ $log->sent_at ? $log->sent_at->format('g:i A') : $log->created_at->format('g:i A') }}</span><i class="bi {{ $log->status==='sent' ? 'bi-check2-all' : ($log->status==='failed' ? 'bi-exclamation-circle' : 'bi-clock') }}"></i><span>{{ ucfirst($log->status) }}</span></div>
                        </div>
                    </div>
                @empty
                    <div class="rc-empty"><i class="bi bi-chat-heart"></i><h6 class="fw-bold text-dark">No messages yet</h6><p style="font-size:.83rem;">Start the conversation below — it will appear here like a chat.</p></div>
                @endforelse
            </div>

            @if($activePatient)
            <div class="rc-composer">
                <form action="{{ route('midwife.sms.send') }}" method="POST" id="smsForm">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $activePatient->id }}">
                    <div class="rc-lang-tabs">
                        <button type="button" class="active" data-lang="en">English</button>
                        <button type="button" data-lang="tl">Tagalog (optional)</button>
                    </div>
                    <div class="rc-composer-bar">
                        <textarea name="message_en" id="smsEn" rows="2" maxlength="320" required placeholder="Type an SMS to {{ $activeName }}..."></textarea>
                        <button type="submit" class="rc-send" title="Send SMS"><i class="bi bi-send-fill"></i></button>
                    </div>
                    <textarea name="message_tl" id="smsTl" class="d-none mt-2" rows="2" maxlength="320" placeholder="Isulat ang mensahe sa Tagalog..." style="width:100%;border:none;border-radius:12px;background:var(--color-surface-soft);background-color:var(--color-surface-soft);padding:.7rem 1rem;font-size:.85rem;outline:none;"></textarea>
                    <div class="rc-char"><span id="smsCount">0</span>/320</div>
                </form>
            </div>
            @endif
        </section>
    </div>
</div>

<div class="modal fade" id="broadcastModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:none; border-radius:20px; overflow:hidden; box-shadow:0 20px 50px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent);">
            <div class="modal-header" style="border:none;">
                <h5 class="modal-title fw-800" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--color-text);">Broadcast SMS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('midwife.sms.broadcast') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Filter by Purok (optional)</label>
                        <select name="purok_id" class="form-select" style="border-radius:12px;">
                            <option value="">— All Puroks —</option>
                            @foreach($puroks as $purok)<option value="{{ $purok->id }}">{{ $purok->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Message (English)</label>
                        <textarea name="message_en" class="form-control" rows="3" required maxlength="320" placeholder="Broadcast message in English..." style="border-radius:12px;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Message (Tagalog) (optional)</label>
                        <textarea name="message_tl" class="form-control" rows="3" maxlength="320" placeholder="Mensahe sa Tagalog..." style="border-radius:12px;"></textarea>
                    </div>
                    <div class="alert alert-warning py-2 mb-0" style="font-size:.8rem;border-radius:12px;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Sends to up to <strong>{{ $stats['enabled'] }}</strong> opted-in patients. Cannot be undone.
                    </div>
                </div>
                <div class="modal-footer" style="border:none;">
                    <button type="button" class="rc-btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="rc-btn-pink" onclick="return confirm('Send broadcast SMS to {{ $stats['enabled'] }} patient(s)?')"><i class="bi bi-megaphone-fill"></i> Send Broadcast</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var thread = document.getElementById('smsThread');
    if (thread) thread.scrollTop = thread.scrollHeight;
    var en = document.getElementById('smsEn'), count = document.getElementById('smsCount');
    if (en && count) en.addEventListener('input', function(){ count.textContent = en.value.length; });
    document.querySelectorAll('.rc-lang-tabs button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.rc-lang-tabs button').forEach(function (b){ b.classList.remove('active'); });
            btn.classList.add('active');
            var tl = document.getElementById('smsTl');
            if (tl) tl.classList.toggle('d-none', btn.dataset.lang !== 'tl');
            if (btn.dataset.lang === 'tl' && tl) tl.focus();
        });
    });
});
</script>
@endpush
@endsection
