@php
    $learningUser = auth()->user();
    $learningLayout = match($learningUser?->role) {
        'cho' => 'cho.layout',
        'midwife' => 'midwife.layout',
        'bhw' => 'bhw.layout',
        'bhw_president' => 'bhw-president.layout',
        default => 'user.layout',
    };
    $learningSection = match($learningUser?->role) {
        'cho' => 'cho-content',
        'midwife' => 'midwife-content',
        'bhw' => 'bhw-content',
        'bhw_president' => 'bhw-president-content',
        default => 'user-content',
    };
@endphp

@extends($learningLayout)

@section('title', $material->title . ' - ReproCare Playable Media')

@push('styles')
<style>
    .material-hero {
        background:var(--bg-card);
        border:1px solid var(--border);
        border-radius:20px;
        overflow:hidden;
        margin-bottom:1.5rem;
    }
    .material-banner { width:100%; height:240px; object-fit:cover; }
    .material-body { padding:2rem; }
    .material-title { font-family:'Plus Jakarta Sans', sans-serif; font-size:1.6rem; font-weight:800; color:var(--text); margin-bottom:1rem; line-height:1.3; }
    .material-prose { font-size:0.95rem; line-height:1.8; color:var(--text); white-space:pre-wrap; }
    .video-embed-container { position:relative; padding-bottom:56.25%; height:0; overflow:hidden; border-radius:16px; margin-bottom:1.5rem; background:var(--color-surface-strong); box-shadow:0 10px 25px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent); }
    .video-embed-container iframe { position:absolute; top:0; left:0; width:100%; height:100%; border:0; }
    .html5-video-player { width:100%; max-height:520px; border-radius:16px; background:var(--color-surface-strong); margin-bottom:1.5rem; box-shadow:0 10px 25px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 30%, transparent); }
    .category-pill { background:var(--primary-subtle); color:var(--primary-light); padding:0.25em 0.85em; border-radius:20px; font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; }
    .meta-row { display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap; margin-bottom:1.25rem; font-size:0.8rem; color:var(--text-muted); }
    .consultation-badge { background:linear-gradient(135deg, var(--color-success), var(--color-success-text)); color:var(--color-on-solid); padding:0.35rem 0.85rem; border-radius:20px; font-size:0.75rem; font-weight:700; }
</style>
@endpush

@section($learningSection)

