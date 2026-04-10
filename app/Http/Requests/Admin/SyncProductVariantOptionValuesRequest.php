<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SyncProductVariantOptionValuesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'option_value_ids' => ['required', 'array'],
            'option_value_ids.*' => ['integer', 'exists:product_option_values,id'],
        ];
    }
}
