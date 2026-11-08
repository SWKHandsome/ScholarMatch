<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\AccountValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StudentAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        abort_if($this->route('student') instanceof User && ! $this->route('student')->isStudent(), 404);

        return $this->user()?->isAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        AccountValidation::normalize($this);
    }

    public function rules(): array
    {
        return [
            'name' => AccountValidation::nameRules(),
            'email' => AccountValidation::emailRules($this->route('student')),
            'password' => [$this->isMethod('POST') ? 'required' : 'nullable', 'string', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return AccountValidation::messages();
    }
}
