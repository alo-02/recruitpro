<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ $brand->siteName() }}</title>
    @include('partials.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/recruitpro.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="@yield('body-class')">
    @include('partials.sidebar-' . ($sidebar ?? 'student'))

    <div class="rp-main-content has-bottom-nav">
        <header class="rp-navbar rp-dashboard-topbar px-3 px-lg-4">
            <div class="d-flex justify-content-between align-items-center w-100">
                <a href="{{ route('home') }}" class="rp-brand d-lg-none">
                    @include('partials.brand-mark', ['size' => 28])
                    <span class="rp-brand-word">{{ $brand->siteName() }}</span>
                </a>
                <nav class="d-flex align-items-center gap-3 gap-md-4">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    @auth
                        <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="nav-link {{ request()->routeIs('student.dashboard', 'recruiter.dashboard', 'admin.index') ? 'active' : '' }}">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link">Dashboard</a>
                    @endauth
                </nav>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('jobs.index') }}" class="btn btn-link p-0 text-secondary-rp d-none d-md-inline text-label-md text-decoration-none">Find Jobs</a>
                    @auth
                        <img src="{{ auth()->user()->avatarUrl() }}" class="rp-avatar rounded-circle" width="32" height="32" alt="{{ auth()->user()->name }}">
                    @endauth
                </div>
            </div>
        </header>
        <div class="p-3 p-lg-4">
            @include('partials.flash')
            @yield('content')
        </div>
    </div>

    @include('partials.mobile-nav')

    <div class="rp-toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/recruitpro.js') }}"></script>
    @stack('scripts')
</body>
</html>
