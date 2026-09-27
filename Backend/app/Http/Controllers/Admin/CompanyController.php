<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyListRequest;
use App\Http\Requests\Admin\CompanyMergeRequest;
use App\Http\Requests\Admin\StoreCompanyRequest;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Models\User;
use App\Services\CompanyManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index(CompanyListRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $query = Company::query()->withCount('jobPosts')->latest('id');

        if (! empty($filters['search'])) {
            $search = Str::lower(Str::trim($filters['search']));
            $query->where(function ($query) use ($search): void {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(normalized_name) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('aliases', fn ($query) => $query->whereRaw('LOWER(normalized_alias) LIKE ?', ["%{$search}%"]));
            });
        }

        foreach (['is_verified', 'is_active', 'industry', 'country'] as $field) {
            if (array_key_exists($field, $filters) && $filters[$field] !== null) {
                $query->where($field, $filters[$field]);
            }
        }

        return CompanyResource::collection($query->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function store(StoreCompanyRequest $request, CompanyManagementService $companies): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user('api');
        $company = $companies->save(null, $request->validated(), $actor);

        return response()->json(['data' => (new CompanyResource($company->refresh()))->resolve($request)], 201);
    }

    public function show(int $company, Request $request): JsonResponse
    {
        $company = Company::query()->findOrFail($company);
        $company->load('aliases')->loadCount('jobPosts');

        return response()->json(['data' => (new CompanyResource($company))->resolve($request)]);
    }

    public function update(UpdateCompanyRequest $request, int $company, CompanyManagementService $companies): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user('api');
        $company = Company::query()->findOrFail($company);
        $company = $companies->save($company, $request->validated(), $actor);

        return response()->json(['data' => (new CompanyResource($company->refresh()))->resolve($request)]);
    }

    public function merge(CompanyMergeRequest $request, int $sourceCompany, CompanyManagementService $companies): JsonResponse
    {
        $source = Company::query()->findOrFail($sourceCompany);
        $targetCompanyId = (int) $request->validated('target_company_id');
        $target = Company::query()->findOrFail($targetCompanyId);
        $result = $companies->mergeCompany($source, $target);

        return response()->json([
            'data' => (new CompanyResource($result['company']->load('aliases')->loadCount('jobPosts')))->resolve($request),
            'meta' => $result['meta'],
        ]);
    }
}
