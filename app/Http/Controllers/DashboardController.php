<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function home(): View
    {
        $featuredJobs = JobPosting::query()
            ->with('company')
            ->active()
            ->featured()
            ->latest('published_at')
            ->take(3)
            ->get();

        if ($featuredJobs->isEmpty()) {
            $featuredJobs = JobPosting::query()
                ->with('company')
                ->active()
                ->latest('published_at')
                ->take(3)
                ->get();
        }

        return view('home.index', compact('featuredJobs'));
    }

    public function student(): View
    {
        $user = auth()->user();
        $profile = $user->profile;

        $applications = $user->applications()
            ->with('jobPosting.company')
            ->latest('applied_at')
            ->take(5)
            ->get();

        $stats = [
            'applications' => $user->applications()->count(),
            'interviews' => $user->applications()->where('status', ApplicationStatus::Interview)->count(),
            'saved' => 0,
            'views' => rand(10, 50),
        ];

        $recommendedJobs = JobPosting::query()
            ->with('company')
            ->active()
            ->latest('published_at')
            ->take(2)
            ->get();

        return view('student.dashboard', compact('user', 'profile', 'applications', 'stats', 'recommendedJobs'));
    }

    public function recruiter(): View
    {
        $user = auth()->user();

        $stats = [
            'active_jobs' => $user->jobPostings()->where('status', JobStatus::Active)->count(),
            'total_applicants' => Application::whereHas('jobPosting', fn ($q) => $q->where('user_id', $user->id))->count(),
            'interviews' => Application::whereHas('jobPosting', fn ($q) => $q->where('user_id', $user->id))
                ->where('status', ApplicationStatus::Interview)->count(),
            'hires' => Application::whereHas('jobPosting', fn ($q) => $q->where('user_id', $user->id))
                ->where('status', ApplicationStatus::Hired)->count(),
        ];

        $pipeline = collect(ApplicationStatus::cases())->mapWithKeys(function (ApplicationStatus $status) use ($user) {
            $apps = Application::with(['applicant', 'jobPosting'])
                ->where('status', $status)
                ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $user->id))
                ->take(5)
                ->get();

            return [$status->value => [
                'name' => $status->label(),
                'count' => Application::where('status', $status)
                    ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $user->id))
                    ->count(),
                'applications' => $apps,
            ]];
        });

        $upcomingInterviews = Application::with(['applicant', 'jobPosting'])
            ->where('status', ApplicationStatus::Interview)
            ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $user->id))
            ->latest('updated_at')
            ->take(5)
            ->get();

        $activeJobs = $user->jobPostings()
            ->withCount('applications')
            ->where('status', JobStatus::Active)
            ->latest()
            ->take(5)
            ->get();

        return view('recruiter.dashboard', compact('stats', 'pipeline', 'upcomingInterviews', 'activeJobs'));
    }

    public function admin(): View
    {
        $stats = [
            'users' => User::count(),
            'active_jobs' => JobPosting::where('status', JobStatus::Active)->count(),
            'applications' => Application::count(),
            'growth' => '+12%',
        ];

        $recentUsers = User::latest()->take(10)->get();

        return view('admin.index', compact('stats', 'recentUsers'));
    }

    public function talentPool(Request $request): View
    {
        $candidates = User::query()
            ->where('role', 'student')
            ->with('profile')
            ->when($request->q, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(12);

        return view('recruiter.talent-pool', compact('candidates'));
    }
}
