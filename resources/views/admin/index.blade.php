@extends('layouts.dashboard', ['sidebar' => 'admin'])

@section('title', 'Admin Control Panel')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Platform Admin</h1>
    <p class="text-body-md text-muted-rp">Monitor and manage the RecruitPro platform.</p>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['icon' => 'group', 'label' => 'Total Users', 'value' => number_format($stats['users'])],
        ['icon' => 'work', 'label' => 'Active Jobs', 'value' => $stats['active_jobs']],
        ['icon' => 'description', 'label' => 'Applications', 'value' => number_format($stats['applications'])],
        ['icon' => 'trending_up', 'label' => 'Growth', 'value' => $stats['growth']],
    ] as $stat)
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <span class="material-symbols-outlined text-secondary-rp mb-2">{{ $stat['icon'] }}</span>
            <div class="stat-value">{{ $stat['value'] }}</div>
            <div class="text-label-md text-muted-rp">{{ $stat['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="rp-card rp-card-lg">
    <h2 class="text-title-lg text-primary-rp mb-4">Recent Users</h2>
    <div class="table-responsive">
        <table class="table rp-table mb-0">
            <thead>
                <tr><th>User</th><th>Role</th><th>Joined</th></tr>
            </thead>
            <tbody>
                @foreach($recentUsers as $user)
                <tr>
                    <td class="d-flex align-items-center gap-2">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0051d5&color=fff" class="rounded-circle" width="32" height="32" alt="">
                        <span class="text-body-md fw-semibold">{{ $user->name }}</span>
                    </td>
                    <td><span class="rp-tag">{{ $user->role->label() }}</span></td>
                    <td class="text-body-md text-muted-rp">{{ $user->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
