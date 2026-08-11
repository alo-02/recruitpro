<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RecruitPro') - Internship & Job Portal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/recruitpro.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100 @yield('body-class')">
    @include('partials.navbar')

    <main class="flex-grow-1">
        <div class="container-rp mx-auto px-3 pt-3">
            @include('partials.flash')
        </div>
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.mobile-nav')

    <div class="rp-toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/recruitpro.js') }}"></script>
    @stack('scripts')
</body>
</html>
