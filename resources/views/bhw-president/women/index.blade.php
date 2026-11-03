@extends('bhw-president.layout')

@section('title', 'All Women - BHW President Portal | ReproCare')

@push('styles')
@include('includes.women-directory-styles')
<style>
    .pw-directory-toolbar { padding:0.75rem 0.85rem; }
    .pw-directory-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; }
    .pw-directory-heading h2 { margin:0 0 0.2rem; color:var(--text); font-size:1.12rem; font-weight:800; }
    .pw-directory-heading p { margin:0; color:var(--text-muted); font-size:0.84rem; }
    .pw-total-chip { display:inline-flex; align-items:center; gap:0.4rem; flex:0 0 auto; padding:0.45rem 0.85rem; border-radius:999px; background:var(--color-surface-soft); color:var(--color-text); font-size:0.76rem; font-weight:800; }
    .pw-search-bar { display:flex; align-items:center; gap:0.45rem; flex-wrap:wrap; margin-top:0.85rem; }
    .pw-search-field, .pw-filter-field { display:flex; align-items:center; gap:0.55rem; min-height:40px; padding:0.5rem 0.8rem; border:1px solid var(--color-border); border-radius:12px; background:var(--color-surface-soft); }
    .pw-search-field { flex:0 0 220px; width:220px; min-width:200px; }
    .pw-filter-field { flex:0 0 145px; width:145px; min-width:0; }
    .pw-search-field > i, .pw-filter-field > i { flex:0 0 auto; color:var(--color-text-muted); }
    .pw-search-field input, .pw-filter-field select { width:100%; min-width:0; border:0; outline:0; background:transparent; color:var(--color-text); font-size:0.82rem; }
    .pw-search-field input::placeholder { color:var(--color-text-muted); }
    .pw-search-actions { display:flex; gap:0.45rem; flex-wrap:nowrap; }
    .pw-search-actions .btn { min-width:86px; min-height:38px; padding:0.38rem 0.75rem !important; font-size:0.76rem !important; white-space:nowrap; }
    .pw-status { display:inline-block; padding:0.25rem 0.7rem; border-radius:999px; font-size:0.7rem; font-weight:800; white-space:nowrap; }
    .pw-status-approved { background:var(--color-success-soft); color:var(--color-success-text); }
    .pw-status-pending { background:var(--color-warning-soft, #fff7e6); color:var(--color-warning-text, #8a5a00); }
    @media (max-width: 767.98px) {
        .wd-wrap .card-header { padding:0.65rem 0.7rem !important; gap:0.5rem !important; }
        .pw-directory-toolbar { padding:0.65rem; }
        .pw-directory-heading { align-items:flex-start; }
        .pw-directory-heading h2 { font-size:0.95rem; }
        .pw-directory-heading p { font-size:0.72rem; }
        .pw-total-chip { padding:0.38rem 0.65rem; font-size:0.68rem; }
        .pw-search-bar { gap:0.5rem; }
        .pw-search-field, .pw-filter-field { min-height:44px; padding:0.6rem 0.75rem; border-radius:12px; }
        .pw-search-field, .pw-filter-field { flex:1 1 100%; width:100%; min-width:0; }
        .pw-search-actions { width:100%; gap:0.5rem; }
        .pw-search-actions .btn { flex:1 1 0; min-height:40px; justify-content:center; }
        .wd-count { font-size:0.7rem; }
        .wd-wrap .table { font-size:0.7rem !important; }
        .wd-wrap .table th, .wd-wrap .table td { padding:0.4rem 0.5rem !important; }
        .wd-wrap .table .badge { font-size:0.6rem !important; white-space:nowrap !important; }
        .wd-wrap .card-footer { font-size:0.7rem !important; padding:0.6rem !important; }
    }
</style>
@endpush

@section('bhw-president-content')
<div class="card fade-in-card wd-wrap">
    <div class="card-header bg-transparent p-3">
        <div class="pw-directory-toolbar">
            <div class="pw-directory-heading">
                <div>
                    <h2>All Women</h2>
                    <p>Registered women living in your assigned barangay.</p>
                </div>
                <span class="pw-total-chip"><i class="bi bi-people-fill" aria-hidden="true"></i>{{ $women->total() }} total</span>
            </div>
            <form method="GET" action="{{ route('bhw-president.women.index') }}" class="pw-search-bar" role="search">
                <div class="pw-search-field">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" id="pwSearch" name="search" value="{{ $search }}" placeholder="Search by name, email, or barangay..." aria-label="Search by name, email, or barangay">
                </div>
                <div class="pw-filter-field">
                    <i class="bi bi-funnel" aria-hidden="true"></i>
                    <select name="status" aria-label="Account status">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="pw-search-actions">
                    <button type="submit" class="btn wd-apply-btn"><i class="bi bi-funnel-fill me-1" aria-hidden="true"></i>Apply</button>
                    <a href="{{ route('bhw-president.women.index') }}" class="btn wd-clear-btn"><i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>Reset</a>
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
                                    @php($womanStatus = strtolower($woman->status ?? 'approved'))
                                    <span class="pw-status pw-status-{{ $womanStatus }}">{{ ucfirst($womanStatus) }}</span>
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
                <p class="text-muted mb-2">{{ $search !== '' || $status !== 'all' ? 'No women match the selected filters in your barangay.' : 'No registered women live in your assigned barangay yet.' }}</p>
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
