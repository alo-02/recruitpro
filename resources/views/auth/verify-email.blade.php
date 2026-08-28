@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center p-4 bg-surface-rp">
    <div class="w-100" style="max-width:480px;">
        <div class="rp-card rp-card-lg text-center">
            <span class="material-symbols-outlined text-secondary-rp mb-3" style="font-size:48px;">mark_email_unread</span>
            <h1 class="text-headline-md text-primary-rp">Verify your email</h1>
            <p class="text-body-md text-muted-rp mb-4">
                We sent a verification link and a 6-digit code to <strong>{{ auth()->user()->email }}</strong>.
                Open the email (including Gmail's spam/promotions tabs) and click the button, or enter the code below.
            </p>
            @include('partials.flash')
            <form method="POST" action="{{ route('verification.code') }}" class="mb-3 text-start">
                @csrf
                <label class="form-label text-label-md" for="code">6-digit code</label>
                <input type="text" name="code" id="code" class="form-control text-center mb-2 @error('code') is-invalid @enderror" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" required>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <button class="btn btn-rp-primary w-100">Verify with code</button>
            </form>
            <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
                @csrf
                <button class="btn btn-rp-primary w-100">Resend verification email</button>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-rp-outline w-100">Logout</button>
            </form>
        </div>
    </div>
</div>
@endsection
