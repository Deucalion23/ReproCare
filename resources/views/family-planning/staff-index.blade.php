@extends(auth()->user()->role === 'midwife' ? 'midwife.layout' : 'bhw.layout')
@section(auth()->user()->role === 'midwife' ? 'midwife-content' : 'bhw-content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4"><div><h1 class="h3 mb-1">Family Planning</h1><p class="text-muted mb-0">Private counseling, resupply, and follow-up tracking.</p></div><a class="btn btn-primary" href="{{ route(auth()->user()->role === 'midwife' ? 'midwife.family-planning.create' : 'bhw.family-planning.create') }}"><i class="bi bi-plus-lg me-1"></i> Add service record</a></div>
@if(session('success'))<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>@endif
@if($pendingCount || $dueCount)
    <div class="rc-indicator-row">
        @if($pendingCount)<span class="rc-indicator ind-info"><i class="bi bi-chat-heart"></i>{{ $pendingCount }} counseling request(s) to review</span>@endif
        @if($dueCount)<span class="rc-indicator ind-warning"><i class="bi bi-calendar-x"></i>{{ $dueCount }} follow-up(s) due or overdue</span>@endif
    </div>
@endif
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Client</th><th>Preference / method</th><th>Status</th><th>Follow-up</th><th></th></tr></thead><tbody>
@forelse($records as $record)
<tr class="{{ $record->status === 'requested' ? 'table-warning' : '' }}"><td><strong>{{ $record->client->name }}</strong><br><small class="text-muted">{{ $record->client->barangay }}</small></td><td>@if(! $record->method_given && $record->status === 'requested')<span class="badge text-bg-warning">Pending counseling</span>@else{{ \App\Models\FamilyPlanningRecord::methodLabel($record->method_given ?: $record->preferred_method) }}@endif</td><td><span class="badge text-bg-{{ $record->status === 'active' ? 'success' : ($record->status === 'requested' ? 'warning' : ($record->status === 'referred' ? 'info' : 'secondary')) }}">{{ ucfirst($record->status) }}</span></td><td>{{ $record->next_follow_up?->format('M j, Y') ?? '—' }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route(auth()->user()->role === 'midwife' ? 'midwife.family-planning.edit' : 'bhw.family-planning.edit', $record) }}">Review</a></td></tr>
@empty<tr><td colspan="5" class="text-center text-muted py-4">No records in your service area.</td></tr>@endforelse
</tbody></table></div></div>{{ $records->links() }}
@endsection
