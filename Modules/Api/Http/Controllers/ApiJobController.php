<?php

namespace Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiJobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $jobs = JobPosting::query()
            ->with('company:id,name,slug,logo_path')
            ->active()
            ->when($request->q, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->latest('published_at')
            ->paginate(min(50, (int) $request->get('per_page', 15)));

        return response()->json($jobs);
    }

    public function show(JobPosting $job): JsonResponse
    {
        $job->load('company:id,name,slug,website,description,logo_path');

        return response()->json($job);
    }
}
