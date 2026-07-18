<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBulkCatalogProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'products' => ['required', 'array', 'min:1', 'max:100'],
            'products.*.id' => ['required', 'integer', 'exists:products,id'],
            'products.*.name' => ['required', 'string', 'max:255'],
            'products.*.sku' => ['required', 'string', 'max:255'],
            'products.*.status' => ['required', 'string', 'max:50'],
            'products.*.product_type' => ['required', 'string', 'max:50'],
            'products.*.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'products.*.brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'products.*.base_price' => ['required', 'integer', 'min:0'],
            'products.*.compare_at_price' => ['nullable', 'integer', 'min:0'],
            'products.*.cost_price' => ['nullable', 'integer', 'min:0'],
            'products.*.quantity_on_hand' => ['nullable', 'integer', 'min:0'],
            'products.*.reorder_level' => ['nullable', 'integer', 'min:0'],
            'products.*.track_inventory' => ['sometimes', 'boolean'],
            'products.*.allow_backorders' => ['sometimes', 'boolean'],
            'products.*.is_featured' => ['sometimes', 'boolean'],
        ];
    }
}
