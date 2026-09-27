<?php

namespace App\Services;

use Carbon\CarbonImmutable;

/**
 * Deterministic vacancy fingerprinting for duplicate candidate discovery.
 *
 * Version 1 components: resolved canonical company identity (or normalized
 * company text), comparison title, normalized location, employment type and
 * stable published date. Volatile data (payload ordering, ingestion
 * timestamps, tracking parameters, metadata) is never included.
 *
 * A fingerprint match only surfaces candidates; it never proves a duplicate.
 */
class JobFingerprintService
{
    public const VERSION = 1;

    /** @param array<string, mixed> $normalizedData */
    public function fingerprintForNormalized(array $normalizedData, ?int $companyId = null, ?string $companyMatch = null): string
    {
        return hash('sha256', (string) json_encode($this->components($normalizedData, $companyId, $companyMatch), JSON_THROW_ON_ERROR));
    }

    /**
     * @param  array<string, mixed>  $normalizedData
     * @return array<string, string|null>
     */
    public function components(array $normalizedData, ?int $companyId = null, ?string $companyMatch = null): array
    {
        $company = $companyId !== null
            ? 'company:'.$companyId
            : 'text:'.$this->normalizeCompany($this->pairValue($normalizedData['company_name'] ?? null));

        $components = [
            'company' => $company,
            'company_match' => $companyMatch,
            'title' => $this->comparisonTitle($this->pairValue($normalizedData['title'] ?? null) ?? ''),
            'location' => $this->normalizeLocation($normalizedData),
            'employment_type' => $this->pairValue($normalizedData['employment_type'] ?? null),
            'published_date' => $this->stableDate($this->pairValue($normalizedData['published_at'] ?? null)),
        ];
        ksort($components);

        return $components;
    }

    public function comparisonTitle(?string $title): string
    {
        $title = mb_strtolower(trim((string) $title));
        $title = (string) preg_replace('/[\/\\\\|_\-]+/u', ' ', $title);
        $title = (string) preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $title);
        $title = (string) preg_replace('/\s+/u', ' ', trim($title));

        return $title;
    }

    public function normalizeCompany(?string $name): string
    {
        $name = mb_strtolower(trim((string) $name));
        $name = (string) preg_replace('/\s+/u', ' ', $name);

        return $name;
    }

    /** @param array<string, mixed> $normalizedData */
    public function normalizeLocation(array $normalizedData): ?string
    {
        $parts = [];
        foreach (['country', 'city', 'state'] as $field) {
            $value = $this->pairValue($normalizedData[$field] ?? null);
            if ($value !== null && trim($value) !== '') {
                $parts[] = mb_strtolower((string) preg_replace('/\s+/u', ' ', trim($value)));
            }
        }
        sort($parts, SORT_STRING);

        return $parts === [] ? null : implode('|', $parts);
    }

    public function titleSimilarity(string $first, string $second): float
    {
        $firstTokens = $this->tokens($this->comparisonTitle($first));
        $secondTokens = $this->tokens($this->comparisonTitle($second));

        if ($firstTokens === [] || $secondTokens === []) {
            return $firstTokens === $secondTokens ? 1.0 : 0.0;
        }

        $intersection = count(array_intersect($firstTokens, $secondTokens));
        $union = count(array_unique([...$firstTokens, ...$secondTokens]));

        return round($intersection / $union, 4);
    }

    /** @return list<string> */
    private function tokens(string $title): array
    {
        if ($title === '') {
            return [];
        }

        return array_values(array_unique(explode(' ', $title)));
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

    private function pairValue(mixed $pair): ?string
    {
        if (is_array($pair) && array_key_exists('normalized', $pair)) {
            $value = $pair['normalized'];

            return $value === null ? null : (string) $value;
        }

        return $pair === null ? null : (string) $pair;
    }
}
