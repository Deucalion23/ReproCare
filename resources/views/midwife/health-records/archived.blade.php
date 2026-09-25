@extends('midwife.layout')

@section('title', 'Archived Health Records - ReproCare')

@section('midwife-content')

{{-- ═══════════════════════════════
     PAGE HERO
════════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Archived Health Records
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; View previously archived health records
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('midwife.health-records.index') }}" class="btn-hero-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Health Records
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" 
         style="background:color-mix(in srgb, var(--color-success-text) 10%, transparent); border:1px solid color-mix(in srgb, var(--color-success-text) 30%, transparent); color:var(--success); border-radius:10px;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter:invert(1);"></button>
    </div>
@endif

{{-- ═══════════════════════════════
     STAT CARDS
════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col">
        <div class="stat-card stat-purple fade-in-card">
            <i class="bi bi-archive stat-icon"></i>
            <div class="stat-value">{{ $archivedRecords->total() }}</div>
            <div class="stat-label">Total Archived Records</div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     ARCHIVED RECORDS TABLE
════════════════════════════════ --}}
<div class="card fade-in-card mb-4" style="border:none; border-radius:16px; box-shadow:0 4px 20px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 8%, transparent);">
    <div class="card-body p-4">
        @if($archivedRecords->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" style="margin:0;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient Name</th>
                            <th>Type</th>
                            <th>BP</th>
                            <th>Weight</th>
                            <th>Risk Level</th>
                            <th>Recorded By</th>
                            <th>Archived At</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedRecords as $record)
                            <tr>
                                <td><span class="badge bg-secondary">#{{ $record->id }}</span></td>
                                <td>
                                    @if($record->user_id)
                                        {{ $record->woman_first_name }} {{ $record->woman_middle_initial }} {{ $record->woman_last_name }}
                                    @elseif($record->walk_in_patient_id)
                                        {{ $record->walkin_first_name }} {{ $record->walkin_middle_initial }} {{ $record->walkin_last_name }}
                                        <span class="badge bg-warning text-dark ms-1">Unlinked</span>
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->user_id)
                                        <span class="badge bg-primary">Enrolled</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Unlinked</span>
                                    @endif
                                </td>
                                <td>{{ $record->bp ?? 'N/A' }}</td>
                                <td>{{ $record->weight ?? 'N/A' }} kg</td>
                                <td>
                                    @if($record->risk_level === 'High')
                                        <span class="badge bg-danger">High</span>
                                    @elseif($record->risk_level === 'Medium')
                                        <span class="badge bg-warning text-dark">Medium</span>
                                    @else
                                        <span class="badge bg-success">Low</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->recorded_by_id)
                                        {{ $record->recorded_by_first_name }} {{ $record->recorded_by_middle_initial }} {{ $record->recorded_by_last_name }}
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($record->archived_at)->format('M d, Y - g:i A') }}</td>
                                <td>
                                    <span class="text-muted" style="font-size:0.875rem;">
                                        {{ \Illuminate\Support\Str::limit($record->archived_reason, 30) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-4">
                {{ $archivedRecords->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-archive" style="font-size:4rem; color:color-mix(in srgb, var(--color-text) 10%, transparent);"></i>
                <h5 class="mt-3 text-muted">No Archived Records</h5>
                <p class="text-muted">There are no archived health records to display.</p>
                <a href="{{ route('midwife.health-records.index') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-arrow-left me-1"></i> Back to Health Records
                </a>
            </div>
        @endif
    </div>
</div>

@endsection
