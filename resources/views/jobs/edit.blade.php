@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Edit Job')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Edit Job</h1>
    <p class="text-body-md text-muted-rp">Update listing details. Changes by recruiters may require re-approval.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="rp-card rp-card-lg">
            <form method="POST" action="{{ route('jobs.update', $job) }}">
                @csrf @method('PUT')
                @include('jobs._form', ['job' => $job])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-rp-primary">Save Changes</button>
                    <a href="{{ route('jobs.manage') }}" class="btn btn-rp-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
