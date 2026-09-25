@php
    $learningUser = auth()->user();
    $learningLayout = match($learningUser?->role) {
        'cho'           => 'cho.layout',
        'midwife'       => 'midwife.layout',
        'bhw'           => 'bhw.layout',
        'bhw_president' => 'bhw-president.layout',
        default         => 'user.layout',
    };
    $learningSection = match($learningUser?->role) {
        'cho'           => 'cho-content',
        'midwife'       => 'midwife-content',
        'bhw'           => 'bhw-content',
        'bhw_president' => 'bhw-president-content',
        default         => 'user-content',
    };
    $activeFilter = request('type');
    $activeCategory = request('category');
@endphp

@extends($learningLayout)

@section('title', 'Learning Materials - ReproCare')

@section($learningSection)

{{-- Header Banner --}}
<div class="card mb-4" style="border:none; border-radius:18px; background:var(--color-surface); box-shadow:var(--wp-shadow-sm);">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3 p-4">
    <div>
        <h2 class="fw-800 mb-1" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--color-text); letter-spacing:-0.5px; font-size:1.6rem;">
            Learning Materials
        </h2>
        <p class="mb-0" style="font-size:0.9rem; color:var(--color-text); font-weight:600;">
            Watch, read, and learn — videos, guides, and resources for mothers and health workers.
        </p>
    </div>
    @if($learningUser?->isMidwife() || $learningUser?->isCho())
        <div class="d-flex gap-2">
            <a href="{{ route('midwife.learning.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm" style="border-radius:10px; font-weight:600;">
                <i class="bi bi-plus-lg"></i> Add New Video / Material
            </a>
            @if($learningUser?->isMidwife())
                <a href="{{ route('midwife.learning.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5" style="border-radius:10px;">
                    <i class="bi bi-gear"></i> Manage Materials
                </a>
            @endif
        </div>
    @endif
    </div>
</div>

{{-- Filter Toolbar --}}
<div class="card mb-4" style="border:none; border-radius:20px; background:var(--color-surface); background-color:var(--color-surface); box-shadow:var(--wp-shadow-sm);">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('learning.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label text-xs fw-800 text-muted mb-1 text-uppercase" style="letter-spacing:0.5px; color:var(--color-text-muted);">Search Topic / Keyword</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text text-muted" style="background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:none; border-radius:999px 0 0 999px;"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm" style="background:var(--color-surface-soft); background-color:var(--color-surface-soft); border:none; border-radius:0 999px 999px 0;"
                           placeholder="Search videos, articles, counseling guides..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-xs fw-800 text-muted mb-1 text-uppercase" style="letter-spacing:0.5px; color:var(--color-text-muted);">Format</label>
                <select name="type" class="form-select form-select-sm" style="background-color:var(--color-surface-soft); border:none; border-radius:12px;" onchange="this.form.submit()">
                    <option value="">All Formats</option>
                    <option value="video" {{ $activeFilter === 'video' ? 'selected' : '' }}>🎬 Playable Videos</option>
                    <option value="article" {{ $activeFilter === 'article' ? 'selected' : '' }}>📄 Articles &amp; Guides</option>
                    <option value="file" {{ $activeFilter === 'file' ? 'selected' : '' }}>📁 Downloadable Files</option>
                    <option value="link" {{ $activeFilter === 'link' ? 'selected' : '' }}>🔗 External Links</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-xs fw-800 text-muted mb-1 text-uppercase" style="letter-spacing:0.5px; color:var(--color-text-muted);">Category</label>
                <select name="category" class="form-select form-select-sm" style="background-color:var(--color-surface-soft); border:none; border-radius:12px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <option value="prenatal-care" {{ $activeCategory === 'prenatal-care' ? 'selected' : '' }}>🤰 Prenatal Care</option>
                    <option value="nutrition" {{ $activeCategory === 'nutrition' ? 'selected' : '' }}>🥗 Nutrition</option>
                    <option value="warning-signs" {{ $activeCategory === 'warning-signs' ? 'selected' : '' }}>⚠️ Warning Signs</option>
                    <option value="family-planning" {{ $activeCategory === 'family-planning' ? 'selected' : '' }}>👨‍👩‍👧 Family Planning</option>
                    <option value="postpartum" {{ $activeCategory === 'postpartum' ? 'selected' : '' }}>👶 Postpartum &amp; Newborn</option>
                </select>
            </div>
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm w-100" style="border:none; border-radius:999px; background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid); font-weight:800;"><i class="bi bi-funnel-fill"></i></button>
                <a href="{{ route('learning.index') }}" class="btn btn-sm" style="border:none; border-radius:999px; background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text);"><i class="bi bi-x"></i></a>
            </div>
        </form>

        {{-- Interactive Category Chips --}}
        <div class="d-flex align-items-center gap-1.5 mt-3 pt-3 flex-wrap" style="font-size:0.8rem; border:none;">
            <span class="text-xs fw-800 text-uppercase me-2" style="letter-spacing:0.5px; color:var(--color-text-muted);">Quick Filters:</span>
            <a href="{{ route('learning.index') }}"
               class="badge text-decoration-none px-3 py-2 rounded-pill {{ !$activeFilter && !$activeCategory ? '' : '' }}"
               style="border:none; {{ !$activeFilter && !$activeCategory ? 'background:var(--color-text); background-color:var(--color-surface-strong); color:var(--color-on-solid);' : 'background:var(--color-surface-soft); background-color:var(--color-surface-soft); color:var(--color-text);' }}">
                All ({{ $materials->total() }})
            </a>
            <a href="{{ route('learning.index', ['type' => 'video']) }}"
               class="badge text-decoration-none px-3 py-2 rounded-pill"
               style="border:none; {{ $activeFilter === 'video' ? 'background:var(--color-text); background-color:var(--color-surface-strong); color:var(--color-on-solid);' : 'background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:var(--color-secondary-text);' }}">
                <i class="bi bi-play-circle-fill me-1"></i> Playable Videos
            </a>
            <a href="{{ route('learning.index', ['category' => 'warning-signs']) }}"
               class="badge text-decoration-none px-3 py-2 rounded-pill"
               style="border:none; {{ $activeCategory === 'warning-signs' ? 'background:var(--color-text); background-color:var(--color-surface-strong); color:var(--color-on-solid);' : 'background:var(--color-peach-soft); background-color:var(--color-peach-soft); color:var(--color-warning-text);' }}">
                <i class="bi bi-exclamation-triangle me-1"></i> Warning Signs
            </a>
            <a href="{{ route('learning.index', ['category' => 'prenatal-care']) }}"
               class="badge text-decoration-none px-3 py-2 rounded-pill"
               style="border:none; {{ $activeCategory === 'prenatal-care' ? 'background:var(--color-text); background-color:var(--color-surface-strong); color:var(--color-on-solid);' : 'background:var(--color-success-soft); background-color:var(--color-success-soft); color:var(--color-success-text);' }}">
                🤰 Prenatal Care
            </a>
        </div>
    </div>
