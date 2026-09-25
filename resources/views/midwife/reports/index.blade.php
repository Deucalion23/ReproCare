@extends('midwife.layout')

@section('title', 'Reports - Midwife Portal')

@section('midwife-content')
<div class="workspace-stack">
    <div class="page-hero fade-in-card">
        <div class="workspace-toolbar" style="position:relative;z-index:1;">
            <div>
                <div class="page-hero-title">Reports Center</div>
                <p class="page-hero-subtitle">Review patient records, monitor maternal status, and export official summaries.</p>
            </div>
            <div class="workspace-toolbar-actions">
                <a href="{{ route('midwife.reports.export.csv') }}?search={{ request('search') }}&status={{ request('status') }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" class="btn-hero-secondary">
                    <i class="bi bi-file-earmark-excel"></i>CSV
                </a>
                <a href="{{ route('midwife.reports.export.pdf') }}?search={{ request('search') }}&status={{ request('status') }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" class="btn-hero-primary" target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i>PDF
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(124,58,237,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width:44px;height:44px;border-radius:14px;background:var(--color-primary-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-primary-text);">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <span style="background:var(--color-primary-soft);color:var(--color-primary-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Scope</span>
                </div>
                <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Total Patients</div>
                <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $totalPatients }}</div>
                <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                    <i class="bi bi-people"></i> Included in current scope
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(14,165,233,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width:44px;height:44px;border-radius:14px;background:var(--color-info-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-info-text);">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>
                    <span style="background:var(--color-info-soft);color:var(--color-info-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Maternal</span>
                </div>
                <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Pregnant Patients</div>
                <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $pregnantPatients }}</div>
                <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                    <i class="bi bi-activity"></i> Currently tracked cases
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(4,120,87,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width:44px;height:44px;border-radius:14px;background:var(--color-success-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-success-text);">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <span style="background:var(--color-success-soft);color:var(--color-success-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Completed</span>
                </div>
                <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Completed Checkups</div>
                <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $completedCheckups }}</div>
                <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                    <i class="bi bi-shield-check"></i> Finished visits in range
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card fade-in-card" style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:24px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;transition:transform .25s,box-shadow .25s;" onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 14px 32px rgba(217,119,6,0.12)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width:44px;height:44px;border-radius:14px;background:var(--color-warning-soft);display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--color-warning-text);">
                        <i class="bi bi-calendar2-week-fill"></i>
                    </div>
                    <span style="background:var(--color-warning-soft);color:var(--color-warning-text);border-radius:8px;font-size:0.72rem;font-weight:700;padding:0.2em 0.6em;">Pending</span>
                </div>
                <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--color-text-muted);margin-bottom:0.35rem;">Upcoming Appointments</div>
                <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:2.1rem;font-weight:800;line-height:1.1;color:var(--color-text);margin-bottom:0.4rem;">{{ $upcomingAppointments }}</div>
                <div style="font-size:0.8rem;font-weight:600;color:var(--color-text-muted);display:flex;align-items:center;gap:0.25rem;">
                    <i class="bi bi-calendar-check"></i> Requires attention
                </div>
            </div>
        </div>
    </div>

    <div class="workspace-panel fade-in-card">
        <div class="workspace-panel-header">
            <h2 class="workspace-panel-title">Filter Reports</h2>
            <p class="workspace-panel-subtitle">Search by patient, maternal status, or report period.</p>
        </div>
        <div class="workspace-panel-body">
            <form method="GET" action="{{ route('midwife.reports.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label fw-bold" style="font-size:0.84rem;color:var(--color-primary-text);">Search Patient</label>
                    <input type="text" name="search" class="form-control" style="height:42px;border-radius:12px;" placeholder="Search by name..." value="{{ $search }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-bold" style="font-size:0.84rem;color:var(--color-primary-text);">Status</label>
                    <select name="status" class="form-select" style="height:42px;border-radius:12px;">
                        <option value="all" {{ $status === 'all' || !$status ? 'selected' : '' }}>All</option>
                        <option value="pregnant" {{ $status === 'pregnant' ? 'selected' : '' }}>Pregnant</option>
                        <option value="postpartum" {{ $status === 'postpartum' ? 'selected' : '' }}>Postpartum</option>
                        <option value="not_pregnant" {{ $status === 'not_pregnant' ? 'selected' : '' }}>Not Pregnant</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-bold" style="font-size:0.84rem;color:var(--color-primary-text);">Start Date</label>
                    <input type="date" name="start_date" class="form-control" style="height:42px;border-radius:12px;" value="{{ $startDate }}">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label fw-bold" style="font-size:0.84rem;color:var(--color-primary-text);">End Date</label>
                    <input type="date" name="end_date" class="form-control" style="height:42px;border-radius:12px;" value="{{ $endDate }}">
                </div>
                <div class="col-lg-2 col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1" style="height:42px;border-radius:12px;font-weight:700;"><i class="bi bi-funnel me-1"></i>Apply</button>
                    <a href="{{ route('midwife.reports.index') }}" class="btn btn-outline-secondary" style="height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>

            <div class="section-chip-row mt-4">
                <a href="{{ route('midwife.reports.index', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')]) }}" class="summary-chip chip-primary">This Month</a>
                <a href="{{ route('midwife.reports.index', ['start_date' => now()->startOfYear()->format('Y-m-d'), 'end_date' => now()->endOfYear()->format('Y-m-d')]) }}" class="summary-chip chip-info">This Year</a>
                <a href="{{ route('midwife.reports.index', ['status' => 'pregnant']) }}" class="summary-chip chip-warning">Pregnant Only</a>
            </div>
        </div>
    </div>

    <div class="workspace-panel fade-in-card">
        <div class="workspace-panel-header">
            <h2 class="workspace-panel-title">Patient Report List</h2>
            <p class="workspace-panel-subtitle">Open a patient report to view pregnancy history, checkups, and health records.</p>
        </div>
        <div class="workspace-panel-body pt-3">
            @if($patients->count() > 0)
                <div class="modern-table-wrap">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Age</th>
                                <th>Status</th>
                                <th>Last Checkup</th>
                                <th>Next Appointment</th>
                                <th>Assigned BHW</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $woman)
                                <tr>
                                    <td>
                                        <div class="table-title">{{ $woman->name }}</div>
                                        <div class="table-subtitle">{{ $woman->email }}</div>
                                    </td>
                                    <td>{{ $woman->age }}</td>
                                    <td>
                                        @if($woman->pregnancy_status === 'Pregnant')
                                            <span class="summary-chip chip-info">Pregnant</span>
                                        @elseif($woman->pregnancy_status === 'Postpartum')
                                            <span class="summary-chip chip-warning">Postpartum</span>
                                        @else
                                            <span class="summary-chip chip-success">Not Pregnant</span>
                                        @endif
                                    </td>
                                    <td>{{ $woman->last_checkup ? $woman->last_checkup->scheduled_date->format('M d, Y') : 'None' }}</td>
                                    <td>
                                        @if($woman->next_appointment)
                                            <span class="summary-chip chip-success">{{ $woman->next_appointment->scheduled_date->format('M d, Y') }}</span>
                                        @else
                                            <span class="table-subtitle">None scheduled</span>
                                        @endif
                                    </td>
                                    <td>{{ $woman->assigned_bhw?->name ?? 'Unassigned' }}</td>
                                    <td class="text-end">
                                        <div class="table-actions">
                                            <a href="{{ route('midwife.reports.details', $woman->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center pt-4">
                    {{ $patients->links() }}
                </div>
            @else
                <div class="empty-state-panel">
                    <i class="bi bi-inboxes"></i>
                    <h3>No patients found</h3>
                    <p>Try adjusting your search terms or widening the date range to see more records.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
