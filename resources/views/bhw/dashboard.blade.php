@extends('bhw.layout')

@section('title', 'Dashboard - BHW Portal | ReproCare')

@section('bhw-content')

@php
    $hour = now()->hour;
    $timeOfDay = $hour < 12 ? 'Morning' : ($hour < 17 ? 'Afternoon' : 'Evening');
@endphp

{{-- PAGE HERO: white card, vertically centered (matches staff portal theme) --}}
<div class="page-hero">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div>
                <div class="page-hero-title">
                    Good {{ $timeOfDay }}, {{ auth()->user()->name }}!
                </div>
                <p class="page-hero-subtitle mb-0">
                    <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <button type="button"
                    class="btn-hero-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addRecordModal">
                <i class="bi bi-plus-circle-fill"></i> Add Health Record
            </button>
        </div>
    </div>
</div>

{{-- STAT CARDS: equal height, aligned --}}
<div class="row g-3 mb-4">

    <div class="col-xl-3 col-md-6 d-flex">
        <div class="stat-card w-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon mb-0"><i class="bi bi-people-fill"></i></div>
                <span class="summary-chip chip-primary" style="font-size:0.72rem;">Care</span>
            </div>
            <div class="stat-label">Total Patients</div>
            <div class="stat-number" data-count="{{ $totalPatients }}">0</div>
            <div class="stat-trend"><i class="bi bi-arrow-up-short" style="color:var(--color-success-text);"></i> Under your care</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 d-flex">
        <div class="stat-card w-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon mb-0"><i class="bi bi-calendar-check-fill"></i></div>
                <span class="summary-chip chip-primary" style="font-size:0.72rem;">Visits</span>
            </div>
            <div class="stat-label">Scheduled Checkups</div>
            <div class="stat-number" data-count="{{ $scheduledCheckups }}">0</div>
            <div class="stat-trend"><i class="bi bi-clock" style="color:var(--color-secondary-text);"></i> Upcoming sessions</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 d-flex">
        <div class="stat-card w-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon mb-0" style="background:var(--color-success-soft) !important;color:var(--color-success-text) !important;"><i class="bi bi-calendar-today-fill"></i></div>
                <span class="summary-chip chip-success" style="font-size:0.72rem;">Today</span>
            </div>
            <div class="stat-label">Today's Checkups</div>
            <div class="stat-number" data-count="{{ $todayCheckups }}">0</div>
            <div class="stat-trend"><i class="bi bi-sun" style="color:var(--color-success-text);"></i> Scheduled for today</div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 d-flex">
        <div class="stat-card w-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="stat-icon mb-0"><i class="bi bi-clipboard-pulse-fill"></i></div>
                <span class="summary-chip chip-primary" style="font-size:0.72rem;">Records</span>
            </div>
            <div class="stat-label">My Records</div>
            <div class="stat-number" data-count="{{ $myHealthRecords }}">0</div>
            <div class="stat-trend"><i class="bi bi-arrow-up-short" style="color:var(--color-success-text);"></i> Records added</div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════
     MAIN CONTENT GRID
