@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Candidate Screening Review')

@section('content')
<div class="mb-4">
    <a href="{{ route('screening.show', $session) }}" class="text-label-md text-secondary-rp">← Back to ranking</a>
    <h1 class="text-headline-md text-primary-rp mt-2 mb-1">{{ $result->candidate->name }}</h1>
    <p class="text-body-md text-muted-rp mb-0">
        Rank #{{ $result->rank }} · {{ $result->overall_score }}% match · {{ $session->jobPosting->title }}
    </p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-card rp-card-lg mb-4">
            <div class="d-flex gap-3 align-items-center mb-3">
                <img src="{{ $result->candidate->avatarUrl() }}" class="rp-avatar rounded-circle" width="72" height="72" alt="">
                <div>
                    <div class="fw-semibold">{{ $result->candidate->email }}</div>
                    <div class="text-muted-rp">{{ $result->candidate->profile?->headline }}</div>
                    <div class="text-label-md">{{ $result->candidate->profile?->location }}</div>
                </div>
            </div>
            <div class="mb-3">
                <span class="badge {{ $result->overall_score >= 75 ? 'bg-success' : ($result->overall_score >= 50 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                    {{ $result->overall_score }}% overall
                </span>
                @if($result->ai_recommended)
                    <span class="badge bg-success-subtle text-success">In AI Top-N</span>
                @else
                    <span class="badge bg-light text-dark">Outside AI Top-N</span>
                @endif
            </div>
            @if($result->score_breakdown)
            <div class="row g-2 mb-3">
                @foreach($result->score_breakdown as $label => $value)
                <div class="col-6 col-md">
                    <div class="text-label-md text-muted-rp">{{ str_replace('_', ' ', $label) }}</div>
                    <div class="fw-semibold">{{ $value }}%</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="rp-card rp-card-lg mb-4">
            <h2 class="text-title-lg text-primary-rp mb-3">AI explanation</h2>
            <p class="text-body-md">{{ $result->ai_explanation }}</p>
            <h3 class="text-title-lg mb-2">Recommendation</h3>
            <p class="text-body-md mb-0">{{ $result->ai_recommendation }}</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="rp-card p-3 h-100">
                    <div class="text-label-md text-success mb-2">Matched</div>
                    @forelse($result->matched_points ?? [] as $point)
                        <div class="mb-2">
                            <div class="fw-semibold text-body-md">{{ $point['label'] }}</div>
                            <div class="text-label-md text-muted-rp">{{ $point['detail'] ?? '' }}</div>
                        </div>
                    @empty
                        <p class="text-muted-rp mb-0">None</p>
                    @endforelse
                </div>
            </div>
            <div class="col-md-4">
                <div class="rp-card p-3 h-100">
                    <div class="text-label-md text-warning mb-2">Partially matched</div>
                    @forelse($result->partial_matches ?? [] as $point)
                        <div class="mb-2">
                            <div class="fw-semibold text-body-md">{{ $point['label'] }}</div>
                            <div class="text-label-md text-muted-rp">{{ $point['detail'] ?? '' }}</div>
                        </div>
                    @empty
                        <p class="text-muted-rp mb-0">None</p>
                    @endforelse
                </div>
            </div>
            <div class="col-md-4">
                <div class="rp-card p-3 h-100">
                    <div class="text-label-md text-danger mb-2">Missing</div>
                    @forelse($result->missing_points ?? [] as $point)
                        <div class="mb-2">
                            <div class="fw-semibold text-body-md">{{ $point['label'] }}</div>
                            <div class="text-label-md text-muted-rp">{{ $point['detail'] ?? '' }}</div>
                        </div>
                    @empty
                        <p class="text-muted-rp mb-0">None</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rp-card rp-card-lg mb-4">
            <h2 class="text-title-lg text-primary-rp mb-3">Extracted CV profile</h2>
            @php $extracted = $result->extracted_profile ?? []; @endphp
            @foreach([
                'education' => 'Education',
                'technical_skills' => 'Technical skills',
                'programming_languages' => 'Programming languages',
                'tools' => 'Tools',
                'work_experience' => 'Work experience',
                'projects' => 'Projects',
                'certifications' => 'Certifications',
            ] as $key => $label)
            <div class="mb-3">
                <div class="text-label-md text-muted-rp mb-1">{{ $label }}</div>
                @if(!empty($extracted[$key]) && is_array($extracted[$key]))
                    @if(in_array($key, ['technical_skills', 'programming_languages', 'tools', 'certifications'], true))
                        @foreach($extracted[$key] as $item)
                            <span class="badge bg-light text-dark me-1 mb-1">{{ $item }}</span>
                        @endforeach
                    @else
                        <ul class="mb-0">
                            @foreach($extracted[$key] as $item)
                                <li class="text-body-md">{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    <p class="text-muted-rp mb-0">Not extracted</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-4">
        <div class="rp-card rp-card-lg mb-3">
            <h2 class="text-title-lg mb-2">Manual review</h2>
            <p class="text-body-md text-muted-rp">Override the AI at any time. Rejecting a recommendation does <strong>not</strong> reject the candidate.</p>

            @if($result->application->applicant->profile?->resume_path)
                <a href="{{ route('applications.resume', $result->application) }}" class="btn btn-rp-outline w-100 mb-2">Download full CV</a>
            @endif
            <a href="{{ route('applications.show', $result->application) }}" class="btn btn-rp-outline w-100 mb-3">Open application</a>

            <form method="POST" action="{{ route('screening.decide', [$session, $result]) }}" class="mb-3">
                @csrf
                <label class="form-label text-label-md">Decision</label>
                <select name="decision" class="form-select mb-2" required>
                    <option value="accept_recommendation">Accept AI recommendation</option>
                    <option value="reject_recommendation">Reject AI recommendation</option>
                    <option value="shortlist">Manually shortlist</option>
                    <option value="remove">Remove from shortlist</option>
                </select>
                <textarea name="notes" class="form-control mb-2" rows="3" placeholder="Notes (stored separately from AI output)"></textarea>
                <button class="btn btn-rp-primary w-100">Save recruiter decision</button>
            </form>
        </div>

        <div class="rp-card rp-card-lg mb-3">
            <h2 class="text-title-lg mb-2">Move to interview</h2>
            <form method="POST" action="{{ route('screening.interview', [$session, $result]) }}">
                @csrf
                <label class="form-label text-label-md">Schedule (optional)</label>
                <input type="datetime-local" name="scheduled_at" class="form-control mb-2">
                <select name="mode" class="form-select mb-2">
                    <option value="online">Online</option>
                    <option value="onsite">On-site</option>
                    <option value="phone">Phone</option>
                </select>
                <input type="text" name="location_or_link" class="form-control mb-2" placeholder="Meeting link or location">
                <textarea name="notes" class="form-control mb-2" rows="2" placeholder="Interview notes"></textarea>
                <button class="btn btn-rp-primary w-100">Push to interview stage</button>
            </form>
            <p class="text-label-md text-muted-rp mt-2 mb-0">If a time is set, the candidate receives an interview email immediately.</p>
        </div>

        <div class="rp-card rp-card-lg">
            <h2 class="text-title-lg mb-2">Decision history</h2>
            @forelse($result->decisions as $decision)
                <div class="border-bottom py-2">
                    <div class="fw-semibold text-body-md">{{ $decision->decision->label() }}</div>
                    <div class="text-label-md text-muted-rp">{{ $decision->created_at->format('M d, Y g:i A') }} · {{ $decision->recruiter?->name }}</div>
                    @if($decision->notes)
                        <p class="text-body-md mb-0">{{ $decision->notes }}</p>
                    @endif
                </div>
            @empty
                <p class="text-muted-rp mb-0">No recruiter decision yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
