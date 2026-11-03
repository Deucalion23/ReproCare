@extends('bhw-president.layout')

@section('title', 'All Women - BHW President Portal | ReproCare')

@push('styles')
@include('includes.women-directory-styles')
<style>
    .pw-women-tools { display:flex; flex-direction:column; align-items:stretch; gap:0.7rem; }
    .pw-women-tools .wd-tabs { gap:0.45rem; }
    .pw-women-tools .wd-tab { padding:0.4rem 0.9rem !important; font-size:0.76rem !important; }
    .pw-women-tools .wd-filters { width:100%; }
    .pw-women-tools .wd-search-field { flex:0 1 320px; min-width:220px; max-width:320px; }
    .pw-women-tools .wd-filters .wd-actions { margin-left:0; }
    @media (max-width: 767.98px) {
        .wd-wrap .card-header { padding:0.65rem 0.7rem !important; gap:0.5rem !important; }
        .wd-wrap .card-header .btn { font-size:0.7rem !important; padding:0.4rem 0.75rem !important; }
        .wd-count { font-size:0.7rem; }
        .pw-women-tools .wd-search-field { flex:1 1 100%; min-width:0; max-width:none; }
        .wd-wrap .table { font-size:0.7rem !important; }
        .wd-wrap .table th, .wd-wrap .table td { padding:0.4rem 0.5rem !important; }
        .wd-wrap .table .badge { font-size:0.6rem !important; white-space:nowrap !important; }
        .wd-wrap .card-footer { font-size:0.7rem !important; padding:0.6rem !important; }
    }
</style>
@endpush

@section('bhw-president-content')
<div class="page-hero fade-in-card">
    <div class="page-hero-title">All Women</div>
    <p class="page-hero-subtitle">Registered women living in your assigned barangay.</p>
</div>

<div class="card fade-in-card wd-wrap">
    <div class="card-header bg-transparent p-3">
        <div class="pw-women-tools">
            <div class="wd-tabs" role="group" aria-label="Women directory category">
                <span class="wd-tab active" aria-current="true"><i class="bi bi-people me-1" aria-hidden="true"></i>Registered ({{ $women->total() }})</span>
            </div>
            <form method="GET" action="{{ route('bhw-president.women.index') }}" class="wd-filters" role="search">
                <div class="wd-field wd-search-field">
                    <label for="pwSearch">Search</label>
                    <div class="wd-search">
                        <input type="search" id="pwSearch" name="search" class="form-control"
                               value="{{ $search }}" placeholder="Name, email, or barangay">
                        <button class="btn" type="submit" title="Search"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <div class="wd-actions d-flex gap-2">
                    <button type="submit" class="btn btn-sm wd-apply-btn">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if ($search !== '')
                        <a href="{{ route('bhw-president.women.index') }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                            <i class="bi bi-x-lg me-1"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 border-bottom">
            <span class="wd-count">
                Showing <strong>{{ $women->count() }}</strong> of <strong>{{ $women->total() }}</strong>
                {{ Str::plural('woman', $women->total()) }}
                @if ($search !== '')
                    <span class="text-muted">· results for "{{ $search }}"</span>
                @else
                    <span class="text-muted">· in your assigned barangay</span>
                @endif
            </span>
        </div>

        @if($women->count() > 0)
            <div class="table-responsive" style="overflow:visible;">
                <table class="table table-hover align-middle table-cards-mobile mb-0">
                    <thead>
                        <tr>
                            <th class="px-3 py-3">Patient</th>
                            <th class="py-3">Barangay</th>
                            <th class="py-3">Contact</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($women as $woman)
                            <tr>
                                <td class="no-card-label px-3" data-label="Patient">
                                    <div class="d-flex align-items-center gap-2">
                                        <x-patient-avatar :patient="$woman" :size="34" />
                                        <div style="min-width:0;">
                                            <div class="wd-name">{{ $woman->name ?? 'Unknown' }}</div>
                                            <div class="wd-sub">{{ $woman->email ?? 'No email' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="wd-cell-wrap" data-label="Barangay">{{ $woman->barangay ?? 'N/A' }}</td>
                                <td data-label="Contact" style="white-space:nowrap;">{{ $woman->contact_number ?? '—' }}</td>
                                <td data-label="Status">
                                    <span class="pill-badge badge-success">{{ ucfirst($woman->status ?? 'approved') }}</span>
                                </td>
                                <td class="no-card-label text-center" data-label="Actions">
                                    <a href="{{ route('profile.view', $woman->id) }}" class="btn btn-sm btn-outline-primary" title="View patient" aria-label="View patient">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($women->hasPages())
                <div class="p-3">{{ $women->links('vendor.pagination.arrows-only') }}</div>
            @endif
        @else
            <div class="empty-state py-4 text-center">
                <i class="bi bi-people empty-state-icon" style="font-size:2.5rem;"></i>
                <h6 class="mt-2 fw-bold">No women found</h6>
                <p class="text-muted mb-2">{{ $search !== '' ? 'No registered women match your search in your barangay.' : 'No registered women live in your assigned barangay yet.' }}</p>
                @if ($search !== '')
                    <a href="{{ route('bhw-president.women.index') }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset filters
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
