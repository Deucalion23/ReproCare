@extends('cho.layout')

@section('title', 'City-Wide Pregnancy Monitoring - CHO Portal | ReproCare')

@push('styles')
<style>
    .preg-pager { display:flex; justify-content:center; align-items:center; gap:.5rem; }
    .preg-page-arrow { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border:1px solid var(--color-border); border-radius:10px; background:var(--color-surface); color:var(--color-text-muted); font-size:.85rem; text-decoration:none; }
    .preg-page-arrow:hover { border-color:var(--color-secondary-soft); color:var(--color-secondary-text); background:var(--color-secondary-soft); }
    .preg-page-arrow.disabled { color:var(--color-border); background:var(--color-bg); cursor:default; }
</style>
@endpush

@section('cho-content')
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">City-Wide Pregnancy Monitoring</div>
            <p class="page-hero-subtitle" style="font-weight:600;">All pregnancy records across RHU catchment barangays.</p>
        </div>
    </div>
</div>

<div class="card fade-in-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('cho.pregnancies.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search patient name...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ ($status ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="risk_level" class="form-select">
                    <option value="all" {{ ($riskLevel ?? 'all') === 'all' ? 'selected' : '' }}>All Risk Levels</option>
                    <option value="Low" {{ ($riskLevel ?? '') === 'Low' ? 'selected' : '' }}>Low</option>
                    <option value="Medium" {{ ($riskLevel ?? '') === 'Medium' ? 'selected' : '' }}>Medium</option>
                    <option value="High" {{ ($riskLevel ?? '') === 'High' ? 'selected' : '' }}>High</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card fade-in-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th class="px-4">Patient</th><th>LMP</th><th>EDD</th><th>AOG</th><th>Risk</th><th>Status</th><th class="text-end px-4">Actions</th></tr></thead>
                <tbody>
                    @forelse($pregnancies as $pregnancy)
                        <tr>
                            <td class="px-4 fw-semibold">{{ $pregnancy->patient_name }}</td>
                            <td>{{ optional($pregnancy->lmp)->format('M j, Y') ?? '—' }}</td>
                            <td>{{ optional($pregnancy->edd)->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $pregnancy->aog ?? '—' }} wks</td>
                            <td><span class="badge bg-{{ ($pregnancy->risk_level ?? 'Low') === 'High' ? 'danger' : (($pregnancy->risk_level ?? '') === 'Medium' ? 'warning' : 'success') }}">{{ $pregnancy->risk_level ?? 'Low' }}</span></td>
                            <td>{{ $pregnancy->ended_at ? 'Completed' : 'Active' }}</td>
                            <td class="text-end px-4"><a href="{{ route('cho.pregnancies.show', $pregnancy->id) }}" class="btn btn-sm btn-view">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No pregnancy records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($pregnancies, 'hasPages') && $pregnancies->hasPages())
    <div class="p-3 border-top preg-pager">
        @if($pregnancies->onFirstPage())
            <span class="preg-page-arrow disabled" aria-disabled="true"><i class="bi bi-chevron-left"></i></span>
        @else
            <a class="preg-page-arrow" href="{{ $pregnancies->withQueryString()->previousPageUrl() }}" rel="prev" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
        @endif
        @if($pregnancies->hasMorePages())
            <a class="preg-page-arrow" href="{{ $pregnancies->withQueryString()->nextPageUrl() }}" rel="next" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
        @else
            <span class="preg-page-arrow disabled" aria-disabled="true"><i class="bi bi-chevron-right"></i></span>
        @endif
    </div>
    @endif
</div>
@endsection
