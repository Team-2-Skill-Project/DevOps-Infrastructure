<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class CvReviewPayloadValidator
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     skills?: list<array{name: string, category?: string|null, proficiency_level?: string|null, confidence_score?: int|float|string|null, evidence?: array<array-key, mixed>|null}>,
     *     experiences?: list<array{company_name: string, title: string, start_date?: string|null, end_date?: string|null, description?: string|null, employment_type?: string|null, country?: string|null, city?: string|null, is_current?: bool, technologies?: list<string>|null}>
     * }
     */
    public function validate(array $payload): array
    {
        $validated = Validator::make(
            ['value' => $payload],
            self::rules('value'),
            [],
            trans('ai_review.attributes'),
        )->validate();

        /** @var array<string, mixed> $value */
        $value = $validated['value'];

        return $value;
    }

    /** @return array<string, mixed> */
    public static function rules(string $prefix): array
    {
        $root = $prefix.'.';

        return [
            $prefix => ['required', 'array:skills,experiences'],
            $root.'skills' => ['sometimes', 'array', 'list', 'max:100'],
            $root.'skills.*' => ['array:name,category,proficiency_level,confidence_score,evidence'],
            $root.'skills.*.name' => ['required', 'string', 'max:255'],
            $root.'skills.*.category' => ['sometimes', 'nullable', 'string', 'max:255'],
            $root.'skills.*.proficiency_level' => ['sometimes', 'nullable', 'string', 'max:100'],
            $root.'skills.*.confidence_score' => ['sometimes', 'nullable', 'numeric', 'between:0,1'],
            $root.'skills.*.evidence' => ['sometimes', 'nullable', 'array', 'max:20'],
            $root.'experiences' => ['sometimes', 'array', 'list', 'max:100'],
            $root.'experiences.*' => ['array:company_name,title,start_date,end_date,description,employment_type,country,city,is_current,technologies'],
            $root.'experiences.*.company_name' => ['required', 'string', 'max:255'],
            $root.'experiences.*.title' => ['required', 'string', 'max:255'],
            $root.'experiences.*.start_date' => ['sometimes', 'nullable', 'date'],
            $root.'experiences.*.end_date' => ['sometimes', 'nullable', 'date'],
            $root.'experiences.*.description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            $root.'experiences.*.employment_type' => ['sometimes', 'nullable', 'string', 'max:100'],
            $root.'experiences.*.country' => ['sometimes', 'nullable', 'string', 'max:100'],
            $root.'experiences.*.city' => ['sometimes', 'nullable', 'string', 'max:100'],
            $root.'experiences.*.is_current' => ['sometimes', 'boolean'],
            $root.'experiences.*.technologies' => ['sometimes', 'nullable', 'array', 'list', 'max:100'],
            $root.'experiences.*.technologies.*' => ['string', 'max:255'],
        ];
    }
}
