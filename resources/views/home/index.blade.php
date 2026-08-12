@extends('layouts.app')

@section('title', 'Home')

@section('content')
{{-- Hero --}}
<section class="hero-pattern py-5 text-center position-relative overflow-hidden">
    <div class="container-rp mx-auto px-3 position-relative" style="z-index:10;">
        <h1 class="text-display-lg text-primary-rp mb-3">
            Bridge the Gap Between <span class="text-secondary-rp">Talent</span> and <span class="text-secondary-rp">Opportunity</span>
        </h1>
        <p class="text-body-lg text-muted-rp mb-4 mx-auto" style="max-width:640px;">
            Discover your next career move or find the perfect candidate to scale your enterprise. RecruitPro connects ambition with industry-leading roles.
        </p>

        <form id="hero-search-form" class="glass-card p-2 mx-auto" style="max-width:768px;">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><span class="material-symbols-outlined text-muted-rp">search</span></span>
                        <input type="text" id="search-keyword" class="form-control border-start-0" placeholder="Job Title, Keyword, or Company">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><span class="material-symbols-outlined text-muted-rp">location_on</span></span>
                        <input type="text" id="search-location" class="form-control border-start-0" placeholder="Location or Remote">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-rp-primary w-100">Find Jobs</button>
                </div>
            </div>
        </form>
    </div>
</section>

{{-- Categories --}}
<section class="container-rp mx-auto px-3 py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <h2 class="text-headline-md text-primary-rp mb-0">Explore by Category</h2>
        <a href="{{ route('jobs.index') }}" class="text-secondary-rp text-label-md text-decoration-none d-flex align-items-center gap-1">
            View All <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
        </a>
    </div>
    <div class="row g-3">
        @foreach(\App\Enums\JobCategory::options() as $cat)
        <div class="col-6 col-md">
            <a href="{{ route('jobs.index', ['category' => [$cat['value']]]) }}" class="category-card">
                <div class="category-icon"><span class="material-symbols-outlined text-primary-rp">{{ $cat['icon'] }}</span></div>
                <span class="text-label-md">{{ $cat['label'] }}</span>
            </a>
        </div>
        @endforeach
    </div>
</section>

{{-- Featured Jobs --}}
<section class="bg-surface-container-low py-5">
    <div class="container-rp mx-auto px-3">
        <div class="mb-4">
            <h2 class="text-headline-md text-primary-rp mb-1">Featured Opportunities</h2>
            <p class="text-body-md text-muted-rp mb-0">Top roles hand-picked for ambitious professionals.</p>
        </div>
        <div class="bento-grid">
            @forelse($featuredJobs as $index => $job)
            <div class="rp-card {{ $index === 0 ? 'rp-card-lg bento-large' : 'rp-card-md d-flex flex-column' }} position-relative">
                @if($job->is_featured && $index === 0)
                <span class="position-absolute top-0 end-0 bg-primary text-white px-2 py-1 text-label-md" style="border-radius:0 0.5rem 0 0.5rem;">Hot Role</span>
                @endif
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="bg-surface-container rounded d-flex align-items-center justify-content-center border" style="width:48px;height:48px;">
                        <span class="material-symbols-outlined text-muted-rp">business</span>
                    </div>
                    <div>
                        <h3 class="text-title-lg text-primary-rp mb-0">{{ $job->title }}</h3>
                        <p class="text-body-md text-muted-rp mb-0">{{ $job->company->name }} &bull; {{ $job->location }}</p>
                    </div>
                </div>
                @if($index === 0)
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    @foreach($job->skills ?? [] as $skill)
                    <span class="rp-tag">{{ $skill }}</span>
                    @endforeach
                </div>
                <p class="text-body-md mb-4">{{ Str::limit($job->description, 180) }}</p>
                @else
                <p class="text-body-md text-muted-rp mb-md">{{ ucfirst($job->type) }} &bull; {{ ucfirst($job->location_type) }}</p>
                @endif
                <div class="d-flex justify-content-between align-items-center {{ $index === 0 ? 'border-top pt-3' : 'mt-auto pt-2' }}">
                    <span class="text-label-md fw-bold">{{ $job->salaryDisplay() }}</span>
                    <a href="{{ route('jobs.show', $job) }}" class="btn {{ $index === 0 ? 'btn-rp-primary' : 'btn-rp-outline btn-sm' }}">View Details</a>
                </div>
            </div>
            @empty
            <p class="text-body-md text-muted-rp">No featured jobs yet. Check back soon!</p>
            @endforelse
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="container-rp mx-auto px-3 py-5">
    <div class="cta-section row g-0">
        <div class="col-md-6 cta-panel border-end border-secondary">
            <h2 class="text-headline-lg text-white mb-2">Find an Internship</h2>
            <p class="text-body-lg mb-4" style="color:var(--rp-primary-fixed-dim);">Build your profile, discover curated opportunities, and kickstart your career trajectory.</p>
            <a href="{{ route('profile.builder') }}" class="btn btn-light text-primary-rp text-label-md d-inline-flex align-items-center gap-1">
                Create Candidate Profile <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>
        <div class="col-md-6 cta-panel cta-panel-dark">
            <h2 class="text-headline-lg text-white mb-2">Hire Top Talent</h2>
            <p class="text-body-lg mb-4" style="color:var(--rp-primary-fixed-dim);">Access a diverse pool of pre-vetted candidates and streamline your recruitment pipeline.</p>
            <a href="{{ route('recruiter.dashboard') }}" class="btn btn-rp-primary d-inline-flex align-items-center gap-1">
                Access Employer Portal <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>
    </div>
</section>
@endsection
