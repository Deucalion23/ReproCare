@extends('bhw.layout')

@section('title', 'Forum Post - ReproCare')

@section('bhw-content')

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

<div class="row">
    <div class="col-lg-8">
        <!-- Post -->
        <div class="card fade-in-card mb-4" style="border:none; border-radius:16px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        @if($post->user->profile_image_url)
                            <img src="{{ $post->user->profile_image_url }}"
                                 alt="{{ $post->user->name }}"
                                 class="rounded-circle me-3"
                                 style="width:48px; height:48px; object-fit:cover; border:2px solid var(--primary);">
                        @else
                            <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                 style="width:48px; height:48px; background:linear-gradient(135deg, var(--primary), var(--accent-violet)); color:var(--color-on-solid); font-size:1.25rem; font-weight:800;">
                                {{ strtoupper(substr($post->user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-0" style="font-weight:800;">{{ $post->user->name }}</h6>
                            <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @if($post->user_id === auth()->id() && $post->user_type === 'bhw' && auth()->user()->role === 'bhw')
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('forum.edit', $post->id) }}">
                                    <i class="bi bi-pencil"></i> Edit
                                </a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('delete-form-{{ $post->id }}').submit();">
                                    <i class="bi bi-trash"></i> Delete
                                </a></li>
                            </ul>
                        </div>
                        <form id="delete-form-{{ $post->id }}" action="{{ route('forum.destroy', $post->id) }}" method="POST" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>

                <p class="mb-3" style="white-space:pre-wrap; line-height:1.6;">{{ $post->content }}</p>

                @if($post->post_image)
                    <div class="mb-3">
                        <img src="{{ $post->post_image_url }}"
                             alt="Post image"
                             class="img-fluid rounded"
                             style="max-height:400px; border-radius:12px;">
                    </div>
                @endif

                <div class="d-flex align-items-center gap-3 pt-3" style="border-top:1px solid var(--border);">
                    <form method="POST" action="{{ route('forum.like', $post->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $post->likes->where('user_id', auth()->id())->where('user_type', auth()->user()->role)->count() > 0 ? 'btn-danger' : 'btn-outline-danger' }}">
                            <i class="bi bi-heart{{ $post->likes->where('user_id', auth()->id())->where('user_type', auth()->user()->role)->count() > 0 ? '-fill' : '' }}"></i>
                            {{ $post->likes_count }}
                        </button>
                    </form>
                    <span class="text-muted">
                        <i class="bi bi-chat"></i> {{ $post->comments_count }} comments
                    </span>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="card fade-in-card" style="border:none; border-radius:16px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);">
            <div class="card-body p-4">
                <h6 class="mb-3" style="font-weight:800;">Comments</h6>

                <!-- Add Comment Form -->
                <form method="POST" action="{{ route('forum.comment', $post->id) }}" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <textarea class="form-control" name="content" rows="3"
                                  placeholder="Add a comment..." required>{{ old('content') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-send-fill me-1"></i> Comment
                    </button>
                </form>

                <!-- Comments List -->
                @if($post->comments->count() > 0)
                    @foreach($post->comments as $comment)
                        <div class="pb-3 mb-3" style="border-bottom:1px solid var(--border);">
                            <div class="d-flex align-items-start mb-2">
                                @if($comment->user->profile_image_url)
                                    <img src="{{ $comment->user->profile_image_url }}"
                                         alt="{{ $comment->user->name }}"
                                         class="rounded-circle me-3"
                                         style="width:40px; height:40px; object-fit:cover;">
                                @else
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                         style="width:40px; height:40px; background:linear-gradient(135deg, var(--accent-violet), var(--primary)); color:var(--color-on-solid); font-size:1rem; font-weight:800;">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <h6 class="mb-0" style="font-weight:800;">{{ $comment->user->name }}</h6>
                                    <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                    <p class="mb-0 mt-1" style="white-space:pre-wrap;">{{ $comment->content }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center">No comments yet. Be the first to comment!</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card fade-in-card mb-4" style="border:none; border-radius:16px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);">
            <div class="card-body p-4">
                <h6 class="mb-3" style="font-weight:800;">Forum Guidelines</h6>
                <ul class="small" style="padding-left:1.2rem;">
                    <li>Be respectful and supportive</li>
                    <li>Share helpful information</li>
                    <li>Keep posts relevant to maternal health</li>
                    <li>No medical advice - consult professionals</li>
                </ul>
            </div>
        </div>

        <div class="card fade-in-card" style="border:none; border-radius:16px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);">
            <div class="card-body p-4">
                <h6 class="mb-3" style="font-weight:800;">Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('forum.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Forum
                    </a>
                    <a href="{{ route('bhw.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
