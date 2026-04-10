<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:product_variants,sku'],
            'price' => ['nullable', 'integer', 'min:0'],
            'compare_at_price' => ['nullable', 'integer', 'min:0'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'option_value_ids' => ['sometimes', 'array'],
            'option_value_ids.*' => ['integer', 'exists:product_option_values,id'],
        ];
    }
}
