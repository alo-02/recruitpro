@extends('layouts.dashboard', ['sidebar' => 'recruiter'])
@section('title', 'Hiring Stages')
@section('content')
<h1 class="text-headline-md text-primary-rp mb-4">Custom Hiring Stages</h1>
<form method="POST" action="{{ route('stages.store') }}" class="rp-card p-3 mb-4 d-flex gap-2">
@csrf
<input name="name" class="form-control" placeholder="Stage name" required>
<input name="color" type="color" class="form-control form-control-color" value="#64748b">
<button class="btn btn-rp-primary">Add</button>
</form>
<div class="d-flex flex-wrap gap-2">
@foreach($stages as $stage)
<span class="badge" style="background:{{ $stage->color }}">{{ $stage->position+1 }}. {{ $stage->name }}</span>
@endforeach
</div>
@endsection
