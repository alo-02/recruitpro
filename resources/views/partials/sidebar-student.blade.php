<nav class="rp-sidebar d-none d-lg-flex">
    <div class="p-3 border-bottom d-flex align-items-center gap-3">
        <div class="bg-primary-rp rounded d-flex align-items-center justify-content-center text-white" style="width:40px;height:40px;">
            <span class="material-symbols-outlined">school</span>
        </div>
        <div>
            <div class="text-title-lg fw-bold text-primary-rp">Student Portal</div>
            <div class="text-label-md text-muted-rp">Candidate Access</div>
        </div>
    </div>

    <div class="flex-grow-1 overflow-auto py-2">
        <a href="{{ route('student.dashboard') }}" class="rp-sidebar-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <span class="material-symbols-outlined {{ request()->routeIs('student.dashboard') ? 'icon-fill' : '' }}">dashboard</span>
            Dashboard
        </a>
        <a href="{{ route('jobs.index') }}" class="rp-sidebar-link {{ request()->routeIs('jobs.index') ? 'active' : '' }}">
            <span class="material-symbols-outlined">search</span>
            Search Jobs
        </a>
        <a href="{{ route('applications.index') }}" class="rp-sidebar-link {{ request()->routeIs('applications.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">description</span>
            Applications
        </a>
        <a href="{{ route('messages.index') }}" class="rp-sidebar-link {{ request()->routeIs('messages.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">chat</span>
            Messages
        </a>
        <a href="{{ route('profile.builder') }}" class="rp-sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">person</span>
            Profile
        </a>
        <hr class="mx-3 my-2">
        <a href="{{ route('settings') }}" class="rp-sidebar-link {{ request()->routeIs('settings') ? 'active' : '' }}">
            <span class="material-symbols-outlined">settings</span>
            Settings
        </a>
    </div>

    <div class="p-3 border-top">
        <a href="{{ route('jobs.index') }}" class="btn btn-rp-primary w-100 d-flex align-items-center justify-content-center gap-2">
            <span class="material-symbols-outlined">search</span>
            Find Opportunities
        </a>
    </div>
</nav>
