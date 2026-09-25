@extends('cho.layout')

@section('title', 'Supply Request Details - CHO Portal | ReproCare')

@section('cho-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Supply Request Review
            </div>
            <p class="page-hero-subtitle">
                Review, approve, or decline supply requests from health centers.
            </p>
        </div>
        <a href="{{ route('cho.supply-requests.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Supply Requests
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:12px;">
        <ul class="mb-0 text-xs">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    {{-- Request info --}}
    <div class="col-lg-6">
        <div class="card fade-in-card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">Request Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Item Name</div>
                        <div class="fw-700" style="font-size:1.1rem; color:var(--text);">{{ $supplyRequest->supply_name }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Category</div>
                        <div class="fw-600" style="color:var(--text);">{{ str_replace('_', ' ', ucfirst($supplyRequest->supply_category)) }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Quantity Requested</div>
                        <div class="fw-700 text-primary" style="font-size:1.05rem;">{{ $supplyRequest->quantity_requested }} {{ $supplyRequest->unit ?? 'pcs' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Urgency Level</div>
                        <span class="badge bg-{{ $supplyRequest->urgency === 'emergency' ? 'danger' : ($supplyRequest->urgency === 'urgent' ? 'warning' : 'info') }} text-white text-xs py-1 px-2.5">
                            {{ ucfirst($supplyRequest->urgency) }}
                        </span>
                    </div>

                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Requested By</div>
                        <div class="fw-600" style="color:var(--text);">{{ $supplyRequest->requestedBy->name ?? 'Unknown' }}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted);">{{ $supplyRequest->requestedBy->rhu_assignment ?? 'RHU Station' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Submitted Date</div>
                        <div style="font-size:0.875rem; color:var(--text);">{{ $supplyRequest->submitted_at ? $supplyRequest->submitted_at->format('F j, Y \a\t g:i A') : 'N/A' }}</div>
                    </div>

                    <div class="col-12 border-top pt-3">
                        <div class="text-muted text-xs mb-1">Reason / Notes from RHU</div>
                        <p style="font-size:0.875rem; color:var(--text); line-height:1.5; background:var(--bg-card2); padding:0.85rem; border-radius:10px; border:1px solid var(--border);" class="mb-0">
                            {{ $supplyRequest->reason ?? 'No specific reasoning provided.' }}
                        </p>
                    </div>

                    @if($supplyRequest->status !== 'submitted')
                        <div class="col-12 border-top pt-3">
                            <div class="text-muted text-xs mb-1">Review Details</div>
                            <div style="background:var(--bg-card2); padding:0.85rem; border-radius:10px; border:1px solid var(--border);">
                                <div style="font-size:0.875rem; color:var(--text); mb-1"><strong>Status:</strong> {{ ucfirst($supplyRequest->status) }}</div>
                                <div style="font-size:0.875rem; color:var(--text); mb-1"><strong>Reviewed By:</strong> {{ $supplyRequest->approvedBy->name ?? 'Unknown' }}</div>
                                @if($supplyRequest->expected_delivery_date)
                                    <div style="font-size:0.875rem; color:var(--text); mb-1"><strong>Expected Delivery:</strong> {{ $supplyRequest->expected_delivery_date->format('F j, Y') }}</div>
                                @endif
                                <div style="font-size:0.875rem; color:var(--text-muted); margin-top:0.4rem; font-style:italic;">
                                    "{{ $supplyRequest->cho_notes ?? 'No review notes provided.' }}"
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Review actions --}}
    @if($supplyRequest->status === 'submitted')
        <div class="col-lg-6">
            <div class="card fade-in-card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">Review Actions
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cho.supply-requests.approve', $supplyRequest->id) }}" class="mb-4">
                        @csrf
                        <h6 class="fw-700 mb-3" style="color:var(--text);">Approve Supply Request</h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Expected Delivery Date (Optional)</label>
                            <input type="date" name="expected_delivery_date" class="form-control" min="{{ date('Y-m-d') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Approval Notes (Optional)</label>
                            <textarea name="cho_notes" rows="3" class="form-control" placeholder="e.g. Approved for dispatch, delivery scheduled."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success text-white w-100">
                            <i class="bi bi-check-circle me-1"></i> Approve & Schedule Delivery
                        </button>
                    </form>

                    <div style="border-top:1px solid var(--border); padding-top:1.5rem;">
                        <h6 class="fw-700 mb-3 text-danger">Decline Supply Request</h6>
                        <form method="POST" action="{{ route('cho.supply-requests.decline', $supplyRequest->id) }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="form-label required-label">Reason for Declining (Required)</label>
                                <textarea name="cho_notes" rows="3" class="form-control" placeholder="e.g. Out of stock / request details need clarification." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-danger text-white w-100">
                                <i class="bi bi-x-circle me-1"></i> Decline Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col-lg-6">
            <div class="card fade-in-card h-100 d-flex flex-column align-items-center justify-content-center text-center p-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size:3.5rem;"></i>
                <h5 class="mt-3 fw-700">Request Processed</h5>
                <p class="text-muted text-xs max-w-280">
                    This supply request has already been reviewed and its status is finalized.
                </p>
            </div>
        </div>
    @endif
</div>

@endsection
