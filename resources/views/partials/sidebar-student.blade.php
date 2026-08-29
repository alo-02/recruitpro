<nav class="rp-sidebar d-none d-lg-flex">
    <div class="p-3 border-bottom d-flex align-items-center gap-3">
        <a href="{{ route('home') }}" class="rp-brand">
            @include('partials.brand-mark', ['size' => 36])
        </a>
        <div>
            <div class="text-title-lg fw-bold text-primary-rp">Student Portal</div>
            <div class="text-label-md text-muted-rp">Candidate Access</div>
        </div>
    </div>

    <div class="flex-grow-1 overflow-auto py-2">
        <a href="{{ route('home') }}" class="rp-sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}">
            <span class="material-symbols-outlined">home</span>
            Home
        </a>
        <a href="{{ route('student.dashboard') }}" class="rp-sidebar-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <span class="material-symbols-outlined {{ request()->routeIs('student.dashboard') ? 'icon-fill' : '' }}">dashboard</span>
            Dashboard
        </a>
        <a href="{{ route('recommendations.index') }}" class="rp-sidebar-link {{ request()->routeIs('recommendations.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">auto_awesome</span>
            Recommendations
        </a>
        <a href="{{ route('jobs.index') }}" class="rp-sidebar-link {{ request()->routeIs('jobs.index') ? 'active' : '' }}">
            <span class="material-symbols-outlined">search</span>
            Search Jobs
        </a>
        <a href="{{ route('portfolio.index') }}" class="rp-sidebar-link {{ request()->routeIs('portfolio.*') || request()->routeIs('resume.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">folder_special</span>
            Portfolio / Resume
        </a>
        <a href="{{ route('assessments.index') }}" class="rp-sidebar-link {{ request()->routeIs('assessments.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">quiz</span>
            Assessments
        </a>
        <a href="{{ route('offers.index') }}" class="rp-sidebar-link {{ request()->routeIs('offers.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">handshake</span>
            Offers
        </a>
        <a href="{{ route('applications.index') }}" class="rp-sidebar-link {{ request()->routeIs('applications.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">description</span>
            Applications
        </a>
        <a href="{{ route('jobs.saved') }}" class="rp-sidebar-link {{ request()->routeIs('jobs.saved') ? 'active' : '' }}">
            <span class="material-symbols-outlined">bookmark</span>
            Saved Jobs
        </a>
        <a href="{{ route('jobs.alerts') }}" class="rp-sidebar-link {{ request()->routeIs('jobs.alerts*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">notifications</span>
            Job Alerts
        </a>
        <a href="{{ route('messages.index') }}" class="rp-sidebar-link {{ request()->routeIs('messages.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">chat</span>
            Messages
        </a>
        <a href="{{ route('interviews.index') }}" class="rp-sidebar-link {{ request()->routeIs('interviews.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">event</span>
            Interviews
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
        <a href="{{ route('privacy.index') }}" class="rp-sidebar-link {{ request()->routeIs('privacy.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">policy</span>
            Privacy
        </a>
    </div>

    <div class="p-3 border-top">
        <a href="{{ route('jobs.index') }}" class="btn btn-rp-primary w-100 d-flex align-items-center justify-content-center gap-2">
            <span class="material-symbols-outlined">search</span>
            Find Opportunities
        </a>
    </div>
</nav>
