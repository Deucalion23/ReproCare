@extends('user.layout')

@section('title', 'Family Planning - ReproCare')

@section('user-content')
<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div><div class="page-hero-title">Family Planning</div><p class="page-hero-subtitle mb-0">Private, informed choices for your health and plans.</p></div>
        <a class="btn btn-primary" href="{{ route('user.family-planning.create') }}"><i class="bi bi-chat-heart me-1"></i> Request counseling</a>
    </div>
</div>
<div class="alert alert-info mt-3"><i class="bi bi-shield-lock me-2"></i>Your request is private. A health worker will discuss options with you; this page does not replace a consultation.</div>
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
<div class="card mt-3"><div class="card-header fw-bold">My counseling and service requests</div><div class="card-body p-0">
@forelse($records as $record)
<div class="p-3 border-bottom">
    <div class="d-flex justify-content-between gap-2"><strong>{{ $record->preferred_method ? \App\Models\FamilyPlanningRecord::methodLabel($record->preferred_method) : 'Counseling request' }}</strong><span class="badge text-bg-{{ $record->status === 'active' ? 'success' : ($record->status === 'referred' ? 'warning' : 'secondary') }}">{{ ucfirst($record->status) }}</span></div>
    <small class="text-muted">{{ $record->created_at->format('M j, Y') }}@if($record->next_follow_up) · Follow-up: {{ $record->next_follow_up->format('M j, Y') }}@endif</small>
</div>
@empty <div class="p-4 text-center text-muted">No family-planning request yet. You may request a private counseling session anytime.</div> @endforelse
</div></div>
{{ $records->links() }}
@endsection
