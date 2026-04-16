<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ResolveShippingMethodsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $zoneProvided = $this->filled('shipping_zone_id');

        return [
            'shipping_zone_id' => ['nullable', 'integer', 'exists:shipping_zones,id'],
            'country'          => [$zoneProvided ? 'nullable' : 'required', 'string', 'size:2'],
            'region'           => ['nullable', 'string', 'max:255'],
            'city'             => [$zoneProvided ? 'nullable' : 'required', 'string', 'max:255'],
            'cart_id'          => ['nullable', 'integer', 'exists:carts,id'],
        ];
    }
}
