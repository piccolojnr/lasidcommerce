<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->getKey();

        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'sku' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($productId)],
            'status' => ['sometimes', 'string', 'max:50'],
            'product_type' => ['sometimes', 'string', 'max:50'],
            'base_price' => ['sometimes', 'integer', 'min:0'],
            'compare_at_price' => ['nullable', 'integer', 'min:0'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            'track_inventory' => ['sometimes', 'boolean'],
            'allow_backorders' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
