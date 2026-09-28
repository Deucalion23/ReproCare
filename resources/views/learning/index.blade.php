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

{{-- Header Banner (compact — no dead space when no staff actions) --}}
<div class="card mb-4 learn-hero" style="border:none; border-radius:18px; background:color-mix(in srgb, var(--color-text) 7%, var(--color-surface)); box-shadow:var(--wp-shadow-sm);">
    <div class="card-body learn-hero-body d-flex align-items-center gap-3 p-3 p-md-4">
        <div class="learn-hero-icon" aria-hidden="true">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <h2 class="fw-800 mb-1" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--color-text); letter-spacing:-0.5px; font-size:1.4rem; line-height:1.2;">
                Learning Materials
            </h2>
            <p class="mb-0" style="font-size:0.88rem; color:var(--color-text-muted); font-weight:500; line-height:1.5;">
                Watch, read, and learn — videos, guides, and resources for mothers and health workers.
            </p>
        </div>
        <span class="learn-hero-count flex-shrink-0" title="Total materials">
            <i class="bi bi-collection-play-fill"></i> {{ $materials->total() }}
        </span>
    @if($learningUser?->isRhu() || $learningUser?->isCho())
        @php $manageBase = $learningUser->isCho() ? 'cho.learning' : 'rhu.learning'; @endphp
        <div class="d-flex gap-2 flex-shrink-0 flex-wrap">
            <a href="{{ route($manageBase . '.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm" style="border-radius:10px; font-weight:600;">
                <i class="bi bi-plus-lg"></i> Add New Video / Material
            </a>
            <a href="{{ route($manageBase . '.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5" style="border-radius:10px;">
                <i class="bi bi-gear"></i> Manage Materials
            </a>
        </div>
    @endif
    </div>
</div>

{{-- Filter Toolbar (desktop grid / mobile bottom-sheet style) --}}
<div class="card mb-4 learn-filter-card" style="border:none; border-radius:20px; background:var(--color-surface); background-color:var(--color-surface); box-shadow:var(--wp-shadow-sm);">
    <div class="card-body p-3 p-md-4">
        {{-- Mobile-only header: clearly different entry point from desktop --}}
        <div class="learn-mobile-head">
            <span class="learn-mobile-title"><i class="bi bi-sliders"></i> Filters</span>
            @php $learnActiveCount = ($activeFilter ? 1 : 0) + ($activeCategory ? 1 : 0) + (request('search') ? 1 : 0); @endphp
            @if($learnActiveCount > 0)
                <span class="learn-mobile-count">{{ $learnActiveCount }} active</span>
            @endif
            <a href="{{ route('learning.index') }}" class="learn-mobile-reset">Reset</a>
        </div>
        <form method="GET" action="{{ route('learning.index') }}" class="learn-filter-grid">
            <div class="learn-field learn-field-search">
                <label for="learnSearch" class="form-label learn-label">Search Topic / Keyword</label>
                <div class="input-group learn-search-group">
                    <span class="input-group-text learn-search-icon"><i class="bi bi-search"></i></span>
                    <input id="learnSearch" type="text" name="search" class="form-control learn-input"
                           placeholder="Search videos, articles, counseling guides..." value="{{ request('search') }}"
                           enterkeyhint="search" autocomplete="off">
                </div>
            </div>
            <div class="learn-field learn-field-format">
                <label for="learnFormat" class="form-label learn-label">Format</label>
                <select id="learnFormat" name="type" class="form-select learn-input" onchange="this.form.submit()">
                    <option value="">All Formats</option>
                    <option value="video" {{ $activeFilter === 'video' ? 'selected' : '' }}>🎬 Playable Videos</option>
                    <option value="article" {{ $activeFilter === 'article' ? 'selected' : '' }}>📄 Articles &amp; Guides</option>
                    <option value="file" {{ $activeFilter === 'file' ? 'selected' : '' }}>📁 Downloadable Files</option>
                    <option value="link" {{ $activeFilter === 'link' ? 'selected' : '' }}>🔗 External Links</option>
                </select>
            </div>
            <div class="learn-field learn-field-category">
                <label for="learnCategory" class="form-label learn-label">Category</label>
                <select id="learnCategory" name="category" class="form-select learn-input" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <option value="prenatal-care" {{ $activeCategory === 'prenatal-care' ? 'selected' : '' }}>🤰 Prenatal Care</option>
                    <option value="nutrition" {{ $activeCategory === 'nutrition' ? 'selected' : '' }}>🥗 Nutrition</option>
                    <option value="warning-signs" {{ $activeCategory === 'warning-signs' ? 'selected' : '' }}>⚠️ Warning Signs</option>
                    <option value="family-planning" {{ $activeCategory === 'family-planning' ? 'selected' : '' }}>👨‍👩‍👧 Family Planning</option>
                    <option value="postpartum" {{ $activeCategory === 'postpartum' ? 'selected' : '' }}>👶 Postpartum &amp; Newborn</option>
                </select>
            </div>
            <div class="learn-actions">
                <button type="submit" class="btn learn-btn-apply" title="Apply filters"><i class="bi bi-funnel-fill"></i><span>Filter</span></button>
                <a href="{{ route('learning.index') }}" class="btn learn-btn-clear" title="Clear all filters"><i class="bi bi-x-lg"></i><span class="visually-hidden">Clear</span></a>
            </div>
        </form>
    </div>
