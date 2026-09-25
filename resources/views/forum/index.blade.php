@php
    $forumUser = auth()->user();
    $forumLayout = $forumUser?->role === 'bhw_president' ? 'bhw-president.layout' : 'user.layout';
    $forumSection = $forumUser?->role === 'bhw_president' ? 'bhw-president-content' : 'user-content';
    $recentPosts = \App\Models\ForumPost::active()->latest()->take(5)->get();
    $forumRoleLabel = match($forumUser?->role) {
        'midwife' => 'Midwife',
        'bhw' => 'Barangay Health Worker',
        'bhw_president' => 'BHW President',
        default => 'Community Member',
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
    .forum-role-badge { background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text); border:none; padding:0.5rem 0.9rem; border-radius:999px; font-weight:800; font-size:0.76rem; }
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
            <span class="badge forum-role-badge">
                <i class="bi bi-person-badge me-1"></i> {{ $forumRoleLabel }}
            </span>
            <a href="{{ route('forum.create') }}" class="btn forum-composer-btn">
                <i class="bi bi-plus-circle-fill me-1"></i> Full Composer
            </a>
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

            {{-- Quick Post Creation Card (Easy Sharing) --}}
            <div class="quick-composer-card fade-in-card">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $forumUser->profile_image_url }}"
                         alt="{{ $forumUser->name }}"
                         class="composer-user-avatar"
                         onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                    <button type="button" class="quick-composer-btn" data-bs-toggle="collapse" data-bs-target="#quickComposerCollapse">
                        Share a pregnancy tip, ask a midwife, or post an update...
                    </button>
                </div>

                <div class="collapse mt-3" id="quickComposerCollapse">
                    <form action="{{ route('forum.store') }}" method="POST" enctype="multipart/form-data" class="composer-form">
                        @csrf
                        <textarea name="content"
                                  class="composer-textarea-full"
                                  placeholder="What would you like to share with mothers and health workers in San Carlos City?"
                                  rows="3"
                                  required>{{ old('content') }}</textarea>

                        <div class="composer-bottom-bar">
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
            </div>

            {{-- Feed Filters (All Posts vs My Posts) --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.15rem;font-weight:800;color:var(--color-text);margin:0;">
                    Community Feed
                </h3>
                <div class="d-flex gap-1 p-1 rounded-pill" style="background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:none;">
                    <a href="{{ route('forum.index') }}"
                       class="btn btn-sm"
                       style="border:none; border-radius:999px; font-weight:800; font-size:0.78rem; padding:0.35rem 0.95rem; {{ !$filter ? 'background:var(--color-text); background-color:var(--color-surface-strong); color:var(--color-on-solid);' : 'background:transparent; color:var(--color-text);' }}">
                        All Discussions
                    </a>
                    <a href="{{ route('forum.index', ['filter' => 'my-posts']) }}"
                       class="btn btn-sm"
                       style="border:none; border-radius:999px; font-weight:800; font-size:0.78rem; padding:0.35rem 0.95rem; {{ $filter === 'my-posts' ? 'background:var(--color-text); background-color:var(--color-surface-strong); color:var(--color-on-solid);' : 'background:transparent; color:var(--color-text);' }}">
                        My Posts
                    </a>
                </div>
            </div>

            {{-- Posts Feed --}}
            @forelse($posts as $post)
                @php
                    $author = $post->user;
                    $authorPhoto = $author ? $author->profile_image_url : '/images/avatars/avatar-female.svg';
                    $roleType = $post->user_type ?? 'user';
                    $roleClass = match($roleType) {
                        'midwife' => 'midwife',
                        'bhw' => 'bhw',
                        'bhw_president' => 'president',
                        default => 'member',
                    };
                    $roleName = match($roleType) {
                        'midwife' => 'Midwife',
                        'bhw' => 'BHW Health Worker',
                        'bhw_president' => 'BHW President',
                        default => 'Community Mother',
                    };
                    $isLiked = $post->isLikedBy(auth()->id(), \App\Models\User::class);
                @endphp

                <article class="forum-post-card fade-in-card" id="post-{{ $post->id }}">
                    {{-- Header --}}
                    <div class="post-header">
                        <div class="author-lockup">
                            <img src="{{ $authorPhoto }}"
                                 alt="{{ optional($author)->name ?? 'User' }}"
                                 class="author-avatar"
                                 onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                            <div>
                                <h4 class="author-name">
                                    {{ optional($author)->name ?? 'Community Member' }}
                                    <span class="role-verified-badge {{ $roleClass }}">
                                        <i class="bi bi-patch-check-fill"></i> {{ $roleName }}
                                    </span>
                                </h4>
                                <div class="author-time">
                                    <i class="bi bi-clock"></i>
                                    {{ $post->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        @if(auth()->id() === $post->user_id)
                            <div class="dropdown">
                                <button class="btn btn-sm rounded-circle" type="button" data-bs-toggle="dropdown" style="border:none; background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text);">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border:none; border-radius:12px; font-size:0.85rem;">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('forum.edit', $post->id) }}">
                                            <i class="bi bi-pencil me-2"></i> Edit Post
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('forum.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bi bi-trash me-2"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- Post Content --}}
                    <div class="post-text-body">{{ $post->content }}</div>

                    {{-- Attached Photo Preview --}}
                    @if($post->post_image)
                        <div class="post-media-frame">
                            <img src="{{ $post->post_image_url }}" alt="Attached photo">
                        </div>
                    @endif

                    {{-- Interaction Bar --}}
                    <div class="post-engagement-bar">
                        <div class="engagement-actions">
                            <form action="{{ route('forum.like', $post->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-engage {{ $isLiked ? 'liked' : '' }}">
                                    <i class="bi {{ $isLiked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                    <span>{{ $post->likes_count }}</span>
                                    <span class="d-none d-sm-inline">{{ $post->likes_count === 1 ? 'Like' : 'Likes' }}</span>
                                </button>
                            </form>

                            <button type="button" class="btn-engage" data-bs-toggle="collapse" data-bs-target="#comments-{{ $post->id }}">
                                <i class="bi bi-chat-dots"></i>
                                <span>{{ $post->comments_count }}</span>
                                <span class="d-none d-sm-inline">{{ $post->comments_count === 1 ? 'Comment' : 'Comments' }}</span>
                            </button>

                            <button type="button" class="btn-engage" onclick="sharePost('{{ route('forum.show', $post->id) }}')">
                                <i class="bi bi-share"></i>
                                <span class="d-none d-sm-inline">Share</span>
                            </button>
                        </div>

                        <a href="{{ route('forum.show', $post->id) }}" class="btn-engage">
                            <i class="bi bi-arrows-angle-expand"></i> Full View
                        </a>
                    </div>

                    {{-- Inline Comments Tray --}}
                    <div class="collapse mt-3" id="comments-{{ $post->id }}">
                        <div class="comments-tray">
                            {{-- Add Comment Form --}}
                            <form action="{{ route('forum.comment', $post->id) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="input-group">
                                    <input type="text"
                                           name="content"
                                           class="form-control"
                                           placeholder="Write a warm, supportive comment..."
                                           style="border:none; background:var(--color-surface); background-color:var(--color-surface); border-radius:999px 0 0 999px; font-size:0.88rem;"
                                           required>
                                    <button type="submit" class="btn" style="border:none; border-radius:0 999px 999px 0; padding:0 1.25rem; background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid);">
                                        <i class="bi bi-send-fill"></i>
                                    </button>
                                </div>
                            </form>

                            {{-- Comments List --}}
                            @forelse($post->comments as $comment)
                                @php
                                    $commentUser = $comment->user;
                                    $commentPhoto = $commentUser ? $commentUser->profile_image_url : '/images/avatars/avatar-female.svg';
                                @endphp
                                <div class="comment-item">
                                    <img src="{{ $commentPhoto }}"
                                         alt="{{ optional($commentUser)->name ?? 'User' }}"
                                         class="comment-avatar"
                                         onerror="this.onerror=null;this.src='/images/avatars/avatar-female.svg';">
                                    <div class="comment-bubble">
                                        <div class="d-flex justify-content-between align-items-center gap-2">
                                            <div class="comment-author">{{ optional($commentUser)->name ?? 'User' }}</div>
                                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="comment-text">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-2 text-muted" style="font-size:0.85rem;">
                                    No comments yet. Be the first to share your thoughts!
                                </div>
                            @endforelse
                        </div>
                    </div>
                </article>
            @empty
                <div class="card p-5 text-center fade-in-card" style="border:none; border-radius:20px; box-shadow:var(--wp-shadow-sm);">
                    <i class="bi bi-chat-heart" style="font-size:3.5rem; color:var(--color-border); margin-bottom:1rem;"></i>
                    <h4 style="font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; color:var(--color-text);">No discussions yet</h4>
                    <p style="color:var(--color-text-muted); max-width:440px; margin:0 auto 1.5rem;">
                        {{ $filter === 'my-posts' ? "You haven't posted in the community yet." : "The community feed is peaceful. Be the first mother or healthcare worker to start a conversation!" }}
                    </p>
                    <button type="button" class="btn mx-auto forum-composer-btn" data-bs-toggle="collapse" data-bs-target="#quickComposerCollapse">
                        <i class="bi bi-plus-circle-fill me-1"></i> Start First Discussion
                    </button>
                </div>
            @endforelse

            <div class="d-flex justify-content-center mt-4">
                {{ $posts->links() }}
            </div>
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

function sharePost(url) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Discussion link copied to clipboard!');
        });
    } else {
        prompt('Copy link to share:', url);
    }
}
</script>
@endpush
@endsection
