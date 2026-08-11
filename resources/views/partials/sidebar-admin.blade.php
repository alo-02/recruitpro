<nav class="rp-sidebar d-none d-lg-flex">
    <div class="p-3 border-bottom d-flex align-items-center gap-3">
        <div class="bg-primary-rp rounded d-flex align-items-center justify-content-center text-white" style="width:40px;height:40px;">
            <span class="material-symbols-outlined">admin_panel_settings</span>
        </div>
        <div>
            <div class="text-title-lg fw-bold text-primary-rp">Admin Panel</div>
            <div class="text-label-md text-muted-rp">Platform Control</div>
        </div>
    </div>

    <div class="flex-grow-1 overflow-auto py-2">
        <a href="{{ route('admin.index') }}" class="rp-sidebar-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
            <span class="material-symbols-outlined {{ request()->routeIs('admin.index') ? 'icon-fill' : '' }}">dashboard</span>
            Overview
        </a>
        <a href="{{ route('admin.index') }}#users" class="rp-sidebar-link">
            <span class="material-symbols-outlined">group</span>
            Users
        </a>
        <a href="{{ route('jobs.manage') }}" class="rp-sidebar-link">
            <span class="material-symbols-outlined">work</span>
            Jobs
        </a>
        <a href="{{ route('admin.index') }}#reports" class="rp-sidebar-link">
            <span class="material-symbols-outlined">analytics</span>
            Reports
        </a>
        <hr class="mx-3 my-2">
        <a href="{{ route('settings') }}" class="rp-sidebar-link">
            <span class="material-symbols-outlined">settings</span>
            Settings
        </a>
    </div>
</nav>
