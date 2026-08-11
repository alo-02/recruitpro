<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
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
        if ($request->user()->applications()->where('job_posting_id', $job->id)->exists()) {
            return back()->withErrors(['application' => 'You have already applied to this job.']);
        }

        Application::create([
            'user_id' => $request->user()->id,
            'job_posting_id' => $job->id,
            'status' => ApplicationStatus::Applied,
            'cover_letter' => $request->cover_letter,
            'applied_at' => now(),
            'match_score' => rand(75, 98),
        ]);

        return redirect()->route('applications.success', $job);
    }

    public function success(JobPosting $job): View
    {
        return view('applications.success', compact('job'));
    }

    public function recruiterIndex(Request $request): View
    {
        $applications = Application::query()
            ->with(['applicant.profile', 'jobPosting'])
            ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest('applied_at')
            ->paginate(20);

        return view('applications.recruiter-index', compact('applications'));
    }

    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $request->validate([
            'status' => ['required', 'in:'.implode(',', array_column(ApplicationStatus::cases(), 'value'))],
        ]);

        $application->update(['status' => $request->status]);

        return back()->with('success', 'Application status updated.');
    }
}
