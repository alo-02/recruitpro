@extends('layouts.app')

@section('title', 'Home')

@section('content')
<section class="hero-pattern home-hero position-relative">
    <div class="container-rp mx-auto px-3">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-7">
                <p class="home-kicker text-label-md mb-0">Campus to career, scored with care</p>
                <h1 class="text-display-lg text-primary-rp mb-3">
                    See the fit.<br>
                    <span class="text-secondary-rp">Don’t guess it.</span>
                </h1>
                <p class="text-body-lg text-muted-rp mb-4 home-lead">
                    RecruitPro is a placement lens — it reads a CV the way a human officer would, then ranks the match. You still make the call.
                </p>
            </div>
            <div class="col-lg-5">
                <div class="home-stage" id="home-iris-stage">
                    <div class="home-iris-frame">
                        @include('partials.brand-mark', ['size' => 168])
                    </div>
                    <div class="home-score-chip">
                        <span class="text-label-md" style="color:#e8d7b8;">Match lens</span>
                        <strong>87%</strong>
                    </div>
                    <div class="home-note-chip">
                        <div class="text-label-md text-secondary-rp mb-1">Human decision</div>
                        <p class="text-body-md mb-0">AI ranks. Recruiters decide. Nothing is auto-rejected.</p>
                    </div>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('jobs.index') }}" class="home-job-filters glass-card p-3 p-md-4 mx-auto text-start mt-4 mt-lg-5">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="form-label text-label-md text-muted-rp mb-1" for="search-keyword">Keyword</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><span class="material-symbols-outlined text-muted-rp">search</span></span>
                        <input type="text" name="q" id="search-keyword" class="form-control" placeholder="Job title, keyword, or company" value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="form-label text-label-md text-muted-rp mb-1" for="search-location">Location</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><span class="material-symbols-outlined text-muted-rp">location_on</span></span>
                        <input type="text" name="location" id="search-location" class="form-control" placeholder="City or remote" value="{{ request('location') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label text-label-md text-muted-rp mb-1" for="search-category">Category</label>
                    <select name="category[]" id="search-category" class="form-select">
                        <option value="">All categories</option>
                        @foreach(\App\Enums\JobCategory::options() as $cat)
                            <option value="{{ $cat['value'] }}" @selected(in_array($cat['value'], (array) request('category'), true))>{{ $cat['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label text-label-md text-muted-rp mb-1" for="search-type">Job type</label>
                    <select name="job_type[]" id="search-type" class="form-select">
                        <option value="">All types</option>
                        <option value="full-time" @selected(in_array('full-time', (array) request('job_type'), true))>Full-time</option>
                        <option value="contract" @selected(in_array('contract', (array) request('job_type'), true))>Contract</option>
                        <option value="internship" @selected(in_array('internship', (array) request('job_type'), true))>Internship</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label text-label-md text-muted-rp mb-1" for="search-mode">Work mode</label>
                    <select name="location_type[]" id="search-mode" class="form-select">
                        <option value="">Any mode</option>
                        <option value="remote" @selected(in_array('remote', (array) request('location_type'), true))>Remote</option>
                        <option value="hybrid" @selected(in_array('hybrid', (array) request('location_type'), true))>Hybrid</option>
                        <option value="onsite" @selected(in_array('onsite', (array) request('location_type'), true))>On-site</option>
                    </select>
                </div>
                <div class="col-12 col-lg-3">
                    <button type="submit" class="btn btn-rp-primary w-100">Find Jobs</button>
                </div>
            </div>
        </form>
    </div>
</section>

<section class="container-rp mx-auto px-3 py-5">
    <p class="section-kicker text-label-md">How the lens works</p>
    <h2 class="text-headline-md text-primary-rp mb-4">Three steps. One human decision.</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="rp-card rp-card-lg h-100">
                <div class="ledger-rail">
                    <span class="ledger-index">01</span>
                    <div>
                        <h3 class="text-title-lg text-primary-rp mb-2">Build a readable profile</h3>
                        <p class="text-body-md text-muted-rp mb-0">Students upload a CV and skills. The system extracts education, experience, and tools — not just a keyword dump.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="rp-card rp-card-lg h-100">
                <div class="ledger-rail">
                    <span class="ledger-index">02</span>
                    <div>
                        <h3 class="text-title-lg text-primary-rp mb-2">Screen against the role</h3>
                        <p class="text-body-md text-muted-rp mb-0">ATS keywords and AI ranking score the fit. Recruiters see why someone matched — and what is missing.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="rp-card rp-card-lg h-100">
                <div class="ledger-rail">
                    <span class="ledger-index">03</span>
                    <div>
                        <h3 class="text-title-lg text-primary-rp mb-2">Interview on your terms</h3>
                        <p class="text-body-md text-muted-rp mb-0">Shortlist, override, or schedule. The score is advisory. The faculty-facing idea: machines rank, people hire.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container-rp mx-auto px-3 pb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <p class="section-kicker text-label-md mb-1">Browse the ledger</p>
            <h2 class="text-headline-md text-primary-rp mb-0">Explore by Category</h2>
        </div>
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

<section class="bg-surface-container-low py-5">
    <div class="container-rp mx-auto px-3">
        <div class="mb-4">
            <p class="section-kicker text-label-md mb-1">Hand-picked, not auto-fed</p>
            <h2 class="text-headline-md text-primary-rp mb-1">Featured Opportunities</h2>
            <p class="text-body-md text-muted-rp mb-0">Roles worth a second look — internships and jobs from approved companies.</p>
        </div>
        <div class="bento-grid">
            @forelse($featuredJobs as $index => $job)
            <div class="rp-card {{ $index === 0 ? 'rp-card-lg bento-large' : 'rp-card-md d-flex flex-column' }} position-relative">
                @if($job->is_featured && $index === 0)
                <span class="hot-role">Hot Role</span>
                @endif
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="bg-surface-container rounded-circle d-flex align-items-center justify-content-center border" style="width:48px;height:48px;">
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

<section class="container-rp mx-auto px-3 py-5">
    <div class="cta-section row g-0">
        <div class="col-md-6 cta-panel border-end" style="border-color:#3a2f22 !important;">
            <p class="text-label-md mb-2" style="color:#d4a017;">For students</p>
            <h2 class="text-headline-lg text-white mb-2">Find an Internship</h2>
            <p class="text-body-lg mb-4" style="color:var(--rp-primary-fixed-dim);">Build your profile, let the lens read your CV, and apply where the fit is real.</p>
            <a href="{{ route('profile.builder') }}" class="btn btn-light text-primary-rp text-label-md d-inline-flex align-items-center gap-1">
                Create Candidate Profile <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>
        <div class="col-md-6 cta-panel cta-panel-dark">
            <p class="text-label-md mb-2" style="color:#d4a017;">For recruiters</p>
            <h2 class="text-headline-lg text-white mb-2">Hire with a second look</h2>
            <p class="text-body-lg mb-4" style="color:var(--rp-primary-fixed-dim);">ATS keywords plus AI ranking — shortlist faster, override anytime, never auto-reject.</p>
            <a href="{{ route('recruiter.dashboard') }}" class="btn btn-rp-primary d-inline-flex align-items-center gap-1">
                Access Employer Portal <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>
    </div>
</section>
@endsection
