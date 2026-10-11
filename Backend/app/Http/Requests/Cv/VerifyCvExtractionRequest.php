<?php

namespace App\Http\Requests\Cv;

use App\Models\CvExtraction;
use Illuminate\Foundation\Http\FormRequest;

class VerifyCvExtractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $extraction = $this->route('extraction');

        return $extraction instanceof CvExtraction
            && $this->user()->can('update', $extraction->cvDocument);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'skills' => ['nullable', 'array'],
            'skills.*.name' => ['required', 'string', 'max:255'],
            'skills.*.category' => ['nullable', 'string', 'max:255'],
            'skills.*.proficiency_level' => ['nullable', 'string'],
            'skills.*.confidence_score' => ['nullable', 'numeric', 'between:0,1'],

            'experiences' => ['nullable', 'array'],
            'experiences.*.company_name' => ['required', 'string', 'max:255'],
            'experiences.*.title' => ['required', 'string', 'max:255'],
            'experiences.*.start_date' => ['required', 'date'],
            'experiences.*.end_date' => ['nullable', 'date', 'after_or_equal:experiences.*.start_date'],
            'experiences.*.description' => ['nullable', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return trans('cv.attributes');
    }
}