</div>

{{-- Media Grid --}}
<div class="row g-4 learn-media-grid">
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
    .video-media-card { border:none !important; padding:0 !important; }
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

    /* ── Compact hero (no dead space) ── */
    .learn-hero-body { flex-wrap:wrap; }
    .learn-hero-icon {
        width:48px; height:48px; border-radius:14px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        font-size:1.4rem; color:#FFFFFF;
        background:linear-gradient(135deg, #1E293B, #0F172A);
        box-shadow:0 8px 18px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 20%, transparent);
    }
    .learn-hero-icon i { color:#FFFFFF !important; }
    .learn-hero-count {
        display:inline-flex; align-items:center; gap:6px;
        background:var(--color-surface-soft); color:var(--color-text);
        border-radius:999px; padding:0.45rem 0.9rem;
        font-size:0.82rem; font-weight:800; white-space:nowrap;
    }
    .learn-hero-count i { color:var(--color-secondary-text); }

    /* ── Appropriate filter layout: labels sit directly above their own control ── */
    .learn-filter-grid {
        display:grid;
        grid-template-columns:minmax(0, 1.5fr) minmax(0, 1fr) minmax(0, 1fr) auto;
        gap:0.9rem;
        align-items:end;
    }
    .learn-field { min-width:0; }
    .learn-label {
        display:block;
        font-size:0.72rem !important; font-weight:800 !important;
        text-transform:uppercase; letter-spacing:0.6px;
        color:var(--color-text-muted) !important;
        margin-bottom:0.4rem !important;
    }
    .learn-input {
        min-height:44px;
        background:var(--color-surface) !important;
        background-color:var(--color-surface) !important;
        border:1.5px solid var(--color-border) !important;
        border-radius:12px !important;
        font-size:0.88rem !important;
        color:var(--color-text) !important;
        box-shadow:none !important;
        outline:none !important;
    }
    .learn-input:focus,
    .learn-input:focus-visible,
    .learn-input:active {
        border-color:var(--color-border) !important;
        box-shadow:none !important;
        outline:none !important;
    }
    .learn-search-group:focus-within .learn-search-icon,
    .learn-search-group:focus-within .learn-input {
        border-color:var(--color-border) !important;
        box-shadow:none !important;
        outline:none !important;
    }
    .learn-search-group { flex-wrap:nowrap; }
    .learn-search-group .learn-search-icon {
        background:var(--color-surface) !important;
        border:1.5px solid var(--color-border) !important;
        border-right:none !important;
        border-radius:12px 0 0 12px !important;
        color:var(--color-text-muted) !important;
        min-height:44px;
        display:flex; align-items:center;
    }
    .learn-search-group .learn-input {
        border-left:none !important;
        border-radius:0 12px 12px 0 !important;
    }
    .learn-actions { display:flex; gap:0.5rem; padding-bottom:1px; }
    .learn-btn-apply {
        display:inline-flex; align-items:center; gap:0.45rem;
        min-height:44px; padding:0 1.2rem;
        border:none !important; border-radius:999px !important;
        background:#1E293B !important; background-color:#1E293B !important;
        color:#FFFFFF !important; font-weight:800; font-size:0.85rem;
        white-space:nowrap;
    }
    .learn-btn-apply :is(i, svg, span) { color:#FFFFFF !important; }
    .learn-btn-apply:hover { background:#0F172A !important; background-color:#0F172A !important; color:#FFFFFF !important; }
    .learn-btn-apply:hover :is(i, svg, span) { color:#FFFFFF !important; }
    .learn-btn-clear {
        display:inline-flex; align-items:center; justify-content:center;
        width:44px; height:44px; min-height:44px;
        border:1.5px solid var(--color-border) !important; border-radius:50% !important;
        background:var(--color-surface-soft) !important; color:var(--color-text) !important;
    }
    .learn-btn-clear:hover { background:var(--color-border) !important; color:var(--color-text) !important; }

    /* Thumbnails must touch card edges (no white inset gap) */
    .video-media-card > .position-relative.overflow-hidden { border-radius:20px 20px 0 0 !important; margin:0 !important; }

    /* Mobile header is desktop-hidden by design */
    .learn-mobile-head { display:none; }

    @media (max-width: 991.98px) {
        /* ── MOBILE filter = different pattern from desktop ──
           Compact sheet: header row + big search + duo selects + full-width CTA + swipe chips.
           Desktop stays a 4-column labeled grid; mobile is a thumb-first stacked sheet. */
        .learn-filter-card { border-radius:18px !important; }
        .learn-filter-card > .card-body { padding:0.9rem !important; }

        .learn-mobile-head {
            display:flex; align-items:center; gap:0.5rem;
            padding:0.15rem 0.25rem 0.8rem;
        }
        .learn-mobile-title {
            display:inline-flex; align-items:center; gap:0.45rem;
            font-family:'Plus Jakarta Sans',sans-serif; font-weight:800;
            font-size:1rem; color:var(--color-text); letter-spacing:-0.2px;
        }
        .learn-mobile-title i { color:var(--color-secondary-text); font-size:1.05rem; }
        .learn-mobile-count {
            font-size:0.7rem; font-weight:800;
            background:var(--color-surface-strong); color:var(--color-on-solid);
            border-radius:999px; padding:0.2rem 0.6rem; white-space:nowrap;
        }
        .learn-mobile-reset {
            margin-left:auto; font-size:0.82rem; font-weight:800;
            color:var(--color-secondary-text); text-decoration:none;
            padding:0.5rem 0.25rem; min-height:44px;
            display:inline-flex; align-items:center;
        }

        .learn-filter-grid {
            display:grid !important;
            grid-template-columns:minmax(0, 1fr) minmax(0, 1fr);
            grid-template-areas:
                "search search"
                "format category"
                "actions actions";
            gap:0.65rem;
            align-items:stretch;
            background:var(--color-surface-soft);
            border:1px solid var(--color-border);
            border-radius:14px;
            padding:0.75rem;
        }
        .learn-field-search { grid-area:search; }
        .learn-field-format { grid-area:format; min-width:0; }
        .learn-field-category { grid-area:category; min-width:0; }
        .learn-actions {
            grid-area:actions; display:flex; gap:0.5rem; padding-bottom:0;
        }
        .learn-label {
            font-size:0.68rem !important; letter-spacing:0.4px;
            margin-bottom:0.3rem !important;
        }
        .learn-input, .learn-search-group .learn-search-icon { min-height:50px !important; }
        .learn-input { font-size:0.92rem !important; border-radius:14px !important; }
        .learn-search-group .learn-search-icon { border-radius:14px 0 0 14px !important; }
        .learn-search-group .learn-input { border-radius:0 14px 14px 0 !important; }
        .learn-field-format .learn-input, .learn-field-category .learn-input {
            padding-left:0.7rem; padding-right:0.7rem;
            text-overflow:ellipsis;
        }
        .learn-btn-apply {
            flex:1 1 auto; min-height:50px; justify-content:center;
            font-size:0.92rem; border-radius:14px !important;
        }
        .learn-btn-clear {
            width:50px; height:50px; min-height:50px; border-radius:14px !important;
        }
    }
    @media (max-width: 575.98px) {
        .learn-hero-icon { width:42px; height:42px; font-size:1.2rem; }
        .learn-hero-count { width:100%; justify-content:center; }

        /* Phones: drop the nested sheet (single flat card), stack selects
           full-width so option text never truncates side-by-side. */
        .learn-filter-grid {
            grid-template-columns:minmax(0, 1fr);
            grid-template-areas:
                "search"
                "format"
                "category"
                "actions";
            gap:0.55rem;
            padding:0;
            background:transparent;
            border:none;
        }
        .learn-filter-card > .card-body { padding:0.85rem !important; }
        .learn-mobile-head { padding:0 0.15rem 0.65rem; align-items:center; }
        .learn-mobile-title { font-size:0.88rem !important; }
        .learn-mobile-reset { font-size:0.74rem !important; min-height:0 !important; padding:0.25rem !important; }
        .learn-mobile-count { font-size:0.62rem !important; }
        .learn-label { margin-bottom:0.25rem !important; font-size:0.6rem !important; letter-spacing:0.3px; text-align:left; }
        .learn-input, .learn-search-group .learn-search-icon { min-height:44px !important; }
        .learn-input {
            font-size:0.78rem !important;
            max-width:100%; width:100%; text-align:left;
        }
        .learn-field-format .learn-input, .learn-field-category .learn-input {
            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
        }
        .learn-btn-apply { min-height:44px; font-size:0.8rem; justify-content:center; }
        .learn-btn-clear { width:44px; height:44px; min-height:44px; }
        .learn-actions { align-items:center; }

        /* Phones: YouTube-style 2-col video grid — compact cards fit side by side */
        .learn-media-grid { display:grid !important; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:0.65rem; }
        .learn-media-grid > [class*="col-"] { width:auto !important; max-width:none !important; padding-left:0 !important; padding-right:0 !important; margin-top:0 !important; }
        .learn-media-grid .video-media-card { border-radius:14px !important; }
        .learn-media-grid .video-media-card > .position-relative.overflow-hidden { height:120px !important; border-radius:14px 14px 0 0 !important; }
        .learn-media-grid .video-media-card .card-body { padding:0.6rem 0.65rem !important; text-align:left; }
        .learn-media-grid .video-media-card h6 { font-size:0.7rem !important; line-height:1.35 !important; margin-bottom:0.25rem !important; text-align:left; }
        .learn-media-grid .video-media-card .card-body p { display:none !important; }
        .learn-media-grid .video-media-card .pt-2\.5 { padding-top:0.3rem !important; justify-content:flex-start !important; }
        .learn-media-grid .video-media-card small { font-size:0.6rem !important; text-align:left; }
        .learn-media-grid .video-media-card .btn { display:none !important; }
        .learn-media-grid .yt-watch-btn.rounded-circle { width:42px !important; height:42px !important; }
    }
</style>
@endpush

@endsection
