@extends('midwife.layout')

@section('title', 'Pregnant Women - ReproCare')

@section('midwife-content')

{{-- ═══════════════════════════════
     PAGE HERO
═══════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h1 class="page-hero-title">Pregnant Women
            </h1>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Monitor active prenatal cases, high-risk triage, and expected deliveries
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('midwife.pregnancies.index') }}" class="btn-hero-secondary">
                <i class="bi bi-eye-fill me-1"></i> View All Pregnancies
            </a>
            <a href="{{ route('midwife.pregnancies.create') }}" class="btn-hero-primary">
                <i class="bi bi-plus-circle-fill me-1"></i> Add Pregnancy
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     STAT CARDS (WHITE THEME)
═══════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card fade-in-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:var(--color-primary-soft); color:var(--color-primary-text);">
                    <i class="bi bi-heart-pulse-fill"></i>
                </div>
                <div>
                    <div class="stat-number">{{ $totalActive }}</div>
                    <div class="stat-label">Active Cases</div>
                </div>
            </div>
            <div class="mt-2 text-muted fw-semibold" style="font-size:0.75rem;">
                <i class="bi bi-check2-circle text-primary me-1"></i> Currently under prenatal monitoring
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card fade-in-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:var(--color-success-soft); color:var(--color-success-text);">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="stat-number">{{ $totalCompleted }}</div>
                    <div class="stat-label">Delivered Cases</div>
                </div>
            </div>
            <div class="mt-2 text-muted fw-semibold" style="font-size:0.75rem;">
                <i class="bi bi-check2-all text-success me-1"></i> Successful deliveries recorded
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card fade-in-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:var(--color-danger-soft); color:var(--color-secondary-text);">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <div class="stat-number">{{ $totalHighRisk }}</div>
                    <div class="stat-label">High-Risk Triage</div>
                </div>
            </div>
            <div class="mt-2 text-danger fw-semibold" style="font-size:0.75rem;">
                <i class="bi bi-exclamation-octagon me-1"></i> Requires urgent clinical attention
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     FILTER TABS & CONTENT
═══════════════════════════════ --}}
<div class="card fade-in-card">
    <div class="card-header p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            {{-- Filter Pills --}}
            <div class="d-flex gap-2">
                <a href="{{ route('midwife.pregnant-patients', ['status' => 'active']) }}" 
                   class="btn btn-sm {{ $status === 'active' ? 'btn-primary' : 'btn-light border' }}"
                   style="border-radius:20px; font-weight:600; padding:0.4rem 1rem;">
                    <i class="bi bi-heart-pulse me-1"></i> Active Cases
                </a>
                <a href="{{ route('midwife.pregnant-patients', ['status' => 'completed']) }}" 
                   class="btn btn-sm {{ $status === 'completed' ? 'btn-primary' : 'btn-light border' }}"
                   style="border-radius:20px; font-weight:600; padding:0.4rem 1rem;">
                    <i class="bi bi-check-circle me-1"></i> Completed
                </a>
                <a href="{{ route('midwife.pregnant-patients', ['status' => 'high-risk']) }}" 
                   class="btn btn-sm {{ $status === 'high-risk' ? 'btn-danger' : 'btn-light border' }}"
                   style="border-radius:20px; font-weight:600; padding:0.4rem 1rem;">
                    <i class="bi bi-exclamation-triangle me-1"></i> High Risk
                </a>
            </div>
            
            {{-- Search Bar --}}
            <form action="{{ route('midwife.pregnant-patients') }}" method="GET" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="position-relative" style="width:260px;">
                    <i class="bi bi-search position-absolute" 
                       style="left:0.85rem; top:50%; transform:translateY(-50%); color:var(--color-text-muted); font-size:0.9rem;"></i>
                    <input type="text" name="search" class="form-control form-control-sm" 
                           style="border-radius:12px; border:1.5px solid var(--color-border); padding:0.5rem 0.85rem 0.5rem 2.25rem; font-size:0.88rem;"
                           placeholder="Search patient name or email..." value="{{ $search ?? '' }}">
                </div>
                @if($search)
                    <a href="{{ route('midwife.pregnant-patients', ['status' => $status]) }}" 
                       class="btn btn-sm btn-light border" style="border-radius:10px;">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>
    
    <div class="card-body p-0">
        @if($pregnancies->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                    <thead>
                        <tr class="text-muted text-uppercase" style="font-size:0.78rem; font-weight:700; border-bottom:1px solid var(--color-border);">
                            <th class="ps-4 py-3">Woman Patient</th>
                            <th class="py-3">Barangay</th>
                            <th class="py-3">Clinical Triage</th>
                            <th class="py-3">Estimated Due Date</th>
                            <th class="py-3 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pregnancies as $pregnancy)
                            <tr style="border-bottom:1px solid var(--color-border);">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width:40px;height:40px;border-radius:12px;background:var(--color-primary-soft);border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--color-primary-text);flex-shrink:0;">
                                            {{ strtoupper(substr($pregnancy->patient_name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="color:var(--color-text);">{{ $pregnancy->patient_name ?? 'Unknown patient' }}</div>
                                            <small class="text-muted">{{ optional($pregnancy->woman)->email ?? 'No email' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    @if(optional($pregnancy->woman)->barangay ?? optional($pregnancy->walkInPatient)->barangay)
                                        <span class="badge bg-light text-dark border px-2 py-1" style="border-radius:8px;">
                                            <i class="bi bi-geo-alt-fill me-1 text-primary"></i>Brgy. {{ optional($pregnancy->woman)->barangay ?? optional($pregnancy->walkInPatient)->barangay }}
                                        </span>
                                    @else
                                        <span style="color:var(--color-text-muted);">—</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if($pregnancy->status === 'high_risk')
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-danger-soft); border:1px solid var(--color-secondary-soft); color:var(--color-secondary-text);">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> High Risk
                                        </span>
                                        <small class="d-block text-muted mt-1">{{ $pregnancy->formatted_aog }}</small>
                                    @elseif($pregnancy->is_active)
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text);">
                                            <i class="bi bi-heart-pulse-fill me-1"></i> Active
                                        </span>
                                        <small class="d-block text-muted mt-1">{{ $pregnancy->formatted_aog }}</small>
                                    @else
                                        <span class="badge rounded-pill bg-light text-muted border font-semibold px-2.5 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i> Completed
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if($pregnancy->edd)
                                        <div class="fw-bold" style="color:var(--color-text);">{{ $pregnancy->edd->format('M d, Y') }}</div>
                                        @if($pregnancy->is_active)
                                            <small style="color:var(--color-success-text); font-weight:600;"><i class="bi bi-hourglass-split me-1"></i>{{ $pregnancy->days_until_due }} days left</small>
                                        @endif
                                    @else
                                        <span style="color:var(--color-text-muted);">—</span>
                                    @endif
                                </td>
                                <td class="py-3 text-end pe-4">
                                    <div class="d-flex gap-2 justify-content-end">
                                        @if($pregnancy->user_id && $pregnancy->woman)
                                        <a href="{{ route('midwife.patient-details', $pregnancy->user_id) }}" 
                                           class="btn btn-sm btn-light border" 
                                           style="border-radius:8px;"
                                           title="View Patient Record">
                                            <i class="bi bi-person me-1"></i> Patient
                                        </a>
                                        @endif
                                        <a href="{{ route('midwife.pregnancies.show', $pregnancy->id) }}" 
                                           class="btn btn-sm btn-primary" 
                                           style="border-radius:8px; font-weight:600;"
                                           title="View Pregnancy Details">
                                            <i class="bi bi-eye me-1"></i> Case
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-top">
                {{ $pregnancies->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-5">
                <div style="width:64px;height:64px;border-radius:18px;background:var(--color-primary-soft);color:var(--color-primary-text);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-heart-pulse" style="font-size:1.75rem;"></i>
                </div>
                <h5 class="fw-bold mb-1" style="color:var(--color-text);">No pregnancies found</h5>
                <p class="text-muted mb-3" style="font-size:0.9rem;">There are no pregnancy records matching your current filter criteria.</p>
                <a href="{{ route('midwife.pregnancies.create') }}" class="btn btn-primary" style="border-radius:10px; font-weight:700;">
                    <i class="bi bi-plus-circle me-1"></i> Add Pregnancy Record
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
