{{-- Reusable YouTube iframe player (strict 16:9).
     Usage: @include('learning.partials.youtube-player', ['id' => $material->youtube_id, 'title' => $material->title])
--}}
@props(['id' => null, 'title' => 'YouTube video'])
@if(!empty($id))
<div class="aspect-video w-full rounded-2xl overflow-hidden shadow-md border border-[var(--color-border)]" style="aspect-ratio:16/9;background:var(--color-surface-strong);">
    <iframe src="https://www.youtube-nocookie.com/embed/{{ $id }}?rel=0&modestbranding=1"
            title="{{ $title }}"
            class="w-full h-full"
            style="width:100%;height:100%;border:0;display:block;"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen></iframe>
</div>
@endif
