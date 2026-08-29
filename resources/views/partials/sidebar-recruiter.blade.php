<nav class="rp-sidebar d-none d-lg-flex">
    <div class="p-3 border-bottom d-flex align-items-center gap-3">
        <a href="{{ route('home') }}" class="rp-brand">
            @include('partials.brand-mark', ['size' => 36])
        </a>
        <div>
            <div class="text-title-lg fw-bold text-primary-rp">Recruiter Portal</div>
            <div class="text-label-md text-muted-rp">Look at the fit</div>
        </div>
    </div>

    <div class="flex-grow-1 overflow-auto py-2">
        <a href="{{ route('recruiter.dashboard') }}" class="rp-sidebar-link {{ request()->routeIs('recruiter.dashboard') ? 'active' : '' }}">
            <span class="material-symbols-outlined">dashboard</span> Dashboard
        </a>
        <a href="{{ route('jobs.manage') }}" class="rp-sidebar-link {{ request()->routeIs('jobs.manage') || request()->routeIs('jobs.post') || request()->routeIs('jobs.edit') ? 'active' : '' }}">
            <span class="material-symbols-outlined">work</span> Jobs
        </a>
        <a href="{{ route('recruiter.applications') }}" class="rp-sidebar-link {{ request()->routeIs('recruiter.applications') || request()->routeIs('applications.show') ? 'active' : '' }}">
            <span class="material-symbols-outlined">description</span> Applications
        </a>
        <a href="{{ route('screening.index') }}" class="rp-sidebar-link {{ request()->routeIs('screening.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">psychology</span> AI Screening
        </a>
        <a href="{{ route('interviews.index') }}" class="rp-sidebar-link {{ request()->routeIs('interviews.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">event</span> Interviews
        </a>
        <a href="{{ route('messages.index') }}" class="rp-sidebar-link {{ request()->routeIs('messages.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">chat</span> Messages
        </a>
        <a href="{{ route('talent-pool') }}" class="rp-sidebar-link {{ request()->routeIs('talent-pool') ? 'active' : '' }}">
            <span class="material-symbols-outlined">group</span> Talent Pool
        </a>
        <a href="{{ route('offers.index') }}" class="rp-sidebar-link {{ request()->routeIs('offers.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">handshake</span> Offers
        </a>
        <a href="{{ route('company.profile') }}" class="rp-sidebar-link {{ request()->routeIs('company.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">apartment</span> Company
        </a>
        <hr class="mx-3 my-2">
        <a href="{{ route('settings') }}" class="rp-sidebar-link {{ request()->routeIs('settings') ? 'active' : '' }}">
            <span class="material-symbols-outlined">settings</span> Settings
        </a>
        <a href="{{ route('privacy.index') }}" class="rp-sidebar-link {{ request()->routeIs('privacy.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">policy</span> Privacy
        </a>
    </div>

    <div class="p-3 border-top">
        <a href="{{ route('jobs.post') }}" class="btn btn-rp-primary w-100 d-flex align-items-center justify-content-center gap-2">
            <span class="material-symbols-outlined">add</span> Post New Role
        </a>
    </div>
</nav>
