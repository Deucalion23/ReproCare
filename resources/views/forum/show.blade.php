@php
    $forumUser = auth()->user();
    $forumLayout = $forumUser?->role === 'bhw_president' ? 'bhw-president.layout' : 'user.layout';
    $forumSection = $forumUser?->role === 'bhw_president' ? 'bhw-president-content' : 'user-content';
@endphp

@extends($forumLayout)

@section('title', '{{ Illuminate\Support\Str::limit($post->content, 50) }} - ReproCare Forum')

@section($forumSection)

@push('styles')
<style>
    .forum-show-wrap { max-width:860px; margin:0 auto; }

    /* Post card */
    .post-detail-card {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:20px;
        overflow:hidden;
        box-shadow:var(--shadow-sm);
        margin-bottom:1.5rem;
        transition:background 0.4s ease;
    }
    .post-detail-header {
        padding:1.25rem 1.5rem;
        border-bottom:1px solid var(--border);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
    }
    .forum-show-avatar {
        width:50px; height:50px;
        object-fit:cover;
        border-radius:50%;
        border:2px solid var(--primary);
        box-shadow:0 0 0 3px var(--primary-subtle);
    }
    .forum-avatar-init-lg {
        width:50px; height:50px;
        border-radius:50%;
        background:linear-gradient(135deg, var(--primary), var(--accent-violet));
        display:flex; align-items:center; justify-content:center;
        font-size:1.2rem; font-weight:700; color:var(--color-on-solid);
        box-shadow:0 2px 10px var(--primary-glow);
        flex-shrink:0;
    }
    .post-detail-body { padding:1.5rem; }
    .post-content-text {
        font-size:1rem;
        line-height:1.8;
        color:var(--text);
        white-space:pre-wrap;
        margin-bottom:1.25rem;
    }
    .forum-show-image {
        width:100%;
        max-height:420px;
        object-fit:cover;
        border-radius:14px;
        border:1px solid var(--border);
        margin-bottom:1.25rem;
        cursor:pointer;
        transition:transform 0.2s ease, opacity 0.2s ease;
    }
    .forum-show-image:hover { transform:scale(1.005); opacity:0.95; }

    /* Actions */
    .post-action-bar {
        display:flex;
        align-items:center;
        gap:0.75rem;
        padding-top:1rem;
        border-top:1px solid var(--border);
        flex-wrap:wrap;
    }
    .post-action-btn {
        display:inline-flex; align-items:center; gap:0.4rem;
        border:1px solid var(--border); background:transparent;
        color:var(--text-muted); border-radius:10px;
        padding:0.38rem 0.85rem; font-size:0.85rem; font-weight:500;
        cursor:pointer; transition:all 0.18s ease; text-decoration:none;
    }
    .post-action-btn:hover {
        background:var(--primary-subtle); border-color:var(--primary); color:var(--primary-light);
    }
    .post-action-btn.liked {
        background:color-mix(in srgb, var(--color-secondary) 12%, transparent); border-color:color-mix(in srgb, var(--color-secondary) 35%, transparent); color:var(--secondary);
    }

    /* Comments */
    .comments-card {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:20px;
        overflow:hidden;
        box-shadow:var(--shadow-sm);
        transition:background 0.4s ease;
    }
    .comments-card-header {
        padding:1rem 1.5rem;
        border-bottom:1px solid var(--border);
        font-family:'Plus Jakarta Sans', sans-serif;
        font-weight:700;
        font-size:0.95rem;
        color:var(--text);
    }
    .comment-form-area { padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); }
    .comment-textarea {
        width:100%;
        background:var(--bg-input);
        border:1.5px solid var(--border);
        border-radius:12px;
        color:var(--text);
        font-size:0.9rem;
        padding:0.75rem 0.95rem;
        resize:none;
        outline:none;
        transition:border-color 0.2s ease, box-shadow 0.2s ease, background 0.3s ease;
        margin-bottom:0.75rem;
    }
    .comment-textarea:focus {
        border-color:var(--primary);
        box-shadow:0 0 0 3px var(--primary-glow);
    }
    .comment-textarea::placeholder { color:var(--text-muted); opacity:0.65; }

    /* Comment items */
    .comment-item-show {
        display:flex; gap:0.9rem;
        padding:1rem 1.5rem;
        border-bottom:1px solid var(--border);
        transition:background 0.15s ease;
    }
    .comment-item-show:last-child { border-bottom:none; }
    .comment-item-show:hover { background:var(--primary-subtle); }
    .comment-avatar-md {
        width:36px; height:36px;
        border-radius:50%;
        background:linear-gradient(135deg, var(--primary), var(--accent-violet));
        display:flex; align-items:center; justify-content:center;
        font-size:0.82rem; font-weight:700; color:var(--color-on-solid);
        flex-shrink:0; margin-top:2px;
        box-shadow:0 2px 6px var(--primary-glow);
    }
    .comment-bubble-show {
        flex:1;
    }
    .comment-meta { display:flex; align-items:center; gap:0.6rem; margin-bottom:0.3rem; }
    .comment-author { font-size:0.875rem; font-weight:700; color:var(--text); }
    .comment-ts { font-size:0.75rem; color:var(--text-muted); }
    .comment-body { font-size:0.875rem; color:var(--text); line-height:1.6; }

    /* Role chip */
    .forum-role-chip {
        font-size:0.65rem; font-weight:700; text-transform:uppercase;
        letter-spacing:0.6px; padding:0.12em 0.55em; border-radius:5px;
    }
    .chip-midwife-s { background:linear-gradient(135deg, var(--primary), var(--accent-violet)); color:var(--color-on-solid); }
    .chip-bhw-s     { background:linear-gradient(135deg, var(--color-info), var(--color-info)); color:var(--color-on-solid); }
    .chip-user-s    { background:var(--primary-subtle); color:var(--primary-light); border:1px solid var(--border-glass); }

    /* Sidebar */
    .forum-sidebar-card { background:var(--bg-card); border:1px solid var(--border); border-radius:18px; overflow:hidden; margin-bottom:1.25rem; transition:background 0.4s ease; }
    .forum-sidebar-card-header { padding:0.9rem 1.2rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:0.6rem; font-family:'Plus Jakarta Sans', sans-serif; font-weight:700; font-size:0.9rem; color:var(--text); }
    .forum-sidebar-card-header i { color:var(--primary-light); }
    .guideline-item { display:flex; align-items:flex-start; gap:0.65rem; padding:0.55rem 1.2rem; font-size:0.875rem; color:var(--text-muted); border-bottom:1px solid var(--border); line-height:1.4; }
    .guideline-item:last-child { border-bottom:none; }
    .guideline-item i { color:var(--success); flex-shrink:0; margin-top:2px; }
