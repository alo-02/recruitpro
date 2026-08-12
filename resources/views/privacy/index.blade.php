@extends('layouts.dashboard', ['sidebar' => auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isRecruiter() ? 'recruiter' : 'student')])

@section('title', 'Privacy & Data')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Privacy & GDPR</h1>
    <p class="text-body-md text-muted-rp">Export or permanently delete your personal data.</p>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="rp-card rp-card-lg h-100">
            <h2 class="text-title-lg mb-2">Export my data</h2>
            <p class="text-muted-rp mb-3">Download a JSON copy of your profile, applications, messages, and related records.</p>
            <a href="{{ route('privacy.export') }}" class="btn btn-rp-primary">Download export</a>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rp-card rp-card-lg h-100 border-danger">
            <h2 class="text-title-lg mb-2 text-danger">Delete my account</h2>
            <p class="text-muted-rp mb-3">This soft-deletes your account and removes uploaded files. Type <strong>DELETE</strong> to confirm.</p>
            <form method="POST" action="{{ route('privacy.destroy') }}" onsubmit="return confirm('Delete your account permanently?')">
                @csrf @method('DELETE')
                <div class="mb-2">
                    <input type="password" name="password" class="form-control" placeholder="Current password" required>
                    @error('password')<div class="text-danger text-label-md">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <input type="text" name="confirm" class="form-control" placeholder="Type DELETE" required>
                </div>
                <button class="btn btn-outline-danger">Delete account</button>
            </form>
        </div>
    </div>
</div>
@endsection
