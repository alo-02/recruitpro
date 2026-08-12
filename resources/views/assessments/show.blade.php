@extends('layouts.dashboard', ['sidebar' => 'student'])
@section('title', $assessment->title)
@section('content')
<h1 class="text-headline-md text-primary-rp mb-2">{{ $assessment->title }}</h1>
<p class="text-muted-rp mb-4">{{ $assessment->description }}</p>
@if($attempt)
<div class="alert alert-info">Last score: <strong>{{ $attempt->score }}%</strong> — {{ $attempt->passed ? 'Passed' : 'Not passed' }}</div>
@endif
<form method="POST" action="{{ route('assessments.submit', $assessment) }}" class="rp-card rp-card-lg p-3">
@csrf
@foreach($assessment->questions as $i => $q)
<div class="mb-4">
  <div class="fw-semibold mb-2">{{ $i+1 }}. {{ $q->prompt }}</div>
  @foreach($q->options as $idx => $opt)
  <div class="form-check">
    <input class="form-check-input" type="radio" name="answers[{{ $q->id }}]" value="{{ $idx }}" id="q{{ $q->id }}_{{ $idx }}" required>
    <label class="form-check-label" for="q{{ $q->id }}_{{ $idx }}">{{ $opt }}</label>
  </div>
  @endforeach
</div>
@endforeach
<button class="btn btn-rp-primary">Submit Assessment</button>
</form>
@endsection
