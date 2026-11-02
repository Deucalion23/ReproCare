{{-- SMS workspace, shared by the midwife and RHU portals (they were byte-identical
     apart from the accent hue). Expects $wsRoute ('midwife.sms' | 'rhu.sms') and
     $wsAccent ('primary' | 'secondary'); the controller supplies $patients,
     $statusTabs, $recentByUser, $stats, $search, $status, $type, $puroks and the
     thread variables. --}}
@php
    $wsRoute = $wsRoute ?? 'midwife.sms';
    $wsAccent = $wsAccent ?? 'primary';
    $search = (string) ($search ?? '');
    $status = (string) ($status ?? '');
    $type = (string) ($type ?? '');
    $threadId = $threadUserId ?? null;
    $threadPhone = trim((string) ($threadPhone ?? ''));
    $threadLogs = $threadLogs ?? collect();
    $threadUser = $threadUser ?? null;
    // A hand-typed ?to= for a patient that does not exist is not an open thread.
    $openThread = (bool) ($threadUser || $threadPhone !== '');

    $peerName = $threadUser?->name
        ?: ($threadLogs->first()?->user?->name ?: ($threadPhone !== '' ? $threadPhone : 'No patient selected'));
    $peerPhone = $threadUser?->contact_number ?: ($threadLogs->first()?->phone_number ?: $threadPhone);

    // One URL builder for every control: filters survive a tab click, a search,
    // or opening a thread, and changing a filter restarts pagination.
    $wsUrl = function (array $changes = [], array $unset = [], bool $keepPage = false) use ($wsRoute) {
        $query = array_merge(request()->query(), $changes);
        foreach ($unset as $key) {
            unset($query[$key]);
        }
        if (!$keepPage) {
            unset($query['page']);
        }
        return route($wsRoute.'.index', array_filter($query, fn ($value) => $value !== '' && $value !== null && $value !== []));
    };
    $isFiltered = $search !== '' || $status !== '' || $type !== '';
@endphp

