@extends('bhw-president.layout')

@section('title', 'All Women - BHW President Portal | ReproCare')

@push('styles')
<style>
    @media (max-width: 767.98px) {
        .page-hero { padding:0.8rem 0.9rem !important; border-radius:14px !important; margin-bottom:0.8rem !important; }
        .page-hero .page-hero-title { font-size:0.95rem !important; }
        .page-hero .page-hero-subtitle { font-size:0.7rem !important; }
        .card { border-radius:14px !important; margin-bottom:0.8rem !important; }
        .card-header { padding:0.65rem 0.8rem !important; gap:0.5rem !important; flex-wrap:wrap !important; }
        .card-header h5 { font-size:0.82rem !important; }
        .card-header .btn { font-size:0.7rem !important; padding:0.4rem 0.75rem !important; }
        .card-body { padding:0.7rem 0.8rem !important; }
        /* Search bar + button stay side by side on one row. */
        .pw-search-card { margin-bottom:0.7rem !important; }
        .pw-search-card .card-body { padding:0.6rem 0.7rem !important; }
        .pw-search-form { display:flex !important; gap:0.4rem !important; align-items:flex-end !important; flex-wrap:nowrap !important; }
        .pw-search-form .pw-search-field { flex:1 1 auto !important; min-width:0 !important; width:auto !important; }
        .pw-search-form .pw-search-btns { flex:0 0 auto !important; width:auto !important; }
        .pw-search-form .pw-search-btns .btn { padding-left:0.7rem !important; padding-right:0.7rem !important; }
        .pw-search-form .pw-search-label { display:none !important; }
        .card-body .form-label { font-size:0.66rem !important; margin-bottom:0.2rem !important; }
        .card-body .form-control { font-size:0.74rem !important; min-height:2.375rem; }
        .card-body .btn { font-size:0.7rem !important; }
        .table { font-size:0.7rem !important; }
        .table th, .table td { padding:0.4rem 0.5rem !important; }
        .table .pw-patient-name { white-space:nowrap !important; font-size:0.7rem !important; }
        .table .pw-patient-sub { white-space:nowrap !important; overflow:hidden !important; text-overflow:ellipsis !important; max-width:6.5rem; font-size:0.6rem !important; }
        .table .badge { font-size:0.6rem !important; white-space:nowrap !important; }
        .table .btn {
            font-size:0.66rem !important; padding:0.32rem 0.6rem !important; white-space:nowrap !important;
            text-align:center !important; display:inline-flex !important;
            align-items:center !important; justify-content:center !important;
        }
        .card-footer { font-size:0.7rem !important; padding:0.6rem !important; }
        .text-center.py-5 { padding-top:1.25rem !important; padding-bottom:1.25rem !important; }
        .text-center h6 { font-size:0.85rem !important; }
        .text-center p { font-size:0.72rem !important; }
    }
</style>
@endpush

@section('bhw-president-content')

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">All Women</div>
            <p class="page-hero-subtitle">Registered women living in your assigned barangay.</p>
        </div>
    </div>
</div>

<div class="card fade-in-card mb-4 pw-search-card">
    <div class="card-body">
        <form method="GET" action="{{ route('bhw-president.women.index') }}" class="row g-3 align-items-end pw-search-form">
            <div class="col-md-9 pw-search-field">
                <label class="form-label">Search Patient</label>
                <input type="text" name="search" class="form-control" placeholder="Search name, email, or barangay..." value="{{ $search }}">
            </div>
            <div class="col-md-3 d-flex gap-2 pw-search-btns">
                <button type="submit" class="btn btn-primary flex-fill" aria-label="Search"><i class="bi bi-search"></i><span class="pw-search-label"> Search</span></button>
                @if($search !== '')
                    <a href="{{ route('bhw-president.women.index') }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card fade-in-card">
    <div class="card-header">
        <h5 class="mb-0">Women in Your Barangay ({{ $women->total() }})</h5>
    </div>
    <div class="card-body p-0">
        @if($women->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-cards-mobile">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Barangay</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($women as $woman)
                            <tr>
                                <td class="no-card-label" data-label="Patient">
                                    <div class="d-flex align-items-center gap-2">
                                        <x-patient-avatar :patient="$woman" :size="26" />
                                        <div style="min-width:0;">
                                            <div class="pw-patient-name" style="font-weight:500;">{{ $woman->name ?? 'Unknown' }}</div>
                                            <small class="text-muted pw-patient-sub d-block">{{ $woman->email ?? 'No email' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Barangay">{{ $woman->barangay ?? 'N/A' }}</td>
                                <td data-label="Contact" style="white-space:nowrap;">{{ $woman->contact_number ?? '—' }}</td>
                                <td data-label="Status">
                                    <span class="badge bg-success">{{ ucfirst($woman->status ?? 'approved') }}</span>
                                </td>
                                <td class="text-center no-card-label" data-label="Actions">
                                    <a href="{{ route('profile.view', $woman->id) }}" class="btn btn-sm btn-outline-primary" title="View patient" aria-label="View patient">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $women->links() }}</div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-people empty-state-icon"></i>
                <h6>No women found</h6>
                <p>{{ $search !== '' ? 'No registered women match your search in your barangay.' : 'No registered women live in your assigned barangay yet.' }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
