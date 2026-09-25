@extends('cho.layout')

@section('title', 'Archived Records Hub - CHO Admin | ReproCare')

@push('styles')
<style>
    .arch-mini .card { border:none !important; border-radius:18px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; transition:transform .2s ease, box-shadow .2s ease; }
    .arch-mini .card:hover { transform:translateY(-3px); box-shadow:0 10px 26px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 10%, transparent) !important; }
    .arch-mini .card.border-primary { background:var(--color-surface-strong) !important; background-color:var(--color-surface-strong) !important; }
    .arch-mini .card.border-primary i, .arch-mini .card.border-primary div, .arch-mini .card.border-primary h4 { color:var(--color-on-solid) !important; }
    .arch-wrap { border:none !important; border-radius:20px !important; box-shadow:0 2px 12px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 6%, transparent) !important; overflow:hidden; }
    .arch-wrap .card-header { border:none !important; }
    .arch-tabs .nav-link { border:none !important; border-radius:999px !important; font-weight:800 !important; font-size:0.8rem !important; padding:0.5rem 1.05rem !important; color:var(--color-text) !important; background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; }
    .arch-tabs .nav-link.active { background:var(--color-surface-strong) !important; background-color:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; box-shadow:0 6px 16px color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent); }
    .arch-search { background:var(--color-surface-soft) !important; background-color:var(--color-surface-soft) !important; border:none !important; border-radius:999px !important; overflow:hidden; }
    .arch-search .form-control { background:transparent !important; border:none !important; box-shadow:none !important; }
    .arch-search .btn { border:none !important; color:var(--color-text) !important; }
    .arch-wrap table thead th { border:none !important; background:transparent !important; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-muted) !important; }
    .arch-wrap table tbody td { border:none !important; }
    .arch-wrap table tbody tr:hover { background:var(--color-bg); }
    .arch-restore-btn { border:none !important; border-radius:999px !important; padding:0.4rem 1rem !important; font-weight:800 !important; font-size:0.78rem !important; background:var(--color-success-soft) !important; background-color:var(--color-success-soft) !important; color:var(--color-success-text) !important; }
    .arch-restore-btn:hover { background:var(--color-surface-strong) !important; background-color:var(--color-surface-strong) !important; color:var(--color-on-solid) !important; }
</style>
@endpush

@section('cho-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Archived Records & Data Integrity
            </div>
            <p class="page-hero-subtitle" style="font-weight:600;">Comprehensive data retention hub. Review and restore soft-deleted records across all system modules.</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-secondary p-2 d-flex align-items-center">
                <i class="bi bi-shield-lock-fill me-1"></i> Zero Hard Deletes Active
            </span>
        </div>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Summary Metric Cards --}}
<div class="row g-3 mb-4 arch-mini">
    <div class="col-6 col-md-4 col-lg-2">
        <a href="?tab=patients" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $activeTab === 'patients' ? 'border-primary shadow-sm' : '' }}" style="border-radius:14px;">
                <i class="bi bi-people text-primary mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Archived Patients</div>
                <h4 class="fw-800 text-primary mb-0 mt-1">{{ $stats['patients'] }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="?tab=staff" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $activeTab === 'staff' ? 'border-primary shadow-sm' : '' }}" style="border-radius:14px;">
                <i class="bi bi-person-badge text-info mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Archived Staff</div>
                <h4 class="fw-800 text-info mb-0 mt-1">{{ $stats['staff'] }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="?tab=health_records" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $activeTab === 'health_records' ? 'border-primary shadow-sm' : '' }}" style="border-radius:14px;">
                <i class="bi bi-file-earmark-medical text-warning mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Health Records</div>
                <h4 class="fw-800 text-warning mb-0 mt-1">{{ $stats['health_records'] }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="?tab=materials" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $activeTab === 'materials' ? 'border-primary shadow-sm' : '' }}" style="border-radius:14px;">
                <i class="bi bi-play-circle text-danger mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Videos / Materials</div>
                <h4 class="fw-800 text-danger mb-0 mt-1">{{ $stats['learning_materials'] }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="?tab=pregnancies" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $activeTab === 'pregnancies' ? 'border-primary shadow-sm' : '' }}" style="border-radius:14px;">
                <i class="bi bi-heart-pulse text-success mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Pregnancies</div>
                <h4 class="fw-800 text-success mb-0 mt-1">{{ $stats['pregnancies'] }}</h4>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="?tab=supplies" class="text-decoration-none">
            <div class="card fade-in-card p-3 text-center h-100 {{ $activeTab === 'supplies' ? 'border-primary shadow-sm' : '' }}" style="border-radius:14px;">
                <i class="bi bi-box-seam text-secondary mb-1" style="font-size:1.4rem;"></i>
                <div class="text-muted" style="font-size:0.75rem;">Supply Requests</div>
                <h4 class="fw-800 text-secondary mb-0 mt-1">{{ $stats['supply_requests'] }}</h4>
            </div>
        </a>
    </div>
