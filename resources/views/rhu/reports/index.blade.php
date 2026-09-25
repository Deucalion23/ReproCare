@extends('rhu.layout')

@section('title', 'FHSIS & MNCHN Surveillance Reports - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">FHSIS &amp; MNCHN Reports</div>
            <p class="page-hero-subtitle">
                <i class="bi bi-file-earmark-bar-graph-fill me-1"></i> Field Health Service Information System &amp; Maternal Health Registry.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('rhu.reports.export.csv') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </a>
            <a href="{{ route('rhu.reports.export.pdf') }}" target="_blank" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-file-pdf me-1"></i> Print PDF
            </a>
        </div>
    </div>
</div>

<!-- Indicator Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-purple fade-in-card py-3">
            <div class="stat-icon" style="font-size:1.5rem;"><i class="bi bi-heart-pulse-fill"></i></div>
            <div class="stat-label" style="font-size:0.78rem;">Active Pregnant Women</div>
            <div class="stat-number" style="font-size:1.8rem;">{{ $totalPregnant }}</div>
            <div class="stat-trend text-xs mt-1">
                Currently tracking prenatal status
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-cyan fade-in-card py-3">
            <div class="stat-icon" style="font-size:1.5rem;"><i class="bi bi-clipboard2-check-fill"></i></div>
            <div class="stat-label" style="font-size:0.78rem;">Postpartum Tracking</div>
            <div class="stat-number" style="font-size:1.8rem;">{{ $totalPostpartum }}</div>
            <div class="stat-trend text-xs mt-1">
                Completed monitoring phase
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-rose fade-in-card py-3">
            <div class="stat-icon" style="font-size:1.5rem;"><i class="bi bi-gender-ambiguous"></i></div>
            <div class="stat-label" style="font-size:0.78rem;">Total Recorded Births</div>
            <div class="stat-number" style="font-size:1.8rem;">{{ $totalBirths }}</div>
            <div class="stat-trend text-xs mt-1">
                Recorded pregnancy ends
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card stat-success fade-in-card py-3">
            <div class="stat-icon" style="font-size:1.5rem;"><i class="bi bi-hospital-fill"></i></div>
            <div class="stat-label" style="font-size:0.78rem;">Facility Delivery Rate</div>
            <div class="stat-number" style="font-size:1.8rem;">{{ $facilityBirthRate }}%</div>
            <div class="stat-trend text-xs mt-1">
                Deliveries inside formal clinics
            </div>
        </div>
    </div>
</div>

<!-- Search & Table Card -->
<div class="card fade-in-card">
    <div class="card-header bg-transparent py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0 fw-700 text-dark">Maternal Registry List
            </h5>

            <!-- Search Form -->
            <form method="GET" action="{{ route('rhu.reports.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute text-muted" style="left:0.9rem; top:50%; transform:translateY(-50%); pointer-events:none;"></i>
                    <input type="text" name="search" class="form-control form-control-sm" style="border-radius:999px; padding-left:2.4rem; width:250px; max-width:100%;" placeholder="Search by name or barangay..." value="{{ $search }}">
                </div>
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                @if($search)
                    <a href="{{ route('rhu.reports.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear search" aria-label="Clear search"><i class="bi bi-x-lg"></i></a>
                @endif
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        @if($patients->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="px-4">Patient Name</th>
                            <th>Contact &amp; Age</th>
                            <th>Barangay</th>
                            <th>Pregnancy Status</th>
                            <th>Total Checkups</th>
                            <th class="text-end px-4">Registry File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patients as $patient)
                            <tr>
                                <td class="px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <x-patient-avatar :patient="$patient" />
                                        <div>
                                            <a href="{{ route('rhu.reports.details', $patient->id) }}" class="fw-700 d-block" style="font-size:0.875rem;">{{ $patient->name }}</a>
                                            <span style="font-size:0.75rem; color:var(--text-muted);">{{ $patient->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block text-xs fw-600 text-dark">{{ $patient->contact_number ?? 'No contact' }}</span>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">{{ $patient->age ? $patient->age . ' yrs old' : 'Age unrecorded' }}</span>
                                </td>
                                <td style="font-size:0.82rem; color:var(--text-dark); fw-600;">
                                    {{ $patient->barangay ?? 'N/A' }}
                                </td>
                                <td>
                                    @php
                                        $pStatus = $patient->pregnancy_status;
                                        $badgeColor = match($pStatus) {
                                            'pregnant' => 'primary',
                                            'postpartum' => 'info',
                                            'not_pregnant' => 'secondary',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }} text-white text-xs">
                                        {{ str_replace('_', ' ', ucfirst($pStatus ?? 'unknown')) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $checkupsCount = \App\Models\Checkup::where('user_id', $patient->id)->count();
                                        $isCompliant = $checkupsCount >= 4;
                                    @endphp
                                    <span class="fw-700 text-{{ $isCompliant ? 'success' : 'warning' }}">
                                        {{ $checkupsCount }}
                                    </span>
                                    <span style="font-size:0.75rem; color:var(--text-muted);">checkups</span>
                                </td>
                                <td class="text-end px-4">
                                    <a href="{{ route('rhu.reports.show', $patient->id) }}" class="btn btn-sm btn-outline-primary fw-600">
                                        <i class="bi bi-folder2-open me-1"></i> MNCHN Profile
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <div class="text-xs text-muted">
                    Showing {{ $patients->firstItem() }} to {{ $patients->lastItem() }} of {{ $patients->total() }} entries
                </div>
                <div>
                    {{ $patients->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-people-fill text-muted" style="font-size:3.5rem;"></i>
                <h5 class="mt-3 mb-1 fw-700">No Patient Records Available</h5>
                <p class="text-muted text-xs px-4">No active enrolled patients in the rural health database fit the current filter criteria.</p>
            </div>
        @endif
    </div>
</div>

@endsection
