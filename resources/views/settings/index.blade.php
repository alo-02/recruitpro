@extends('layouts.dashboard', ['sidebar' => 'student'])

@section('title', 'Account Settings')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Account Settings</h1>
    <p class="text-body-md text-muted-rp">Manage your account preferences and security.</p>
</div>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="list-group">
            <a href="#profile" class="list-group-item list-group-item-action active">Profile</a>
            <a href="#security" class="list-group-item list-group-item-action">Security</a>
            <a href="#notifications" class="list-group-item list-group-item-action">Notifications</a>
            <a href="#privacy" class="list-group-item list-group-item-action">Privacy</a>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="rp-card rp-card-lg mb-4" id="profile">
            <h2 class="text-title-lg text-primary-rp mb-4">Profile Settings</h2>
            <form>
                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="https://ui-avatars.com/api/?name=Alex+Johnson&size=64&background=0051d5&color=fff" class="rounded-circle" width="64" height="64" alt="">
                    <button type="button" class="btn btn-rp-outline btn-sm">Change Photo</button>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Full Name</label>
                        <input type="text" class="form-control" value="Alex Johnson">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Email</label>
                        <input type="email" class="form-control" value="alex@university.edu">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Phone</label>
                        <input type="tel" class="form-control" value="+1 (555) 123-4567">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-label-md text-muted-rp">Location</label>
                        <input type="text" class="form-control" value="San Francisco, CA">
                    </div>
                </div>
                <button type="submit" class="btn btn-rp-primary" onclick="event.preventDefault(); showToast('Settings saved!', 'success')">Save Changes</button>
            </form>
        </div>

        <div class="rp-card rp-card-lg" id="security">
            <h2 class="text-title-lg text-primary-rp mb-4">Security</h2>
            <div class="mb-3">
                <label class="form-label text-label-md text-muted-rp">Current Password</label>
                <input type="password" class="form-control">
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-label-md text-muted-rp">New Password</label>
                    <input type="password" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-label-md text-muted-rp">Confirm Password</label>
                    <input type="password" class="form-control">
                </div>
            </div>
            <button type="button" class="btn btn-rp-primary" onclick="showToast('Password updated!', 'success')">Update Password</button>
        </div>
    </div>
</div>
@endsection
