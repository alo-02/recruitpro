@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center p-4 bg-surface-rp">
    <div class="w-100" style="max-width:480px;">
        <div class="rp-card rp-card-lg text-center">
            <span class="material-symbols-outlined text-secondary-rp mb-3" style="font-size:48px;">mark_email_unread</span>
            <h1 class="text-headline-md text-primary-rp">Verify your email</h1>
            <p class="text-body-md text-muted-rp mb-4">
                We sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
                Please check your inbox (or the mail log in local development).
            </p>
            @include('partials.flash')
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
