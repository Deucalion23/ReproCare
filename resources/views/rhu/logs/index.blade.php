@extends('rhu.layout')

@section('title', 'Activity Logs - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">System Activity Logs
            </div>
            <p class="page-hero-subtitle">
                Audit trail of midwife registrations, BHW presidents, supply requests, and clinical morbidity entries.
            </p>
        </div>
    </div>
</div>

<div class="card fade-in-card">
    <div class="card-body p-0">
        @if($logs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Timestamp</th>
                            <th>User Name</th>
                            <th>Role</th>
                            <th>Action Type</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td class="ps-4" style="font-size:0.82rem; color:var(--text-muted);">
                                    {{ $log->created_at->format('M j, Y g:i:s A') }}
                                </td>
                                <td>
                                    <div class="fw-700 text-dark" style="font-size:0.875rem;">
                                        {{ $log->user->name ?? 'System' }}
                                    </div>
                                    @if($log->user)
                                        <div style="font-size:0.72rem; color:var(--text-muted);">{{ $log->user->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $roleClass = match($log->user_role) {
                                            'cho' => 'badge-role-cho',
                                            'rhu' => 'badge-role-rhu',
                                            'midwife' => 'badge-role-midwife',
                                            'bhw_president' => 'badge-role-bhw-president',
                                            'bhw' => 'badge-role-bhw',
                                            default => 'badge-role-user'
                                        };
                                        $roleLabel = match($log->user_role) {
                                            'cho' => 'CHO Admin',
                                            'rhu' => 'RHU Admin',
                                            'midwife' => 'Midwife',
                                            'bhw_president' => 'BHW President',
                                            'bhw' => 'BHW',
                                            default => ucfirst($log->user_role ?? 'System')
                                        };
                                    @endphp
                                    <span class="badge {{ $roleClass }} px-2.5 py-1 rounded-pill" style="font-size:0.72rem;">
                                        {{ $roleLabel }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $actionColor = match($log->action) {
                                            'create' => 'success',
                                            'update' => 'info',
                                            'delete' => 'danger',
                                            'approve' => 'success',
                                            'reject' => 'warning',
                                            'request_supply' => 'primary',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $actionColor }} text-white text-xs">
                                        {{ str_replace('_', ' ', ucfirst($log->action)) }}
                                    </span>
                                </td>
                                <td style="font-size:0.875rem; color:var(--text);">
                                    {{ $log->description }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="card-footer bg-transparent border-top">
                    {{ $logs->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="bi bi-activity" style="font-size:3rem; color:var(--text-muted);"></i>
                <h5 class="mt-3">No Activity Logs Found</h5>
                <p class="text-muted text-xs">System logs will populate as staff perform actions.</p>
            </div>
        @endif
    </div>
</div>

@endsection
