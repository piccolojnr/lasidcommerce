<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:packed,shipped,in_transit,delivered,failed,returned,cancelled'],
            'note'   => ['nullable', 'string'],
        ];
    }
}
