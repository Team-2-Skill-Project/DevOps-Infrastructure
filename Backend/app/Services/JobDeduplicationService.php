<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyAlias;
use App\Models\JobPost;
use App\Models\JobSourceReference;
use App\Models\RawJob;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Phase 5 duplicate detection.
 *
 * Conservative policy: only strong deterministic evidence (existing
 * reference, same source + same external_id, exact normalized URL) may
 * automatically match a canonical JobPost. Fingerprint and title/company
 * similarity only surface candidates and yield "possible" without merging.
 *
 * Canonical creation boundary: Phase 5 never creates JobPost records.
 * Distinct and possible outcomes leave canonical_job_post_id null so Phase 6
 * can review and create canonical vacancies safely.
 */
class JobDeduplicationService
{
    public function __construct(
        private JobFingerprintService $fingerprints,
        private RawJobDataSanitizer $sanitizer,
    ) {}

    public function evaluate(RawJob $rawJob): DuplicateDecision
    {
        $record = RawJob::query()->findOrFail($rawJob->id);

        if ($record->normalization_status !== RawJob::NORMALIZATION_NORMALIZED) {
            throw ValidationException::withMessages(['normalization_status' => __('raw_jobs.deduplication_requires_normalization')]);
        }

        $normalized = is_array($record->normalized_data) ? $record->normalized_data : [];
        [$companyId, $companyMode] = $this->resolveCompany($this->pairValue($normalized['company_name'] ?? null));

        $fingerprint = $this->fingerprints->fingerprintForNormalized($normalized, $companyId, $companyMode);
        $evidence = $this->baseEvidence($record, $normalized, $companyId, $companyMode, $fingerprint);

        if ($reference = JobSourceReference::query()->where('raw_job_id', $record->id)->first()) {
            if ($this->canonicalExists((int) $reference->job_post_id)) {
                return new DuplicateDecision(
                    'matched', (int) $reference->job_post_id, 'existing_reference',
                    $fingerprint, JobFingerprintService::VERSION,
                    [...$evidence, 'existing_reference' => true, 'existing_job_post_id' => (int) $reference->job_post_id],
                );
            }
        }

        $externalId = trim((string) ($record->external_id ?? ''));
        if ($externalId !== '') {
            $match = JobSourceReference::query()
                ->where('job_source_id', $record->job_source_id)
                ->where('external_id', $externalId)
                ->orderBy('id')
                ->first();
            if ($match !== null && $this->canonicalExists((int) $match->job_post_id)) {
                return new DuplicateDecision(
                    'matched', (int) $match->job_post_id, 'same_source_external_id',
                    $fingerprint, JobFingerprintService::VERSION,
                    [...$evidence, 'same_source_external_id' => true, 'existing_job_post_id' => (int) $match->job_post_id],
                );
            }
        }

        if ($urlMatch = $this->findUrlMatch($record)) {
            return new DuplicateDecision(
                'matched', (int) $urlMatch->job_post_id, 'exact_normalized_url',
                $fingerprint, JobFingerprintService::VERSION,
                [...$evidence, 'exact_normalized_url' => true, 'existing_job_post_id' => (int) $urlMatch->job_post_id],
            );
        }

        $similarity = $this->bestCandidate($record, $normalized, $companyId, $fingerprint);
        $evidence = [...$evidence, ...$similarity['evidence']];

        if ($similarity['possible']) {
            return new DuplicateDecision(
                'possible', null, $similarity['method'],
                $fingerprint, JobFingerprintService::VERSION, $evidence,
            );
        }

        return new DuplicateDecision(
            'distinct', null, 'no_match',
            $fingerprint, JobFingerprintService::VERSION, $evidence,
        );
    }

