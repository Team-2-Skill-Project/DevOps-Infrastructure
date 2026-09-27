<?php

namespace App\Services;

use App\Models\RawJob;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class JobNormalizationService
{
    public function __construct(private RawJobDataSanitizer $sanitizer) {}

    public function normalize(RawJob $rawJob): RawJob
    {
        return DB::transaction(function () use ($rawJob): RawJob {
            $record = RawJob::query()->lockForUpdate()->findOrFail($rawJob->id);

            if ($record->extraction_status !== RawJob::STATUS_EXTRACTED) {
                throw ValidationException::withMessages(['extraction_status' => __('raw_jobs.normalization_requires_extraction')]);
            }
            if ($record->normalization_status !== RawJob::NORMALIZATION_PENDING) {
                throw ValidationException::withMessages(['normalization_status' => __('raw_jobs.normalization_already_finalized')]);
            }

            try {
                $normalized = $this->buildNormalizedData($record->extracted_data ?? []);
            } catch (ValidationException|InvalidArgumentException) {
                $record->update([
                    'normalization_status' => RawJob::NORMALIZATION_FAILED,
                    'normalization_error_code' => 'invalid_data',
                    'normalization_error_message' => __('raw_jobs.normalization_errors.invalid_data'),
                    'normalized_data' => null,
                    'normalized_at' => null,
                ]);

                return $record;
            }

            $record->update([
                'normalization_status' => RawJob::NORMALIZATION_NORMALIZED,
                'normalization_error_code' => null,
                'normalization_error_message' => null,
                'normalized_data' => $normalized,
                'normalized_at' => now(),
            ]);

            return $record;
        });
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function buildNormalizedData(array $data): array
    {
        $normalized = [];
        foreach (['title', 'company_name', 'description', 'canonical_role', 'country', 'state', 'city'] as $field) {
            $normalized[$field] = $this->textValue($data[$field] ?? null);
        }
        foreach (['employment_type', 'job_type', 'work_mode', 'experience_level'] as $field) {
            $normalized[$field] = $this->mappedValue($data[$field] ?? null, $field);
        }
        foreach (['application_url', 'external_url'] as $field) {
            $normalized[$field] = $this->urlValue($data[$field] ?? null);
        }
        foreach (['published_at', 'expires_at'] as $field) {
            $normalized[$field] = $this->dateValue($data[$field] ?? null);
        }

        $min = $this->numberValue($data['min_years_experience'] ?? null);
        $max = $this->numberValue($data['max_years_experience'] ?? null);
        if ($min !== null && $max !== null && $min > $max) {
            throw new InvalidArgumentException('Experience range is invalid.');
        }
        $normalized['min_years_experience'] = $this->pair($data['min_years_experience'] ?? null, $min);
        $normalized['max_years_experience'] = $this->pair($data['max_years_experience'] ?? null, $max);

        $normalized['responsibilities'] = array_map(
            fn (mixed $value): array => $this->textValue(is_string($value) ? $value : null),
            is_array($data['responsibilities'] ?? null) ? $data['responsibilities'] : [],
        );
        $normalized['skills'] = $this->skills($data['skills'] ?? []);

        return $normalized;
    }

    /** @return array{original: string|null, normalized: string|null} */
    private function textValue(mixed $value): array
    {
        if ($value === null) {
            return $this->pair(null, null);
        }
        if (! is_string($value)) {
            throw new InvalidArgumentException('Text value is invalid.');
        }

        $normalized = preg_replace('/\s+/u', ' ', trim($value));

        return $this->pair($value, $normalized ?? trim($value));
    }

    /** @return array{original: string|null, normalized: string|null} */
    private function mappedValue(mixed $value, string $field): array
    {
        $text = $this->textValue($value);
        if ($text['normalized'] === null) {
            return $text;
        }

        $key = strtolower($text['normalized']);
        $maps = [
            'employment_type' => [
                'full time' => 'full_time', 'full-time' => 'full_time', 'fulltime' => 'full_time',
                'part time' => 'part_time', 'part-time' => 'part_time', 'parttime' => 'part_time',
                'contract' => 'contract', 'temporary' => 'temporary', 'internship' => 'internship',
            ],
            'job_type' => ['job' => 'job', 'full time' => 'job', 'internship' => 'internship'],
            'work_mode' => [
                'remote' => 'remote', 'remote / home' => 'remote', 'work from home' => 'remote', 'work-from-home' => 'remote',
                'hybrid' => 'hybrid', 'onsite' => 'onsite', 'on-site' => 'onsite', 'on site' => 'onsite', 'office' => 'onsite',
            ],
            'experience_level' => [
                'entry' => 'entry', 'entry level' => 'entry', 'junior' => 'junior', 'mid' => 'mid', 'mid-level' => 'mid',
                'mid level' => 'mid', 'senior' => 'senior', 'lead' => 'lead', 'principal' => 'principal', 'executive' => 'executive',
            ],
        ];

        return $this->pair($value, $maps[$field][$key] ?? null);
    }

    /** @return array{original: string|null, normalized: string|null} */
    private function urlValue(mixed $value): array
    {
        $text = $this->textValue($value);
        if ($text['normalized'] === null) {
            return $text;
        }

        return $this->pair($value, $this->sanitizer->normalizeUrl($text['normalized']));
    }

    /** @return array{original: string|null, normalized: string|null} */
    private function dateValue(mixed $value): array
    {
        $text = $this->textValue($value);
        if ($text['normalized'] === null) {
            return $text;
        }

        return $this->pair($value, CarbonImmutable::parse($text['normalized'])->toISOString());
    }

    private function numberValue(mixed $value): int|float|null
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_int($value) && ! is_float($value) && (! is_string($value) || ! is_numeric($value))) {
            throw new InvalidArgumentException('Experience value is invalid.');
        }
        $number = (float) $value;
        if ($number < 0) {
            throw new InvalidArgumentException('Experience value is negative.');
        }

        return fmod($number, 1.0) === 0.0 ? (int) $number : $number;
    }

    /** @return array{original: mixed, normalized: mixed} */
    private function pair(mixed $original, mixed $normalized): array
    {
        return ['original' => $original, 'normalized' => $normalized];
    }

    /** @return array<int, array<string, mixed>> */
    private function skills(mixed $skills): array
    {
        if (! is_array($skills)) {
            throw new InvalidArgumentException('Skills are invalid.');
        }

        return array_map(function (mixed $skill): array {
            if (! is_string($skill)) {
                throw new InvalidArgumentException('Skill is invalid.');
            }
            $original = $this->textValue($skill);
            $normalizedName = strtolower((string) $original['normalized']);
            $canonical = app(SkillTaxonomyService::class)->resolve($normalizedName);

            return [
                'original' => $skill,
                'normalized' => $original['normalized'],
                'skill_id' => $canonical?->id,
                'canonical_name' => $canonical?->name,
            ];
        }, array_values($skills));
    }
}
