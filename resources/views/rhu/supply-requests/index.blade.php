@extends('rhu.layout')

@section('title', 'Supply Requests - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Supply Requests
            </div>
            <p class="page-hero-subtitle">
                Track and submit requests to the City Health Office (CHO) for clinic supplies.
            </p>
        </div>
        <a href="{{ route('rhu.supply-requests.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Request
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:12px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card fade-in-card">
    <div class="card-body p-0">
        @if($requests->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Item Name</th>
                            <th>Category</th>
                            <th>Qty Requested</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <th>Exp. Delivery Date</th>
                            <th>Submitted Date</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-700" style="color:var(--text);">{{ $req->supply_name }}</span>
                                </td>
                                <td>
                                    <span style="font-size:0.875rem;">{{ str_replace('_', ' ', ucfirst($req->supply_category)) }}</span>
                                </td>
                                <td>
                                    <span style="font-weight:600;">{{ $req->quantity_requested }} {{ $req->unit ?? 'pcs' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $req->urgency === 'emergency' ? 'danger' : ($req->urgency === 'urgent' ? 'warning' : 'info') }} text-white text-xs">
                                        {{ ucfirst($req->urgency) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $req->status === 'approved' ? 'success' : ($req->status === 'declined' ? 'danger' : 'secondary') }} text-white text-xs">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </td>
                                <td style="font-size:0.875rem;">
                                    {{ $req->expected_delivery_date ? $req->expected_delivery_date->format('M j, Y') : 'Pending review' }}
                                </td>
                                <td style="font-size:0.8rem; color:var(--text-muted);">
                                    {{ $req->submitted_at ? $req->submitted_at->format('M j, Y \a\t g:i A') : 'N/A' }}
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('rhu.supply-requests.show', $req->id) }}" class="btn btn-xs btn-outline-primary" style="font-size:0.75rem;">
                                        <i class="bi bi-eye"></i> Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="card-footer bg-transparent border-top">
                    {{ $requests->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-box-seam" style="font-size:3rem; color:var(--text-muted);"></i>
                <h5 class="mt-3">No Supply Requests Found</h5>
                <p class="text-muted text-xs">Submit supply replenishment requests to keep inventory stocked.</p>
            </div>
        @endif
    </div>
</div>

@endsection
