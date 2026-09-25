@extends('bhw.layout')

@section('title', 'Pregnancies - BHW Portal')

@push('styles')
<style>
    /* Compact fit-no-scroll pregnancy table. */
    #bhw-preg-table > :not(caption) > * > * {
        padding:0.55rem 0.5rem;
        font-size:0.85rem;
    }
    /* Forced equal action buttons: variant padding/borders can't unbalance them. */
    #bhw-preg-table .tbl-actions .btn {
        width:38px;
        height:38px;
        padding:0;
        border-radius:12px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
    }
    #bhw-preg-table .tbl-actions .btn > i { line-height:1; }
    #bhw-preg-table .pg-nowrap {
        white-space:nowrap;
    }
</style>
@endpush

@section('bhw-content')
<div class="py-4">
    <div class="page-hero fade-in-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Pregnancies</div>
                <p class="page-hero-subtitle mb-0">View pregnancies you created, those created by other BHWs, or by the midwife.</p>
            </div>
            <a href="{{ route('bhw.pregnancies.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Report Pregnancy
            </a>
        </div>
    </div>

    <div class="card fade-in-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('bhw.pregnancies.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter</label>
                    <select name="filter" class="form-select">
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Pregnancies</option>
                        <option value="mine" {{ $filter === 'mine' ? 'selected' : '' }}>My Created Patients</option>
                        <option value="midwife" {{ $filter === 'midwife' ? 'selected' : '' }}>Midwife Created</option>
                        <option value="others" {{ $filter === 'others' ? 'selected' : '' }}>Other BHW Created</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Search Patient</label>
                    <input type="text" name="search" class="form-control" placeholder="Search enrolled or unlinked patient..." value="{{ $search }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-filter flex-fill"><i class="bi bi-search me-1"></i> Apply</button>
                    <a href="{{ route('bhw.pregnancies.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card fade-in-card">
        <div class="card-body p-0">
                            @if($pregnancies->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="bhw-preg-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>LMP</th>
                                <th>EDD</th>
                                <th>AOG</th>
                                <th>Trimester</th>
                                <th>Risk</th>
                                <th>Created By</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pregnancies as $pregnancy)
                                @php
                                    $isWalkIn = !is_null($pregnancy->walk_in_patient_id);
                                    $patientRoute = $isWalkIn
                                        ? route('bhw.walk-in-patients.show', [$pregnancy->walk_in_patient_id, 'from' => 'pregnancies'])
                                        : route('bhw.patient-details', $pregnancy->user_id);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $pregnancy->patient_name }}</div>
                                        <small class="text-muted">{{ $isWalkIn ? 'Unlinked patient' : (optional($pregnancy->woman)->email ?? 'Enrolled patient') }}</small>
                                    </td>
                                    <td class="pg-nowrap">{{ $pregnancy->lmp?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td class="pg-nowrap">{{ $pregnancy->edd?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td><span class="badge bg-light text-dark border text-nowrap">{{ $pregnancy->formatted_aog ?? 'N/A' }}</span></td>
                                    <td><span class="badge bg-info text-dark text-nowrap">{{ $pregnancy->trimester_name ?? 'N/A' }}</span></td>
                                    <td>
                                        <span class="badge text-nowrap {{ $pregnancy->is_high_risk ? 'bg-danger' : 'bg-success' }}">
                                            {{ $pregnancy->is_high_risk ? 'High Risk' : 'Normal' }}
                                        </span>
                                    </td>
                                    <td class="pg-nowrap">{{ $pregnancy->created_by_name ?? 'Unknown' }}</td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-1 tbl-actions">
                                            <a href="{{ route('bhw.referrals.report-pregnancy', $pregnancy->id) }}" class="btn btn-sm btn-outline-success" title="Report this pregnancy to a midwife" aria-label="Report this pregnancy to a midwife">
                                                <i class="bi bi-send-fill"></i>
                                            </a>
                                            <a href="{{ $patientRoute }}" class="btn btn-sm btn-outline-primary" title="View patient" aria-label="View patient">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $pregnancies->links() }}</div>
            @else
                <div class="empty-state">
                    <i class="bi bi-heart empty-state-icon"></i>
                    <h6>No pregnancies found</h6>
                    <p>Try a different filter or search term.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
