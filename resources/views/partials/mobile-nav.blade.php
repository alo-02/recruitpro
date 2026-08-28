<nav class="rp-bottom-nav d-lg-none">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
        <span class="material-symbols-outlined icon-fill">home</span>
        <span>Home</span>
    </a>
    @auth
    <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="{{ request()->routeIs('student.dashboard', 'recruiter.dashboard', 'admin.index') ? 'active' : '' }}">
        <span class="material-symbols-outlined">dashboard</span>
        <span>Dashboard</span>
    </a>
    @else
    <a href="{{ route('login') }}">
        <span class="material-symbols-outlined">dashboard</span>
        <span>Dashboard</span>
    </a>
    @endauth
    <a href="{{ route('jobs.index') }}" class="{{ request()->routeIs('jobs.*') ? 'active' : '' }}">
        <span class="material-symbols-outlined">search</span>
        <span>Search</span>
    </a>
    <a href="{{ auth()->check() ? route('profile.builder') : route('login') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <span class="material-symbols-outlined">person</span>
        <span>Profile</span>
    </a>
</nav>
