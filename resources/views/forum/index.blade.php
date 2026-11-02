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
    $recentPosts = \App\Models\ForumPost::active()->latest()->take(5)->get();
@endphp

@extends($forumLayout)

@section('title', 'Community Forum - ReproCare')

@push('styles')
<style>
    /* === REPROCARE COMMUNITY FORUM === */
    .forum-container {
        max-width:1200px;
        margin:0 auto;
        padding-bottom:2rem;
    }

    /* ── Hero Banner ── */
    .forum-hero {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        border-radius:22px;
        padding:1.75rem 2rem;
        margin-bottom:1.5rem;
        color:var(--color-text);
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:1.5rem;
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
        font-size:1.75rem;
        font-weight:800;
        margin:0 0 0.3rem;
        display:flex;
        align-items:center;
        gap:0.6rem;
        color:var(--color-text);
        letter-spacing:-0.02em;
    }
    .forum-hero-sub {
        font-size:0.9rem;
        color:var(--color-text-muted);
        margin:0;
        max-width:600px;
        line-height:1.5;
        opacity:1;
    }
    .forum-composer-btn { background:var(--color-surface-strong) !important; background-color:var(--color-surface-strong) !important; border:none !important; border-radius:999px !important; padding:0.6rem 1.3rem !important; font-weight:800 !important; color:var(--color-on-solid) !important; box-shadow:0 8px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 22%, transparent); }
    .forum-composer-btn:hover { background:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; transform:translateY(-1px); }

    /* ── Quick Share / Composer Card (Makes sharing effortless) ── */
    .quick-composer-card {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        border-radius:20px;
        padding:1.25rem 1.4rem;
        box-shadow:var(--wp-shadow-sm);
        margin-bottom:1.5rem;
        transition:border-color 0.2s;
    }
    .quick-composer-card:hover {
        border:none;
    }
    .composer-user-avatar {
        width:44px;
        height:44px;
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
        gap:1rem;
        margin-top:0.85rem;
        flex-wrap:wrap;
    }
    .btn-attach-photo {
        display:inline-flex;
        align-items:center;
        gap:0.4rem;
        padding:0.5rem 0.95rem;
        border-radius:999px;
        border:none;
        background:var(--color-surface-soft); background-color:var(--color-surface-soft);
        color:var(--color-text);
        font-size:0.84rem;
        font-weight:700;
        cursor:pointer;
        transition:all 0.2s;
    }
    .btn-attach-photo:hover {
        background:var(--color-border);
        color:var(--color-text);
        border:none;
    }
    .composer-preview-tag {
        display:inline-flex;
        align-items:center;
        gap:0.4rem;
        background:var(--color-primary-subtle);
        color:var(--color-primary-text);
        padding:0.35rem 0.75rem;
        border-radius:8px;
        font-size:0.78rem;
        font-weight:600;
    }
    .composer-preview-tag button {
        background:none;
        border:none;
        color:inherit;
        padding:0;
        cursor:pointer;
        display:flex;
        align-items:center;
    }

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
        padding:0.72rem 1.25rem !important;
        line-height:1.4 !important;
    }
    .quick-composer-card:focus-within .quick-inline-input {
        border-radius:14px !important;
        min-height:88px;
        white-space:normal !important;
        line-height:1.5 !important;
    }
    .quick-inline-bar { display:none; }
    .quick-composer-card:focus-within .quick-inline-bar { display:flex; }

    /* ── Post Feed Cards (Makes reading enjoyable) ── */
    .forum-post-card {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        border-radius:22px;
        padding:1.5rem;
        margin-bottom:1.25rem;
        box-shadow:var(--wp-shadow-sm);
        transition:all 0.25s ease;
    }
    .forum-post-card:hover {
        border:none;
        box-shadow:var(--wp-shadow-md);
        transform:translateY(-2px);
    }
    /* ── Infinite scroll tail ── */
    .forum-feed-sentinel { width:100%; height:1px; }
    .forum-feed-status { display:flex; align-items:center; justify-content:center; gap:.55rem; padding:.9rem 0 1.4rem; font-size:.82rem; font-weight:700; color:var(--color-text-muted); text-align:center; }
    .forum-feed-status.is-hidden { display:none; }
    .forum-feed-spinner { width:15px; height:15px; border-radius:50%; border:2px solid var(--color-border); border-top-color:var(--color-secondary-text); animation:forum-spin .7s linear infinite; flex-shrink:0; }
    .forum-feed-status.is-done .forum-feed-spinner { display:none; }
    .forum-feed-status.is-error { color:var(--color-danger-text); }
    .forum-feed-status.is-error .forum-feed-spinner { display:none; }
    .forum-feed-retry { border:1px solid var(--color-danger-text); background:transparent; color:var(--color-danger-text); border-radius:999px; padding:.2rem .75rem; font-size:.75rem; font-weight:700; cursor:pointer; }
    @keyframes forum-spin { to { transform:rotate(360deg); } }
    .post-header {
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:1rem;
        margin-bottom:1rem;
    }
    .author-lockup {
        display:flex;
        align-items:center;
        gap:0.85rem;
    }
    .author-avatar {
        width:46px;
        height:46px;
        border-radius:50%;
        object-fit:cover;
        border:none;
        flex-shrink:0;
    }
    .author-name {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:0.96rem;
        font-weight:800;
        color:var(--color-text);
        margin:0;
        display:flex;
        align-items:center;
        gap:0.4rem;
    }
    .author-time {
        font-size:0.76rem;
        color:var(--color-text-muted);
        display:flex;
        align-items:center;
        gap:0.3rem;
        margin-top:0.15rem;
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
        font-size:0.95rem;
        line-height:1.68;
        color:var(--color-text);
        white-space:pre-wrap;
        margin-bottom:1rem;
    }

    .post-media-frame {
        border-radius:16px;
        overflow:hidden;
        margin-bottom:1.15rem;
        border:none;
        max-height:380px;
    }
    .post-media-frame img {
        width:100%;
        height:auto;
        max-height:380px;
        object-fit:cover;
        display:block;
    }

    /* Post Interaction Bar */
    .post-engagement-bar {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:0.75rem;
        border:none;
        padding-top:0.85rem;
        flex-wrap:wrap;
    }
    .engagement-actions {
        display:flex;
        align-items:center;
        gap:0.5rem;
    }
    .btn-engage {
        display:inline-flex;
        align-items:center;
        gap:0.45rem;
        padding:0.45rem 0.9rem;
        border-radius:999px;
        border:none;
        background:var(--color-surface-soft); background-color:var(--color-surface-soft);
        color:var(--color-text);
        font-size:0.82rem;
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
        margin-top:1.15rem;
        padding:1.15rem;
        background:var(--color-bg); background-color:var(--color-bg);
        border-radius:16px;
        border:none;
    }
    .comment-item {
        display:flex;
        /* flex-start also blocks the default align-items:stretch, which would
           otherwise elongate a height-less avatar into an ellipse. */
        align-items:flex-start;
        gap:0.75rem;
        margin-bottom:0.95rem;
    }
    .comment-avatar {
        width:34px;
        height:34px;
        border-radius:50%;
        object-fit:cover;
        flex-shrink:0;
    }
    .comment-bubble {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        border-radius:14px;
        padding:0.7rem 0.9rem;
        flex:1;
    }
    .comment-author {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:0.86rem;
        font-weight:700;
        color:var(--color-text);
        margin-bottom:0.2rem;
    }
    .comment-text {
        font-size:0.86rem;
        color:var(--color-text);
        line-height:1.5;
        margin:0;
    }

    /* ── Right Column Sidebar ── */
    .forum-side-card {
        background:var(--color-surface); background-color:var(--color-surface);
        border:none;
        border-radius:20px;
        padding:1.35rem;
        margin-bottom:1.25rem;
        box-shadow:var(--wp-shadow-sm);
    }
    .side-card-title {
        font-family:'Plus Jakarta Sans', sans-serif;
        font-size:1.05rem;
        font-weight:800;
        color:var(--color-text);
        margin:0 0 0.9rem;
        display:flex;
        align-items:center;
        gap:0.5rem;
    }
    .guide-item {
        display:flex;
        align-items:flex-start;
        gap:0.65rem;
        margin-bottom:0.75rem;
        font-size:0.83rem;
        color:var(--color-text-muted);
        line-height:1.5;
    }
    .guide-item i {
        color:var(--color-primary-text);
        font-size:0.95rem;
        flex-shrink:0;
        margin-top:2px;
    }

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
                A safe, caring space for expectant mothers and local healthcare workers in San Carlos City to exchange advice, support, and experiences.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($isLimited)
                <span class="badge rounded-pill px-3 py-2" style="border:1px solid var(--color-border); background:var(--color-surface-soft); color:var(--color-text-muted); font-size:0.75rem;" title="Posting unlocks after RHU approval of your account">
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

    <div class="row g-4">

        {{-- Left Column: Feed & Composer --}}
        <div class="col-lg-8">

            {{-- Quick Post Creation Card (type directly in the pill — expands in place) --}}
            @if($isLimited)
            <div class="quick-composer-card fade-in-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px; height:44px; border-radius:50%; background:var(--color-surface-soft); color:var(--color-text-muted); font-size:1.1rem;" aria-hidden="true">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <div style="min-width:0;">
                        <div style="font-weight:800; font-size:0.9rem; color:var(--color-text);">Posting unlocks after RHU approval</div>
                        <div style="font-size:0.82rem; color:var(--color-text-muted);">You're browsing with limited access — read and explore meanwhile.</div>
                    </div>
                </div>
            </div>
            @else
            <div class="quick-composer-card fade-in-card">
                <form action="{{ route('forum.store') }}" method="POST" enctype="multipart/form-data" class="composer-form quick-inline-form">
                    @csrf
                    <div class="d-flex align-items-center gap-3">
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
                            <input type="file" name="post_image" id="quickPostImageInput" accept="image/*" style="display:none;" onchange="handleImageSelected(this)">
                        </label>
                        <div id="quickImgPreviewTag" style="display:none;" class="composer-preview-tag">
                            <i class="bi bi-file-earmark-image"></i>
                            <span id="quickImgName">photo.jpg</span>
                            <button type="button" onclick="clearQuickImage()"><i class="bi bi-x-circle-fill"></i></button>
                        </div>
                        <button type="submit" class="btn btn-primary" style="border-radius:12px;padding:0.5rem 1.35rem;font-weight:700;">
                            <i class="bi bi-send-fill me-1"></i> Post
                        </button>
                    </div>
                </form>
            </div>
            @endif

            {{-- Feed Filters (All Posts vs My Posts) --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 forum-feed-head">
                <h3 class="forum-feed-title" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.15rem;font-weight:800;color:var(--color-text);margin:0;">
                    Community Feed
                </h3>
                <div class="d-flex gap-1 p-1 rounded-pill" style="background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:none;">
                    <a href="{{ route('forum.index') }}"
                       class="btn btn-sm"
                       style="border:none; border-radius:999px; font-weight:800; font-size:0.78rem; padding:0.35rem 0.95rem; {{ !$filter ? 'background:var(--color-text) !important; color:var(--color-bg) !important;' : 'background:transparent; color:var(--color-text);' }}">
                        All Discussions
                    </a>
                    <a href="{{ route('forum.index', ['filter' => 'my-posts']) }}"
                       class="btn btn-sm"
                       style="border:none; border-radius:999px; font-weight:800; font-size:0.78rem; padding:0.35rem 0.95rem; {{ $filter === 'my-posts' ? 'background:var(--color-text) !important; color:var(--color-bg) !important;' : 'background:transparent; color:var(--color-text);' }}">
                        My Posts
                    </a>
                </div>
            </div>

            {{-- Posts Feed --}}
            <div id="forumFeed" class="forum-feed-stack">
            @forelse($posts as $post)
                @include('forum.post-card', ['post' => $post, 'isLimited' => $isLimited, 'likedIds' => $likedIds])
            @empty
                <div class="card p-5 text-center fade-in-card" style="border:none; border-radius:20px; box-shadow:var(--wp-shadow-sm);">
                    <i class="bi bi-chat-heart" style="font-size:3.5rem; color:var(--color-border); margin-bottom:1rem;"></i>
                    <h4 style="font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; color:var(--color-text);">No discussions yet</h4>
                    <p style="color:var(--color-text-muted); max-width:440px; margin:0 auto 1.5rem;">
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
                 data-filter="{{ $filter ?? '' }}" data-search="{{ $search ?? '' }}"></div>
            <div id="forumFeedStatus" class="forum-feed-status{{ $posts->hasMorePages() ? '' : ' is-done' }}" role="status" aria-live="polite">
                <span class="forum-feed-spinner" aria-hidden="true"></span>
                <span class="forum-feed-status-text">{{ $posts->hasMorePages() ? 'Loading more discussions...' : 'You are all caught up.' }}</span>
            </div>
            @endif
        </div>

        {{-- Right Column: Community Guidelines & Activity --}}
        <div class="col-lg-4">

            {{-- Community Values Card (Collapsible) --}}
            <div class="forum-side-card fade-in-card">
                <div class="d-flex justify-content-between align-items-center" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#guidelinesCollapse">
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

            {{-- Recent Discussions --}}
            <div class="forum-side-card fade-in-card">
                <h4 class="side-card-title" style="color:var(--color-text);">
                    Recent Discussions
                </h4>
                @forelse($recentPosts as $recent)
                    <a href="{{ route('forum.show', $recent->id) }}" class="d-block text-decoration-none p-2.5 rounded-3 mb-2" style="background:var(--color-bg); background-color:var(--color-bg); border:none; border-radius:12px; transition:all 0.2s; padding:0.65rem 0.8rem;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="font-weight:800; font-size:0.86rem; color:var(--color-text);">{{ optional($recent->user)->name }}</span>
                            <small style="color:var(--color-text-muted);">{{ $recent->created_at->diffForHumans(null, true, true) }}</small>
                        </div>
                        <p class="small text-truncate mb-0 mt-1" style="color:var(--color-text-muted);">{{ $recent->content }}</p>
                    </a>
                @empty
                    <p class="small mb-0" style="color:var(--color-text-muted);">No recent activity.</p>
                @endforelse
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
function handleImageSelected(input) {
    if (input.files && input.files[0]) {
        document.getElementById('quickImgName').textContent = input.files[0].name;
        document.getElementById('quickImgPreviewTag').style.display = 'inline-flex';
    }
}

function clearQuickImage() {
    const input = document.getElementById('quickPostImageInput');
    if (input) input.value = '';
    document.getElementById('quickImgPreviewTag').style.display = 'none';
}

function focusQuickComposer() {
    var q = document.getElementById('quickInlineInput');
    if (!q) return;
    q.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(function () { q.focus({ preventScroll: true }); }, 350);
}

(function () {
    var q = document.getElementById('quickInlineInput');
    if (!q) return;
    q.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
    var card = q.closest('.quick-composer-card');
    if (card) {
        card.addEventListener('focusout', function () {
            if (q.value.trim() === '') q.style.height = '';
        });
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
            added++;
        });
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

                if (isExhausted(added, data.hasMore, nextCursor)) {
                    finish();
                    return;
                }
                // Appending can leave the sentinel exactly where it was, and an
                // unchanged intersection never re-fires the observer.
                requestAnimationFrame(fillViewport);
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
