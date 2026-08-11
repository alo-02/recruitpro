@extends('layouts.app')

@section('title', $job->title)

@section('content')
<div class="container-rp mx-auto px-3 py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('jobs.index') }}">Jobs</a></li>
            <li class="breadcrumb-item active">{{ $job->title }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="rp-card rp-card-lg mb-4">
                <div class="d-flex align-items-start gap-3 mb-4">
                    <div class="bg-surface-container rounded d-flex align-items-center justify-content-center border" style="width:64px;height:64px;">
                        <span class="material-symbols-outlined text-muted-rp" style="font-size:32px;">business</span>
                    </div>
                    <div>
                        <h1 class="text-headline-md text-primary-rp mb-1">{{ $job->title }}</h1>
                        <p class="text-body-lg text-muted-rp mb-2">{{ $job->company->name }} &bull; {{ $job->location }} ({{ ucfirst($job->location_type) }})</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="rp-tag">{{ ucfirst(str_replace('-', ' ', $job->type)) }}</span>
                            @foreach($job->skills ?? [] as $skill)
                            <span class="rp-tag">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <h2 class="text-title-lg text-primary-rp mb-3">About the Role</h2>
                <div class="text-body-md mb-4">{!! nl2br(e($job->description)) !!}</div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="rp-card rp-card-lg position-sticky" style="top:80px;">
                <div class="mb-3">
                    <span class="text-label-md text-muted-rp d-block">Compensation</span>
                    <span class="text-headline-md text-primary-rp">{{ $job->salaryDisplay() }}</span>
                </div>
                <div class="mb-3">
                    <span class="text-label-md text-muted-rp d-block">Posted</span>
                    <span class="text-body-md">{{ $job->published_at?->diffForHumans() }}</span>
                </div>
                <div class="mb-4">
                    <span class="text-label-md text-muted-rp d-block">Applicants</span>
                    <span class="text-body-md">{{ $job->applications()->count() }} applicants</span>
                </div>

                @auth
                    @if(auth()->user()->isStudent())
                        @if($hasApplied)
                            <button class="btn btn-secondary w-100 py-2 mb-2" disabled>Already Applied</button>
                        @else
                            <form action="{{ route('applications.store', $job) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-rp-primary w-100 py-2 mb-2">Apply Now</button>
                            </form>
                        @endif
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-rp-primary w-100 py-2 mb-2">Sign In to Apply</a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
