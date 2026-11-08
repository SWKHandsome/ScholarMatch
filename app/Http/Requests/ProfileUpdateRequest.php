<?php

namespace App\Http\Requests;

use App\Support\AccountValidation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        AccountValidation::normalize($this);
    }

    public function messages(): array
    {
        return AccountValidation::messages();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => AccountValidation::nameRules(),
            'email' => AccountValidation::emailRules($this->user()),
        ];
    }
}
