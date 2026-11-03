@extends('rhu.layout')

@section('title', 'Pending Women Registrations - ReproCare')

@push('styles')
@include('includes.women-directory-styles')
@include('includes.report-queue-styles')
<style>
    .pp-stack { display:flex; flex-direction:column; gap:0.6rem; }
    .pp-search { flex:1 1 210px; max-width:300px; }

    /* One row per registrant: identity and contact meta on the left, documents
       and the decision on the right. Replaces the three-field grid plus the two
       column footer, which made every card about 330px tall. */
    .pp-item { border-bottom:1px solid var(--color-border); }
    .pp-item:last-child { border-bottom:none; }
    .pp-row { display:flex; align-items:center; gap:0.7rem; padding:0.55rem 0.85rem; }
    .pp-row:hover { background:var(--color-bg); }
    .pp-main { flex:1 1 auto; min-width:0; }
    .pp-line { display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap; }
    .pp-name { font-weight:800; font-size:0.85rem; color:var(--color-primary-text); text-decoration:none; overflow-wrap:anywhere; }
    .pp-name:hover { text-decoration:underline; }
    .pp-pill { display:inline-flex; align-items:center; gap:0.28rem; font-size:0.66rem; font-weight:800; border-radius:999px; padding:0.16rem 0.5rem; white-space:nowrap; border:0; }
    .pp-pill-pending { background:var(--color-peach-soft); color:var(--color-peach-text); }
    .pp-pill-locked { background:var(--color-danger-soft); color:var(--color-danger-text); }
    .pp-pill-dup { background:var(--color-primary-soft); color:var(--color-primary-text); cursor:pointer; }
    .pp-pill-dup:hover { filter:brightness(.96); }
    .pp-meta { display:flex; align-items:center; gap:0.1rem 0.8rem; flex-wrap:wrap; margin-top:0.18rem; font-size:0.72rem; color:var(--color-text-muted); }
    .pp-meta span { display:inline-flex; align-items:center; gap:0.28rem; min-width:0; }
    .pp-meta i { font-size:0.68rem; }
    .pp-email { max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

    .pp-side { display:flex; align-items:center; gap:0.5rem; flex:0 0 auto; }
    .pp-docs { display:flex; gap:0.28rem; }
    .pp-doc { width:38px; height:29px; padding:0; overflow:hidden; border:1px solid var(--color-border); border-radius:7px; background:var(--color-surface-soft); }
    .pp-doc img { display:block; width:100%; height:100%; object-fit:cover; }
    .pp-doc:hover { border-color:var(--color-primary); }
    .pp-noid { display:inline-flex; align-items:center; gap:0.28rem; font-size:0.68rem; font-weight:800; color:var(--color-danger-text); background:var(--color-danger-soft); border-radius:999px; padding:0.2rem 0.55rem; white-space:nowrap; }
    .pp-actions { display:flex; gap:0.3rem; }
    /* The queue's own button sizing lives under .rq-wrap, and this list is not a
       table, so the compact pill sizes are set here with the same specificity
       portal-theme uses for `.main-content .btn`. */
    body .main-content .pp-actions .btn { padding:0.28rem 0.7rem; font-size:0.73rem; font-weight:800; border-radius:999px; white-space:nowrap; }

    .pp-panel { padding:0.6rem 0.85rem; border-bottom:1px solid var(--color-border); }
    .pp-panel-reject { background:var(--color-danger-soft); }
    .pp-panel-dup { background:var(--color-primary-soft); }
    .pp-dup-line { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.4rem 0.8rem; background:var(--color-surface); border:1px solid var(--color-border); border-radius:10px; padding:0.4rem 0.6rem; }
    .pp-dup-line + .pp-dup-line { margin-top:0.35rem; }
    .pp-identity-modal-image { display:block; max-width:100%; max-height:72vh; margin:auto; object-fit:contain; border-radius:10px; }

    /* The row needs ~560px beside the sidebar to keep the documents and the
       decision on the same line as the name, so the stacked layout starts at
       the tablet width rather than the phone width. */
    @media (max-width: 991.98px) {
        .pp-row { display:grid; grid-template-columns:auto 1fr; align-items:center; gap:0.35rem 0.6rem; padding:0.5rem 0.7rem; }
        /* Grid keeps the avatar beside the name and meta instead of starting a
           third line of its own. */
        .pp-main { grid-column:2; flex:none; }
        .pp-side { grid-column:1 / -1; flex:none; justify-content:flex-end; }
        .pp-email { max-width:100%; }
    }
</style>
@endpush

@section('rhu-content')

<div class="pp-stack">

    {{-- HEAD BAR: what the queue is, the search, and how big it is --}}
    <div class="rq-bar fade-in-card">
        <div class="rq-bar-id">
            <div class="rq-bar-title"><i class="bi bi-person-check-fill" aria-hidden="true"></i>Pending Registrations</div>
            <p class="rq-bar-subtitle">Review and verify new woman patient registrations before they can access the portal.</p>
        </div>

        <form method="GET" action="{{ route('rhu.pending-patients') }}" class="wd-search pp-search" role="search">
            <input type="search" name="search" class="form-control" autocomplete="off"
                   placeholder="Search pending patients..." value="{{ request('search') }}" aria-label="Search pending patients">
            <button class="btn" type="submit" title="Search"><i class="bi bi-search" aria-hidden="true"></i></button>
        </form>

        <div class="rq-chips" aria-label="Queue counts">
            <span class="summary-chip chip-warning rq-chip">
                <b>{{ number_format($pending->total()) }}</b> awaiting action
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- VERIFICATION QUEUE --}}
    <div class="card rq-wrap fade-in-card">
        @if($pending->isNotEmpty())
            <div class="pp-list">
                @foreach($pending as $woman)
                <article class="pp-item">
                    <div class="pp-row">
                        <x-patient-avatar :patient="$woman" size="34" />

                        <div class="pp-main">
                            <div class="pp-line">
                                <a href="{{ route('profile.view', $woman->id) }}" class="pp-name">{{ $woman->name }}</a>
                                @if($woman->status === 'inactive')
                                    <span class="pp-pill pp-pill-locked"><i class="bi bi-lock-fill" aria-hidden="true"></i> Locked — failed logins</span>
                                @else
                                    <span class="pp-pill pp-pill-pending"><i class="bi bi-clock" aria-hidden="true"></i> Verification pending</span>
                                @endif
                                @if(!empty($duplicates[$woman->id] ?? []))
                                    <button class="pp-pill pp-pill-dup" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#dup-{{ $woman->id }}"
                                            aria-expanded="false" aria-controls="dup-{{ $woman->id }}">
                                        <i class="bi bi-people-fill" aria-hidden="true"></i>
                                        {{ count($duplicates[$woman->id]) }} possible match{{ count($duplicates[$woman->id]) > 1 ? 'es' : '' }}
                                    </button>
                                @endif
                            </div>
                            <div class="pp-meta">
                                <span class="pp-email" title="{{ $woman->email }}">
                                    <i class="bi bi-envelope" aria-hidden="true"></i>{{ $woman->email }}
                                </span>
                                <span>
                                    <i class="bi bi-telephone" aria-hidden="true"></i>{{ $woman->phone ?? $woman->contact_number ?? '—' }}
                                </span>
                                <span>
                                    <i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $woman->barangay ? 'Brgy. ' . $woman->barangay : '—' }}
                                </span>
                                <span title="{{ $woman->created_at->format('M d, Y h:i A') }}">
                                    <i class="bi bi-calendar3" aria-hidden="true"></i>{{ $woman->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <div class="pp-side">
                            {{-- Keyed off the resolved URLs, not hasIdImages(): a stored
                                 path whose file is gone yields no thumb and would leave
                                 this slot blank. --}}
                            @php($idPhotos = array_filter([$woman->id_image_front_url, $woman->id_image_back_url]))
                            @if($idPhotos)
                                <div class="pp-docs">
                                    @if($woman->id_image_front_url)
                                        <button type="button" class="pp-doc" data-bs-toggle="modal" data-bs-target="#identityImageModal"
                                                data-image="{{ $woman->id_image_front_url }}" data-title="{{ $woman->name }} — face and ID front"
                                                title="View face and ID front" aria-label="View ID front for {{ $woman->name }}">
                                            <img src="{{ $woman->id_image_front_url }}" alt="" loading="lazy" decoding="async">
                                        </button>
                                    @endif
                                    @if($woman->id_image_back_url)
                                        <button type="button" class="pp-doc" data-bs-toggle="modal" data-bs-target="#identityImageModal"
                                                data-image="{{ $woman->id_image_back_url }}" data-title="{{ $woman->name }} — ID back"
                                                title="View ID back" aria-label="View ID back for {{ $woman->name }}">
                                            <img src="{{ $woman->id_image_back_url }}" alt="" loading="lazy" decoding="async">
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span class="pp-noid"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>No ID</span>
                            @endif

                            <div class="pp-actions">
                                @if($woman->status === 'inactive')
                                    <form method="POST" action="{{ route('rhu.reactivate-patient', $woman->id) }}"
                                          data-confirm="Reactivate {{ $woman->name }}? They will be able to log in again.">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-unlock-fill me-1" aria-hidden="true"></i>Reactivate
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('rhu.approve-patient', $woman->id) }}"
                                          data-confirm="Approve and activate {{ $woman->name }}?">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-outline-danger" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#reject-{{ $woman->id }}"
                                            aria-expanded="false" aria-controls="reject-{{ $woman->id }}">
                                        <i class="bi bi-x-lg me-1" aria-hidden="true"></i>Reject
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($woman->status !== 'inactive')
                    <div class="collapse pp-panel pp-panel-reject" id="reject-{{ $woman->id }}">
                        <form method="POST" action="{{ route('rhu.reject-patient', $woman->id) }}">
                            @csrf
                            <label class="form-label fw-bold small mb-1 text-danger" for="reason-{{ $woman->id }}">Reason for rejection</label>
                            <textarea id="reason-{{ $woman->id }}" name="reason" class="form-control form-control-sm mb-2"
                                      placeholder="State clinical or identity reason..." rows="2" required></textarea>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-light border"
                                        data-bs-toggle="collapse" data-bs-target="#reject-{{ $woman->id }}">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-danger fw-bold">Confirm rejection</button>
                            </div>
                        </form>
                    </div>
                    @endif

                    @if(!empty($duplicates[$woman->id] ?? []))
                    <div class="collapse pp-panel pp-panel-dup" id="dup-{{ $woman->id }}">
                        <div class="fw-bold mb-2" style="color:var(--color-primary-text); font-size:0.8rem;">
                            <i class="bi bi-people-fill me-1" aria-hidden="true"></i>
                            Possible existing profiles for {{ $woman->name }} — link instead of creating a duplicate?
                        </div>
                        @foreach($duplicates[$woman->id] as $match)
                            <div class="pp-dup-line">
                                <div style="font-size:0.8rem; min-width:0;">
                                    <strong>{{ $match['name'] }}</strong>
                                    <span class="badge bg-light text-dark border ms-1">{{ $match['kind'] === 'walk_in' ? 'BHW field record' : 'Portal account (' . $match['status'] . ')' }}</span>
                                    <span class="badge ms-1" style="background:var(--color-primary-soft);color:var(--color-primary-text);">score {{ $match['score'] }} · {{ str_replace('_', ' ', $match['match_type']) }}</span>
                                    <div class="text-muted small">
                                        {{ $match['barangay'] ?? '—' }}
                                        @if($match['birthdate']) · born {{ $match['birthdate'] }} @endif
                                        @if($match['contact']) · {{ $match['contact'] }} @endif
                                    </div>
                                </div>
                                <div class="pp-actions">
                                    @if($match['kind'] === 'walk_in')
                                        <form method="POST" action="{{ route('rhu.link-duplicate', $woman->id) }}"
                                              data-confirm="Link this field profile to {{ $woman->name }}? Histories merge, nothing is deleted.">
                                            @csrf
                                            <input type="hidden" name="walk_in_patient_id" value="{{ $match['id'] }}">
                                            <button type="submit" class="btn btn-sm btn-primary fw-bold">
                                                <i class="bi bi-link-45deg me-1" aria-hidden="true"></i>Link
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('rhu.dismiss-duplicate', $woman->id) }}">
                                        @csrf
                                        <input type="hidden" name="kind" value="{{ $match['kind'] }}">
                                        <input type="hidden" name="match_id" value="{{ $match['id'] }}">
                                        <input type="hidden" name="match_type" value="{{ $match['match_type'] }}">
                                        <input type="hidden" name="score" value="{{ $match['score'] }}">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Not the same person</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </article>
                @endforeach
            </div>

            @if ($pending->hasPages())
                <div class="rq-foot">{{ $pending->links('vendor.pagination.arrows-only') }}</div>
            @endif
        @else
            <div class="rq-empty">
                <i class="bi bi-person-check-fill" style="color:var(--color-success-text);" aria-hidden="true"></i>
                <h6 class="fw-bold mt-2 mb-1" style="color:var(--color-text);">No pending registrations</h6>
                @if(request('search'))
                    <p class="text-muted mb-2" style="font-size:0.82rem;">No pending women matched “{{ request('search') }}”.</p>
                    <a href="{{ route('rhu.pending-patients') }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                        <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>Clear search
                    </a>
                @else
                    <p class="text-muted mb-2" style="font-size:0.82rem;">Every woman registration has been reviewed and validated.</p>
                    <a href="{{ route('rhu.patients.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-person-plus-fill me-1" aria-hidden="true"></i>Register new woman
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="identityImageModal" tabindex="-1" aria-labelledby="identityImageModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="identityImageModalTitle">Identity image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <img id="identityImageModalPhoto" class="pp-identity-modal-image" src="" alt="Selected identity document">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('identityImageModal')?.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        const image = this.querySelector('#identityImageModalPhoto');
        image.src = trigger.dataset.image;
        image.alt = trigger.dataset.title;
        this.querySelector('#identityImageModalTitle').textContent = trigger.dataset.title;
    });

    // Inline confirm() strings never fire when a handler is rebound; a delegated
    // listener keeps the guard on every decision form in the queue.
    document.addEventListener('submit', function (event) {
        const message = event.target.dataset?.confirm;
        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    });
</script>
@endpush

@endsection
