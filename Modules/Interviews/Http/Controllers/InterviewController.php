<?php

namespace Modules\Interviews\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\InterviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Notifications\InterviewScheduled;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InterviewController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Interview::with(['application.jobPosting', 'candidate', 'recruiter'])
            ->latest('scheduled_at');

        if ($user->isStudent()) {
            $query->where('candidate_id', $user->id);
        } elseif ($user->isRecruiter()) {
            $query->where('recruiter_id', $user->id);
        }

        $interviews = $query->paginate(20);

        $applications = collect();
        if ($user->isRecruiter() || $user->isAdmin()) {
            $applications = Application::with(['applicant', 'jobPosting'])
                ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $user->id))
                ->whereIn('status', [
                    ApplicationStatus::Screening->value,
                    ApplicationStatus::Interview->value,
                    ApplicationStatus::Offer->value,
                ])
                ->latest()
                ->get();
        }

        return view('interviews.index', compact('interviews', 'applications'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'mode' => ['required', 'in:online,onsite,phone'],
            'location_or_link' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $application = Application::with('jobPosting')->findOrFail($request->application_id);
        abort_unless(
            $request->user()->isAdmin() || $application->jobPosting->user_id === $request->user()->id,
            403
        );

        $interview = Interview::create([
            'application_id' => $application->id,
            'recruiter_id' => $request->user()->id,
            'candidate_id' => $application->user_id,
            'scheduled_at' => $request->scheduled_at,
            'mode' => $request->mode,
            'location_or_link' => $request->location_or_link,
            'notes' => $request->notes,
            'status' => InterviewStatus::Scheduled,
        ]);

        $application->update(['status' => ApplicationStatus::Interview]);
        $application->applicant->notify(new InterviewScheduled($interview->load('application.jobPosting')));
        ActivityLogger::log('interview.scheduled', $interview);

        return back()->with('success', 'Interview scheduled and candidate notified.');
    }

    public function updateStatus(Request $request, Interview $interview): RedirectResponse
    {
        abort_unless(
            $request->user()->isAdmin()
            || $interview->recruiter_id === $request->user()->id
            || $interview->candidate_id === $request->user()->id,
            403
        );

        $request->validate([
            'status' => ['required', 'in:scheduled,completed,cancelled'],
        ]);

        $interview->update(['status' => $request->status]);
        ActivityLogger::log('interview.status_updated', $interview, ['status' => $request->status]);

        return back()->with('success', 'Interview status updated.');
    }
}
