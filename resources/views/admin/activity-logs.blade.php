@extends('layouts.dashboard', ['sidebar' => 'admin'])

@section('title', 'Activity Logs')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Activity Logs</h1>
    <p class="text-body-md text-muted-rp">Audit trail of platform actions.</p>
</div>

<form method="GET" class="mb-3">
    <input type="text" name="action" value="{{ request('action') }}" class="form-control w-auto d-inline-block" placeholder="Filter by action">
    <button class="btn btn-rp-primary">Search</button>
</form>

<div class="rp-card rp-card-lg">
    <div class="table-responsive">
        <table class="table rp-table mb-0">
            <thead><tr><th>When</th><th>User</th><th>Action</th><th>IP</th></tr></thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('M d, Y g:i A') }}</td>
                    <td>{{ $log->user?->name ?? 'System' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->ip_address }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $logs->links() }}</div>
</div>
@endsection
