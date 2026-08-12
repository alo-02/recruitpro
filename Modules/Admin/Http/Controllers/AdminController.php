<?php

namespace Modules\Admin\Http\Controllers;

use App\Enums\CompanyStatus;
use App\Enums\JobStatus;
use App\Enums\ModerationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Notifications\CompanyStatusChanged;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::count(),
            'students' => User::where('role', UserRole::Student)->count(),
            'recruiters' => User::where('role', UserRole::Recruiter)->count(),
            'active_jobs' => JobPosting::where('status', JobStatus::Active)
                ->where('moderation_status', ModerationStatus::Approved)->count(),
            'applications' => Application::count(),
            'pending_companies' => Company::where('status', CompanyStatus::Pending)->count(),
            'pending_jobs' => JobPosting::where('moderation_status', ModerationStatus::Pending)->count(),
        ];

        $recentUsers = User::latest()->take(8)->get();
        $recentLogs = ActivityLog::with('user')->latest()->take(10)->get();

        return view('admin.index', compact('stats', 'recentUsers', 'recentLogs'));
    }

    public function users(Request $request): View
    {
        $users = User::query()
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->when($request->q, fn ($q, $search) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
        ]);

        $user = User::create([
            ...$data,
            'email_verified_at' => now(),
            'is_active' => true,
            'is_blocked' => false,
        ]);

        \Spatie\Permission\Models\Role::findOrCreate($data['role']);
        $user->syncRoles([$data['role']]);

        ActivityLogger::log('admin.user_created', $user);

        return back()->with('success', 'User created.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
            'is_active' => ['nullable', 'boolean'],
            'is_blocked' => ['nullable', 'boolean'],
            'password' => ['nullable', Password::defaults()],
        ]);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active', $user->is_active),
            'is_blocked' => $request->boolean('is_blocked', $user->is_blocked),
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->syncRoles([$data['role']]);
        ActivityLogger::log('admin.user_updated', $user);

        return back()->with('success', 'User updated.');
    }

    public function blockUser(User $user): RedirectResponse
    {
        abort_if($user->isAdmin() && $user->id === auth()->id(), 403);

        $user->update(['is_blocked' => ! $user->is_blocked]);
        ActivityLogger::log($user->is_blocked ? 'admin.user_blocked' : 'admin.user_unblocked', $user);

        return back()->with('success', $user->is_blocked ? 'User blocked.' : 'User unblocked.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403);

        ActivityLogger::log('admin.user_deleted', $user, ['email' => $user->email]);
        $user->delete();

        return back()->with('success', 'User deleted.');
    }

    public function companies(Request $request): View
    {
        $companies = Company::with('owner')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.companies', compact('companies'));
    }

    public function moderateCompany(Request $request, Company $company): RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $status = CompanyStatus::from($request->status);

        $company->update([
            'status' => $status,
            'approved_by' => auth()->id(),
            'approved_at' => $status === CompanyStatus::Approved ? now() : null,
            'rejection_reason' => $status === CompanyStatus::Rejected ? $request->rejection_reason : null,
        ]);

        $company->owner?->notify(new CompanyStatusChanged($company));
        ActivityLogger::log('admin.company_moderated', $company, ['status' => $status->value]);

        return back()->with('success', 'Company '.$status->label().'.');
    }

    public function jobs(Request $request): View
    {
        $jobs = JobPosting::with(['company', 'recruiter'])
            ->withCount('applications')
            ->when($request->moderation, fn ($q, $m) => $q->where('moderation_status', $m))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.jobs', compact('jobs'));
    }

    public function moderateJob(Request $request, JobPosting $job): RedirectResponse
    {
        $request->validate([
            'moderation_status' => ['required', Rule::in(['approved', 'rejected'])],
        ]);

        $job->update([
            'moderation_status' => $request->moderation_status,
            'published_at' => $request->moderation_status === 'approved'
                ? ($job->published_at ?? now())
                : $job->published_at,
        ]);

        ActivityLogger::log('admin.job_moderated', $job, ['status' => $request->moderation_status]);

        return back()->with('success', 'Job moderation updated.');
    }

    public function reports(): View
    {
        $byStatus = Application::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $byJobType = JobPosting::selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $monthlyApplications = Application::selectRaw("strftime('%Y-%m', applied_at) as month, count(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // MySQL fallback if not sqlite
        if ($monthlyApplications->isEmpty()) {
            try {
                $monthlyApplications = Application::selectRaw("DATE_FORMAT(applied_at, '%Y-%m') as month, count(*) as total")
                    ->groupBy('month')
                    ->orderBy('month')
                    ->pluck('total', 'month');
            } catch (\Throwable) {
                $monthlyApplications = collect();
            }
        }

        $stats = [
            'users' => User::count(),
            'companies' => Company::count(),
            'jobs' => JobPosting::count(),
            'applications' => Application::count(),
            'hires' => Application::where('status', 'hired')->count(),
        ];

        return view('admin.reports', compact('stats', 'byStatus', 'byJobType', 'monthlyApplications'));
    }

    public function exportReport()
    {
        $rows = [
            ['Metric', 'Value'],
            ['Total Users', User::count()],
            ['Students', User::where('role', UserRole::Student)->count()],
            ['Recruiters', User::where('role', UserRole::Recruiter)->count()],
            ['Companies', Company::count()],
            ['Jobs', JobPosting::count()],
            ['Applications', Application::count()],
            ['Hires', Application::where('status', 'hired')->count()],
        ];

        $filename = 'recruitpro-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fputcsv($out, []);
            fputcsv($out, ['Application Status', 'Count']);
            foreach (Application::selectRaw('status, count(*) as total')->groupBy('status')->get() as $row) {
                fputcsv($out, [$row->status, $row->total]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportReportPdf(): Response
    {
        $stats = [
            'users' => User::count(),
            'companies' => Company::count(),
            'jobs' => JobPosting::count(),
            'applications' => Application::count(),
            'hires' => Application::where('status', 'hired')->count(),
            'generated_at' => now()->format('M d, Y g:i A'),
        ];

        $byStatus = Application::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        $byCategory = JobPosting::selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->get();

        $pdf = Pdf::loadView('admin.report-pdf', compact('stats', 'byStatus', 'byCategory'))
            ->setPaper('a4');

        ActivityLogger::log('admin.report_pdf_exported');

        return $pdf->download('recruitpro-report-'.now()->format('Y-m-d').'.pdf');
    }

    public function activityLogs(Request $request): View
    {
        $logs = ActivityLog::with('user')
            ->when($request->action, fn ($q, $action) => $q->where('action', 'like', "%{$action}%"))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity-logs', compact('logs'));
    }

    public function securityEvents(Request $request): View
    {
        $events = SecurityEvent::with('user')
            ->when($request->event_type, fn ($q, $type) => $q->where('event_type', $type))
            ->when($request->severity, fn ($q, $severity) => $q->where('severity', $severity))
            ->when($request->boolean('unresolved'), fn ($q) => $q->where('resolved', false))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $summary = [
            'total' => SecurityEvent::count(),
            'unresolved' => SecurityEvent::where('resolved', false)->count(),
            'critical' => SecurityEvent::where('severity', 'critical')->where('resolved', false)->count(),
            'failed_logins_24h' => SecurityEvent::where('event_type', 'login_failed')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
        ];

        return view('admin.security-events', compact('events', 'summary'));
    }

    public function resolveSecurityEvent(SecurityEvent $event): RedirectResponse
    {
        $event->update(['resolved' => true]);
        ActivityLogger::log('admin.security_event_resolved', $event);

        return back()->with('success', 'Security event marked resolved.');
    }
}
