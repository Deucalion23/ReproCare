@extends('midwife.layout')

@section('title', 'Pregnancies - ReproCare')

@section('midwife-content')

{{-- ═══════════════════════════════
     PAGE HERO
═══════════════════════════════ --}}
<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="page-hero-title">Pregnancy Tracking
            </div>
            <p class="page-hero-subtitle">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp; Manage all pregnancy records
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('midwife.pregnant-patients') }}" class="btn-hero-secondary">
                <i class="bi bi-heart-pulse-fill"></i> Pregnant Patients
            </a>
            <a href="{{ route('midwife.pregnancies.create') }}" class="btn-hero-primary">
                <i class="bi bi-plus-circle-fill"></i> Add Pregnancy
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     STAT CARDS (White bg with colored accents)
═══════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(124,58,237,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div style="width:44px;height:44px;border-radius:14px;background:var(--color-primary-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-primary-text);">
                    <i class="bi bi-heart-fill"></i>
                </div>
                <span style="background:var(--color-primary-soft);color:var(--color-primary-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">All</span>
            </div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Total Pregnancies</div>
            <div class="stat-number" data-count="{{ $pregnancies->total() }}" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">0</div>
            <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                <i class="bi bi-heart"></i> All recorded cases
            </div>
            <canvas id="sparkline1" width="80" height="36" style="position:absolute;bottom:1rem;right:1rem;opacity:0.25;"></canvas>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(4,120,87,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div style="width:44px;height:44px;border-radius:14px;background:var(--color-success-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-success-text);">
                    <i class="bi bi-heart-pulse-fill"></i>
                </div>
                <span style="background:var(--color-success-soft);color:var(--color-success-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Prenatal</span>
            </div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Active Monitoring</div>
            <div class="stat-number" data-count="{{ $pregnancies->where('status', 'active')->count() }}" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">0</div>
            <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                <i class="bi bi-activity"></i> Currently tracked
            </div>
            <canvas id="sparkline2" width="80" height="36" style="position:absolute;bottom:1rem;right:1rem;opacity:0.25;"></canvas>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(29,78,216,0.10)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div style="width:44px;height:44px;border-radius:14px;background:var(--color-info-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-info-text);">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <span style="background:var(--color-info-soft);color:var(--color-info-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Delivered</span>
            </div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Completed Deliveries</div>
            <div class="stat-number" data-count="{{ $pregnancies->where('status', 'completed')->count() }}" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">0</div>
            <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                <i class="bi bi-check2-all"></i> Postpartum transitioned
            </div>
            <canvas id="sparkline3" width="80" height="36" style="position:absolute;bottom:1rem;right:1rem;opacity:0.25;"></canvas>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(220,38,38,0.10)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div style="width:44px;height:44px;border-radius:14px;background:var(--color-danger-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-danger-text);">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <span style="background:var(--color-danger-soft);color:var(--color-danger-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Triage</span>
            </div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">High Risk Triage</div>
            <div class="stat-number" data-count="{{ $pregnancies->where('risk_level', 'High')->count() }}" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">0</div>
            <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                <i class="bi bi-arrow-up-short"></i> Requires clinical attention
            </div>
            <canvas id="sparkline4" width="80" height="36" style="position:absolute;bottom:1rem;right:1rem;opacity:0.25;"></canvas>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════
     SEARCH & SINGLE-ROW FILTERS
═══════════════════════════════ --}}
<div class="card fade-in-card mb-4">
    <div class="card-body p-3">
        <form action="{{ route('midwife.pregnancies.index') }}" method="GET" class="d-flex gap-2 align-items-center flex-wrap">
            <div class="position-relative flex-grow-1" style="min-width:260px; max-width:400px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" name="search" class="form-control ps-5" 
                       style="height:42px; border-radius:12px;"
                       placeholder="Search by patient name..." value="{{ request('search') }}">
            </div>
            <select name="trimester" class="form-select" style="width:180px; height:42px; border-radius:12px;" onchange="this.form.submit()">
                <option value="all" {{ $trimester === 'all' ? 'selected' : '' }}>All Trimesters</option>
                <option value="1" {{ $trimester === '1' ? 'selected' : '' }}>1st Trimester</option>
                <option value="2" {{ $trimester === '2' ? 'selected' : '' }}>2nd Trimester</option>
                <option value="3" {{ $trimester === '3' ? 'selected' : '' }}>3rd Trimester</option>
            </select>
            <select name="risk_level" class="form-select" style="width:170px; height:42px; border-radius:12px;" onchange="this.form.submit()">
                <option value="all" {{ $riskLevel === 'all' ? 'selected' : '' }}>All Risk Levels</option>
                <option value="Low" {{ $riskLevel === 'Low' ? 'selected' : '' }}>Low Risk</option>
                <option value="Medium" {{ $riskLevel === 'Medium' ? 'selected' : '' }}>Medium Risk</option>
                <option value="High" {{ $riskLevel === 'High' ? 'selected' : '' }}>High Risk</option>
            </select>
            @if(request('search') || request('trimester', 'all') !== 'all' || request('risk_level', 'all') !== 'all')
                <a href="{{ route('midwife.pregnancies.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none fw-semibold">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

