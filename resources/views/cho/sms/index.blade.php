@extends('cho.layout')

@section('title', 'SMS Logs | ReproCare')

@push('styles')
<style>
    .rc-sms { max-width:1200px; margin:0 auto; padding-bottom:2rem; }
    .rc-sms-hero { background:var(--color-surface); border:1px solid var(--color-border); border-radius:18px; padding:1.25rem 1.5rem; margin-bottom:1.25rem; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent); display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; }
    .rc-sms-hero h1 { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.3rem; font-weight:800; color:var(--color-text); margin:0; display:flex; align-items:center; gap:.6rem; }
    .rc-sms-hero h1 .rc-ico { width:42px; height:42px; border-radius:12px; background:var(--color-secondary-soft); border:1px solid var(--color-secondary-soft); color:var(--color-secondary-text); display:inline-flex; align-items:center; justify-content:center; font-size:1.2rem; }
    .rc-sms-hero p { margin:.25rem 0 0; font-size:.85rem; color:var(--color-text-muted); }
    .rc-stat-pills { display:flex; gap:.5rem; flex-wrap:wrap; }
    .rc-stat-pill { background:var(--color-bg); border:1px solid var(--color-border); border-radius:999px; padding:.4rem .9rem; font-size:.78rem; font-weight:700; color:var(--color-text); white-space:nowrap; }
    .rc-stat-pill b { color:var(--color-secondary-text); } .rc-stat-pill.ok b { color:var(--color-success-text); } .rc-stat-pill.bad b { color:var(--color-danger-text); }
    .rc-shell { display:grid; grid-template-columns:360px 1fr; gap:1.25rem; align-items:start; }
    @media (max-width:991px){ .rc-shell{ grid-template-columns:1fr; } }
    .rc-panel { background:var(--color-surface); border:1px solid var(--color-border); border-radius:18px; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent); display:flex; flex-direction:column; overflow:hidden; min-height:600px; }
    .rc-panel-head { padding:1.05rem 1.2rem; border-bottom:1px solid var(--color-border); }
    .rc-panel-head h3 { font-size:1rem; font-weight:800; color:var(--color-text); margin:0; font-family:'Plus Jakarta Sans',sans-serif; }
    .rc-search { position:relative; margin-top:.8rem; }
    .rc-search i { position:absolute; left:.9rem; top:50%; transform:translateY(-50%); color:var(--color-text-muted); }
    .rc-search input { width:100%; padding:.62rem 1rem .62rem 2.5rem; border-radius:999px; border:1px solid var(--color-border); background:var(--color-bg); font-size:.84rem; outline:none; }
    .rc-search input:focus { border-color:var(--color-secondary); background:var(--color-surface); box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 16%, transparent); }
    .rc-filter-row { display:flex; gap:.5rem; margin-top:.6rem; }
    .rc-filter-row select { border:1px solid var(--color-border); border-radius:10px; font-size:.78rem; padding:.45rem .6rem; flex:1; background:var(--color-surface); }
    .rc-conv-list { padding:.7rem; overflow-y:auto; flex:1; max-height:600px; display:flex; flex-direction:column; gap:.25rem; }
    .rc-conv { display:flex; align-items:center; gap:.8rem; padding:.8rem; border-radius:14px; text-decoration:none; color:inherit; border:1px solid transparent; }
    .rc-conv:hover { background:var(--color-secondary-soft); border-color:var(--color-secondary-soft); color:inherit; }
    .rc-conv.active { background:var(--color-secondary-soft); border-color:var(--color-secondary-soft); }
    .rc-av { width:46px; height:46px; border-radius:50%; background:var(--color-secondary-soft); border:1px solid var(--color-secondary-soft); color:var(--color-secondary-text); display:inline-flex; align-items:center; justify-content:center; font-weight:800; flex-shrink:0; }
    .rc-conv-meta { flex:1; min-width:0; }
    .rc-conv-name { font-weight:700; font-size:.88rem; color:var(--color-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin:0; }
    .rc-conv-preview { font-size:.8rem; color:var(--color-text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin:.15rem 0 0; }
    .rc-conv-time { font-size:.7rem; color:var(--color-text-muted); white-space:nowrap; }
    .rc-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; } .rc-dot.sent{background:var(--color-success);} .rc-dot.failed{background:var(--color-danger);} .rc-dot.pending{background:var(--color-warning);}
    .rc-thread-head { padding:1rem 1.25rem; border-bottom:1px solid var(--color-border); display:flex; align-items:center; gap:.85rem; }
    .rc-thread-head h4 { font-size:1rem; font-weight:800; color:var(--color-text); margin:0; }
    .rc-thread-head small { color:var(--color-text-muted); font-size:.78rem; }
    .rc-thread-body { flex:1; overflow-y:auto; padding:1.4rem; background:var(--color-surface-soft); display:flex; flex-direction:column; gap:.75rem; min-height:380px; max-height:520px; }
    .rc-day { align-self:center; background:var(--color-surface); border:1px solid var(--color-border); color:var(--color-text-muted); font-size:.68rem; font-weight:700; padding:.25rem .85rem; border-radius:999px; text-transform:uppercase; letter-spacing:.5px; }
    .rc-bubble-row { display:flex; max-width:80%; align-self:flex-end; }
    .rc-bubble { padding:.75rem 1rem; border-radius:16px; border-bottom-right-radius:6px; font-size:.87rem; line-height:1.55; background:var(--color-secondary); color:var(--color-on-solid); box-shadow:0 4px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent); word-break:break-word; }
    .rc-type-tag { display:inline-block; font-size:.66rem; font-weight:700; padding:.1rem .55rem; border-radius:999px; background:color-mix(in srgb, var(--color-surface) 22%, transparent); border:1px solid color-mix(in srgb, var(--color-border) 30%, transparent); margin-bottom:.3rem; }
    .rc-bubble-meta { display:flex; align-items:center; gap:.35rem; font-size:.68rem; margin-top:.3rem; opacity:.9; justify-content:flex-end; }
    .rc-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:3rem 1.5rem; color:var(--color-text-muted); }
    .rc-empty i { font-size:2.6rem; color:var(--color-secondary-text); margin-bottom:.7rem; }
    .rc-readonly { margin:0 1.25rem 1.25rem; background:var(--color-bg); border:1px dashed var(--color-border); border-radius:12px; padding:.7rem 1rem; font-size:.78rem; color:var(--color-text-muted); text-align:center; }

    /* ── Pager: compact pink pills (Bootstrap-5 markup) ── */
    .rc-pager { padding:.8rem 1rem; border-top:1px solid var(--color-border); background:var(--color-surface); }
    .rc-pager nav { display:flex; justify-content:center; }
    .rc-pager .pagination { margin:0; display:flex; justify-content:center; align-items:center; gap:.3rem; flex-wrap:nowrap; }
    .rc-pager .page-item .page-link { border:1px solid var(--color-border); background:var(--color-surface); color:var(--color-text-muted); font-size:.78rem; font-weight:700; border-radius:10px; padding:.35rem .7rem; min-width:34px; text-align:center; box-shadow:none; }
    .rc-pager .page-item .page-link:hover { border-color:var(--color-secondary-soft); color:var(--color-secondary-text); background:var(--color-secondary-soft); }
    .rc-pager .page-item.active .page-link { background:var(--color-secondary); border-color:var(--color-secondary); color:var(--color-on-solid); box-shadow:0 3px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent); }
    .rc-pager .page-item.disabled .page-link { color:var(--color-border); background:var(--color-bg); border-color:var(--color-border); }
    .rc-pager .page-link:focus { box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 16%, transparent); }
    .rc-pager p.small, .rc-pager .small { font-size:.72rem; color:var(--color-text-muted); text-align:center; margin:.4rem 0 0; }
    .rc-pager-arrows { display:flex; justify-content:center; align-items:center; gap:.5rem; }
    .rc-page-arrow { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border:1px solid var(--color-border); border-radius:10px; background:var(--color-surface); color:var(--color-text-muted); font-size:.85rem; text-decoration:none; }
    .rc-page-arrow:hover { border-color:var(--color-secondary-soft); color:var(--color-secondary-text); background:var(--color-secondary-soft); }
    .rc-page-arrow.disabled { color:var(--color-border); background:var(--color-bg); cursor:default; }
