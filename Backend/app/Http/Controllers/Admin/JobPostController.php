<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreJobRequest;
use App\Http\Requests\Admin\UpdateJobRequest;
use App\Http\Resources\JobPostResource;
use App\Models\JobPost;
use App\Models\User;
use App\Services\JobManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobPostController extends Controller
{
    public function store(StoreJobRequest $request, JobManagementService $jobs): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user('api');
        $job = $jobs->save(null, $request->validated(), $actor);

        return response()->json(['data' => (new JobPostResource($this->details($job)))->resolve($request)], 201);
    }

    public function update(UpdateJobRequest $request, int $jobPost, JobManagementService $jobs): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user('api');
        $job = JobPost::query()->findOrFail($jobPost);
        $job = $jobs->save($job, $request->validated(), $actor);

        return response()->json(['data' => (new JobPostResource($this->details($job)))->resolve($request)]);
    }

    public function show(int $jobPost, Request $request): JsonResponse
    {
        $job = JobPost::query()->findOrFail($jobPost);

        return response()->json(['data' => (new JobPostResource($this->details($job)))->resolve($request)]);
    }

    private function details(JobPost $job): JobPost
    {
        return $job->load(['company', 'requiredSkills', 'preferredSkills']);
    }
}
