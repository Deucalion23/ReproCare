@extends('midwife.layout')

@section('title', 'Walk-in Women - ReproCare')

@section('midwife-content')

{{-- ═══════════════════════════════
     PAGE HERO
═══════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h1 class="page-hero-title">Walk-in Women
            </h1>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; View and manage walk-in consultation patient records
            </p>
        </div>
        <div class="d-flex gap-2">
            <span class="btn-hero-secondary">
                <i class="bi bi-people-fill me-1 text-primary"></i> {{ $patients->total() ?? 0 }} Total Walk-ins
            </span>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" 
         style="background:color-mix(in srgb, var(--color-success) 12%, transparent); border:1px solid color-mix(in srgb, var(--color-success) 30%, transparent); color:var(--color-success-text); border-radius:14px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card fade-in-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold" style="color:var(--color-text); font-family:'Plus Jakarta Sans',sans-serif;">Walk-in Patient Records
        </h5>
        <span class="badge rounded-pill px-3 py-1 fw-bold" style="background:var(--color-primary-soft); border:1px solid var(--color-border); color:var(--color-primary-text); font-size:0.8rem;">
            {{ $patients->total() ?? 0 }} Records
        </span>
    </div>
    <div class="card-body p-0">
        @if($patients->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size:0.78rem; font-weight:700; border-bottom:1px solid var(--color-border);">
                            <th class="ps-4 py-3">Patient Name</th>
                            <th class="py-3">Age</th>
                            <th class="py-3">Contact</th>
                            <th class="py-3">Barangay</th>
                            <th class="py-3">Recorded By</th>
                            <th class="py-3">Record Type</th>
                            <th class="py-3">Recorded Date</th>
                            <th class="py-3 text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patients as $patient)
                            <tr style="border-bottom:1px solid var(--color-border);">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width:38px;height:38px;border-radius:12px;background:var(--color-primary-soft);border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;color:var(--color-primary-text);font-weight:700;flex-shrink:0;">
                                            {{ strtoupper(substr($patient->full_name, 0, 1)) }}
                                        </div>
                                        <div class="fw-bold" style="color:var(--color-text);">{{ $patient->full_name }}</div>
                                    </div>
                                </td>
                                <td class="py-3 fw-semibold" style="color:var(--color-text);">{{ $patient->age ? $patient->age . ' yrs' : '—' }}</td>
                                <td class="py-3" style="color:var(--color-text);">
                                    @if($patient->contact_number)
                                        <a href="tel:{{ $patient->contact_number }}" style="color:var(--color-primary-text); text-decoration:none;">{{ $patient->contact_number }}</a>
                                    @else
                                        <span style="color:var(--color-text-muted);">—</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if($patient->barangay)
                                        <span class="badge bg-light text-dark border px-2 py-1" style="border-radius:8px;">
                                            Brgy. {{ $patient->barangay }}
                                        </span>
                                    @else
                                        <span style="color:var(--color-text-muted);">—</span>
                                    @endif
                                </td>
                                <td class="py-3" style="color:var(--color-text);">{{ $patient->recordedBy?->name ?? 'RHU Staff' }}</td>
                                <td class="py-3">
                                    @if($patient->isPortalActive())
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text);" title="Authenticated Patient · Direct Access">
                                            <i class="bi bi-person-check-fill me-1"></i> Enrolled · Portal-Active
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-peach-soft); border:1px solid var(--color-peach-soft); color:var(--color-peach-text);" title="Managed Beneficiary · Field Record Only">
                                            <i class="bi bi-person-walking me-1"></i> Unlinked · BHW-Managed
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 text-muted">{{ $patient->created_at->format('M d, Y') }}</td>
                                <td class="py-3 text-end pe-4">
                                    <a href="{{ route('midwife.walk-in-patients.show', $patient->id) }}" class="btn btn-sm btn-light border" style="border-radius:8px; font-weight:600;">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-top">
                {{ $patients->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-5">
                <div style="width:64px;height:64px;border-radius:18px;background:var(--color-primary-soft);color:var(--color-primary-text);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-person-walking" style="font-size:1.75rem;"></i>
                </div>
                <h5 class="fw-bold mb-1" style="color:var(--color-text);">No Walk-in Women</h5>
                <p class="text-muted mb-0" style="font-size:0.9rem;">There are currently no walk-in patient entries recorded.</p>
            </div>
        @endif
    </div>
</div>
@endsection
