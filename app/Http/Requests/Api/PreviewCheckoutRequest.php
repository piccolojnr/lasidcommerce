<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PreviewCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'shipping_method_id' => ['nullable', 'integer', 'exists:shipping_methods,id'],
            'coupon_code' => ['nullable', 'string', 'max:255'],
        ];
    }
}