<div class="rc-sms" style="--rc-accent:var(--color-{{ $wsAccent }}); --rc-accent-soft:var(--color-{{ $wsAccent }}-soft); --rc-accent-text:var(--color-{{ $wsAccent }}-text);">
    <div class="rc-sms-bar">
        <div class="rc-sms-heading">
            <h1><i class="bi bi-chat-dots-fill"></i> SMS Alerts</h1>
            <p>Reminders and health alerts straight to a patient's phone.</p>
        </div>
        <div class="rc-sms-tools">
            <span class="rc-optin" title="Approved patients with a number who have not opted out"><i class="bi bi-people-fill"></i> {{ $stats['enabled'] }} opted-in</span>
            <div class="rc-seg" role="group" aria-label="Filter patients by delivery status">
                @foreach($statusTabs as $tabKey => $tab)
                    <a href="{{ $wsUrl(['status' => $tabKey === '' ? null : $tabKey]) }}"
                       class="rc-seg-btn {{ $status === $tabKey ? 'active' : '' }}"
                       @if($status === $tabKey) aria-current="page" @endif>{{ $tab['label'] }} <b>{{ $tab['count'] }}</b></a>
                @endforeach
            </div>
            <form method="GET" action="{{ $wsUrl() }}" class="rc-sms-form" role="search">
                <input type="hidden" name="status" value="{{ $status }}">
                @if($threadUser)<input type="hidden" name="to" value="{{ $threadUser->id }}">@endif
                @if($threadPhone !== '')<input type="hidden" name="phone" value="{{ $threadPhone }}">@endif
                <span class="rc-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" name="search" value="{{ $search }}" placeholder="Name or number..." aria-label="Search patients by name or number">
                </span>
                <select name="type" class="rc-select" aria-label="Filter by message type">
                    @foreach(['' => 'All types', 'appointment_reminder' => 'Appointment', 'high_risk_alert' => 'High risk', 'missed_checkup' => 'Missed check-up', 'custom' => 'Custom', 'broadcast' => 'Broadcast'] as $optionKey => $optionLabel)
                        <option value="{{ $optionKey }}" {{ $type === $optionKey ? 'selected' : '' }}>{{ $optionLabel }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rc-submit" aria-label="Apply search"><i class="bi bi-search"></i></button>
            </form>
        </div>
        <button type="button" class="rc-btn-pink rc-sms-broadcast" data-bs-toggle="modal" data-bs-target="#smsBroadcastModal"><i class="bi bi-megaphone-fill"></i> Broadcast</button>
    </div>

    @if($isFiltered)
        <div class="rc-filterline d-flex align-items-center gap-2 mb-2" style="font-size:.74rem; color:var(--color-text-muted);">
            <i class="bi bi-funnel-fill"></i>
            <span>Filtered by {{ collect(['search' => $search !== '' ? '"'.$search.'"' : null, 'status' => $status !== '' ? $status : null, 'type' => $type !== '' ? str_replace('_',' ',$type) : null])->filter()->implode(', ') }}.</span>
            <a href="{{ $wsUrl([], ['status', 'type', 'search', 'to', 'phone']) }}" class="rc-reset"><i class="bi bi-x-lg"></i> Clear</a>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-2" style="border-radius:12px; font-size:.8rem;"><i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}<button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 mb-2" style="border-radius:12px; font-size:.8rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ session('error') }}<button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button></div>
    @endif

    <div class="rc-sms-shell" data-thread="{{ $openThread ? 1 : 0 }}">
        <section class="rc-panel rc-conv-panel" aria-label="Patients">
            <div class="rc-panel-head" aria-live="polite">
                <h3><i class="bi bi-person-lines-fill"></i> Patients</h3>
                <span class="rc-count">{{ $patients->total() }} shown</span>
            </div>

            <div class="rc-conv-list">
                @forelse($patients as $patient)
                    @php
                        $last = $recentByUser->get($patient->id);
                        $label = $patient->name ?: trim($patient->first_name.' '.$patient->last_name);
                    @endphp
                    <a href="{{ $wsUrl(['to' => $patient->id], [], true) }}"
                       class="rc-conv {{ $threadId === (int) $patient->id ? 'active' : '' }} {{ $last ? 'has-thread' : '' }}"
                       title="{{ $patient->contact_number }}@if($last) — {{ $last->message }}@endif">
                        <x-patient-avatar :patient="$patient" :size="34" />
                        <span class="rc-conv-meta">
                            <span class="rc-conv-top">
                                <span class="rc-conv-name">{{ $label }}</span>
                                <span class="rc-conv-time">{{ $last ? $last->created_at->diffForHumans(null, true, true) : 'new' }}</span>
                            </span>
                            <span class="rc-conv-sub">{{ $last ? \Illuminate\Support\Str::limit($last->message, 54) : $patient->contact_number }}</span>
                        </span>
                        <span class="rc-dot {{ $last?->status ?? '' }}"></span>
                    </a>
                @empty
                    <div class="rc-empty">
                        <i class="bi bi-chat-dots"></i>
                        <strong>{{ $isFiltered ? 'No match' : 'No patients yet' }}</strong>
                        <span>{{ $isFiltered ? 'No SMS-enabled patient matches these filters.' : 'Approved patients with a mobile number appear here.' }}</span>
                    </div>
                @endforelse
            </div>

            @if($patients->hasPages())
                <div class="rc-pager">{{ $patients->links() }}</div>
            @endif
        </section>

        <section class="rc-panel rc-thread-panel" aria-label="Conversation">
            @if($openThread)
                <div class="rc-thread-head">
                    <a href="{{ $wsUrl([], ['to', 'phone']) }}" class="rc-back"><i class="bi bi-chevron-left"></i> Patients</a>
                    <x-patient-avatar :patient="$threadUser" :name="$peerName" :size="34" />
                    <div class="rc-peer">
                        <h2>{{ $peerName }}</h2>
                        <small>
                            <i class="bi bi-telephone-fill me-1"></i>{{ $peerPhone ?: 'No number on file' }}
                            @if($threadUser)
                                · <span class="{{ $threadUser->hasSmsEnabled() ? 'on' : 'off' }}">{{ $threadUser->hasSmsEnabled() ? 'SMS enabled' : 'SMS unavailable' }}</span>
                            @endif
                        </small>
                    </div>
                    <span class="rc-count">{{ $threadLogs->count() }} msg</span>
                </div>

                <div class="rc-thread-body" id="smsThread" tabindex="0">
                    @php $seenDay = null; @endphp
                    @forelse($threadLogs as $log)
                        @php
                            $day = $log->created_at->format('Y-m-d');
                            $showDay = $day !== $seenDay; $seenDay = $day;
                        @endphp
                        @if($showDay)<div class="rc-day">{{ $log->created_at->format('F d, Y') }}</div>@endif
                        <div class="rc-bubble-row out">
                            <div class="rc-bubble">
                                <span class="rc-type-tag">{{ $log->type_icon }} {{ str_replace('_',' ',ucfirst($log->type)) }}</span>
                                <div style="white-space:pre-wrap;">{{ $log->message }}</div>
                                <div class="rc-bubble-meta">
                                    <span>{{ ($log->sent_at ?? $log->created_at)->format('g:i A') }}</span>
                                    <i class="bi {{ $log->status === 'sent' ? 'bi-check2-all' : ($log->status === 'failed' ? 'bi-exclamation-circle' : 'bi-clock') }}"></i>
                                    <span class="st-{{ $log->status }}">{{ ucfirst($log->status) }}</span>
                                </div>
                                @if($log->error_message)<div class="rc-bubble-meta st-failed">{{ $log->error_message }}</div>@endif
                            </div>
                        </div>
                    @empty
                        <div class="rc-empty">
                            <i class="bi bi-chat-heart"></i>
                            <strong>No messages yet</strong>
                            <span>Send the first reminder below — it lands here like a chat.</span>
                        </div>
                    @endforelse
                </div>

                @if($threadUser)
                    <div class="rc-composer">
                        <form action="{{ route($wsRoute.'.send') }}" method="POST" id="smsForm">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $threadUser->id }}">
                            <div class="rc-lang">
                                <button type="button" class="active" data-lang="en">English</button>
                                <button type="button" data-lang="tl">Tagalog</button>
                                <span class="hint">Ctrl+Enter sends</span>
                            </div>
                            <div class="rc-composer-bar">
                                <textarea name="message_en" id="smsEn" rows="2" maxlength="320" required
                                          placeholder="SMS to {{ $peerName }}..."></textarea>
                                <button type="submit" class="rc-send" title="Send SMS" aria-label="Send SMS"><i class="bi bi-send-fill"></i></button>
                            </div>
                            <textarea name="message_tl" id="smsTl" class="d-none mt-2" rows="2" maxlength="320"
                                      placeholder="Isulat ang mensahe sa Tagalog..."
                                      style="width:100%;border:none;border-radius:12px;background:var(--color-surface-soft);padding:.55rem .8rem;font-size:.82rem;outline:none;"></textarea>
                            <div class="rc-char"><span id="smsCount">0</span>/320</div>
                        </form>
                    </div>
                @else
                    <div class="rc-composer"><p class="rc-char mb-0" style="text-align:left;">This number has no portal account, so it cannot be replied to from here.</p></div>
                @endif
            @else
                <div class="rc-empty">
                    <i class="bi bi-cursor-fill"></i>
                    <strong>Pick a patient</strong>
                    <span>Choose anyone on the left to read their thread and send an SMS.</span>
                </div>
            @endif
        </section>
    </div>
