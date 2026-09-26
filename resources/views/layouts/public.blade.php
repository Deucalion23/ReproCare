<!DOCTYPE html>
<html lang="en">
<head>
    @include('includes.appearance-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FBF7FA">
    <meta name="description" content="ReproCare connects women in San Carlos City with their community care team. Follow your pregnancy, manage checkups, and stay informed.">
    <title>@yield('title', 'ReproCare — Care for every chapter')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/brand/reprocare-logo.png?v=5') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&display=swap" rel="stylesheet">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/public.css') }}?v={{ filemtime(public_path('css/public.css')) }}">
    @vite('resources/css/theme.css')
</head>
<body class="public-page @yield('page-class')">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="public-header">
        <div class="public-container header-inner">
            <a class="public-brand" href="{{ url('/') }}" aria-label="ReproCare home">
                <img src="{{ asset('images/brand/reprocare-logo.png?v=5') }}" width="40" height="40" alt="">
                <span>Repro<span class="brand-accent">Care</span></span>
            </a>
            @yield('navigation')
        </div>
    </header>
    <main id="main-content">@yield('content')</main>
    <footer class="public-footer public-container">
        <span>&copy; {{ date('Y') }} ReproCare</span>
        <span>Made for women. Connected to community.</span>
        <span>San Carlos City, Pangasinan</span>
    </footer>
    @stack('scripts')
</body>
</html>
