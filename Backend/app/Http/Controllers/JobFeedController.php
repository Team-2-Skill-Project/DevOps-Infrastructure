<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobListRequest;
use App\Http\Resources\JobFeedResource;
use App\Models\JobPost;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class JobFeedController extends Controller
{
    public function __invoke(JobListRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $userId = (int) $request->user('api')->getKey();
        $query = JobPost::query()
            ->active()
            ->notExpired()
            ->whereHas('company', function ($query): void {
                $query->where('is_active', true);
            })
            ->with([
                'company:id,name,logo_url,is_verified',
                'requiredSkills:id,name',
                'preferredSkills:id,name',
            ])
            ->withExists(['savedJobs as is_saved' => fn ($query) => $query->where('user_id', $userId)]);

        $search = null;
        if (! empty($filters['search'])) {
            $search = Str::lower(Str::trim($filters['search']));
            $query->where(function ($query) use ($search): void {
                $like = "%{$search}%";
                $query->whereRaw('LOWER(job_posts.title) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(job_posts.canonical_role) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(job_posts.description) LIKE ?', [$like])
                    ->orWhereHas('company', function ($query) use ($like): void {
                        $query->whereRaw('LOWER(companies.name) LIKE ?', [$like])
                            ->orWhereHas('aliases', fn ($query) => $query->whereRaw('LOWER(normalized_alias) LIKE ?', [$like]));
                    })
                    ->orWhereHas('requiredSkills', function ($query) use ($like): void {
                        $query->whereRaw('LOWER(skills.name) LIKE ?', [$like])
                            ->orWhereHas('aliases', fn ($query) => $query->whereRaw('LOWER(normalized_alias) LIKE ?', [$like]));
                    })
                    ->orWhereHas('preferredSkills', function ($query) use ($like): void {
                        $query->whereRaw('LOWER(skills.name) LIKE ?', [$like])
                            ->orWhereHas('aliases', fn ($query) => $query->whereRaw('LOWER(normalized_alias) LIKE ?', [$like]));
                    });
            });
        }

        foreach (['company_id', 'job_type', 'work_mode', 'employment_type', 'experience_level', 'country', 'state', 'city', 'source', 'application_method'] as $field) {
            if (array_key_exists($field, $filters)) {
                $query->where('job_posts.'.$field, $filters[$field]);
            }
        }

        if (array_key_exists('is_verified_company', $filters)) {
            $query->whereHas('company', fn ($query) => $query->where('is_verified', $filters['is_verified_company']));
        }

        foreach ($filters['required_skill_ids'] ?? [] as $skillId) {
            $query->whereHas('requiredSkills', fn ($query) => $query->whereKey($skillId));
        }
        foreach ($filters['preferred_skill_ids'] ?? [] as $skillId) {
            $query->whereHas('preferredSkills', fn ($query) => $query->whereKey($skillId));
        }

        if (($filters['sort'] ?? 'newest') === 'relevance' && $search !== null) {
            $like = "%{$search}%";
            $prefix = "{$search}%";
            $query->select('job_posts.*')->selectRaw(
                'CASE
                    WHEN LOWER(job_posts.title) = ? THEN 1
                    WHEN LOWER(job_posts.title) LIKE ? THEN 2
                    WHEN LOWER(job_posts.title) LIKE ? THEN 3
                    WHEN LOWER(job_posts.canonical_role) = ? THEN 4
                    WHEN LOWER(job_posts.canonical_role) LIKE ? THEN 5
                    WHEN LOWER(job_posts.canonical_role) LIKE ? THEN 6
                    WHEN EXISTS (
                        SELECT 1 FROM companies
                        WHERE companies.id = job_posts.company_id
                        AND LOWER(companies.name) = ?
                    ) THEN 7
                    WHEN EXISTS (
                        SELECT 1 FROM company_aliases
                        WHERE company_aliases.company_id = job_posts.company_id
                        AND LOWER(company_aliases.normalized_alias) LIKE ?
                    ) THEN 8
                    WHEN EXISTS (
                        SELECT 1 FROM companies
                        WHERE companies.id = job_posts.company_id
                        AND LOWER(companies.name) LIKE ?
                    ) THEN 9
                    WHEN EXISTS (
                        SELECT 1 FROM job_skills
                        INNER JOIN skills ON skills.id = job_skills.skill_id
                        LEFT JOIN skill_aliases ON skill_aliases.skill_id = skills.id
                        WHERE job_skills.job_post_id = job_posts.id
                        AND (LOWER(skills.name) LIKE ? OR LOWER(skill_aliases.normalized_alias) LIKE ?)
                    ) THEN 10
                    WHEN LOWER(job_posts.description) LIKE ? THEN 11
                    ELSE 12
                END AS relevance_rank',
                [$search, $prefix, $like, $search, $prefix, $like, $search, $like, $like, $like, $like, $like]
            )
                ->orderBy('relevance_rank')
                ->orderByRaw('published_at IS NULL ASC')
                ->orderByDesc('published_at')
                ->orderByDesc('id');
        } else {
            $query->orderByRaw('published_at IS NULL ASC')
                ->orderByDesc('published_at')
                ->orderByDesc('id');
        }

        return JobFeedResource::collection($query->paginate($filters['per_page'] ?? 15)->withQueryString());
    }
}
