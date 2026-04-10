<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingZoneAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area_type' => ['required', 'string', 'max:100'],
            'area_name' => ['required', 'string', 'max:255'],
        ];
    }
}
