<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = $this->route('coupon')?->getKey();

        return [
            'code' => ['sometimes', 'string', 'max:255', Rule::unique('coupons', 'code')->ignore($couponId)],
            'type' => ['sometimes', 'string', 'max:50'],
            'value' => ['sometimes', 'integer', 'min:0'],
            'minimum_order_amount' => ['nullable', 'integer', 'min:0'],
            'maximum_discount_amount' => ['nullable', 'integer', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
