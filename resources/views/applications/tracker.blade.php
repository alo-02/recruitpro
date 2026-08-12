@extends('layouts.dashboard', ['sidebar' => 'student'])

@section('title', 'Application Tracker')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Application Tracker</h1>
    <p class="text-body-md text-muted-rp">Track the status of all your job applications.</p>
</div>

<div class="rp-card rp-card-lg">
    <div class="table-responsive">
        <table class="table rp-table mb-0">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Company</th>
                    <th>Applied</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $app)
                <tr>
                    <td class="text-body-md fw-semibold">{{ $app->jobPosting->title }}</td>
                    <td class="text-body-md text-muted-rp">{{ $app->jobPosting->company->name }}</td>
                    <td class="text-body-md text-muted-rp">{{ $app->applied_at->format('M d, Y') }}</td>
                    <td><span class="badge {{ $app->status->badgeClass() }} text-label-md">{{ $app->status->label() }}</span></td>
                    <td class="text-nowrap">
                        <a href="{{ route('applications.show', $app) }}" class="btn btn-rp-outline btn-sm">View</a>
                        @if($app->status->canWithdraw())
                        <form method="POST" action="{{ route('applications.withdraw', $app) }}" class="d-inline" onsubmit="return confirm('Withdraw this application?')">
                            @csrf @method('PATCH')
                            <button class="btn btn-outline-danger btn-sm">Withdraw</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $applications->links() }}</div>
</div>
@endsection
