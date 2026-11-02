@php
    // Shared by the forum page and the infinite-scroll fragment, so a post that
    // arrives over XHR is indistinguishable from one that came with the page.
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
    $isLiked = in_array($post->id, $likedIds ?? [], true);
@endphp

<article class="forum-post-card fade-in-card" id="post-{{ $post->id }}" data-post-id="{{ $post->id }}">
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
                    @if($roleClass !== 'member')
                    <span class="role-verified-badge {{ $roleClass }}">
                        <i class="bi bi-patch-check-fill"></i> {{ $roleName }}
                    </span>
                    @endif
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
    @if($post->post_image_url)
        <div class="post-media-frame">
            <img src="{{ $post->post_image_url }}" alt="Attached photo">
        </div>
    @endif

    {{-- Interaction Bar --}}
    <div class="post-engagement-bar">
        <div class="engagement-actions">
            @if($isLimited)
            <span class="btn-engage" title="Liking unlocks after RHU approval of your account" style="cursor:default;">
                <i class="bi bi-heart"></i>
                <span>{{ $post->likes_count }}</span>
                <span class="d-none d-sm-inline">{{ $post->likes_count === 1 ? 'Like' : 'Likes' }}</span>
            </span>
            @else
            <form action="{{ route('forum.like', $post->id) }}" method="POST" class="d-inline" data-forum-like>
                @csrf
                <button type="submit" class="btn-engage {{ $isLiked ? 'liked' : '' }}">
                    <i class="bi {{ $isLiked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                    <span class="forum-like-count">{{ $post->likes_count }}</span>
                    <span class="d-none d-sm-inline forum-like-word">{{ $post->likes_count === 1 ? 'Like' : 'Likes' }}</span>
                </button>
            </form>
            @endif

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
            {{-- Add Comment Form (locked until RHU approval) --}}
            @if(!$isLimited)
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
            @endif

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
