@extends('layouts.auth')

@section('title', 'Set New Password')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center p-4 bg-surface-rp">
    <div class="w-100" style="max-width:440px;">
        <div class="text-center mb-4">
            <h1 class="text-headline-md text-primary-rp">Choose a new password</h1>
        </div>
        <div class="rp-card rp-card-lg">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button class="btn btn-rp-primary w-100">Update Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
