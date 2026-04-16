<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->getKey();

        return [
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'notification_preferences' => ['sometimes', 'array'],
            'notification_preferences.auth_magic_link' => ['sometimes', 'boolean'],
            'notification_preferences.auth_verify_email' => ['sometimes', 'boolean'],
            'notification_preferences.auth_password_reset' => ['sometimes', 'boolean'],
            'notification_preferences.auth_welcome' => ['sometimes', 'boolean'],
            'notification_preferences.orders_placed' => ['sometimes', 'boolean'],
            'notification_preferences.orders_status_updates' => ['sometimes', 'boolean'],
            'notification_preferences.payments_action_required' => ['sometimes', 'boolean'],
            'notification_preferences.payments_received' => ['sometimes', 'boolean'],
            'notification_preferences.shipments_status_updates' => ['sometimes', 'boolean'],
        ];
    }
}
