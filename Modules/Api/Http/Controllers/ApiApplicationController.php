<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isStudent()) {
            $apps = $user->applications()
                ->with(['jobPosting.company:id,name'])
                ->latest('applied_at')
                ->paginate(20);
        } else {
            $apps = Application::query()
                ->with(['applicant:id,name,email', 'jobPosting:id,title,slug'])
                ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $user->id))
                ->latest('applied_at')
                ->paginate(20);
        }

        return response()->json($apps);
    }

    public function show(Request $request, Application $application): JsonResponse
    {
        $user = $request->user();
        $allowed = $application->user_id === $user->id
            || $application->jobPosting->user_id === $user->id
            || $user->isAdmin();

        abort_unless($allowed, 403);

        $application->load(['applicant.profile', 'jobPosting.company']);

        return response()->json($application);
    }
}
