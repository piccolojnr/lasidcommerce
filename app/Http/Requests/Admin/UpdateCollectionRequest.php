<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $collectionId = $this->route('collection')?->getKey();

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('collections', 'slug')->ignore($collectionId)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'product_memberships' => ['nullable', 'array'],
            'product_memberships.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'product_memberships.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
