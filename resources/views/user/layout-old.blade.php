<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ReproCare - Maternal Health System')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color:var(--color-primary-text);


            --secondary-color:var(--color-secondary);
            --success-color:var(--color-success);
            --warning-color:var(--color-warning);
            --danger-color:var(--color-danger);
            --info-color:var(--color-info);
        }
        
        body {
            background-color:var(--color-surface-soft);
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight:bold;
            color:var(--primary-color) !important;
        }
        
        .btn-primary {
            background-color:var(--primary-color);
            border-color:var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color:var(--primary-dark);
            border-color:var(--primary-dark);
        }
        
        .card-header {
            background-color:var(--primary-color);
            color:var(--color-on-solid);
        }
        
        .sidebar {
            background-color:var(--color-surface);
            border-right:1px solid var(--color-border);
            min-height:calc(100vh - 56px);
            position:fixed;
            top:56px;
            left:0;
            width:240px;
            z-index:100;
        }
        
        .sidebar .nav-link {
            color:var(--color-text-muted);
            padding:0.75rem 1rem;
            border-radius:0;
            display:flex;
            align-items:center;
        }
        
        .sidebar .nav-link:hover {
            background-color:var(--color-surface-soft);
            color:var(--primary-color);
        }
        
        .sidebar .nav-link.active {
            background-color:var(--primary-color);
            color:var(--color-on-solid);
        }
        
        .sidebar .nav-link i {
            margin-right:0.5rem;
        }
        
        .main-content {
            margin-left:240px;
            padding:1rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform:translateX(-100%);
                transition:transform 0.3s ease;
            }
            
            .sidebar.show {
                transform:translateX(0);
            }
            
            .main-content {
                margin-left:0;
            }
        }
        
        .status-badge {
            padding:0.25rem 0.5rem;
            border-radius:0.375rem;
            font-size:0.875rem;
            font-weight:500;
        }
        
        .status-scheduled {
            background-color:var(--color-info-soft);
            color:var(--color-primary-text);
        }
        
        .status-completed {
            background-color:var(--color-success-soft);
            color:var(--color-success-text);
        }
        
        .status-missed {
            background-color:var(--color-danger-soft);
            color:var(--color-danger-text);
        }
        
        .risk-low {
            background-color:var(--color-success-soft);
            color:var(--color-success-text);
        }
        
        .risk-medium {
            background-color:var(--color-warning-soft);
            color:var(--color-warning-text);
        }
        
        .risk-high {
            background-color:var(--color-danger-soft);
            color:var(--color-danger-text);
        }
        
        .notification-badge {
            position:absolute;
            top:-5px;
            right:-5px;
            background-color:var(--danger-color);
            color:var(--color-on-solid);
            border-radius:50%;
            padding:0.125rem 0.375rem;
            font-size:0.75rem;
            min-width:1.25rem;
            text-align:center;
        }
        
        .stats-card {
            background:linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color:var(--color-on-solid);
            border:none;
        }
        
        .stats-card .card-body {
            padding:1.5rem;
        }
        
        .stats-number {
            font-size:2rem;
            font-weight:bold;
        }
        
        .form-control:focus {
            border-color:var(--primary-color);
            box-shadow:0 0 0 0.2rem color-mix(in srgb, rgb(var(--color-shadow-rgb)) 25%, transparent);
        }
        
        .table-hover tbody tr:hover {
            background-color:var(--color-surface-soft);
        }
        
        .footer {
            background-color:var(--primary-color);
            color:var(--color-on-solid);
            padding:1rem 0;
            margin-top:auto;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-heart-pulse-fill"></i> ReproCare
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Notifications -->
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            <span class="notification-badge" id="notificationCount">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" id="notificationDropdownMenu">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="{{ route('user.notifications') }}">View All</a></li>
                        </ul>
                    </li>
                    
                    <!-- User Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('user.profile') }}">
                                <i class="bi bi-person"></i> Profile
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('user-logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Logout Form -->
    <form id="user-logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
        @method('POST')
    </form>
    
    @extends('layouts.app')

@section('title', 'User Dashboard - ReproCare')

@section('content')
<div class="main-content fade-in">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="position-sticky pt-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}" href="{{ route('user.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.profile') ? 'active' : '' }}" href="{{ route('user.profile') }}">
                        <i class="bi bi-person"></i> My Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.pregnancies.*') ? 'active' : '' }}" href="{{ route('user.pregnancies.index') }}">
                        <i class="bi bi-heart"></i> Pregnancy Tracking
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.menstruation.*') ? 'active' : '' }}" href="{{ route('user.menstruation.index') }}">
                        <i class="bi bi-calendar3"></i> Menstruation Tracking
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.checkups') ? 'active' : '' }}" href="{{ route('user.checkups') }}">
                        <i class="bi bi-calendar-check"></i> My Checkups
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.health-records') ? 'active' : '' }}" href="{{ route('user.health-records') }}">
                        <i class="bi bi-clipboard-pulse"></i> Health Records
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('forum.*') ? 'active' : '' }}" href="{{ route('forum.index') }}">
                        <i class="bi bi-chat-dots"></i> Community Forum
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('learning.*') ? 'active' : '' }}" href="{{ route('learning.index') }}">
                        <i class="bi bi-book"></i> Learning Materials
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.notifications') ? 'active' : '' }}" href="{{ route('user.notifications') }}">
                        <i class="bi bi-bell"></i> Notifications
                        @if(($unreadNotifications ?? 0) > 0)
                            <span class="badge bg-danger ms-1">{{ $unreadNotifications }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content flex-grow-1">
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="footer mt-auto">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; {{ date('Y') }} ReproCare Maternal Health System</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Providing quality maternal healthcare</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    @stack('scripts')
    
    <!-- Notification System -->
    <script>
        // Load notifications
        function loadNotifications() {
            $.get('{{ route("api.notifications.unread-count") }}', function(data) {
                $('#notificationCount').text(data.count);
                if (data.count > 0) {
                    $('#notificationCount').show();
                } else {
                    $('#notificationCount').hide();
                }
            });
            
            $.get('{{ route("api.notifications.recent") }}', function(data) {
                let html = '<li><h6 class="dropdown-header">Notifications</h6></li><li><hr class="dropdown-divider"></li>';
                
                if (data.notifications.length === 0) {
                    html += '<li><a class="dropdown-item" href="#">No new notifications</a></li>';
                } else {
                    data.notifications.forEach(function(notification) {
                        html += '<li><a class="dropdown-item" href="#" onclick="markAsRead(' + notification.id + ')">' + notification.message + '</a></li>';
                    });
                }
                    
                html += '<li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-center" href="{{ route("user.notifications") }}">View All</a></li>';
                $('#notificationDropdownMenu').html(html);
            });
        }
        
        function markAsRead(notificationId) {
            $.post('{{ route("api.notifications.mark-read", ":id") }}'.replace(':id', notificationId), function() {
                loadNotifications();
            });
        }
        
        // Load notifications on page load
        $(document).ready(function() {
            loadNotifications();
            
            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
            
            // Simple back button protection
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        });
    </script>
</body>
</html>