<div class="d-flex align-items-center justify-content-between gap-3 mb-4 fade-in-card flex-wrap">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ $learningUser?->isMidwife() ? route('midwife.learning.index') : route('learning.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Materials
        </a>
        <div>
            <h1 class="page-title mb-0" style="font-size:1.4rem;">{{ $material->title }}</h1>
            <p class="page-subtitle mb-0">Educational & Training Media Module</p>
        </div>
    </div>
    @if($learningUser?->isMidwife() || $learningUser?->isCho())
        <span class="consultation-badge d-inline-flex align-items-center">
            <i class="bi bi-broadcast me-1"></i> Consultation & Training Stream Ready
        </span>
    @endif
</div>

<div class="material-hero fade-in-card">

    {{-- Banner image if available and not a direct video --}}
    @if($material->image_url && !$material->isPlayableVideo())
        <img src="{{ $material->image_url }}"
             alt="{{ $material->title }}"
             class="material-banner"
             onerror="this.style.display='none'">
    @endif

    <div class="material-body">

        {{-- Meta Badges --}}
        <div class="meta-row">
            <span class="category-pill">{{ ucfirst(str_replace('-', ' ', $material->category ?? 'General')) }}</span>
            @php
                $typeConfig = [
                    'article' => ['bg' => 'var(--color-purple-text)', 'icon' => 'bi-file-text', 'label' => 'Article'],
                    'link'    => ['bg' => 'var(--color-success)', 'icon' => 'bi-link-45deg', 'label' => 'Link'],
                    'file'    => ['bg' => 'var(--color-warning)', 'icon' => 'bi-file-earmark', 'label' => 'File'],
                    'video'   => ['bg' => 'var(--color-teal-text)', 'icon' => 'bi-play-circle-fill', 'label' => 'Playable Video'],
                    'quiz'    => ['bg' => 'var(--color-purple-text)', 'icon' => 'bi-patch-question', 'label' => 'Quiz'],
                ];
                $tc = $typeConfig[$material->material_type] ?? $typeConfig['article'];
            @endphp
            <span style="background:color-mix(in srgb, {{ $tc['bg'] }} 12%, var(--color-surface));color:{{ $tc['bg'] }};padding:0.25em 0.85em;border-radius:20px;font-size:0.75rem;font-weight:700;">
                <i class="bi {{ $tc['icon'] }} me-1"></i>{{ $tc['label'] }}
            </span>
            @if($material->week_number)
                <span style="background:color-mix(in srgb, var(--color-secondary) 12%, transparent);color:var(--color-secondary-text);padding:0.25em 0.85em;border-radius:20px;font-size:0.75rem;font-weight:700;">
                    <i class="bi bi-calendar-heart me-1"></i>Week {{ $material->week_number }}
                </span>
            @endif
            <span><i class="bi bi-calendar3 me-1"></i>{{ $material->created_at->format('F j, Y') }}</span>
        </div>

        <h2 class="material-title">{{ $material->title }}</h2>

        {{-- ── 1. DYNAMIC PLAYABLE VIDEO PLAYER ─────────────────── --}}
        @if($material->isPlayableVideo())
            {{-- Case A: Uploaded Direct MP4 Video File --}}
            @if($material->isDirectVideoFile())
                <div class="mb-4">
                    <video class="html5-video-player" controls preload="metadata" playsinline poster="{{ $material->image_url ?? '' }}">
                        <source src="{{ $material->file_url }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            {{-- Case B: YouTube embedded player (privacy-enhanced nocookie) --}}
            @elseif($material->isYoutubeVideo())
                <div class="mb-4">
                    @include('learning.partials.youtube-player', ['id' => $material->youtube_id, 'title' => $material->title])
                </div>
            {{-- Case C: Vimeo / other embedded streams --}}
            @elseif($material->embed_url)
                <div class="video-embed-container mb-4">
                    <iframe src="{{ $material->embed_url }}"
                            title="{{ $material->title }}"
                            allowfullscreen
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share">
                    </iframe>
                </div>
            {{-- Case C: External Video Link Fallback --}}
            @elseif($material->video_url)
                <div class="p-4 bg-light text-center rounded-3 mb-4">
                    <i class="bi bi-film text-danger mb-2" style="font-size:2.5rem;"></i>
                    <h5>External Video Resource</h5>
                    <a href="{{ $material->video_url }}" target="_blank" class="btn btn-danger mt-2">
                        <i class="bi bi-play-circle-fill me-1"></i> Open Video Stream
                    </a>
                </div>
            @endif
        @endif

        {{-- ── 2. EXTERNAL RESOURCE LINK ────────────────────────── --}}
        @if($material->material_type === 'link' && $material->link_url)
            <div class="alert alert-info d-flex align-items-center mb-4" style="border-radius:14px;">
                <i class="bi bi-box-arrow-up-right me-2 fs-5"></i>
                <div>
                    <strong>External Resource Reference:</strong><br>
                    <a href="{{ $material->link_url }}" target="_blank" class="alert-link text-break">
                        {{ $material->link_url }}
                    </a>
                </div>
            </div>
        @endif

        {{-- ── 3. CONTENT & CLINICAL TEACHING NOTES ──────────────── --}}
        <div class="card p-3 bg-light border-0 mb-4" style="border-radius:14px;">
            <h6 class="fw-700 text-xs text-muted text-uppercase mb-2">Educational Content & Discussion Points</h6>
            <div class="material-prose">{{ $material->content }}</div>
        </div>

        {{-- ── 4. DOWNLOADABLE FILE ATTACHMENT ──────────────────── --}}
        @if($material->material_type === 'file' && !$material->isDirectVideoFile())
            <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 mt-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-arrow-down-fill text-warning fs-3"></i>
                    <div>
                        <div class="fw-700">{{ basename($material->file ?? 'Attachment') }}</div>
                        <small class="text-muted">Downloadable teaching resource</small>
                    </div>
                </div>
                <a href="{{ $material->file_url ?: '#' }}" class="btn btn-sm btn-warning" {{ $material->file_url ? 'target=_blank' : '' }}>
                    <i class="bi bi-download me-1"></i> Download
                </a>
            </div>
        @endif
    </div>
</div>

@endsection
