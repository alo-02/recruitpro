<?php

namespace Modules\Profiles\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ResumeTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Profiles\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    public function __construct(protected ResumeTextExtractor $resumeExtractor) {}

    public function edit(): View
    {
        $user = auth()->user();
        $profile = $user->profile ?? $user->profile()->create();

        return view('profile.builder', compact('user', 'profile'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->profile ?? $user->profile()->create();

        $user->update(['name' => $request->name]);

        $skills = $request->skills ? array_map('trim', explode(',', $request->skills)) : [];

        $education = collect($request->input('education', []))
            ->map(fn ($row) => array_filter([
                'school' => $row['school'] ?? null,
                'degree' => $row['degree'] ?? null,
                'graduation_year' => $row['graduation_year'] ?? null,
                'gpa' => $row['gpa'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''))
            ->filter()
            ->values()
            ->all();

        $experience = collect($request->input('experience', []))
            ->map(fn ($row) => array_filter([
                'title' => $row['title'] ?? null,
                'company' => $row['company'] ?? null,
                'years' => $row['years'] ?? null,
                'details' => $row['details'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''))
            ->filter()
            ->values()
            ->all();

        $data = [
            'headline' => $request->headline,
            'bio' => $request->bio,
            'phone' => $request->phone,
            'location' => $request->location,
            'skills' => $skills,
            'education' => $education,
            'experience' => $experience,
        ];

        if ($request->hasFile('resume')) {
            if ($profile->resume_path) {
                Storage::disk('public')->delete($profile->resume_path);
            }
            $data['resume_path'] = $request->file('resume')->store('resumes', 'public');
            $check = $this->resumeExtractor->validateUploadedPath($data['resume_path']);
            if (! $check['valid']) {
                Storage::disk('public')->delete($data['resume_path']);

                return back()->withErrors(['resume' => $check['error']])->withInput();
            }
            $data['resume_text'] = $this->resumeExtractor->fromStoragePath($data['resume_path']);
        }

        if ($request->hasFile('avatar')) {
            if ($profile->avatar_path) {
                Storage::disk('public')->delete($profile->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->fill($data);
        $profile->completeness = $profile->calculateCompleteness();
        $profile->save();

        ActivityLogger::log('profile.updated', $profile);

        return back()->with('success', 'Profile saved successfully!');
    }

    public function show(User $user): View
    {
        abort_unless($user->isStudent(), 404);
        $user->load('profile');

        return view('profile.show', compact('user'));
    }
}
