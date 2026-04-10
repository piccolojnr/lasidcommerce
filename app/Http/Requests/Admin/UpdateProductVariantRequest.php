<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variantId = $this->route('variant')?->getKey();

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'sku' => ['sometimes', 'string', 'max:255', Rule::unique('product_variants', 'sku')->ignore($variantId)],
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
