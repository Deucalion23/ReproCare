@extends('rhu.layout')

@section('title', 'Health Record Tracking - RHU Portal | ReproCare')

@section('rhu-content')

{{-- ═══════════════════════════════
     PAGE HERO
═══════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Health Records</div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Read-only oversight of records entered by midwives and health workers
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <span class="badge bg-secondary p-2 d-flex align-items-center" style="border-radius:12px;">
                <i class="bi bi-eye me-1"></i> Read-only
            </span>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
         style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text); border-radius:16px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ═══════════════════════════════
     STAT CARDS
═══════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(155,100,185,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div style="width:44px;height:44px;border-radius:14px;background:var(--color-primary-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-primary-text);">
                    <i class="bi bi-clipboard-data"></i>
                </div>
                <span style="background:var(--color-primary-soft);color:var(--color-primary-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Total</span>
            </div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Total Records</div>
            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $totalCount }}</div>
            <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                <i class="bi bi-folder2-open"></i> Clinical history logs
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(4,120,87,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div style="width:44px;height:44px;border-radius:14px;background:var(--color-success-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-success-text);">
                    <i class="bi bi-shield-check"></i>
                </div>
                <span style="background:var(--color-success-soft);color:var(--color-success-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Normal</span>
            </div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Low Risk</div>
            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $lowCount }}</div>
            <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                <i class="bi bi-check-circle"></i> Stable parameters
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(217,119,6,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div style="width:44px;height:44px;border-radius:14px;background:var(--color-warning-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-warning-text);">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <span style="background:var(--color-warning-soft);color:var(--color-warning-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Watch</span>
            </div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Medium Risk</div>
            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $mediumCount }}</div>
            <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                <i class="bi bi-clock-history"></i> Monitor closely
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(220,38,38,0.10)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div style="width:44px;height:44px;border-radius:14px;background:var(--color-danger-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-danger-text);">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <span style="background:var(--color-danger-soft);color:var(--color-danger-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Urgent</span>
            </div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">High Risk</div>
            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $highCount }}</div>
            <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                <i class="bi bi-arrow-up-right-circle"></i> Requires immediate intervention
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     HEALTH RECORDS TABLE
═══════════════════════════════ --}}
<div class="card fade-in-card mb-4" style="border:1px solid var(--color-border); border-radius:24px; background:var(--color-surface); box-shadow:0 10px 30px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 7%, transparent); overflow:hidden;">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3"
         style="background:var(--color-surface); border-bottom:1px solid var(--color-border); padding:1.25rem 1.5rem;">
        <div>
            <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--color-text); font-size:1.1rem;">All Health Records</h5>
            <small style="color:var(--color-text-muted);">View and track clinical parameters recorded across patients</small>
        </div>

        {{-- Search & Filter Toolbar --}}
        <form method="GET" action="{{ route('rhu.health-records.index') }}" class="d-flex gap-2 align-items-end flex-wrap">
            <div class="d-flex flex-column" style="min-width:220px;">
                <label class="form-label small fw-semibold mb-1" style="color:var(--color-text-muted);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Search</label>
                <div class="search-bar" style="display:flex; gap:0.5rem; align-items:center; background:var(--color-bg); border:1px solid var(--color-border); border-radius:12px; padding:0.4rem 0.8rem; height:42px;">
                    <i class="bi bi-search" style="color:var(--color-text-muted);"></i>
                    <input type="search" name="search" placeholder="Name or risk level..." value="{{ $search }}"
                           style="border:none; background:transparent; color:var(--color-text); font-size:0.875rem; outline:none; flex:1; min-width:0;">
                </div>
            </div>
            <div class="d-flex flex-column">
                <label class="form-label small fw-semibold mb-1" style="color:var(--color-text-muted);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Risk level</label>
                <select name="risk_level" class="form-select" onchange="this.form.submit()" style="width:150px; height:42px; border-radius:12px; border:1px solid var(--color-border); background-color:var(--color-bg); color:var(--color-text); font-size:0.875rem;">
                    @foreach(['all', 'Low', 'Medium', 'High', 'Critical'] as $level)
                        <option value="{{ $level }}" {{ $riskLevel === $level ? 'selected' : '' }}>{{ $level === 'all' ? 'All Risk Levels' : $level.' Risk' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex flex-column">
                <label class="form-label small fw-semibold mb-1" style="color:var(--color-text-muted);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Barangay</label>
                <select name="barangay" class="form-select" onchange="this.form.submit()" style="width:160px; height:42px; border-radius:12px; border:1px solid var(--color-border); background-color:var(--color-bg); color:var(--color-text); font-size:0.875rem;">
                    <option value="">All barangays</option>
                    @foreach($barangays as $b)
                        <option value="{{ $b }}" {{ $barangay === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex flex-column">
                <label class="form-label small fw-semibold mb-1" style="color:var(--color-text-muted);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" max="{{ date('Y-m-d') }}"
                       style="height:42px; border-radius:12px; border:1px solid var(--color-border); background-color:var(--color-bg); color:var(--color-text); font-size:0.875rem; padding:0.4rem 0.75rem;">
            </div>
            <div class="d-flex flex-column">
                <label class="form-label small fw-semibold mb-1" style="color:var(--color-text-muted);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" max="{{ date('Y-m-d') }}"
                       style="height:42px; border-radius:12px; border:1px solid var(--color-border); background-color:var(--color-bg); color:var(--color-text); font-size:0.875rem; padding:0.4rem 0.75rem;">
            </div>
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-primary d-flex align-items-center" style="height:42px; border-radius:12px; padding:0 0.9rem;">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if($search || $riskLevel !== 'all' || $barangay || $dateFrom || $dateTo)
                    <a href="{{ route('rhu.health-records.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center" style="height:42px; border-radius:12px; padding:0 0.8rem;">
                        <i class="bi bi-x-lg me-1"></i>Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($records->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                    <thead style="background:var(--color-bg); border-bottom:1px solid var(--color-border);">
                        <tr style="color:var(--color-text-muted); font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">
                            <th style="padding:1rem 1.5rem; border:none;">Date</th>
                            <th style="padding:1rem; border:none;">Patient</th>
                            <th style="padding:1rem; border:none;">Pregnancy</th>
                            <th style="padding:1rem; border:none;">Vital Signs</th>
                            <th style="padding:1rem; border:none;">Risk Level</th>
                            <th style="padding:1rem; border:none;">Recorded By</th>
                            <th style="padding:1rem 1.5rem; border:none; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                            @php
                                $patient = $record->woman ?? $record->walkInPatient;
                                $isWalkIn = $record->walkInPatient !== null;
                                $patientName = $record->patient_name;
                                $patientContact = $record->patient_contact ?? 'N/A';
                                $patientBarangay = $record->patient_barangay;
                                $recordedByName = $record->recordedBy?->name ?? 'System';
                                $recordedByRole = $record->recordedBy?->role ? str_replace('_', ' ', $record->recordedBy->role) : null;
                                $vitals = [];
                                if ($record->bp) $vitals[] = $record->bp . ' mmHg';
                                if ($record->heart_rate) $vitals[] = $record->heart_rate . ' bpm';
                                if ($record->temperature) $vitals[] = $record->temperature . '°C';
                                if ($record->weight) $vitals[] = $record->weight . ' kg';
                            @endphp
                            <tr style="border-bottom:1px solid var(--color-primary-soft);">
                                <td style="padding:1rem 1.5rem;">
                                    <div style="font-weight:700; color:var(--color-text);">{{ $record->created_at->format('M j, Y') }}</div>
                                    <small style="color:var(--color-text-muted); font-size:0.75rem;">{{ $record->created_at->format('h:i A') }}</small>
                                </td>
                                <td style="padding:1rem;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:38px;height:38px;border-radius:12px;background:var(--color-primary-soft);border:1px solid var(--color-border);color:var(--color-primary-text);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                                            {{ strtoupper(substr($patientName, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight:600; color:var(--color-text);">
                                                {{ $patientName }}
                                                @if($isWalkIn)
                                                    <span class="badge" style="background:var(--color-info-soft); color:var(--color-info-text); font-size:0.7rem; font-weight:600; margin-left:4px;">Unlinked</span>
                                                @endif
                                            </div>
                                            <small style="color:var(--color-text-muted); font-size:0.75rem;">{{ $patientContact }}</small>
                                            <small class="d-block" style="color:var(--color-text-muted); font-size:0.75rem;">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $patientBarangay ?: 'No barangay' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:1rem;">
                                    @if($record->pregnancy)
                                        <div style="font-weight:600; color:var(--color-text); font-size:0.85rem;">Pregnancy #{{ $record->pregnancy->id }}</div>
                                        <small style="color:var(--color-text-muted); font-size:0.75rem;">EDD {{ $record->pregnancy->edd?->format('M j, Y') ?? 'N/A' }}</small>
                                    @else
                                        <span class="text-muted" style="font-size:0.85rem;">—</span>
                                    @endif
                                </td>
                                <td style="padding:1rem;">
                                    @if(count($vitals) > 0)
                                        <div style="font-family:'Plus Jakarta Sans',sans-serif; font-size:0.86rem; font-weight:600; color:var(--color-text);">
                                            {{ implode(' · ', $vitals) }}
                                        </div>
                                        @if($record->fetal_heart_rate)
                                            <div style="font-size:0.75rem; color:var(--color-primary-text); font-weight:700; margin-top:2px;">
                                                <i class="bi bi-heart-pulse-fill me-1"></i>FHR: {{ $record->fetal_heart_rate }} bpm
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted" style="font-size:0.85rem;">—</span>
                                    @endif
                                </td>
                                <td style="padding:1rem;">
                                    @switch($record->risk_level)
                                        @case('Low')
                                            <span style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.3rem 0.75rem; background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text); border-radius:9999px; font-weight:600; font-size:0.75rem;">
                                                <i class="bi bi-shield-check"></i> Low Risk
                                            </span>
                                            @break
                                        @case('Medium')
                                            <span style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.3rem 0.75rem; background:var(--color-warning-soft); border:1px solid var(--color-warning); color:var(--color-warning-text); border-radius:9999px; font-weight:600; font-size:0.75rem;">
                                                <i class="bi bi-exclamation-circle"></i> Medium Risk
                                            </span>
                                            @break
                                        @case('High')
                                        @case('Critical')
                                            <span style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.3rem 0.75rem; background:var(--color-danger-soft); border:1px solid var(--color-danger-soft); color:var(--color-danger-text); border-radius:9999px; font-weight:600; font-size:0.75rem;">
                                                <i class="bi bi-exclamation-triangle"></i> {{ $record->risk_level }} Risk
                                            </span>
                                            @break
                                        @default
                                            <span style="padding:0.3rem 0.75rem; background:var(--color-bg); border:1px solid var(--color-border); color:var(--color-text-muted); border-radius:9999px; font-size:0.75rem; font-weight:500;">{{ $record->risk_level ?? 'Unassessed' }}</span>
                                    @endswitch
                                </td>
                                <td style="padding:1rem;">
                                    <div style="font-weight:600; color:var(--color-text); font-size:0.875rem;">{{ $recordedByName }}</div>
                                    @if($recordedByRole)
                                        <small style="color:var(--color-text-muted); font-size:0.75rem;">{{ ucfirst($recordedByRole) }}</small>
                                    @endif
                                </td>
                                <td style="padding:1rem 1.5rem; text-align:right;">
                                    <a href="{{ route('rhu.health-records.show', $record->id) }}"
                                       class="btn btn-sm btn-primary"
                                       style="border-radius:10px; padding:0.4rem 0.85rem; font-size:0.82rem; font-weight:700;">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="card-footer" style="background:transparent; border-top:1px solid var(--color-border); padding:1rem 1.5rem;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small style="color:var(--color-text-muted);">
                        Showing {{ $records->firstItem() }}–{{ $records->lastItem() }} of {{ $records->total() }} records
                    </small>
                    <div>{{ $records->links() }}</div>
                </div>
            </div>
        @else
            <div class="card-body p-5">
                <div class="text-center py-5">
                    <div style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,var(--color-primary),var(--color-secondary));display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                        <i class="bi bi-clipboard-pulse" style="font-size:2rem;color:var(--color-on-solid);"></i>
                    </div>
                    <h5 style="font-weight:600; color:var(--color-text); margin-bottom:0.75rem;">No Health Records Found</h5>
                    <p style="color:var(--color-text-muted); margin-bottom:1.5rem;">
                        @if($search || $riskLevel !== 'all' || $barangay || $dateFrom || $dateTo)
                            No health records match the current filters.
                        @else
                            No health records have been created yet.
                        @endif
                    </p>
                    @if($search || $riskLevel !== 'all' || $barangay || $dateFrom || $dateTo)
                        <a href="{{ route('rhu.health-records.index') }}" class="btn btn-primary"
                           style="border-radius:10px; padding:0.6rem 1.5rem;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Clear Filters
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
