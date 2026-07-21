<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductOptionValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $optionTypeId = $this->route('option_type')?->getKey();

        // Accept either a single `value` string or a `values[]` array.
        if ($this->has('values')) {
            return [
                'values' => ['required', 'array', 'min:1'],
                'values.*' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('product_option_values', 'value')
                        ->where('option_type_id', $optionTypeId),
                ],
            ];
        }

        return [
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_option_values', 'value')
                    ->where('option_type_id', $optionTypeId),
            ],
        ];
    }
}
