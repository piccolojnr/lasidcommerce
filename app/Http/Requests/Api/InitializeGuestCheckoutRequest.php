<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class InitializeGuestCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $zoneProvided = $this->filled('shipping_zone_id');

        return [
            'email'                  => ['required', 'email', 'max:255'],
            'name'                   => ['required', 'string', 'max:255'],
            'phone'                  => ['nullable', 'string', 'max:50'],
            'shipping_zone_id'       => ['nullable', 'integer', 'exists:shipping_zones,id'],
            'shipping_zone_area_id'  => ['nullable', 'integer', 'exists:shipping_zone_areas,id'],
            'country'                => [$zoneProvided ? 'nullable' : 'required', 'string', 'max:255'],
            'region'                 => ['nullable', 'string', 'max:255'],
            'city'                   => [$zoneProvided ? 'nullable' : 'required', 'string', 'max:255'],
            'district'               => ['nullable', 'string', 'max:255'],
            'address_line_1'         => ['required', 'string', 'max:255'],
            'address_line_2'         => ['nullable', 'string', 'max:255'],
            'landmark'               => ['nullable', 'string', 'max:255'],
            'postal_code'            => ['nullable', 'string', 'max:50'],
            'shipping_method_id'     => ['required', 'integer', 'exists:shipping_methods,id'],
            'payment_provider'       => ['required', 'string', 'in:paystack'],
            'coupon_code'            => ['nullable', 'string', 'max:255'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
            'delivery_notes'         => ['nullable', 'string', 'max:1000'],
        ];
    }
}
