@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Post a New Job')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Post a New Job</h1>
    <p class="text-body-md text-muted-rp">Create a job listing to attract top candidates.</p>
</div>

@if(isset($company) && !$company->isApproved())
<div class="alert alert-warning">Your company is <strong>{{ $company->status->label() }}</strong>. You can draft jobs, but publishing requires admin approval of your company.</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="rp-card rp-card-lg">
            <form method="POST" action="{{ route('jobs.store') }}">
                @csrf
                @include('jobs._form', ['job' => null])
                <div class="d-flex gap-2">
                    <button type="submit" name="status" value="active" class="btn btn-rp-primary">Publish Job</button>
                    <button type="submit" name="status" value="draft" class="btn btn-rp-outline">Save as Draft</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
