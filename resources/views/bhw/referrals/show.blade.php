@extends('bhw.layout')

@section('bhw-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h2 class="page-title">Referral Details</h2>
        <a href="{{ route('bhw.referrals.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Referrals
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Referral Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Patient:</strong>
                            <p>{{ $referral->patient_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Patient Type:</strong>
                            <p>
                                @if($referral->woman)
                                    Enrolled Patient
                                @elseif($referral->walkInPatient)
                                    Unlinked Patient
                                @else
                                    Unknown
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Reason for Referral:</strong>
                            <p>{{ $referral->reason }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Urgency:</strong>
                            <p>
                                @if($referral->urgency === 'emergency')
                                    <span class="badge bg-danger">Emergency</span>
                                @elseif($referral->urgency === 'urgent')
                                    <span class="badge bg-warning text-dark">Urgent</span>
                                @else
                                    <span class="badge bg-info">Routine</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Assigned Midwife:</strong>
                            <p>{{ $referral->assignedMidwife?->name ?? 'Not assigned' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <p>
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
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>BHW Notes:</strong>
                        <p>{{ $referral->bhw_notes ?? 'No notes provided' }}</p>
                    </div>

                    @if($referral->midwife_notes)
                        <div class="mb-3">
                            <strong>Midwife Notes:</strong>
                            <p>{{ $referral->midwife_notes }}</p>
                        </div>
                    @endif

                    <div class="row text-muted">
                        <div class="col-md-4">
                            <small>Created: {{ $referral->created_at->format('M d, Y g:i A') }}</small>
                        </div>
                        @if($referral->reviewed_at)
                            <div class="col-md-4">
                                <small>Reviewed: {{ $referral->reviewed_at->format('M d, Y g:i A') }}</small>
                            </div>
                        @endif
                        @if($referral->scheduled_at)
                            <div class="col-md-4">
                                <small>Scheduled: {{ $referral->scheduled_at->format('M d, Y g:i A') }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($referral->convertedCheckup)
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Converted to Checkup</h5>
                    </div>
                    <div class="card-body">
                        <p>This referral has been converted to a scheduled checkup.</p>
                        <a href="{{ route('bhw.checkups.index') }}" class="btn btn-success">
                            <i class="bi bi-calendar-check"></i> View in Checkups
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            @if($referral->woman)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Patient Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $referral->woman->name }}</p>
                        <p><strong>Email:</strong> {{ $referral->woman->email }}</p>
                        <p><strong>Address:</strong> {{ $referral->woman->address ?? '—' }}</p>
                        <p><strong>Barangay:</strong> {{ $referral->woman->barangay ?? '—' }}</p>
                        <a href="{{ route('bhw.patient-details', $referral->woman->id) }}" class="btn btn-sm btn-outline-primary">
                            View Full Profile
                        </a>
                    </div>
                </div>
            @elseif($referral->walkInPatient)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Unlinked Patient Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $referral->walkInPatient->full_name }}</p>
                        <p><strong>Age:</strong> {{ $referral->walkInPatient->age ?? '—' }}</p>
                        <p><strong>Contact:</strong> {{ $referral->walkInPatient->contact_number ?? '—' }}</p>
                        <p><strong>Address:</strong> {{ $referral->walkInPatient->address ?? '—' }}</p>
                        <p><strong>Barangay:</strong> {{ $referral->walkInPatient->barangay ?? '—' }}</p>
                        <p><strong>Reason for Visit:</strong> {{ $referral->walkInPatient->reason_for_visit ?? '—' }}</p>
                        @if($referral->walkInPatient->converted_to_user_id)
                            <span class="badge bg-success">Converted to Enrolled Account</span>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Referral Timeline</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Created</strong>
                            <br>
                            <small class="text-muted">{{ $referral->created_at->format('M d, Y g:i A') }}</small>
                        </li>
                        @if($referral->reviewed_at)
                            <li class="list-group-item">
                                <strong>Reviewed by Midwife</strong>
                                <br>
                                <small class="text-muted">{{ $referral->reviewed_at->format('M d, Y g:i A') }}</small>
                            </li>
                        @endif
                        @if($referral->scheduled_at)
                            <li class="list-group-item">
                                <strong>Scheduled for Checkup</strong>
                                <br>
                                <small class="text-muted">{{ $referral->scheduled_at->format('M d, Y g:i A') }}</small>
                            </li>
                        @endif
                        @if($referral->completed_at)
                            <li class="list-group-item">
                                <strong>Checkup Completed</strong>
                                <br>
                                <small class="text-muted">{{ $referral->completed_at->format('M d, Y g:i A') }}</small>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
