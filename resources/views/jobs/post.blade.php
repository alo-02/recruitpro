@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Post a New Job')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Post a New Job</h1>
    <p class="text-body-md text-muted-rp">Create a job listing to attract top candidates.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="rp-card rp-card-lg">
            <form method="POST" action="{{ route('jobs.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label text-label-md text-muted-rp">Job Title *</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="e.g. Software Engineer Intern" required>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Job Type *</label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">Select type</option>
                            @foreach(['internship' => 'Internship', 'full-time' => 'Full-time', 'contract' => 'Contract'] as $val => $label)
                            <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Location Type *</label>
                        <select name="location_type" class="form-select" required>
                            <option value="">Select location</option>
                            @foreach(['remote' => 'Remote', 'onsite' => 'On-site', 'hybrid' => 'Hybrid'] as $val => $label)
                            <option value="{{ $val }}" {{ old('location_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-label-md text-muted-rp">Location</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g. San Francisco, CA">
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Salary Min</label>
                        <input type="text" name="salary_min" class="form-control" value="{{ old('salary_min') }}" placeholder="$35/hr or $80k/yr">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Salary Max</label>
                        <input type="text" name="salary_max" class="form-control" value="{{ old('salary_max') }}" placeholder="$45/hr or $100k/yr">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-label-md text-muted-rp">Job Description *</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="6" placeholder="Describe the role..." required>{{ old('description') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label text-label-md text-muted-rp">Required Skills</label>
                    <input type="text" name="skills" class="form-control" value="{{ old('skills') }}" placeholder="React, TypeScript, Python (comma separated)">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="status" value="active" class="btn btn-rp-primary">Publish Job</button>
                    <button type="submit" name="status" value="draft" class="btn btn-rp-outline">Save as Draft</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
