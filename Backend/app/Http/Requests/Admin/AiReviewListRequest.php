<?php

namespace App\Http\Requests\Admin;

use App\Enums\AiReviewEntityType;
use App\Enums\AiReviewStatus;
use App\Enums\AiReviewTriggerReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiReviewListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::in(AiReviewStatus::values())],
            'entity_type' => ['sometimes', Rule::in(AiReviewEntityType::values())],
            'reason' => ['sometimes', Rule::in(AiReviewTriggerReason::values())],
            'source' => ['sometimes', 'string', 'max:191'],
            'reviewer_id' => ['sometimes', 'integer', 'min:1'],
            'confidence_min' => ['sometimes', 'numeric', 'between:0,1'],
            'confidence_max' => ['sometimes', 'numeric', 'between:0,1', 'gte:confidence_min'],
            'created_from' => ['sometimes', 'date'],
            'created_to' => ['sometimes', 'date', 'after_or_equal:created_from'],
            'search' => ['sometimes', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return trans('ai_review.attributes');
    }
}
