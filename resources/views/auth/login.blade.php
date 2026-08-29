@extends('layouts.auth')

@section('title', 'Authentication')

@section('content')
<div class="row g-0 min-vh-100">
    <div class="col-lg-6 auth-brand-panel d-none d-lg-flex flex-column justify-content-between p-5 text-white">
        <a href="{{ route('home') }}" class="rp-brand">
            @include('partials.brand-mark', ['size' => 40, 'tone' => 'light'])
            <span class="rp-brand-word" style="color:#fff6e4;">
                {{ $brand->siteName() }}
                <span class="rp-brand-sub" style="color:#d4a017;">{{ $brand->tagline() }}</span>
            </span>
        </a>
        <div style="max-width:480px;">
            <p class="text-label-md mb-3" style="color:#d4a017;">The match lens</p>
            <h1 class="text-display-lg text-white mb-3">Look at the person, not just the paper.</h1>
            <p class="text-body-lg" style="color:var(--rp-on-primary-container);">
                Sign in to build a profile, post a role, or run AI CV screening — scores advise, you decide.
            </p>
        </div>
        <div class="text-label-md" style="color:var(--rp-on-primary-container);">&copy; {{ date('Y') }} {{ $brand->siteName() }} Internship System</div>
    </div>

    <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5 bg-surface-rp">
        <div class="w-100" style="max-width:440px;">
            @include('partials.flash')

            <div class="d-flex border-bottom mb-4">
                <button type="button" class="auth-tab active" id="tab-login" onclick="switchTab('login')">Log In</button>
                <button type="button" class="auth-tab" id="tab-signup" onclick="switchTab('signup')">Sign Up</button>
            </div>

            <form id="form-login" method="POST" action="{{ route('login') }}">
                @csrf
                <h2 class="text-headline-md mb-1">Welcome back</h2>
                <p class="text-body-md text-muted-rp mb-4">Please enter your details to access your dashboard.</p>

                <div class="mb-3">
                    <label class="form-label text-label-md text-muted-rp" for="login-email">Email Address</label>
                    <div class="rp-input-icon">
                        <span class="material-symbols-outlined">mail</span>
                        <input type="email" name="email" id="login-email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="name@company.com" required>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label text-label-md text-muted-rp mb-0" for="login-password">Password</label>
                        <a href="{{ route('password.request') }}" class="text-label-md text-secondary-rp">Forgot Password?</a>
                    </div>
                    <div class="rp-input-icon">
                        <span class="material-symbols-outlined">lock</span>
                        <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label text-body-md" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-rp-primary w-100 py-2">Sign In</button>
            </form>

            <form id="form-signup" method="POST" action="{{ route('register') }}" class="d-none">
                @csrf
                <h2 class="text-headline-md mb-1">Create an account</h2>
                <p class="text-body-md text-muted-rp mb-4">Join the network to find or post opportunities.</p>

                <div class="mb-3">
                    <label class="form-label text-label-md text-muted-rp" for="signup-name">Full Name</label>
                    <input type="text" name="name" id="signup-name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="John Doe" required>
                </div>

                <div class="mb-3">
                    <label class="text-label-md text-muted-rp d-block mb-2">I am a...</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="role-option w-100">
                                <input type="radio" name="role" value="student" {{ old('role', 'student') === 'student' ? 'checked' : '' }} onchange="toggleCompanyField()">
                                <span class="material-symbols-outlined me-1" style="font-size:20px;">school</span> Candidate
                            </label>
                        </div>
                        <div class="col-6">
                            <label class="role-option w-100">
                                <input type="radio" name="role" value="recruiter" {{ old('role') === 'recruiter' ? 'checked' : '' }} onchange="toggleCompanyField()">
                                <span class="material-symbols-outlined me-1" style="font-size:20px;">business_center</span> Recruiter
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3 {{ old('role') === 'recruiter' ? '' : 'd-none' }}" id="company-name-field">
                    <label class="form-label text-label-md text-muted-rp" for="company_name">Company Name</label>
                    <input type="text" name="company_name" id="company_name" class="form-control" value="{{ old('company_name') }}" placeholder="Acme Inc.">
                </div>

                <div class="mb-3">
                    <label class="form-label text-label-md text-muted-rp" for="signup-email">Email Address</label>
                    <div class="rp-input-icon">
                        <span class="material-symbols-outlined">mail</span>
                        <input type="email" name="email" id="signup-email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="name@company.com" required>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label text-label-md text-muted-rp" for="signup-password">Password</label>
                        <input type="password" name="password" id="signup-password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label text-label-md text-muted-rp" for="signup-confirm">Confirm</label>
                        <input type="password" name="password_confirmation" id="signup-confirm" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-rp-primary w-100 py-2">Create Account</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleCompanyField() {
    const isRecruiter = document.querySelector('input[name="role"]:checked')?.value === 'recruiter';
    document.getElementById('company-name-field')?.classList.toggle('d-none', !isRecruiter);
}
if (window.location.hash === '#signup' || {{ $errors->has('name') || $errors->has('role') || $errors->has('company_name') ? 'true' : 'false' }}) {
    switchTab('signup');
}
toggleCompanyField();
</script>
@endpush
@endsection
