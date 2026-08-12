@extends('layouts.dashboard', ['sidebar' => 'admin'])

@section('title', 'Roles & Permissions')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Roles & Permissions</h1>
    <p class="text-body-md text-muted-rp">Spatie permission matrix for industrial access control.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="rp-card rp-card-lg">
            <h2 class="text-title-lg mb-3">Create permission</h2>
            <form method="POST" action="{{ route('admin.permissions.store') }}">
                @csrf
                <input type="text" name="name" class="form-control mb-2" placeholder="e.g. jobs.feature" required>
                <button class="btn btn-rp-primary w-100">Add</button>
            </form>
            <hr>
            <div class="small text-muted-rp">
                @foreach($permissions as $permission)
                    <span class="badge bg-light text-dark me-1 mb-1">{{ $permission->name }}</span>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        @foreach($roles as $role)
        <div class="rp-card rp-card-lg mb-3">
            <h2 class="text-title-lg mb-3">Role: {{ $role->name }}</h2>
            <form method="POST" action="{{ route('admin.permissions.roles.sync', $role) }}">
                @csrf @method('PUT')
                <div class="row g-2 mb-3">
                    @foreach($permissions as $permission)
                    <div class="col-md-4">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="permissions[]" value="{{ $permission->name }}"
                                @checked($role->hasPermissionTo($permission->name))>
                            <span class="form-check-label">{{ $permission->name }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>
                <button class="btn btn-rp-outline btn-sm">Save role</button>
            </form>
        </div>
        @endforeach
    </div>
</div>

<div class="rp-card rp-card-lg">
    <h2 class="text-title-lg mb-3">Assign to users</h2>
    @foreach($users as $user)
    <form method="POST" action="{{ route('admin.permissions.users.sync', $user) }}" class="border-bottom py-3">
        @csrf @method('PUT')
        <div class="fw-semibold mb-2">{{ $user->name }} <span class="text-muted-rp">({{ $user->email }})</span></div>
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-label-md">Roles</label>
                <select name="roles[]" class="form-select" multiple size="3">
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" @selected($user->hasRole($role->name))>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label text-label-md">Direct permissions</label>
                <select name="permissions[]" class="form-select" multiple size="3">
                    @foreach($permissions as $permission)
                        <option value="{{ $permission->name }}" @selected($user->hasDirectPermission($permission->name))>{{ $permission->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-rp-primary w-100">Save</button>
            </div>
        </div>
    </form>
    @endforeach
    <div class="p-3">{{ $users->links() }}</div>
</div>
@endsection
