@extends('layouts.auth')

@section('title', 'Two-Factor Verification')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center p-4 bg-surface-rp">
    <div class="w-100" style="max-width:420px;">
        <div class="rp-card rp-card-lg">
            <h1 class="text-headline-md text-primary-rp mb-2">Enter verification code</h1>
            <p class="text-body-md text-muted-rp mb-4">Check your email for a 6-digit code.</p>
            @include('partials.flash')
            <form method="POST" action="{{ route('two-factor.verify') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control form-control-lg text-center" maxlength="6" inputmode="numeric" required autofocus>
                    @error('code')<div class="text-danger text-label-md mt-1">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-rp-primary w-100">Verify & Continue</button>
            </form>
        </div>
    </div>
</div>
@endsection
