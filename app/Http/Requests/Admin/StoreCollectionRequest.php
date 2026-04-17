<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('collections', 'slug')],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'product_memberships' => ['nullable', 'array'],
            'product_memberships.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'product_memberships.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
