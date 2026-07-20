<?php

namespace App\Http\Requests\Api;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => [
                'nullable',
                'integer',
                // Variant must exist AND belong to the submitted product.
                Rule::exists('product_variants', 'id')->where(
                    'product_id',
                    (int) $this->input('product_id', 0)
                ),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_variant_id.exists' => 'The selected variant does not belong to this product.',
        ];
    }
}
