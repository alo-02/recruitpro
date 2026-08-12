@extends('layouts.dashboard', ['sidebar' => 'admin'])

@section('title', 'User Management')

@section('content')
<div class="d-flex justify-content-between mb-4">
    <div>
        <h1 class="text-headline-md text-primary-rp mb-1">Users</h1>
        <p class="text-body-md text-muted-rp mb-0">Create, update, block, or delete accounts.</p>
    </div>
    <button class="btn btn-rp-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">Create User</button>
</div>

<form class="rp-card p-3 mb-4" method="GET">
    <div class="row g-2">
        <div class="col-md-5"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name or email"></div>
        <div class="col-md-4">
            <select name="role" class="form-select">
                <option value="">All roles</option>
                @foreach(\App\Enums\UserRole::cases() as $role)
                    <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-rp-primary w-100">Filter</button></div>
    </div>
</form>

<div class="rp-card rp-card-lg">
    <div class="table-responsive">
        <table class="table rp-table mb-0">
            <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $user->name }}</div>
                        <div class="text-muted-rp text-label-md">{{ $user->email }}</div>
                    </td>
                    <td>{{ $user->role->label() }}</td>
                    <td>
                        @if($user->is_blocked)<span class="badge bg-danger">Blocked</span>
                        @elseif(!$user->is_active)<span class="badge bg-secondary">Inactive</span>
                        @else<span class="badge bg-success">Active</span>@endif
                    </td>
                    <td class="text-nowrap">
                        <form method="POST" action="{{ route('admin.users.block', $user) }}" class="d-inline">@csrf @method('PATCH')
                            <button class="btn btn-sm btn-outline-warning">{{ $user->is_blocked ? 'Unblock' : 'Block' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Delete user?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $users->links() }}</div>
</div>

<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.users.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Create User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
                <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
                <select name="role" class="form-select" required>
                    @foreach(\App\Enums\UserRole::cases() as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer"><button class="btn btn-rp-primary">Create</button></div>
        </form>
    </div>
</div>
@endsection
