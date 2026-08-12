@extends('layouts.dashboard', ['sidebar' => 'admin'])

@section('title', 'Job Moderation')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Job Moderation</h1>
    <p class="text-body-md text-muted-rp">Review and approve job postings.</p>
</div>

<form method="GET" class="mb-3">
    <select name="moderation" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
        <option value="">All</option>
        @foreach(['pending','approved','rejected'] as $status)
            <option value="{{ $status }}" @selected(request('moderation') === $status)>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
</form>

<div class="rp-card rp-card-lg">
    <div class="table-responsive">
        <table class="table rp-table mb-0">
            <thead><tr><th>Job</th><th>Company</th><th>Status</th><th>Moderation</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($jobs as $job)
                <tr>
                    <td><a href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></td>
                    <td>{{ $job->company->name }}</td>
                    <td>{{ $job->status->label() }}</td>
                    <td><span class="badge {{ $job->moderation_status->badgeClass() }}">{{ $job->moderation_status->label() }}</span></td>
                    <td>
                        @if($job->moderation_status->value === 'pending')
                        <form method="POST" action="{{ route('admin.jobs.moderate', $job) }}" class="d-inline">@csrf @method('PATCH')
                            <input type="hidden" name="moderation_status" value="approved">
                            <button class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.jobs.moderate', $job) }}" class="d-inline">@csrf @method('PATCH')
                            <input type="hidden" name="moderation_status" value="rejected">
                            <button class="btn btn-sm btn-outline-danger">Reject</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $jobs->links() }}</div>
</div>
@endsection
