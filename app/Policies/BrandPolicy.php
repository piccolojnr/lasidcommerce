<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;

class BrandPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage brands');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->can('manage brands');
    }

    public function create(User $user): bool
    {
        return $user->can('manage brands');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->can('manage brands');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->can('manage brands');
    }
}
