@extends('cho.layout')

@section('title', 'Supply Requests - CHO Portal | ReproCare')

@section('cho-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Supply Requests
            </div>
            <p class="page-hero-subtitle" style="font-weight:600;">Review and approve supply replenishment requests submitted by Rural Health Units.</p>
        </div>
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
                            <th>Requested By</th>
                            <th>Quantity</th>
                            <th>Urgency</th>
                            <th>Status</th>
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
                                    <span style="font-size:0.875rem;">{{ $req->requestedBy->name ?? 'Unknown' }}</span>
                                    <div style="font-size:0.75rem; color:var(--text-muted);">{{ $req->requestedBy->rhu_assignment ?? 'RHU Unit' }}</div>
                                </td>
                                <td>
                                    <span style="font-weight:600;">{{ $req->quantity_requested }} {{ $req->unit ?? 'pcs' }}</span>
                                </td>
                                <td>
                                    @php
                                        $urgBadge = match($req->urgency) {
                                            'emergency' => 'danger',
                                            'urgent' => 'warning',
                                            'routine' => 'info',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $urgBadge }} text-white text-xs">
                                        {{ ucfirst($req->urgency) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusBadge = match($req->status) {
                                            'approved' => 'success',
                                            'declined' => 'danger',
                                            'submitted' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusBadge }} text-white text-xs">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </td>
                                <td style="font-size:0.8rem; color:var(--text-muted);">
                                    {{ $req->submitted_at ? $req->submitted_at->format('M j, Y \a\t g:i A') : 'N/A' }}
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('cho.supply-requests.show', $req->id) }}" class="btn btn-xs btn-view text-white py-1 px-3" style="font-size:0.75rem; border-radius:8px;">
                                        Review Request
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
                <p class="text-muted text-xs">No supply requests have been registered in the system.</p>
            </div>
        @endif
    </div>
</div>

@endsection
