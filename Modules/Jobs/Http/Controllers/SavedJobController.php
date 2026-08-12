<?php

namespace Modules\Jobs\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobAlert;
use App\Models\JobPosting;
use App\Models\SavedJob;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedJobController extends Controller
{
    public function index(Request $request): View
    {
        $saved = $request->user()
            ->savedJobs()
            ->with('jobPosting.company')
            ->latest()
            ->paginate(15);

        return view('jobs.saved', compact('saved'));
    }

    public function store(Request $request, JobPosting $job): RedirectResponse
    {
        SavedJob::firstOrCreate([
            'user_id' => $request->user()->id,
            'job_posting_id' => $job->id,
        ]);

        ActivityLogger::log('job.saved', $job);

        return back()->with('success', 'Job saved.');
    }

    public function destroy(Request $request, JobPosting $job): RedirectResponse
    {
        SavedJob::where('user_id', $request->user()->id)
            ->where('job_posting_id', $job->id)
            ->delete();

        return back()->with('success', 'Job removed from saved list.');
    }

    public function alerts(Request $request): View
    {
        $alerts = $request->user()->jobAlerts()->latest()->get();

        return view('jobs.alerts', compact('alerts'));
    }

    public function storeAlert(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'keywords' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'job_type' => ['nullable', 'in:internship,full-time,contract'],
        ]);

        $request->user()->jobAlerts()->create([
            ...$data,
            'is_active' => true,
        ]);

        return back()->with('success', 'Job alert created.');
    }

    public function destroyAlert(Request $request, JobAlert $alert): RedirectResponse
    {
        abort_unless($alert->user_id === $request->user()->id, 403);
        $alert->delete();

        return back()->with('success', 'Alert deleted.');
    }
}
