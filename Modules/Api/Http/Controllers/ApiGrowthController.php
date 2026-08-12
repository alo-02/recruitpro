<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\JobOffer;
use App\Models\SkillAssessment;
use App\Services\JobMatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiGrowthController extends Controller
{
    public function recommendations(Request $request, JobMatchService $matcher): JsonResponse
    {
        $items = $matcher->recommendFor($request->user(), 10)->map(fn ($row) => [
            'score' => $row['score'],
            'reasons' => $row['reasons'],
            'missing' => $row['missing'],
            'job' => [
                'id' => $row['job']->id,
                'title' => $row['job']->title,
                'slug' => $row['job']->slug,
                'company' => $row['job']->company?->name,
            ],
        ]);

        return response()->json(['data' => $items]);
    }

    public function assessments(): JsonResponse
    {
        return response()->json([
            'data' => SkillAssessment::where('is_active', true)->withCount('questions')->get(),
        ]);
    }

    public function offers(Request $request): JsonResponse
    {
        $offers = JobOffer::with('application.jobPosting')
            ->whereHas('application', fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->get();

        return response()->json(['data' => $offers]);
    }

    public function blog(): JsonResponse
    {
        return response()->json([
            'data' => BlogPost::where('is_published', true)->latest('published_at')->limit(20)->get(),
        ]);
    }
}
