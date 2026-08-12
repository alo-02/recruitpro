@extends('layouts.dashboard', ['sidebar' => 'admin'])

@section('title', 'Security Events')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Security Events</h1>
    <p class="text-body-md text-muted-rp mb-0">Monitor failed logins, blocked access attempts, and other security signals.</p>
</div>

<div class="row g-3 mb-4">
    @foreach($summary as $label => $value)
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-value">{{ $value }}</div>
            <div class="text-label-md text-muted-rp">{{ str_replace('_', ' ', ucfirst($label)) }}</div>
        </div>
    </div>
    @endforeach
</div>

<form class="rp-card rp-card-lg p-3 mb-4" method="GET">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label text-label-md">Event type</label>
            <input type="text" name="event_type" class="form-control" value="{{ request('event_type') }}" placeholder="login_failed">
        </div>
        <div class="col-md-3">
            <label class="form-label text-label-md">Severity</label>
            <select name="severity" class="form-select">
                <option value="">All</option>
                @foreach(['info','warning','critical'] as $sev)
                    <option value="{{ $sev }}" @selected(request('severity') === $sev)>{{ ucfirst($sev) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="unresolved" value="1" id="unresolved" @checked(request()->boolean('unresolved'))>
                <label class="form-check-label" for="unresolved">Unresolved only</label>
            </div>
        </div>
        <div class="col-md-3">
            <button class="btn btn-rp-primary w-100">Filter</button>
        </div>
    </div>
</form>

<div class="rp-card rp-card-lg">
    <div class="table-responsive">
        <table class="table rp-table mb-0">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Event</th>
                    <th>Severity</th>
                    <th>Email / User</th>
                    <th>IP</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                <tr>
                    <td>{{ $event->created_at->format('M d, Y g:i A') }}</td>
                    <td>{{ $event->event_type }}</td>
                    <td><span class="badge {{ $event->severityBadge() }}">{{ $event->severity }}</span></td>
                    <td>{{ $event->email ?? $event->user?->email ?? '—' }}</td>
                    <td>{{ $event->ip_address ?? '—' }}</td>
                    <td>{{ $event->resolved ? 'Resolved' : 'Open' }}</td>
                    <td>
                        @unless($event->resolved)
                        <form method="POST" action="{{ route('admin.security-events.resolve', $event) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-rp-outline">Resolve</button>
                        </form>
                        @endunless
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted-rp py-4">No security events yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $events->links() }}</div>
</div>
@endsection
