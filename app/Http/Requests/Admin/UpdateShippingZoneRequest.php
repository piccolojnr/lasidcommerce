<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShippingZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $zoneId = $this->route('zone')?->getKey();

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:255', Rule::unique('shipping_zones', 'code')->ignore($zoneId)],
            'description' => ['nullable', 'string'],
            'country_code' => ['sometimes', 'string', 'size:2'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
