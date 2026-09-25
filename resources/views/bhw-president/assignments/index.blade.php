@extends('bhw-president.layout')

@section('title', 'BHW Assignments - BHW President Portal | ReproCare')

@section('bhw-president-content')

{{-- ═══════════════════════════════
     PAGE HERO
═══════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">BHW Assignments
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Manage BHW to Purok assignments
            </p>
        </div>
        <a href="{{ route('bhw-president.assignments.create') }}" class="btn-hero-primary">
            <i class="bi bi-plus-circle-fill me-1"></i> New Assignment
        </a>
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
         ASSIGNMENTS TABLE
    ════════════════════════════════ --}}
    <div class="card fade-in-card" style="border:none; background:var(--bg-card);">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="background:transparent; border-bottom:1px solid var(--border-color); padding:1rem 1.5rem;">
            <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">All Assignments
            </h5>
            <span style="background:color-mix(in srgb, var(--color-primary) 10%, transparent); color:var(--primary); padding:0.3rem 0.8rem; border-radius:20px; font-size:0.8rem; font-weight:500;">
                {{ $assignments->count() }} Assignments
            </span>
        </div>
        <div class="card-body p-0">
            @if($assignments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                        <thead>
                            <tr style="color:var(--text-muted); font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; background:transparent;">
                                <th style="padding:1rem 1.5rem; border:none;">BHW</th>
                                <th style="padding:1rem; border:none;">Purok</th>
                                <th style="padding:1rem; border:none;">Assigned By</th>
                                <th style="padding:1rem; border:none;">Assigned At</th>
                                <th style="padding:1rem; border:none;">Status</th>
                                <th style="padding:1rem 1.5rem; border:none; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $assignment)
                            <tr style="border-bottom:1px solid var(--border-color);">
                                <td style="padding:1rem 1.5rem;">
                                    <div style="font-weight:600; color:var(--text);">{{ $assignment->bhw->name }}</div>
                                </td>
                                <td style="padding:1rem;">
                                    <div style="font-weight:500; color:var(--text);">{{ $assignment->purok->name }}</div>
                                    <small style="color:var(--text-muted);">{{ $assignment->purok->barangay }}</small>
                                </td>
                                <td style="padding:1rem; color:var(--text-muted);">{{ $assignment->assignedBy->name }}</td>
                                <td style="padding:1rem; color:var(--text-muted);">{{ $assignment->assigned_at->format('M d, Y') }}</td>
                                <td style="padding:1rem;">
                                    @if($assignment->is_active)
                                        <span style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.25rem 0.75rem; background:color-mix(in srgb, var(--color-success-text) 10%, transparent); color:var(--success); border-radius:20px; font-weight:500; font-size:0.8rem;">
                                            <i class="bi bi-check-circle"></i> Active
                                        </span>
                                    @else
                                        <span style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.25rem 0.75rem; background:var(--bg-card2); color:var(--text-muted); border-radius:20px; font-weight:500; font-size:0.8rem;">
                                            <i class="bi bi-pause-circle"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td style="padding:1rem 1.5rem; text-align:right;">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('bhw-president.assignments.show', $assignment->id) }}"
                                           class="btn btn-sm"
                                           style="width:36px;height:36px;border-radius:8px;background:var(--bg-card2);border:1px solid var(--border);color:var(--primary);display:flex;align-items:center;justify-content:center;"
                                           title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('bhw-president.assignments.edit', $assignment->id) }}"
                                           class="btn btn-sm"
                                           style="width:36px;height:36px;border-radius:8px;background:var(--bg-card2);border:1px solid var(--border);color:var(--warning);display:flex;align-items:center;justify-content:center;"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('bhw-president.assignments.toggle', $assignment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm"
                                                    style="width:36px;height:36px;border-radius:8px;background:var(--bg-card2);border:1px solid var(--border);color:{{ $assignment->is_active ? 'var(--warning)' : 'var(--success)' }};display:flex;align-items:center;justify-content:center;"
                                                    title="{{ $assignment->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="bi bi-{{ $assignment->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('bhw-president.assignments.destroy', $assignment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this assignment?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm"
                                                    style="width:36px;height:36px;border-radius:8px;background:var(--bg-card2);border:1px solid var(--border);color:var(--danger);display:flex;align-items:center;justify-content:center;"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="card-body p-5">
                    <div class="text-center py-5">
                        <div style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,var(--primary),var(--accent-violet));display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                            <i class="bi bi-geo-alt" style="font-size:2rem;color:var(--color-on-solid);"></i>
                        </div>
                        <h5 style="font-weight:600; color:var(--text); margin-bottom:0.75rem;">No Assignments Found</h5>
                        <p style="color:var(--text-muted); margin-bottom:1.5rem;">
                            No BHW assignments have been created yet. Start by assigning a BHW to a Purok.
                        </p>
                        <a href="{{ route('bhw-president.assignments.create') }}" class="btn"
                           style="background:linear-gradient(135deg,var(--primary),var(--accent-violet)); color:var(--color-on-solid); border:none; border-radius:10px; padding:0.6rem 1.5rem;">
                            <i class="bi bi-plus-circle me-1"></i>Create First Assignment
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
