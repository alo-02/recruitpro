@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Recruiter Dashboard')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Recruitment Dashboard</h1>
    <p class="text-body-md text-muted-rp">Manage your hiring pipeline and track performance. Drag cards to update status.</p>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['icon' => 'work', 'label' => 'Active Jobs', 'value' => $stats['active_jobs']],
        ['icon' => 'group', 'label' => 'Total Applicants', 'value' => $stats['total_applicants']],
        ['icon' => 'event', 'label' => 'Interviews', 'value' => $stats['interviews']],
        ['icon' => 'check_circle', 'label' => 'Hires', 'value' => $stats['hires']],
    ] as $stat)
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <span class="material-symbols-outlined text-secondary-rp mb-2">{{ $stat['icon'] }}</span>
            <div class="stat-value">{{ $stat['value'] }}</div>
            <div class="text-label-md text-muted-rp">{{ $stat['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="rp-card rp-card-lg mb-4">
    <h2 class="text-title-lg text-primary-rp mb-4">Hiring Pipeline</h2>
    <div class="d-flex gap-3 overflow-auto pb-2" id="pipeline-board">
        @foreach($pipeline as $status => $col)
        <div class="kanban-column flex-shrink-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-label-md text-primary-rp">{{ $col['name'] }}</span>
                <span class="badge bg-secondary column-count">{{ $col['count'] }}</span>
            </div>
            <div class="kanban-drop-zone min-vh-25" data-status="{{ $status }}" style="min-height:120px;">
                @foreach($col['applications'] as $app)
                <div class="kanban-card mb-2" id="app-{{ $app->id }}" draggable="true" data-id="{{ $app->id }}">
                    <div class="text-body-md fw-semibold">{{ $app->applicant->name }}</div>
                    <span class="text-label-md text-muted-rp">{{ $app->jobPosting->title }}</span>
                    <div class="mt-2">
                        <a href="{{ route('applications.show', $app) }}" class="text-label-md text-secondary-rp">Open</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="rp-card rp-card-lg">
            <h2 class="text-title-lg text-primary-rp mb-3">Upcoming Interviews</h2>
            @forelse($upcomingInterviews as $interview)
            <div class="d-flex align-items-center gap-3 py-3 border-bottom">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($interview->candidate->name) }}&background=0051d5&color=fff" class="rounded-circle" width="40" height="40" alt="">
                <div>
                    <div class="text-body-md fw-semibold">{{ $interview->candidate->name }}</div>
                    <div class="text-body-md text-muted-rp">{{ $interview->application->jobPosting->title }} · {{ $interview->scheduled_at->format('M d, g:i A') }}</div>
                </div>
            </div>
            @empty
            <p class="text-body-md text-muted-rp">No upcoming interviews.</p>
            @endforelse
        </div>
    </div>
    <div class="col-lg-6">
        <div class="rp-card rp-card-lg">
            <h2 class="text-title-lg text-primary-rp mb-3">Active Job Postings</h2>
            @forelse($activeJobs as $job)
            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                <div>
                    <div class="text-body-md fw-semibold">{{ $job->title }}</div>
                    <div class="text-body-md text-muted-rp">{{ $job->applications_count }} applicants</div>
                </div>
                <a href="{{ route('jobs.manage') }}" class="btn btn-rp-outline btn-sm">Manage</a>
            </div>
            @empty
            <p class="text-body-md text-muted-rp">No active jobs. <a href="{{ route('jobs.post') }}">Post one</a></p>
            @endforelse
            <a href="{{ route('jobs.post') }}" class="btn btn-rp-primary w-100 mt-3">Post New Job</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const cards = document.querySelectorAll('.kanban-card[draggable="true"]');
    const columns = document.querySelectorAll('.kanban-drop-zone');

    cards.forEach(card => {
        card.addEventListener('dragstart', function (e) {
            e.dataTransfer.setData('text/plain', this.id);
            this.classList.add('opacity-50');
        });
        card.addEventListener('dragend', function () {
            this.classList.remove('opacity-50');
        });
    });

    columns.forEach(col => {
        col.addEventListener('dragover', e => { e.preventDefault(); col.classList.add('border', 'border-primary'); });
        col.addEventListener('dragleave', () => col.classList.remove('border', 'border-primary'));
        col.addEventListener('drop', async function (e) {
            e.preventDefault();
            col.classList.remove('border', 'border-primary');
            const id = e.dataTransfer.getData('text/plain');
            const card = document.getElementById(id);
            if (!card) return;
            col.appendChild(card);
            const appId = card.dataset.id;
            const status = col.dataset.status;
            try {
                const res = await fetch(`/applications/${appId}/pipeline`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status })
                });
                if (!res.ok) throw new Error('Failed');
                showToast('Pipeline updated', 'success');
            } catch (err) {
                showToast('Could not update pipeline', 'error');
            }
        });
    });
});
</script>
@endpush
