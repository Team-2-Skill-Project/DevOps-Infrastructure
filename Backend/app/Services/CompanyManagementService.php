<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyAlias;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CompanyManagementService
{
    public function normalize(string $name): string
    {
        return Str::lower(Str::trim($name));
    }

    /** @param array<string, mixed> $data */
    public function save(?Company $company, array $data, User $actor): Company
    {
        try {
            return DB::transaction(function () use ($company, $data, $actor): Company {
                $record = $company
                    ? Company::query()->lockForUpdate()->findOrFail($company->id)
                    : new Company;

                if (array_key_exists('name', $data)) {
                    $data['name'] = Str::trim($data['name']);
                    $normalized = $this->normalize($data['name']);
                    $this->ensureAvailable($normalized, $record->exists ? $record->id : null);
                    $record->normalized_name = $normalized;
                }

                $record->fill($data);

                if (! $record->exists) {
                    $record->created_by = max(0, (int) $actor->getKey());
                }

                $record->save();

                return $record;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['name' => 'This company name is already in use.']);
        }
    }

    /** @return array{company: Company, meta: array<string, int>} */
    public function mergeCompany(Company $source, Company $target): array
    {
        try {
            return DB::transaction(function () use ($source, $target): array {
                $companies = Company::query()
                    ->whereIn('id', [$source->id, $target->id])
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
                $source = $companies->get($source->id);
                $target = $companies->get($target->id);

                if (! $source || ! $target) {
                    throw ValidationException::withMessages(['target_company_id' => 'The source or target company no longer exists.']);
                }

                $sourceAliases = CompanyAlias::query()
                    ->where('company_id', $source->id)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
                $targetAliases = CompanyAlias::query()
                    ->where('company_id', $target->id)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('normalized_alias');

                foreach ($sourceAliases->pluck('normalized_alias')->push($source->normalized_name)->unique() as $normalized) {
                    $this->ensureMergeAliasAvailable($normalized, $source->id, $target->id);
                }

                $jobs = JobPost::query()
                    ->where('company_id', $source->id)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
                foreach ($jobs as $job) {
                    $job->company_id = max(0, (int) $target->getKey());
                    $job->save();
                }

                $aliasesMoved = 0;
                $aliasesCollapsed = 0;
                foreach ($sourceAliases as $alias) {
                    if ($alias->normalized_alias === $target->normalized_name || $targetAliases->has($alias->normalized_alias)) {
                        $alias->delete();
                        $aliasesCollapsed++;

                        continue;
                    }

                    $alias->company_id = max(0, (int) $target->getKey());
                    $alias->save();
                    $targetAliases->put($alias->normalized_alias, $alias);
                    $aliasesMoved++;
                }

                $sourceNameAliasCreated = 0;
                if ($source->normalized_name !== $target->normalized_name && ! $targetAliases->has($source->normalized_name)) {
                    $target->aliases()->create(['alias' => $source->name]);
                    $sourceNameAliasCreated = 1;
                }

                foreach (['website_url', 'linkedin_url', 'logo_url', 'industry', 'country', 'state', 'city', 'description'] as $field) {
                    if (($target->getAttribute($field) === null || $target->getAttribute($field) === '')
                        && $source->getAttribute($field) !== null && $source->getAttribute($field) !== '') {
                        $target->setAttribute($field, $source->getAttribute($field));
                    }
                }

                if ($source->is_verified) {
                    $target->is_verified = true;
                }

                $target->save();
                $source->delete();

                return [
                    'company' => $target,
                    'meta' => [
                        'merged_company_id' => $source->id,
                        'target_company_id' => $target->id,
                        'job_posts_moved' => $jobs->count(),
                        'aliases_moved' => $aliasesMoved,
                        'aliases_collapsed' => $aliasesCollapsed,
                        'source_name_aliases_created' => $sourceNameAliasCreated,
                    ],
                ];
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['target_company_id' => 'The company merge encountered a conflicting company or alias.']);
        }
    }

    private function ensureAvailable(string $normalized, ?int $companyId): void
    {
        $canonical = Company::query()
            ->where('normalized_name', $normalized)
            ->when($companyId !== null, fn ($query) => $query->where('id', '!=', $companyId))
            ->lockForUpdate()
            ->first();

        $alias = CompanyAlias::query()
            ->where('normalized_alias', $normalized)
            ->lockForUpdate()
            ->first();

        if ($canonical || $alias) {
            throw ValidationException::withMessages(['name' => 'This company name is already used by a company or alias.']);
        }
    }

    private function ensureMergeAliasAvailable(string $normalized, int $sourceCompanyId, int $targetCompanyId): void
    {
        $canonical = Company::query()
            ->where('normalized_name', $normalized)
            ->whereNotIn('id', [$sourceCompanyId, $targetCompanyId])
            ->lockForUpdate()
            ->exists();
        $alias = CompanyAlias::query()
            ->where('normalized_alias', $normalized)
            ->where('company_id', '!=', $sourceCompanyId)
            ->lockForUpdate()
            ->first();

        if ($canonical || ($alias && $alias->company_id !== $targetCompanyId)) {
            throw ValidationException::withMessages(['target_company_id' => 'A company name or alias conflicts with another company.']);
        }
    }
}
