@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Job Listings Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="text-headline-md text-primary-rp mb-1">Job Listings</h1>
        <p class="text-body-md text-muted-rp mb-0">Manage your active and draft job postings.</p>
    </div>
    <a href="{{ route('jobs.post') }}" class="btn btn-rp-primary d-flex align-items-center gap-1">
        <span class="material-symbols-outlined" style="font-size:18px;">add</span> Post New Job
    </a>
</div>

<div class="rp-card rp-card-lg">
    <div class="table-responsive">
        <table class="table rp-table mb-0">
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Type</th>
                    <th>Applicants</th>
                    <th>Status</th>
                    <th>Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobs as $job)
                <tr>
                    <td class="text-body-md fw-semibold">
                        <a href="{{ route('jobs.show', $job) }}" class="text-decoration-none">{{ $job->title }}</a>
                    </td>
                    <td><span class="rp-tag">{{ ucfirst(str_replace('-', ' ', $job->type)) }}</span></td>
                    <td class="text-body-md">{{ $job->applications_count }}</td>
                    <td>
                        <span class="badge {{ $job->status->value === 'active' ? 'bg-success' : ($job->status->value === 'draft' ? 'bg-secondary' : 'bg-danger') }} text-label-md">
                            {{ $job->status->label() }}
                        </span>
                    </td>
                    <td class="text-body-md text-muted-rp">{{ $job->published_at?->format('M d, Y') ?? '—' }}</td>
                    <td>
                        @if($job->status->value === 'active')
                        <form action="{{ route('jobs.close', $job) }}" method="POST" class="d-inline">
                            @csrf @method('PATCH')
                            <button class="btn btn-outline-danger btn-sm">Close</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted-rp py-4">No job postings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $jobs->links() }}</div>
</div>
@endsection
