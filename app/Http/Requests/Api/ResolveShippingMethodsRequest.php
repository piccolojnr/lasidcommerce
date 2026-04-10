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
        return [
            'country' => ['required', 'string', 'size:2'],
            'region' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'cart_id' => ['nullable', 'integer', 'exists:carts,id'],
        ];
    }
}
