@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Candidate Review')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Candidate Review</h1>
    <p class="text-body-md text-muted-rp">Shortlist and evaluate applicants in screening/interview stages.</p>
</div>

<div class="row g-3">
    @forelse($applications as $app)
    <div class="col-md-6 col-xl-4">
        <div class="rp-card rp-card-lg h-100">
            <div class="d-flex gap-3 mb-3">
                <img src="{{ $app->applicant->avatarUrl() }}" class="rp-avatar rounded-circle" width="48" height="48" alt="">
                <div>
                    <div class="fw-semibold">{{ $app->applicant->name }}</div>
                    <div class="text-muted-rp text-label-md">{{ $app->jobPosting->title }}</div>
                </div>
            </div>
            <div class="mb-2"><span class="badge {{ $app->status->badgeClass() }}">{{ $app->status->label() }}</span></div>
            <div class="mb-3">
                @include('applications._ai-match', [
                    'score' => $app->match_score ?? 0,
                    'threshold' => $app->jobPosting->ats_min_score ?? 50,
                    'summary' => $app->ai_summary,
                    'source' => $app->ai_match_source,
                    'matched' => $app->ats_matched_keywords ?? [],
                    'missing' => $app->ats_missing_keywords ?? [],
                    'compact' => false,
                ])
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('applications.show', $app) }}" class="btn btn-rp-primary btn-sm flex-grow-1">Review</a>
                <a href="{{ route('candidates.show', $app->applicant) }}" class="btn btn-rp-outline btn-sm">Profile</a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="rp-card p-4 text-muted-rp">No candidates currently in review.</div></div>
    @endforelse
</div>
<div class="mt-3">{{ $applications->links() }}</div>
@endsection
