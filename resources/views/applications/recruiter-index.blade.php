@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'ATS Applicant Screening')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="text-headline-md text-primary-rp mb-1">ATS CV Screening</h1>
        <p class="text-body-md text-muted-rp mb-0">Filter applicants by job keywords matched against profile + resume text.</p>
    </div>
    @if($selectedJob && $selectedJob->slug)
    <form method="POST" action="{{ route('jobs.ats-rescan', ['job' => $selectedJob->slug]) }}">
        @csrf
        <button class="btn btn-rp-outline">Re-scan all CVs for this job</button>
    </form>
    @endif
</div>

<form class="rp-card rp-card-lg mb-4 p-3" method="GET">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label text-label-md">Job</label>
            <select name="job_id" class="form-select">
                <option value="">All jobs</option>
                @foreach($jobs as $job)
                    <option value="{{ $job->id }}" @selected(request('job_id') == $job->id)>{{ $job->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label text-label-md">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach(\App\Enums\ApplicationStatus::cases() as $status)
                    @continue($status === \App\Enums\ApplicationStatus::Withdrawn)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label text-label-md">Min ATS %</label>
            <input type="number" name="min_score" class="form-control" min="0" max="100"
                   value="{{ request('min_score', $minScore) }}" placeholder="{{ $selectedJob?->ats_min_score ?? 50 }}">
        </div>
        <div class="col-md-3">
            <label class="form-label text-label-md">Keyword</label>
            <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}" placeholder="e.g. Laravel">
        </div>
        <div class="col-md-2">
            <label class="form-label text-label-md">Sort</label>
            <select name="sort" class="form-select">
                <option value="ats" @selected(request('sort', 'ats') === 'ats')>Best ATS match</option>
                <option value="newest" @selected(request('sort') === 'newest')>Newest</option>
                <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
            </select>
        </div>
        <div class="col-md-3">
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="ats_pass" value="1" id="ats_pass" @checked(request()->boolean('ats_pass'))>
                <label class="form-check-label" for="ats_pass">Only ATS-qualified (min score)</label>
            </div>
        </div>
        <div class="col-md-3">
            <button class="btn btn-rp-primary w-100">Apply ATS Filter</button>
        </div>
    </div>
    @if($selectedJob && ($selectedJob->ats_keywords || $selectedJob->skills))
        <div class="mt-3">
            <span class="text-label-md text-muted-rp">Job ATS keywords:</span>
            @foreach(($selectedJob->ats_keywords ?: $selectedJob->skills ?: []) as $kw)
                <span class="badge bg-light text-dark me-1">{{ $kw }}</span>
            @endforeach
            <span class="text-label-md text-muted-rp ms-2">Threshold: {{ $selectedJob->ats_min_score ?? 50 }}%</span>
        </div>
    @endif
</form>

<div class="rp-card rp-card-lg">
    <div class="table-responsive">
        <table class="table rp-table mb-0">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Job</th>
                    <th>ATS Score</th>
                    <th>Matched</th>
                    <th>Missing</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                @php
                    $threshold = $app->jobPosting->ats_min_score ?? 50;
                    $pass = ($app->match_score ?? 0) >= $threshold;
                @endphp
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $app->applicant->name }}</div>
                        <div class="text-muted-rp text-label-md">{{ $app->applicant->email }}</div>
                    </td>
                    <td>{{ $app->jobPosting->title }}</td>
                    <td>
                        <span class="badge {{ $pass ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $app->match_score ?? 0 }}%
                        </span>
                        <div class="text-label-md text-muted-rp">{{ $pass ? 'Qualified' : 'Below threshold' }}</div>
                    </td>
                    <td style="max-width:180px;">
                        @forelse($app->ats_matched_keywords ?? [] as $kw)
                            <span class="badge bg-success-subtle text-success me-1 mb-1">{{ $kw }}</span>
                        @empty
                            <span class="text-muted-rp">—</span>
                        @endforelse
                    </td>
                    <td style="max-width:180px;">
                        @forelse($app->ats_missing_keywords ?? [] as $kw)
                            <span class="badge bg-danger-subtle text-danger me-1 mb-1">{{ $kw }}</span>
                        @empty
                            <span class="text-muted-rp">—</span>
                        @endforelse
                    </td>
                    <td><span class="badge {{ $app->status->badgeClass() }}">{{ $app->status->label() }}</span></td>
                    <td class="text-nowrap">
                        <a href="{{ route('applications.show', $app) }}" class="btn btn-rp-outline btn-sm">Review</a>
                        @if($app->applicant->profile?->resume_path)
                            <a href="{{ route('applications.resume', $app) }}" class="btn btn-rp-outline btn-sm">CV</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted-rp py-4">No applications match this ATS filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $applications->links() }}</div>
</div>
@endsection
