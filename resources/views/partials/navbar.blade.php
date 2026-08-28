<header class="rp-navbar px-3 px-md-4">
    <div class="container-rp mx-auto d-flex justify-content-between align-items-center w-100">
        <a href="{{ route('home') }}" class="rp-brand">RecruitPro</a>

        <nav class="d-none d-md-flex gap-4 h-100">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
            <a href="{{ route('jobs.index') }}" class="nav-link {{ request()->routeIs('jobs.index', 'jobs.show') ? 'active' : '' }}">Search</a>
            @auth
                <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="nav-link {{ request()->routeIs('student.dashboard', 'recruiter.dashboard', 'admin.index') ? 'active' : '' }}">Dashboard</a>
            @endauth
        </nav>

        <div class="d-flex align-items-center gap-3">
            @auth
                <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="btn btn-link text-secondary-rp text-label-md text-decoration-none p-0">Dashboard</a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-rp-outline btn-sm">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-link text-secondary-rp text-label-md text-decoration-none p-0">Sign In</a>
                <a href="{{ route('login') }}#signup" class="btn btn-rp-primary">Get Started</a>
            @endauth
        </div>
    </div>
</header>
