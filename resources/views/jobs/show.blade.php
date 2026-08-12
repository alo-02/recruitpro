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
                            <span class="rp-tag">{{ $job->category?->label() }}</span>
                            <span class="rp-tag">{{ ucfirst(str_replace('-', ' ', $job->type)) }}</span>
                            @foreach($job->skills ?? [] as $skill)
                            <span class="rp-tag">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <h2 class="text-title-lg text-primary-rp mb-3">About the Role</h2>
                <div class="text-body-md mb-4">{!! nl2br(e($job->description)) !!}</div>
                @if($job->requirements)
                <h2 class="text-title-lg text-primary-rp mb-3">Requirements</h2>
                <div class="text-body-md mb-4">{!! nl2br(e($job->requirements)) !!}</div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="rp-card rp-card-lg position-sticky" style="top:80px;">
                <div class="mb-3">
                    <span class="text-label-md text-muted-rp d-block">Compensation</span>
                    <span class="text-headline-md text-primary-rp">{{ $job->salaryDisplay() }}</span>
                </div>
                <div class="mb-3">
                    <span class="text-label-md text-muted-rp d-block">Deadline</span>
                    <span class="text-body-md">{{ $job->deadline?->format('M d, Y') ?? 'Open' }}</span>
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
                        @php $isSaved = auth()->user()->savedJobs()->where('job_posting_id', $job->id)->exists(); @endphp
                        @if($isSaved)
                            <form action="{{ route('jobs.unsave', $job) }}" method="POST">@csrf @method('DELETE')
                                <button class="btn btn-rp-outline w-100 py-2 mb-2">Unsave</button>
                            </form>
                        @else
                            <form action="{{ route('jobs.save', $job) }}" method="POST">@csrf
                                <button class="btn btn-rp-outline w-100 py-2 mb-2">Save Job</button>
                            </form>
                        @endif
                        <form action="{{ route('jobs.cover-letter', $job) }}" method="POST" class="mb-2">@csrf
                            <button class="btn btn-rp-outline w-100 py-2">Draft Cover Letter</button>
                        </form>
                        @if(session('cover_letter_draft'))
                            <textarea class="form-control mb-2" rows="6">{{ session('cover_letter_draft') }}</textarea>
                        @endif
                        @isset($match)
                        <div class="border rounded p-2 mb-2">
                            <div class="fw-semibold">Your match: {{ $match['score'] }}%</div>
                            @foreach($match['reasons'] as $reason)<div class="text-label-md text-success">• {{ $reason }}</div>@endforeach
                            @if($match['missing'])<div class="text-label-md text-danger">Missing: {{ implode(', ', array_slice($match['missing'],0,6)) }}</div>@endif
                        </div>
                        @endisset
                        @if($isFollowing)
                            <form method="POST" action="{{ route('companies.unfollow', $job->company) }}">@csrf @method('DELETE')
                                <button class="btn btn-rp-outline w-100 py-2 mb-2">Unfollow Company</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('companies.follow', $job->company) }}">@csrf
                                <button class="btn btn-rp-outline w-100 py-2 mb-2">Follow Company</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('companies.reviews.store', $job->company) }}" class="mb-2">@csrf
                            <input type="number" min="1" max="5" name="rating" class="form-control mb-1" placeholder="Rating 1-5" required>
                            <input name="title" class="form-control mb-1" placeholder="Review title">
                            <textarea name="body" class="form-control mb-1" rows="2" placeholder="Review"></textarea>
                            <button class="btn btn-sm btn-rp-outline w-100">Submit Review</button>
                        </form>
                        <form method="POST" action="{{ route('abuse.store') }}">@csrf
                            <input type="hidden" name="reportable_type" value="job">
                            <input type="hidden" name="reportable_id" value="{{ $job->id }}">
                            <input name="reason" class="form-control mb-1" placeholder="Report reason" required>
                            <button class="btn btn-sm btn-outline-danger w-100">Report Job</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-rp-primary w-100 py-2 mb-2">Sign In to Apply</a>
                @endauth
            </div>
            @if(($reviews ?? collect())->count())
            <div class="rp-card rp-card-lg mt-3">
                <h3 class="text-title-lg mb-2">Company Reviews</h3>
                @foreach($reviews as $review)
                    <div class="border-bottom py-2">
                        <strong>{{ $review->rating }}/5</strong> {{ $review->title }}
                        <div class="text-muted-rp text-label-md">{{ $review->user?->name }}</div>
                        <p class="mb-0">{{ $review->body }}</p>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
