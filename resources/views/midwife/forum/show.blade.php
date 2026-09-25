@extends('midwife.layout')

@section('title', 'Forum Post - ReproCare')

@section('midwife-content')

{{-- ═══════════════════════════════
     PAGE HERO
════════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Forum Post
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ $post->created_at->format('l, F j, Y') }}
                &nbsp;·&nbsp; View and discuss this post
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('forum.index') }}" class="btn-hero-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Forum
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
         style="background:color-mix(in srgb, var(--color-success-text) 10%, transparent); border:1px solid color-mix(in srgb, var(--color-success-text) 30%, transparent); color:var(--success); border-radius:10px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter:invert(1);"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Post Card -->
        <div class="card fade-in-card mb-4" style="border:1px solid var(--color-border); border-radius:20px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        @if($post->user && $post->user->profile_image_url)
                            <img src="{{ $post->user->profile_image_url }}"
                                 alt="{{ $post->user->name }}"
                                 class="rounded-circle me-3"
                                 style="width:48px; height:48px; object-fit:cover; border:2px solid var(--color-primary);">
                        @else
                            <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                 style="width:48px; height:48px; background:linear-gradient(135deg, var(--color-primary), var(--color-primary-text)); color:var(--color-on-solid); font-size:1.25rem; font-weight:800; box-shadow:0 4px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);">
                                {{ $post->user ? strtoupper(substr($post->user->name, 0, 1)) : '?' }}
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-0 fw-bold" style="color:var(--color-text); font-size:1rem;">{{ $post->user->name ?? 'Unknown User' }}</h6>
                            <small class="text-muted" style="font-size:0.8rem;">
                                <i class="bi bi-clock me-1"></i>{{ $post->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                    @if($post->user_id === auth()->id() && $post->user_type === 'midwife' && auth()->user()->role === 'midwife')
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" style="border-radius:8px;">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius:12px;">
                                <li><a class="dropdown-item py-2" href="{{ route('forum.edit', $post->id) }}">
                                    <i class="bi bi-pencil me-2 text-primary"></i> Edit
                                </a></li>
                                <li>
                                    <hr class="dropdown-divider my-1">
                                </li>
                                <li><a class="dropdown-item py-2 text-danger" href="#" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this post?')) document.getElementById('delete-form-{{ $post->id }}').submit();">
                                    <i class="bi bi-trash me-2"></i> Delete
                                </a></li>
                            </ul>
                        </div>
                        <form id="delete-form-{{ $post->id }}" action="{{ route('forum.destroy', $post->id) }}" method="POST" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>

                <div class="mb-3" style="color:var(--color-text); font-size:0.96rem; line-height:1.7; white-space:pre-wrap;">{{ $post->content }}</div>

                @if($post->post_image)
                    <div class="mb-3">
                        <img src="{{ $post->post_image_url }}"
                             alt="Post image"
                             class="img-fluid rounded-3 border"
                             style="max-height:420px; width:100%; object-fit:cover; border-color:var(--color-border) !important;">
                    </div>
                @endif

                <div class="d-flex align-items-center gap-3 pt-3" style="border-top:1px solid var(--color-border);">
                    <form method="POST" action="{{ route('forum.like', $post->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $post->likes->where('user_id', auth()->id())->where('user_type', auth()->user()->role)->count() > 0 ? 'btn-danger' : 'btn-light border' }}" style="border-radius:20px; font-weight:600; padding:0.35rem 0.85rem;">
                            <i class="bi bi-heart{{ $post->likes->where('user_id', auth()->id())->where('user_type', auth()->user()->role)->count() > 0 ? '-fill text-white' : ' text-danger' }} me-1"></i>
                            {{ $post->likes_count }}
                        </button>
                    </form>
                    <span class="text-muted fw-semibold" style="font-size:0.85rem;">
                        <i class="bi bi-chat-dots me-1 text-primary"></i> {{ $post->comments_count }} comments
                    </span>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="card fade-in-card" style="border:1px solid var(--color-border); border-radius:20px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent);">
            <div class="card-body p-4">
                <h6 class="mb-3 fw-bold" style="color:var(--color-text); font-size:1.05rem;">Comments</h6>

                <!-- Add Comment Form -->
                <form method="POST" action="{{ route('forum.comment', $post->id) }}" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <textarea class="form-control" name="content" rows="3"
                                  placeholder="Write a clinical note or supportive reply..." required
                                  style="border-radius:14px; border:1.5px solid var(--color-border); padding:0.85rem 1rem; font-size:0.92rem;"></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" style="border-radius:10px; font-weight:700; padding:0.5rem 1.25rem;">
                            <i class="bi bi-send-fill me-1"></i> Post Comment
                        </button>
                    </div>
                </form>

                <!-- Comments List -->
                @if($post->comments->count() > 0)
                    <div class="d-flex flex-column gap-3">
                        @foreach($post->comments as $comment)
                            <div class="p-3 rounded-3" style="background:var(--color-bg); border:1px solid var(--color-border);">
                                <div class="d-flex align-items-start gap-3">
                                    @if($comment->user->profile_image_url)
                                        <img src="{{ $comment->user->profile_image_url }}"
                                             alt="{{ $comment->user->name }}"
                                             class="rounded-circle"
                                             style="width:38px; height:38px; object-fit:cover;">
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width:38px; height:38px; background:var(--color-primary-soft); color:var(--color-primary-text); border:1px solid var(--color-border); font-size:0.9rem; font-weight:700;">
                                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-bold" style="color:var(--color-text); font-size:0.9rem;">{{ $comment->user->name }}</h6>
                                            <small class="text-muted" style="font-size:0.75rem;">{{ $comment->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-0" style="color:var(--color-info-text); font-size:0.88rem; line-height:1.5; white-space:pre-wrap;">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-chat-heart" style="font-size:2rem; color:var(--color-border);"></i>
                        <p class="mt-2 mb-0" style="font-size:0.88rem;">No comments yet. Be the first to start the discussion!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card fade-in-card mb-4" style="border:1px solid var(--color-border); border-radius:20px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent);">
            <div class="card-body p-4">
                <h6 class="mb-3 fw-bold" style="color:var(--color-text);">Forum Guidelines</h6>
                <ul class="small d-flex flex-column gap-2 mb-0" style="padding-left:1.2rem; color:var(--color-text-muted);">
                    <li>Be respectful, empathetic, and supportive.</li>
                    <li>Share verified clinical and maternal health advice.</li>
                    <li>Keep patient privacy protected at all times.</li>
                    <li>Refer critical emergencies directly to RHU/CHO.</li>
                </ul>
            </div>
        </div>

        <div class="card fade-in-card" style="border:1px solid var(--color-border); border-radius:20px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 4%, transparent);">
            <div class="card-body p-4">
                <h6 class="mb-3 fw-bold" style="color:var(--color-text);">Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('forum.index') }}" class="btn btn-hero-secondary btn-sm text-center">
                        <i class="bi bi-arrow-left me-1"></i> Back to Forum
                    </a>
                    @if(auth()->check() && auth()->user()->role === 'midwife' && ($post->user_id !== auth()->id() || $post->user_type !== \App\Models\User::class))
                        <form action="{{ route('forum.destroy', $post->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" style="border-radius:10px; font-weight:700;" onclick="return confirm('Moderate and delete this post?');">
                                <i class="bi bi-shield-exclamation me-1"></i> Moderate Post
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
