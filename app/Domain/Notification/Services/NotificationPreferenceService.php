<?php

namespace App\Domain\Notification\Services;

use App\Models\User;

class NotificationPreferenceService
{
    /**
     * @return array<string, bool>
     */
    public function defaults(): array
    {
        return [
            'auth_magic_link' => true,
            'auth_verify_email' => true,
            'auth_password_reset' => true,
            'auth_welcome' => true,
            'orders_placed' => true,
            'orders_status_updates' => true,
            'payments_action_required' => true,
            'payments_received' => true,
            'shipments_status_updates' => true,
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function resolveForUser(User $user): array
    {
        $stored = $user->notification_preferences;

        if (! is_array($stored)) {
            $stored = [];
        }

        $resolved = $this->defaults();

        foreach ($resolved as $key => $value) {
            if (array_key_exists($key, $stored)) {
                $resolved[$key] = (bool) $stored[$key];
            }
        }

        return $resolved;
    }

    public function allows(User $user, string $key): bool
    {
        return $this->resolveForUser($user)[$key] ?? true;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, bool>
     */
    public function merge(User $user, array $input): array
    {
        $resolved = $this->resolveForUser($user);

        foreach ($resolved as $key => $value) {
            if (array_key_exists($key, $input)) {
                $resolved[$key] = (bool) $input[$key];
            }
        }

        return $resolved;
    }
}
