<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - RecruitPro</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/recruitpro.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="@yield('body-class')">
    {{-- Mobile header --}}
    <header class="rp-navbar d-lg-none px-3">
        <div class="d-flex justify-content-between align-items-center w-100">
            <a href="{{ route('home') }}" class="rp-brand">RecruitPro</a>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-link p-0 text-secondary-rp"><span class="material-symbols-outlined">search</span></button>
                <button class="btn btn-link p-0 text-muted-rp"><span class="material-symbols-outlined">notifications</span></button>
                <img src="https://ui-avatars.com/api/?name=User&background=0051d5&color=fff" class="rounded-circle" width="32" height="32" alt="Avatar">
            </div>
        </div>
    </header>

    @include('partials.sidebar-' . ($sidebar ?? 'student'))

    <div class="rp-main-content has-bottom-nav">
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
