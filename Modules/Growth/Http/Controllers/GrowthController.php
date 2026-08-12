<?php

namespace Modules\Growth\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\AbuseReport;
use App\Models\Application;
use App\Models\AssessmentAttempt;
use App\Models\BlogPost;
use App\Models\CandidateBlacklist;
use App\Models\Company;
use App\Models\CompanyFollow;
use App\Models\CompanyMember;
use App\Models\CompanyReview;
use App\Models\FeatureFlag;
use App\Models\HiringStage;
use App\Models\Interview;
use App\Models\InterviewScorecard;
use App\Models\JobOffer;
use App\Models\JobPosting;
use App\Models\PortfolioProject;
use App\Models\PushSubscription;
use App\Models\SkillAssessment;
use App\Models\User;
use App\Notifications\ApplicationStatusChanged;
use App\Services\ActivityLogger;
use App\Services\JobMatchService;
use App\Services\ResumePdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GrowthController extends Controller
{
    public function recommendations(Request $request, JobMatchService $matcher): View
    {
        abort_unless($request->user()->isStudent(), 403);
        $recommendations = $matcher->recommendFor($request->user(), 12);

        return view('recommendations.index', compact('recommendations'));
    }

    public function coverLetterDraft(Request $request, JobPosting $job, JobMatchService $matcher): RedirectResponse
    {
        abort_unless($request->user()->isStudent(), 403);
        $draft = $matcher->draftCoverLetter($request->user(), $job);

        return back()->with('cover_letter_draft', $draft);
    }

    public function portfolio(Request $request): View
    {
        abort_unless($request->user()->isStudent(), 403);
        $projects = $request->user()->portfolioProjects;

        return view('portfolio.index', compact('projects'));
    }

    public function storeProject(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isStudent(), 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'url' => ['nullable', 'url', 'max:500'],
            'repo_url' => ['nullable', 'url', 'max:500'],
            'tech_stack' => ['nullable', 'string', 'max:500'],
        ]);

        PortfolioProject::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'url' => $data['url'] ?? null,
            'repo_url' => $data['repo_url'] ?? null,
            'tech_stack' => isset($data['tech_stack']) ? array_map('trim', explode(',', $data['tech_stack'])) : [],
            'sort_order' => $request->user()->portfolioProjects()->count(),
        ]);

        return back()->with('success', 'Project added to portfolio.');
    }

    public function destroyProject(Request $request, PortfolioProject $project): RedirectResponse
    {
        abort_unless($project->user_id === $request->user()->id, 403);
        $project->delete();

        return back()->with('success', 'Project removed.');
    }

    public function resumePdf(Request $request, ResumePdfService $pdf): Response
    {
        abort_unless($request->user()->isStudent(), 403);

        return $pdf->download($request->user());
    }

    public function assessments(): View
    {
        $assessments = SkillAssessment::query()->where('is_active', true)->withCount('questions')->get();

        return view('assessments.index', compact('assessments'));
    }

    public function showAssessment(Request $request, SkillAssessment $assessment): View
    {
        $assessment->load('questions');
        $attempt = AssessmentAttempt::query()
            ->where('skill_assessment_id', $assessment->id)
            ->where('user_id', $request->user()->id)
            ->latest()
            ->first();

        return view('assessments.show', compact('assessment', 'attempt'));
    }

    public function submitAssessment(Request $request, SkillAssessment $assessment): RedirectResponse
    {
        abort_unless($request->user()->isStudent(), 403);
        $assessment->load('questions');
        $answers = $request->input('answers', []);
        $score = 0;
        $total = max(1, $assessment->questions->sum('points'));

        foreach ($assessment->questions as $question) {
            $chosen = (int) ($answers[$question->id] ?? -1);
            if ($chosen === (int) $question->correct_index) {
                $score += $question->points;
            }
        }

        $percent = (int) round(($score / $total) * 100);
        AssessmentAttempt::create([
            'skill_assessment_id' => $assessment->id,
            'user_id' => $request->user()->id,
            'score' => $percent,
            'passed' => $percent >= $assessment->pass_score,
            'answers' => $answers,
            'completed_at' => now(),
        ]);

        return redirect()->route('assessments.show', $assessment)
            ->with('success', "Assessment scored {$percent}%.");
    }

    public function followCompany(Request $request, Company $company): RedirectResponse
    {
        abort_unless($request->user()->isStudent(), 403);
        CompanyFollow::firstOrCreate([
            'user_id' => $request->user()->id,
            'company_id' => $company->id,
        ]);

        return back()->with('success', 'Company followed.');
    }

    public function unfollowCompany(Request $request, Company $company): RedirectResponse
    {
        CompanyFollow::where('user_id', $request->user()->id)->where('company_id', $company->id)->delete();

        return back()->with('success', 'Unfollowed company.');
    }

    public function storeReview(Request $request, Company $company): RedirectResponse
    {
        abort_unless($request->user()->isStudent(), 403);
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        CompanyReview::updateOrCreate(
            ['user_id' => $request->user()->id, 'company_id' => $company->id],
            [...$data, 'is_approved' => true]
        );

        return back()->with('success', 'Review submitted.');
    }

    public function team(Request $request): View
    {
        $company = $request->user()->company;
        abort_unless($company, 404);
        $members = $company->members()->with('user')->get();

        return view('team.index', compact('company', 'members'));
    }

    public function inviteMember(Request $request): RedirectResponse
    {
        $company = $request->user()->company;
        abort_unless($company && $company->user_id === $request->user()->id, 403);

        if ($company->remainingSeats() <= 0) {
            return back()->withErrors(['email' => 'No team seats left. Upgrade your plan.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'seat_role' => ['required', 'in:recruiter,viewer'],
        ]);

        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make(Str::random(16)),
                'role' => 'recruiter',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        \Spatie\Permission\Models\Role::findOrCreate('recruiter');
        $user->syncRoles(['recruiter']);

        CompanyMember::updateOrCreate(
            ['company_id' => $company->id, 'user_id' => $user->id],
            [
                'seat_role' => $data['seat_role'],
                'invited_at' => now(),
                'accepted_at' => now(),
            ]
        );

        return back()->with('success', 'Team member invited.');
    }

    public function stages(Request $request): View
    {
        $company = $request->user()->company;
        abort_unless($company, 404);
        $stages = $company->hiringStages;

        return view('stages.index', compact('company', 'stages'));
    }

    public function storeStage(Request $request): RedirectResponse
    {
        $company = $request->user()->company;
        abort_unless($company, 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        HiringStage::create([
            'company_id' => $company->id,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'position' => $company->hiringStages()->count(),
            'color' => $data['color'] ?? '#64748b',
        ]);

        return back()->with('success', 'Hiring stage added.');
    }

    public function bulkApplications(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'application_ids' => ['required', 'array', 'min:1'],
            'application_ids.*' => ['integer', 'exists:applications,id'],
            'action' => ['required', 'in:shortlist,reject,tag,stage'],
            'tag' => ['nullable', 'string', 'max:50'],
            'hiring_stage_id' => ['nullable', 'exists:hiring_stages,id'],
        ]);

        $apps = Application::with('jobPosting')
            ->whereIn('id', $data['application_ids'])
            ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $request->user()->id))
            ->get();

        foreach ($apps as $app) {
            match ($data['action']) {
                'shortlist' => $app->update(['status' => ApplicationStatus::Screening]),
                'reject' => $app->update(['status' => ApplicationStatus::Rejected]),
                'tag' => $app->update([
                    'recruiter_tags' => array_values(array_unique(array_filter([
                        ...($app->recruiter_tags ?? []),
                        $data['tag'] ?? null,
                    ]))),
                ]),
                'stage' => $app->update(['hiring_stage_id' => $data['hiring_stage_id']]),
            };

            if (in_array($data['action'], ['shortlist', 'reject'], true)) {
                $app->applicant->notify(new ApplicationStatusChanged($app->fresh(['jobPosting.company'])));
            }
        }

        ActivityLogger::log('applications.bulk_updated', null, [
            'action' => $data['action'],
            'count' => $apps->count(),
        ]);

        return back()->with('success', 'Bulk action applied to '.$apps->count().' applications.');
    }

    public function storeScorecard(Request $request, Interview $interview): RedirectResponse
    {
        abort_unless(
            $request->user()->isAdmin() || $interview->recruiter_id === $request->user()->id,
            403
        );

        $data = $request->validate([
            'technical' => ['nullable', 'integer', 'min:1', 'max:5'],
            'communication' => ['nullable', 'integer', 'min:1', 'max:5'],
            'culture_fit' => ['nullable', 'integer', 'min:1', 'max:5'],
            'overall' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:5000'],
            'recommendation' => ['required', 'in:hire,maybe,no'],
        ]);

        InterviewScorecard::updateOrCreate(
            ['interview_id' => $interview->id, 'reviewer_id' => $request->user()->id],
            $data
        );

        return back()->with('success', 'Scorecard saved.');
    }

    public function interviewIcs(Interview $interview): Response
    {
        abort_unless(
            auth()->id() === $interview->candidate_id
            || auth()->id() === $interview->recruiter_id
            || auth()->user()?->isAdmin(),
            403
        );

        return response($interview->toIcs(), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="interview-'.$interview->id.'.ics"',
        ]);
    }

    public function offers(Request $request): View
    {
        $user = $request->user();
        $offers = JobOffer::with(['application.jobPosting', 'application.applicant'])
            ->when($user->isStudent(), fn ($q) => $q->whereHas('application', fn ($a) => $a->where('user_id', $user->id)))
            ->when($user->isRecruiter(), function ($q) use ($user) {
                $q->where(function ($inner) use ($user) {
                    $inner->where('created_by', $user->id)
                        ->orWhereHas('application.jobPosting', fn ($j) => $j->where('user_id', $user->id));
                });
            })
            ->latest()
            ->paginate(20);

        $applications = collect();
        if ($user->isRecruiter()) {
            $applications = Application::with(['applicant', 'jobPosting'])
                ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $user->id))
                ->whereIn('status', [ApplicationStatus::Interview, ApplicationStatus::Offer, ApplicationStatus::Screening])
                ->latest()
                ->get();
        }

        return view('offers.index', compact('offers', 'applications'));
    }

    public function storeOffer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
            'title' => ['required', 'string', 'max:255'],
            'salary' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $application = Application::with('jobPosting')->findOrFail($data['application_id']);
        abort_unless($application->jobPosting->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $offer = JobOffer::create([
            ...$data,
            'created_by' => $request->user()->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $application->update(['status' => ApplicationStatus::Offer]);
        $application->applicant->notify(new ApplicationStatusChanged($application->fresh(['jobPosting.company'])));
        ActivityLogger::log('offer.sent', $offer);

        return back()->with('success', 'Offer sent to candidate.');
    }

    public function respondOffer(Request $request, JobOffer $offer): RedirectResponse
    {
        abort_unless($offer->application->user_id === $request->user()->id, 403);
        $data = $request->validate([
            'decision' => ['required', 'in:accepted,declined'],
            'candidate_signature' => ['required_if:decision,accepted', 'nullable', 'string', 'max:255'],
        ]);

        $offer->update([
            'status' => $data['decision'],
            'candidate_signature' => $data['candidate_signature'] ?? null,
            'responded_at' => now(),
        ]);

        if ($data['decision'] === 'accepted') {
            $offer->application->update(['status' => ApplicationStatus::Hired]);
        }

        return back()->with('success', 'Offer response recorded.');
    }

    public function talentPool(Request $request): View
    {
        $company = $request->user()->company;
        $tag = $request->get('tag');
        $candidates = Application::with(['applicant.profile', 'jobPosting', 'hiringStage'])
            ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($tag, fn ($q) => $q->whereJsonContains('recruiter_tags', $tag))
            ->latest()
            ->paginate(20);

        $blacklist = $company
            ? CandidateBlacklist::with('user')->where('company_id', $company->id)->get()
            : collect();

        return view('talent.pool', compact('candidates', 'blacklist', 'tag'));
    }

    public function blacklist(Request $request): RedirectResponse
    {
        $company = $request->user()->company;
        abort_unless($company, 403);
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        CandidateBlacklist::updateOrCreate(
            ['company_id' => $company->id, 'user_id' => $data['user_id']],
            ['created_by' => $request->user()->id, 'reason' => $data['reason'] ?? null]
        );

        return back()->with('success', 'Candidate blacklisted for your company.');
    }

    public function blogIndex(): View
    {
        $posts = BlogPost::query()->where('is_published', true)->latest('published_at')->paginate(10);

        return view('blog.index', compact('posts'));
    }

    public function blogShow(BlogPost $post): View
    {
        abort_unless($post->is_published || auth()->user()?->isAdmin(), 404);

        return view('blog.show', compact('post'));
    }

    public function adminCms(): View
    {
        $posts = BlogPost::latest()->paginate(20);

        return view('admin.cms.index', compact('posts'));
    }

    public function storePost(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        BlogPost::create([
            'author_id' => $request->user()->id,
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(4),
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'],
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->boolean('is_published') ? now() : null,
        ]);

        return back()->with('success', 'Blog post saved.');
    }

    public function analytics(): View
    {
        $stats = [
            'users' => User::count(),
            'companies' => Company::count(),
            'jobs' => JobPosting::count(),
            'applications' => Application::count(),
            'offers_accepted' => JobOffer::where('status', 'accepted')->count(),
            'assessments_passed' => AssessmentAttempt::where('passed', true)->count(),
        ];

        $funnel = [
            'applied' => Application::where('status', ApplicationStatus::Applied)->count(),
            'screening' => Application::where('status', ApplicationStatus::Screening)->count(),
            'interview' => Application::where('status', ApplicationStatus::Interview)->count(),
            'offer' => Application::where('status', ApplicationStatus::Offer)->count(),
            'hired' => Application::where('status', ApplicationStatus::Hired)->count(),
        ];

        return view('admin.analytics.index', compact('stats', 'funnel'));
    }

    public function plans(): View
    {
        $companies = Company::with('owner')->latest()->paginate(20);

        return view('admin.plans.index', compact('companies'));
    }

    public function updatePlan(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', 'in:free,pro,enterprise'],
            'max_team_seats' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $company->update([
            'plan' => $data['plan'],
            'max_team_seats' => $data['max_team_seats'],
            'plan_expires_at' => $data['plan'] === 'free' ? null : now()->addYear(),
        ]);

        return back()->with('success', 'Company plan updated.');
    }

    public function abuseIndex(): View
    {
        $reports = AbuseReport::with(['reporter', 'reportable'])->latest()->paginate(30);

        return view('admin.abuse.index', compact('reports'));
    }

    public function storeAbuse(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reportable_type' => ['required', 'string'],
            'reportable_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:100'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);

        $map = [
            'job' => JobPosting::class,
            'company' => Company::class,
            'user' => User::class,
            'message' => \App\Models\Message::class,
        ];
        $type = $map[$data['reportable_type']] ?? null;
        abort_unless($type, 422);

        AbuseReport::create([
            'reporter_id' => $request->user()->id,
            'reportable_type' => $type,
            'reportable_id' => $data['reportable_id'],
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'status' => 'open',
        ]);

        return back()->with('success', 'Abuse report submitted.');
    }

    public function resolveAbuse(Request $request, AbuseReport $report): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:resolved,dismissed,reviewing']]);
        $report->update([
            'status' => $data['status'],
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Report updated.');
    }

    public function flags(): View
    {
        $flags = FeatureFlag::orderBy('key')->get();

        return view('admin.flags.index', compact('flags'));
    }

    public function toggleFlag(FeatureFlag $flag): RedirectResponse
    {
        $flag->update(['enabled' => ! $flag->enabled]);

        return back()->with('success', 'Feature flag updated.');
    }

    public function auditExport(): Response
    {
        $logs = \App\Models\ActivityLog::with('user')->latest()->limit(2000)->get();
        $csv = "id,action,user,ip,created_at\n";
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s\n",
                $log->id,
                str_replace(',', ' ', $log->action),
                str_replace(',', ' ', $log->user?->email ?? ''),
                $log->ip_address ?? '',
                $log->created_at
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-export-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    public function switchLocale(Request $request): RedirectResponse
    {
        $locale = $request->validate(['locale' => ['required', Rule::in(['en', 'bn'])]])['locale'];
        session(['locale' => $locale]);
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        return back()->with('success', $locale === 'bn' ? 'ভাষা বাংলা করা হয়েছে।' : 'Language set to English.');
    }

    public function storePushSubscription(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:500'],
            'public_key' => ['nullable', 'string', 'max:255'],
            'auth_token' => ['nullable', 'string', 'max:255'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id' => $request->user()->id,
                'public_key' => $data['public_key'] ?? null,
                'auth_token' => $data['auth_token'] ?? null,
                'content_encoding' => 'aesgcm',
            ]
        );

        return back()->with('success', 'Push subscription saved.');
    }
}
