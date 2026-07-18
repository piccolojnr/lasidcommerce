<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'option_value_ids.*' => ['nullable', 'integer', 'exists:product_option_values,id'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $product = $this->route('product');
                $optionValueIds = collect($this->input('option_value_ids', []))
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                if ($product === null || $optionValueIds->isEmpty()) {
                    return;
                }

                $optionValues = $product->optionTypes()
                    ->whereHas('optionValues', fn ($query) => $query->whereIn('id', $optionValueIds))
                    ->with(['optionValues' => fn ($query) => $query->whereIn('id', $optionValueIds)])
                    ->get();

                $validIds = $optionValues->flatMap->optionValues->pluck('id')->map(fn ($id) => (int) $id);

                if ($validIds->count() !== $optionValueIds->count()) {
                    $validator->errors()->add('option_value_ids', 'Every selected option value must belong to this product.');
                }

                if ($optionValues->count() !== $optionValueIds->count()) {
                    $validator->errors()->add('option_value_ids', 'Select only one value for each option.');
                }
            },
        ];
    }
}
