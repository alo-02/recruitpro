<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Http\Requests\StoreJobPostingRequest;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JobPostingController extends Controller
{
    public function index(Request $request): View
    {
        $query = JobPosting::query()
            ->with('company')
            ->active()
            ->latest('published_at');

        if ($keyword = $request->get('q')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhereHas('company', fn ($c) => $c->where('name', 'like', "%{$keyword}%"));
            });
        }

        if ($location = $request->get('location')) {
            $query->where('location', 'like', "%{$location}%");
        }

        if ($types = $request->get('job_type')) {
            $query->whereIn('type', (array) $types);
        }

        if ($locationTypes = $request->get('location_type')) {
            $query->whereIn('location_type', (array) $locationTypes);
        }

        $jobs = $query->paginate(10)->withQueryString();

        return view('jobs.search', compact('jobs'));
    }

    public function show(JobPosting $job): View
    {
        $job->load(['company', 'recruiter']);
        $hasApplied = auth()->check()
            && auth()->user()->applications()->where('job_posting_id', $job->id)->exists();

        return view('jobs.show', compact('job', 'hasApplied'));
    }

    public function create(): View
    {
        return view('jobs.post');
    }

    public function store(StoreJobPostingRequest $request): RedirectResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return back()->withErrors(['company' => 'Please set up your company profile first.']);
        }

        $slug = Str::slug($request->title);
        $original = $slug;
        $counter = 1;
        while (JobPosting::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$counter++;
        }

        $skills = $request->skills
            ? array_map('trim', explode(',', $request->skills))
            : [];

        JobPosting::create([
            'user_id' => $request->user()->id,
            'company_id' => $company->id,
            'title' => $request->title,
            'slug' => $slug,
            'type' => $request->type,
            'location_type' => $request->location_type,
            'location' => $request->location,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'description' => $request->description,
            'skills' => $skills,
            'status' => $request->status ?? JobStatus::Active,
            'published_at' => now(),
        ]);

        return redirect()->route('jobs.manage')->with('success', 'Job posted successfully!');
    }

    public function manage(Request $request): View
    {
        $jobs = JobPosting::query()
            ->with('company')
            ->withCount('applications')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('jobs.manage', compact('jobs'));
    }

    public function close(JobPosting $job): RedirectResponse
    {
        $this->authorize('update', $job);
        $job->update(['status' => JobStatus::Closed]);

        return back()->with('success', 'Job listing closed.');
    }
}
