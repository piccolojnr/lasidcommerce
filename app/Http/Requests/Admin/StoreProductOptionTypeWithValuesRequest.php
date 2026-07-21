<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductOptionTypeWithValuesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller via policy.
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'values' => ['nullable', 'array'],
            'values.*' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The option name is required.',
            'values.*.required' => 'Each option value must be a non-empty string.',
        ];
    }
}
