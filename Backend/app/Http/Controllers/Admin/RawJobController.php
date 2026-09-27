<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RawJobListRequest;
use App\Http\Resources\RawJobResource;
use App\Models\JobSource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RawJobController extends Controller
{
    public function index(RawJobListRequest $request, JobSource $jobSource): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $query = $jobSource->rawJobs()->latest('id');
        foreach (['ingestion_run_id', 'extraction_status', 'deduplication_status'] as $field) {
            if (array_key_exists($field, $filters)) {
                $query->where($field, $filters[$field]);
            }
        }

        return RawJobResource::collection($query->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function show(JobSource $jobSource, int $rawJob): RawJobResource
    {
        return new RawJobResource($jobSource->rawJobs()->findOrFail($rawJob));
    }
}
