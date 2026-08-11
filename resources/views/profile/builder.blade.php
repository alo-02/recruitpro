@extends('layouts.dashboard', ['sidebar' => 'student'])

@section('title', 'Profile Builder')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Profile Builder</h1>
    <p class="text-body-md text-muted-rp">Build a standout profile to attract top employers.</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-card rp-card-lg mb-4">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <h2 class="text-title-lg text-primary-rp mb-4">Personal Information</h2>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-label-md text-muted-rp">Headline</label>
                        <input type="text" name="headline" class="form-control" value="{{ old('headline', $profile->headline) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-label-md text-muted-rp">Bio</label>
                        <textarea name="bio" class="form-control" rows="4">{{ old('bio', $profile->bio) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $profile->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Location</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location', $profile->location) }}">
                    </div>
                </div>

                <h2 class="text-title-lg text-primary-rp mb-3">Skills</h2>
                <input type="text" name="skills" class="form-control mb-4" value="{{ old('skills', implode(', ', $profile->skills ?? [])) }}" placeholder="JavaScript, React, Python">

                <h2 class="text-title-lg text-primary-rp mb-3">Education</h2>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">School</label>
                        <input type="text" name="school" class="form-control" value="{{ old('school', $profile->education['school'] ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Degree</label>
                        <input type="text" name="degree" class="form-control" value="{{ old('degree', $profile->education['degree'] ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Graduation Year</label>
                        <input type="number" name="graduation_year" class="form-control" value="{{ old('graduation_year', $profile->education['graduation_year'] ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">GPA</label>
                        <input type="text" name="gpa" class="form-control" value="{{ old('gpa', $profile->education['gpa'] ?? '') }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-label-md text-muted-rp">Resume (PDF/DOC)</label>
                    <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                </div>

                <button type="submit" class="btn btn-rp-primary">Save Profile</button>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="rp-card rp-card-lg position-sticky" style="top:80px;">
            <div class="text-center mb-4">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=96&background=0051d5&color=fff" class="rounded-circle mb-3" width="96" height="96" alt="Profile">
                <h3 class="text-title-lg text-primary-rp">{{ $user->name }}</h3>
                <p class="text-body-md text-muted-rp">{{ $profile->headline ?? 'Add a headline' }}</p>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-label-md">Profile Strength</span>
                    <span class="text-label-md text-secondary-rp">{{ $profile->completeness }}%</span>
                </div>
                <div class="rp-progress">
                    <div class="rp-progress-bar" style="width:{{ $profile->completeness }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
