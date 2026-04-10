<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:shipping_methods,code'],
            'method_type' => ['required', 'string', 'max:100'],
            'price_type' => ['required', 'string', 'max:100'],
            'flat_rate_amount' => ['nullable', 'integer', 'min:0'],
            'min_delivery_days' => ['nullable', 'integer', 'min:0'],
            'max_delivery_days' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
