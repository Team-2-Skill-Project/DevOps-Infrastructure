<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IngestionRunListRequest;
use App\Http\Resources\IngestionRunResource;
use App\Models\JobSource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IngestionRunController extends Controller
{
    public function index(IngestionRunListRequest $request, JobSource $jobSource): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $query = $jobSource->runs()->orderByDesc('started_at')->orderByDesc('id');
        foreach (['status', 'trigger_type'] as $field) {
            if (array_key_exists($field, $filters)) {
                $query->where($field, $filters[$field]);
            }
        }

        return IngestionRunResource::collection($query->paginate($filters['per_page'] ?? 15)->withQueryString());
    }
}
