{{-- Live YouTube preview for the "YouTube Video Link" form field.
     Requires an input with id="video_url". Include once per form.
     Usage: @include('learning.partials.youtube-live-preview')
--}}
<div id="yt_live_preview_wrap" class="mt-3" style="display:none;">
    <label class="form-label fw-700 text-xs text-uppercase text-muted" style="letter-spacing:0.5px;">
        <i class="bi bi-play-circle-fill text-danger me-1"></i>Live Preview
    </label>
    <div class="aspect-video w-full rounded-2xl overflow-hidden shadow-md border border-[var(--color-border)]" style="aspect-ratio:16/9;background:var(--color-surface-strong);">
        <iframe id="yt_live_iframe" src="" title="YouTube video preview"
                style="width:100%;height:100%;border:0;display:block;"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen></iframe>
    </div>
    <small class="text-muted d-block mt-1">Video ID: <code id="yt_live_id_label"></code></small>
</div>

@push('scripts')
<script>
(function () {
    function extractYoutubeIdClient(url) {
        if (!url) return null;
        url = url.trim();
        var patterns = [
            /youtube\.com\/watch\?(?:.*[?&])?v=([\w-]{11})/i,
            /youtube\.com\/shorts\/([\w-]{11})/i,
            /youtube\.com\/embed\/([\w-]{11})/i,
            /youtube\.com\/live\/([\w-]{11})/i,
            /youtu\.be\/([\w-]{11})/i
        ];
        for (var i = 0; i < patterns.length; i++) {
            var m = url.match(patterns[i]);
            if (m) return m[1];
        }
        return null;
    }

    function refreshYoutubePreview() {
        var input = document.getElementById('video_url');
        var wrap = document.getElementById('yt_live_preview_wrap');
        var frame = document.getElementById('yt_live_iframe');
        var label = document.getElementById('yt_live_id_label');
        if (!input || !wrap || !frame) return;
        var id = extractYoutubeIdClient(input.value);
        if (id) {
            frame.src = 'https://www.youtube-nocookie.com/embed/' + id + '?rel=0&modestbranding=1';
            if (label) label.textContent = id;
            wrap.style.display = 'block';
        } else {
            frame.removeAttribute('src');
            wrap.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('video_url');
        if (input) {
            input.addEventListener('input', refreshYoutubePreview);
            refreshYoutubePreview();
        }
    });
})();
</script>
@endpush
