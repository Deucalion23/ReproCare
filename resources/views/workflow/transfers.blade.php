@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Patient Transfer Requests</h4>
    <p class="text-muted">Barangay-to-barangay / purok reassignment. Approval preserves full pregnancy &amp; checkup history — no deletes, no duplicates.</p>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="table-responsive">
    <table class="table table-striped">
        <thead><tr><th>#</th><th>Patient</th><th>From</th><th>To</th><th>Reason</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        @forelse ($transfers as $t)
            <tr>
                <td>{{ $t->id }}</td>
                <td>{{ $t->patient_name }}</td>
                <td>{{ $t->from_barangay }} / {{ $t->fromPurok?->name }}</td>
                <td>{{ $t->to_barangay }} / {{ $t->toPurok?->name }} → {{ $t->toBhw?->name }}</td>
                <td>{{ $t->reason }}</td>
                <td><span class="badge bg-secondary">{{ $t->status }}</span></td>
                <td>
                    @if ($t->status === 'pending')
                        <form method="POST" action="{{ route('workflow.transfers.approve', $t->id) }}" class="d-inline">@csrf<input name="notes" class="form-control form-control-sm mb-1" placeholder="Approval note (optional)"><button class="btn btn-success btn-sm">Approve</button></form>
                        <form method="POST" action="{{ route('workflow.transfers.reject', $t->id) }}" class="d-inline">@csrf<input name="notes" class="form-control form-control-sm mb-1" placeholder="Rejection reason (required)" required><button class="btn btn-danger btn-sm">Reject</button></form>
                    @else
                        <small class="text-muted">{{ $t->reviewer_notes }}</small>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted">No transfer requests.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    {{ $transfers->links() }}
</div>
@endsection
