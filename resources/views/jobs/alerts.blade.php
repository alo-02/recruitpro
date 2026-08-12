@extends('layouts.dashboard', ['sidebar' => 'student'])

@section('title', 'Job Alerts')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Job Alerts</h1>
    <p class="text-body-md text-muted-rp">Get emailed when matching jobs are posted.</p>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="rp-card rp-card-lg">
            <form method="POST" action="{{ route('jobs.alerts.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Keywords</label>
                    <input type="text" name="keywords" class="form-control" placeholder="React, Intern">
                </div>
                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" placeholder="Remote / City">
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select name="job_type" class="form-select">
                        <option value="">Any</option>
                        <option value="internship">Internship</option>
                        <option value="full-time">Full-time</option>
                        <option value="contract">Contract</option>
                    </select>
                </div>
                <button class="btn btn-rp-primary w-100">Create Alert</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="rp-card rp-card-lg">
            @forelse($alerts as $alert)
                <div class="d-flex justify-content-between border-bottom py-3">
                    <div>
                        <div class="fw-semibold">{{ $alert->keywords ?: 'Any keywords' }}</div>
                        <div class="text-muted-rp text-label-md">{{ $alert->location ?: 'Any location' }} · {{ $alert->job_type ?: 'Any type' }}</div>
                    </div>
                    <form method="POST" action="{{ route('jobs.alerts.destroy', $alert) }}">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            @empty
                <p class="text-muted-rp mb-0">No alerts yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
