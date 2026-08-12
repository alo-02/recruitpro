@extends('layouts.dashboard', ['sidebar' => 'student'])
@section('title', 'Skill Assessments')
@section('content')
<h1 class="text-headline-md text-primary-rp mb-4">Skill Assessments</h1>
<div class="row g-3">
@forelse($assessments as $a)
<div class="col-md-4">
  <div class="rp-card rp-card-lg h-100">
    <h3 class="text-title-lg">{{ $a->title }}</h3>
    <p class="text-muted-rp">{{ $a->skill }} · {{ $a->questions_count }} questions · Pass {{ $a->pass_score }}%</p>
    <p>{{ $a->description }}</p>
    <a href="{{ route('assessments.show', $a) }}" class="btn btn-rp-primary btn-sm">Take quiz</a>
  </div>
</div>
@empty
<div class="col-12 text-muted-rp">No assessments available.</div>
@endforelse
</div>
@endsection
