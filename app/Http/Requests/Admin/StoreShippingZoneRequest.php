<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:shipping_zones,code'],
            'description' => ['nullable', 'string'],
            'country_code' => ['required', 'string', 'size:2'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
