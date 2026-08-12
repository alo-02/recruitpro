@php $job = $job ?? null; @endphp
<div class="mb-4">
    <label class="form-label text-label-md text-muted-rp">Job Title *</label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $job?->title) }}" required>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label text-label-md text-muted-rp">Job Type *</label>
        <select name="type" class="form-select" required>
            <option value="">Select type</option>
            @foreach(['internship' => 'Internship', 'full-time' => 'Full-time', 'contract' => 'Contract'] as $val => $label)
            <option value="{{ $val }}" @selected(old('type', $job?->type) === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label text-label-md text-muted-rp">Category *</label>
        <select name="category" class="form-select" required>
            <option value="">Select category</option>
            @foreach(\App\Enums\JobCategory::cases() as $cat)
            <option value="{{ $cat->value }}" @selected(old('category', $job?->category?->value ?? $job?->category) === $cat->value)>{{ $cat->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label text-label-md text-muted-rp">Location Type *</label>
        <select name="location_type" class="form-select" required>
            <option value="">Select location</option>
            @foreach(['remote' => 'Remote', 'onsite' => 'On-site', 'hybrid' => 'Hybrid'] as $val => $label)
            <option value="{{ $val }}" @selected(old('location_type', $job?->location_type) === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-4">
    <label class="form-label text-label-md text-muted-rp">Location</label>
    <input type="text" name="location" class="form-control" value="{{ old('location', $job?->location) }}">
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label text-label-md text-muted-rp">Salary Min</label>
        <input type="text" name="salary_min" class="form-control" value="{{ old('salary_min', $job?->salary_min) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label text-label-md text-muted-rp">Salary Max</label>
        <input type="text" name="salary_max" class="form-control" value="{{ old('salary_max', $job?->salary_max) }}">
    </div>
</div>

<div class="mb-4">
    <label class="form-label text-label-md text-muted-rp">Application Deadline</label>
    <input type="date" name="deadline" class="form-control" value="{{ old('deadline', $job?->deadline?->format('Y-m-d')) }}">
</div>

<div class="mb-4">
    <label class="form-label text-label-md text-muted-rp">Job Description *</label>
    <textarea name="description" class="form-control" rows="6" required>{{ old('description', $job?->description) }}</textarea>
</div>

<div class="mb-4">
    <label class="form-label text-label-md text-muted-rp">Requirements</label>
    <textarea name="requirements" class="form-control" rows="4">{{ old('requirements', $job?->requirements) }}</textarea>
</div>

<div class="mb-4">
    <label class="form-label text-label-md text-muted-rp">Required Skills</label>
    <input type="text" name="skills" class="form-control" value="{{ old('skills', $job ? implode(', ', $job->skills ?? []) : '') }}" placeholder="React, TypeScript, Python">
</div>

<div class="mb-4">
    <label class="form-label text-label-md text-muted-rp">ATS Keywords * <span class="text-muted">(CV screening)</span></label>
    <input type="text" name="ats_keywords" class="form-control @error('ats_keywords') is-invalid @enderror"
           value="{{ old('ats_keywords', $job ? implode(', ', $job->ats_keywords ?? $job->skills ?? []) : '') }}"
           placeholder="Laravel, PHP, MySQL, REST API, Git" required>
    <div class="form-text">Applicant CVs are scanned for these keywords. Only strong matches appear when you filter by ATS score.</div>
    @error('ats_keywords')<div class="text-danger text-label-md">{{ $message }}</div>@enderror
</div>

<div class="mb-4">
    <label class="form-label text-label-md text-muted-rp">Minimum ATS Score (%)</label>
    <input type="number" name="ats_min_score" class="form-control" min="0" max="100"
           value="{{ old('ats_min_score', $job?->ats_min_score ?? 50) }}">
    <div class="form-text">Default shortlist threshold for this job (e.g. 60 = show CVs matching at least 60% keywords).</div>
</div>

@if($job)
<div class="mb-4">
    <label class="form-label text-label-md text-muted-rp">Status</label>
    <select name="status" class="form-select">
        @foreach(['draft' => 'Draft', 'active' => 'Active', 'closed' => 'Closed'] as $val => $label)
        <option value="{{ $val }}" @selected(old('status', $job->status->value) === $val)>{{ $label }}</option>
        @endforeach
    </select>
</div>
@endif
