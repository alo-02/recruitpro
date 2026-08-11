@extends('layouts.auth')

@section('title', 'Password Reset')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center p-4 bg-surface-rp">
    <div class="w-100" style="max-width:440px;">
        <div class="text-center mb-4">
            <span class="material-symbols-outlined text-secondary-rp icon-fill" style="font-size:48px;">lock_reset</span>
            <h1 class="text-headline-md text-primary-rp mt-2">Reset Password</h1>
            <p class="text-body-md text-muted-rp">Enter your email and we'll send you a reset link.</p>
        </div>

        <div class="rp-card rp-card-lg">
            <form id="reset-form">
                <div class="mb-4">
                    <label class="form-label text-label-md text-muted-rp" for="reset-email">Email Address</label>
                    <div class="rp-input-icon">
                        <span class="material-symbols-outlined">mail</span>
                        <input type="email" id="reset-email" class="form-control" placeholder="name@company.com" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-rp-primary w-100 py-2 mb-3">Send Reset Link</button>
                <a href="{{ route('login') }}" class="btn btn-rp-outline w-100 py-2 d-flex align-items-center justify-content-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span> Back to Login
                </a>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('reset-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    showToast('Password reset link sent to your email.', 'success');
});
</script>
@endpush
@endsection
