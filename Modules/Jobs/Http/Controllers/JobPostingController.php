<?php

namespace Modules\Jobs\Http\Controllers;

use App\Enums\JobCategory;
use App\Enums\JobStatus;
use App\Enums\ModerationStatus;
use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Jobs\Http\Requests\StoreJobPostingRequest;
use Modules\Jobs\Http\Requests\UpdateJobPostingRequest;

class JobPostingController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->get('q');

        if ($keyword && config('scout.driver') !== 'null') {
            try {
                $builder = JobPosting::search($keyword)->query(function ($query) use ($request) {
                    $query->with('company')->active();

                    if ($location = $request->get('location')) {
                        $query->where('location', 'like', "%{$location}%");
                    }
                    if ($types = $request->get('job_type')) {
                        $query->whereIn('type', (array) $types);
                    }
                    if ($categories = $request->get('category')) {
                        $query->whereIn('category', (array) $categories);
                    }
                    if ($locationTypes = $request->get('location_type')) {
                        $query->whereIn('location_type', (array) $locationTypes);
                    }
                });

                $jobs = $builder->paginate(10)->withQueryString();

                return view('jobs.search', compact('jobs'));
            } catch (\Throwable) {
                // Fall back to SQL search if Meilisearch/Scout is unavailable.
            }
        }

        $query = JobPosting::query()
            ->with('company')
            ->active()
            ->latest('published_at');

        if ($keyword) {
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

        if ($categories = $request->get('category')) {
            $query->whereIn('category', (array) $categories);
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

        $match = null;
        if (auth()->check() && auth()->user()->isStudent()) {
            $profile = auth()->user()->profile;
            $match = app(\App\Services\JobMatchService::class)->explain(
                $job,
                $profile?->skills ?? [],
                $profile?->resume_text,
                $profile?->headline,
                $profile?->bio
            );
        }

        $isFollowing = auth()->check()
            && auth()->user()->followedCompanies()->where('company_id', $job->company_id)->exists();

        $reviews = $job->company->reviews()->with('user')->where('is_approved', true)->latest()->take(5)->get();

        return view('jobs.show', compact('job', 'hasApplied', 'match', 'isFollowing', 'reviews'));
    }

    public function create(): View
    {
        $company = auth()->user()->company;

        return view('jobs.post', compact('company'));
    }

    public function store(StoreJobPostingRequest $request): RedirectResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return back()->withErrors(['company' => 'Please set up your company profile first.']);
        }

        if (! $company->isApproved() && ! $request->user()->isAdmin()) {
            return back()->withErrors(['company' => 'Your company must be approved by admin before posting jobs.']);
        }

        $slug = $this->uniqueSlug($request->title);
        $skills = $request->skills ? array_map('trim', explode(',', $request->skills)) : [];
        $atsKeywords = $request->ats_keywords ? array_map('trim', explode(',', $request->ats_keywords)) : [];
        $status = JobStatus::tryFrom($request->status ?? 'active') ?? JobStatus::Active;

        $job = JobPosting::create([
            'user_id' => $request->user()->id,
            'company_id' => $company->id,
            'title' => $request->title,
            'slug' => $slug,
            'type' => $request->type,
            'category' => $request->category ?? JobCategory::Technology->value,
            'location_type' => $request->location_type,
            'location' => $request->location,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'skills' => $skills ?: $atsKeywords,
            'ats_keywords' => $atsKeywords,
            'ats_min_score' => $request->integer('ats_min_score', 50),
            'deadline' => $request->deadline,
            'status' => $status,
            'moderation_status' => $request->user()->isAdmin()
                ? ModerationStatus::Approved
                : ModerationStatus::Pending,
            'published_at' => $status === JobStatus::Active ? now() : null,
        ]);

        ActivityLogger::log('job.created', $job);

        $msg = $job->moderation_status === ModerationStatus::Pending
            ? 'Job submitted for admin approval.'
            : 'Job posted successfully!';

        return redirect()->route('jobs.manage')->with('success', $msg);
    }

    public function edit(JobPosting $job): View
    {
        $this->authorize('update', $job);

        return view('jobs.edit', compact('job'));
    }

    public function update(UpdateJobPostingRequest $request, JobPosting $job): RedirectResponse
    {
        $this->authorize('update', $job);

        $skills = $request->skills ? array_map('trim', explode(',', $request->skills)) : [];
        $atsKeywords = $request->ats_keywords ? array_map('trim', explode(',', $request->ats_keywords)) : [];
        $status = JobStatus::tryFrom($request->status ?? $job->status->value) ?? $job->status;

        $job->update([
            'title' => $request->title,
            'type' => $request->type,
            'category' => $request->category ?? $job->category?->value ?? JobCategory::Technology->value,
            'location_type' => $request->location_type,
            'location' => $request->location,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'skills' => $skills ?: $atsKeywords,
            'ats_keywords' => $atsKeywords,
            'ats_min_score' => $request->integer('ats_min_score', $job->ats_min_score ?? 50),
            'deadline' => $request->deadline,
            'status' => $status,
            'published_at' => $status === JobStatus::Active ? ($job->published_at ?? now()) : $job->published_at,
            'moderation_status' => $request->user()->isAdmin()
                ? $job->moderation_status
                : ModerationStatus::Pending,
        ]);

        ActivityLogger::log('job.updated', $job);

        return redirect()->route('jobs.manage')->with('success', 'Job updated successfully.');
    }

    public function manage(Request $request): View
    {
        $query = JobPosting::query()->with('company')->withCount('applications');

        if ($request->user()->isAdmin()) {
            $query->latest();
        } else {
            $query->where('user_id', $request->user()->id)->latest();
        }

        $jobs = $query->paginate(15);

        return view('jobs.manage', compact('jobs'));
    }

    public function close(JobPosting $job): RedirectResponse
    {
        $this->authorize('update', $job);
        $job->update(['status' => JobStatus::Closed]);
        ActivityLogger::log('job.closed', $job);

        return back()->with('success', 'Job listing closed.');
    }

    public function destroy(JobPosting $job): RedirectResponse
    {
        $this->authorize('delete', $job);
        ActivityLogger::log('job.deleted', $job, ['title' => $job->title]);
        $job->delete();

        return back()->with('success', 'Job deleted.');
    }

    protected function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while (JobPosting::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }
}
