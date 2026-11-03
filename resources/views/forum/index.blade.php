@php
    $forumUser = auth()->user();
    // Read-only UI for both limited tiers (provisional + view-only):
    // composers, likes and comment forms are replaced with an unlock
    // notice (routes enforce it too).
    $isLimited = (bool) ($forumUser?->hasLimitedAccess());
    $forumLayout = match($forumUser?->role) {
        'bhw_president' => 'bhw-president.layout',
        'midwife' => 'midwife.layout',
        'bhw' => 'bhw.layout',
        'rhu' => 'rhu.layout',
        'cho' => 'cho.layout',
        default => 'user.layout',
    };
    $forumSection = match($forumUser?->role) {
        'bhw_president' => 'bhw-president-content',
        'midwife' => 'midwife-content',
        'bhw' => 'bhw-content',
        'rhu' => 'rhu-content',
        'cho' => 'cho-content',
        default => 'user-content',
    };
@endphp

@extends($forumLayout)

@section('title', 'Community Forum - ReproCare')

@push('styles')
<style>
    /* === REPROCARE COMMUNITY FORUM === */
    .forum-container {
        max-width:1200px;
        margin:0 auto;
        padding-bottom:1rem;
    }

    /* ── Hero Banner (one slim line: what this is, and where to write) ── */
    .forum-hero {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        border-radius:16px;
        padding:0.7rem 0.95rem;
        margin-bottom:0.85rem;
        color:var(--color-text);
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:0.9rem;
        flex-wrap:wrap;
        box-shadow:var(--wp-shadow-sm);
    }
    [data-theme="light"] .forum-hero {
        background:var(--color-surface); background-color:var(--color-surface);
        color:var(--color-text);
        box-shadow:var(--wp-shadow-sm);
    }
    .forum-hero-title {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:1.05rem;
        font-weight:800;
        margin:0;
        display:flex;
        align-items:center;
        gap:0.5rem;
        color:var(--color-text);
        letter-spacing:-0.01em;
    }
    .forum-hero-sub {
        font-size:0.78rem;
        color:var(--color-text-muted);
        margin:0.1rem 0 0;
        max-width:76ch;
        line-height:1.35;
        opacity:1;
    }
    .forum-composer-btn { background:var(--color-surface-strong) !important; background-color:var(--color-surface-strong) !important; border:none !important; border-radius:999px !important; padding:0.38rem 0.85rem !important; font-size:0.78rem !important; font-weight:800 !important; color:var(--color-on-solid) !important; box-shadow:0 6px 14px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent); }
    .forum-composer-btn:hover { background:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; transform:translateY(-1px); }

    /* ── Quick Share / Composer Card (Makes sharing effortless) ── */
    .quick-composer-card {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        border-radius:16px;
        padding:0.65rem 0.8rem;
        box-shadow:var(--wp-shadow-sm);
        margin-bottom:0.85rem;
        transition:border-color 0.2s;
    }
    .quick-composer-card:hover {
        border:none;
    }
    .composer-user-avatar {
        width:32px;
        height:32px;
        border-radius:50%;
        object-fit:cover;
        border:none;
        flex-shrink:0;
    }
    .quick-composer-btn {
        flex:1;
        background:var(--color-surface-soft); background-color:var(--color-surface-soft);
        border:none;
        border-radius:999px;
        padding:0.75rem 1.25rem;
        text-align:left;
        color:var(--color-text-muted);
        font-size:0.9rem;
        font-weight:500;
        cursor:pointer;
        transition:all 0.2s;
    }
    .quick-composer-btn:hover {
        background:var(--color-border);
        border:none;
        color:var(--color-text);
        box-shadow:none;
    }
    .composer-form {
        background:var(--color-surface);
        border-radius:16px;
        padding-top:0.5rem;
    }
    .composer-textarea-full {
        width:100%;
        border:none;
        border-radius:14px;
        padding:0.9rem 1rem;
        font-size:0.92rem;
        color:var(--color-text);
        background:var(--color-surface-soft); background-color:var(--color-surface-soft);
        outline:none;
        resize:vertical;
        font-family:inherit;
        line-height:1.5;
        transition:border-color 0.2s;
    }
    .composer-textarea-full:focus {
        border:none;
        box-shadow:0 0 0 3px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 15%, transparent);
        background:var(--color-surface); background-color:var(--color-surface);
    }
    .composer-bottom-bar {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:0.6rem;
        margin-top:0.5rem;
        flex-wrap:wrap;
    }
    .btn-attach-photo {
        display:inline-flex;
        align-items:center;
        gap:0.35rem;
        padding:0.3rem 0.7rem;
        border-radius:999px;
        border:none;
        background:var(--color-surface-soft); background-color:var(--color-surface-soft);
        color:var(--color-text);
        font-size:0.76rem;
        font-weight:700;
        cursor:pointer;
        transition:all 0.2s;
    }
    .btn-attach-photo:hover {
        background:var(--color-border);
        color:var(--color-text);
        border:none;
    }
    /* The chosen photo, shown as a real thumbnail so the reader can see what
       they are about to post instead of a bare filename. */
    .composer-preview {
        display:flex;
        align-items:center;
        gap:0.5rem;
        padding:0.3rem 0.5rem;
        border-radius:10px;
        background:var(--color-surface-soft);
        max-width:260px;
        min-width:0;
    }
    .composer-preview img {
        width:38px;
        height:38px;
        object-fit:cover;
        border-radius:8px;
        flex-shrink:0;
        background:var(--color-bg);
    }
    .composer-preview-meta {
        display:flex;
        flex-direction:column;
        min-width:0;
        line-height:1.25;
    }
    .composer-preview-meta strong {
        font-size:0.76rem;
        font-weight:700;
        color:var(--color-text);
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
        max-width:150px;
    }
    .composer-preview-meta small { font-size:0.68rem; color:var(--color-text-muted); }
    .composer-preview button {
        background:none;
        border:none;
        color:var(--color-text-muted);
        padding:0;
        cursor:pointer;
        display:flex;
        align-items:center;
        font-size:1rem;
        flex-shrink:0;
    }
    .composer-preview button:hover { color:var(--color-danger-text); }
    .composer-error {
        width:100%;
        margin:0.35rem 0 0;
        font-size:0.76rem;
        font-weight:700;
        color:var(--color-danger-text);
    }
    .composer-error:empty { display:none; }

    /* ── Inline composer: pill at rest, expands in place on tap ── */
    .quick-inline-form { padding-top:0; }
    .quick-inline-input {
        border-radius:999px !important;
        height:auto;
        min-height:0;
        resize:none;
        overflow:hidden;
        white-space:nowrap !important;
        text-overflow:ellipsis !important;
        padding:0.42rem 0.85rem !important;
        font-size:0.84rem;
        line-height:1.4 !important;
    }
    /* Expansion is a class the script owns, not :focus-within. The file picker
       takes focus away, so a focus-only rule collapsed the bar with the photo
       already chosen — the Post button vanished and the attach looked broken.
       A draft (typed text or an attached file) keeps the composer open. */
    .quick-composer-card.is-open .quick-inline-input {
        border-radius:14px !important;
        min-height:72px;
        white-space:normal !important;
        line-height:1.5 !important;
    }
    .quick-inline-bar { display:none; }
    .quick-composer-card.is-open .quick-inline-bar { display:flex; }

    /* ── Post Feed Cards (Makes reading enjoyable) ── */
    .forum-post-card {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        border-radius:16px;
        padding:0.85rem 0.95rem;
        margin-bottom:0.6rem;
        box-shadow:var(--wp-shadow-sm);
        transition:all 0.25s ease;
    }
    .forum-post-card:hover {
        border:none;
        box-shadow:var(--wp-shadow-md);
    }
    /* ── Infinite scroll tail ── */
    .forum-feed-sentinel { width:100%; height:1px; }
    .forum-feed-status { display:flex; align-items:center; justify-content:center; gap:.55rem; padding:.55rem 0 .8rem; font-size:.78rem; font-weight:700; color:var(--color-text-muted); text-align:center; }
    .forum-feed-status.is-hidden { display:none; }
    .forum-feed-spinner { width:15px; height:15px; border-radius:50%; border:2px solid var(--color-border); border-top-color:var(--color-secondary-text); animation:forum-spin .7s linear infinite; flex-shrink:0; }
    .forum-feed-status.is-done .forum-feed-spinner { display:none; }
    .forum-feed-status.is-error { color:var(--color-danger-text); }
    .forum-feed-status.is-error .forum-feed-spinner { display:none; }
    .forum-feed-retry { border:1px solid var(--color-danger-text); background:transparent; color:var(--color-danger-text); border-radius:999px; padding:.2rem .75rem; font-size:.75rem; font-weight:700; cursor:pointer; }
    @keyframes forum-spin { to { transform:rotate(360deg); } }
    /* ── Resume strip: what the reader already scrolled past, one line away ── */
    .forum-post-card.is-read { display:none; }
    .forum-read-strip { display:flex; align-items:center; justify-content:space-between; gap:.6rem; margin-bottom:.7rem; padding:.45rem .7rem .45rem .85rem; border:1px dashed var(--color-border); border-radius:12px; background:var(--color-surface-soft); color:var(--color-text-muted); font-size:.78rem; font-weight:700; }
    .forum-read-strip button { border:1px solid var(--color-border); background:transparent; color:var(--color-text); border-radius:999px; padding:.18rem .7rem; font-size:.73rem; font-weight:800; cursor:pointer; }
    .post-header {
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:0.6rem;
        margin-bottom:0.5rem;
    }
    .author-lockup {
        display:flex;
        align-items:center;
        gap:0.6rem;
    }
    .author-avatar {
        width:34px;
        height:34px;
        border-radius:50%;
        object-fit:cover;
        border:none;
        flex-shrink:0;
    }
    .author-name {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:0.86rem;
        font-weight:800;
        color:var(--color-text);
        margin:0;
        display:flex;
        align-items:center;
        gap:0.4rem;
        flex-wrap:wrap;
    }
    .author-time {
        font-size:0.72rem;
        color:var(--color-text-muted);
        display:flex;
        align-items:center;
        gap:0.3rem;
        margin-top:0.05rem;
    }
    .role-verified-badge {
        font-size:0.64rem;
        font-weight:800;
        padding:0.18rem 0.6rem;
        border-radius:999px;
        text-transform:uppercase;
        letter-spacing:0.4px;
        display:inline-flex;
        align-items:center;
        gap:0.25rem;
        border:none;
    }
    .role-verified-badge.midwife { background:var(--color-success-soft); background-color:var(--color-success-soft); color:var(--color-success-text); }
    .role-verified-badge.bhw { background:var(--color-peach-soft); background-color:var(--color-peach-soft); color:var(--color-warning-text); }
    .role-verified-badge.president { background:var(--color-primary-soft); background-color:var(--color-primary-soft); color:var(--color-primary-text); }
    .role-verified-badge.member { background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:var(--color-secondary-text); }

    .post-text-body {
        font-size:0.86rem;
        line-height:1.5;
        color:var(--color-text);
        white-space:pre-wrap;
        margin-bottom:0.55rem;
    }

    .post-media-frame {
        border-radius:12px;
        overflow:hidden;
        margin-bottom:0.6rem;
        border:none;
        max-height:280px;
    }
    .post-media-frame img {
        width:100%;
        height:auto;
        max-height:280px;
        object-fit:cover;
        display:block;
    }

    /* Post Interaction Bar */
    .post-engagement-bar {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:0.5rem;
        border:none;
        padding-top:0.35rem;
        flex-wrap:wrap;
    }
    .engagement-actions {
        display:flex;
        align-items:center;
        gap:0.4rem;
    }
    .btn-engage {
        display:inline-flex;
        align-items:center;
        gap:0.35rem;
        padding:0.28rem 0.65rem;
        border-radius:999px;
        border:none;
        background:var(--color-surface-soft); background-color:var(--color-surface-soft);
        color:var(--color-text);
        font-size:0.75rem;
        font-weight:700;
        cursor:pointer;
        text-decoration:none;
        transition:all 0.2s;
    }
    .btn-engage:hover {
        background:var(--color-border);
        color:var(--color-text);
        border:none;
    }
    .btn-engage.liked {
        background:var(--color-secondary-soft); background-color:var(--color-secondary-soft);
        border:none;
        color:var(--color-secondary-text);
    }

    /* Comment Section */
    .comments-tray {
        margin-top:0.6rem;
        padding:0.7rem;
        background:var(--color-bg); background-color:var(--color-bg);
        border-radius:12px;
        border:none;
    }
    .comment-item {
        display:flex;
        /* flex-start also blocks the default align-items:stretch, which would
           otherwise elongate a height-less avatar into an ellipse. */
        align-items:flex-start;
        gap:0.55rem;
        margin-bottom:0.6rem;
    }
    .comment-avatar {
        width:28px;
        height:28px;
        border-radius:50%;
        object-fit:cover;
        flex-shrink:0;
    }
    .comment-bubble {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        border-radius:12px;
        padding:0.45rem 0.65rem;
        flex:1;
    }
    .comment-author {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:0.78rem;
        font-weight:700;
        color:var(--color-text);
        margin-bottom:0.1rem;
    }
    .comment-text {
        font-size:0.8rem;
        color:var(--color-text);
        line-height:1.45;
        margin:0;
    }

    /* ── Right Column Sidebar ── */
    .forum-side-card {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        border-radius:16px;
        padding:0.85rem 0.95rem;
        margin-bottom:0.85rem;
        box-shadow:var(--wp-shadow-sm);
    }
    .side-card-title {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:0.92rem;
        font-weight:800;
        color:var(--color-text);
        margin:0 0 0.55rem;
        display:flex;
        align-items:center;
        gap:0.5rem;
    }
    .guide-item {
        display:flex;
        align-items:flex-start;
        gap:0.5rem;
        margin-bottom:0.5rem;
        font-size:0.78rem;
        color:var(--color-text-muted);
        line-height:1.45;
    }
    .guide-item i {
        color:var(--color-primary-text);
        font-size:0.95rem;
        flex-shrink:0;
        margin-top:2px;
    }
    /* The guidelines strip sits inside the feed column, so it takes the same
       rhythm as the post cards instead of the sidebar's heavier block. */
    .forum-guide-strip {
        padding:0.6rem 0.85rem;
        margin-bottom:0.6rem;
    }
    .forum-guide-strip .side-card-title { margin:0; font-size:0.84rem; }

    @media (max-width: 575.98px) {
        /* Phones: compact hero — title + subtitle only. */
        .forum-hero {
            border-radius:14px !important;
            padding:0.8rem 0.85rem !important;
            margin-bottom:0.8rem !important;
            gap:0 !important;
            flex-direction:column !important;
            align-items:flex-start !important;
        }
        .forum-hero-title { font-size:1rem !important; gap:0.4rem !important; margin-bottom:0.15rem !important; }
        .forum-hero-sub { font-size:0.74rem !important; line-height:1.4 !important; }
        .forum-composer-btn { display:none !important; }

        /* Phones: tighter composer + post cards, smaller text throughout. */
        .forum-feed-title { font-size:0.95rem !important; }
        .quick-composer-card { padding:0.8rem !important; border-radius:14px !important; margin-bottom:0.9rem !important; }
        .composer-user-avatar { width:34px !important; height:34px !important; }
        .quick-composer-btn { font-size:0.78rem !important; padding:0.6rem 0.9rem !important; }
        .composer-textarea-full { font-size:0.82rem !important; padding:0.7rem 0.8rem !important; }
        .forum-post-card { padding:0.9rem !important; border-radius:14px !important; margin-bottom:0.9rem !important; }
        .author-avatar { width:32px !important; height:32px !important; }
        .author-name { font-size:0.78rem !important; }
        .author-time { font-size:0.64rem !important; }
        .author-lockup { gap:0.6rem !important; }
        .role-verified-badge { font-size:0.58rem !important; }
        .post-text-body { font-size:0.82rem !important; line-height:1.55 !important; }
        .post-media-frame { max-height:200px !important; border-radius:10px !important; }
        .post-media-frame img { max-height:200px !important; }

        /* Phones: engagement buttons wrap small. */
        .post-engagement-bar { flex-wrap:wrap !important; gap:0.45rem !important; }
        .btn-engage { font-size:0.7rem !important; padding:0.35rem 0.65rem !important; }
        .comments-tray { padding:0.75rem !important; }
        .comment-avatar { width:28px !important; height:28px !important; }
        .comment-author { font-size:0.78rem !important; }
        .comment-text { font-size:0.78rem !important; }
        .comment-bubble { padding:0.55rem 0.7rem !important; }
        .btn-attach-photo { font-size:0.76rem !important; padding:0.45rem 0.8rem !important; }

        /* Phones: sidebar cards go full-width below the feed, compact. */
        .forum-side-card { padding:0.9rem !important; border-radius:14px !important; }
        .side-card-title { font-size:0.88rem !important; }
        .guide-item { font-size:0.76rem !important; }

        /* Phones: center the All Discussions / My Posts button text. */
        .forum-feed-head .btn {
            text-align:center !important;
            display:inline-flex !important;
            align-items:center !important;
            justify-content:center !important;
        }
    }