</style>
@endpush

@section('user-content')

<div class="row g-4">

    {{-- ════ MAIN COLUMN ════ --}}
    <div class="col-lg-8">

        {{-- Back link --}}
        <div class="mb-3 fade-in-card">
            <a href="{{ route('forum.index') }}"
               style="color:var(--text-muted);font-size:0.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:0.4rem;transition:color 0.2s ease;">
                <i class="bi bi-arrow-left"></i> Back to Forum
            </a>
        </div>

        {{-- Post Card --}}
        <div class="post-detail-card fade-in-card">

            {{-- Header --}}
            <div class="post-detail-header">
                <div class="d-flex align-items-center gap-3">
                    {{-- Avatar --}}
                    <div class="forum-avatar-init-lg">
                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span style="font-weight:700;color:var(--text);">{{ $post->user->name }}</span>
                            <span class="forum-role-chip
                                {{ $post->user_type === 'midwife' ? 'chip-midwife-s' : ($post->user_type === 'bhw' ? 'chip-bhw-s' : 'chip-user-s') }}">
                                {{ $post->user_type === 'midwife' ? 'Midwife' : ($post->user_type === 'bhw' ? 'BHW' : 'Member') }}
                            </span>
                        </div>
                        <div style="font-size:0.78rem;color:var(--text-muted);">
                            <i class="bi bi-clock me-1"></i>{{ $post->created_at->diffForHumans() }}
                            &nbsp;·&nbsp;
                            <i class="bi bi-circle-fill" style="font-size:0.4rem;color:var(--success);"></i>
                            Active
                        </div>
                    </div>
                </div>

                {{-- Author actions dropdown --}}
                @if($post->user_id === auth()->id() && $post->user_type === 'user' && auth()->user()->role === 'user')
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('forum.edit', $post->id) }}">
                                    <i class="bi bi-pencil me-2" style="color:var(--primary-light);"></i> Edit Post
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="#"
                                   onclick="event.preventDefault(); document.getElementById('delete-form-{{ $post->id }}').submit();">
                                    <i class="bi bi-trash me-2"></i> Delete Post
                                </a>
                            </li>
                        </ul>
                        <form id="delete-form-{{ $post->id }}"
                              action="{{ route('forum.destroy', $post->id) }}"
                              method="POST" style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    </div>
                @endif
            </div>

            {{-- Body --}}
            <div class="post-detail-body">
                <p class="post-content-text">{{ $post->content }}</p>

                @if($post->post_image)
                    <img src="{{ $post->post_image_url }}"
                         alt="Post image"
                         class="forum-show-image">
                @endif

                {{-- Actions --}}
                <div class="post-action-bar">
                    <form method="POST" action="{{ route('forum.like', $post->id) }}" class="d-inline">
                        @csrf
                        <button type="submit"
                                class="post-action-btn {{ $post->likes->where('user_id', auth()->id())->where('user_type', auth()->user()->role)->count() > 0 ? 'liked' : '' }}">
                            <i class="bi {{ $post->likes->where('user_id', auth()->id())->where('user_type', auth()->user()->role)->count() > 0 ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                            {{ $post->likes_count }} {{ Str::plural('Like', $post->likes_count) }}
                        </button>
                    </form>

                    <span class="post-action-btn" style="cursor:default;">
                        <i class="bi bi-chat-dots"></i>
                        {{ $post->comments_count }} {{ Str::plural('Comment', $post->comments_count) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                {{ session('success') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Comments Card --}}
        <div class="comments-card fade-in-card">
            <div class="comments-card-header">
                <i class="bi bi-chat-dots-fill me-2" style="color:var(--primary-light);"></i>
                Comments
                <span style="font-size:0.82rem;color:var(--text-muted);font-weight:400;margin-left:0.5rem;">
                    ({{ $post->comments->count() }})
                </span>
            </div>

            {{-- Add comment form --}}
            <div class="comment-form-area">
                <form method="POST" action="{{ route('forum.comment', $post->id) }}">
                    @csrf
                    <textarea class="comment-textarea"
                              name="content"
                              rows="3"
                              placeholder="Share your thoughts or reply to this post..."
                              required>{{ old('content') }}</textarea>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-send-fill me-1"></i> Post Comment
                        </button>
                        <button type="reset" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </button>
                    </div>
                </form>
            </div>

            {{-- Comments list --}}
            @if($post->comments->count() > 0)
                @foreach($post->comments as $comment)
                    <div class="comment-item-show">
                        <div class="comment-avatar-md">
                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                        </div>
                        <div class="comment-bubble-show">
                            <div class="comment-meta">
                                <span class="comment-author">{{ $comment->user->name }}</span>
                                <span class="forum-role-chip
                                    {{ $comment->user_type === 'midwife' ? 'chip-midwife-s' : ($comment->user_type === 'bhw' ? 'chip-bhw-s' : 'chip-user-s') }}">
                                    {{ $comment->user_type === 'midwife' ? 'Midwife' : ($comment->user_type === 'bhw' ? 'BHW' : 'Member') }}
                                </span>
                                <span class="comment-ts">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="comment-body">{{ $comment->content }}</p>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state" style="padding:2.5rem 1rem;">
                    <i class="bi bi-chat-square-dots empty-state-icon"></i>
                    <h6>No Comments Yet</h6>
                    <p>Be the first to comment on this post!</p>
                </div>
            @endif
        </div>

    </div>

    {{-- ════ SIDEBAR ════ --}}
    <div class="col-lg-4">

        <div class="forum-sidebar-card fade-in-card">
            <div class="forum-sidebar-card-header">
                <i class="bi bi-shield-check-fill"></i> Forum Guidelines
            </div>
            @foreach([
                'Be respectful and supportive to all members',
                'Share helpful experiences and insights',
                'Ask relevant health-related questions',
                'Maintain privacy and confidentiality',
                'No medical advice — consult professionals',
            ] as $g)
            <div class="guideline-item">
                <i class="bi bi-check-circle-fill"></i> {{ $g }}
            </div>
            @endforeach
        </div>

        <div class="forum-sidebar-card fade-in-card">
            <div class="forum-sidebar-card-header">
                <i class="bi bi-chat-left-dots-fill"></i> Other Posts
            </div>
            @php
                $otherPosts = \App\Models\ForumPost::active()
                    ->where('id', '!=', $post->id)
                    ->latest()->take(4)->get();
            @endphp
            @foreach($otherPosts as $op)
                <a href="{{ route('forum.show', $op->id) }}"
                   style="display:block;padding:0.85rem 1.2rem;border-bottom:1px solid var(--border);text-decoration:none;transition:background 0.15s ease;"
                   onmouseover="this.style.background='var(--primary-subtle)'"
                   onmouseout="this.style.background='transparent'">
                    <div style="font-size:0.82rem;font-weight:700;color:var(--text);margin-bottom:0.2rem;">{{ $op->user->name }}</div>
                    <div style="font-size:0.8rem;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ \Illuminate\Support\Str::limit($op->content, 60) }}
                    </div>
                    <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.25rem;">
                        <i class="bi bi-clock me-1"></i>{{ $op->created_at->diffForHumans() }}
                    </div>
                </a>
            @endforeach
            @if($otherPosts->count() === 0)
                <div style="padding:1.25rem;text-align:center;color:var(--text-muted);font-size:0.875rem;">
                    No other posts yet
                </div>
            @endif
        </div>

        <a href="{{ route('forum.index') }}" class="btn btn-outline-primary w-100 fade-in-card">
            <i class="bi bi-arrow-left me-1"></i> Back to Forum
        </a>
    </div>

</div>

@endsection
