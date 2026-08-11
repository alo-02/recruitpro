@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Talent Pool')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Talent Pool CRM</h1>
    <p class="text-body-md text-muted-rp">Browse and manage your candidate database.</p>
</div>

<form method="GET" class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><span class="material-symbols-outlined">search</span></span>
            <input type="text" name="q" class="form-control" placeholder="Search candidates..." value="{{ request('q') }}">
        </div>
    </div>
</form>

<div class="row g-3">
    @foreach($candidates as $candidate)
    <div class="col-md-6 col-lg-4">
        <div class="rp-card rp-card-md h-100">
            <div class="d-flex align-items-start gap-3 mb-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($candidate->name) }}&background=0051d5&color=fff" class="rounded-circle" width="48" height="48" alt="">
                <div class="flex-grow-1">
                    <h3 class="text-title-lg text-primary-rp mb-0">{{ $candidate->name }}</h3>
                    <p class="text-body-md text-muted-rp mb-0">{{ $candidate->profile?->headline ?? 'No headline' }}</p>
                </div>
            </div>
            @if($candidate->profile?->skills)
            <div class="d-flex flex-wrap gap-1 mb-3">
                @foreach(array_slice($candidate->profile->skills, 0, 4) as $skill)
                <span class="rp-tag">{{ $skill }}</span>
                @endforeach
            </div>
            @endif
            <a href="{{ route('candidates.review') }}" class="btn btn-rp-primary btn-sm w-100">View Profile</a>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-4">{{ $candidates->links() }}</div>
@endsection
