<nav class="rp-sidebar d-none d-lg-flex">
    <div class="p-3 border-bottom d-flex align-items-center gap-3">
        <div class="bg-primary-rp rounded d-flex align-items-center justify-content-center text-white" style="width:40px;height:40px;">
            <span class="material-symbols-outlined">business_center</span>
        </div>
        <div>
            <div class="text-title-lg fw-bold text-primary-rp">Enterprise Portal</div>
            <div class="text-label-md text-muted-rp">Recruiter Access</div>
        </div>
    </div>

    <div class="flex-grow-1 overflow-auto py-2">
        <a href="{{ route('recruiter.dashboard') }}" class="rp-sidebar-link {{ request()->routeIs('recruiter.dashboard') ? 'active' : '' }}">
            <span class="material-symbols-outlined {{ request()->routeIs('recruiter.dashboard') ? 'icon-fill' : '' }}">dashboard</span>
            Dashboard
        </a>
        <a href="{{ route('jobs.manage') }}" class="rp-sidebar-link {{ request()->routeIs('jobs.manage') ? 'active' : '' }}">
            <span class="material-symbols-outlined">work</span>
            Jobs
        </a>
        <a href="{{ route('applications.index') }}" class="rp-sidebar-link {{ request()->routeIs('applications.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">description</span>
            Applications
        </a>
        <a href="{{ route('interviews.index') }}" class="rp-sidebar-link {{ request()->routeIs('interviews.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">event</span>
            Interviews
        </a>
        <a href="{{ route('jobs.index') }}" class="rp-sidebar-link {{ request()->routeIs('jobs.index') ? 'active' : '' }}">
            <span class="material-symbols-outlined">search</span>
            Search
        </a>
        <a href="{{ route('talent-pool') }}" class="rp-sidebar-link {{ request()->routeIs('talent-pool') ? 'active' : '' }}">
            <span class="material-symbols-outlined">group</span>
            Talent Pool
        </a>
        <a href="{{ route('candidates.review') }}" class="rp-sidebar-link {{ request()->routeIs('candidates.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">rate_review</span>
            Review
        </a>
        <hr class="mx-3 my-2">
        <a href="{{ route('settings') }}" class="rp-sidebar-link {{ request()->routeIs('settings') ? 'active' : '' }}">
            <span class="material-symbols-outlined">settings</span>
            Settings
        </a>
    </div>

    <div class="p-3 border-top">
        <a href="{{ route('jobs.post') }}" class="btn btn-rp-primary w-100 d-flex align-items-center justify-content-center gap-2">
            <span class="material-symbols-outlined">add</span>
            Post New Role
        </a>
    </div>
</nav>
