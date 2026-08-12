@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Company Profile')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Company Profile</h1>
    <p class="text-body-md text-muted-rp">Manage your company information. Status: <span class="badge {{ $company->status->badgeClass() }}">{{ $company->status->label() }}</span></p>
</div>

<div class="rp-card rp-card-lg col-lg-8">
    <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="mb-4 text-center">
            @if($company->logo_path)
                <img src="{{ asset('storage/'.$company->logo_path) }}" alt="Logo" class="rounded mb-2" style="max-height:80px;">
            @endif
            <input type="file" name="logo" class="form-control" accept="image/*">
        </div>
        <div class="mb-3">
            <label class="form-label">Company Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Website</label>
            <input type="url" name="website" class="form-control" value="{{ old('website', $company->website) }}">
        </div>
        <div class="mb-4">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="5">{{ old('description', $company->description) }}</textarea>
        </div>
        <button class="btn btn-rp-primary">Save Company Profile</button>
    </form>
</div>
@endsection
