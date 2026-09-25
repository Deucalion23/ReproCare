@extends('bhw.layout')

@section('bhw-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h2 class="page-title">Checkup Referrals</h2>
        <a href="{{ route('bhw.referrals.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Referral
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($referrals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Reason</th>
                                <th>Urgency</th>
                                <th>Assigned Midwife</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referrals as $referral)
                                <tr>
                                    <td>{{ $referral->patient_name }}</td>
                                    <td>{{ $referral->reason }}</td>
                                    <td>
                                        @if($referral->urgency === 'emergency')
                                            <span class="badge bg-danger">Emergency</span>
                                        @elseif($referral->urgency === 'urgent')
                                            <span class="badge bg-warning text-dark">Urgent</span>
                                        @else
                                            <span class="badge bg-info">Routine</span>
                                        @endif
                                    </td>
                                    <td>{{ $referral->assignedMidwife?->name ?? '—' }}</td>
                                    <td>
                                        @if($referral->status === 'pending')
                                            <span class="badge bg-secondary">Pending</span>
                                        @elseif($referral->status === 'reviewed')
                                            <span class="badge bg-primary">Reviewed</span>
                                        @elseif($referral->status === 'scheduled')
                                            <span class="badge bg-success">Scheduled</span>
                                        @elseif($referral->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($referral->status === 'declined')
                                            <span class="badge bg-danger">Declined</span>
                                        @endif
                                    </td>
                                    <td>{{ $referral->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('bhw.referrals.show', $referral->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $referrals->links() }}
            @else
                <div class="text-center py-5">
                    <i class="bi bi-clipboard-data fs-1 text-muted mb-3"></i>
                    <p class="text-muted">No referrals found.</p>
                    <a href="{{ route('bhw.referrals.create') }}" class="btn btn-primary">
                        Create Your First Referral
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
