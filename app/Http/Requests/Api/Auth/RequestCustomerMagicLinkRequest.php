<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RequestCustomerMagicLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'cart_token' => ['nullable', 'string', 'max:255'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
