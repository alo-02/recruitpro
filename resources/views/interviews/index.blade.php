@extends('layouts.dashboard', ['sidebar' => auth()->user()->isStudent() ? 'student' : 'recruiter'])

@section('title', 'Interviews')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="text-headline-md text-primary-rp mb-1">Interviews</h1>
        <p class="text-body-md text-muted-rp mb-0">Schedule and track interview sessions.</p>
    </div>
    @if(auth()->user()->isRecruiter() || auth()->user()->isAdmin())
    <button class="btn btn-rp-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal">Schedule Interview</button>
    @endif
</div>

<div class="rp-card rp-card-lg">
    <div class="table-responsive">
        <table class="table rp-table mb-0">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Role</th>
                    <th>When</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($interviews as $interview)
                <tr>
                    <td>{{ $interview->candidate->name }}</td>
                    <td>{{ $interview->application->jobPosting->title }}</td>
                    <td>{{ $interview->scheduled_at->format('M d, Y g:i A') }}</td>
                    <td>{{ ucfirst($interview->mode) }}</td>
                    <td><span class="badge {{ $interview->status->badgeClass() }}">{{ $interview->status->label() }}</span></td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @if($interview->meeting_url)
                                <a href="{{ $interview->meeting_url }}" target="_blank" class="btn btn-sm btn-rp-outline">Join video</a>
                            @endif
                            <a href="{{ route('interviews.ics', $interview) }}" class="btn btn-sm btn-rp-outline">.ICS</a>
                            <a href="{{ $interview->googleCalendarUrl() }}" target="_blank" class="btn btn-sm btn-rp-outline">Google Cal</a>
                        </div>
                        <form method="POST" action="{{ route('interviews.update-status', $interview) }}" class="d-flex gap-1 mt-1">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select form-select-sm">
                                @foreach(\App\Enums\InterviewStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($interview->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-rp-outline btn-sm">Update</button>
                        </form>
                        @if(auth()->user()->isRecruiter() || auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('scorecards.store', $interview) }}" class="mt-2 border rounded p-2">
                            @csrf
                            <div class="text-label-md mb-1">Scorecard</div>
                            <div class="d-flex gap-1 mb-1">
                                <input type="number" min="1" max="5" name="technical" class="form-control form-control-sm" placeholder="Tech">
                                <input type="number" min="1" max="5" name="communication" class="form-control form-control-sm" placeholder="Comm">
                                <input type="number" min="1" max="5" name="culture_fit" class="form-control form-control-sm" placeholder="Culture">
                                <input type="number" min="1" max="5" name="overall" class="form-control form-control-sm" placeholder="Overall" required>
                            </div>
                            <select name="recommendation" class="form-select form-select-sm mb-1" required>
                                <option value="hire">Hire</option>
                                <option value="maybe">Maybe</option>
                                <option value="no">No</option>
                            </select>
                            <textarea name="feedback" class="form-control form-control-sm mb-1" rows="2" placeholder="Feedback"></textarea>
                            <button class="btn btn-sm btn-rp-primary">Save scorecard</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted-rp py-4">No interviews scheduled.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $interviews->links() }}</div>
</div>

@if(auth()->user()->isRecruiter() || auth()->user()->isAdmin())
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('interviews.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Schedule Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Application</label>
                    <select name="application_id" class="form-select" required>
                        <option value="">Select applicant</option>
                        @foreach($applications as $app)
                            <option value="{{ $app->id }}">{{ $app->applicant->name }} — {{ $app->jobPosting->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date & Time</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mode</label>
                    <select name="mode" class="form-select" required>
                        <option value="online">Online</option>
                        <option value="onsite">On-site</option>
                        <option value="phone">Phone</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Location / Meeting Link</label>
                    <input type="text" name="location_or_link" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-rp-outline" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-rp-primary">Schedule</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
