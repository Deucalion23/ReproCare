@php
    $user = auth()->user();
    $role = null;
    $roleLabel = null;
    $roleColor = null;
    $unreadMsgs = 0;
    $pendingCount = 0;
    $routeRole = null;

    if ($user) {
        $role = $user->role;
        $routeRole = $role === 'bhw_president' ? 'bhw-president' : $role;
        if ($role === 'cho') {
            $roleLabel = 'CHO Admin';
            $roleColor = 'var(--color-primary)'; /* Soft Pink */
        } elseif ($role === 'rhu') {
            $roleLabel = 'RHU1 (Rural Health Unit 1)';
            $roleColor = 'var(--color-pink)'; /* Brand pink accent */
            $pendingCount = \App\Models\User::where('role', 'user')->where('status', 'pending')->count();
        } elseif ($role === 'midwife') {
            $roleLabel = 'Midwife';
            $roleColor = 'var(--color-primary)'; /* Soft Pink */
            $unreadMsgs = \App\Models\Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();
        } elseif ($role === 'bhw') {
            $roleLabel = 'BHW';
            $roleColor = 'var(--color-pink)'; /* Brand pink accent */
            $unreadMsgs = \App\Models\Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();
        } elseif ($role === 'bhw_president') {
            $roleLabel = 'BHW President';
            $roleColor = 'var(--color-primary-text)'; /* Soft Pink Deep */
            $unreadMsgs = \App\Models\Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();
            $pendingCount = \App\Models\User::where('role', 'user')->where('status', 'pending')->count();
        } elseif ($role === 'user') {
            $roleLabel = 'Patient';
            $roleColor = 'var(--color-purple)'; /* Interactive purple */
            $unreadMsgs = \App\Models\Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();
        }
    }
@endphp

