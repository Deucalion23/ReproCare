@extends(request()->routeIs('midwife.messages.*') ? 'midwife.layout' : (request()->routeIs('bhw.messages.*') ? 'bhw.layout' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president.layout' : 'user.layout')))

@section('title', 'Trash - Messages')

@push('styles')
<style>
    @media (max-width:640px){
        .page-hero{ padding:.9rem 1rem !important; border-radius:14px !important; margin-bottom:.9rem !important; }
        .page-hero .page-hero-title{ font-size:1.02rem !important; }
        .page-hero .page-hero-subtitle{ font-size:.74rem !important; }
    }
    .rc-trash-row{ gap:.75rem; }
    .rc-trash-row > div:first-child{ flex:1 1 auto; }
    .rc-trash-row form{ flex-shrink:0; }
</style>
@endpush

@php
    $contentSection = request()->routeIs('midwife.messages.*') ? 'midwife-content' : (request()->routeIs('bhw.messages.*') ? 'bhw-content' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president-content' : 'user-content'));
    $messagesRouteBase = request()->routeIs('midwife.messages.*') ? 'midwife.messages' : (request()->routeIs('bhw.messages.*') ? 'bhw.messages' : (request()->routeIs('bhw-president.messages.*') ? 'bhw-president.messages' : 'user.messages'));
@endphp

@section($contentSection)
<div style="max-width:900px;margin:0 auto;padding-bottom:2rem;">
    <div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Trash</div>
            <p class="page-hero-subtitle">Review deleted conversations; restore or clear.</p>
        </div>
        <a href="{{ route($messagesRouteBase.'.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to chats</a>
    </div>
</div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="card-body p-0">
        @forelse($messages as $msg)
            @php $other = $msg->sender_id === auth()->id() ? $msg->receiver : $msg->sender; @endphp
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom rc-trash-row">
                <div style="min-width:0;">
                    <strong>{{ $other?->name ?? 'Unknown' }}</strong>
                    <div class="small text-muted text-truncate" style="max-width:420px;">{{ \Illuminate\Support\Str::limit($msg->body, 80) }}</div>
                    <div class="small text-muted">deleted {{ $msg->deleted_at?->diffForHumans() }}</div>
                </div>
                <form method="POST" action="{{ route($messagesRouteBase.'.restore', $msg->id) }}">@csrf<button class="btn btn-sm btn-success"><i class="bi bi-arrow-counterclockwise me-1"></i> Restore</button></form>
            </div>
        @empty
            <div class="p-4 text-center text-muted">Trash is empty.</div>
        @endforelse
    </div></div>
    <div class="mt-3">{{ $messages->links() }}</div>
</div>
@endsection
