<?php

namespace App\Policies;

use App\Models\ShippingMethod;
use App\Models\User;

class ShippingMethodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage shipments');
    }

    public function view(User $user, ShippingMethod $shippingMethod): bool
    {
        return $user->can('manage shipments');
    }

    public function create(User $user): bool
    {
        return $user->can('manage shipments');
    }

    public function update(User $user, ShippingMethod $shippingMethod): bool
    {
        return $user->can('manage shipments');
    }

    public function delete(User $user, ShippingMethod $shippingMethod): bool
    {
        return $user->can('manage shipments');
    }
}
