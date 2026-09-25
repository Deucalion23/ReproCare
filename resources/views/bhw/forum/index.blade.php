@extends('bhw.layout')

@section('title', 'Community Forum - ReproCare')

@section('bhw-content')

{{-- ═══════════════════════════════
     PAGE HERO
════════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Community Forum
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Share experiences, ask questions, and support each other
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('forum.create') }}" class="btn-hero-primary">
                <i class="bi bi-plus-circle-fill me-1"></i> New Post
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

{{-- ═══════════════════════════════
     FILTER TABS
════════════════════════════════ --}}
<div class="card fade-in-card mb-4" style="border:none; border-radius:16px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);">
    <div class="card-body p-3">
        <div class="d-flex gap-2">
            <a href="{{ route('forum.index') }}" class="btn {{ !$filter ? 'btn-primary' : 'btn-outline-secondary' }}">
                <i class="bi bi-globe"></i> All Posts
            </a>
            <a href="{{ route('forum.index', ['filter' => 'my-posts']) }}" class="btn {{ $filter === 'my-posts' ? 'btn-primary' : 'btn-outline-secondary' }}">
                <i class="bi bi-person"></i> My Posts
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        @if($posts->count() > 0)
            <div class="posts-list">
        @foreach($posts as $post)
            <div class="card fade-in-card mb-3" style="border:none; border-radius:16px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);">
                <div class="card-body p-4">
                    <!-- Post Header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            @if($post->user && $post->user->profile_image_url)
                                <img src="{{ $post->user->profile_image_url }}"
                                     alt="{{ $post->user->name }}"
                                     class="rounded-circle me-3"
                                     style="width:48px; height:48px; object-fit:cover; border:2px solid var(--primary);">
                            @else
                                <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                     style="width:48px; height:48px; background:linear-gradient(135deg, var(--primary), var(--accent-violet)); color:var(--color-on-solid); font-size:1.25rem; font-weight:800;">
                                    {{ $post->user ? strtoupper(substr($post->user->name, 0, 1)) : '?' }}
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0" style="font-weight:800;">{{ $post->user->name ?? 'Unknown User' }}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> {{ $post->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>

                        <!-- Post Actions -->
                        <div class="d-flex gap-2">
                            @if(auth()->id() === $post->user_id && $post->user_type === 'bhw' && auth()->user()->role === 'bhw')
                                <a href="{{ route('forum.edit', $post->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('forum.destroy', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Post Content -->
                    <div class="post-content mb-3">
                        <p class="card-text" style="font-size:1rem; line-height:1.6; white-space:pre-wrap;">{{ $post->content }}</p>

                        @if($post->post_image)
                            <div class="mt-2">
                                <img src="{{ $post->post_image_url }}"
                                     alt="Post image"
                                     class="img-fluid rounded"
                                     style="max-height:300px; cursor:pointer; border-radius:12px;"
                                     onclick="window.location.href='{{ route('forum.show', $post->id) }}'">
                            </div>
                        @endif
                    </div>

                    <!-- Post Actions Bar -->
                    <div class="d-flex justify-content-between align-items-center pt-3" style="border-top:1px solid var(--border);">
                        <div class="d-flex gap-3">
                            <form action="{{ route('forum.like', $post->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $post->isLikedBy(auth()->id(), \App\Models\User::class) ? 'btn-danger' : 'btn-outline-danger' }}">
                                    <i class="bi {{ $post->isLikedBy(auth()->id(), \App\Models\User::class) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                    <span class="ms-1">{{ $post->likes_count }}</span>
                                </button>
                            </form>
                            <a href="{{ route('forum.show', $post->id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-chat"></i>
                                <span class="ms-1">{{ $post->comments_count }}</span>
                            </a>
                        </div>
                        <a href="{{ route('forum.show', $post->id) }}" class="btn btn-sm btn-outline-primary">
                            Read More <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $posts->links() }}
            </div>
        @else
            <div class="card fade-in-card text-center py-5" style="border:none; border-radius:16px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);">
                <i class="bi bi-chat-dots" style="font-size:3rem; color:color-mix(in srgb, var(--color-text) 10%, transparent);"></i>
                <h4 class="text-muted mt-3">No posts yet</h4>
                <p class="text-muted">Be the first to share something with the community!</p>
                <a href="{{ route('forum.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create First Post
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
