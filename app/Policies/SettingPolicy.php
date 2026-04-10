<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    public function manage(User $user): bool
    {
        return $user->can('manage settings');
    }

    public function viewAny(User $user): bool
    {
        return $user->can('manage settings');
    }

    public function view(User $user, Setting $setting): bool
    {
        return $user->can('manage settings');
    }

    public function create(User $user): bool
    {
        return $user->can('manage settings');
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->can('manage settings');
    }

    public function delete(User $user, Setting $setting): bool
    {
        return $user->can('manage settings');
    }
}