    public function deduplicate(RawJob $rawJob): RawJob
    {
        return DB::transaction(function () use ($rawJob): RawJob {
            $record = RawJob::query()->lockForUpdate()->findOrFail($rawJob->id);

            if ($record->normalization_status !== RawJob::NORMALIZATION_NORMALIZED) {
                throw ValidationException::withMessages(['normalization_status' => __('raw_jobs.deduplication_requires_normalization')]);
            }

            if ($record->deduplication_status !== RawJob::DEDUPLICATION_PENDING) {
                return $record;
            }

            try {
                $decision = $this->evaluate($record);
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (\Throwable) {
                $record->update([
                    'deduplication_status' => RawJob::DEDUPLICATION_FAILED,
                    'deduplication_method' => null,
                    'deduplication_evidence' => ['error' => 'deduplication_failed'],
                    'deduplicated_at' => now(),
                ]);

                return $record;
            }

            $record->update([
                'deduplication_status' => $decision->decision,
                'fingerprint' => $decision->fingerprint,
                'fingerprint_version' => $decision->fingerprintVersion,
                'canonical_job_post_id' => $decision->canonicalJobPostId,
                'deduplication_method' => $decision->method,
                'deduplication_evidence' => $decision->evidence,
                'deduplicated_at' => now(),
            ]);

            if ($decision->isMatched() && $decision->canonicalJobPostId !== null) {
                $this->attachReference($record->refresh(), $decision);
            }

            return $record->refresh();
        });
    }

    /**
     * @return array{0: int|null, 1: string} mode is canonical|alias|text|unknown.
     */
    public function resolveCompany(?string $name): array
    {
        $normalized = mb_strtolower((string) preg_replace('/\s+/u', ' ', trim((string) $name)));

        if ($normalized === '') {
            return [null, 'unknown'];
        }

        $company = Company::query()->where('normalized_name', $normalized)->first();
        if ($company !== null) {
            return [$company->id, 'canonical'];
        }

        $alias = CompanyAlias::query()->where('normalized_alias', $normalized)->first();
        if ($alias !== null) {
            return [$alias->company_id, 'alias'];
        }

        return [null, 'text'];
    }

    /**
     * @param  array<string, mixed>  $normalized
     * @return array<string, mixed>
     */
    private function baseEvidence(RawJob $record, array $normalized, ?int $companyId, string $companyMode, string $fingerprint): array
    {
        return [
            'fingerprint' => $fingerprint,
            'fingerprint_version' => JobFingerprintService::VERSION,
            'company_match' => $companyMode,
            'company_id' => $companyId,
            'comparison_title' => $this->fingerprints->comparisonTitle($this->pairValue($normalized['title'] ?? null) ?? ''),
            'comparison_company' => $this->fingerprints->normalizeCompany($this->pairValue($normalized['company_name'] ?? null)),
            'location' => $this->fingerprints->normalizeLocation($normalized),
        ];
    }

    private function findUrlMatch(RawJob $record): ?JobSourceReference
    {
        foreach ([$record->detail_url, $record->source_url] as $url) {
            if ($url === null || trim($url) === '') {
                continue;
            }

            try {
                $normalized = $this->sanitizer->normalizeUrl(trim($url));
            } catch (\Throwable) {
                continue;
            }
            /** @var Builder<JobSourceReference> $references */
            $references = JobSourceReference::query();
            $match = $references
                ->where(function (Builder $query) use ($normalized): void {
                    $query->where('detail_url', $normalized)->orWhere('source_url', $normalized);
                })
                ->orderBy('id')
                ->first();

            if ($match !== null && $this->canonicalExists((int) $match->job_post_id)) {
                return $match;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $normalized
     * @return array{possible: bool, method: string, evidence: array<string, mixed>}
     */
    private function bestCandidate(RawJob $record, array $normalized, ?int $companyId, string $fingerprint): array
    {
        $title = $this->pairValue($normalized['title'] ?? null) ?? '';
        $location = $this->fingerprints->normalizeLocation($normalized);
        $publishedDate = $this->pairValue($normalized['published_at'] ?? null);

        $siblings = RawJob::query()
            ->where('id', '!=', $record->id)
            ->where('fingerprint', $fingerprint)
            ->where('deduplication_status', '!=', RawJob::DEDUPLICATION_PENDING)
            ->orderBy('id')
            ->limit(20)
            ->get();

        $evidence = [
            'fingerprint_candidate' => $siblings->isNotEmpty(),
            'fingerprint_candidate_count' => $siblings->count(),
            'title_similarity' => 0.0,
            'location_agreement' => false,
            'date_agreement' => false,
            'candidate_raw_job_id' => null,
        ];

        $bestSimilarity = 0.0;
        $best = null;

        foreach ($siblings as $sibling) {
            $siblingNormalized = is_array($sibling->normalized_data) ? $sibling->normalized_data : [];
            $similarity = $this->fingerprints->titleSimilarity($title, $this->pairValue($siblingNormalized['title'] ?? null) ?? '');
            if ($similarity > $bestSimilarity) {
                $bestSimilarity = $similarity;
                $best = $sibling;
            }
        }

        if ($best !== null) {
            $bestNormalized = is_array($best->normalized_data) ? $best->normalized_data : [];
            $evidence['title_similarity'] = $bestSimilarity;
            $evidence['location_agreement'] = $location !== null && $location === $this->fingerprints->normalizeLocation($bestNormalized);
            $evidence['date_agreement'] = $this->stableDate($publishedDate) !== null && $this->stableDate($publishedDate) === $this->stableDate($this->pairValue($bestNormalized['published_at'] ?? null));
            $evidence['candidate_raw_job_id'] = $best->id;
        } else {
            $nearby = RawJob::query()
                ->where('id', '!=', $record->id)
                ->where('job_source_id', $record->job_source_id)
                ->where('deduplication_status', '!=', RawJob::DEDUPLICATION_PENDING)
                ->orderByDesc('id')
                ->limit(20)
                ->get();

            foreach ($nearby as $candidate) {
                $candidateNormalized = is_array($candidate->normalized_data) ? $candidate->normalized_data : [];
                [$candidateCompanyId] = $this->resolveCompany($this->pairValue($candidateNormalized['company_name'] ?? null));
                $companyAgrees = $companyId !== null
                    ? $candidateCompanyId === $companyId
                    : $this->fingerprints->normalizeCompany($this->pairValue($candidateNormalized['company_name'] ?? null)) === $this->fingerprints->normalizeCompany($this->pairValue($normalized['company_name'] ?? null))
                    && $this->fingerprints->normalizeCompany($this->pairValue($normalized['company_name'] ?? null)) !== '';
                $similarity = $this->fingerprints->titleSimilarity($title, $this->pairValue($candidateNormalized['title'] ?? null) ?? '');

                if ($similarity > $bestSimilarity && $companyAgrees) {
                    $bestSimilarity = $similarity;
                    $best = $candidate;
                }
            }

            if ($best !== null) {
                $bestNormalized = is_array($best->normalized_data) ? $best->normalized_data : [];
                $evidence['title_similarity'] = $bestSimilarity;
                $evidence['location_agreement'] = $location !== null && $location === $this->fingerprints->normalizeLocation($bestNormalized);
                $evidence['date_agreement'] = $this->stableDate($publishedDate) !== null && $this->stableDate($publishedDate) === $this->stableDate($this->pairValue($bestNormalized['published_at'] ?? null));
                $evidence['candidate_raw_job_id'] = $best->id;
            }
        }

        if ($siblings->isNotEmpty()) {
            return ['possible' => true, 'method' => 'fingerprint_candidate', 'evidence' => $evidence];
        }

        if ($best !== null && $bestSimilarity >= 0.5) {
            return ['possible' => true, 'method' => 'title_company_similarity', 'evidence' => $evidence];
        }

        return ['possible' => false, 'method' => 'no_match', 'evidence' => $evidence];
    }

    private function attachReference(RawJob $record, DuplicateDecision $decision): void
    {
        try {
            JobSourceReference::query()->firstOrCreate(['raw_job_id' => $record->id], [
                'job_post_id' => $decision->canonicalJobPostId,
                'job_source_id' => $record->job_source_id,
                'ingestion_run_id' => $record->ingestion_run_id,
                'external_id' => $record->external_id,
                'source_url' => $record->source_url,
                'detail_url' => $record->detail_url,
                'match_method' => $decision->method ?? 'existing_reference',
                'match_evidence' => $this->referenceEvidence($decision),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Concurrent worker created the reference first; idempotent reuse wins.
        }

        $reference = JobSourceReference::query()->where('raw_job_id', $record->id)->first();
        if ($reference !== null
            && ((int) $reference->job_source_id !== (int) $record->job_source_id
                || (int) $reference->ingestion_run_id !== (int) $record->ingestion_run_id)) {
            throw ValidationException::withMessages(['raw_job_id' => __('raw_jobs.reference_provenance_mismatch')]);
        }
    }

    /** @return array<string, mixed> */
    private function referenceEvidence(DuplicateDecision $decision): array
    {
        $evidence = $decision->evidence;
        unset($evidence['comparison_title'], $evidence['comparison_company']);

        return [
            'method' => $decision->method,
            'fingerprint_version' => $decision->fingerprintVersion,
            'company_match' => $evidence['company_match'] ?? null,
            'title_similarity' => $evidence['title_similarity'] ?? null,
            'location_agreement' => $evidence['location_agreement'] ?? null,
            'date_agreement' => $evidence['date_agreement'] ?? null,
            'fingerprint_candidate' => $evidence['fingerprint_candidate'] ?? false,
        ];
    }

    private function canonicalExists(int $jobPostId): bool
    {
        return JobPost::query()->whereKey($jobPostId)->exists();
    }

    private function pairValue(mixed $pair): ?string
    {
        if (is_array($pair) && array_key_exists('normalized', $pair)) {
            $value = $pair['normalized'];

            return $value === null ? null : (string) $value;
        }

        return $pair === null ? null : (string) $pair;
    }

    private function stableDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
