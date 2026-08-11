<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
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

        $skills = $request->skills
            ? array_map('trim', explode(',', $request->skills))
            : [];

        $education = array_filter([
            'school' => $request->school,
            'degree' => $request->degree,
            'graduation_year' => $request->graduation_year,
            'gpa' => $request->gpa,
        ]);

        $data = [
            'headline' => $request->headline,
            'bio' => $request->bio,
            'phone' => $request->phone,
            'location' => $request->location,
            'skills' => $skills,
            'education' => $education,
        ];

        if ($request->hasFile('resume')) {
            if ($profile->resume_path) {
                Storage::disk('public')->delete($profile->resume_path);
            }
            $data['resume_path'] = $request->file('resume')->store('resumes', 'public');
        }

        $profile->fill($data);
        $profile->completeness = $profile->calculateCompleteness();
        $profile->save();

        return back()->with('success', 'Profile saved successfully!');
    }
}
