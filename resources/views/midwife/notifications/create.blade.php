@extends('midwife.layout')

@section('title', 'Create Notification - ReproCare')

@section('midwife-content')
<div>
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-1">Create Notification
                    </h1>
                    <p class="text-muted mb-0">Send notifications to users and health workers</p>
                </div>
                <div>
                    <a href="{{ route('midwife.notifications.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>

<!-- Create Notification Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h5 class="mb-0">Notification Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('midwife.notifications.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Notification Type</label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_info" value="info" checked>
                                    <label class="form-check-label" for="type_info">
                                        <i class="bi bi-info-circle text-info"></i> Information
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_warning" value="warning">
                                    <label class="form-check-label" for="type_warning">
                                        <i class="bi bi-exclamation-triangle text-warning"></i> Warning
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_success" value="success">
                                    <label class="form-check-label" for="type_success">
                                        <i class="bi bi-check-circle text-success"></i> Success
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_error" value="error">
                                    <label class="form-check-label" for="type_error">
                                        <i class="bi bi-x-circle text-danger"></i> Error
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" placeholder="Enter notification title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Message *</label>
                        <textarea class="form-control" name="message" rows="5" placeholder="Enter your notification message" required></textarea>
                        <small class="text-muted">Maximum 1000 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Target Audience</label>
                        <select class="form-select" name="target_role">
                            <option value="">All Users</option>
                            <option value="user">Women Only</option>
                            <option value="bhw">BHWs Only</option>
                            <option value="midwife">Midwives Only</option>
                        </select>
                        <small class="text-muted">Choose who will see this notification</small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Notification
                        </button>
                        <a href="{{ route('midwife.notifications.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0">Quick Templates</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <button type="button" class="list-group-item list-group-item-action text-start" onclick="fillTemplate('system-maintenance')">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-gear text-warning me-2"></i>
                            <div>
                                <h6 class="mb-0">System Maintenance</h6>
                                <small class="text-muted">Scheduled system updates</small>
                            </div>
                        </div>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action text-start" onclick="fillTemplate('new-feature')">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-star text-info me-2"></i>
                            <div>
                                <h6 class="mb-0">New Feature</h6>
                                <small class="text-muted">Announce new features</small>
                            </div>
                        </div>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action text-start" onclick="fillTemplate('health-alert')">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                            <div>
                                <h6 class="mb-0">Health Alert</h6>
                                <small class="text-muted">Important health updates</small>
                            </div>
                        </div>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action text-start" onclick="fillTemplate('reminder')">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-check text-success me-2"></i>
                            <div>
                                <h6 class="mb-0">Reminder</h6>
                                <small class="text-muted">General reminders</small>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0">Recent Notifications</h6>
            </div>
            <div class="card-body">
                @php
                    $recentNotifications = auth()->user()->notifications()
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                @endphp
                
                @if($recentNotifications->count() > 0)
                    <div class="timeline">
                        @foreach($recentNotifications as $notification)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $notification->type === 'info' ? 'info' : ($notification->type === 'warning' ? 'warning' : ($notification->type === 'success' ? 'success' : 'danger')) }}"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ $notification->title }}</h6>
                                    <p class="text-muted small mb-0">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center">No recent notifications</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function fillTemplate(type) {
    const templates = {
        'system-maintenance': {
            type: 'warning',
            title: 'Scheduled System Maintenance',
            message: 'The ReproCare system will undergo scheduled maintenance on [date] from [start time] to [end time]. Please save your work and log out before the maintenance period.',
            target_role: ''
        },
        'new-feature': {
            type: 'info',
            title: 'New Feature Available',
            message: 'We are excited to announce the launch of [feature name]! This new feature will help you [benefit]. Check it out in your dashboard.',
            target_role: ''
        },
        'health-alert': {
            type: 'error',
            title: 'Important Health Alert',
            message: 'Attention all healthcare providers: [health issue details]. Please take appropriate action and follow updated protocols.',
            target_role: ''
        },
        'reminder': {
            type: 'success',
            title: 'Important Reminder',
            message: 'This is a reminder about [reminder topic]. Please ensure you [required action] by [deadline].',
            target_role: ''
        }
    };
    
    const template = templates[type];
    if (template) {
        document.querySelector(`input[name="type"][value="${template.type}"]`).checked = true;
        document.querySelector('input[name="title"]').value = template.title;
        document.querySelector('textarea[name="message"]').value = template.message;
        document.querySelector('select[name="target_role"]').value = template.target_role;
    }
}
</script>

<style>
.timeline {
    position:relative;
    padding-left:20px;
}

.timeline-item {
    position:relative;
    margin-bottom:1.5rem;
}

.timeline-marker {
    position:absolute;
    left:-25px;
    top:5px;
    width:10px;
    height:10px;
    border-radius:50%;
}

.timeline-content {
    background:var(--color-surface-soft);
    padding:1rem;
    border-radius:8px;
    border-left:3px solid var(--primary-color);
}
</style>
        </div>
    </div>
</div>
@endsection
