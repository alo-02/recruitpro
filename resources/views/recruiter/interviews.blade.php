@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Interview Scheduler')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="text-headline-md text-primary-rp mb-1">Interview Scheduler</h1>
        <p class="text-body-md text-muted-rp mb-0">Schedule and manage candidate interviews.</p>
    </div>
    <button class="btn btn-rp-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#scheduleModal">
        <span class="material-symbols-outlined" style="font-size:18px;">add</span> Schedule Interview
    </button>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="rp-card rp-card-lg">
            <h2 class="text-title-lg text-primary-rp mb-4">Upcoming Interviews</h2>
            @foreach([
                ['candidate' => 'Alex Kim', 'role' => 'SWE Intern', 'date' => 'Apr 8, 2026', 'time' => '2:00 PM', 'type' => 'Technical', 'interviewer' => 'John Smith'],
                ['candidate' => 'Emily Davis', 'role' => 'UX Intern', 'date' => 'Apr 9, 2026', 'time' => '10:00 AM', 'type' => 'Portfolio Review', 'interviewer' => 'Lisa Wong'],
                ['candidate' => 'Sarah Chen', 'role' => 'Frontend Intern', 'date' => 'Apr 10, 2026', 'time' => '3:30 PM', 'type' => 'Behavioral', 'interviewer' => 'Mike Torres'],
            ] as $interview)
            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-center bg-surface-container-low rounded p-2" style="min-width:60px;">
                        <div class="text-label-md text-secondary-rp">{{ explode(' ', $interview['date'])[0] }}</div>
                        <div class="text-title-lg text-primary-rp fw-bold">{{ explode(', ', $interview['date'])[0] }}</div>
                    </div>
                    <div>
                        <div class="text-body-md fw-semibold">{{ $interview['candidate'] }} — {{ $interview['role'] }}</div>
                        <div class="text-body-md text-muted-rp">{{ $interview['time'] }} &bull; {{ $interview['interviewer'] }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="rp-tag">{{ $interview['type'] }}</span>
                    <button class="btn btn-rp-outline btn-sm">Reschedule</button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-4">
        <div class="rp-card rp-card-lg">
            <h2 class="text-title-lg text-primary-rp mb-3">Today's Schedule</h2>
            <div class="text-center py-4 text-muted-rp">
                <span class="material-symbols-outlined d-block mb-2" style="font-size:48px;">event_available</span>
                <p class="text-body-md mb-0">No interviews scheduled for today.</p>
            </div>
        </div>
    </div>
</div>

{{-- Schedule Modal --}}
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-title-lg">Schedule Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-label-md text-muted-rp">Candidate</label>
                    <select class="form-select">
                        <option>Alex Kim — SWE Intern</option>
                        <option>Emily Davis — UX Intern</option>
                        <option>Sarah Chen — Frontend Intern</option>
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label text-label-md text-muted-rp">Date</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-label-md text-muted-rp">Time</label>
                        <input type="time" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-label-md text-muted-rp">Interview Type</label>
                    <select class="form-select">
                        <option>Technical</option>
                        <option>Behavioral</option>
                        <option>Portfolio Review</option>
                        <option>Final Round</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-rp-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-rp-primary" onclick="showToast('Interview scheduled!', 'success')" data-bs-dismiss="modal">Schedule</button>
            </div>
        </div>
    </div>
</div>
@endsection
