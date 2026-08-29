<nav class="rp-sidebar d-none d-lg-flex">
    <div class="p-3 border-bottom d-flex align-items-center gap-3">
        <a href="{{ route('home') }}" class="rp-brand">
            @include('partials.brand-mark', ['size' => 36])
        </a>
        <div>
            <div class="text-title-lg fw-bold text-primary-rp">Admin Panel</div>
            <div class="text-label-md text-muted-rp">Platform Control</div>
        </div>
    </div>

    <div class="flex-grow-1 overflow-auto py-2">
        <a href="{{ route('admin.index') }}" class="rp-sidebar-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
            <span class="material-symbols-outlined">dashboard</span> Overview
        </a>
        <a href="{{ route('admin.users') }}" class="rp-sidebar-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">group</span> Users
        </a>
        <a href="{{ route('admin.companies') }}" class="rp-sidebar-link {{ request()->routeIs('admin.companies*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">business</span> Companies
        </a>
        <a href="{{ route('admin.jobs') }}" class="rp-sidebar-link {{ request()->routeIs('admin.jobs*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">work</span> Jobs
        </a>
        <a href="{{ route('admin.reports') }}" class="rp-sidebar-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">analytics</span> Reports
        </a>
        <a href="{{ route('admin.analytics') }}" class="rp-sidebar-link {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
            <span class="material-symbols-outlined">monitoring</span> Analytics
        </a>
        <a href="{{ route('admin.abuse') }}" class="rp-sidebar-link {{ request()->routeIs('admin.abuse*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">report</span> Abuse Reports
        </a>
        <a href="{{ route('admin.activity-logs') }}" class="rp-sidebar-link {{ request()->routeIs('admin.activity-logs') ? 'active' : '' }}">
            <span class="material-symbols-outlined">history</span> Activity Logs
        </a>
        <a href="{{ route('admin.security-events') }}" class="rp-sidebar-link {{ request()->routeIs('admin.security-events*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">shield</span> Security Events
        </a>
        <a href="{{ route('admin.permissions') }}" class="rp-sidebar-link {{ request()->routeIs('admin.permissions*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">admin_panel_settings</span> Permissions
        </a>
        <a href="{{ route('admin.branding.edit') }}" class="rp-sidebar-link {{ request()->routeIs('admin.branding*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">palette</span> Logo &amp; Favicon
        </a>
        <hr class="mx-3 my-2">
        <a href="{{ route('privacy.index') }}" class="rp-sidebar-link {{ request()->routeIs('privacy.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">policy</span> Privacy
        </a>
        <a href="{{ route('settings') }}" class="rp-sidebar-link {{ request()->routeIs('settings') ? 'active' : '' }}">
            <span class="material-symbols-outlined">settings</span> Settings
        </a>
    </div>
</nav>
