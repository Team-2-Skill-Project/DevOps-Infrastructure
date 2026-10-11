<?php

namespace App\Http\Requests\Cv;

use Illuminate\Foundation\Http\FormRequest;

class UploadCvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string|\Closure>> */
    public function rules(): array
    {
        return [
            'cv' => [
                'required',
                'file',
                'mimes:pdf,docx',
                'max:5120',
                function ($attribute, $value, $fail) {
                    if ($value->getSize() === 0) {
                        $fail(__('cv.validation.empty_file'));
                    }
                },
            ],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return trans('cv.attributes');
    }
}
