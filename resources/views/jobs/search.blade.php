@extends('layouts.dashboard', ['sidebar' => 'student'])

@section('title', 'Search Jobs')

@section('content')
<div class="d-flex">
    <aside class="filter-sidebar d-none d-md-block">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-title-lg mb-0">Filters</h2>
            <a href="{{ route('jobs.index') }}" class="btn btn-link text-secondary-rp text-label-md p-0">Clear All</a>
        </div>

        <form method="GET" action="{{ route('jobs.index') }}" id="job-filters">
            <div class="mb-4 pb-3 border-bottom">
                <h3 class="text-label-md text-muted-rp mb-2">Job Type</h3>
                @foreach(['full-time' => 'Full-time', 'contract' => 'Contract', 'internship' => 'Internship'] as $val => $label)
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="job_type[]" value="{{ $val }}" id="type-{{ $val }}"
                        {{ in_array($val, (array) request('job_type')) ? 'checked' : '' }}>
                    <label class="form-check-label text-body-md" for="type-{{ $val }}">{{ $label }}</label>
                </div>
                @endforeach
            </div>

            <div class="mb-4 pb-3 border-bottom">
                <h3 class="text-label-md text-muted-rp mb-2">Location</h3>
                @foreach(['remote' => 'Remote', 'onsite' => 'On-site', 'hybrid' => 'Hybrid'] as $val => $label)
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="location_type[]" value="{{ $val }}" id="loc-{{ $val }}"
                        {{ in_array($val, (array) request('location_type')) ? 'checked' : '' }}>
                    <label class="form-check-label text-body-md" for="loc-{{ $val }}">{{ $label }}</label>
                </div>
                @endforeach
            </div>

            <button type="submit" class="btn btn-rp-primary w-100">Apply Filters</button>
        </form>
    </aside>

    <div class="flex-grow-1 ps-md-4">
        <div class="mb-4">
            <h1 class="text-headline-md text-primary-rp mb-1">Search Internships & Jobs</h1>
            <p class="text-body-md text-muted-rp mb-3">{{ $jobs->total() }} jobs found</p>
            <form method="GET" action="{{ route('jobs.index') }}" class="input-group">
                <span class="input-group-text"><span class="material-symbols-outlined">search</span></span>
                <input type="text" name="q" class="form-control" placeholder="Search by title, company, or keyword..." value="{{ request('q') }}">
                <button class="btn btn-rp-primary" type="submit">Search</button>
            </form>
        </div>

        <div class="row g-3">
            @forelse($jobs as $job)
            <div class="col-12">
                <div class="job-card" data-job-type="{{ $job->type }}" data-location-type="{{ $job->location_type }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h3 class="text-title-lg text-primary-rp mb-1">{{ $job->title }}</h3>
                            <p class="text-body-md text-muted-rp mb-0">{{ $job->company->name }} &bull; {{ $job->location }}</p>
                        </div>
                        <span class="text-label-md text-muted-rp">{{ $job->published_at?->diffForHumans() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            <span class="rp-tag">{{ ucfirst(str_replace('-', ' ', $job->type)) }}</span>
                            <span class="text-label-md">{{ $job->salaryDisplay() }}</span>
                        </div>
                        <a href="{{ route('jobs.show', $job) }}" class="btn btn-rp-outline btn-sm">View Details</a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="empty-state">
                    <span class="material-symbols-outlined d-block">search_off</span>
                    <p class="text-body-lg text-muted-rp">No jobs match your search criteria.</p>
                </div>
            </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $jobs->links() }}</div>
    </div>
</div>
@endsection
