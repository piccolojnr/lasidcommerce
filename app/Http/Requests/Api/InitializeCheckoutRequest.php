<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class InitializeCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'],
            'payment_provider' => ['required', 'string', 'in:paystack'],
            'coupon_code' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
