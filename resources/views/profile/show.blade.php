@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Candidate Profile')

@section('content')
<div class="mb-4">
    <a href="{{ url()->previous() }}" class="text-label-md text-secondary-rp">← Back</a>
    <h1 class="text-headline-md text-primary-rp mt-2">{{ $user->name }}</h1>
    <p class="text-body-md text-muted-rp">{{ $user->profile?->headline }}</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-card rp-card-lg mb-4">
            <div class="d-flex gap-3 align-items-center mb-4">
                <img src="{{ $user->avatarUrl() }}" class="rp-avatar rounded-circle" width="72" height="72" alt="">
                <div>
                    <div class="fw-semibold">{{ $user->email }}</div>
                    <div class="text-muted-rp">{{ $user->profile?->location }} · {{ $user->profile?->phone }}</div>
                </div>
            </div>
            <h2 class="text-title-lg mb-2">About</h2>
            <p>{{ $user->profile?->bio ?: 'No bio provided.' }}</p>
            <h2 class="text-title-lg mb-2">Skills</h2>
            @foreach($user->profile?->skills ?? [] as $skill)
                <span class="badge bg-light text-dark me-1 mb-1">{{ $skill }}</span>
            @endforeach
            <h2 class="text-title-lg mt-4 mb-2">Education</h2>
            @forelse($user->profile?->educationEntries() ?? [] as $edu)
                <div class="mb-2">
                    <div class="fw-semibold">{{ $edu['degree'] ?? '—' }} · {{ $edu['school'] ?? '' }}</div>
                    <div class="text-muted-rp text-label-md">
                        {{ $edu['graduation_year'] ?? '' }}
                        @if(!empty($edu['gpa'])) · GPA {{ $edu['gpa'] }}@endif
                    </div>
                </div>
            @empty
                <p class="text-muted-rp">No education listed.</p>
            @endforelse
            <h2 class="text-title-lg mt-4 mb-2">Experience</h2>
            @forelse($user->profile?->experienceEntries() ?? [] as $exp)
                <div class="mb-3">
                    <div class="fw-semibold">{{ $exp['title'] ?? '—' }} at {{ $exp['company'] ?? '—' }}</div>
                    <div class="text-muted-rp text-label-md">{{ $exp['years'] ?? '' }}</div>
                    <p class="mb-0">{{ $exp['details'] ?? '' }}</p>
                </div>
            @empty
                <p class="text-muted-rp">No experience listed.</p>
            @endforelse
        </div>
    </div>
    <div class="col-lg-4">
        <div class="rp-card rp-card-lg">
            <div class="mb-3">Profile completeness: {{ $user->profile?->completeness ?? 0 }}%</div>
            <form method="POST" action="{{ route('messages.start') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="subject" value="Message from recruiter">
                <textarea name="body" class="form-control mb-2" rows="4" placeholder="Send a message..." required></textarea>
                <button class="btn btn-rp-primary w-100">Message Candidate</button>
            </form>
        </div>
    </div>
</div>
@endsection