</div>

<div class="modal fade" id="smsBroadcastModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:none; border-radius:18px; overflow:hidden;">
            <div class="modal-header py-2">
                <h5 class="modal-title" style="font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:.95rem;">Broadcast SMS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route($wsRoute.'.broadcast') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted" for="broadcastPurok">Filter by Purok (optional)</label>
                        <select name="purok_id" id="broadcastPurok" class="form-select form-select-sm" style="border-radius:10px;">
                            <option value="">— All Puroks —</option>
                            @foreach($puroks as $purok)<option value="{{ $purok->id }}">{{ $purok->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted" for="broadcastEn">Message (English)</label>
                        <textarea name="message_en" id="broadcastEn" class="form-control form-control-sm" rows="3" required maxlength="320" placeholder="Broadcast message in English..." style="border-radius:10px;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted" for="broadcastTl">Message (Tagalog) (optional)</label>
                        <textarea name="message_tl" id="broadcastTl" class="form-control form-control-sm" rows="3" maxlength="320" placeholder="Mensahe sa Tagalog..." style="border-radius:10px;"></textarea>
                    </div>
                    <div class="alert alert-warning py-2 mb-0" style="font-size:.76rem; border-radius:10px;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Sends to up to <strong>{{ $stats['enabled'] }}</strong> opted-in patients. Cannot be undone.
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="rc-btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="rc-btn-pink" onclick="return confirm('Send broadcast SMS to {{ $stats['enabled'] }} patient(s)?')"><i class="bi bi-megaphone-fill"></i> Send Broadcast</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var shell = document.querySelector('.rc-sms-shell');

    // The columns fill exactly the space left under the bar, so the list and the
    // composer never fall below the fold. Measured, not guessed, because the bar
    // wraps differently on every width.
    function sizePanels() {
        if (!shell) { return; }
        var top = shell.getBoundingClientRect().top;
        var height = Math.round(Math.max(340, Math.min(760, window.innerHeight - top - 14)));
        shell.style.setProperty('--rc-panel-h', height + 'px');
    }
    sizePanels();
    window.addEventListener('resize', sizePanels);
    window.addEventListener('load', sizePanels);

    // Staff read the list while they type, so the panel refreshes in place: a real
    // form submit would reload the page and drop the caret they are still using.
    var searchForm = document.querySelector('.rc-sms-form');
    var searchBox = searchForm ? searchForm.querySelector('input[name="search"]') : null;
    var convPanel = document.querySelector('.rc-conv-panel');

    if (searchForm && searchBox && convPanel) {
        var seq = 0;
        var settle = null;
        var lastQuery = null;
        var endpoint = searchForm.getAttribute('action').split('?')[0];
        var workspace = document.querySelector('.rc-sms');

        var syncFilterLine = function (doc) {
            var next = doc.querySelector('.rc-filterline');
            var current = document.querySelector('.rc-filterline');
            if (current && next) { current.innerHTML = next.innerHTML; return; }
            if (current && !next) { current.remove(); return; }
            if (!current && next && workspace) {
                workspace.insertBefore(document.importNode(next, true), shell);
            }
        };

        var refresh = function () {
            var params = new URLSearchParams();
            new FormData(searchForm).forEach(function (value, key) {
                if (String(value).trim() !== '') { params.append(key, value); }
            });
            var query = params.toString();
            if (query === lastQuery) { return; }
            lastQuery = query;

            var url = query === '' ? endpoint : endpoint + '?' + query;
            var attempt = ++seq;
            convPanel.classList.add('is-loading');

            fetch(url, { credentials: 'same-origin' })
                .then(function (response) { return response.text(); })
                .then(function (html) {
                    if (attempt !== seq) { return; }
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var nextPanel = doc.querySelector('.rc-conv-panel');
                    if (nextPanel) { convPanel.innerHTML = nextPanel.innerHTML; }
                    syncFilterLine(doc);
                    window.history.replaceState(null, '', url);
                })
                .catch(function () { lastQuery = null; })
                .finally(function () {
                    if (attempt === seq) { convPanel.classList.remove('is-loading'); }
                });
        };

        searchBox.addEventListener('input', function () {
            clearTimeout(settle);
            settle = setTimeout(refresh, 260);
        });
        // Enter and the magnifier take the same path so the caret survives.
        searchForm.addEventListener('submit', function (event) {
            event.preventDefault();
            clearTimeout(settle);
            refresh();
        });
        // The type dropdown used to call form.submit(), which bypasses this handler
        // and reloads the page; without script the magnifier still applies it.
        var typeSelect = searchForm.querySelector('.rc-select');
        if (typeSelect) { typeSelect.addEventListener('change', refresh); }
    }

    var thread = document.getElementById('smsThread');
    if (thread) { thread.scrollTop = thread.scrollHeight; }

    var en = document.getElementById('smsEn');
    var tl = document.getElementById('smsTl');
    var count = document.getElementById('smsCount');
    var form = document.getElementById('smsForm');

    if (en && count) {
        en.addEventListener('input', function () {
            count.textContent = en.value.length;
            en.style.height = 'auto';
            en.style.height = Math.min(en.scrollHeight, 120) + 'px';
        });
    }

    document.querySelectorAll('.rc-lang button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.rc-lang button').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            if (!tl) { return; }
            tl.classList.toggle('d-none', btn.dataset.lang !== 'tl');
            if (btn.dataset.lang === 'tl') { tl.focus(); }
        });
    });

    // Field staff type on a phone: Ctrl+Enter sends without hunting for the button.
    [en, tl].forEach(function (box) {
        if (!box || !form) { return; }
        box.addEventListener('keydown', function (event) {
            if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                event.preventDefault();
                form.requestSubmit();
            }
        });
    });
})();
</script>