═══════════════════════════════ --}}
<div class="row g-4">

    {{-- LEFT: Upcoming Checkups --}}
    <div class="col-lg-8">
        <div class="card fade-in-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-700" style="font-family:'Plus Jakarta Sans',sans-serif;">Upcoming Checkups
                </h5>
                <a href="{{ route('bhw.schedules') }}" class="btn btn-sm btn-outline-primary">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @if($upcomingCheckups->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Date</th>
                                    <th>Purpose</th>
                                    <th>Midwife</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingCheckups as $checkup)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--color-info),var(--color-info));display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:var(--color-on-solid);flex-shrink:0;">
                                                    {{ strtoupper(substr(optional($checkup->patient)->name ?? 'N/A', 0, 1)) }}
                                                </div>
                                                <span style="font-weight:500;">{{ optional($checkup->patient)->name ?? 'Unknown Patient' }}</span>
                                            </div>
                                        </td>
                                        <td style="color:var(--text-muted);font-size:0.875rem;">
                                            {{ $checkup->scheduled_date->format('M j, Y') }}
                                        </td>
                                        <td style="font-size:0.875rem;">{{ $checkup->purpose }}</td>
                                        <td style="font-size:0.875rem;color:var(--text-muted);">
                                            {{ $checkup->midwife ? $checkup->midwife->name : 'Not assigned' }}
                                        </td>
                                        <td>
                                            <span class="status-{{ strtolower($checkup->status) }}">
                                                {{ ucfirst($checkup->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-calendar-x empty-state-icon"></i>
                        <h6>No Upcoming Checkups</h6>
                        <p>All scheduled checkups will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div class="col-lg-4">

        {{-- Today's Checkups Card --}}
        <div class="card fade-in-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">Today's Schedule
                </h5>
                <span class="badge" style="background:var(--info);color:var(--color-on-solid);padding:0.35em 0.7em;border-radius:8px;">
                    {{ $todayCheckups }}
                </span>
            </div>
            <div class="card-body">
                @if($todayCheckups > 0)
                    <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-calendar-day-fill flex-shrink-0"></i>
                        <span><strong>{{ $todayCheckups }}</strong> checkup(s) scheduled for today.</span>
                    </div>
                    <a href="{{ route('bhw.schedules') }}" class="btn btn-info w-100">
                        <i class="bi bi-eye me-1"></i> View Today's Schedule
                    </a>
                @else
                    <div class="empty-state" style="padding:1.5rem;">
                        <i class="bi bi-calendar-check" style="font-size:2.5rem;color:var(--success);display:block;margin-bottom:0.75rem;"></i>
                        <h6 style="color:var(--success);">Clear Today!</h6>
                        <p class="mb-0">No checkups scheduled for today.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card fade-in-card mb-4">
            <div class="card-header">
                <h5 class="mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('bhw.patients') }}" class="quick-action-tile qa-blue">
                            <i class="bi bi-people-fill"></i>
                            <span>Patients</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('bhw.schedules') }}" class="quick-action-tile qa-teal">
                            <i class="bi bi-calendar-check-fill"></i>
                            <span>Schedules</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('bhw.health-records.index') }}" class="quick-action-tile qa-green">
                            <i class="bi bi-clipboard-pulse-fill"></i>
                            <span>My Records</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('forum.index') }}" class="quick-action-tile qa-violet">
                            <i class="bi bi-chat-dots-fill"></i>
                            <span>Forum</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- BHW Role Info - Redesigned --}}
        <div class="card fade-in-card">
            <div class="card-header">
                <h5 class="mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">Your BHW Capabilities
                </h5>
            </div>
            <div class="card-body p-0">
                @php
                    $capabilities = [
                        ['icon' => 'eye-fill',              'color' => 'var(--info)',    'text' => 'View patient information'],
                        ['icon' => 'clipboard-plus-fill',   'color' => 'var(--success)', 'text' => 'Add health records'],
                        ['icon' => 'calendar-check-fill',   'color' => 'var(--primary)', 'text' => 'View checkup schedules'],
                        ['icon' => 'chat-dots-fill',         'color' => 'var(--accent-pink)', 'text' => 'Participate in forum'],
                    ];
                @endphp
                <ul class="list-group list-group-flush">
                    @foreach($capabilities as $cap)
                        <li class="list-group-item d-flex align-items-center gap-3 py-3">
                            <div style="width:34px;height:34px;border-radius:10px;background:{{ $cap['color'] }}22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-{{ $cap['icon'] }}" style="color:{{ $cap['color'] }};font-size:0.95rem;"></i>
                            </div>
                            <span style="font-size:0.875rem;font-weight:500;">{{ $cap['text'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

    </div>
</div>

{{-- ═══════════════════════════════
     ADD HEALTH RECORD MODAL
═══════════════════════════════ --}}
<div class="modal fade" id="addRecordModal" tabindex="-1" aria-labelledby="addRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRecordModalLabel">Add Health Record
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addRecordForm" method="POST" action="{{ route('bhw.health-records.store', ':userId') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="patientSearch" class="form-label">
                                <i class="bi bi-search me-1" style="color:var(--primary-light);"></i>
                                Search Patient
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="patientSearch"
                                   placeholder="Search by name or email..."
                                   onkeyup="filterPatients()">
                        </div>

                        <div class="col-12">
                            <label for="patientSelect" class="form-label">
                                <i class="bi bi-person-fill me-1" style="color:var(--primary-light);"></i>
                                Select Patient
                            </label>
                            <select class="form-select" id="patientSelect" required>
                                <option value="">Choose a patient...</option>
                                @foreach(\App\Models\User::where('role', 'user')->where('status', 'approved')->get() as $woman)
                                    <option value="{{ $woman->id }}"
                                            data-search="{{ strtolower($woman->name . ' ' . $woman->email) }}">
                                        {{ $woman->name }} ({{ $woman->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <hr style="border-color:var(--border);margin:0.25rem 0;">
                            <div class="d-flex align-items-center gap-2 my-2">
                                <i class="bi bi-heart-pulse-fill" style="color:var(--primary-light);"></i>
                                <span style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);">Vitals</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="bp" class="form-label">Blood Pressure</label>
                            <input type="text" class="form-control" id="bp" name="bp"
                                   required placeholder="e.g., 120/80">
                        </div>

                        <div class="col-md-6">
                            <label for="weight" class="form-label">Weight (kg)</label>
                            <input type="number" class="form-control" id="weight" name="weight"
                                   required min="0" max="300" step="0.1" placeholder="e.g., 65.5">
                        </div>

                        <div class="col-md-6">
                            <label for="heart_rate" class="form-label">Heart Rate (bpm)</label>
                            <input type="number" class="form-control" id="heart_rate" name="heart_rate"
                                   required min="0" max="250" placeholder="e.g., 72">
                        </div>

                        <div class="col-md-6">
                            <label for="temperature" class="form-label">Temperature (°C)</label>
                            <input type="number" class="form-control" id="temperature" name="temperature"
                                   required min="30" max="45" step="0.1" placeholder="e.g., 36.5">
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                      placeholder="Add any observations or notes..."></textarea>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="submitHealthRecord()">
                    <i class="bi bi-save me-1"></i> Save Record
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterPatients() {
    const val = document.getElementById('patientSearch').value.toLowerCase();
    const opts = document.getElementById('patientSelect').getElementsByTagName('option');
    Array.from(opts).forEach(o => {
        const s = o.getAttribute('data-search') || o.text.toLowerCase();
        o.style.display = (s.indexOf(val) > -1 || o.value === '') ? '' : 'none';
    });
}

function submitHealthRecord() {
    const id = document.getElementById('patientSelect').value;
    if (!id) { alert('Please select a patient'); return; }
    const form = document.getElementById('addRecordForm');
    form.action = form.action.replace(':userId', id);
    form.submit();
}

// Sparklines
function drawSparkline(canvasId, data) {
    const c = document.getElementById(canvasId);
    if (!c || !window.Chart) return;
    new Chart(c, {
        type: 'line',
        data: {
            labels: data.map((_, i) => i),
            datasets: [{ data, borderColor: 'rgba(255,255,255,0.9)', borderWidth: 2,
                         pointRadius: 0, tension: 0.4, fill: false }]
        },
        options: {
            responsive: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } },
            animation: { duration: 1000 }
        }
    });
}

// Animate stat numbers
function animateStatNumbers() {
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const target = parseInt(stat.dataset.count);
        if (target > 0) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = target;
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.floor(current);
                }
            }, 30);
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    drawSparkline('bhwSparkline1', [5, 8, 7, 10, 9, 12, {{ $totalPatients }}]);
    drawSparkline('bhwSparkline2', [3, 5, 4, 7, 6, 8, {{ $scheduledCheckups }}]);
    drawSparkline('bhwSparkline3', [1, 2, 1, 3, 2, 4, {{ $todayCheckups }}]);
    drawSparkline('bhwSparkline4', [4, 6, 5, 8, 7, 9, {{ $myHealthRecords }}]);
    animateStatNumbers();
});
</script>
@endpush

@endsection
