<nav class="rp-bottom-nav d-lg-none">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
        <span class="material-symbols-outlined icon-fill">home</span>
        <span>Home</span>
    </a>
    <a href="{{ route('jobs.index') }}" class="{{ request()->routeIs('jobs.*') ? 'active' : '' }}">
        <span class="material-symbols-outlined">search</span>
        <span>Search</span>
    </a>
    <a href="{{ route('applications.index') }}" class="{{ request()->routeIs('applications.*') ? 'active' : '' }}">
        <span class="material-symbols-outlined">business_center</span>
        <span>My Jobs</span>
    </a>
    <a href="{{ route('profile.builder') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <span class="material-symbols-outlined">person</span>
        <span>Profile</span>
    </a>
</nav>
