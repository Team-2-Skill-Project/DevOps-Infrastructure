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
                        $fail('The uploaded file is empty and contains no data.');
                    }
                },
            ],
        ];
    }
}
