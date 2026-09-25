@extends('rhu.layout')

@section('title', 'Supply Request Details - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Supply Request Details
            </div>
            <p class="page-hero-subtitle">
                Track status and view audit feedback from City Health Office.
            </p>
        </div>
        <a href="{{ route('rhu.supply-requests.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Supply Requests
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8 mx-auto">
        <div class="card fade-in-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">Request Details
                </h5>
                <span class="badge bg-{{ $supplyRequest->status === 'approved' ? 'success' : ($supplyRequest->status === 'declined' ? 'danger' : 'warning') }} text-white text-xs py-1.5 px-3 rounded-pill">
                    Status: {{ ucfirst($supplyRequest->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Item Name</div>
                        <div class="fw-700 text-lg" style="color:var(--text);">{{ $supplyRequest->supply_name }}</div>
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
                        <div class="text-muted text-xs mb-1">Submitted Date</div>
                        <div style="font-size:0.875rem; color:var(--text);">{{ $supplyRequest->submitted_at ? $supplyRequest->submitted_at->format('F j, Y \a\t g:i A') : 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted text-xs mb-1">Expected Delivery Date</div>
                        <div class="fw-600 text-success" style="font-size:0.875rem;">
                            {{ $supplyRequest->expected_delivery_date ? $supplyRequest->expected_delivery_date->format('F j, Y') : 'Pending review' }}
                        </div>
                    </div>

                    <div class="col-12 border-top pt-3">
                        <div class="text-muted text-xs mb-1">Reason for Request</div>
                        <p style="font-size:0.875rem; color:var(--text); line-height:1.5; background:var(--bg-card2); padding:0.85rem; border-radius:10px; border:1px solid var(--border);" class="mb-0">
                            {{ $supplyRequest->reason ?? 'No explanation provided.' }}
                        </p>
                    </div>

                    @if($supplyRequest->status !== 'submitted')
                        <div class="col-12 border-top pt-3">
                            <div class="text-muted text-xs mb-1">City Health Office Feedback</div>
                            <div style="background:var(--bg-card2); padding:0.85rem; border-radius:10px; border:1px solid var(--border);">
                                <div style="font-size:0.875rem; color:var(--text); mb-1"><strong>Reviewed By:</strong> {{ $supplyRequest->approvedBy->name ?? 'CHO Officer' }}</div>
                                <div style="font-size:0.875rem; color:var(--text-muted); margin-top:0.4rem; font-style:italic;">
                                    "{{ $supplyRequest->cho_notes ?? 'No feedback comments recorded.' }}"
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
