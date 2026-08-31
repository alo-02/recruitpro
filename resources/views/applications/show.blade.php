@extends('layouts.dashboard', ['sidebar' => auth()->user()->isStudent() ? 'student' : 'recruiter'])

@section('title', 'Application Details')

@section('content')
<div class="mb-4">
    <a href="{{ url()->previous() }}" class="text-label-md text-secondary-rp">← Back</a>
    <h1 class="text-headline-md text-primary-rp mt-2">{{ $application->jobPosting->title }}</h1>
    <p class="text-body-md text-muted-rp">{{ $application->jobPosting->company->name }} · Applied {{ $application->applied_at->format('M d, Y') }}</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-card rp-card-lg mb-4">
            <h2 class="text-title-lg mb-3">Candidate</h2>
            <div class="d-flex gap-3 align-items-center mb-3">
                <img src="{{ $application->applicant->avatarUrl() }}" class="rp-avatar rounded-circle" width="56" height="56" alt="">
                <div>
                    <div class="fw-semibold">{{ $application->applicant->name }}</div>
                    <div class="text-muted-rp">{{ $application->applicant->profile?->headline }}</div>
                    <div class="text-label-md">{{ $application->applicant->email }}</div>
                </div>
            </div>
            @if($application->applicant->profile?->skills)
                <div class="mb-3">
                    @foreach($application->applicant->profile->skills as $skill)
                        <span class="badge bg-light text-dark me-1">{{ $skill }}</span>
                    @endforeach
                </div>
            @endif
            <h3 class="text-title-lg mb-2">Cover Letter</h3>
            <p class="text-body-md">{{ $application->cover_letter ?: 'No cover letter provided.' }}</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="rp-card rp-card-lg mb-3">
            <div class="mb-2">Status: <span class="badge {{ $application->status->badgeClass() }}">{{ $application->status->label() }}</span></div>
            @if($isOwner || auth()->user()?->isAdmin())
                @include('applications._ai-match', [
                    'score' => $application->match_score ?? 0,
                    'threshold' => $application->jobPosting->ats_min_score ?? 50,
                    'matched' => $application->ats_matched_keywords ?? [],
                    'missing' => $application->ats_missing_keywords ?? [],
                    'summary' => $application->ai_summary,
                    'source' => $application->ai_match_source,
                ])
            @else
                <div class="mb-2">Match: <strong>{{ $application->match_score ?? 0 }}%</strong></div>
            @endif

            @if($isOwner)
            <form method="POST" action="{{ route('applications.ats-rescan', $application) }}" class="mb-3">
                @csrf
                <button class="btn btn-rp-outline w-100 btn-sm">Re-run AI match</button>
            </form>
            @endif

            @if($isOwner)
            <form method="POST" action="{{ route('applications.update-status', $application) }}" class="mb-3">
                @csrf @method('PATCH')
                <label class="form-label">Update Status</label>
                <select name="status" class="form-select mb-2">
                    @foreach(\App\Enums\ApplicationStatus::cases() as $status)
                        @continue($status === \App\Enums\ApplicationStatus::Withdrawn)
                        <option value="{{ $status->value }}" @selected($application->status === $status)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <label class="form-label">Recruiter Notes</label>
                <textarea name="recruiter_notes" class="form-control mb-2" rows="3">{{ old('recruiter_notes', $application->recruiter_notes) }}</textarea>
                <button class="btn btn-rp-primary w-100">Save Status</button>
            </form>

            <form method="POST" action="{{ route('messages.start') }}" class="mb-3">
                @csrf
                <input type="hidden" name="user_id" value="{{ $application->user_id }}">
                <input type="hidden" name="application_id" value="{{ $application->id }}">
                <input type="hidden" name="subject" value="Re: {{ $application->jobPosting->title }}">
                <label class="form-label">Message Candidate</label>
                <textarea name="body" class="form-control mb-2" rows="3" required placeholder="Write a message..."></textarea>
                <button class="btn btn-rp-outline w-100">Send Message</button>
            </form>

            @if($application->applicant->profile?->resume_path)
                <a href="{{ route('applications.resume', $application) }}" class="btn btn-rp-outline w-100 mb-2">Download Resume</a>
            @endif
            <a href="{{ route('candidates.show', $application->applicant) }}" class="btn btn-rp-outline w-100">Full Profile</a>
            @endif

            @if(auth()->id() === $application->user_id && $application->status->canWithdraw())
            <form method="POST" action="{{ route('applications.withdraw', $application) }}" onsubmit="return confirm('Withdraw this application?')">
                @csrf @method('PATCH')
                <button class="btn btn-outline-danger w-100 mt-2">Withdraw Application</button>
            </form>
            @endif

            @if(auth()->id() === $application->user_id)
            <form method="POST" action="{{ route('messages.start') }}" class="mt-3">
                @csrf
                <input type="hidden" name="user_id" value="{{ $application->jobPosting->user_id }}">
                <input type="hidden" name="application_id" value="{{ $application->id }}">
                <input type="hidden" name="subject" value="Re: {{ $application->jobPosting->title }}">
                <label class="form-label">Message Recruiter</label>
                <textarea name="body" class="form-control mb-2" rows="3" required placeholder="Ask the recruiter a question..."></textarea>
                <button class="btn btn-rp-outline w-100">Send Message</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