</div>

{{-- Media Grid --}}
<div class="row g-4">
    @forelse($materials as $material)
        @php
            $isVideo = $material->isPlayableVideo();
            $ytId = $material->youtube_id;
            $badgeStyle = match($material->category) {
                'warning-signs' => 'background:var(--color-peach-soft); background-color:var(--color-peach-soft); color:var(--color-warning-text);',
                'nutrition' => 'background:var(--color-success-soft); background-color:var(--color-success-soft); color:var(--color-success-text);',
                'family-planning' => 'background:var(--color-peach-soft); background-color:var(--color-peach-soft); color:var(--color-warning-text);',
                default => 'background:var(--color-secondary-soft); background-color:var(--color-secondary-soft); color:var(--color-secondary-text);',
            };
        @endphp
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 video-media-card position-relative"
                 style="border:none; border-radius:20px; background:var(--color-surface); background-color:var(--color-surface); box-shadow:var(--wp-shadow-sm); overflow:hidden; transition:transform 0.2s ease, box-shadow 0.2s ease;">
                
                {{-- Media Thumbnail with Video Overlay --}}
                <div class="position-relative overflow-hidden bg-dark" style="height:200px;">
                    @if($ytId)
                        <img src="{{ $material->youtube_thumbnail_url }}" alt="{{ $material->title }}" class="w-100 h-100" style="object-fit:cover; opacity:0.95;" loading="lazy"
                             onerror="this.style.display='none';">
                    @elseif($material->image_url)
                        <img src="{{ $material->image_url }}" alt="{{ $material->title }}" class="w-100 h-100" style="object-fit:cover; opacity:0.9;">
                    @else
                        @php
                            $cover = match($material->category) {
                                'nutrition' => ['bi-apple', '#14744B', '#22A06B'],
                                'family-planning' => ['bi-people-fill', '#964B2D', '#F59E7A'],
                                'teen-pregnancy' => ['bi-mortarboard-fill', '#6D28D9', '#A78BFA'],
                                'breastfeeding' => ['bi-heart-fill', '#AD2851', '#F472A0'],
                                'prenatal-care' => ['bi-clipboard2-pulse-fill', '#176C63', '#2A9D8F'],
                                'pregnancy-guide' => ['bi-journal-medical', '#1D4ED8', '#60A5FA'],
                                default => ['bi-file-earmark-text', '#4C1D95', '#8B5CF6'],
                            };
                        @endphp
                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white text-center px-3"
                             style="background:linear-gradient(135deg, {{ $cover[1] }}, {{ $cover[2] }});">
                            <i class="bi {{ $cover[0] }}" style="font-size:2.4rem; opacity:0.85;"></i>
                            <div class="fw-bold mt-2" style="font-size:0.85rem; line-height:1.35;">{{ \Illuminate\Support\Str::limit($material->title, 60) }}</div>
                        </div>
                    @endif

                    {{-- Central Play badge for YouTube videos (opens watch modal) --}}
                    @if($ytId)
                        <button type="button"
                            class="position-absolute top-50 start-50 translate-middle rounded-circle d-flex align-items-center justify-content-center text-white shadow border-0 yt-watch-btn"
                            style="width:56px; height:56px; background:color-mix(in srgb, var(--color-surface-strong) 92%, transparent); background-color:color-mix(in srgb, var(--color-surface-strong) 92%, transparent); border:none; backdrop-filter:blur(4px); transition:transform 0.2s ease; cursor:pointer;"
                            data-bs-toggle="modal" data-bs-target="#ytWatchModal"
                            data-yt="{{ $ytId }}"
                            data-title="{{ e($material->title) }}"
                            data-topic="{{ e($material->topic_badge) }}"
                            data-points="{{ e(json_encode($material->teaching_points)) }}"
                            data-url="{{ route('learning.show', $material->id) }}"
                            aria-label="Watch video: {{ e($material->title) }}">
                            <i class="bi bi-play-fill fs-3 ms-0.5"></i>
                        </button>
                        <span class="position-absolute bottom-0 start-0 m-2.5 badge bg-dark bg-opacity-75 text-white text-xs px-2 py-1">
                            <i class="bi bi-youtube text-danger me-1"></i> YouTube
                        </span>
                    @elseif($isVideo)
                        <a href="{{ route('learning.show', $material->id) }}"
                           class="position-absolute top-50 start-50 translate-middle rounded-circle d-flex align-items-center justify-content-center text-white shadow"
                           style="width:52px; height:52px; background:color-mix(in srgb, var(--color-surface-strong) 92%, transparent); background-color:color-mix(in srgb, var(--color-surface-strong) 92%, transparent); border:none; backdrop-filter:blur(4px); transition:transform 0.2s ease;">
                            <i class="bi bi-play-fill fs-3 ms-0.5"></i>
                        </a>
                        <span class="position-absolute bottom-0 start-0 m-2.5 badge bg-dark bg-opacity-75 text-white text-xs px-2 py-1">
                            <i class="bi bi-play-circle-fill text-danger me-1"></i> Stream Ready
                        </span>
                    @endif

                    {{-- Category Tag --}}
                    <span class="position-absolute top-0 end-0 m-2.5 badge text-xs px-2.5 py-1" style="{{ $badgeStyle }} border:none; border-radius:999px; font-weight:800;">
                        {{ ucfirst(str_replace('-', ' ', $material->category ?? 'General')) }}
                    </span>
                </div>

                {{-- Body Info --}}
                <div class="card-body p-3.5 d-flex flex-column justify-content-between" style="border:none;">
                    <div>
                        <h6 class="fw-800 mb-1.5 line-clamp-2" style="font-size:0.98rem; line-height:1.4; color:var(--color-text);">
                            {{ $material->title }}
                        </h6>
                        <p class="text-xs mb-3 line-clamp-2" style="line-height:1.5; color:var(--color-text-muted);">
                            {{ Str::limit(strip_tags($material->content), 120) }}
                        </p>
                    </div>

                    <div class="pt-2.5 d-flex align-items-center justify-content-between" style="border:none;">
                        <small class="text-xs" style="color:var(--color-text-muted);">
                            <i class="bi bi-calendar3 me-1"></i>{{ $material->created_at->format('M d, Y') }}
                        </small>
                        <div class="d-flex gap-1">
                            @if($ytId)
                                <button type="button" class="btn btn-sm d-inline-flex align-items-center gap-1 yt-watch-btn" style="border:none; border-radius:999px; font-size:0.8rem; font-weight:800; background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid);"
                                    data-bs-toggle="modal" data-bs-target="#ytWatchModal"
                                    data-yt="{{ $ytId }}"
                                    data-title="{{ e($material->title) }}"
                                    data-topic="{{ e($material->topic_badge) }}"
                                    data-points="{{ e(json_encode($material->teaching_points)) }}"
                                    data-url="{{ route('learning.show', $material->id) }}">
                                    <i class="bi bi-play-fill"></i> Watch Video
                                </button>
                            @else
                                <a href="{{ route('learning.show', $material->id) }}" class="btn btn-sm d-inline-flex align-items-center gap-1" style="border:none; border-radius:999px; font-size:0.8rem; font-weight:800; background:var(--color-surface-strong); background-color:var(--color-surface-strong); color:var(--color-on-solid);">
                                    <i class="bi {{ $isVideo ? 'bi-play-fill' : 'bi-eye-fill' }}"></i> {{ $isVideo ? 'Play Video' : 'View Guide' }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="card shadow-sm border p-5" style="border-radius:16px; background:var(--bg-card);">
                <i class="bi bi-camera-video text-muted mb-3" style="font-size:2.5rem;"></i>
                <h5 class="fw-700 text-dark">No Media Found</h5>
                <p class="text-muted text-xs mb-3">No learning materials match your active search or filter category.</p>
                <div>
                    <a href="{{ route('learning.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius:10px;">
                        Reset Filter
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($materials->hasPages())
    <div class="mt-4 d-flex justify-content-center">
        {{ $materials->links() }}
    </div>
@endif

{{-- ── YouTube Watch Modal ─────────────────────────────────────── --}}
<div class="modal fade" id="ytWatchModal" tabindex="-1" aria-labelledby="ytWatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:20px; overflow:hidden; border:1px solid var(--border);">
            <div class="modal-header border-0 pb-2">
                <div class="me-auto pe-3" style="min-width:0;">
                    <span id="ytModalTopic" class="badge rounded-pill mb-2" style="background:var(--color-secondary-soft); color:var(--color-secondary-text); border:1px solid var(--color-secondary-soft); font-size:0.72rem; font-weight:700;"></span>
                    <h5 class="modal-title fw-800 mb-0" id="ytWatchModalLabel" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);"></h5>
                </div>
                <button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div id="ytModalPlayer"></div>
                <h6 class="fw-800 mt-3 mb-2 text-xs text-uppercase text-muted" style="letter-spacing:0.5px;">Key Teaching Points
                </h6>
                <ul id="ytModalPoints" class="mb-3 ps-3" style="font-size:0.9rem; color:var(--text); line-height:1.7;"></ul>
                <a id="ytModalFull" href="#" class="btn btn-sm btn-outline-primary" style="border-radius:10px;">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open Full Learning Page
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var modal = document.getElementById('ytWatchModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        var btn = event.relatedTarget;
        if (!btn) return;
        var ytId = btn.getAttribute('data-yt');
        var title = btn.getAttribute('data-title') || 'Learning Video';
        var topic = btn.getAttribute('data-topic') || 'Maternal Care';
        var fullUrl = btn.getAttribute('data-url') || '#';
        var points = [];
        try { points = JSON.parse(btn.getAttribute('data-points') || '[]'); } catch (e) { points = []; }

        modal.querySelector('#ytWatchModalLabel').textContent = title;
        modal.querySelector('#ytModalTopic').textContent = topic;
        modal.querySelector('#ytModalFull').setAttribute('href', fullUrl);

        var list = modal.querySelector('#ytModalPoints');
        list.innerHTML = '';
        if (points.length === 0) {
            var li = document.createElement('li');
            li.textContent = 'Watch the video above for the full lesson.';
            list.appendChild(li);
        } else {
            points.forEach(function (p) {
                var li = document.createElement('li');
                li.textContent = p;
                list.appendChild(li);
            });
        }

        var holder = modal.querySelector('#ytModalPlayer');
        holder.innerHTML = '';
        if (ytId) {
            var wrap = document.createElement('div');
            wrap.setAttribute('class', 'aspect-video w-full rounded-2xl overflow-hidden shadow-md border border-[var(--color-border)]');
            wrap.style.aspectRatio = '16/9';
            wrap.style.background = 'var(--color-surface-strong)';
            var frame = document.createElement('iframe');
            frame.src = 'https://www.youtube-nocookie.com/embed/' + ytId + '?rel=0&modestbranding=1&autoplay=1';
            frame.title = title;
            frame.style.width = '100%';
            frame.style.height = '100%';
            frame.style.border = '0';
            frame.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
            frame.setAttribute('allowfullscreen', '');
            wrap.appendChild(frame);
            holder.appendChild(wrap);
        }
    });

    // Stop playback when the modal closes.
    modal.addEventListener('hidden.bs.modal', function () {
        var holder = modal.querySelector('#ytModalPlayer');
        if (holder) holder.innerHTML = '';
    });
})();
</script>
@endpush

@push('styles')
<style>
    .video-media-card { border:none !important; }
    .video-media-card:hover {
        transform:translateY(-3px);
        box-shadow:var(--wp-shadow-md) !important;
        border:none !important;
    }
    .video-media-card:hover .rounded-circle {
        transform:scale(1.1);
    }
    .line-clamp-2 {
        display:-webkit-box;
        -webkit-line-clamp:2;
        -webkit-box-orient:vertical;
        overflow:hidden;
    }
</style>
@endpush

@endsection
