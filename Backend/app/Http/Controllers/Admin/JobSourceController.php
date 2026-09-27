<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\JobSourceListRequest;
use App\Http\Requests\Admin\JobSourceSaveRequest;
use App\Http\Resources\JobSourceResource;
use App\Models\JobSource;
use App\Models\User;
use App\Services\JobSourceHealthService;
use App\Services\JobSourceManagementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobSourceController extends Controller
{
    public function index(JobSourceListRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $query = JobSource::query()->latest('id');

        foreach (['is_active', 'source_type', 'collection_method', 'schedule_enabled'] as $field) {
            if (array_key_exists($field, $filters)) {
                $query->where($field, $filters[$field]);
            }
        }

        if (! empty($filters['search'])) {
            $query->where(function (Builder $query) use ($filters): void {
                $query->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('slug', 'like', '%'.$filters['search'].'%');
            });
        }

        return JobSourceResource::collection($query->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function store(JobSourceSaveRequest $request, JobSourceManagementService $sources): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user('api');
        $source = $sources->save(null, $request->validated(), $actor);

        return response()->json(['data' => (new JobSourceResource($source->refresh()))->resolve($request)], 201);
    }

    public function show(JobSource $jobSource, JobSourceHealthService $health): JobSourceResource
    {
        return (new JobSourceResource($jobSource))->additional(['health' => $health->forSource($jobSource)]);
    }

    public function update(JobSourceSaveRequest $request, JobSource $jobSource, JobSourceManagementService $sources): JobSourceResource
    {
        /** @var User $actor */
        $actor = $request->user('api');

        return new JobSourceResource($sources->save($jobSource, $request->validated(), $actor)->refresh());
    }
}
