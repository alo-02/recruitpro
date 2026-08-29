<?php

namespace Modules\Applications\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\InterviewStatus;
use App\Enums\ScreeningDecisionType;
use App\Http\Controllers\Controller;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\ScreeningDecision;
use App\Models\ScreeningResult;
use App\Models\ScreeningSession;
use App\Notifications\InterviewScheduled;
use App\Services\ActivityLogger;
use App\Services\CvScreeningEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScreeningController extends Controller
{
    public function __construct(protected CvScreeningEngine $engine) {}

    public function index(Request $request): View
    {
        $sessions = ScreeningSession::query()
            ->with(['jobPosting', 'recruiter'])
            ->where('recruiter_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        $jobs = $request->user()->jobPostings()
            ->withCount('applications')
            ->where('status', 'active')
            ->latest()
            ->get();

        return view('screening.index', compact('sessions', 'jobs'));
    }

    public function store(Request $request, JobPosting $job): RedirectResponse
    {
        $this->authorizeJob($request, $job);

        $data = $request->validate([
            'top_n' => ['required', 'integer', 'in:5,10,15'],
        ]);

        $count = $job->applications()->where('status', '!=', ApplicationStatus::Withdrawn)->count();
        if ($count === 0) {
            return back()->withErrors(['screening' => 'This job has no applications to screen yet.']);
        }

        $session = $this->engine->run($job, $request->user(), (int) $data['top_n']);
        ActivityLogger::log('screening.started', $session, [
            'job_id' => $job->id,
            'top_n' => $session->top_n,
            'candidates' => $session->candidates_evaluated,
        ]);

        if ($session->candidates_evaluated === 0) {
            return redirect()
                ->route('screening.show', $session)
                ->with('error', 'Screening finished but no CVs could be scored. Confirm each applicant has a readable PDF or Word resume.');
        }

        return redirect()
            ->route('screening.show', $session)
            ->with('success', "AI screening complete. {$session->candidates_evaluated} candidate(s) ranked. Review the list — nothing was auto-rejected.");
    }

    public function show(Request $request, ScreeningSession $session): View
    {
        $this->authorizeSession($request, $session);

        $session->load([
            'jobPosting.company',
            'results.candidate.profile',
            'results.application',
            'results.decisions',
        ]);

        $topN = (int) $request->get('top_n', $session->top_n);
        if (! in_array($topN, [5, 10, 15], true)) {
            $topN = $session->top_n;
        }

        if ($topN !== $session->top_n) {
            $session->update(['top_n' => $topN]);
            $session->results()->each(function (ScreeningResult $result) use ($topN) {
                $result->update(['ai_recommended' => $result->rank <= $topN]);
            });
            $session->load('results.candidate.profile', 'results.application', 'results.decisions');
        }

        $ranked = $session->results->sortBy('rank')->values();
        $recommended = $ranked->where('rank', '<=', $topN)->values();
        $outside = $ranked->where('rank', '>', $topN)->values();

        return view('screening.show', compact('session', 'topN', 'ranked', 'recommended', 'outside'));
    }

    public function candidate(Request $request, ScreeningSession $session, ScreeningResult $result): View
    {
        $this->authorizeSession($request, $session);
        abort_unless($result->screening_session_id === $session->id, 404);

        $result->load(['candidate.profile', 'application.jobPosting', 'decisions.recruiter']);
        $session->load('jobPosting');

        return view('screening.candidate', compact('session', 'result'));
    }

    public function decide(Request $request, ScreeningSession $session, ScreeningResult $result): RedirectResponse
    {
        $this->authorizeSession($request, $session);
        abort_unless($result->screening_session_id === $session->id, 404);

        $data = $request->validate([
            'decision' => ['required', 'in:'.implode(',', array_column(ScreeningDecisionType::cases(), 'value'))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $decision = ScreeningDecisionType::from($data['decision']);

        if ($decision === ScreeningDecisionType::Interview) {
            return $this->pushToInterview($request, $session, $result);
        }

        ScreeningDecision::create([
            'screening_session_id' => $session->id,
            'screening_result_id' => $result->id,
            'application_id' => $result->application_id,
            'recruiter_id' => $request->user()->id,
            'decision' => $decision,
            'notes' => $data['notes'] ?? null,
        ]);

        $application = $result->application;

        if ($decision === ScreeningDecisionType::Shortlist || $decision === ScreeningDecisionType::AcceptRecommendation) {
            if ($application->status === ApplicationStatus::Applied) {
                $application->update(['status' => ApplicationStatus::Screening]);
            }
        }

        ActivityLogger::log('screening.decision', $result, [
            'decision' => $decision->value,
        ]);

        return back()->with('success', 'Your decision was saved. AI suggestions were not applied automatically.');
    }

    public function pushToInterview(Request $request, ScreeningSession $session, ScreeningResult $result): RedirectResponse
    {
        $this->authorizeSession($request, $session);
        abort_unless($result->screening_session_id === $session->id, 404);

        $data = $request->validate([
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'mode' => ['nullable', 'in:online,onsite,phone'],
            'location_or_link' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $application = $result->application()->with(['applicant', 'jobPosting'])->firstOrFail();

        if (! empty($data['scheduled_at'])) {
            $interview = Interview::create([
                'application_id' => $application->id,
                'recruiter_id' => $request->user()->id,
                'candidate_id' => $application->user_id,
                'scheduled_at' => $data['scheduled_at'],
                'mode' => $data['mode'] ?? 'online',
                'location_or_link' => $data['location_or_link'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => InterviewStatus::Scheduled,
            ]);

            $application->applicant->notify(new InterviewScheduled($interview->load('application.jobPosting')));
        }

        $application->update(['status' => ApplicationStatus::Interview]);

        ScreeningDecision::create([
            'screening_session_id' => $session->id,
            'screening_result_id' => $result->id,
            'application_id' => $result->application_id,
            'recruiter_id' => $request->user()->id,
            'decision' => ScreeningDecisionType::Interview,
            'notes' => $data['notes'] ?? null,
        ]);

        ActivityLogger::log('screening.moved_to_interview', $result);

        return back()->with('success', empty($data['scheduled_at'])
            ? 'Candidate moved to the interview stage. Schedule a time from Interviews when ready — an email will be sent.'
            : 'Candidate moved to interviews and notified by email.');
    }

    protected function authorizeJob(Request $request, JobPosting $job): void
    {
        abort_unless(
            $request->user()->isAdmin() || $job->user_id === $request->user()->id,
            403
        );
    }

    protected function authorizeSession(Request $request, ScreeningSession $session): void
    {
        abort_unless(
            $request->user()->isAdmin() || $session->recruiter_id === $request->user()->id,
            403
        );
    }
}
