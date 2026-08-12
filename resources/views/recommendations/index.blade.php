@extends('layouts.dashboard', ['sidebar' => 'student'])
@section('title', __('Recommended Jobs'))
@section('content')
<h1 class="text-headline-md text-primary-rp mb-1">{{ __('Recommended for you') }}</h1>
<p class="text-muted-rp mb-4">{{ __('Matched from your skills, profile, and resume keywords.') }}</p>
<div class="row g-3">
@forelse($recommendations as $item)
<div class="col-md-6">
  <div class="rp-card rp-card-lg h-100">
    <div class="d-flex justify-content-between mb-2">
      <h3 class="text-title-lg mb-0">{{ $item['job']->title }}</h3>
      <span class="badge bg-success">{{ $item['score'] }}% match</span>
    </div>
    <p class="text-muted-rp mb-2">{{ $item['job']->company?->name }} · {{ $item['job']->location }}</p>
    <div class="mb-2">
      @foreach($item['reasons'] as $reason)<div class="text-label-md text-success">• {{ $reason }}</div>@endforeach
      @if($item['missing'])<div class="text-label-md text-danger mt-1">Missing: {{ implode(', ', array_slice($item['missing'],0,5)) }}</div>@endif
    </div>
    <a href="{{ route('jobs.show', $item['job']) }}" class="btn btn-rp-outline btn-sm">{{ __('View job') }}</a>
  </div>
</div>
@empty
<div class="col-12"><div class="empty-state">{{ __('No recommendations yet. Update your profile skills.') }}</div></div>
@endforelse
</div>
@endsection