{{-- ═══════════════════════════════
     PREGNANCY RECORDS TABLE
═══════════════════════════════ --}}
<div class="card fade-in-card" style="border:none; background:var(--bg-card);">
    <div class="card-header d-flex justify-content-between align-items-center" 
         style="background:transparent; border-bottom:1px solid var(--border-color); padding:1rem 1.5rem;">
        <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--text);">Pregnancy Records
        </h5>
    </div>
    
    <div class="card-body p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" 
                 style="background:color-mix(in srgb, var(--color-success-text) 10%, transparent); border:1px solid color-mix(in srgb, var(--color-success-text) 30%, transparent); color:var(--success); border-radius:10px;">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter:invert(1);"></button>
            </div>
        @endif

        @if($pregnancies->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="font-size:0.9rem;">
                    <thead>
                        <tr style="color:var(--text-muted); font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">
                            <th style="padding:1rem; border:none;">Patient</th>
                            <th style="padding:1rem; border:none;">Gestational Age</th>
                            <th style="padding:1rem; border:none;">Trimester</th>
                            <th style="padding:1rem; border:none;">LMP</th>
                            <th style="padding:1rem; border:none;">EDD</th>
                            <th style="padding:1rem; border:none;">Status</th>
                            <th style="padding:1rem; border:none;">Risk</th>
                            <th style="padding:1rem; border:none; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pregnancies as $pregnancy)
                            <tr style="border-bottom:1px solid var(--border-color);">
                                <td style="padding:1rem;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,var(--primary),var(--accent-violet));display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--color-on-solid);flex-shrink:0;">
                                            {{ strtoupper(substr($pregnancy->patient_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight:600; color:var(--text);">{{ $pregnancy->patient_name }}</div>
                                            <small style="color:var(--text-muted);">
                                                @if($pregnancy->user_id && $pregnancy->woman)
                                                    {{ $pregnancy->woman->email }}
                                                @elseif($pregnancy->walk_in_patient_id)
                                                    Unlinked Patient
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:1rem;">
                                    <div style="font-weight:600; color:var(--text);">{{ $pregnancy->formatted_aog }}</div>
                                </td>
                                <td style="padding:1rem;">
                                    @if($pregnancy->trimester)
                                        <div style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.35rem 0.75rem; background:color-mix(in srgb, var(--color-info) 10%, transparent); color:var(--info); border-radius:20px; font-weight:500; font-size:0.85rem;">
                                            <i class="bi bi-calendar-week"></i>
                                            {{ $pregnancy->trimester_name }}
                                        </div>
                                    @else
                                        <span style="color:var(--text-muted);">N/A</span>
                                    @endif
                                </td>
                                <td style="padding:1rem;">
                                    <div style="font-weight:500; color:var(--text);">
                                        {{ \Carbon\Carbon::parse($pregnancy->lmp)->format('M j, Y') }}
                                    </div>
                                </td>
                                <td style="padding:1rem;">
                                    <div style="font-weight:500; color:var(--text);">
                                        {{ \Carbon\Carbon::parse($pregnancy->lmp)->addDays(280)->format('M j, Y') }}
                                    </div>
                                    @if($pregnancy->is_active)
                                        <small style="color:var(--text-muted);">{{ $pregnancy->days_until_due }} days left</small>
                                    @endif
                                </td>
                                <td style="padding:1rem;">
                                    <div style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.35rem 0.75rem; background:{{ $pregnancy->status === 'completed' ? 'color-mix(in srgb, var(--color-surface-soft) 10%, transparent)' : 'color-mix(in srgb, var(--color-success-text) 10%, transparent)' }}; color:{{ $pregnancy->status === 'completed' ? 'var(--text-muted)' : 'var(--success)' }}; border-radius:20px; font-weight:500; font-size:0.85rem;">
                                        {{ ucfirst($pregnancy->status ?? 'Unknown') }}
                                    </div>
                                </td>
                                <td style="padding:1rem;">
                                    @php $rLevel = $pregnancy->risk_level ?? 'Low'; @endphp
                                    @if($rLevel === 'High')
                                        <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background:var(--color-danger-soft); border:1px solid var(--color-secondary-soft); color:var(--color-secondary-text);">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> High Risk
                                        </span>
                                    @elseif($rLevel === 'Medium')
                                        <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background:var(--color-peach-soft); border:1px solid var(--color-peach-soft); color:var(--color-peach-text);">
                                            <i class="bi bi-exclamation-circle-fill me-1"></i> Medium Risk
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background:var(--color-success-soft); border:1px solid var(--color-success-soft); color:var(--color-success-text);">
                                            <i class="bi bi-check-circle-fill me-1"></i> Low Risk
                                        </span>
                                    @endif
                                </td>
                                <td style="padding:1rem; text-align:right;">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('midwife.pregnancies.show', $pregnancy->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           style="border-radius:8px; padding:0.4rem 0.6rem;"
                                           title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('midwife.pregnancies.edit', $pregnancy->id) }}" 
                                           class="btn btn-sm btn-outline-warning" 
                                           style="border-radius:8px; padding:0.4rem 0.6rem;"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-4">
                {{ $pregnancies->links('pagination::bootstrap-5') }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-5">
                <div style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,var(--primary),var(--accent-violet));display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                    <i class="bi bi-heart" style="font-size:2.5rem;color:var(--color-on-solid);"></i>
                </div>
                <h4 style="font-weight:700; color:var(--text); margin-bottom:0.5rem;">No Pregnancies Found</h4>
                <p style="color:var(--text-muted); margin-bottom:1.5rem;">No pregnancy records have been added yet.</p>
                <a href="{{ route('midwife.pregnancies.create') }}" class="btn btn-primary" style="border-radius:10px; padding:0.75rem 1.5rem;">
                    <i class="bi bi-plus-circle me-2"></i>Add First Pregnancy
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Sparkline helper
function drawSparkline(canvasId, data, color) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || !window.Chart) return;
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.map((_, i) => i),
            datasets: [{
                data: data,
                borderColor: color || 'rgba(255,255,255,0.9)',
                borderWidth: 2,
                pointRadius: 0,
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } },
            animation: { duration: 1000 }
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    drawSparkline('sparkline1', [5, 8, 6, 10, 9, 12, {{ $pregnancies->total() }}], 'rgba(255,255,255,0.9)');
    drawSparkline('sparkline2', [3, 5, 4, 7, 6, 8, {{ $pregnancies->where('status', 'active')->count() }}], 'rgba(255,255,255,0.9)');
    drawSparkline('sparkline3', [2, 4, 3, 5, 4, 6, {{ $pregnancies->where('status', 'completed')->count() }}], 'rgba(255,255,255,0.9)');
    drawSparkline('sparkline4', [1, 2, 1, 3, 2, 4, {{ $pregnancies->where('risk_level', 'High')->count() }}], 'rgba(255,255,255,0.9)');
});
</script>
@endpush

@endsection
