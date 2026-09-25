@extends('rhu.layout')

@section('title', 'Pending Women Registrations - ReproCare')

@push('styles')
<style>
    .pp-eyebrow { display:inline-flex; align-items:center; gap:.5rem; font-size:.68rem; font-weight:800; letter-spacing:1.4px; text-transform:uppercase; background:var(--color-warning-soft); color:var(--color-warning-text); border:1px solid var(--color-warning); border-radius:999px; padding:.32rem .8rem; margin-bottom:.7rem; }
    .pp-eyebrow .dot { width:8px; height:8px; border-radius:50%; background:var(--color-warning); box-shadow:0 0 0 0 color-mix(in srgb, rgb(var(--color-shadow-rgb)) 50%, transparent); animation:pp-pulse 1.8s infinite; }
    @keyframes pp-pulse { 0% { box-shadow:0 0 0 0 color-mix(in srgb, rgb(var(--color-shadow-rgb)) 45%, transparent); } 70% { box-shadow:0 0 0 7px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 0%, transparent); } 100% { box-shadow:0 0 0 0 color-mix(in srgb, rgb(var(--color-shadow-rgb)) 0%, transparent); } }
    .pp-search-box { position:relative; width:280px; max-width:100%; display:flex; align-items:center; }
    .pp-search-box > i { position:absolute; left:0.9rem; color:var(--color-text-muted); font-size:0.9rem; pointer-events:none; }
    .pp-search-box input { width:100%; border-radius:999px !important; border:1px solid var(--color-border) !important; background:var(--color-bg) !important; padding:0.6rem 2.5rem 0.6rem 2.5rem !important; font-size:0.87rem !important; color:var(--color-text); transition:all .18s ease; }
    .pp-search-box input::placeholder { color:var(--color-text-muted); }
    .pp-search-box input:focus { outline:none !important; border-color:var(--color-text) !important; background:var(--color-surface) !important; box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent) !important; }
    .pp-clear-btn { position:absolute; right:0.45rem; width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:var(--color-surface-soft); color:var(--color-text-muted); font-size:0.7rem; text-decoration:none; transition:all .15s ease; }
    .pp-clear-btn:hover { background:var(--color-surface-strong); color:var(--color-on-solid); }
    @media (max-width: 768px) {
        .pp-search-box { width:100%; }
        .pp-search-wrap { width:100%; }
        .pp-search-wrap form { width:100%; }
    }
    /* Card-based verification list — every field stays fully visible, no squeezed columns. */
    .pp-list { display:flex; flex-direction:column; gap:1rem; padding:1.25rem; }
    .pp-card { border:1px solid var(--color-border); border-radius:16px; background:var(--color-surface); box-shadow:0 2px 10px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 5%, transparent); overflow:hidden; }
    .pp-card-top { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:.9rem; padding:1rem 1.25rem; border-bottom:1px dashed var(--color-border); }
    .pp-patient { display:flex; align-items:center; gap:.85rem; min-width:0; flex:1 1 300px; }
    .pp-patient-name { font-weight:800; font-size:1rem; color:var(--color-primary-text); text-decoration:underline; text-underline-offset:2px; overflow-wrap:anywhere; }
    .pp-patient-name:hover { color:var(--color-primary); }
    .pp-badges { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.3rem; }
    .pp-badge-pending { display:inline-flex; align-items:center; gap:.3rem; background:var(--color-peach-soft); color:var(--color-peach-text); font-size:.7rem; font-weight:800; border-radius:999px; padding:.22rem .6rem; white-space:nowrap; }
    .pp-badge-dup { display:inline-flex; align-items:center; gap:.3rem; background:var(--color-primary-soft); color:var(--color-primary-text); font-size:.7rem; font-weight:800; border-radius:999px; padding:.22rem .6rem; border:0; cursor:pointer; white-space:nowrap; }
    .pp-badge-dup:hover { filter:brightness(.96); }
    .pp-registered { display:flex; align-items:center; gap:.5rem; flex-shrink:0; font-size:.8rem; color:var(--color-text-muted); background:var(--color-surface-soft); border:1px solid var(--color-border); border-radius:999px; padding:.35rem .8rem; white-space:nowrap; }
    .pp-card-grid { display:grid; grid-template-columns:1.5fr 1fr 1fr; gap:1rem; padding:1rem 1.25rem; }
    .pp-field { min-width:0; background:var(--color-surface-soft); border:1px solid var(--color-border); border-radius:12px; padding:.7rem .85rem; }
    .pp-field-label { display:flex; align-items:center; gap:.35rem; font-size:.66rem; font-weight:800; letter-spacing:.07em; text-transform:uppercase; color:var(--color-text-muted); margin-bottom:.3rem; white-space:nowrap; }
    .pp-field-value { font-size:.88rem; color:var(--color-text); font-weight:600; line-height:1.45; overflow-wrap:anywhere; }
    .pp-field-value.nowrap { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .pp-barangay-pill { display:inline-flex; align-items:center; gap:.3rem; background:var(--color-surface); border:1px solid var(--color-border); border-radius:8px; padding:.25rem .6rem; font-size:.82rem; font-weight:700; white-space:nowrap; max-width:100%; overflow:hidden; text-overflow:ellipsis; }
    .pp-card-foot { display:grid; grid-template-columns:1.25fr .9fr; gap:1rem; padding:1rem 1.25rem; background:color-mix(in srgb, var(--color-surface-soft) 55%, transparent); border-top:1px solid var(--color-border); }
    .pp-foot-title { display:flex; align-items:center; gap:.35rem; font-size:.66rem; font-weight:800; letter-spacing:.07em; text-transform:uppercase; color:var(--color-text-muted); margin-bottom:.55rem; white-space:nowrap; }
    .pp-id-preview { display:flex; flex-wrap:wrap; gap:.55rem; }
    .pp-id-button { display:flex; align-items:center; gap:.6rem; padding:.4rem .7rem .4rem .4rem; border:1px solid var(--color-border); border-radius:12px; background:var(--color-surface); color:var(--color-text); font-size:.78rem; font-weight:700; text-align:left; transition:all .15s ease; min-width:0; }
    .pp-id-button:hover { background:var(--color-primary-soft); border-color:var(--color-primary); color:var(--color-primary-text); transform:translateY(-1px); }
    .pp-id-button img { width:58px; height:42px; object-fit:cover; border-radius:8px; flex-shrink:0; }
    .pp-id-button small { display:block; font-weight:600; font-size:.68rem; color:var(--color-text-muted); }
    .pp-id-hint { font-size:.7rem; color:var(--color-text-muted); margin-top:.45rem; }
    .pp-decision { display:flex; flex-direction:column; gap:.55rem; }
    .pp-decision-btns { display:grid; grid-template-columns:1fr 1fr; gap:.55rem; }
    .pp-decision-btns .btn { border-radius:10px; font-weight:800; padding:.55rem .5rem; white-space:nowrap; }
    .pp-dup-wrap { border-top:1px dashed var(--color-border); background:var(--color-primary-soft); }
    .pp-identity-modal-image { display:block; max-width:100%; max-height:72vh; margin:auto; object-fit:contain; border-radius:10px; }
    @media (max-width: 1100px) { .pp-card-grid { grid-template-columns:1fr 1fr; } .pp-card-foot { grid-template-columns:1fr; } }
    @media (max-width: 640px) { .pp-list { padding:.8rem; } .pp-card-grid { grid-template-columns:1fr; } .pp-card-top { padding:.9rem; } .pp-card-grid, .pp-card-foot { padding:.9rem; } .pp-registered { width:100%; justify-content:center; } }
</style>
@endpush

@section('rhu-content')

{{-- PAGE HERO --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div style="min-width:240px; flex:1 1 320px;">
            <span class="pp-eyebrow"><span class="dot"></span> Verification queue{{ $pending->total() > 0 ? ' · ' . $pending->total() . ' awaiting' : '' }}</span>
            <h1 class="page-hero-title mb-1">Pending Registrations</h1>
            <p class="page-hero-subtitle mb-0">Review and verify new woman patient registrations before they can access the portal.</p>
        </div>
        <div class="pp-search-wrap d-flex gap-2 align-items-center">
            <form method="GET" action="{{ route('rhu.pending-patients') }}" class="d-flex" role="search">
                <div class="pp-search-box">
                    <i class="bi bi-search"></i>
                    <input type="search" name="search"
                            placeholder="Search pending patients..." value="{{ request('search') }}" aria-label="Search pending patients">
                    @if(request('search'))
                        <a href="{{ route('rhu.pending-patients') }}" class="pp-clear-btn" title="Clear search" aria-label="Clear search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
         style="background:color-mix(in srgb, var(--color-success) 12%, transparent); border:1px solid color-mix(in srgb, var(--color-success) 30%, transparent); color:var(--color-success-text); border-radius:14px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- PENDING REGISTRATIONS TABLE --}}
<div class="card fade-in-card">
    @if($pending->count() > 0)
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--color-text);">
                Pending Verification
            </h5>
            <span class="badge rounded-pill px-3 py-1 fw-bold" style="background:var(--color-peach-soft); border:1px solid var(--color-peach-soft); color:var(--color-peach-text);">
                {{ $pending->total() }} Awaiting Action
            </span>
        </div>
        <div class="card-body p-0">
            <div class="pp-list">
                @foreach($pending as $woman)
                <article class="pp-card">
                    {{-- Header: patient + registered --}}
                    <div class="pp-card-top">
                        <div class="pp-patient">
                            <x-patient-avatar :patient="$woman" />
                            <div style="min-width:0;">
                                <a href="{{ route('profile.view', $woman->id) }}" class="pp-patient-name">{{ $woman->name }}</a>
                                <div class="pp-badges">
                                    <span class="pp-badge-pending"><i class="bi bi-clock"></i> Verification Pending</span>
                                    @if(!empty($duplicates[$woman->id] ?? []))
                                        <button class="pp-badge-dup" type="button" data-bs-toggle="collapse" data-bs-target="#dup-{{ $woman->id }}">
                                            <i class="bi bi-people-fill"></i> {{ count($duplicates[$woman->id]) }} possible match{{ count($duplicates[$woman->id]) > 1 ? 'es' : '' }} — review
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <span class="pp-registered" title="{{ $woman->created_at->format('M d, Y h:i A') }}">
                            <i class="bi bi-calendar3"></i> {{ $woman->created_at->diffForHumans() }}
                        </span>
                    </div>

                    {{-- Contact details: full width, no truncation --}}
                    <div class="pp-card-grid">
                        <div class="pp-field">
                            <div class="pp-field-label"><i class="bi bi-envelope"></i> Email address</div>
                            <div class="pp-field-value">{{ $woman->email }}</div>
                        </div>
                        <div class="pp-field">
                            <div class="pp-field-label"><i class="bi bi-telephone"></i> Contact</div>
                            <div class="pp-field-value nowrap">
                                @if($woman->phone ?? $woman->contact_number)
                                    {{ $woman->phone ?? $woman->contact_number }}
                                @else
                                    <span style="color:var(--color-text-muted);">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="pp-field">
                            <div class="pp-field-label"><i class="bi bi-geo-alt"></i> Barangay</div>
                            <div class="pp-field-value">
                                @if($woman->barangay)
                                    <span class="pp-barangay-pill"><i class="bi bi-pin-map" style="color:var(--color-primary-text);"></i> Brgy. {{ $woman->barangay }}</span>
                                @else
                                    <span style="color:var(--color-text-muted);">—</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Identity + decision side by side --}}
                    <div class="pp-card-foot">
                        <div style="min-width:0;">
                            <div class="pp-foot-title"><i class="bi bi-person-vcard"></i> Identity review</div>
                            @if($woman->hasIdImages())
                                <div class="pp-id-preview">
                                    @if($woman->id_image_front_url)
                                        <button type="button" class="pp-id-button" data-bs-toggle="modal" data-bs-target="#identityImageModal"
                                            data-image="{{ $woman->id_image_front_url }}" data-title="{{ $woman->name }} — face and ID front">
                                            <img src="{{ $woman->id_image_front_url }}" alt="ID front thumbnail">
                                            <span><i class="bi bi-person-bounding-box me-1"></i>View face<small>Front + selfie</small></span>
                                        </button>
                                    @endif
                                    @if($woman->id_image_back_url)
                                        <button type="button" class="pp-id-button" data-bs-toggle="modal" data-bs-target="#identityImageModal"
                                            data-image="{{ $woman->id_image_back_url }}" data-title="{{ $woman->name }} — ID back">
                                            <img src="{{ $woman->id_image_back_url }}" alt="ID back thumbnail">
                                            <span><i class="bi bi-card-text me-1"></i>View back<small>ID reverse</small></span>
                                        </button>
                                    @endif
                                </div>
                                <div class="pp-id-hint">Front + Back · click to enlarge</div>
                            @else
                                <span class="badge rounded-pill px-2 py-1" style="background:var(--color-danger-soft);color:var(--color-danger-text);font-size:0.75rem;">
                                    <i class="bi bi-exclamation-triangle me-1"></i>No ID on file
                                </span>
                            @endif
                        </div>
                        <div class="pp-decision">
                            <div class="pp-foot-title"><i class="bi bi-check2-square"></i> Decision</div>
                            <div class="pp-decision-btns">
                                <form action="{{ route('rhu.approve-patient', $woman->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success w-100"
                                        onclick="return confirm('Approve and activate {{ $woman->name }}?')">
                                        <i class="bi bi-check-lg me-1"></i> Approve
                                    </button>
                                </form>
                                <button class="btn btn-sm btn-outline-danger w-100" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#reject-{{ $woman->id }}">
                                    <i class="bi bi-x-lg me-1"></i> Reject
                                </button>
                            </div>
                            <div class="collapse" id="reject-{{ $woman->id }}">
                                <form action="{{ route('rhu.reject-patient', $woman->id) }}" method="POST" class="p-3 border rounded-3 text-start" style="background:var(--color-danger-soft); border-color:var(--color-danger-soft);">
                                    @csrf
                                    <label class="form-label fw-bold text-danger small mb-1">Reason for Rejection</label>
                                    <textarea name="reason" class="form-control form-control-sm mb-2"
                                        placeholder="State clinical or identity reason..." rows="2"
                                        style="border-radius:8px; border:1px solid var(--color-danger-soft);" required></textarea>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="collapse" data-bs-target="#reject-{{ $woman->id }}">Cancel</button>
                                        <button type="submit" class="btn btn-sm btn-danger" style="border-radius:8px; font-weight:700;">
                                            Confirm Rejection
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    @if(!empty($duplicates[$woman->id] ?? []))
                    <div class="pp-dup-wrap">
                        <div class="collapse" id="dup-{{ $woman->id }}">
                            <div class="p-3">
                                <div class="fw-bold mb-2" style="color:var(--color-primary-text); font-size:0.85rem;">
                                    <i class="bi bi-people-fill me-1"></i> Possible existing profiles for {{ $woman->name }} — link instead of creating a duplicate?
                                </div>
                                @foreach($duplicates[$woman->id] as $match)
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 border rounded-3 p-2 mb-2" style="background:var(--color-surface);">
                                        <div style="font-size:0.85rem;">
                                            <strong>{{ $match['name'] }}</strong>
                                            <span class="badge bg-light text-dark border ms-1">{{ $match['kind'] === 'walk_in' ? 'BHW field record' : 'Portal account (' . $match['status'] . ')' }}</span>
                                            <span class="badge ms-1" style="background:var(--color-primary-soft);color:var(--color-primary-text);">score {{ $match['score'] }} · {{ str_replace('_', ' ', $match['match_type']) }}</span>
                                            <div class="text-muted small">
                                                {{ $match['barangay'] ?? '—' }}
                                                @if($match['birthdate']) · born {{ $match['birthdate'] }} @endif
                                                @if($match['contact']) · {{ $match['contact'] }} @endif
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            @if($match['kind'] === 'walk_in')
                                                <form action="{{ route('rhu.link-duplicate', $woman->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="walk_in_patient_id" value="{{ $match['id'] }}">
                                                    <button type="submit" class="btn btn-sm btn-primary" style="border-radius:8px; font-weight:700;"
                                                        onclick="return confirm('Link this field profile to {{ $woman->name }}? Histories merge, nothing is deleted.')">
                                                        <i class="bi bi-link-45deg me-1"></i> Link profiles
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('rhu.dismiss-duplicate', $woman->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="kind" value="{{ $match['kind'] }}">
                                                <input type="hidden" name="match_id" value="{{ $match['id'] }}">
                                                <input type="hidden" name="match_type" value="{{ $match['match_type'] }}">
                                                <input type="hidden" name="score" value="{{ $match['score'] }}">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                                                    Not the same person
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </article>
                @endforeach
            </div>
        </div>
        <div class="p-4 border-top">
            <div class="d-flex justify-content-center">
                {{ $pending->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @else
        <div class="card-body p-5">
            <div class="text-center py-5">
                <div style="width:64px;height:64px;border-radius:18px;background:var(--color-success-soft);color:var(--color-success-text);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-person-check-fill" style="font-size:1.75rem;"></i>
                </div>
                <h5 class="fw-bold mb-1" style="color:var(--color-text);">No Pending Registrations</h5>
                <p class="text-muted mb-4" style="font-size:0.9rem; max-width:400px; margin:0 auto;">
                    @if(request('search'))
                        No pending women matched your search criteria.
                    @else
                        All woman registrations have been reviewed and validated.
                    @endif
                </p>
                @if(request('search'))
                    <a href="{{ route('rhu.pending-patients') }}" class="btn btn-sm btn-hero-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Clear Search
                    </a>
                @else
                    <a href="{{ route('rhu.patients.create') }}" class="btn btn-sm btn-primary" style="border-radius:10px; font-weight:700;">
                        <i class="bi bi-person-plus-fill me-1"></i> Register New Woman
                    </a>
                @endif
            </div>
        </div>
    @endif
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
</script>
@endpush

@endsection
