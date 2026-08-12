@extends('layouts.dashboard', ['sidebar' => 'admin'])

@section('title', 'Admin Control Panel')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Platform Admin</h1>
    <p class="text-body-md text-muted-rp">Monitor and manage the RecruitPro platform.</p>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['icon' => 'group', 'label' => 'Total Users', 'value' => number_format($stats['users']), 'href' => route('admin.users')],
        ['icon' => 'work', 'label' => 'Active Jobs', 'value' => $stats['active_jobs'], 'href' => route('admin.jobs')],
        ['icon' => 'description', 'label' => 'Applications', 'value' => number_format($stats['applications']), 'href' => route('admin.reports')],
        ['icon' => 'pending', 'label' => 'Pending Companies', 'value' => $stats['pending_companies'], 'href' => route('admin.companies')],
    ] as $stat)
    <div class="col-6 col-lg-3">
        <a href="{{ $stat['href'] }}" class="text-decoration-none">
            <div class="stat-card">
                <span class="material-symbols-outlined text-secondary-rp mb-2">{{ $stat['icon'] }}</span>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="text-label-md text-muted-rp">{{ $stat['label'] }}</div>
            </div>
        </a>
    </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="rp-card rp-card-lg">
            <div class="d-flex justify-content-between mb-3">
                <h2 class="text-title-lg text-primary-rp mb-0">Recent Users</h2>
                <a href="{{ route('admin.users') }}" class="text-label-md text-secondary-rp">Manage</a>
            </div>
            <div class="table-responsive">
                <table class="table rp-table mb-0">
                    <thead><tr><th>User</th><th>Role</th><th>Joined</th></tr></thead>
                    <tbody>
                        @foreach($recentUsers as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td><span class="rp-tag">{{ $user->role->label() }}</span></td>
                            <td class="text-muted-rp">{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="rp-card rp-card-lg">
            <div class="d-flex justify-content-between mb-3">
                <h2 class="text-title-lg text-primary-rp mb-0">Activity</h2>
                <a href="{{ route('admin.activity-logs') }}" class="text-label-md text-secondary-rp">All logs</a>
            </div>
            @forelse($recentLogs as $log)
                <div class="border-bottom py-2">
                    <div class="text-body-md">{{ $log->action }}</div>
                    <div class="text-label-md text-muted-rp">{{ $log->user?->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}</div>
                </div>
            @empty
                <p class="text-muted-rp mb-0">No activity yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
