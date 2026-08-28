@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Screening Results')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <a href="{{ route('screening.index') }}" class="text-label-md text-secondary-rp">← Screening history</a>
        <h1 class="text-headline-md text-primary-rp mt-2 mb-1">{{ $session->jobPosting->title }}</h1>
        <p class="text-body-md text-muted-rp mb-0">
            {{ $session->candidates_evaluated }} candidate(s) ranked on {{ $session->completed_at?->format('M d, Y g:i A') }}.
            AI recommendations are shown first — you can override any suggestion.
        </p>
    </div>
    <form method="GET" class="d-flex align-items-center gap-2">
        <label class="text-label-md mb-0" for="top_n">Show</label>
        <select name="top_n" id="top_n" class="form-select" onchange="this.form.submit()">
            <option value="5" @selected($topN === 5)>Top 5</option>
            <option value="10" @selected($topN === 10)>Top 10</option>
            <option value="15" @selected($topN === 15)>Top 15</option>
        </select>
    </form>
</div>

@if($session->job_requirements)
<div class="rp-card rp-card-lg mb-4">
    <h2 class="text-title-lg text-primary-rp mb-3">Job requirements analyzed</h2>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="text-label-md text-muted-rp mb-1">Required skills</div>
            @forelse($session->job_requirements['required_skills'] ?? [] as $skill)
                <span class="badge bg-primary-subtle text-primary me-1 mb-1">{{ $skill }}</span>
            @empty
                <span class="text-muted-rp">None extracted</span>
            @endforelse
        </div>
        <div class="col-md-6">
            <div class="text-label-md text-muted-rp mb-1">Preferred skills</div>
            @forelse($session->job_requirements['preferred_skills'] ?? [] as $skill)
                <span class="badge bg-light text-dark me-1 mb-1">{{ $skill }}</span>
            @empty
                <span class="text-muted-rp">None extracted</span>
            @endforelse
        </div>
        <div class="col-12">
            @foreach($session->job_requirements['criteria'] ?? [] as $item)
                <span class="rp-tag me-1">{{ $item }}</span>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="alert alert-info">
    Candidates outside the Top {{ $topN }} are still listed below. Shortlist or remove anyone — the system never auto-rejects.
</div>

<h2 class="text-title-lg text-primary-rp mb-3">AI-recommended (Top {{ $topN }})</h2>
@include('screening._table', ['results' => $recommended, 'session' => $session, 'empty' => 'No candidates in this Top-N window.'])

<h2 class="text-title-lg text-primary-rp mt-4 mb-3">Other applicants</h2>
@include('screening._table', ['results' => $outside, 'session' => $session, 'empty' => 'Every applicant is currently inside the Top-N list.'])
@endsection