</style>
@endpush

@section('cho-content')
@php
    $grouped = $logs->getCollection()->groupBy(fn($l) => $l->user_id ?? ('phone:'.$l->phone_number));
    $selPhone = request('phone');
    $activeKey = null; $activeLogs = collect(); $lastDate = null;
    if ($selPhone) { $activeKey = 'phone:'.$selPhone; $activeLogs = isset($threadLogs) && $threadLogs->isNotEmpty() ? $threadLogs : $logs->getCollection()->where('phone_number', $selPhone)->sortBy('created_at'); }
    else { $activeKey = $grouped->keys()->first(); if ($activeKey !== null) { $activeLogs = is_numeric($activeKey) ? $logs->getCollection()->where('user_id', (int) $activeKey)->sortBy('created_at') : $logs->getCollection()->filter(fn($l) => ('phone:'.$l->phone_number) === $activeKey)->sortBy('created_at'); } }
    $activeName = $activeLogs->first()?->user?->name ?? ($activeLogs->first()?->phone_number ?? 'Select a thread');
    $activePhone = $activeLogs->first()?->phone_number ?? '';
@endphp
<div class="rc-sms">
    <div class="rc-sms-hero">
        <div class="d-flex align-items-center gap-3">
            <div class="rc-ico" style="display:inline-flex;"><i class="bi bi-chat-dots-fill"></i></div>
            <div>
                <h1>SMS Logs</h1>
                <p style="font-size:0.9rem; color:var(--color-text); font-weight:600; margin:0;">City-wide SMS history in chat view. Read-only for CHO.</p>
            </div>
        </div>
        <div class="rc-stat-pills">
            <span class="rc-stat-pill">Total <b>{{ $stats['total'] }}</b></span>
            <span class="rc-stat-pill ok">Sent <b>{{ $stats['sent'] }}</b></span>
            <span class="rc-stat-pill bad">Failed <b>{{ $stats['failed'] }}</b></span>
            <span class="rc-stat-pill">Opted-in <b>{{ $stats['enabled'] }}</b></span>
        </div>
    </div>

    <div class="rc-shell">
        <section class="rc-panel">
            <div class="rc-panel-head">
                <h3>Threads</h3>
                <form method="GET" class="rc-search">
                    <i class="bi bi-search"></i>
                    <input type="search" name="search" placeholder="Search name or number..." value="{{ request('search') }}">
                    @if(request('phone'))<input type="hidden" name="phone" value="{{ request('phone') }}">@endif
                </form>
                <form method="GET" class="rc-filter-row">
                    @if(request('phone'))<input type="hidden" name="phone" value="{{ request('phone') }}">@endif
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
                @forelse($grouped as $key => $thread)
                    @php $last = $thread->sortByDesc('created_at')->first(); $nm = $last->user?->name ?? $last->phone_number; $isActive = (string) $activeKey === (string) $key; $link = route('cho.sms.index', array_merge(request()->except('page'), ['phone' => $last->phone_number])); @endphp
                    <a href="{{ $link }}" class="rc-conv {{ $isActive ? 'active' : '' }}">
                        <span class="rc-av">{{ strtoupper(substr($nm, 0, 1)) }}</span>
                        <span class="rc-conv-meta">
                            <span class="d-flex justify-content-between align-items-baseline gap-2">
                                <span class="rc-conv-name">{{ $nm }}</span>
                                <span class="rc-conv-time">{{ $last->created_at->diffForHumans(null, true, true) }}</span>
                            </span>
                            <span class="rc-conv-preview d-block">{{ \Illuminate\Support\Str::limit($last->message, 48) }}</span>
                            <small style="color:var(--color-text-muted);">{{ $last->phone_number }} · {{ $thread->count() }} msg</small>
                        </span>
                        <span class="rc-dot {{ $last->status }}"></span>
                    </a>
                @empty
                    <div class="rc-empty"><i class="bi bi-chat-dots"></i><h6 class="fw-bold text-dark">No SMS yet</h6><p style="font-size:.82rem;">No messages match the current filters.</p></div>
                @endforelse
            </div>
            @if($logs->hasPages())
                <div class="rc-pager rc-pager-arrows">
                    @if($logs->onFirstPage())
                        <span class="rc-page-arrow disabled" aria-disabled="true"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <a class="rc-page-arrow" href="{{ $logs->withQueryString()->previousPageUrl() }}" rel="prev" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
                    @endif
                    @if($logs->hasMorePages())
                        <a class="rc-page-arrow" href="{{ $logs->withQueryString()->nextPageUrl() }}" rel="next" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
                    @else
                        <span class="rc-page-arrow disabled" aria-disabled="true"><i class="bi bi-chevron-right"></i></span>
                    @endif
                </div>
            @endif
        </section>

        <section class="rc-panel">
            <div class="rc-thread-head">
                <span class="rc-av">{{ strtoupper(substr($activeName, 0, 1)) }}</span>
                <div style="min-width:0;">
                    <h4>{{ $activeName }}</h4>
                    <small>{{ $activePhone }} · {{ $activeLogs->count() }} message(s)</small>
                </div>
                <span class="badge ms-auto" style="background:var(--color-secondary-soft);color:var(--color-secondary-text);border:1px solid var(--color-secondary-soft);">Read-only</span>
            </div>
            <div class="rc-thread-body">
                @forelse($activeLogs as $log)
                    @php $d = $log->created_at->format('Y-m-d'); $showDay = $d !== $lastDate; $lastDate = $d; @endphp
                    @if($showDay)<div class="rc-day">{{ $log->created_at->format('F d, Y') }}</div>@endif
                    <div class="rc-bubble-row">
                        <div class="rc-bubble">
                            <span class="rc-type-tag">{{ $log->type_icon }} {{ str_replace('_',' ', ucfirst($log->type)) }}</span>
                            <div style="white-space:pre-wrap;">{{ $log->message }}</div>
                            <div class="rc-bubble-meta"><span>{{ $log->sent_at ? $log->sent_at->format('g:i A') : $log->created_at->format('g:i A') }}</span><span>· {{ ucfirst($log->status) }}</span></div>
                            @if($log->error_message)<div style="font-size:.7rem;opacity:.9;margin-top:.2rem;">Error: {{ $log->error_message }}</div>@endif
                        </div>
                    </div>
                @empty
                    <div class="rc-empty"><i class="bi bi-chat-heart"></i><h6 class="fw-bold text-dark">No thread selected</h6><p style="font-size:.83rem;">Pick a thread on the left to view the chat history.</p></div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
