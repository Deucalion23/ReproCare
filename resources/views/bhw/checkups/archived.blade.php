@extends('bhw.layout')

@section('title', 'Archived Checkups - ReproCare')

@section('bhw-content')

{{-- ═══════════════════════════════
     PAGE HERO
════════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Archived Checkups
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Completed checkups move here automatically
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('bhw.checkups.index') }}" class="btn"
                    style="background:var(--color-surface); color:var(--primary); border-radius:12px; padding:0.6rem 1.25rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; box-shadow:0 4px 15px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent);">
                <i class="bi bi-arrow-left"></i> Back to Scheduled
            </a>
        </div>
    </div>
</div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ═══════════════════════════════
         ARCHIVED TABLE
    ════════════════════════════════ --}}
    <div class="card fade-in-card" style="border:none; background:var(--bg-card);">
        <div class="card-header" style="background:transparent; border-bottom:1px solid var(--border-color); padding:1rem 1.5rem;">
            <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">Archived Checkups ({{ $checkups->total() }})
            </h5>
        </div>
        <div class="card-body p-4">
            @if($checkups->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Purpose</th>
                                <th>Completed On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checkups as $checkup)
                                <tr>
                                    <td>{{ $checkup->patient_name }}</td>
                                    <td>{{ optional($checkup->scheduled_date)->format('M j, Y') ?? '—' }}</td>
                                    <td>{{ $checkup->purpose ?? '—' }}</td>
                                    <td>{{ optional($checkup->updated_at)->format('M j, Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $checkups->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-archive empty-state-icon"></i>
                    <h6>No Archived Checkups</h6>
                    <p>Completed checkups will automatically appear here.</p>
                    <a href="{{ route('bhw.checkups.index') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Scheduled
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
