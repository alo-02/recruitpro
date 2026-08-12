@extends('layouts.dashboard', ['sidebar' => auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isRecruiter() ? 'recruiter' : 'student')])

@section('title', 'Account Settings')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Account Settings</h1>
    <p class="text-body-md text-muted-rp">Manage your account preferences and security.</p>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="rp-card rp-card-lg mb-4">
            <h2 class="text-title-lg text-primary-rp mb-4">Profile & Security</h2>
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf @method('PUT')
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
                <button class="btn btn-rp-primary">Save Settings</button>
            </form>

            <hr class="my-4">
            <h3 class="text-title-lg mb-3">{{ __('Language') }}</h3>
            <form method="POST" action="{{ route('locale.switch') }}" class="d-flex gap-2 mb-3">
                @csrf
                <select name="locale" class="form-select">
                    <option value="en" @selected(($user->locale ?? app()->getLocale()) === 'en')>{{ __('English') }}</option>
                    <option value="bn" @selected(($user->locale ?? app()->getLocale()) === 'bn')>{{ __('Bangla') }}</option>
                </select>
                <button class="btn btn-rp-outline">Save</button>
            </form>

            <hr class="my-4">
            <h3 class="text-title-lg mb-3">Push Notifications</h3>
            <form method="POST" action="{{ route('push.subscribe') }}">
                @csrf
                <input type="url" name="endpoint" class="form-control mb-2" placeholder="Web Push endpoint URL" required>
                <button class="btn btn-rp-outline">Save Push Endpoint</button>
            </form>

            <hr class="my-4">
            <h3 class="text-title-lg mb-3">Two-Factor Authentication</h3>
            <p class="text-muted-rp mb-3">Status: <strong>{{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}</strong></p>
            @if($user->two_factor_enabled)
                <form method="POST" action="{{ route('two-factor.disable') }}">@csrf
                    <button class="btn btn-outline-danger">Disable 2FA</button>
                </form>
            @else
                <form method="POST" action="{{ route('two-factor.enable') }}">@csrf
                    <button class="btn btn-rp-outline">Enable email 2FA</button>
                </form>
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        <div class="rp-card rp-card-lg">
            <h2 class="text-title-lg text-primary-rp mb-3">Notifications</h2>
            @forelse($notifications as $notification)
                <div class="border-bottom py-3 d-flex justify-content-between gap-2">
                    <div>
                        <div class="text-body-md">{{ $notification->data['message'] ?? class_basename($notification->type) }}</div>
                        <div class="text-label-md text-muted-rp">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                    @if(is_null($notification->read_at))
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button class="btn btn-sm btn-rp-outline">Mark read</button>
                    </form>
                    @endif
                </div>
            @empty
                <p class="text-muted-rp mb-0">No notifications yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
