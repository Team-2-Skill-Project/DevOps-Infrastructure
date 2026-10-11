<?php

namespace App\Http\Requests\Admin;

use App\Services\CvReviewPayloadValidator;
use Illuminate\Foundation\Http\FormRequest;

class CorrectAiReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            ...CvReviewPayloadValidator::rules('corrected_value'),
            'decision_reason' => ['required', 'string', 'max:1000'],
            'reviewer_notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return trans('ai_review.attributes');
    }
}
