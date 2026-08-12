<?php

namespace Modules\Applications\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\JobPosting;
use App\Notifications\ApplicationStatusChanged;
use App\Notifications\NewApplicationReceived;
use App\Services\ActivityLogger;
use App\Services\AtsCvChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(protected AtsCvChecker $ats) {}

    public function index(Request $request): View
    {
        $applications = $request->user()
            ->applications()
            ->with(['jobPosting.company'])
            ->latest('applied_at')
            ->paginate(15);

        if ($applications->isEmpty()) {
            return view('applications.empty');
        }

        return view('applications.tracker', compact('applications'));
    }

    public function store(StoreApplicationRequest $request, JobPosting $job): RedirectResponse
    {
        if (! $job->isOpen()) {
            return back()->withErrors(['application' => 'This job is no longer accepting applications.']);
        }

        if ($request->user()->applications()->where('job_posting_id', $job->id)->exists()) {
            return back()->withErrors(['application' => 'You have already applied to this job.']);
        }

        $application = Application::create([
            'user_id' => $request->user()->id,
            'job_posting_id' => $job->id,
            'status' => ApplicationStatus::Applied,
            'cover_letter' => $request->cover_letter,
            'applied_at' => now(),
            'match_score' => 0,
        ]);

        $this->ats->applyToApplication($application);

        ActivityLogger::log('application.submitted', $application);
        $job->recruiter->notify(new NewApplicationReceived($application->load(['applicant', 'jobPosting'])));

        return redirect()->route('applications.success', $job);
    }

    public function success(JobPosting $job): View
    {
        return view('applications.success', compact('job'));
    }

    public function withdraw(Request $request, Application $application): RedirectResponse
    {
        abort_unless($application->user_id === $request->user()->id, 403);

        if (! $application->status->canWithdraw()) {
            return back()->withErrors(['application' => 'This application can no longer be withdrawn.']);
        }

        $application->update([
            'status' => ApplicationStatus::Withdrawn,
            'withdrawn_at' => now(),
        ]);

        ActivityLogger::log('application.withdrawn', $application);

        return back()->with('success', 'Application withdrawn.');
    }

    public function recruiterIndex(Request $request): View
    {
        $jobs = $request->user()->jobPostings()->latest()->get(['id', 'title', 'ats_keywords', 'ats_min_score']);

        $selectedJob = null;
        if ($jobId = $request->get('job_id')) {
            $selectedJob = $jobs->firstWhere('id', (int) $jobId);
        }

        $minScore = $request->filled('min_score') ? (int) $request->get('min_score') : null;

        $query = Application::query()
            ->with(['applicant.profile', 'jobPosting'])
            ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $request->user()->id))
            ->where('status', '!=', ApplicationStatus::Withdrawn);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($jobId) {
            $query->where('job_posting_id', $jobId);
        }

        if ($minScore !== null && $minScore > 0) {
            $query->where('match_score', '>=', $minScore);
        } elseif ($request->boolean('ats_pass')) {
            $threshold = $selectedJob?->ats_min_score ?? 50;
            $query->where('match_score', '>=', $threshold);
        }

        if ($keyword = trim((string) $request->get('keyword'))) {
            $query->where(function ($q) use ($keyword) {
                $q->where('ats_matched_keywords', 'like', '%"'.$keyword.'"%')
                    ->orWhereHas('applicant.profile', function ($p) use ($keyword) {
                        $p->where('resume_text', 'like', "%{$keyword}%")
                            ->orWhere('headline', 'like', "%{$keyword}%")
                            ->orWhere('bio', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('applicant', fn ($u) => $u->where('name', 'like', "%{$keyword}%"));
            });
        }

        $sort = $request->get('sort', 'ats');
        $query = match ($sort) {
            'newest' => $query->latest('applied_at'),
            'oldest' => $query->oldest('applied_at'),
            default => $query->orderByDesc('match_score')->orderByDesc('applied_at'),
        };

        $applications = $query->paginate(20)->withQueryString();

        return view('applications.recruiter-index', compact('applications', 'jobs', 'selectedJob', 'minScore'));
    }

    public function show(Request $request, Application $application): View
    {
        $application->load(['applicant.profile', 'jobPosting.company', 'interviews']);

        $isOwner = $application->jobPosting->user_id === $request->user()->id;
        $isApplicant = $application->user_id === $request->user()->id;

        abort_unless($isOwner || $isApplicant || $request->user()->isAdmin(), 403);

        return view('applications.show', compact('application', 'isOwner'));
    }

    public function rescanAts(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);
        $this->ats->applyToApplication($application);
        ActivityLogger::log('application.ats_rescanned', $application);

        return back()->with('success', 'ATS CV scan refreshed.');
    }

    public function rescanJobAts(Request $request, JobPosting $job): RedirectResponse
    {
        abort_unless($request->user()->isAdmin() || $job->user_id === $request->user()->id, 403);

        $count = 0;
        $job->applications()->with('applicant.profile')->each(function (Application $application) use (&$count) {
            $this->ats->applyToApplication($application);
            $count++;
        });

        return back()->with('success', "ATS rescanned {$count} applications for this job.");
    }

    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $request->validate([
            'status' => ['required', 'in:'.implode(',', array_column(ApplicationStatus::cases(), 'value'))],
            'recruiter_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $application->update([
            'status' => $request->status,
            'recruiter_notes' => $request->recruiter_notes ?? $application->recruiter_notes,
        ]);

        $application->applicant->notify(new ApplicationStatusChanged($application->fresh(['jobPosting.company'])));
        ActivityLogger::log('application.status_updated', $application, ['status' => $request->status]);

        return back()->with('success', 'Application status updated.');
    }

    public function downloadResume(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $path = $application->applicant->profile?->resume_path;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        if ($request->boolean('signed_redirect')) {
            return redirect()->away(
                URL::temporarySignedRoute(
                    'applications.resume.signed',
                    now()->addMinutes(10),
                    ['application' => $application->id]
                )
            );
        }

        return Storage::disk('public')->download($path);
    }

    public function signedResume(Request $request, Application $application)
    {
        abort_unless($request->hasValidSignature(), 401);

        $path = $application->applicant->profile?->resume_path;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path);
    }

    public function updatePipeline(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $request->validate([
            'status' => ['required', 'in:'.implode(',', array_column(ApplicationStatus::cases(), 'value'))],
        ]);

        $application->update(['status' => $request->status]);
        $application->applicant->notify(new ApplicationStatusChanged($application->fresh(['jobPosting.company'])));
        ActivityLogger::log('application.pipeline_moved', $application, ['status' => $request->status]);

        return response()->json([
            'ok' => true,
            'status' => $application->status->value,
            'label' => $application->status->label(),
        ]);
    }
}
