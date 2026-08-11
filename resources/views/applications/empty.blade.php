@extends('layouts.dashboard', ['sidebar' => 'student'])

@section('title', 'No Applications')

@section('content')
<div class="empty-state">
    <span class="material-symbols-outlined d-block">description</span>
    <h1 class="text-headline-md text-primary-rp mb-2">No Applications Yet</h1>
    <p class="text-body-lg text-muted-rp mb-4 mx-auto" style="max-width:400px;">
        You haven't applied to any jobs yet. Start exploring opportunities and take the first step in your career journey.
    </p>
    <a href="{{ route('jobs.index') }}" class="btn btn-rp-primary d-inline-flex align-items-center gap-1">
        <span class="material-symbols-outlined" style="font-size:18px;">search</span>
        Find Jobs
    </a>
</div>
@endsection
