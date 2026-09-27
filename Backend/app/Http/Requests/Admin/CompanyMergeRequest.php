<?php

namespace App\Http\Requests\Admin;

use App\Models\Company;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyMergeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target_company_id' => [
                'required',
                'integer',
                Rule::exists(Company::class, 'id'),
                Rule::notIn([(int) $this->route('sourceCompany')]),
            ],
        ];
    }
}
