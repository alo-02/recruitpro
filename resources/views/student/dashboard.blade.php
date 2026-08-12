@extends('layouts.dashboard', ['sidebar' => 'student'])

@section('title', 'Student Dashboard')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Welcome back, {{ $user->name }}!</h1>
    <p class="text-body-md text-muted-rp">Here's your career dashboard overview.</p>
</div>

<div class="rp-card rp-card-lg mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-title-lg text-primary-rp">Profile Completeness</span>
        <span class="text-label-md text-secondary-rp">{{ $profile?->completeness ?? 0 }}%</span>
    </div>
    <div class="rp-progress mb-2">
        <div class="rp-progress-bar" style="width:{{ $profile?->completeness ?? 0 }}%"></div>
    </div>
    <a href="{{ route('profile.builder') }}" class="btn btn-rp-primary btn-sm">Complete Profile</a>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['icon' => 'description', 'label' => 'Applications', 'value' => $stats['applications']],
        ['icon' => 'event', 'label' => 'Interviews', 'value' => $stats['interviews']],
        ['icon' => 'chat', 'label' => 'Messages', 'value' => $stats['messages']],
        ['icon' => 'person', 'label' => 'Profile %', 'value' => $stats['profile']],
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

<div class="row g-4">
    <div class="col-lg-7">
        <div class="rp-card rp-card-lg">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="text-title-lg text-primary-rp mb-0">Recent Applications</h2>
                <a href="{{ route('applications.index') }}" class="text-label-md text-secondary-rp">View All</a>
            </div>
            @forelse($applications as $app)
            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                <div>
                    <div class="text-body-md fw-semibold">{{ $app->jobPosting->title }}</div>
                    <div class="text-body-md text-muted-rp">{{ $app->jobPosting->company->name }}</div>
                </div>
                <span class="badge {{ $app->status->badgeClass() }} text-label-md">{{ $app->status->label() }}</span>
            </div>
            @empty
            <p class="text-body-md text-muted-rp">No applications yet. <a href="{{ route('jobs.index') }}">Browse jobs</a></p>
            @endforelse
        </div>
    </div>

    <div class="col-lg-5">
        <div class="rp-card rp-card-lg">
            <h2 class="text-title-lg text-primary-rp mb-3">Recommended for You</h2>
            @foreach($recommendedJobs as $job)
            <div class="job-card mb-2">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-body-md fw-semibold">{{ $job->title }}</div>
                        <div class="text-body-md text-muted-rp">{{ $job->company->name }}</div>
                    </div>
                </div>
            </div>
            @endforeach
            <a href="{{ route('jobs.index') }}" class="btn btn-rp-outline w-100 mt-2">Browse More Jobs</a>
        </div>
    </div>
</div>
@endsection