@if($user && $role !== 'user')
<nav class="sidebar" id="sidebar">
    {{-- Floating Overlap Badge — 32px circular toggle anchored to right border --}}
    <button id="sidebarCollapseBtn" class="sidebar-edge-toggle" aria-label="Collapse sidebar" title="Collapse sidebar" type="button">
        <svg id="sidebarCollapseIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M15 18L9 12L15 6"/>
        </svg>
    </button>
    <div class="sidebar-inner">
    @if($role === 'cho')
        <div class="sidebar-section-label">Overview</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.dashboard') ? 'active' : '' }}"
                   href="{{ route('cho.dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.analytics') ? 'active' : '' }}"
                   href="{{ route('cho.analytics') }}">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Analytics</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('learning.*') ? 'active' : '' }}"
                   href="{{ route('learning.index') }}">
                    <i class="bi bi-camera-video-fill"></i>
                    <span>Learning Materials</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Management</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.users*') ? 'active' : '' }}"
                   href="{{ route('cho.users.index') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.supply-requests*') ? 'active' : '' }}"
                   href="{{ route('cho.supply-requests.index') }}">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>Supply Requests</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Maternal Audit</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.maternal-deaths*') ? 'active' : '' }}"
                   href="{{ route('cho.maternal-deaths.index') }}">
                    <i class="bi bi-journal-x"></i>
                    <span>Maternal Deaths</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.reports*') ? 'active' : '' }}"
                   href="{{ route('cho.reports.index') }}">
                    <i class="bi bi-file-earmark-text-fill"></i>
                    <span>City Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.pregnancies*') ? 'active' : '' }}"
                   href="{{ route('cho.pregnancies.index') }}">
                    <i class="bi bi-heart-pulse-fill"></i>
                    <span>Pregnancies</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.handover*') ? 'active' : '' }}"
                   href="{{ route('cho.handover.index') }}">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Succession Handover</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">System</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.sms*') ? 'active' : '' }}"
                   href="{{ route('cho.sms.index') }}">
                    <i class="bi bi-chat-text-fill"></i>
                    <span>SMS Monitor</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.database*') ? 'active' : '' }}"
                   href="{{ route('cho.database.index') }}">
                    <i class="bi bi-database-fill"></i>
                    <span>Backup</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.archived*') ? 'active' : '' }}"
                   href="{{ route('cho.archived.index') }}">
                    <i class="bi bi-archive-fill"></i>
                    <span>Archived Records</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.logs*') ? 'active' : '' }}"
                   href="{{ route('cho.logs.index') }}">
                    <i class="bi bi-activity"></i>
                    <span>Activity Logs</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cho.settings') ? 'active' : '' }}"
                   href="{{ route('cho.settings') }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>

    @elseif($role === 'rhu')
        <div class="sidebar-section-label">Overview</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.dashboard') ? 'active' : '' }}"
                   href="{{ route('rhu.dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.reports*') ? 'active' : '' }}"
                   href="{{ route('rhu.reports.index') }}">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>FHSIS Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.analytics*') ? 'active' : '' }}"
                   href="{{ route('rhu.analytics') }}">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Analytics</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Management</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.pending-patients*') ? 'active' : '' }}"
                   href="{{ route('rhu.pending-patients') }}">
                    <i class="bi bi-person-check-fill"></i>
                    <span>Account Verification</span>
                    @if($pendingCount > 0)
                        <span style="background:color-mix(in srgb, var(--color-warning) 20%, transparent);color:var(--color-warning-text);font-size:0.68rem;font-weight:700;padding:0.1em 0.5em;border-radius:20px;margin-left:auto;">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.patients.create') ? 'active' : '' }}"
                   href="{{ route('rhu.patients.create') }}">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Register Woman</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.midwives*') ? 'active' : '' }}"
                   href="{{ route('rhu.midwives.index') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Midwife Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.bhw-presidents*') ? 'active' : '' }}"
                   href="{{ route('rhu.bhw-presidents.index') }}">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>BHW Presidents</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.bhws*') ? 'active' : '' }}"
                   href="{{ route('rhu.bhws.index') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>BHWs</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.supply-requests*') ? 'active' : '' }}"
                   href="{{ route('rhu.supply-requests.index') }}">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>Supply Requests</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Maternal Health</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.maternal-deaths*') ? 'active' : '' }}"
                   href="{{ route('rhu.maternal-deaths.index') }}">
                    <i class="bi bi-journal-x"></i>
                    <span>Maternal Deaths</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.morbidities*') ? 'active' : '' }}"
                   href="{{ route('rhu.morbidities.index') }}">
                    <i class="bi bi-heart-pulse-fill"></i>
                    <span>Near-Miss Events</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">BHW Submissions</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.bhw-reports*') ? 'active' : '' }}"
                   href="{{ route('rhu.bhw-reports.index') }}">
                    <i class="bi bi-file-earmark-text-fill"></i>
                    <span>BHW Monthly Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('workflow.transfers*') ? 'active' : '' }}"
                   href="{{ route('workflow.transfers.index') }}">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Patient Transfers</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">System</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.staff-transitions*') ? 'active' : '' }}"
                   href="{{ route('rhu.staff-transitions.index') }}">
                    <i class="bi bi-person-gear"></i>
                    <span>Staff Handover</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.database*') ? 'active' : '' }}"
                   href="{{ route('rhu.database.index') }}">
                    <i class="bi bi-database-fill-gear"></i>
                    <span>Database Backup</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.logs*') ? 'active' : '' }}"
                   href="{{ route('rhu.logs.index') }}">
                    <i class="bi bi-activity"></i>
                    <span>Activity Logs</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.sms.*') ? 'active' : '' }}"
                   href="{{ route('rhu.sms.index') }}">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span>SMS Alerts</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rhu.settings') ? 'active' : '' }}"
                   href="{{ route('rhu.settings') }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>

    @elseif($role === 'user')
        <div class="sidebar-section-label">My Health</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('user.pregnancies*') ? 'active' : '' }}"
                   href="{{ route('user.pregnancies.index') }}">
                    <i class="bi bi-heart-pulse-fill"></i>
                    <span>Pregnancy Tracking</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('user.menstruation*') ? 'active' : '' }}"
                   href="#menstruationMenu"
                   data-bs-toggle="collapse"
                   role="button"
                   aria-expanded="{{ request()->routeIs('user.menstruation*') ? 'true' : 'false' }}"
                   aria-controls="menstruationMenu">
                    <i class="bi bi-calendar-heart-fill"></i>
                    <span>Menstrual Cycle</span>
                    <i class="bi bi-chevron-down ms-auto"
                       style="font-size:0.7rem; transition:transform 0.25s ease;"
                       id="menstruChevron"></i>
                </a>
                <div class="collapse {{ request()->routeIs('user.menstruation*') ? 'show' : '' }}"
                     id="menstruationMenu">
                    <div class="sub-menu">
                        <a class="nav-link {{ request()->routeIs('user.menstruation.index') ? 'active' : '' }}"
                           href="{{ route('user.menstruation.index') }}">
                            <i class="bi bi-list-ul"></i>
                            <span>Overview</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('user.menstruation.calendar') ? 'active' : '' }}"
                           href="{{ route('user.menstruation.calendar') }}">
                            <i class="bi bi-calendar3"></i>
                            <span>Calendar</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('user.menstruation.statistics') ? 'active' : '' }}"
                           href="{{ route('user.menstruation.statistics') }}">
                            <i class="bi bi-bar-chart-line-fill"></i>
                            <span>Statistics</span>
                        </a>
                    </div>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('user.checkups*') ? 'active' : '' }}"
                   href="{{ route('user.checkups') }}">
                    <i class="bi bi-clipboard-heart-fill"></i>
                    <span>Checkups</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('user.health-records*') ? 'active' : '' }}"
                   href="{{ route('user.health-records') }}">
                    <i class="bi bi-file-medical-fill"></i>
                    <span>Health Records</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Community</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('user.messages.*') ? 'active' : '' }}"
                   href="{{ route('user.messages.index') }}">
                    <i class="bi bi-chat-text-fill"></i>
                    <span>Messages</span>
                    @if($unreadMsgs > 0)
                        <span style="background:color-mix(in srgb, var(--color-primary) 20%, transparent);color:var(--primary-light);font-size:0.68rem;font-weight:700;padding:0.1em 0.5em;border-radius:20px;margin-left:auto;">{{ $unreadMsgs }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('forum.*') ? 'active' : '' }}"
                   href="{{ route('forum.index') }}">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span>Community Forum</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('learning.*') ? 'active' : '' }}"
                   href="{{ route('learning.index') }}">
                    <i class="bi bi-book-fill"></i>
                    <span>Learning Materials</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">System</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('user.notifications*') ? 'active' : '' }}"
                   href="{{ route('user.notifications') }}">
                    <i class="bi bi-bell-fill"></i>
                    <span>Notifications</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('user.settings') ? 'active' : '' }}"
                   href="{{ route('user.settings') }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    @elseif($role === 'midwife')
        <div class="sidebar-section-label">Overview</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.dashboard') ? 'active' : '' }}"
                   href="{{ route('midwife.dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Women &amp; Care</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.patients*') ? 'active' : '' }}"
                   href="{{ route('midwife.patients') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Women</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.decision-support') ? 'active' : '' }}" href="{{ route('midwife.decision-support') }}">
                    <i class="bi bi-clipboard2-check"></i><span>Decision Support</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.pregnancies*') ? 'active' : '' }}"
                   href="{{ route('midwife.pregnancies.index') }}">
                    <i class="bi bi-heart-fill"></i>
                    <span>Pregnancies</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.postpartum*') ? 'active' : '' }}"
                   href="{{ route('midwife.postpartum.index') }}">
                    <i class="bi bi-balloon-heart-fill"></i>
                    <span>Postpartum &amp; Newborn</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.checkups*') ? 'active' : '' }}"
                   href="{{ route('midwife.checkups.index') }}">
                    <i class="bi bi-clipboard-heart-fill"></i>
                    <span>Checkups</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.referrals*') ? 'active' : '' }}"
                   href="{{ route('midwife.referrals.index') }}">
                    <i class="bi bi-share-fill"></i>
                    <span>Checkup Referrals</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.risk-alerts') ? 'active' : '' }}"
                   href="{{ route('midwife.risk-alerts') }}">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>Risk Alerts</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.health-records*') ? 'active' : '' }}"
                   href="{{ route('midwife.health-records.index') }}">
                    <i class="bi bi-file-medical-fill"></i>
                    <span>Health Records</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Content</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.sms.*') ? 'active' : '' }}"
                   href="{{ route('midwife.sms.index') }}">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span>SMS Alerts</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.messages.*') ? 'active' : '' }}"
                   href="{{ route('midwife.messages.index') }}">
                    <i class="bi bi-chat-text-fill"></i>
                    <span>Messages</span>
                    @if($unreadMsgs > 0)
                        <span style="background:color-mix(in srgb, var(--color-primary) 20%, transparent);color:var(--primary-light);font-size:0.68rem;font-weight:700;padding:0.1em 0.5em;border-radius:20px;margin-left:auto;">{{ $unreadMsgs }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('forum.*') && !request()->routeIs('midwife.forum.admin.*') ? 'active' : '' }}"
                   href="{{ route('forum.index') }}">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span>Community Forum</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.forum.admin.*') ? 'active' : '' }}"
                   href="{{ route('midwife.forum.admin.index') }}">
                    <i class="bi bi-shield-check"></i>
                    <span>Forum Admin</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.learning*') || request()->routeIs('learning.*') ? 'active' : '' }}"
                   href="{{ route('midwife.learning.index') }}">
                    <i class="bi bi-mortarboard-fill"></i>
                    <span>Learning Materials</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Reports</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.reports*') ? 'active' : '' }}"
                   href="{{ route('midwife.reports.index') }}">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.maternal-care-target-clients*') ? 'active' : '' }}"
                   href="{{ route('midwife.maternal-care-target-clients.index') }}">
                    <i class="bi bi-table"></i>
                    <span>Maternal Client List</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.child-care-target-clients*') ? 'active' : '' }}"
                   href="{{ route('midwife.child-care-target-clients.index') }}">
                    <i class="bi bi-tablet-landscape"></i>
                    <span>Childcare Client List</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">System</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.notifications*') ? 'active' : '' }}"
                   href="{{ route('midwife.notifications.index') }}">
                    <i class="bi bi-bell-fill"></i>
                    <span>Notifications</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('midwife.settings') ? 'active' : '' }}"
                   href="{{ route('midwife.settings') }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    @elseif($role === 'bhw_president')
        <div class="sidebar-section-label">Overview</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw-president.dashboard') ? 'active' : '' }}"
                   href="{{ route('bhw-president.dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">BHW Management</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw-president.bhws*') ? 'active' : '' }}"
                   href="{{ route('bhw-president.bhws.index') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>All BHWs</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw-president.health-records*') ? 'active' : '' }}"
                   href="{{ route('bhw-president.health-records.index') }}">
                    <i class="bi bi-clipboard2-check-fill"></i>
                    <span>Record Review</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw-president.tasks*') ? 'active' : '' }}"
                   href="{{ route('bhw-president.tasks.index') }}">
                    <i class="bi bi-list-task"></i>
                    <span>Tasks</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw-president.pregnancies*') ? 'active' : '' }}"
                   href="{{ route('bhw-president.pregnancies.index') }}">
                    <i class="bi bi-heart-pulse-fill"></i>
                    <span>Pregnancy Review</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Analytics</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw-president.analytics') ? 'active' : '' }}"
                   href="{{ route('bhw-president.analytics') }}">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Health Analytics</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw-president.coverage') ? 'active' : '' }}"
                   href="{{ route('bhw-president.coverage') }}">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Coverage Report</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw-president.high-risk') ? 'active' : '' }}"
                   href="{{ route('bhw-president.high-risk') }}">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>High-Risk Cases</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Reports</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw-president.reports*') ? 'active' : '' }}"
                   href="{{ route('bhw-president.reports.index') }}">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>Monthly Reports</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Content</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw-president.messages.*') ? 'active' : '' }}"
                   href="{{ route('bhw-president.messages.index') }}">
                    <i class="bi bi-chat-text-fill"></i>
                    <span>Messages</span>
                    @if($unreadMsgs > 0)
                        <span style="background:color-mix(in srgb, var(--color-primary) 20%, transparent);color:var(--primary-light);font-size:0.68rem;font-weight:700;padding:0.1em 0.5em;border-radius:20px;margin-left:auto;">{{ $unreadMsgs }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('forum.*') ? 'active' : '' }}"
                   href="{{ route('forum.index') }}">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span>Community Forum</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('learning.*') ? 'active' : '' }}"
                   href="{{ route('learning.index') }}">
                    <i class="bi bi-mortarboard-fill"></i>
                    <span>Learning Materials</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">System</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw-president.settings') ? 'active' : '' }}"
                   href="{{ route('bhw-president.settings') }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    @else
        <div class="sidebar-section-label">Overview</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw.dashboard') ? 'active' : '' }}"
                   href="{{ route('bhw.dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Women &amp; Care</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw.patients*') ? 'active' : '' }}"
                   href="{{ route('bhw.patients') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Women</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw.checkups*') ? 'active' : '' }}"
                   href="{{ route('bhw.checkups.index') }}">
                    <i class="bi bi-clipboard-heart-fill"></i>
                    <span>Checkups</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw.pregnancies*') ? 'active' : '' }}"
                   href="{{ route('bhw.pregnancies.index') }}">
                    <i class="bi bi-heart-fill"></i>
                    <span>Pregnancies</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw.postpartum*') ? 'active' : '' }}"
                   href="{{ route('bhw.postpartum.index') }}">
                    <i class="bi bi-balloon-heart-fill"></i>
                    <span>Postpartum &amp; Newborn</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw.health-records*') ? 'active' : '' }}"
                   href="{{ route('bhw.health-records.index') }}">
                    <i class="bi bi-file-medical-fill"></i>
                    <span>Health Records</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">Content</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw.messages.*') ? 'active' : '' }}"
                   href="{{ route('bhw.messages.index') }}">
                    <i class="bi bi-chat-text-fill"></i>
                    <span>Messages</span>
                    @if($unreadMsgs > 0)
                        <span style="background:color-mix(in srgb, var(--color-primary) 20%, transparent);color:var(--primary-light);font-size:0.68rem;font-weight:700;padding:0.1em 0.5em;border-radius:20px;margin-left:auto;">{{ $unreadMsgs }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('forum.*') ? 'active' : '' }}"
                   href="{{ route('forum.index') }}">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span>Community Forum</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw.learning*') || request()->routeIs('learning.*') ? 'active' : '' }}"
                   href="{{ route('bhw.learning.index') }}">
                    <i class="bi bi-mortarboard-fill"></i>
                    <span>Learning Materials</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-section-label">System</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw.reports*') ? 'active' : '' }}"
                   href="{{ route('bhw.reports.index') }}">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw.tasks*') ? 'active' : '' }}"
                   href="{{ route('bhw.tasks.index') }}">
                    <i class="bi bi-list-task"></i>
                    <span>My Tasks</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw.notifications*') ? 'active' : '' }}"
                   href="{{ route('bhw.notifications.index') }}">
                    <i class="bi bi-bell-fill"></i>
                    <span>Notifications</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('bhw.settings') ? 'active' : '' }}"
                   href="{{ route('bhw.settings') }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    @endif

    <div class="sidebar-footer mt-auto">
        <img src="{{ $user->profile_image_url ?? '/images/avatars/avatar-default.svg' }}"
             alt="{{ $user->name }}"
             class="sidebar-footer-avatar"
             onerror="this.onerror=null;this.src='{{ $user->gender === 'male'
                 ? '/images/avatars/avatar-male.svg'
                 : '/images/avatars/avatar-female.svg' }}';">
        <div class="sidebar-footer-info">
            <div class="sidebar-footer-name">{{ $user->name }}</div>
            <div class="sidebar-footer-role" style="color:{{ $roleColor }};">
                <i class="bi bi-circle-fill me-1" style="font-size:0.45rem; vertical-align:middle;"></i>{{ $roleLabel }}
            </div>
        </div>
    </div>
    </div>{{-- /.sidebar-inner --}}
</nav>

{{-- Preserve sidebar scroll position across page reloads --}}
<script>
(function() {
    function initSidebarScroll() {
        var sidebar = document.querySelector('#sidebar .sidebar-inner') || document.getElementById('sidebar');
        if (!sidebar) return;
        var saved = localStorage.getItem('reprocare_sidebar_scrollTop');
        if (saved !== null) {
            sidebar.scrollTop = parseInt(saved, 10) || 0;
        }
        sidebar.addEventListener('scroll', function() {
            localStorage.setItem('reprocare_sidebar_scrollTop', sidebar.scrollTop);
        }, { passive: true });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarScroll);
    } else {
        initSidebarScroll();
    }
})();
</script>
@endif