</style>
@endpush

@section($forumSection)
<div class="forum-container">

    {{-- Hero Section --}}
    <div class="forum-hero fade-in-card">
        <div>
            <h1 class="forum-hero-title">
                Community Forum
            </h1>
            <p class="forum-hero-sub">
                Mothers and health workers of San Carlos City exchange advice, support, and experiences.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($isLimited)
                <span class="badge rounded-pill px-3 py-2" style="border:1px solid var(--color-border); background:var(--color-surface-soft); color:var(--color-text-muted); font-size:0.72rem;" title="Posting unlocks after RHU approval of your account">
                    <i class="bi bi-lock-fill me-1"></i> Read-only
                </span>
            @else
            <a href="{{ route('forum.create') }}" class="btn forum-composer-btn">
                <i class="bi bi-plus-circle-fill me-1"></i> Full Composer
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius:14px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- One full-width column: the timeline scrolls on its own, no sidebar to
         compete with it. --}}
    <div class="forum-feed-column">

            {{-- Quick Post Creation Card (type directly in the pill — expands in place) --}}
            @if($isLimited)
            <div class="quick-composer-card fade-in-card">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px; height:32px; border-radius:50%; background:var(--color-surface-soft); color:var(--color-text-muted); font-size:0.9rem;" aria-hidden="true">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <div style="min-width:0;">
                        <div style="font-weight:800; font-size:0.82rem; color:var(--color-text);">Posting unlocks after RHU approval</div>
                        <div style="font-size:0.75rem; color:var(--color-text-muted);">You're browsing with limited access — read and explore meanwhile.</div>
                    </div>
                </div>
            </div>
            @else
            <div class="quick-composer-card fade-in-card">
                <form action="{{ route('forum.store') }}" method="POST" enctype="multipart/form-data" class="composer-form quick-inline-form">
                    @csrf
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $forumUser->profile_image_url }}"
                             alt="{{ $forumUser->name }}"
                             class="composer-user-avatar"
                             onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                        <textarea id="quickInlineInput"
                                  name="content"
                                  class="composer-textarea-full quick-inline-input"
                                  placeholder="Share a pregnancy tip, ask a midwife, or post an update..."
                                  rows="1"
                                  required>{{ old('content') }}</textarea>
                    </div>

                    <div class="composer-bottom-bar quick-inline-bar">
                        <label class="btn-attach-photo" title="Attach a photo">
                            <i class="bi bi-image text-success"></i> Add Photo
                            <input type="file" name="post_image" id="quickPostImageInput" accept="image/jpeg,image/png,image/webp" style="display:none;">
                        </label>
                        <div id="quickImgPreview" class="composer-preview" hidden>
                            <img id="quickImgThumb" alt="Selected photo preview">
                            <div class="composer-preview-meta">
                                <strong id="quickImgName">photo.jpg</strong>
                                <small id="quickImgSize"></small>
                            </div>
                            <button type="button" id="quickImgClear" aria-label="Remove photo">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary" style="border-radius:999px;padding:0.3rem 0.95rem;font-size:0.8rem;font-weight:800;">
                            <i class="bi bi-send-fill me-1"></i> Post
                        </button>
                    </div>
                    {{-- Outside the bar on purpose: a rejected photo bounces the
                         reader back with the composer collapsed, and an error
                         hidden inside it would read as another silent failure. --}}
                    @if ($errors->any())
                        <p class="composer-error" id="quickComposerError" role="alert">
                            {{ $errors->first('post_image', 'Post photo: :message') ?: $errors->first('content', ':message') }}
                        </p>
                    @else
                        <p class="composer-error" id="quickComposerError" role="alert" hidden></p>
                    @endif
                </form>
            </div>
            @endif

            {{-- Community values live on one collapsible line now that the feed
                 owns the whole width; collapsed, it costs a single row. --}}
            <div class="forum-side-card forum-guide-strip fade-in-card">
                <div class="d-flex justify-content-between align-items-center" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#guidelinesCollapse" aria-expanded="false" aria-controls="guidelinesCollapse">
                    <h4 class="side-card-title" style="margin:0; color:var(--color-text);">
                        Community Guidelines
                    </h4>
                    <i class="bi bi-chevron-down" style="font-size:0.85rem; color:var(--color-text-muted);"></i>
                </div>
                <div class="collapse" id="guidelinesCollapse">
                    <hr style="margin:0.75rem 0; border:none; border-top:1px solid var(--color-border);">
                    <div class="guide-item">
                        <i class="bi bi-check-circle-fill" style="color:var(--color-success-text);"></i>
                        <span><strong>Supportive &amp; Respectful:</strong> Encourage every mother's journey without judgment.</span>
                    </div>
                    <div class="guide-item">
                        <i class="bi bi-lock-fill" style="color:var(--color-secondary-text);"></i>
                        <span><strong>Privacy First:</strong> Never share sensitive clinical IDs or private home addresses.</span>
                    </div>
                    <div class="guide-item">
                        <i class="bi bi-hospital-fill" style="color:var(--color-peach-text);"></i>
                        <span><strong>Seek Medical Care:</strong> Direct urgent labor or emergency complications to your RHU midwife.</span>
                    </div>
                </div>
            </div>

            {{-- Feed Filters (All Posts vs My Posts) --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2 forum-feed-head">
                <h3 class="forum-feed-title" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:0.95rem;font-weight:800;color:var(--color-text);margin:0;">
                    Community Feed
                </h3>
                <div class="d-flex gap-1 p-1 rounded-pill" style="background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:none;">
                    <a href="{{ route('forum.index') }}"
                       class="btn btn-sm"
                       style="border:none; border-radius:999px; font-weight:800; font-size:0.75rem; padding:0.26rem 0.8rem; {{ !$filter ? 'background:var(--color-text) !important; color:var(--color-bg) !important;' : 'background:transparent; color:var(--color-text);' }}">
                        All Discussions
                    </a>
                    <a href="{{ route('forum.index', ['filter' => 'my-posts']) }}"
                       class="btn btn-sm"
                       style="border:none; border-radius:999px; font-weight:800; font-size:0.75rem; padding:0.26rem 0.8rem; {{ $filter === 'my-posts' ? 'background:var(--color-text) !important; color:var(--color-bg) !important;' : 'background:transparent; color:var(--color-text);' }}">
                        My Posts
                    </a>
                </div>
            </div>

            {{-- Posts Feed --}}
            <div id="forumFeed" class="forum-feed-stack">
            @forelse($posts as $post)
                @include('forum.post-card', ['post' => $post, 'isLimited' => $isLimited, 'likedIds' => $likedIds])
            @empty
                <div class="card p-4 text-center fade-in-card" style="border:none; border-radius:16px; box-shadow:var(--wp-shadow-sm);">
                    <i class="bi bi-chat-heart" style="font-size:2.2rem; color:var(--color-border); margin-bottom:0.6rem;"></i>
                    <h4 style="font-family:'Plus Jakarta Sans',sans-serif; font-size:1rem; font-weight:800; color:var(--color-text);">No discussions yet</h4>
                    <p style="color:var(--color-text-muted); font-size:0.84rem; max-width:440px; margin:0 auto 1rem;">
                        {{ $filter === 'my-posts' ? "You haven't posted in the community yet." : "The community feed is peaceful. Be the first mother or healthcare worker to start a conversation!" }}
                    </p>
                    @if(!$isLimited)
                    <button type="button" class="btn mx-auto forum-composer-btn" onclick="focusQuickComposer()">
                        <i class="bi bi-plus-circle-fill me-1"></i> Start First Discussion
                    </button>
                    @endif
                </div>
            @endforelse
            </div>

            {{-- Infinite scroll: the observer watches this sentinel, and the
                 status line is the only feedback besides freshly added cards.
                 An empty feed has nothing to scroll onto, so the tail is skipped. --}}
            @if($posts->isNotEmpty())
            <div id="forumFeedSentinel" class="forum-feed-sentinel"
                 data-endpoint="{{ route('forum.feed') }}"
                 data-cursor="{{ $posts->nextCursor()?->encode() }}"
                 data-has-more="{{ $posts->hasMorePages() ? 1 : 0 }}"
                 data-user="{{ (int) ($forumUser->id ?? 0) }}"
                 data-filter="{{ $filter ?? '' }}" data-search="{{ $search ?? '' }}"></div>
            <div id="forumFeedStatus" class="forum-feed-status{{ $posts->hasMorePages() ? '' : ' is-done' }}" role="status" aria-live="polite">
                <span class="forum-feed-spinner" aria-hidden="true"></span>
                <span class="forum-feed-status-text">{{ $posts->hasMorePages() ? 'Loading more discussions...' : 'You are all caught up.' }}</span>
            </div>
            @endif
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/forum-photo.js') }}?v={{ filemtime(public_path('js/forum-photo.js')) }}" defer></script>
<script>
function focusQuickComposer() {
    var q = document.getElementById('quickInlineInput');
    if (!q) return;
    q.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(function () { q.focus({ preventScroll: true }); }, 350);
}

/* Inline composer. The bar used to hang on .quick-composer-card:focus-within,
   and opening the OS file picker drops focus — so the card collapsed with the
   photo already chosen, taking the Post button with it and making "Add Photo"
   look broken. The script owns the open state instead and keeps it while there
   is typed text or an attached file. */
(function () {
    var card = document.querySelector('.quick-composer-card');
    var ta = document.getElementById('quickInlineInput');
    var input = document.getElementById('quickPostImageInput');
    if (!card || !ta || !input) return;

    var preview = document.getElementById('quickImgPreview');
    var thumb = document.getElementById('quickImgThumb');
    var nameEl = document.getElementById('quickImgName');
    var sizeEl = document.getElementById('quickImgSize');
    var clearBtn = document.getElementById('quickImgClear');
    var errorEl = document.getElementById('quickComposerError');
    var form = ta.closest('form');
    var MAX_BYTES = 5 * 1024 * 1024;
    var ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];
    var thumbUrl = null;

    function isOpen() {
        return card.contains(document.activeElement) || ta.value.trim() !== '' || input.files.length > 0;
    }

    function syncOpen() {
        card.classList.toggle('is-open', isOpen());
        if (!isOpen()) ta.style.height = '';
    }

    function grow() {
        ta.style.height = 'auto';
        ta.style.height = ta.scrollHeight + 'px';
    }

    function showError(message) {
        if (!errorEl) return;
        errorEl.textContent = message;
        errorEl.hidden = message === '';
    }

    function clearImage() {
        input.value = '';
        if (thumbUrl) {
            URL.revokeObjectURL(thumbUrl);
            thumbUrl = null;
        }
        preview.hidden = true;
    }

    // Returns true when the picked file can actually be uploaded. The server
    // accepts the same list, so a rejection here is the reader's only warning.
    function showImage() {
        var file = input.files[0];
        if (!file) {
            preview.hidden = true;
            return true;
        }
        if (input.dataset.photoPending) {
            // forum-photo.js is shrinking this photo; a second change event
            // arrives with the smaller file and runs the real checks.
            if (thumbUrl) URL.revokeObjectURL(thumbUrl);
            thumbUrl = URL.createObjectURL(file);
            thumb.src = thumbUrl;
            nameEl.textContent = file.name;
            sizeEl.textContent = 'Optimising...';
            preview.hidden = false;
            showError('');
            return true;
        }
        if (ALLOWED.indexOf(file.type) === -1) {
            clearImage();
            showError('That file type is not supported yet — pick a JPG, PNG or WebP photo.');
            return false;
        }
        if (file.size > MAX_BYTES) {
            clearImage();
            showError('That photo could not be shrunk enough to post. Choose a smaller one.');
            return false;
        }
        if (thumbUrl) URL.revokeObjectURL(thumbUrl);
        thumbUrl = URL.createObjectURL(file);
        thumb.src = thumbUrl;
        nameEl.textContent = file.name;
        sizeEl.textContent = file.size >= 1024 * 1024
            ? (file.size / (1024 * 1024)).toFixed(1) + ' MB'
            : Math.max(1, Math.round(file.size / 1024)) + ' KB';
        preview.hidden = false;
        showError('');
        return true;
    }

    ta.addEventListener('input', function () {
        grow();
        syncOpen();
    });
    input.addEventListener('change', function () {
        showImage();
        syncOpen();
    });
    clearBtn.addEventListener('click', function () {
        clearImage();
        syncOpen();
    });
    document.addEventListener('focusin', syncOpen);
    // The blur that follows a file-dialog handoff lands before the picker's
    // own focus change, so the state is re-read on the next tick.
    document.addEventListener('focusout', function () {
        setTimeout(syncOpen, 0);
    });
    form.addEventListener('submit', function (event) {
        if (!showImage()) event.preventDefault();
    });

    // A post that bounced back on validation keeps its draft open.
    if (ta.value.trim() !== '') {
        card.classList.add('is-open');
        grow();
    }
})();

function sharePost(url) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Discussion link copied to clipboard!');
        });
    } else {
        prompt('Copy link to share:', url);
    }
}

