@extends('midwife.layout')

@section('title', 'Active Pregnancies - ReproCare')

@section('midwife-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Active Pregnancies</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('midwife.pregnancies.create') }}" class="btn btn-primary">
                <i class="bi bi-heart-plus"></i> Add Pregnancy
            </a>
            <a href="{{ route('midwife.pregnancies.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list"></i> All Pregnancies
            </a>
            <form class="d-flex" method="GET" action="{{ route('midwife.pregnancies.active') }}">
                <input class="form-control me-2" type="search" name="search" placeholder="Search active pregnancies..." value="{{ request('search') }}">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="bi bi-search"></i> Search
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4>{{ $pregnancies->total() }}</h4>
                    <p class="mb-0">Active Pregnancies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4>{{ $pregnancies->where('status', 'high_risk')->count() }}</h4>
                    <p class="mb-0">High Risk</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4>{{ $pregnancies->where('status', 'active')->count() }}</h4>
                    <p class="mb-0">Normal Risk</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4>{{ $pregnancies->where('status', 'active')->where('gravida', '>', 1)->count() }}</h4>
                    <p class="mb-0">Multiparous</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Active Pregnancies</h5>
        </div>
        <div class="card-body">
            @if($pregnancies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>LMP</th>
                                <th>EDD</th>
                                <th>Gravida</th>
                                <th>Para</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pregnancies as $pregnancy)
                                <tr>
                                    <td>
                                        @if($pregnancy->woman || $pregnancy->walkInPatient)
                                            <div>
                                                <strong>{{ $pregnancy->patient_name }}</strong>
                                            </div>
                                            <div>
                                                @if($pregnancy->user_id && $pregnancy->woman)
                                                    <small class="text-muted">{{ $pregnancy->woman->email }}</small>
                                                @elseif($pregnancy->walk_in_patient_id)
                                                    <small class="text-warning">Unlinked Patient</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">No patient</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($pregnancy->lmp)->format('M j, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($pregnancy->lmp)->addDays(280)->format('M j, Y') }}</td>
                                    <td>{{ $pregnancy->gravida }}</td>
                                    <td>{{ $pregnancy->para }}</td>
                                    <td>
                                        @switch($pregnancy->status)
                                            @case('active')
                                                <span class="badge bg-success">Active</span>
                                                @break
                                            @case('high_risk')
                                                <span class="badge bg-danger">High Risk</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $pregnancy->status ?? 'Unknown' }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('midwife.pregnancies.show', $pregnancy->id) }}" class="btn btn-sm btn-view" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('midwife.pregnancies.edit', $pregnancy->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('midwife.pregnancies.destroy', $pregnancy->id) }}" method="POST" onsubmit="return confirm('Archive this pregnancy record? It will be hidden from active records but retained for audit.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Archive">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $pregnancies->links() }}
                </div>
            @else
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-heart-pulse text-muted" style="font-size:4rem;"></i>
                        <h3 class="text-muted mt-3">No Active Pregnancies</h3>
                        <p class="text-muted">
                            No active pregnancy records found.
                        </p>
                        <a href="{{ route('midwife.pregnancies.create') }}" class="btn btn-primary">
                            <i class="bi bi-heart-plus"></i> Add Pregnancy
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
