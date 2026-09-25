@extends('bhw-president.layout')

@use('Carbon\Carbon')

@section('title', 'Coverage Report - BHW President Portal | ReproCare')

@section('bhw-president-content')

<div class="page-hero fade-in-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Coverage Report</div>
            <p class="page-hero-subtitle">Enrolled women and unlinked patients covered by purok and service usage.</p>
        </div>
    </div>
</div>

<div class="card fade-in-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Purok</label>
                <select name="purok" class="form-select">
                    <option value="">All Puroks</option>
                    @foreach($puroks as $p)
                        <option value="{{ $p->id }}" {{ $purokId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Month</label>
                <select name="month" class="form-select">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ Carbon::create()->month($i)->format('F') }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    @for($i = 2024; $i <= 2030; $i++)
                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <a href="{{ route('bhw-president.coverage') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-purple fade-in-card">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-label">Total Patients</div>
            <div class="stat-number">{{ $coverageStats['totalPatients'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-cyan fade-in-card">
            <div class="stat-icon"><i class="bi bi-person-plus-fill"></i></div>
            <div class="stat-label">Walk-ins Included</div>
            <div class="stat-number">{{ $coverageStats['walkInPatients'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-green fade-in-card">
            <div class="stat-icon"><i class="bi bi-file-medical-fill"></i></div>
            <div class="stat-label">With Health Records</div>
            <div class="stat-number">{{ $coverageStats['patientsWithHealthRecords'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-amber fade-in-card">
            <div class="stat-icon"><i class="bi bi-heart-fill"></i></div>
            <div class="stat-label">With Pregnancies</div>
            <div class="stat-number">{{ $coverageStats['patientsWithPregnancies'] }}</div>
        </div>
    </div>
</div>

<div class="card fade-in-card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Coverage Overview</h5>
    </div>
    <div class="card-body">
        <div class="progress mb-3" style="height:30px;">
            <div class="progress-bar bg-success" style="width:{{ $coverageStats['coveragePercentage'] }}%;display:flex;align-items:center;justify-content:center;font-weight:600;">
                {{ $coverageStats['coveragePercentage'] }}% Coverage
            </div>
        </div>
        <p class="mb-0 text-muted">{{ $coverageStats['patientsWithHealthRecords'] }} of {{ $coverageStats['totalPatients'] }} patients have health records this period.</p>
    </div>
</div>

<div class="card fade-in-card">
    <div class="card-header">
        <h5 class="mb-0">Patient List</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Type</th>
                        <th>Purok</th>
                        <th>Has Checkups</th>
                        <th>Has Health Records</th>
                        <th>Has Pregnancies</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patients as $patient)
                        @php
                            $isWalkIn = ($patient->coverage_type ?? 'registered') === 'walk_in';
                            $displayName = $isWalkIn ? $patient->full_name : $patient->name;
                            $hasCheckups = $isWalkIn
                                ? ($patient->checkupReferrals && $patient->checkupReferrals->count() > 0)
                                : ($patient->checkups && $patient->checkups->count() > 0);
                            $hasHealthRecords = $isWalkIn
                                ? \App\Models\HealthRecord::where('walk_in_patient_id', $patient->id)->exists()
                                : ($patient->healthRecords && $patient->healthRecords->count() > 0);
                            $hasPregnancies = $patient->pregnancies && $patient->pregnancies->count() > 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <x-patient-avatar :patient="$patient" :name="$displayName" :size="32" />
                                    @if($isWalkIn)
                                        <span style="font-weight:500;">{{ $displayName }}</span>
                                    @else
                                        <a href="{{ route('profile.view', $patient->id) }}" class="fw-semibold">{{ $displayName }}</a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $isWalkIn ? 'bg-info text-dark' : 'bg-primary' }}">
                                    {{ $isWalkIn ? 'Unlinked' : 'Enrolled' }}
                                </span>
                            </td>
                            <td>{{ $patient->purok?->name ?? 'N/A' }}</td>
                            <td><span class="badge {{ $hasCheckups ? 'bg-success' : 'bg-secondary' }}"><i class="bi bi-{{ $hasCheckups ? 'check' : 'x' }}"></i></span></td>
                            <td><span class="badge {{ $hasHealthRecords ? 'bg-success' : 'bg-secondary' }}"><i class="bi bi-{{ $hasHealthRecords ? 'check' : 'x' }}"></i></span></td>
                            <td><span class="badge {{ $hasPregnancies ? 'bg-success' : 'bg-secondary' }}"><i class="bi bi-{{ $hasPregnancies ? 'check' : 'x' }}"></i></span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $patients->links() }}
    </div>
</div>

@endsection
