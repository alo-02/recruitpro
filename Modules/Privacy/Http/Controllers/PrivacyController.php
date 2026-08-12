<?php

namespace Modules\Privacy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivacyController extends Controller
{
    public function index(): View
    {
        return view('privacy.index', ['user' => auth()->user()]);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user()->load([
            'profile',
            'company',
            'applications.jobPosting.company',
            'savedJobs.jobPosting',
            'jobAlerts',
            'interviewsAsCandidate.application.jobPosting',
            'messages',
        ]);

        ActivityLogger::log('privacy.export', $user);

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'profile' => $user->profile,
            'company' => $user->company,
            'applications' => $user->applications,
            'saved_jobs' => $user->savedJobs,
            'job_alerts' => $user->jobAlerts,
            'interviews' => $user->interviewsAsCandidate,
            'messages' => $user->messages->map(fn ($m) => [
                'id' => $m->id,
                'conversation_id' => $m->conversation_id,
                'body' => $m->body,
                'created_at' => $m->created_at?->toIso8601String(),
            ]),
        ];

        $filename = 'recruitpro-data-export-'.$user->id.'-'.now()->format('YmdHis').'.json';

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required'],
            'confirm' => ['required', 'in:DELETE'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password is incorrect.']);
        }

        ActivityLogger::log('privacy.account_deleted', $user, ['email' => $user->email]);

        if ($user->profile?->resume_path) {
            Storage::disk('public')->delete($user->profile->resume_path);
        }
        if ($user->profile?->avatar_path) {
            Storage::disk('public')->delete($user->profile->avatar_path);
        }
        if ($user->company?->logo_path) {
            Storage::disk('public')->delete($user->company->logo_path);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->tokens()->delete();
        $user->delete();

        return redirect()->route('home')->with('success', 'Your account and personal data have been deleted.');
    }
}
