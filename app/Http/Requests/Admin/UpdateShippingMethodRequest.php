<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShippingMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $methodId = $this->route('method')?->getKey();

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:255', Rule::unique('shipping_methods', 'code')->ignore($methodId)],
            'method_type' => ['sometimes', 'string', 'max:100'],
            'price_type' => ['sometimes', 'string', 'max:100'],
            'flat_rate_amount' => ['nullable', 'integer', 'min:0'],
            'min_delivery_days' => ['nullable', 'integer', 'min:0'],
            'max_delivery_days' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
