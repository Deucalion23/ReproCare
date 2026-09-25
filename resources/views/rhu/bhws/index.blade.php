@extends('rhu.layout')

@section('title', 'BHW Management - RHU Portal | ReproCare')

@section('rhu-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 mb-1">Barangay Health Workers</h1>
            <p class="text-muted mb-0">Register and manage rank-and-file BHW accounts. Day-to-day supervision stays with the BHW President.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('rhu.staff-transitions.index', ['type' => 'bhw_transfer']) }}" class="btn btn-filter">
                <i class="bi bi-arrow-left-right me-1"></i> Transfer Roster
            </a>
            <a href="{{ route('rhu.bhws.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Register BHW
            </a>
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

    <div class="card fade-in-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('rhu.bhws.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name or email..." value="{{ $search }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach(['approved', 'inactive', 'archived', 'suspended'] as $s)
                            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Barangay</label>
                    <select name="barangay" class="form-select">
                        <option value="">All barangays</option>
                        @foreach($barangays as $brgy)
                            <option value="{{ $brgy->name }}" {{ $barangay === $brgy->name ? 'selected' : '' }}>Barangay {{ $brgy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Apply</button>
                    <a href="{{ route('rhu.bhws.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card fade-in-card">
        <div class="card-body p-0">
            @if($bhws->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Barangay</th>
                                <th>Purok</th>
                                <th>Status</th>
                                <th>Records</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bhws as $bhw)
                                <tr>
                                    <td class="fw-semibold">{{ $bhw->name }}</td>
                                    <td>{{ $bhw->email }}</td>
                                    <td>{{ $bhw->barangay ?? '—' }}</td>
                                    <td>{{ $bhw->purok?->name ?? $bhw->activeBhwAssignment?->purok?->name ?? '—' }}</td>
                                    <td><span class="badge bg-{{ ($bhw->status ?? 'approved') === 'approved' ? 'success' : 'secondary' }}">{{ ucfirst($bhw->status ?? 'approved') }}</span></td>
                                    <td class="text-muted small">{{ $bhw->records_count }} records · {{ $bhw->checkups_count }} checkups</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1 tbl-actions">
                                            <a href="{{ route('rhu.bhws.edit', $bhw->id) }}" class="btn btn-sm btn-outline-primary" title="Edit BHW">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if(($bhw->status ?? 'approved') === 'approved')
                                                <form method="POST" action="{{ route('rhu.bhws.archive', $bhw->id) }}" onsubmit="return ppPromptArchiveReason(this);">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Archive BHW">
                                                        <i class="bi bi-archive"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('rhu.bhws.activate', $bhw->id) }}" onsubmit="return confirm('Re-activate this BHW account?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Re-activate BHW">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                </form>
                                                <span class="badge bg-secondary" title="Archived accounts are retained for audit and can be restored from the Archives Hub">
                                                    <i class="bi bi-archive"></i> Archived
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $bhws->links() }}</div>
            @else
                <div class="empty-state">
                    <i class="bi bi-people empty-state-icon"></i>
                    <h6>No BHWs found</h6>
                    <p>Try a different filter or register a new BHW.</p>
                    <a href="{{ route('rhu.bhws.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Register BHW
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@push('scripts')
<script>
    // Archive guardrail: require a reason so the audit trail records who / when / why.
    function ppPromptArchiveReason(form) {
        const reason = prompt('Reason for archiving this BHW? (e.g. resigned, reassigned)');
        if (reason === null) return false;
        if (reason.trim() === '') { alert('A reason for archiving is required.'); return false; }
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'reason'; input.value = reason.trim();
        form.appendChild(input);
        return confirm('Archive this BHW account? Sessions will be revoked immediately.');
    }
</script>
@endpush
@endsection
