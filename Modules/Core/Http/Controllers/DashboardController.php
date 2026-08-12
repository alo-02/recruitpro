<?php

namespace Modules\Core\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'interviews' => $user->interviewsAsCandidate()->count(),
            'messages' => $user->conversations()->count(),
            'profile' => $profile?->completeness ?? 0,
            'saved' => $user->savedJobs()->count(),
        ];

        $recommendedJobs = app(\App\Services\JobMatchService::class)
            ->recommendFor($user, 4)
            ->pluck('job');

        return view('student.dashboard', compact('user', 'profile', 'applications', 'stats', 'recommendedJobs'));
    }

    public function recruiter(): View
    {
        $user = auth()->user();

        $stats = [
            'active_jobs' => $user->jobPostings()->where('status', JobStatus::Active)->count(),
            'total_applicants' => Application::whereHas('jobPosting', fn ($q) => $q->where('user_id', $user->id))->count(),
            'interviews' => $user->interviewsAsRecruiter()->count(),
            'hires' => Application::whereHas('jobPosting', fn ($q) => $q->where('user_id', $user->id))
                ->where('status', ApplicationStatus::Hired)->count(),
        ];

        $pipeline = collect(ApplicationStatus::cases())
            ->reject(fn ($s) => $s === ApplicationStatus::Withdrawn)
            ->mapWithKeys(function (ApplicationStatus $status) use ($user) {
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

        $upcomingInterviews = $user->interviewsAsRecruiter()
            ->with(['candidate', 'application.jobPosting'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
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

    public function candidateReview(Request $request): View
    {
        $applications = Application::with(['applicant.profile', 'jobPosting'])
            ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $request->user()->id))
            ->whereIn('status', [ApplicationStatus::Screening, ApplicationStatus::Interview])
            ->latest()
            ->paginate(12);

        return view('recruiter.candidate-review', compact('applications'));
    }

    public function settings(): View
    {
        $user = auth()->user();
        $notifications = $user->notifications()->latest()->take(20)->get();

        return view('settings.index', compact('user', 'notifications'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'current_password' => ['nullable', 'required_with:password'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        if ($request->filled('password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->password = $request->password;
        }

        $user->name = $request->name;
        if ($user->email !== $request->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
            $user->sendEmailVerificationNotification();
        }
        $user->save();

        ActivityLogger::log('settings.updated', $user);

        return back()->with('success', 'Settings updated.');
    }

    public function markNotificationRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return back();
    }
}
