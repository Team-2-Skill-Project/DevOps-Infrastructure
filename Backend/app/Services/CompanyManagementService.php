<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\AuditEntityType;
use App\Enums\AuditSource;
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
    public function __construct(private AuditService $audit) {}

    /**
     * @var list<string>
     */
    private const SNAPSHOT_FIELDS = [
        'name', 'website_url', 'linkedin_url', 'logo_url', 'industry',
        'country', 'state', 'city', 'description', 'is_verified', 'is_active',
    ];

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

                $isNew = ! $record->exists;
                $before = $isNew ? null : $record->only(self::SNAPSHOT_FIELDS);

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
                $record->refresh();

                $this->audit->record(
                    $isNew ? AuditAction::COMPANY_CREATED : AuditAction::COMPANY_UPDATED,
                    AuditEntityType::COMPANY,
                    (int) $record->getKey(),
                    AuditSource::ADMIN,
                    $actor,
                    $before,
                    $record->only(self::SNAPSHOT_FIELDS),
                );

                return $record;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['name' => __('company.validation.name_taken')]);
        }
    }

    /** @return array{company: Company, meta: array<string, int>} */
    public function mergeCompany(Company $source, Company $target, ?User $actor = null): array
    {
        try {
            return DB::transaction(function () use ($source, $target, $actor): array {
                $companies = Company::query()
                    ->whereIn('id', [$source->id, $target->id])
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
                $source = $companies->get($source->id);
                $target = $companies->get($target->id);

                if (! $source || ! $target) {
                    throw ValidationException::withMessages(['target_company_id' => __('company.validation.merge_subject_missing')]);
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

                $meta = [
                    'merged_company_id' => $source->id,
                    'target_company_id' => $target->id,
                    'job_posts_moved' => $jobs->count(),
                    'aliases_moved' => $aliasesMoved,
                    'aliases_collapsed' => $aliasesCollapsed,
                    'source_name_aliases_created' => $sourceNameAliasCreated,
                ];

                $this->audit->record(
                    AuditAction::COMPANY_MERGED,
                    AuditEntityType::COMPANY,
                    (int) $target->id,
                    AuditSource::ADMIN,
                    $actor ?? $this->audit->currentActor(),
                    ['source_company' => ['id' => $source->id, 'name' => $source->name]],
                    ['target_company' => ['id' => $target->id, 'name' => $target->name]],
                    $meta,
                );

                return [
                    'company' => $target,
                    'meta' => $meta,
                ];
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['target_company_id' => __('company.validation.merge_conflict')]);
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
            throw ValidationException::withMessages(['name' => __('company.validation.name_unavailable')]);
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
            throw ValidationException::withMessages(['target_company_id' => __('company.validation.merge_alias_conflict')]);
        }
    }
}
