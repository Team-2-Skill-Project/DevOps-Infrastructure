<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavedJobListRequest;
use App\Http\Resources\JobFeedResource;
use App\Models\JobPost;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SavedJobController extends Controller
{
    public function store(Request $request, int $jobPost): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');
        $job = JobPost::query()->find($jobPost);

        if ($job === null) {
            return response()->json(['message' => __('jobs.not_found')], 404);
        }

        try {
            $savedJob = SavedJob::query()->firstOrCreate([
                'user_id' => $user->id,
                'job_post_id' => $job->id,
            ]);
        } catch (UniqueConstraintViolationException) {
            $savedJob = SavedJob::query()
                ->where('user_id', $user->id)
                ->where('job_post_id', $job->id)
                ->firstOrFail();
        }

        return response()->json([
            'data' => ['job_id' => $job->id, 'is_saved' => true],
        ], $savedJob->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, int $jobPost): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');
        $job = JobPost::query()->find($jobPost);

        if ($job === null) {
            return response()->json(['message' => __('jobs.not_found')], 404);
        }

        SavedJob::query()->where('user_id', $user->id)->where('job_post_id', $job->id)->delete();

        return response()->json(['data' => ['job_id' => $job->id, 'is_saved' => false]]);
    }

    public function index(SavedJobListRequest $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user('api');
        $userId = (int) $user->getKey();
        $savedAt = SavedJob::query()
            ->select('created_at')
            ->whereColumn('job_post_id', 'job_posts.id')
            ->where('user_id', $userId)
            ->limit(1);
        $savedId = SavedJob::query()
            ->select('id')
            ->whereColumn('job_post_id', 'job_posts.id')
            ->where('user_id', $userId)
            ->limit(1);

        $jobs = JobPost::query()
            ->whereHas('savedJobs', fn ($query) => $query->where('user_id', $userId))
            ->with([
                'company:id,name,logo_url,is_verified,is_active',
                'requiredSkills:id,name',
                'preferredSkills:id,name',
            ])
            ->select('job_posts.*')
            ->selectSub($savedAt, 'saved_at')
            ->selectSub($savedId, 'saved_job_id')
            ->withExists(['savedJobs as is_saved' => fn ($query) => $query->where('user_id', $userId)])
            ->orderByDesc('saved_at')
            ->orderByDesc('saved_job_id')
            ->paginate($request->validated('per_page', 15));

        return JobFeedResource::collection($jobs);
    }
}
