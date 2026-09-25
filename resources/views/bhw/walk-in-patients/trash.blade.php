@extends('bhw.layout')

@section('title', 'Trashed Walk-in Patients - ReproCare')

@section('bhw-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title">Trashed Walk-ins</h2>
            <p class="page-subtitle">Soft-deleted records. Restore to bring them back — nothing is permanently erased here.</p>
        </div>
        <a href="{{ route('bhw.patients', ['filter' => 'unregistered']) }}" class="btn btn-outline-light"><i class="bi bi-arrow-left me-1"></i> Back to Women</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="card-body p-0">
        @forelse($patients as $p)
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                <div><strong>{{ $p->full_name }}</strong><div class="small text-muted">{{ $p->contact_number ?? 'No contact' }} · deleted {{ $p->deleted_at?->diffForHumans() }}</div></div>
                <form method="POST" action="{{ route('bhw.walk-in-patients.restore', $p->id) }}">@csrf<button class="btn btn-sm btn-success"><i class="bi bi-arrow-counterclockwise me-1"></i> Restore</button></form>
            </div>
        @empty
            <div class="p-4 text-center text-muted">Trash is empty.</div>
        @endforelse
    </div></div>
    <div class="mt-3">{{ $patients->links() }}</div>
</div>
@endsection
