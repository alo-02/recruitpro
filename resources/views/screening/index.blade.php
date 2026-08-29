@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'AI CV Screening')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="text-headline-md text-primary-rp mb-1">AI-Based CV Screening</h1>
        <p class="text-body-md text-muted-rp mb-0">Rank applicants against job requirements. You always make the final call — nothing is auto-rejected.</p>
    </div>
</div>

<div class="rp-card rp-card-lg mb-4">
    <h2 class="text-title-lg text-primary-rp mb-3">Start screening</h2>
    @if($jobs->isEmpty())
        <p class="text-body-md text-muted-rp mb-0">No active jobs. <a href="{{ route('jobs.post') }}">Post a job</a> first.</p>
    @else
        <form method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-6">
                <label class="form-label text-label-md">Active job</label>
                <select name="job_slug" id="screening-job" class="form-select" required>
                    @foreach($jobs as $job)
                        @continue(! $job->slug)
                        <option value="{{ $job->slug }}" data-action="{{ route('screening.store', ['job' => $job->slug]) }}">
                            {{ $job->title }} ({{ $job->applications_count }} applicants)
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-label-md">Top-N shortlist size</label>
                <select name="top_n" class="form-select" required>
                    <option value="5">Top 5</option>
                    <option value="10" selected>Top 10</option>
                    <option value="15">Top 15</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-rp-primary w-100" id="start-screening-btn">Start AI Screening</button>
            </div>
        </form>
        <p class="text-label-md text-muted-rp mt-3 mb-0">AI recommendations are advisory. You can shortlist, remove, or move candidates to interviews regardless of rank.</p>
    @endif
</div>

<div class="rp-card rp-card-lg">
    <h2 class="text-title-lg text-primary-rp mb-3">Screening history</h2>
    <div class="table-responsive">
        <table class="table rp-table mb-0">
            <thead>
                <tr>
                    <th>Job</th>
                    <th>When</th>
                    <th>Candidates</th>
                    <th>Top-N</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td class="fw-semibold">{{ $session->jobPosting?->title ?? 'Job removed' }}</td>
                    <td>{{ $session->completed_at?->format('M d, Y g:i A') ?? $session->created_at->format('M d, Y g:i A') }}</td>
                    <td>{{ $session->candidates_evaluated }}</td>
                    <td>Top {{ $session->top_n }}</td>
                    <td><a href="{{ route('screening.show', $session) }}" class="btn btn-rp-outline btn-sm">Open</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted-rp py-4">No screening sessions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $sessions->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('screening-job');
    const form = select?.closest('form');
    const setAction = () => {
        const option = select?.selectedOptions[0];
        if (form && option?.dataset.action) form.action = option.dataset.action;
    };
    setAction();
    select?.addEventListener('change', setAction);
});
</script>
@endpush
