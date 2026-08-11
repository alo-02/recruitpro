@extends('layouts.app')

@section('title', 'Application Submitted')

@section('content')
<div class="container-rp mx-auto px-3 py-5 text-center" style="max-width:600px;">
    <div class="rp-card rp-card-lg">
        <span class="material-symbols-outlined text-success mb-3" style="font-size:64px;">check_circle</span>
        <h1 class="text-headline-md text-primary-rp mb-2">Application Submitted!</h1>
        <p class="text-body-lg text-muted-rp mb-4">
            Your application for <strong>{{ $job->title }}</strong> at {{ $job->company->name }} has been successfully submitted.
        </p>
        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
            <a href="{{ route('applications.index') }}" class="btn btn-rp-primary">Track Application</a>
            <a href="{{ route('jobs.index') }}" class="btn btn-rp-outline">Browse More Jobs</a>
        </div>
    </div>
</div>
@endsection