/* ── Infinite scroll ──
   The feed is cursor-paginated on (created_at, id), so a page never shifts
   the way an offset page does when a new post lands. The seen set is the
   second half of that promise: it also covers the reader scrolling back up
   and re-triggering the sentinel. */
(function () {
    var feed = document.getElementById('forumFeed');
    var sentinel = document.getElementById('forumFeedSentinel');
    var status = document.getElementById('forumFeedStatus');
    if (!feed || !sentinel || !status) return;

    var label = status.querySelector('.forum-feed-status-text');
    var endpoint = sentinel.getAttribute('data-endpoint');
    var nextCursor = sentinel.getAttribute('data-cursor') || '';
    var filter = sentinel.getAttribute('data-filter') || '';
    var search = sentinel.getAttribute('data-search') || '';

    var seen = new Set();
    feed.querySelectorAll('[data-post-id]').forEach(function (card) {
        seen.add(card.getAttribute('data-post-id'));
    });

    /* Resume, don't re-read. The reader who scrolled down to post 17 should
       open the feed on post 16 next visit instead of meeting the same ten
       posts again; a search is a different question, so it never resumes. */
    var markerKey = 'forum-read:' + (sentinel.getAttribute('data-user') || '0') + ':' + (filter || 'all');
    var resumable = search === '';
    var readUpTo = null;
    var skippedPages = 0;

    function cardList() {
        return Array.prototype.slice.call(feed.querySelectorAll('.forum-post-card'));
    }

    function readMarker() {
        if (!resumable) return null;
        try { return JSON.parse(window.localStorage.getItem(markerKey) || 'null'); }
        catch (e) { return null; }
    }

    /* The feed runs newest first, so a post at or above the stamp the reader
       stopped on is one they already passed. Comparing timestamps rather than
       page positions keeps pages that arrive after the resume in the same
       collapsed region instead of slipping past it. */
    function isRead(card) {
        return readUpTo !== null && Number(card.getAttribute('data-created')) >= readUpTo;
    }

    function readStrip() {
        var existing = feed.querySelector('.forum-read-strip');
        if (existing) return existing;
        var strip = document.createElement('div');
        strip.className = 'forum-read-strip';
        var text = document.createElement('span');
        var toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.textContent = 'Show';
        toggle.addEventListener('click', function () {
            readUpTo = null;
            cardList().forEach(function (card) { card.classList.remove('is-read'); });
            strip.remove();
        });
        strip.appendChild(text);
        strip.appendChild(toggle);
        return strip;
    }

    /* Returns true when every loaded post is already-read, which is the signal
       to pull the next page until the reader lands on something new. */
    function syncReadState() {
        if (readUpTo === null) return false;
        var firstUnread = null;
        var readCount = 0;
        cardList().forEach(function (card) {
            if (isRead(card)) {
                card.classList.add('is-read');
                readCount++;
            } else if (!firstUnread) {
                firstUnread = card;
            }
        });
        var strip = readStrip();
        if (readCount === 0) {
            strip.remove();
            return false;
        }
        strip.querySelector('span').textContent = readCount +
            (readCount === 1 ? ' post you have already read' : ' posts you have already read');
        feed.insertBefore(strip, firstUnread);
        return firstUnread === null;
    }

    function revealEverything() {
        readUpTo = null;
        cardList().forEach(function (card) { card.classList.remove('is-read'); });
        var strip = feed.querySelector('.forum-read-strip');
        if (strip) strip.remove();
    }

    /* Eight pages covers the whole curated feed, so a reader who has seen
       everything can always reach the "all caught up" state instead of being
       left with a collapsed feed and nothing under it. */
    function keepSkipping() {
        if (skippedPages < 8 && !loading && !done) {
            skippedPages++;
            load();
        }
    }

    function resume() {
        var marker = readMarker();
        if (!marker) return;
        readUpTo = Number(marker.created);
        if (!isFinite(readUpTo)) {
            readUpTo = null;
            return;
        }
        settleResume();
    }

    function settleResume() {
        if (!syncReadState()) return;
        if (done) {
            // Nothing unseen exists anywhere: an all-hidden feed would read as
            // missing posts, so leave the posts where the reader expects them.
            revealEverything();
            return;
        }
        keepSkipping();
    }

    var deepest = null;
    var tracker = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var index = cardList().indexOf(entry.target);
            if (index > -1 && (!deepest || index > deepest.index)) {
                deepest = {index: index, id: entry.target.getAttribute('data-post-id'), created: entry.target.getAttribute('data-created')};
            }
        });
    }, { rootMargin: '-15% 0px -15% 0px' });

    function track(card) { tracker.observe(card); }
    cardList().forEach(track);
    window.addEventListener('pagehide', function () {
        if (deepest) {
            try { window.localStorage.setItem(markerKey, JSON.stringify({id: deepest.id, created: deepest.created})); }
            catch (e) { /* a private-mode block only costs the resume, not the feed */ }
        }
    });

    var loading = false;
    var done = sentinel.getAttribute('data-has-more') !== '1';
    var retry = null;

    function setIdle() {
        status.className = 'forum-feed-status';
        label.textContent = 'Loading more discussions...';
        if (retry) {
            retry.remove();
            retry = null;
        }
    }

    function finish() {
        done = true;
        observer.disconnect();
        status.className = 'forum-feed-status is-done';
        label.textContent = 'You are all caught up.';
    }

    function fail() {
        status.className = 'forum-feed-status is-error';
        label.textContent = "Couldn't load more discussions.";
        if (retry) return;
        retry = document.createElement('button');
        retry.type = 'button';
        retry.className = 'forum-feed-retry';
        retry.textContent = 'Try again';
        retry.addEventListener('click', function () {
            setIdle();
            load();
        });
        status.appendChild(retry);
    }

    function appendCards(html) {
        if (!html || !html.trim()) return 0;
        var bag = document.createElement('div');
        bag.innerHTML = html;
        var added = 0;
        bag.querySelectorAll('.forum-post-card').forEach(function (card) {
            var id = card.getAttribute('data-post-id');
            if (!id || seen.has(id)) return;
            seen.add(id);
            feed.appendChild(card);
            track(card);
            added++;
        });
        if (added) {
            // Without this the page the reader scrolled into is invisible: the
            // layout fades cards in only for nodes it got to observe at load.
            if (typeof window.ReproCareReveal === 'function') {
                window.ReproCareReveal(feed);
            } else {
                feed.querySelectorAll('.fade-in-card:not(.visible)').forEach(function (card) {
                    card.classList.add('visible');
                });
            }
        }
        return added;
    }

    // A page of already-read posts means the tail is looping; stopping beats
    // spinning forever on a sentinel that never leaves the viewport.
    function isExhausted(added, hasMore, cursor) {
        return !hasMore || !cursor || added === 0;
    }

    function load() {
        if (loading || done) return;
        loading = true;
        setIdle();

        var url = new URL(endpoint, window.location.origin);
        if (filter) url.searchParams.set('filter', filter);
        if (search) url.searchParams.set('search', search);
        url.searchParams.set('cursor', nextCursor);

        fetch(url.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Feed request failed.');
                return response.json();
            })
            .then(function (data) {
                var added = appendCards(data.html);
                nextCursor = data.cursor || '';
                loading = false;

                if (isExhausted(added, data.hasMore, nextCursor)) finish();
                // A resumed reader can be handed another page of posts they
                // already passed, so re-split the feed before pulling more.
                settleResume();
                // Appending can leave the sentinel exactly where it was, and an
                // unchanged intersection never re-fires the observer.
                if (!done) requestAnimationFrame(fillViewport);
            })
            .catch(function () {
                loading = false;
                fail();
            });
    }

    function fillViewport() {
        if (done || loading) return;
        var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
        if (sentinel.getBoundingClientRect().top - 600 < viewportHeight) load();
    }

    var observer = new IntersectionObserver(function (entries) {
        if (entries.some(function (entry) { return entry.isIntersecting; })) load();
    }, { rootMargin: '600px 0px' });
    observer.observe(sentinel);

    resume();
})();

/* Liking stays on the page: a full reload would drop every page the reader
   scrolled in and send them back through posts they already read. Delegated on
   the document so appended cards behave like the server-rendered ones. */
(function () {
    document.addEventListener('submit', function (event) {
        var form = event.target.closest('form[data-forum-like]');
        if (!form || form.dataset.busy) return;
        event.preventDefault();
        form.dataset.busy = '1';

        var button = form.querySelector('button');
        var token = form.querySelector('input[name="_token"]');
        var icon = button.querySelector('i');
        var count = button.querySelector('.forum-like-count');
        var word = button.querySelector('.forum-like-word');

        fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token ? token.value : '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Like request failed.');
                return response.json();
            })
            .then(function (data) {
                delete form.dataset.busy;
                button.classList.toggle('liked', !!data.liked);
                icon.className = 'bi ' + (data.liked ? 'bi-heart-fill' : 'bi-heart');
                count.textContent = data.likes_count;
                word.textContent = Number(data.likes_count) === 1 ? 'Like' : 'Likes';
            })
            .catch(function () {
                // Nothing reached the server, so fall back to the plain form
                // post rather than leaving a heart that only looks pressed.
                delete form.dataset.busy;
                form.submit();
            });
    });
})();
</script>
@endpush
@endsection
