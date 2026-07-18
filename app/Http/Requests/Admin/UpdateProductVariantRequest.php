<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'option_value_ids.*' => ['nullable', 'integer', 'exists:product_option_values,id'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $variant = $this->route('variant');
                $product = $variant?->product;
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