</div>

{{-- Main Tabbed Container --}}
<div class="card fade-in-card arch-wrap" style="border-radius:20px;">
    <div class="card-header bg-transparent p-3" style="border:none;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            {{-- Navigation Tabs --}}
            <ul class="nav nav-pills arch-tabs gap-2 flex-wrap" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'patients' ? 'active' : '' }}" href="?tab=patients">
                        <i class="bi bi-people me-1"></i> Patients ({{ $stats['patients'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'staff' ? 'active' : '' }}" href="?tab=staff">
                        <i class="bi bi-person-badge me-1"></i> Staff ({{ $stats['staff'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'health_records' ? 'active' : '' }}" href="?tab=health_records">
                        <i class="bi bi-file-earmark-medical me-1"></i> Medical Records ({{ $stats['health_records'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'materials' ? 'active' : '' }}" href="?tab=materials">
                        <i class="bi bi-play-circle me-1"></i> Videos & Learning ({{ $stats['learning_materials'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'pregnancies' ? 'active' : '' }}" href="?tab=pregnancies">
                        <i class="bi bi-heart-pulse me-1"></i> Pregnancies ({{ $stats['pregnancies'] }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'supplies' ? 'active' : '' }}" href="?tab=supplies">
                        <i class="bi bi-box-seam me-1"></i> Supplies ({{ $stats['supply_requests'] }})
                    </a>
                </li>
            </ul>

            {{-- Tab Search Filter --}}
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="input-group input-group-sm arch-search" style="max-width:260px;">
                    <input type="text" name="search" class="form-control" placeholder="Search in this tab..." value="{{ $search }}">
                    <button class="btn" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body p-0">

        {{-- TAB 1: ARCHIVED PATIENTS --}}
        @if($activeTab === 'patients')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-3">Patient Name</th>
                            <th class="py-3">Contact / Email</th>
                            <th class="py-3">Barangay</th>
                            <th class="py-3">Archived On</th>
                            <th class="py-3 text-end px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($patients as $patient)
                            <tr>
                                <td class="px-3">
                                    <div class="fw-700">{{ $patient->first_name }} {{ $patient->last_name }}</div>
                                    <small class="text-muted">ID: #{{ $patient->id }}</small>
                                </td>
                                <td>
                                    <div>{{ $patient->contact_number ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $patient->email }}</small>
                                </td>
                                <td>{{ $patient->barangay ?? 'N/A' }}</td>
                                <td>
                                    <span class="text-muted">{{ $patient->deleted_at ? $patient->deleted_at->format('M d, Y h:i A') : 'N/A' }}</span>
                                </td>
                                <td class="text-end px-3">
                                    <form action="{{ route('cho.archived.restore', ['type' => 'patient', 'id' => $patient->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore patient record for {{ $patient->name }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm arch-restore-btn">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-archive" style="font-size:2rem;"></i>
                                    <p class="mt-2 mb-0">No archived patients found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($patients->hasPages())
                <div class="p-3">{{ $patients->links() }}</div>
            @endif
        @endif

        {{-- TAB 2: ARCHIVED STAFF --}}
        @if($activeTab === 'staff')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-3">Staff Name</th>
                            <th class="py-3">Role</th>
                            <th class="py-3">Email / Contact</th>
                            <th class="py-3">Archived On</th>
                            <th class="py-3 text-end px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $member)
                            <tr>
                                <td class="px-3">
                                    <div class="fw-700">{{ $member->first_name }} {{ $member->last_name }}</div>
                                    <small class="text-muted">ID: #{{ $member->id }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-600 text-uppercase" style="font-size:0.75rem;">
                                        {{ str_replace('_', ' ', $member->role) }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $member->email }}</div>
                                    <small class="text-muted">{{ $member->contact_number ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $member->deleted_at ? $member->deleted_at->format('M d, Y h:i A') : 'N/A' }}</span>
                                </td>
                                <td class="text-end px-3">
                                    <form action="{{ route('cho.archived.restore', ['type' => 'staff', 'id' => $member->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore staff account for {{ $member->name }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm arch-restore-btn">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x" style="font-size:2rem;"></i>
                                    <p class="mt-2 mb-0">No archived staff accounts found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($staff->hasPages())
                <div class="p-3">{{ $staff->links() }}</div>
            @endif
        @endif

        {{-- TAB 3: HEALTH RECORDS --}}
        @if($activeTab === 'health_records')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-3">Record ID & Patient</th>
                            <th class="py-3">Vital Signs / Vitals</th>
                            <th class="py-3">Risk Level</th>
                            <th class="py-3">Archived Date</th>
                            <th class="py-3 text-end px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($healthRecords as $record)
                            <tr>
                                <td class="px-3">
                                    <div class="fw-700">Record #{{ $record->id }}</div>
                                    <small class="text-muted">{{ $record->user?->name ?? 'Patient' }}</small>
                                </td>
                                <td>
                                    <div>BP: <strong>{{ $record->bp ?? 'N/A' }}</strong> | Hb: <strong>{{ $record->hemoglobin ?? 'N/A' }}</strong></div>
                                    <small class="text-muted">Weight: {{ $record->weight ?? 'N/A' }} kg</small>
                                </td>
                                <td>
                                    @php
                                        $badgeColor = match(strtolower($record->risk_level ?? 'low')) {
                                            'critical', 'high' => 'danger',
                                            'medium' => 'warning',
                                            default => 'success',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}">{{ ucfirst($record->risk_level ?? 'Low') }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $record->deleted_at ? $record->deleted_at->format('M d, Y') : 'N/A' }}</span>
                                </td>
                                <td class="text-end px-3">
                                    <form action="{{ route('cho.archived.restore', ['type' => 'health-record', 'id' => $record->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm arch-restore-btn">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-file-earmark-medical" style="font-size:2rem;"></i>
                                    <p class="mt-2 mb-0">No archived health records.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($healthRecords->hasPages())
                <div class="p-3">{{ $healthRecords->links() }}</div>
            @endif
        @endif

        {{-- TAB 4: VIDEOS / LEARNING MATERIALS --}}
        @if($activeTab === 'materials')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-3">Material Title</th>
                            <th class="py-3">Type & Category</th>
                            <th class="py-3">Media Source</th>
                            <th class="py-3">Archived On</th>
                            <th class="py-3 text-end px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($learningMaterials as $mat)
                            <tr>
                                <td class="px-3">
                                    <div class="fw-700">{{ $mat->title }}</div>
                                    <small class="text-muted">{{ Str::limit($mat->content, 60) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary me-1">{{ ucfirst($mat->material_type) }}</span>
                                    <span class="badge bg-light text-dark border">{{ ucfirst($mat->category ?? 'General') }}</span>
                                </td>
                                <td>
                                    @if($mat->video_url)
                                        <span class="text-truncate d-inline-block" style="max-width:200px;"><i class="bi bi-camera-video me-1 text-danger"></i>{{ $mat->video_url }}</span>
                                    @elseif($mat->file)
                                        <span class="text-truncate d-inline-block" style="max-width:200px;"><i class="bi bi-file-earmark-arrow-down me-1 text-primary"></i>{{ basename($mat->file) }}</span>
                                    @else
                                        <span class="text-muted">Article/Text</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">{{ $mat->deleted_at ? $mat->deleted_at->format('M d, Y') : 'N/A' }}</span>
                                </td>
                                <td class="text-end px-3">
                                    <form action="{{ route('cho.archived.restore', ['type' => 'learning-material', 'id' => $mat->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm arch-restore-btn">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-camera-video-off" style="font-size:2rem;"></i>
                                    <p class="mt-2 mb-0">No archived videos or learning materials found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($learningMaterials->hasPages())
                <div class="p-3">{{ $learningMaterials->links() }}</div>
            @endif
        @endif

        {{-- TAB 5: PREGNANCIES --}}
        @if($activeTab === 'pregnancies')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-3">Pregnancy ID & Patient</th>
                            <th class="py-3">EDD / AOG</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Archived On</th>
                            <th class="py-3 text-end px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pregnancies as $preg)
                            <tr>
                                <td class="px-3">
                                    <div class="fw-700">Pregnancy #{{ $preg->id }}</div>
                                    <small class="text-muted">{{ $preg->user?->name ?? 'Patient' }}</small>
                                </td>
                                <td>
                                    <div>EDD: {{ $preg->expected_delivery_date ? \Carbon\Carbon::parse($preg->expected_delivery_date)->format('M d, Y') : 'N/A' }}</div>
                                    <small class="text-muted">AOG: {{ $preg->aog_weeks ?? 'N/A' }} weeks</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($preg->status ?? 'archived') }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $preg->deleted_at ? $preg->deleted_at->format('M d, Y') : 'N/A' }}</span>
                                </td>
                                <td class="text-end px-3">
                                    <form action="{{ route('cho.archived.restore', ['type' => 'pregnancy', 'id' => $preg->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm arch-restore-btn">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-heartbreak" style="font-size:2rem;"></i>
                                    <p class="mt-2 mb-0">No archived pregnancies found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pregnancies->hasPages())
                <div class="p-3">{{ $pregnancies->links() }}</div>
            @endif
        @endif

        {{-- TAB 6: SUPPLIES --}}
        @if($activeTab === 'supplies')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-3">Supply Item</th>
                            <th class="py-3">Requested By</th>
                            <th class="py-3">Quantity</th>
                            <th class="py-3">Archived On</th>
                            <th class="py-3 text-end px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplyRequests as $sup)
                            <tr>
                                <td class="px-3">
                                    <div class="fw-700">{{ $sup->supply_name }}</div>
                                    <small class="text-muted">{{ ucfirst($sup->category ?? 'General') }}</small>
                                </td>
                                <td>{{ $sup->requestedBy?->name ?? 'Staff' }}</td>
                                <td>{{ $sup->quantity }} {{ $sup->unit ?? 'units' }}</td>
                                <td>
                                    <span class="text-muted">{{ $sup->deleted_at ? $sup->deleted_at->format('M d, Y') : 'N/A' }}</span>
                                </td>
                                <td class="text-end px-3">
                                    <form action="{{ route('cho.archived.restore', ['type' => 'supply-request', 'id' => $sup->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm arch-restore-btn">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-box" style="font-size:2rem;"></i>
                                    <p class="mt-2 mb-0">No archived supply requests found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($supplyRequests->hasPages())
                <div class="p-3">{{ $supplyRequests->links() }}</div>
            @endif
        @endif

    </div>
</div>

@endsection
